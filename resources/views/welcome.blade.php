<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        @include('partials.head', ['title' => 'Prefabricados Alesa'])
    </head>
    <body
        class="bg-[#DCFCE7] text-[#1A1A1A] antialiased"
        x-data="{
            lightboxOpen: false,
            lightboxSrc: null,
            lightboxItems: [],
            lightboxIndex: 0,
            lightboxFading: false,
            videoOpen: false,
            videoSrc: null,
            videoPoster: null,
            galleryItems: @js($galleryImages
                ->map(function ($item) {
                    $type = (string) ($item->media_type ?? 'image');
                    $isVideo = $type === 'video';

                    $thumbPath = $isVideo
                        ? ((string) ($item->video_cover_thumb_path ?? '') !== '' ? $item->video_cover_thumb_path : ((string) ($item->video_cover_path ?? '') !== '' ? $item->video_cover_path : ($item->thumb_path ?: $item->image_path)))
                        : ((string) ($item->thumb_path ?? '') !== '' ? $item->thumb_path : $item->image_path);

                    return [
                        'id' => $item->id,
                        'type' => $type,
                        'src' => asset($item->image_path),
                        'thumb' => asset($thumbPath),
                        'video' => filled($item->video_path) ? asset($item->video_path) : null,
                        'poster' => asset($item->video_cover_path ?: $item->image_path),
                        'posterThumb' => asset($item->video_cover_thumb_path ?: $thumbPath),
                    ];
                })
                ->values()),
            mobileMenuOpen: false,
            _creditsResizeTimer: null,
            get lightboxCurrentSrc() { return this.lightboxItems.length ? this.lightboxItems[this.lightboxIndex] : this.lightboxSrc; },
            get galleryImageItems() { return (this.galleryItems || []).filter(i => i && i.type === 'image'); },
            init() {
                this.onScroll();
                window.addEventListener('scroll', this.onScroll.bind(this), { passive: true });
                this.initCredits();
                window.addEventListener(
                    'resize',
                    () => {
                        clearTimeout(this._creditsResizeTimer);
                        this._creditsResizeTimer = setTimeout(() => this.initCredits(), 120);
                    },
                    { passive: true }
                );
            },
            onScroll() {
                const scrolled = window.scrollY > 12;

                if (this.$refs.headerNav) {
                    this.$refs.headerNav.classList.toggle('shadow-sm', scrolled);
                }

                if (this.$refs.headerInner) {
                    this.$refs.headerInner.classList.toggle('py-2', scrolled);
                    this.$refs.headerInner.classList.toggle('py-4', !scrolled);
                }

                if (this.$refs.headerLogo) {
                    this.$refs.headerLogo.classList.toggle('!h-10', scrolled);
                    this.$refs.headerLogo.classList.toggle('md:!h-12', scrolled);
                    this.$refs.headerLogo.classList.toggle('!h-12', !scrolled);
                    this.$refs.headerLogo.classList.toggle('md:!h-14', !scrolled);
                }
            },
            openLightbox(src) {
                this.lightboxItems = [];
                this.lightboxIndex = 0;
                this.lightboxSrc = src;
                this.lightboxOpen = true;
                document.body.style.overflow = 'hidden';
            },
            openGallery(index) {
                const item = (this.galleryItems || [])[index];
                if (!item) return;

                if (item.type === 'video') {
                    this.openVideo(item);
                    return;
                }

                const images = this.galleryImageItems;
                const imageIndex = images.findIndex(i => i.id === item.id);

                this.lightboxItems = images.map(i => i.src);
                this.lightboxIndex = Math.max(0, imageIndex);
                this.lightboxSrc = null;
                this.lightboxOpen = true;
                document.body.style.overflow = 'hidden';
                this.prefetchNext();
            },
            closeLightbox() {
                this.lightboxOpen = false;
                this.lightboxSrc = null;
                this.lightboxItems = [];
                this.lightboxIndex = 0;
                if (!this.videoOpen) {
                    document.body.style.overflow = '';
                }
            },
            openVideo(item) {
                if (!item || !item.video) return;
                this.videoSrc = item.video;
                this.videoPoster = item.poster || null;
                this.videoOpen = true;
                document.body.style.overflow = 'hidden';
            },
            closeVideo() {
                if (this.$refs.videoPlayer) {
                    try {
                        this.$refs.videoPlayer.pause();
                        this.$refs.videoPlayer.currentTime = 0;
                    } catch (_) {}
                }
                this.videoOpen = false;
                this.videoSrc = null;
                this.videoPoster = null;
                if (!this.lightboxOpen) {
                    document.body.style.overflow = '';
                }
            },
            nextLightbox() {
                if (this.lightboxItems.length < 2) return;
                this.lightboxFading = true;
                setTimeout(() => {
                    this.lightboxIndex = (this.lightboxIndex + 1) % this.lightboxItems.length;
                    this.prefetchNext();
                    requestAnimationFrame(() => (this.lightboxFading = false));
                }, 140);
            },
            prevLightbox() {
                if (this.lightboxItems.length < 2) return;
                this.lightboxFading = true;
                setTimeout(() => {
                    this.lightboxIndex = (this.lightboxIndex - 1 + this.lightboxItems.length) % this.lightboxItems.length;
                    this.prefetchNext();
                    requestAnimationFrame(() => (this.lightboxFading = false));
                }, 140);
            },
            prefetchNext() {
                if (this.lightboxItems.length < 2) return;
                const nextIndex = (this.lightboxIndex + 1) % this.lightboxItems.length;
                const img = new Image();
                img.src = this.lightboxItems[nextIndex];
            },
            initCredits() {
                const stages = document.querySelectorAll('[data-al-credits]');
                stages.forEach((stage) => {
                    const mask = stage.querySelector('[data-al-credits-mask]');
                    const track = stage.querySelector('[data-al-credits-track]');
                    const block = stage.querySelector('[data-al-credits-block]');
                    if (!mask || !track || !block) return;

                    const loopHeight = Math.ceil(block.getBoundingClientRect().height);
                    const maskHeight = Math.ceil(mask.getBoundingClientRect().height);
                    if (!loopHeight || !maskHeight) return;

                    const startOffset = Math.max(0, maskHeight - loopHeight + 1);
                    track.style.setProperty('--al-credits-loop', `${loopHeight}px`);
                    track.style.setProperty('--al-credits-start', `${startOffset}px`);
                    track.dataset.alReady = '1';
                });
            },
        }"
        @keydown.escape.window="closeLightbox(); closeVideo(); mobileMenuOpen = false"
        @keydown.arrow-right.window="lightboxOpen && lightboxItems.length > 1 && nextLightbox()"
        @keydown.arrow-left.window="lightboxOpen && lightboxItems.length > 1 && prevLightbox()"
    >
        <header class="hidden md:block fixed inset-x-0 top-0 z-50">
            <nav x-ref="headerNav" class="w-full bg-[#DCFCE7]/95 backdrop-blur-sm transition-all duration-300 ease-out">
                <div x-ref="headerInner" class="mx-auto flex w-[min(1280px,calc(100%-2rem))] max-w-7xl items-center justify-between py-3 transition-all duration-300 ease-out md:py-4">
                    <div class="h-10 w-10 md:h-14 md:w-14"></div>

                    <div class="hidden items-center gap-8 text-[17px] leading-none font-bold tracking-wide uppercase text-[#E98332] md:flex">
                        <a href="#inicio" class="relative pb-1 transition-colors duration-200 hover:text-[#E98332] after:absolute after:inset-x-0 after:bottom-0 after:h-[2px] after:origin-left after:scale-x-0 after:rounded-full after:bg-[#E98332] after:transition-transform after:duration-200 hover:after:scale-x-100">Inicio</a>
                        <a href="#nosotros" class="relative pb-1 transition-colors duration-200 hover:text-[#E98332] after:absolute after:inset-x-0 after:bottom-0 after:h-[2px] after:origin-left after:scale-x-0 after:rounded-full after:bg-[#E98332] after:transition-transform after:duration-200 hover:after:scale-x-100">Nosotros</a>
                        <a href="#productos" class="relative pb-1 transition-colors duration-200 hover:text-[#E98332] after:absolute after:inset-x-0 after:bottom-0 after:h-[2px] after:origin-left after:scale-x-0 after:rounded-full after:bg-[#E98332] after:transition-transform after:duration-200 hover:after:scale-x-100">Productos</a>
                        <a href="#galeria" class="relative pb-1 transition-colors duration-200 hover:text-[#E98332] after:absolute after:inset-x-0 after:bottom-0 after:h-[2px] after:origin-left after:scale-x-0 after:rounded-full after:bg-[#E98332] after:transition-transform after:duration-200 hover:after:scale-x-100">Galería</a>
                        <a href="#contacto" class="relative pb-1 transition-colors duration-200 hover:text-[#E98332] after:absolute after:inset-x-0 after:bottom-0 after:h-[2px] after:origin-left after:scale-x-0 after:rounded-full after:bg-[#E98332] after:transition-transform after:duration-200 hover:after:scale-x-100">Contacto</a>
                    </div>

                    <div class="h-10 w-10 md:h-14 md:w-14"></div>
                </div>
            </nav>
        </header>

        <!-- Mobile Bottom Navigation -->
        <nav class="fixed inset-x-0 bottom-0 z-50 flex items-center justify-around border-t border-black/10 bg-[#DCFCE7]/95 pb-3 pt-3 backdrop-blur-sm md:hidden">
            <a href="#inicio" class="flex flex-col items-center gap-1 px-2 text-[10px] font-bold tracking-wide uppercase text-[#008D62]">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                Inicio
            </a>
            <a href="#nosotros" class="flex flex-col items-center gap-1 px-2 text-[10px] font-bold tracking-wide uppercase text-slate-500 hover:text-[#008D62]">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                Nosotros
            </a>
            <a href="#productos" class="flex flex-col items-center gap-1 px-2 text-[10px] font-bold tracking-wide uppercase text-slate-500 hover:text-[#008D62]">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                Productos
            </a>
            <a href="#galeria" class="flex flex-col items-center gap-1 px-2 text-[10px] font-bold tracking-wide uppercase text-slate-500 hover:text-[#008D62]">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                Galería
            </a>
            <a href="#contacto" class="flex flex-col items-center gap-1 px-2 text-[10px] font-bold tracking-wide uppercase text-slate-500 hover:text-[#008D62]">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                Contacto
            </a>
        </nav>

        <section id="inicio" class="relative min-h-[40svh] overflow-hidden bg-[#DCFCE7] sm:min-h-[100svh]">
            <div class="absolute inset-0">
                @php
                    $heroMediaPath = (string) ($siteSettings?->hero_video_path ?? '');
                    $heroIsImage = $heroMediaPath !== '' && ! preg_match('/\.(mp4|webm)$/i', $heroMediaPath);
                    $heroSrc = $heroIsImage ? $heroMediaPath : 'image/empresa.jpg';
                @endphp
                <img
                    src="{{ asset($heroSrc) }}"
                    alt="Prefabricados Alesa"
                    class="h-full w-full object-cover"
                    loading="eager"
                />
            </div>
            <div class="absolute inset-0 bg-[#DCFCE7]/25"></div>

            <style>
                .al-credits-mask {
                    overflow: hidden;
                    -webkit-mask-image: linear-gradient(to bottom, transparent, black 6%, black 97%, transparent);
                    mask-image: linear-gradient(to bottom, transparent, black 6%, black 97%, transparent);
                }
                .al-credits-stage { perspective: 700px; }
                @keyframes alCreditsScroll {
                    0% { transform: translate3d(0, var(--al-credits-start, 0px), 0) rotateX(18deg); }
                    100% { transform: translate3d(0, calc(var(--al-credits-start, 0px) - var(--al-credits-loop, 0px)), 0) rotateX(18deg); }
                }
                .al-credits-track { animation: alCreditsScroll 26s linear infinite; transform-origin: 50% 100%; top: 0; will-change: transform; opacity: 0; }
                .al-credits-track[data-al-ready="1"] { opacity: 1; }
                @media (prefers-reduced-motion: reduce) {
                    .al-credits-track { animation: none; transform: none; }
                }
            </style>

            <div class="relative mx-auto grid min-h-[40svh] w-[min(1280px,calc(100%-2rem))] max-w-7xl items-start md:items-center gap-10 px-6 pb-10 pt-24 sm:min-h-[100svh] sm:px-0 sm:pb-16 sm:pt-32 md:grid-cols-2">
                <div class="hidden md:flex items-center justify-center">
                    <img
                        src="{{ asset('image/logo_transparente.png') }}"
                        alt="Prefabricados Alesa"
                        class="pointer-events-none w-[92%] max-w-xl select-none opacity-40 drop-shadow-[0_0_14px_rgba(255,255,255,0.35)] md:opacity-70"
                        loading="eager"
                    />
                </div>

                <div class="w-full">
                    <div class="relative w-full max-w-2xl overflow-hidden rounded-3xl bg-white/55 p-6 backdrop-blur-sm md:ml-auto">
                        <img
                            src="{{ asset('image/logo_transparente.png') }}"
                            alt="Fondo móvil"
                            class="absolute inset-0 h-full w-full object-contain p-8 opacity-40 pointer-events-none select-none md:hidden"
                        />
                        <div class="relative z-10 al-credits-stage" data-al-credits>
                            <div class="al-credits-mask relative h-[260px] sm:h-[320px] md:h-[460px]" data-al-credits-mask>
                                <div class="al-credits-track absolute inset-x-0 top-0" data-al-credits-track data-al-ready="0">
                                    <div class="al-credits-block text-center" data-al-credits-block>
                                        <div class="pt-16 md:pt-0">
                                            <h1 class="text-xl font-black leading-tight tracking-tight text-[#0f172a] md:text-3xl">
                                                <span class="text-orange-600"> PREFABRICADOS ALESA, S.A. DE C.V.</span>
                                                <br>LO QUE TU PROYECTO NECESITA ES CALIDAD
                                            </h1>

                                            <p class="mt-4 text-left text-xs font-medium leading-relaxed text-[#111827] md:mt-8 md:text-base">
                                                JUNTAMOS LA TECNOLOGÍA CON INSUMOS DE CALIDAD, PARA QUE NUESTROS PRODUCTOS SEAN CONFIABLES Y PERDURABLES, CUMPLIENDO SIEMPRE CON LAS NORMAS QUE RIGEN LA INDUSTRIA DE LA CONSTRUCCIÓN.
                                            </p>
                                        </div>
                                        <div class="h-10 md:h-32"></div>
                                    </div>

                                    <div class="al-credits-block text-center" aria-hidden="true">
                                        <div class="pt-16 md:pt-0">
                                            <h1 class="text-xl font-black leading-tight tracking-tight text-[#0f172a] md:text-3xl">
                                                <span class="text-orange-600"> PREFABRICADOS ALESA, S.A. DE C.V.</span>
                                                <br>LO QUE TU PROYECTO NECESITA ES CALIDAD
                                            </h1>

                                            <p class="mt-4 text-left text-xs font-medium leading-relaxed text-[#111827] md:mt-8 md:text-base">
                                                JUNTAMOS LA TECNOLOGÍA CON INSUMOS DE CALIDAD, PARA QUE NUESTROS PRODUCTOS SEAN CONFIABLES Y PERDURABLES, CUMPLIENDO SIEMPRE CON LAS NORMAS QUE RIGEN LA INDUSTRIA DE LA CONSTRUCCIÓN.
                                            </p>
                                        </div>
                                        <div class="h-10 md:h-32"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </section>

        <section id="nosotros" class="relative bg-[#DCFCE7]">
            <div class="mx-auto w-[min(1100px,calc(100%-2rem))] py-20 md:py-28" data-reveal>
                <div class="mb-12 flex items-center justify-center gap-4">
                    <div class="h-px w-14 bg-slate-200"></div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#E98332]/30 bg-[#E98332]/10 px-8 py-3 text-base font-black tracking-widest text-[#E98332] uppercase md:px-10 md:text-lg">
                       
                        Nosotros
                    </span>
                    <div class="h-px w-14 bg-slate-200"></div>
                </div>

                <div class="grid gap-10 md:grid-cols-12 md:gap-12">
                    <div class="order-2 md:order-1 md:col-span-6 md:flex md:items-center">
                        <div class="w-full overflow-hidden rounded-2xl border border-black/10 bg-[#DCFCE7] shadow-sm">
                            <div class="relative aspect-[4/3] w-full bg-[#DCFCE7]">
                                <img src="{{ asset($about?->image_path ?: 'image/empresa.jpg') }}" alt="Prefabricados Alesa" class="absolute inset-0 h-full w-full object-cover" />
                            </div>
                        </div>
                    </div>
                    <div class="order-1 md:order-2 md:col-span-6">
                        <div class="max-w-2xl">
                            <h2 class="text-3xl font-extrabold tracking-tight text-[#008D62] text-center md:text-4xl md:text-left">
                                {{ $about?->headline ?? 'Empresa 100% campechana con tecnología alemana' }}
                            </h2>

                            <div class="mt-6 grid gap-4">
                                <div>
                                    <p class="text-sm font-extrabold tracking-widest text-slate-900 uppercase">Misión</p>
                                    <p class="mt-3 text-sm leading-relaxed text-zinc-600 md:text-base">
                                        {{ $about?->mission ?? 'Fabricar productos de calidad, en cantidades suficientes y a precios justos; contribuyendo así, al desarrollo de la región.' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-sm font-extrabold tracking-widest text-slate-900 uppercase">Visión</p>
                                    <p class="mt-3 text-sm leading-relaxed text-zinc-600 md:text-base">
                                        {{ $about?->vision ?? 'Ser la opción más confiable en prefabricados en Campeche, con procesos industriales y tecnología de punta, garantizando calidad constante y entregas oportunas.' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-sm font-extrabold tracking-widest text-slate-900 uppercase">Historia</p>
                                    <p class="mt-3 text-sm leading-relaxed text-zinc-600 md:text-base">
                                        @php
                                            $historyText =
                                                $about?->history ??
                                                $about?->body ??
                                                'Prefabricados Alesa, S.A. de C.V., se fundó en el año de 2002 y fue el 3 de Agosto de 2004 cuando iniciamos operaciones, después de dos años de llevar el proyecto poco a poco y sorteando la difícil situación económica que imperaba. Somos una empresa 100% campechana que tomando lo más alta tecnología disponible, se preocupa por competir primero con calidad; por esta razón se adquirió una máquina bloquera de origen Alemán, marca Euroblock modelo 2005, además de que cuidamos la calidad de la materia prima para la elaboración de nuestros productos.';
                                            $historyHtml = e($historyText);
                                            $historyHtml = preg_replace('/\b(2004|2005|modelo\s+\d{4})\b/iu', '<span class="font-mono font-semibold">$0</span>', $historyHtml);
                                        @endphp
                                        {!! $historyHtml !!}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="productos" class="bg-[#DCFCE7]">
            <div class="mx-auto w-[min(1100px,calc(100%-2rem))] py-20 md:py-28">
                <div class="mb-12 flex items-center justify-center gap-4">
                    <div class="h-px w-14 bg-slate-200"></div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#E98332]/30 bg-[#E98332]/10 px-8 py-3 text-base font-black tracking-widest text-[#E98332] uppercase md:px-10 md:text-lg">
                       
                        Productos
                    </span>
                    <div class="h-px w-14 bg-slate-200"></div>
                </div>

                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-3xl font-extrabold tracking-tight text-[#008D62] text-center md:text-4xl md:text-left">Catálogo</h2>
                        <p class="mt-4 max-w-2xl text-sm text-zinc-600 md:text-base">
                            Fabricamos materiales con estándares de ingeniería para mantener una calidad optima. 
                        </p>
                    </div>
                </div>

                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3" data-stagger="0.1">
                    @forelse ($products as $product)
                        @php
                            $description = trim((string) $product->description);
                            $rawTechSpecs = is_array($product->tech_specs) ? $product->tech_specs : [];
                            $specs = collect($rawTechSpecs)
                                ->map(fn ($row) => [trim((string) ($row['label'] ?? '')), trim((string) ($row['value'] ?? ''))])
                                ->filter(fn ($row) => filled($row[0]) || filled($row[1]))
                                ->values();

                            if ($specs->isEmpty()) {
                                $lines = collect(preg_split("/\\r\\n|\\n|\\r/", $description))->map(fn ($line) => trim($line))->filter();
                                $specs = $lines->map(function ($line) {
                                    $delimiter = str_contains($line, '|') ? '|' : (str_contains($line, ':') ? ':' : null);
                                    if (! $delimiter) {
                                        return ['Detalle', $line];
                                    }

                                    [$label, $value] = array_pad(explode($delimiter, $line, 2), 2, '');

                                    return [trim($label), trim($value)];
                                })->filter(fn ($row) => filled($row[0]) || filled($row[1]))->values();

                                if ($specs->isEmpty() && filled($description)) {
                                    $specs = collect([['Descripción', $description]]);
                                }
                            }

                            $unit = trim((string) ($product->unit ?? ''));
                            if ($unit !== '') {
                                $specs = collect([['Unidad', $unit]])->concat($specs)->values();
                            }

                            $datasheetPath = trim((string) ($product->datasheet_path ?? ''));
                        @endphp
                        <article data-stagger-item class="group flex min-h-[400px] flex-col overflow-hidden rounded-3xl border border-black/10 bg-white shadow-sm transition-all duration-[400ms] ease-out transform-gpu hover:-translate-y-2 hover:shadow-xl">
                            <div class="relative h-48 overflow-hidden border border-gray-100 border-x-0 border-t-0">
                                <button
                                    type="button"
                                    class="block h-full w-full cursor-zoom-in"
                                    aria-label="Ver imagen de {{ $product->title }}"
                                    @click="openLightbox('{{ asset($product->image_path) }}')"
                                >
                                    <span class="pointer-events-none absolute inset-0 bg-orange-500/10 opacity-0 transition-opacity duration-[400ms] ease-out group-hover:opacity-100"></span>
                                    <img
                                        src="{{ asset($product->thumb_path ?: $product->image_path) }}"
                                        alt="{{ $product->title }}"
                                        class="h-48 w-full object-cover transition-transform duration-[400ms] ease-out group-hover:scale-105"
                                        loading="lazy"
                                    />
                                </button>
                            </div>
                            <div class="flex flex-1 flex-col p-6">
                                <p class="font-mono text-xs font-bold tracking-widest text-[#E98332] uppercase">ESPECIFICACIÓN</p>
                                <h3 class="mt-2 text-2xl font-extrabold text-[#1A1A1A]">{{ $product->title }}</h3>

                                <table class="mt-4 w-full font-mono text-[13px] text-zinc-700">
                                    <tbody>
                                        @forelse ($specs as $specRow)
                                            <tr class="border-b border-zinc-100 last:border-b-0">
                                                <td class="py-1 pr-4 text-zinc-600">{{ $specRow[0] }}</td>
                                                <td class="py-1 text-zinc-900 font-semibold js-countup" data-countup-auto>{{ $specRow[1] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="py-1 pr-4 text-zinc-600">Especificación</td>
                                                <td class="py-1 text-zinc-800 font-semibold">—</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                <div class="mt-6 flex flex-wrap gap-3">
                                    @if ($datasheetPath !== '')
                                        <a href="{{ asset($datasheetPath) }}" target="_blank" rel="noopener" data-icon-shift class="inline-flex items-center justify-center gap-2 rounded-xl border border-black/10 bg-white px-4 py-2 text-sm font-semibold text-slate-900 transition-colors duration-[400ms] ease-out hover:bg-slate-50">
                                            <i class="fa-solid fa-file-pdf text-[#E98332] al-icon al-icon-up"></i>
                                            Ficha técnica
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-2xl border border-black/10 bg-white p-8 text-center shadow-sm">
                            <p class="text-sm text-zinc-600">Aún no hay productos publicados. Entra al panel para cargar el catálogo.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="galeria" class="bg-[#DCFCE7]">
            <div class="mx-auto w-[min(1100px,calc(100%-2rem))] py-20 md:py-28">
                <div class="mb-12 flex items-center justify-center gap-4">
                    <div class="h-px w-14 bg-slate-200"></div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#E98332]/30 bg-[#E98332]/10 px-8 py-3 text-base font-black tracking-widest text-[#E98332] uppercase md:px-10 md:text-lg">
                        
                        Galería
                    </span>
                    <div class="h-px w-14 bg-slate-200"></div>
                </div>

                <div>
                    <h2 class="text-3xl font-extrabold tracking-tight text-[#008D62] text-center md:text-4xl md:text-left">Calidad en cada Proyecto</h2>
                    <p class="mt-4 max-w-2xl text-sm text-zinc-600 md:text-base">
                       
                    </p>
                </div>

                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($galleryImages as $image)
                        @php
                            $isVideo = (string) ($image->media_type ?? 'image') === 'video';
                            $thumbPath = $isVideo
                                ? ((string) ($image->video_cover_thumb_path ?? '') !== '' ? $image->video_cover_thumb_path : ((string) ($image->video_cover_path ?? '') !== '' ? $image->video_cover_path : ($image->thumb_path ?: $image->image_path)))
                                : ((string) ($image->thumb_path ?? '') !== '' ? $image->thumb_path : $image->image_path);
                        @endphp
                        <button
                            type="button"
                            class="group relative aspect-[4/3] w-full cursor-zoom-in overflow-hidden rounded-2xl border border-black/10 bg-[#DCFCE7] shadow-sm"
                            @click="openGallery({{ $loop->index }})"
                        >
                            <img
                                src="{{ asset($thumbPath) }}"
                                alt="Galería"
                                class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                                loading="lazy"
                            />
                            <span class="pointer-events-none absolute inset-0 bg-linear-to-t from-black/35 via-transparent to-transparent opacity-0 transition group-hover:opacity-100"></span>
                            @if ($isVideo)
                                <div class="pointer-events-none absolute inset-0 grid place-items-center">
                                    <div class="grid size-14 place-items-center rounded-full bg-black/60 text-white">
                                        <i class="fa-solid fa-play text-lg"></i>
                                    </div>
                                </div>
                            @endif
                        </button>
                    @empty
                        <div class="col-span-full rounded-2xl border border-black/10 bg-[#DCFCE7] p-8 text-center shadow-sm">
                            <p class="text-sm text-zinc-600">Aún no hay imágenes en la galería. Entra al panel para subirlas.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="contacto" class="bg-[#DCFCE7]">
            <div class="mx-auto w-[min(1100px,calc(100%-2rem))] py-20 md:py-28" data-reveal>
                <div class="mb-12 flex items-center justify-center gap-4">
                    <div class="h-px w-14 bg-slate-200"></div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#E98332]/30 bg-[#E98332]/10 px-8 py-3 text-base font-black tracking-widest text-[#E98332] uppercase md:px-10 md:text-lg">
                        
                        Contacto
                    </span>
                    <div class="h-px w-14 bg-slate-200"></div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-black/10 bg-white shadow-sm">
                    @php
                        $rawMapUrl = trim((string) ($siteSettings?->map_embed_url ?? ''));

                        $address = trim((string) ($siteSettings?->contact_address ?? ''));
                        $address = $address !== '' ? $address : 'Libramiento carretera de Campeche a Uayamón KM. 2.6';
                        $encodedAddress = rawurlencode($address);

                        $fallbackMapUrl = "https://www.google.com/maps?q=Prefabricados%20Alesa%2C%20S.A.%20de%20C.V.%20{$encodedAddress}&output=embed&z=17";

                        if ($rawMapUrl === '') {
                            $mapUrl = $fallbackMapUrl;
                        } elseif (str_contains($rawMapUrl, '/maps/embed') || str_contains($rawMapUrl, 'output=embed')) {
                            $mapUrl = $rawMapUrl;
                        } else {
                            $mapUrl = $fallbackMapUrl;
                        }

                        $mapLat = null;
                        $mapLng = null;

                        if ($rawMapUrl !== '') {
                            $parts = parse_url($rawMapUrl);
                            $query = [];
                            parse_str((string) ($parts['query'] ?? ''), $query);
                            $q = (string) ($query['q'] ?? '');

                            if (preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/', $q, $m)) {
                                $mapLat = (float) $m[1];
                                $mapLng = (float) $m[2];
                            }
                        }
                    @endphp
                    @if (is_float($mapLat) && is_float($mapLng))
                        <div
                            id="alexaMap"
                            class="h-[480px] w-full md:h-[560px]"
                            data-lat="{{ $mapLat }}"
                            data-lng="{{ $mapLng }}"
                        ></div>
                        <script>
                            (function () {
                                const mapEl = document.getElementById('alexaMap');
                                if (!mapEl || mapEl.dataset.ready === '1') return;
                                mapEl.dataset.ready = '1';

                                const lat = parseFloat(mapEl.dataset.lat || '');
                                const lng = parseFloat(mapEl.dataset.lng || '');

                                if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

                                function init() {
                                    if (!window.google || !window.google.maps) {
                                        setTimeout(init, 200);
                                        return;
                                    }

                                    const center = { lat: lat, lng: lng };
                                    const map = new window.google.maps.Map(mapEl, {
                                        center: center,
                                        zoom: 16,
                                        streetViewControl: false,
                                        mapTypeControl: false,
                                        fullscreenControl: true,
                                    });

                                    new window.google.maps.Marker({
                                        position: center,
                                        map: map,
                                    });
                                }

                                init();
                            })();
                        </script>
                    @else
                        <iframe
                            title="Mapa Prefabricados Alesa"
                            class="h-[480px] w-full md:h-[560px]"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            src="{{ $mapUrl }}"
                        ></iframe>
                    @endif
                </div>

                <div class="mt-10 h-px w-full bg-slate-200/80"></div>

                <div class="mt-10 grid gap-10 md:grid-cols-12 md:gap-12">
                    <div class="md:col-span-5">
                        <h2 class="text-3xl font-extrabold tracking-tight text-[#008D62] text-center md:text-4xl md:text-left">Hablemos de tu proyecto</h2>
                        <p class="mt-4 text-sm text-zinc-600 md:text-base">
                            Ubicación: {{ trim((string) ($siteSettings?->contact_address ?? '')) !== '' ? $siteSettings->contact_address : 'Libramiento carretera de Campeche a Uayamón KM. 2.6' }}
                        </p>

                        @php
                            $contactEmails = $siteSettings?->contactEmails ?? collect();
                            $contactPhones = $siteSettings?->contactPhones ?? collect();
                        @endphp

                        <form
                            method="POST"
                            action="{{ route('contact.send') }}"
                            class="mt-8 space-y-4"
                            x-data="{
                                sending: false,
                                sent: false,
                                error: '',
                                startedAt: Date.now(),
                                async submit() {
                                    this.error = '';
                                    this.sent = false;
                                    this.sending = true;
                                    try {
                                        const form = this.$el;
                                        const response = await fetch(form.action, {
                                            method: 'POST',
                                            headers: {
                                                Accept: 'application/json',
                                                'X-Requested-With': 'XMLHttpRequest',
                                            },
                                            body: new FormData(form),
                                        });

                                        if (response.ok) {
                                            this.sent = true;
                                            form.reset();
                                            this.startedAt = Date.now();
                                            return;
                                        }

                                        const payload = await response.json().catch(() => null);
                                        const firstError = payload?.errors ? Object.values(payload.errors).flat()[0] : null;
                                        this.error = firstError || payload?.message || 'No se pudo enviar el mensaje. Intenta nuevamente.';
                                    } catch (e) {
                                        this.error = 'No se pudo enviar el mensaje. Intenta nuevamente.';
                                    } finally {
                                        this.sending = false;
                                    }
                                },
                            }"
                            @submit.prevent="submit()"
                        >
                            @csrf
                            <div style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                                <label for="contactWebsite">Sitio web</label>
                                <input id="contactWebsite" type="text" name="website" tabindex="-1" autocomplete="off" />
                            </div>
                            <input type="hidden" name="started_at" :value="startedAt" />
                            <div x-show="sent" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                                Mensaje enviado. En breve nos pondremos en contacto.
                            </div>
                            <div x-show="error" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" x-text="error"></div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <input type="text" name="name" value="{{ old('name') }}" placeholder="Nombre" class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-black placeholder:text-black/35 focus:border-[#008D62]/70 focus:outline-hidden" />
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="Correo" class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-black placeholder:text-black/35 focus:border-[#008D62]/70 focus:outline-hidden" />
                            </div>
                            <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="Teléfono" class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-black placeholder:text-black/35 focus:border-[#008D62]/70 focus:outline-hidden" />
                            <textarea name="message" rows="5" placeholder="Mensaje" class="w-full resize-y rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-black placeholder:text-black/35 focus:border-[#008D62]/70 focus:outline-hidden">{{ old('message') }}</textarea>
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#008D62] px-6 py-3 text-sm font-semibold text-white hover:bg-[#008D62]/90 disabled:cursor-not-allowed disabled:opacity-70" :disabled="sending">
                                <i class="fa-solid fa-paper-plane"></i>
                                <span x-text="sending ? 'Enviando…' : 'Enviar mensaje'"></span>
                            </button>
                        </form>
                    </div>

                    <div class="md:col-span-7 max-md:mt-2 md:border-l md:border-black/10 md:pl-10">
                        <h3 class="text-3xl font-extrabold tracking-tight text-[#008D62] text-center md:text-4xl md:text-left">Directorio</h3>
                        <p class="mt-3 text-sm text-zinc-600">Datos de contacto.</p>

                        <div class="mt-8 space-y-6 text-zinc-700">
                            @if ($contactEmails->isNotEmpty())
                                @foreach ($contactEmails as $email)
                                    @php
                                        $label = trim((string) ($email->label ?? ''));
                                        $label = $label !== '' ? $label : 'Correo';
                                        $emailValue = trim((string) ($email->email ?? ''));
                                        $bccEmail = 'gerencia.alesa@gmail.com';
                                        $mailHref = $emailValue !== '' ? ('mailto:'.$emailValue.'?'.http_build_query(['bcc' => $bccEmail])) : '#';
                                    @endphp
                                    <div>
                                        <p class="text-xs font-bold tracking-widest text-[#E98332] uppercase">{{ $label }}</p>
                                        <a class="mt-1 inline-flex font-mono text-sm text-zinc-900 transition-colors duration-200 ease-out hover:text-[#008D62]" href="{{ $mailHref }}">
                                            {{ $emailValue }}
                                        </a>
                                    </div>
                                @endforeach
                            @endif

                            @if ($contactPhones->isNotEmpty())
                                <div>
                                    <p class="text-xs font-bold tracking-widest text-[#E98332] uppercase">TELÉFONOS</p>
                                    @foreach ($contactPhones as $phone)
                                        @php
                                            $phoneTrimmed = trim((string) $phone->phone);
                                            $tel = preg_replace('/\s+/', '', $phoneTrimmed);
                                        @endphp
                                        <div class="mt-1 flex items-center gap-2">
                                            <svg viewBox="0 0 24 24" class="size-4 text-[#008D62]" fill="currentColor" aria-hidden="true">
                                                <path d="M12 2a10 10 0 0 0-8.66 15l-1.2 4.38 4.49-1.18A10 10 0 1 0 12 2Zm0 18.18a8.16 8.16 0 0 1-4.15-1.13l-.3-.18-2.67.7.72-2.6-.2-.3a8.18 8.18 0 1 1 6.6 3.5Zm4.47-5.8c-.25-.13-1.48-.73-1.7-.82-.23-.08-.4-.12-.56.13-.17.25-.65.82-.8.98-.14.17-.29.19-.54.07-.25-.12-1.05-.39-2-1.26-.74-.65-1.24-1.45-1.38-1.7-.15-.25-.02-.38.1-.5.1-.1.25-.27.37-.4.12-.12.16-.2.25-.35.08-.17.04-.3-.02-.44-.06-.13-.56-1.35-.77-1.84-.2-.49-.4-.42-.56-.43h-.48c-.16 0-.43.06-.65.3-.22.25-.86.83-.86 2.02s.88 2.35 1 2.5c.13.17 1.73 2.63 4.18 3.7.58.25 1.03.4 1.38.5.58.18 1.1.15 1.51.1.47-.08 1.48-.6 1.69-1.18.2-.58.2-1.08.14-1.18-.06-.1-.23-.17-.48-.3Z"/>
                                            </svg>
                                            <a class="font-mono text-sm text-zinc-900 transition-colors duration-200 ease-out hover:text-[#008D62]" href="tel:{{ $tel }}">{{ $phoneTrimmed }}</a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="border-t border-black/10 bg-[#DCFCE7]/95 py-12">
            <div class="mx-auto flex w-[min(1100px,calc(100%-2rem))] flex-col items-center gap-5 text-sm text-zinc-600 md:flex-row md:justify-between">
                <p class="text-center font-medium md:text-left">© {{ now()->year }} Prefabricados Alesa</p>
                <div class="flex flex-wrap items-center justify-center gap-6 font-semibold md:justify-end">
                    <a href="#inicio" class="transition-colors duration-200 hover:text-[#008D62]">Inicio</a>
                    <a href="#nosotros" class="transition-colors duration-200 hover:text-[#008D62]">Nosotros</a>
                    <a href="#productos" class="transition-colors duration-200 hover:text-[#008D62]">Productos</a>
                    <a href="#galeria" class="transition-colors duration-200 hover:text-[#008D62]">Galería</a>
                    <a href="#contacto" class="transition-colors duration-200 hover:text-[#008D62]">Contacto</a>
                </div>
            </div>
        </footer>

        @php
            $whatsappFloatingEnabled = (bool) ($siteSettings?->whatsapp_floating_enabled ?? true);
            $whatsappLinks = collect($siteSettings?->contactPhones ?? [])
                ->map(function ($phone) {
                    $url = trim((string) ($phone->whatsapp_url ?? ''));
                    if ($url !== '') {
                        return $url;
                    }

                    $digits = preg_replace('/\D+/', '', trim((string) ($phone->phone ?? '')));
                    return $digits !== '' ? 'https://wa.me/'.$digits : '';
                })
                ->filter()
                ->unique()
                ->values()
                ->all();
        @endphp

        @if ($whatsappFloatingEnabled && count($whatsappLinks) > 0)
            <a
                href="{{ $whatsappLinks[0] }}"
                target="_blank"
                rel="noopener"
                data-icon-shift
                class="fixed bottom-28 md:bottom-6 right-6 z-[55] grid size-14 place-items-center rounded-full bg-[#008D62] text-white shadow-lg shadow-black/20 hover:bg-[#008D62]/90"
                aria-label="Abrir WhatsApp"
                x-data="{ links: @js($whatsappLinks) }"
                @click.prevent="
                    if (!links || links.length === 0) return;
                    const idx = Math.floor(Math.random() * links.length);
                    window.open(links[idx], '_blank', 'noopener');
                "
            >
                <svg viewBox="0 0 24 24" class="size-7 al-icon al-icon-up" fill="currentColor" aria-hidden="true">
                    <path d="M12 2a10 10 0 0 0-8.66 15l-1.2 4.38 4.49-1.18A10 10 0 1 0 12 2Zm0 18.18a8.16 8.16 0 0 1-4.15-1.13l-.3-.18-2.67.7.72-2.6-.2-.3a8.18 8.18 0 1 1 6.6 3.5Zm4.47-5.8c-.25-.13-1.48-.73-1.7-.82-.23-.08-.4-.12-.56.13-.17.25-.65.82-.8.98-.14.17-.29.19-.54.07-.25-.12-1.05-.39-2-1.26-.74-.65-1.24-1.45-1.38-1.7-.15-.25-.02-.38.1-.5.1-.1.25-.27.37-.4.12-.12.16-.2.25-.35.08-.17.04-.3-.02-.44-.06-.13-.56-1.35-.77-1.84-.2-.49-.4-.42-.56-.43h-.48c-.16 0-.43.06-.65.3-.22.25-.86.83-.86 2.02s.88 2.35 1 2.5c.13.17 1.73 2.63 4.18 3.7.58.25 1.03.4 1.38.5.58.18 1.1.15 1.51.1.47-.08 1.48-.6 1.69-1.18.2-.58.2-1.08.14-1.18-.06-.1-.23-.17-.48-.3Z"/>
                </svg>
            </a>
        @endif

        <div
            class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm"
            x-show="videoOpen"
            x-transition.opacity
            @click.self="closeVideo()"
        >
            <div class="relative w-full max-w-5xl">
                <div class="relative w-full overflow-hidden rounded-2xl border border-white/10 bg-black">
                    <button type="button" class="absolute right-3 top-3 z-10 grid size-10 place-items-center rounded-xl bg-black/60 text-white hover:bg-black/70" @click="closeVideo()">
                        <i class="fa-solid fa-xmark"></i>
                    </button>

                    <video
                        x-ref="videoPlayer"
                        :src="videoSrc"
                        :poster="videoPoster"
                        class="max-h-[85vh] w-full bg-black object-contain"
                        controls
                        autoplay
                        playsinline
                    ></video>
                </div>
            </div>
        </div>

        <div
            class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm"
            x-show="lightboxOpen"
            x-transition.opacity
            @click.self="closeLightbox()"
        >
            <div class="relative w-full max-w-5xl">
                <div
                    class="relative w-full overflow-hidden rounded-2xl border border-white/10 bg-white group"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                >
                <button type="button" class="absolute right-3 top-3 z-10 grid size-10 place-items-center rounded-xl bg-black/60 text-white hover:bg-black/70" @click="closeLightbox()">
                    <i class="fa-solid fa-xmark"></i>
                </button>

                    <div class="relative">
                        <img
                            :src="lightboxCurrentSrc"
                            alt="Imagen"
                            class="max-h-[85vh] w-full object-contain transition-opacity duration-300 ease-out"
                            :class="lightboxFading ? 'opacity-0' : 'opacity-100'"
                        />

                        <button
                            type="button"
                            data-icon-shift
                            class="absolute left-3 top-1/2 z-10 grid size-12 -translate-y-1/2 place-items-center rounded-full bg-gray-100/50 text-white opacity-0 backdrop-blur-sm transition-opacity duration-200 hover:bg-gray-100/70 group-hover:opacity-100"
                            x-show="lightboxItems.length > 1"
                            @click="prevLightbox()"
                            aria-label="Imagen anterior"
                        >
                            <i class="fa-solid fa-chevron-left al-icon al-icon-left"></i>
                        </button>

                        <button
                            type="button"
                            data-icon-shift
                            class="absolute right-3 top-1/2 z-10 grid size-12 -translate-y-1/2 place-items-center rounded-full bg-gray-100/50 text-white opacity-0 backdrop-blur-sm transition-opacity duration-200 hover:bg-gray-100/70 group-hover:opacity-100"
                            x-show="lightboxItems.length > 1"
                            @click="nextLightbox()"
                            aria-label="Imagen siguiente"
                        >
                            <i class="fa-solid fa-chevron-right al-icon al-icon-right"></i>
                        </button>
                    </div>

                    <div class="flex justify-center px-6 pb-5 pt-3" x-show="lightboxItems.length > 1">
                        <p class="font-mono text-xs font-bold tracking-widest text-[#E98332]" x-text="(lightboxIndex + 1) + ' de ' + lightboxItems.length"></p>
                    </div>
                </div>
            </div>
        </div>

        @fluxScripts
    </body>
</html>
