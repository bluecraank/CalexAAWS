<?php

namespace App\Services;

use RuntimeException;
use SimpleXMLElement;
use App\Models\Room;
use Carbon\Carbon;


class EwsCalendarService
{
    public function findRoomCalendarItems(
        Room $room,
        string $startDate,
        string $endDate,
        int $maxEntries = 10
    ): array {

        $url = config('ews.url');
        $domain = config('ews.domain');
        $version = config('ews.version', 'Exchange2016');
        $verifySsl = (bool) config('ews.verify_ssl', true);

        // dd($url, $domain, $room->username, $room->password, $version);

        $soap = $this->buildFindItemSoap(
            startDate: $startDate,
            endDate: $endDate,
            maxEntries: $maxEntries,
            version: $version,
            mailbox: $room->email
        );

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $soap,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: "http://schemas.microsoft.com/exchange/services/2006/messages/FindItem"',
            ],
            CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
            CURLOPT_USERPWD => $domain . '\\' . $room->username . ':' . $room->password,
            CURLOPT_SSL_VERIFYPEER => $verifySsl,
            CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('cURL-Fehler: ' . $error);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException("EWS antwortete mit HTTP $httpCode: " . $response);
        }

        return $this->parseCalendarItems($response);
    }

    private function buildFindItemSoap(
        string $startDate,
        string $endDate,
        int $maxEntries,
        string $version,
        ?string $mailbox = null
    ): string {
        $mailboxXml = '';

        if ($mailbox) {
            $mailboxXml = <<<XML
<t:Mailbox>
  <t:EmailAddress>{$this->xml($mailbox)}</t:EmailAddress>
</t:Mailbox>
XML;
        }

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns:m="http://schemas.microsoft.com/exchange/services/2006/messages"
 xmlns:t="http://schemas.microsoft.com/exchange/services/2006/types"
 xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Header>
    <t:RequestServerVersion Version="{$this->xml($version)}"/>
  </soap:Header>
  <soap:Body>
    <m:FindItem Traversal="Shallow">
      <m:ItemShape>
        <t:BaseShape>IdOnly</t:BaseShape>
        <t:AdditionalProperties>
          <t:FieldURI FieldURI="item:Subject"/>
          <t:FieldURI FieldURI="calendar:Start"/>
          <t:FieldURI FieldURI="calendar:End"/>
          <t:FieldURI FieldURI="calendar:Location"/>
        </t:AdditionalProperties>
      </m:ItemShape>
      <m:CalendarView
        StartDate="{$this->xml($startDate)}"
        EndDate="{$this->xml($endDate)}"
        MaxEntriesReturned="{$maxEntries}"/>
      <m:ParentFolderIds>
        <t:DistinguishedFolderId Id="calendar">
          {$mailboxXml}
        </t:DistinguishedFolderId>
      </m:ParentFolderIds>
    </m:FindItem>
  </soap:Body>
</soap:Envelope>
XML;
    }

    private function parseCalendarItems(string $xml): array
    {
        $doc = new SimpleXMLElement($xml);
        $doc->registerXPathNamespace('t', 'http://schemas.microsoft.com/exchange/services/2006/types');
        $doc->registerXPathNamespace('m', 'http://schemas.microsoft.com/exchange/services/2006/messages');

        $responseCode = $doc->xpath('//m:ResponseCode');
        if ($responseCode && (string) $responseCode[0] !== 'NoError') {
            throw new RuntimeException('EWS-Fehler: ' . (string) $responseCode[0]);
        }

        $items = $doc->xpath('//t:CalendarItem') ?: [];
        $result = [];

        foreach ($items as $item) {

            $t = $item->children('http://schemas.microsoft.com/exchange/services/2006/types');

            $itemIdNode = $item->xpath('./t:ItemId');

            $id = $itemIdNode ? (string)$itemIdNode[0]['Id'] : null;
            $changeKey = $itemIdNode ? (string)$itemIdNode[0]['ChangeKey'] : null;

            $result[] = [
                'id'        => $id,
                'changeKey' => $changeKey,
                'subject'   => (string) $t->Subject,
                'start'     => Carbon::parse((string) $t->Start)->setTimezone('Europe/Berlin'),
                'end'       => Carbon::parse((string) $t->End)->setTimezone('Europe/Berlin'),
                'location'  => (string) $t->Location,
            ];
        }

        return $result;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    public function createEntry(
        Room $room,
        Carbon $start,
        Carbon $end,
        string $subject = 'Ad-hoc Meeting'
    ): bool {

        $url = config('ews.url');
        $domain = config('ews.domain');
        $version = config('ews.version', 'Exchange2016');
        $verifySsl = (bool) config('ews.verify_ssl', true);

        $soap = $this->buildCreateItemSoap(
            start: $start,
            end: $end,
            subject: $subject,
            mailbox: $room->email,
            version: $version
        );

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $soap,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: "http://schemas.microsoft.com/exchange/services/2006/messages/CreateItem"',
            ],
            CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
            CURLOPT_USERPWD => $domain . '\\' . $room->username . ':' . $room->password,
            CURLOPT_SSL_VERIFYPEER => $verifySsl,
            CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('cURL-Fehler: ' . $error);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException("EWS antwortete mit HTTP $httpCode: " . $response);
        }

        $doc = new SimpleXMLElement($response);
        $doc->registerXPathNamespace('m', 'http://schemas.microsoft.com/exchange/services/2006/messages');

        $responseCode = $doc->xpath('//m:ResponseCode');

        if ($responseCode && (string) $responseCode[0] !== 'NoError') {
            throw new RuntimeException('EWS CreateItem Fehler: ' . (string) $responseCode[0]);
        }

        return true;
    }

    private function buildCreateItemSoap(
        Carbon $start,
        Carbon $end,
        string $subject,
        string $mailbox,
        string $version
    ): string {

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns:m="http://schemas.microsoft.com/exchange/services/2006/messages"
 xmlns:t="http://schemas.microsoft.com/exchange/services/2006/types"
 xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Header>
    <t:RequestServerVersion Version="{$this->xml($version)}"/>
  </soap:Header>
  <soap:Body>
    <m:CreateItem SendMeetingInvitations="SendToNone">
      <m:SavedItemFolderId>
        <t:DistinguishedFolderId Id="calendar">
          <t:Mailbox>
            <t:EmailAddress>{$this->xml($mailbox)}</t:EmailAddress>
          </t:Mailbox>
        </t:DistinguishedFolderId>
      </m:SavedItemFolderId>

      <m:Items>

        <t:CalendarItem>

          <t:Subject>{$this->xml($subject)}</t:Subject>

          <t:Start>{$start->toIso8601String()}</t:Start>
          <t:End>{$end->toIso8601String()}</t:End>

          <t:LegacyFreeBusyStatus>Busy</t:LegacyFreeBusyStatus>

        </t:CalendarItem>

      </m:Items>

    </m:CreateItem>
  </soap:Body>
</soap:Envelope>
XML;
    }

    private function buildDeleteItemSoap(string $id, string $changeKey): string
    {
        $version = config('ews.version', 'Exchange2016');

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns:m="http://schemas.microsoft.com/exchange/services/2006/messages"
 xmlns:t="http://schemas.microsoft.com/exchange/services/2006/types"
 xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">

  <soap:Header>
    <t:RequestServerVersion Version="{$this->xml($version)}"/>
  </soap:Header>

  <soap:Body>
    <m:DeleteItem DeleteType="MoveToDeletedItems" SendMeetingCancellations="SendToNone">
      <m:ItemIds>
        <t:ItemId Id="{$this->xml($id)}" ChangeKey="{$this->xml($changeKey)}"/>
      </m:ItemIds>
    </m:DeleteItem>
  </soap:Body>

</soap:Envelope>
XML;
    }

    public function deleteEntry(Room $room): bool
    {
        $now = Carbon::now();

        $items = $this->findRoomCalendarItems(
            $room,
            $now->copy()->subHours(2)->toIso8601String(),
            $now->copy()->addHours(2)->toIso8601String(),
            10
        );

        $current = collect($items)->first(function ($item) use ($now) {
            return $now->between($item['start'], $item['end']);
        });

        if (!$current) {
            return false;
        }

        if (!$current['id']) {
            throw new RuntimeException("Keine ItemId gefunden.");
        }

        $soap = $this->buildDeleteItemSoap(
            $current['id'],
            $current['changeKey']
        );

        $url = config('ews.url');
        $domain = config('ews.domain');
        $verifySsl = (bool) config('ews.verify_ssl', true);

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $soap,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: "http://schemas.microsoft.com/exchange/services/2006/messages/DeleteItem"',
            ],
            CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
            CURLOPT_USERPWD => $domain . '\\' . $room->username . ':' . $room->password,
            CURLOPT_SSL_VERIFYPEER => $verifySsl,
            CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new RuntimeException('cURL Fehler: ' . curl_error($ch));
        }

        curl_close($ch);

        return true;
    }
}
