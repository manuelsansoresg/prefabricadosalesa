<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('image/logo_sm.png') }}?v=1">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('image/logo_sm.png') }}?v=1">
<link rel="shortcut icon" type="image/png" href="{{ asset('image/logo_sm.png') }}?v=1">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('image/logo_sm.png') }}?v=1">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer" />

<script defer src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js"></script>

@php
    $googleMapsKey = (string) (config('services.google_maps.key') ?? '');
    if ($googleMapsKey === '') {
        $googleMapsKey = (string) (env('GOOGLE_MAPS_API_KEY') ?: '');
    }
@endphp
@if ((request()->routeIs('admin.site') || request()->is('welcome')) && filled($googleMapsKey))
    <script src="https://maps.googleapis.com/maps/api/js?key={{ urlencode($googleMapsKey) }}&libraries=places&v=weekly&language=es&region=MX" async defer></script>
@endif

@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles
@fluxAppearance
<script>
    try {
        window.localStorage.setItem('flux.appearance', 'light');
    } catch (e) {}

    if (window.Flux && typeof window.Flux.applyAppearance === 'function') {
        const originalApplyAppearance = window.Flux.applyAppearance.bind(window.Flux);
        window.Flux.applyAppearance = function () {
            return originalApplyAppearance('light');
        };
        window.Flux.applyAppearance('light');
    } else {
        document.documentElement.classList.remove('dark');
    }
</script>
