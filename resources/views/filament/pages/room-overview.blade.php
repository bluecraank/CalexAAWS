<x-filament-panels::page>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

@foreach($this->rooms as $room)

<div class="rounded-2xl border bg-white shadow-sm hover:shadow-md transition p-6 flex flex-col justify-between">

    {{-- HEADER --}}
    <div class="flex justify-between items-start mb-4">

        <div>
            <h2 class="text-lg font-semibold flex items-center gap-2">
                🏢 {{ $room->name }}
            </h2>

            <div class="text-sm text-gray-500 mt-1">
                👥 {{ $room->capacity }} Personen
            </div>
        </div>

        {{-- STATUS BADGE --}}
        @if($room->status === 'belegt')
            <span class="px-3 py-1 text-xs font-medium rounded-full bg-red-50 text-red-600 border border-red-200">
                🔴 Belegt
            </span>
        @elseif($room->status === 'bald')
            <span class="px-3 py-1 text-xs font-medium rounded-full bg-yellow-50 text-yellow-700 border border-yellow-200">
                🟡 Bald
            </span>
        @else
            <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-50 text-green-600 border border-green-200">
                🟢 Frei
            </span>
        @endif

    </div>

    {{-- EQUIPMENT --}}
    <div>
        <div class="text-xs font-medium text-gray-400 mb-2 uppercase tracking-wide">
            Ausstattung
        </div>

        <div class="flex flex-wrap gap-2">

            @foreach($room->equipment ?? [] as $eq)

                <span class="px-3 py-1 text-xs rounded-lg bg-gray-100 text-gray-700">

                    @switch($eq)
                        @case('computer') 🖥 Computer @break
                        @case('beamer') 📽 Beamer @break
                        @case('wireless') 📡 Wireless @break
                        @case('monitor') 📺 Monitor @break
                        @case('meeting') 🎤 Meeting @break
                    @endswitch

                </span>

            @endforeach

        </div>
    </div>

</div>

@endforeach

</div>

</x-filament-panels::page>