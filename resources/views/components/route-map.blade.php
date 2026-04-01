@props([
    'title' => 'Route Map',
    'sourceLatitude' => null,
    'sourceLongitude' => null,
    'destinationLatitude' => null,
    'destinationLongitude' => null,
    'sourceLabel' => 'Point A',
    'destinationLabel' => 'Point B',
    'heightClass' => 'h-64',
])

@php
    $mapboxToken = config('services.mapbox.public_token');
    $hasCoordinates =
        is_numeric($sourceLatitude) &&
        is_numeric($sourceLongitude) &&
        is_numeric($destinationLatitude) &&
        is_numeric($destinationLongitude);

    $distanceKm = null;
    $mapUrl = null;

    if ($hasCoordinates) {
        $sourceLatitude = (float) $sourceLatitude;
        $sourceLongitude = (float) $sourceLongitude;
        $destinationLatitude = (float) $destinationLatitude;
        $destinationLongitude = (float) $destinationLongitude;

        $earthRadiusKm = 6371;
        $latitudeDelta = deg2rad($destinationLatitude - $sourceLatitude);
        $longitudeDelta = deg2rad($destinationLongitude - $sourceLongitude);
        $a =
            sin($latitudeDelta / 2) * sin($latitudeDelta / 2) +
            cos(deg2rad($sourceLatitude)) *
                cos(deg2rad($destinationLatitude)) *
                sin($longitudeDelta / 2) *
                sin($longitudeDelta / 2);
        $distanceKm = $earthRadiusKm * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    if ($hasCoordinates && filled($mapboxToken)) {
        $featureCollection = [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => [
                        'stroke' => '#ff7a00',
                        'stroke-width' => 4,
                        'stroke-opacity' => 0.85,
                    ],
                    'geometry' => [
                        'type' => 'LineString',
                        'coordinates' => [
                            [$sourceLongitude, $sourceLatitude],
                            [$destinationLongitude, $destinationLatitude],
                        ],
                    ],
                ],
                [
                    'type' => 'Feature',
                    'properties' => [
                        'marker-color' => '#0f172a',
                        'marker-size' => 'small',
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$sourceLongitude, $sourceLatitude],
                    ],
                ],
                [
                    'type' => 'Feature',
                    'properties' => [
                        'marker-color' => '#ff7a00',
                        'marker-size' => 'small',
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$destinationLongitude, $destinationLatitude],
                    ],
                ],
            ],
        ];

        $overlay = rawurlencode(json_encode($featureCollection, JSON_UNESCAPED_SLASHES));
        $mapUrl =
            'https://api.mapbox.com/styles/v1/mapbox/streets-v12/static/geojson(' .
            $overlay .
            ')/auto/900x420?' .
            http_build_query([
                'padding' => '64,64,64,64',
                'access_token' => $mapboxToken,
            ]);
    }
@endphp

<section class="rounded-[1.5rem] border border-slate-100 bg-white px-5 py-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $title }}</p>
            @if ($distanceKm !== null)
                <p class="mt-2 text-lg font-black text-slate-900">
                    {{ number_format($distanceKm, $distanceKm >= 10 ? 1 : 2) }} km apart
                </p>
            @endif
        </div>
        <div class="flex items-center gap-3 text-[10px] font-black uppercase tracking-widest text-slate-400">
            <span class="inline-flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded-full bg-slate-900"></span>
                {{ $sourceLabel }}
            </span>
            <span class="inline-flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded-full bg-[var(--brand)]"></span>
                {{ $destinationLabel }}
            </span>
        </div>
    </div>

    @if ($mapUrl)
        <div class="mt-4 overflow-hidden rounded-[1.5rem] border border-slate-100 bg-slate-50">
            <img src="{{ $mapUrl }}" alt="{{ $title }}" class="w-full object-cover {{ $heightClass }}" loading="lazy" />
        </div>
        <p class="mt-3 text-xs font-medium text-slate-500">
            The map draws a direct line between both saved coordinate points.
        </p>
    @elseif(!$hasCoordinates)
        <div class="mt-4 rounded-[1.25rem] border border-dashed border-slate-200 bg-slate-50 px-4 py-5">
            <p class="text-sm font-medium text-slate-500">
                A route map cannot be shown yet because one or both saved coordinate points are missing.
            </p>
        </div>
    @else
        <div class="mt-4 rounded-[1.25rem] border border-dashed border-slate-200 bg-slate-50 px-4 py-5">
            <p class="text-sm font-medium text-slate-500">
                Mapbox is not configured, so the route preview is unavailable.
            </p>
        </div>
    @endif
</section>
