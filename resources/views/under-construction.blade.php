<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => 'En construcción'])
    </head>
    <body class="min-h-screen bg-white">
        <main class="mx-auto flex min-h-screen w-[min(1100px,calc(100%-2rem))] flex-col items-center justify-center py-20 text-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-[#E98332]/30 bg-[#E98332]/10 px-6 py-2.5 text-sm font-mono font-bold tracking-widest text-[#E98332] uppercase">
                En construcción
            </span>
            <h1 class="mt-8 text-4xl font-extrabold tracking-tight text-slate-900 md:text-6xl">Estamos trabajando en el sitio</h1>
            <p class="mt-5 max-w-2xl text-sm text-zinc-600 md:text-base">Esta página es temporal. Vuelve pronto.</p>
        </main>

        @fluxScripts
    </body>
</html>
