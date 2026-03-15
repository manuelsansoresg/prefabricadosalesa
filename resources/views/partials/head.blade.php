<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer" />

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js"></script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
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
