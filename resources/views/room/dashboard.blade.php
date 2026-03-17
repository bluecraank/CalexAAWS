<!DOCTYPE html>
<html lang="de">

<head>
    <script src="https://unpkg.com/heroicons@2.0.18/dist/heroicons.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $room->name }}</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        * {
            user-select: none;
        }

        /* moderne scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #4b5563;
            /* gray-600 */
            border-radius: 9999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
            /* gray-500 */
        }

        /* Firefox */
        * {
            scrollbar-width: thin;
            scrollbar-color: #4b5563 transparent;
        }

        .free-gradient {
            background: radial-gradient(circle at bottom,
                    rgba(34, 197, 94, 0.28),
                    rgba(17, 24, 39, 0) 75%);
        }

        .busy-gradient {
            background: radial-gradient(circle at bottom,
                    rgba(239, 68, 68, 0.28),
                    rgba(17, 24, 39, 0) 75%);
        }

        .warning-gradient {
            background: radial-gradient(circle at bottom,
                    rgba(234, 179, 8, 0.18),
                    rgba(17, 24, 39, 0) 75%);
        }

        .spinner {
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-top: 2px solid white;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            animation: spin 0.7s linear infinite;
            margin: auto;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

</head>


<!-- <body class="bg-gray-900 text-gray-200 h-screen"> -->

<body id="pageBody" class="bg-gray-900 text-gray-200 h-screen relative overflow-hidden">
    <div id="statusGradient"
        class="pointer-events-none absolute inset-0 transition-all duration-1000">
    </div>
    <div class="grid grid-cols-3 h-screen gap-10 p-10">

        {{-- LEFT SIDE --}}
        <div style="max-height: 68%;" class="col-span-2 flex flex-col justify-between">

            <div>
                <div>
                    <img style="display: inline-block;" src="https://www.doepke.de/typo3conf/ext/doepke/Resources/Public/img/logo.svg" width="135px" alt="">
                </div>

                <h1 class="text-6xl font-bold flex items-center gap-5 pt-3">
                    {{ $room->name }}
                </h1>

                @php
                $current = $room->events
                ->where('start','<=',now())
                    ->where('end','>=',now())
                    ->first();

                    $next = $room->events
                    ->where('start','>',now())
                    ->sortBy('start')
                    ->first();

                    $minutes = $next ? ceil(now()->diffInSeconds($next->start) / 60) : null;
                    @endphp


                    {{-- STATUS --}}
                    <div class="mt-8 text-4xl font-semibold">

                        @if($current)

                        <span class="text-red-400">
                            🔴 Belegt
                        </span>

                        @elseif($next && $minutes <= 15)

                            <span class="text-yellow-400">
                            🟡 Nur noch kurz verfügbar
                            </span>

                            @else

                            <span class="text-green-400">
                                🟢 Frei
                            </span>

                            @endif

                    </div>


                    {{-- NEXT MEETING --}}
                    @if($next)

                    <div class="mt-6 text-xl text-gray-400">


                        <span class="text-white">

                            @if($minutes <= 1)
                                Nächstes Meeting in weniger als 1 Minute
                            @elseif($minutes <= 60)
                                Nächstes Meeting in {{ $minutes }} Minuten
                            @elseif($minutes <= 119)
                                Für {{ $minutes }} Minuten noch frei
                            @else
                                Für {{ (int) ($minutes / 60) }} Stunden noch frei
                            @endif

                                </span>

                    </div>

                    @endif

            </div>


            {{-- ROOM INFO --}}
            <div class="space-y-8" style="position:absolute;bottom:20px;">

                <div class="text-2xl flex items-center gap-4">
                    <div class="flex items-center gap-4 text-2xl">

                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>

                        {{ $room->capacity }} Personen

                    </div>
                </div>


                <div>

                    <div class="text-xl font-semibold mb-4 text-gray-400">
                        Ausstattung
                    </div>

                    <div class="flex flex-wrap gap-4">

                        @foreach($room->equipment ?? [] as $eq)

                        <div class="flex items-center gap-2 bg-gray-800 px-4 py-2 rounded-lg">

                            @if($eq == 'computer')

                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                            </svg>


                            Computer

                            @endif


                            @if($eq == 'monitor')

                            <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 5h18v12H3zM8 21h8" />
                            </svg>

                            Monitor

                            @endif


                            @if($eq == 'meeting')

                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                            </svg>

                            Online-Meeting

                            @endif


                            @if($eq == 'beamer')

                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                            </svg>


                            Beamer

                            @endif


                            @if($eq == 'wireless')

                            <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M8.111 16.404a5 5 0 017.778 0M5.05 13.343a9 9 0 0113.9 0M2 10.293a13 13 0 0120 0" />
                            </svg>

                            Wireless

                            @endif

                        </div>

                        @endforeach

                    </div>

                </div>

            </div>

        </div>



        {{-- RIGHT SIDE CALENDAR --}}
        <div style="max-height: 100%; overflow: auto" class="bg-gray-800 rounded-2xl shadow-xl border border-gray-700 flex flex-col">

            <div class="p-8 flex flex-col flex-1 min-h-0">

                <div>
                    <div style="float:left">
                        <h2 style="display: inline-block;" class="text-2xl font-semibold text-gray-300">
                            {{ now()->translatedFormat('l') }}
                        </h2>
                        <h2 style="display: block;" class="text-1xl font-semibold mb-6 text-gray-300">
                            {{ now()->translatedFormat('d.m.Y')}}

                        </h2>
                    </div>

                    <div style="display: inline-block; float:right" id="clock" class="text-2xl text-white-400 mt-3 font-mono"></div>
                </div>


                @php
                $events = $room->events->sortBy('start');

                $current = $events->first(function($event) {
                return now()->between($event->start, $event->end);
                });

                $pastEvents = $events
                    ->filter(fn($event) =>
                    $event->end < now() &&
                        $event->start->isToday()
                        )
                        ->take(-3);

                $futureEvents = $events
                    ->filter(fn($event) => $event->start > now())
                    ->take(3);
                    @endphp

                    {{-- SCROLLBAR NUR FÜR TERMINE --}}
                    <div class="space-y-3 overflow-y-auto flex-1 min-h-0">

                        {{-- VERGANGENE --}}
                        @foreach($pastEvents as $event)

                        <div class="rounded-xl border border-gray-700 bg-gray-900 p-2 text-sm">

                            <div class="flex gap-3 text-gray-500">

                                <span class="whitespace-nowrap">
                                    {{ $event->start->format('H:i') }} – {{ $event->end->format('H:i') }}
                                </span>

                                <span class="truncate">
                                    {{ $event->subject }}
                                </span>

                            </div>

                        </div>

                        @endforeach


                        {{-- AKTUELL --}}
                        @if($current)

                        <div class="rounded-xl border border-white bg-gray-900 p-4">

                            <div class="text-sm text-gray-400">
                                {{ $current->start->format('H:i') }} – {{ $current->end->format('H:i') }}
                            </div>

                            <div class="text-lg font-semibold">
                                {{ $current->subject }}
                            </div>

                        </div>

                        @endif


                        {{-- ZUKÜNFTIG --}}
                        @foreach($futureEvents as $event)

                        <div class="rounded-xl border border-gray-700 bg-gray-900 p-4">

                            <div class="text-sm text-gray-400">
                                {{ $event->start->format('H:i') }} – {{ $event->end->format('H:i') }}
                            </div>

                            <div class="text-lg font-semibold">
                                {{ $event->subject }}
                            </div>

                        </div>

                        @endforeach

                    </div>

            </div>


            {{-- BUTTON --}}
            <div class="p-8 pt-0 shrink-0">

                @if($current)

                <button
                    onclick="openEndModal()"
                    class="w-full bg-red-500 text-white font-semibold py-3 rounded-xl hover:bg-red-600 transition">
                    Termin beenden
                </button>

                @else

                <button
                    onclick="openBookingModal()"
                    class="w-full bg-white text-black font-semibold py-3 rounded-xl hover:bg-gray-200 transition">
                    Jetzt buchen
                </button>

                @endif

            </div>

        </div>

        <div
            id="bookingModal"
            class="fixed inset-0 bg-black/60 flex items-center justify-center hidden z-50">

            <div class="bg-gray-800 rounded-2xl p-8 w-96 border border-gray-700 relative">

                {{-- CLOSE BUTTON --}}
                <button
                    onclick="closeBookingModal()"
                    class="absolute top-4 right-4 text-gray-400 hover:text-white text-xl">
                    ✕
                </button>

                <h3 class="text-xl font-semibold mb-6 text-center">
                    Buchungsdauer
                </h3>

                <div id="bookingMessage" class="hidden mb-4 text-sm rounded-lg px-4 py-2"></div>

                <div class="grid grid-cols-3 gap-3">

                    <button
                        onclick="bookRoom(30, this)"
                        class="bg-gray-700 hover:bg-gray-600 py-3 rounded-lg text-sm">
                        30 Min
                    </button>

                    <button
                        onclick="bookRoom(60, this)"
                        class="bg-gray-700 hover:bg-gray-600 py-3 rounded-lg text-sm">
                        1 Stunde
                    </button>

                    <button
                        onclick="bookRoom(120, this)"
                        class="bg-gray-700 hover:bg-gray-600 py-3 rounded-lg text-sm">
                        2 Stunden
                    </button>

                </div>

            </div>

        </div>

        <div
            id="endModal"
            class="fixed inset-0 bg-black/60 flex items-center justify-center hidden z-50">

            <div class="bg-gray-800 rounded-2xl p-8 w-96 border border-gray-700 relative">

                {{-- CLOSE BUTTON --}}
                <button
                    onclick="closeEndModal()"
                    class="absolute top-4 right-4 text-gray-400 hover:text-white text-xl">
                    ✕
                </button>

                <h3 class="text-xl font-semibold mb-6 text-center">
                    Termin wirklich beenden?
                </h3>

                <p class="text-gray-400 text-center mb-6">
                    Der aktuelle Termin wird sofort beendet.
                </p>

                <div class="flex gap-4">

                    <button
                        onclick="closeEndModal()"
                        class="flex-1 bg-gray-700 hover:bg-gray-600 py-3 rounded-lg">
                        Abbrechen
                    </button>

                    <button
                        onclick="confirmEndMeeting(this)"
                        class="flex-1 bg-red-500 hover:bg-red-600 py-3 rounded-lg font-semibold">
                        Beenden
                    </button>

                </div>

            </div>

        </div>

        <script>
            /* CLOCK */

            function updateClock() {

                const now = new Date()

                const hours = now.getHours().toString().padStart(2, "0")
                const minutes = now.getMinutes().toString().padStart(2, "0")

                // Sekunden für Blinkeffekt
                const colon = now.getSeconds() % 2 ? ":" : " "

                document.getElementById("clock").innerText =
                    hours + colon + minutes

            }

            setInterval(updateClock, 1000)
            updateClock()

            /* AUTO REFRESH */

            setInterval(() => location.reload(), 30000)

            function openBookingModal() {
                document.getElementById("bookingModal").classList.remove("hidden")
            }

            function closeBookingModal() {
                document.getElementById("bookingModal").classList.add("hidden")
            }

            async function bookRoom(duration, btn) {

                const token = "{{ $room->dashboard_token }}"

                // alle Buttons deaktivieren
                document.querySelectorAll("#bookingModal button").forEach(b => {
                    b.disabled = true
                    b.classList.add("opacity-50", "cursor-not-allowed")
                })

                // Spinner im geklickten Button
                btn.innerHTML = '<div class="spinner"></div>'

                const response = await fetch("/room-book/" + token + "?duration=" + duration)

                const data = await response.json()

                if (data.success) {

                    showBookingMessage("Termin erfolgreich erstellt", true)

                    setTimeout(() => {
                        closeBookingModal()
                        refreshRoom()
                    }, 1200)

                } else {

                    showBookingMessage(data.message)

                    // Buttons wieder aktivieren
                    document.querySelectorAll("#bookingModal button").forEach(b => {
                        b.disabled = false
                        b.classList.remove("opacity-50", "cursor-not-allowed")
                    })

                    btn.innerHTML = duration === 30 ? "30 Min" :
                        duration === 60 ? "1 Stunde" :
                        "2 Stunden"
                }
            }

            function showBookingMessage(text, success = false) {

                const box = document.getElementById("bookingMessage")

                box.innerText = text
                box.classList.remove("hidden")

                if (success) {

                    box.className =
                        "mb-4 text-sm rounded-lg px-4 py-2 bg-green-500/20 text-green-300 border border-green-500"

                } else {

                    box.className =
                        "mb-4 text-sm rounded-lg px-4 py-2 bg-red-500/20 text-red-300 border border-red-500"

                }

                // nach 10 Sekunden ausblenden
                setTimeout(() => {
                    box.classList.add("hidden")
                }, 10000)

            }

            function openEndModal() {
                document.getElementById("endModal").classList.remove("hidden")
            }

            function closeEndModal() {
                document.getElementById("endModal").classList.add("hidden")
            }

            async function confirmEndMeeting(btn) {

                const token = "{{ $room->dashboard_token }}"

                btn.innerHTML = '<div class="spinner"></div>'
                btn.disabled = true

                const response = await fetch("/room-end/" + token)

                const data = await response.json()

                if (data.success) {

                    closeEndModal()
                    refreshRoom()

                } else {

                    btn.innerHTML = "Fehler"
                }
            }

            function refreshRoom() {
                window.location.reload();
            }
        </script>

        <script>
            function updateBackground(status) {

                const el = document.getElementById("statusGradient")

                el.classList.remove("free-gradient", "busy-gradient", "warning-gradient")

                if (status === "free") {
                    el.classList.add("free-gradient")
                }

                if (status === "busy") {
                    el.classList.add("busy-gradient")
                }

                if (status === "warning") {
                    el.classList.add("warning-gradient")
                }

            }
        </script>

        <script>
            @if($current)
            updateBackground("busy")
            @elseif($next && $minutes <= 15)
            updateBackground("warning")
            @else
            updateBackground("free")
            @endif
        </script>
</body>

</html>