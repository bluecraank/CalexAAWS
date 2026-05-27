<x-filament-panels::page>

<div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide border-b border-gray-200">
            <tr>
                <th class="px-4 py-3">Raum</th>
                <th class="px-4 py-3">Kapazität</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Ausstattung</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
            @foreach($this->rooms as $room)
                @php $status = $this->getRoomStatus($room); @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $room->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $room->capacity }} Pers.</td>
                    <td class="px-4 py-3">
                        @if($status['color'] === 'red')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-red-50 text-red-600 border border-red-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Belegt
                            </span>
                        @elseif($status['color'] === 'yellow')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-yellow-50 text-yellow-700 border border-yellow-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span> Bald belegt
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-green-50 text-green-600 border border-green-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Frei
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($room->equipment ?? [] as $eq)
                                <span class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-600">
                                    @switch($eq)
                                        @case('computer') 🖥 Computer @break
                                        @case('beamer') 📽 Beamer @break
                                        @case('wireless') 📡 Wireless @break
                                        @case('monitor') 📺 Monitor @break
                                        @case('meeting') 🎤 Meeting @break
                                        @default {{ $eq }}
                                    @endswitch
                                </span>
                            @endforeach
                        </div>
                    </td>
                </tr>
            @endforeach

            @if($this->rooms->isEmpty())
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-400">Keine Räume gefunden.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

</x-filament-panels::page>
