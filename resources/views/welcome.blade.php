<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        @include('partials.head', ['title' => 'Prefabricados Alesa'])
    </head>
    <body
        class="bg-white text-[#1A1A1A] antialiased"
        x-data="{
            lightboxOpen: false,
            lightboxSrc: null,
            lightboxItems: [],
            lightboxIndex: 0,
            lightboxFading: false,
            galleryItems: @js($galleryImages->map(fn ($image) => asset($image->image_path))->values()),
            mobileMenuOpen: false,
            get lightboxCurrentSrc() { return this.lightboxItems.length ? this.lightboxItems[this.lightboxIndex] : this.lightboxSrc; },
            init() {
                this.onScroll();
                window.addEventListener('scroll', this.onScroll.bind(this), { passive: true });
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
                this.lightboxItems = this.galleryItems;
                this.lightboxIndex = index;
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
                document.body.style.overflow = '';
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
        }"
        @keydown.escape.window="closeLightbox(); mobileMenuOpen = false"
        @keydown.arrow-right.window="lightboxOpen && lightboxItems.length > 1 && nextLightbox()"
        @keydown.arrow-left.window="lightboxOpen && lightboxItems.length > 1 && prevLightbox()"
    >
        <header class="fixed inset-x-0 top-0 z-50">
            <nav x-ref="headerNav" class="w-full bg-white transition-all duration-300 ease-out">
                <div x-ref="headerInner" class="mx-auto flex w-[min(1280px,calc(100%-2rem))] max-w-7xl items-center justify-between py-4 transition-all duration-300 ease-out">
                    <a href="#inicio" class="flex items-center gap-3">
                        <img
                            src="{{ asset('image/logo_transparente.png') }}"
                            alt="Prefabricados Alesa"
                            x-ref="headerLogo"
                            class="!h-12 !w-auto object-contain transition-all duration-300 ease-out md:!h-14"
                        />
                    </a>

                    <div class="hidden items-center gap-8 text-[13px] leading-none font-medium tracking-wide uppercase text-[#1A1A1A] md:flex">
                        <a href="#inicio" class="relative pb-1 transition-colors duration-200 hover:text-[#008D62] after:absolute after:inset-x-0 after:bottom-0 after:h-[2px] after:origin-left after:scale-x-0 after:rounded-full after:bg-[#008D62] after:transition-transform after:duration-200 hover:after:scale-x-100">Inicio</a>
                        <a href="#nosotros" class="relative pb-1 transition-colors duration-200 hover:text-[#008D62] after:absolute after:inset-x-0 after:bottom-0 after:h-[2px] after:origin-left after:scale-x-0 after:rounded-full after:bg-[#008D62] after:transition-transform after:duration-200 hover:after:scale-x-100">Nosotros</a>
                        <a href="#productos" class="relative pb-1 transition-colors duration-200 hover:text-[#008D62] after:absolute after:inset-x-0 after:bottom-0 after:h-[2px] after:origin-left after:scale-x-0 after:rounded-full after:bg-[#008D62] after:transition-transform after:duration-200 hover:after:scale-x-100">Productos</a>
                        <a href="#galeria" class="relative pb-1 transition-colors duration-200 hover:text-[#008D62] after:absolute after:inset-x-0 after:bottom-0 after:h-[2px] after:origin-left after:scale-x-0 after:rounded-full after:bg-[#008D62] after:transition-transform after:duration-200 hover:after:scale-x-100">Galería</a>
                        <a href="#contacto" class="relative pb-1 transition-colors duration-200 hover:text-[#008D62] after:absolute after:inset-x-0 after:bottom-0 after:h-[2px] after:origin-left after:scale-x-0 after:rounded-full after:bg-[#008D62] after:transition-transform after:duration-200 hover:after:scale-x-100">Contacto</a>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex size-10 items-center justify-center rounded-xl border border-black/10 text-[#1A1A1A] hover:bg-black/5 md:hidden"
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            :aria-expanded="mobileMenuOpen.toString()"
                            aria-controls="mobile-menu"
                            aria-label="Abrir menú"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 6h16"></path>
                                <path d="M4 12h16"></path>
                                <path d="M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div
                    id="mobile-menu"
                    class="border-t border-black/10 bg-white md:hidden"
                    x-show="mobileMenuOpen"
                    x-transition
                    @click.outside="mobileMenuOpen = false"
                >
                    <div class="mx-auto flex w-[min(1280px,calc(100%-2rem))] max-w-7xl flex-col gap-3 py-4 text-[13px] leading-none font-medium tracking-wide uppercase text-[#1A1A1A]">
                        <a href="#inicio" class="relative inline-flex py-2 pb-3 transition-colors duration-200 hover:text-[#008D62] after:absolute after:inset-x-0 after:bottom-1 after:h-[2px] after:origin-left after:scale-x-0 after:rounded-full after:bg-[#008D62] after:transition-transform after:duration-200 hover:after:scale-x-100" @click="mobileMenuOpen = false">Inicio</a>
                        <a href="#nosotros" class="relative inline-flex py-2 pb-3 transition-colors duration-200 hover:text-[#008D62] after:absolute after:inset-x-0 after:bottom-1 after:h-[2px] after:origin-left after:scale-x-0 after:rounded-full after:bg-[#008D62] after:transition-transform after:duration-200 hover:after:scale-x-100" @click="mobileMenuOpen = false">Nosotros</a>
                        <a href="#productos" class="relative inline-flex py-2 pb-3 transition-colors duration-200 hover:text-[#008D62] after:absolute after:inset-x-0 after:bottom-1 after:h-[2px] after:origin-left after:scale-x-0 after:rounded-full after:bg-[#008D62] after:transition-transform after:duration-200 hover:after:scale-x-100" @click="mobileMenuOpen = false">Productos</a>
                        <a href="#galeria" class="relative inline-flex py-2 pb-3 transition-colors duration-200 hover:text-[#008D62] after:absolute after:inset-x-0 after:bottom-1 after:h-[2px] after:origin-left after:scale-x-0 after:rounded-full after:bg-[#008D62] after:transition-transform after:duration-200 hover:after:scale-x-100" @click="mobileMenuOpen = false">Galería</a>
                        <a href="#contacto" class="relative inline-flex py-2 pb-3 transition-colors duration-200 hover:text-[#008D62] after:absolute after:inset-x-0 after:bottom-1 after:h-[2px] after:origin-left after:scale-x-0 after:rounded-full after:bg-[#008D62] after:transition-transform after:duration-200 hover:after:scale-x-100" @click="mobileMenuOpen = false">Contacto</a>
                    </div>
                </div>
            </nav>
        </header>

        <section id="inicio" class="relative min-h-[75svh] overflow-hidden sm:min-h-[100svh]">
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

            <div class="relative flex min-h-[75svh] flex-col justify-center p-10 sm:min-h-[100svh] sm:p-16">
                <div class="w-full max-w-2xl md:max-w-[45%] lg:max-w-[40%]">
                    <p data-animate="fade-up" class="inline-flex w-fit items-center gap-2 rounded-full bg-[#008D62] px-4 py-2 text-xs font-bold text-white [text-shadow:0_2px_10px_rgba(0,0,0,0.1)]">
                        <span class="inline-block size-2 rounded-full bg-white/90"></span>
                        Materiales de construcción y renta de maquinaria
                    </p>

                    <h1 data-animate="fade-up" class="mt-6 text-4xl font-black leading-tight tracking-tight text-[#0f172a] [text-shadow:0_2px_10px_rgba(0,0,0,0.1)] md:text-6xl">
                        Construye con
                        <span class="text-orange-600">calidad</span>
                        lo que tu proyecto exige
                    </h1>

                    <p data-animate="fade-up" class="mt-5 text-base font-medium leading-relaxed text-gray-900 [text-shadow:0_2px_10px_rgba(0,0,0,0.1)] md:text-lg">
                        Tecnología alemana y calidad industrial al servicio de la obra en Campeche. Productos de alto rendimiento para resultados que duran.
                    </p>

                    <div data-animate="fade-up" class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a
                            href="#productos"
                            data-icon-shift
                            class="inline-flex w-fit items-center justify-center gap-2 rounded-2xl border border-[#E98332] bg-white px-6 py-3 text-sm font-bold text-[#E98332] shadow-sm hover:bg-orange-50 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-[#E98332]/40 [text-shadow:0_2px_10px_rgba(0,0,0,0.1)]"
                        >
                            <i class="fa-solid fa-layer-group al-icon al-icon-right"></i>
                            Ver productos
                        </a>
                    </div>
                </div>
            </div>

        </section>

        <section id="nosotros" class="relative">
            <div class="mx-auto w-[min(1100px,calc(100%-2rem))] py-20 md:py-28" data-reveal>
                <div class="mb-12 flex items-center justify-center gap-4">
                    <div class="h-px w-14 bg-slate-200"></div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#E98332]/30 bg-[#E98332]/10 px-6 py-2.5 text-sm font-mono font-bold tracking-widest text-[#E98332] uppercase">
                       
                        Nosotros
                    </span>
                    <div class="h-px w-14 bg-slate-200"></div>
                </div>

                <div class="grid gap-10 md:grid-cols-12 md:gap-12">
                    <div class="order-2 md:order-1 md:col-span-6 md:flex md:items-center">
                        <div class="w-full overflow-hidden rounded-2xl border border-black/10 bg-white shadow-sm">
                            <div class="relative aspect-[4/3] w-full bg-white">
                                <img src="{{ asset($about?->image_path ?: 'image/empresa.jpg') }}" alt="Prefabricados Alesa" class="absolute inset-0 h-full w-full object-cover" />
                            </div>
                        </div>
                    </div>
                    <div class="order-1 md:order-2 md:col-span-6">
                        <div class="max-w-2xl">
                            <h2 class="text-3xl font-extrabold tracking-tight text-[#008D62] md:text-4xl">
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

                        <div class="mt-8 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-black/10 bg-white p-6 shadow-sm">
                                <div class="flex items-center gap-3">
                                    <span class="grid size-10 place-items-center rounded-xl bg-[#E98332]/15 text-[#E98332]">
                                        <i class="fa-solid fa-award"></i>
                                    </span>
                                    <p class="text-sm font-semibold">{{ $about?->card_1_title ?? 'Calidad industrial' }}</p>
                                </div>
                                <p class="mt-4 text-sm text-zinc-600">{{ $about?->card_1_body ?? 'Control y consistencia para piezas listas para instalar.' }}</p>
                            </div>
                            <div class="rounded-2xl border border-black/10 bg-white p-6 shadow-sm">
                                <div class="flex items-center gap-3">
                                    <span class="grid size-10 place-items-center rounded-xl bg-[#008D62]/15 text-[#008D62]">
                                        <i class="fa-solid fa-gears"></i>
                                    </span>
                                    <p class="text-sm font-semibold">{{ $about?->card_2_title ?? 'Tecnología Euroblock' }}</p>
                                </div>
                                <p class="mt-4 text-sm text-zinc-600">
                                    @php
                                        $card2Text = $about?->card_2_body ?? 'Tecnología alemana (Euroblock 2005) como base de producción.';
                                        $card2Html = e($card2Text);
                                        $card2Html = preg_replace('/\b(2004|2005|modelo\s+\d{4})\b/iu', '<span class="font-mono font-semibold">$0</span>', $card2Html);
                                    @endphp
                                    {!! $card2Html !!}
                                </p>
                            </div>
                          
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="productos" class="bg-[#F9FAFB]">
            <div class="mx-auto w-[min(1100px,calc(100%-2rem))] py-20 md:py-28">
                <div class="mb-12 flex items-center justify-center gap-4">
                    <div class="h-px w-14 bg-slate-200"></div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#E98332]/30 bg-[#E98332]/10 px-6 py-2.5 text-sm font-mono font-bold tracking-widest text-[#E98332] uppercase">
                       
                        Productos
                    </span>
                    <div class="h-px w-14 bg-slate-200"></div>
                </div>

                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-3xl font-extrabold tracking-tight text-[#008D62] md:text-4xl">Catálogo</h2>
                        <p class="mt-4 max-w-2xl text-sm text-zinc-600 md:text-base">
                            Fabricamos materiales con estándares de ingeniería para mantener una calidad optima. 
                        </p>
                    </div>
                </div>

                @php
                    $quoteBaseMessage = trim((string) ($siteSettings?->whatsapp_message ?? ''));
                    $quoteWhatsappLinks = collect($siteSettings?->contactPhones ?? [])
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
                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3" data-stagger="0.1">
                    @forelse ($products as $product)
                        @php
                            $description = trim((string) $product->description);
                            $rawTechSpecs = is_array($product->tech_specs) ? $product->tech_specs : [];
                            $specs = collect($rawTechSpecs)
                                ->map(fn ($row) => [trim((string) ($row['label'] ?? '')), trim((string) ($row['value'] ?? ''))])
                                ->filter(fn ($row) => filled($row[0]) && filled($row[1]))
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
                                })->filter(fn ($row) => filled($row[0]) && filled($row[1]))->values();

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
                        @php
                            $quoteHref = '';
                            if (! empty($quoteWhatsappLinks)) {
                                $quoteHref = $quoteWhatsappLinks[array_rand($quoteWhatsappLinks)];
                                $quoteText = trim($quoteBaseMessage);
                                $quoteText = $quoteText !== '' ? $quoteText.' ' : '';
                                $quoteText .= 'Cotización: '.trim((string) $product->title);
                                $quoteHref .= (str_contains($quoteHref, '?') ? '&' : '?').'text='.rawurlencode($quoteText);
                            }
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
                                        src="{{ asset($product->image_path) }}"
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

                                <div class="mt-6 grid gap-3 sm:grid-cols-4">
                                    @if ($quoteHref !== '')
                                        <a
                                            href="{{ $quoteHref }}"
                                            target="_blank"
                                            rel="noopener"
                                            data-icon-shift
                                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#008D62] px-4 py-2 text-sm font-semibold text-white transition-all duration-[400ms] ease-out transform-gpu hover:bg-[#007A55] group-hover:-translate-y-1 group-hover:bg-[#007A55]"
                                        >
                                            Solicitar cotización
                                            <i class="fa-solid fa-arrow-right al-icon al-icon-right"></i>
                                        </a>
                                    @endif
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

        <section id="galeria">
            <div class="mx-auto w-[min(1100px,calc(100%-2rem))] py-20 md:py-28" data-reveal>
                <div class="mb-12 flex items-center justify-center gap-4">
                    <div class="h-px w-14 bg-slate-200"></div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#E98332]/30 bg-[#E98332]/10 px-6 py-2.5 text-sm font-mono font-bold tracking-widest text-[#E98332] uppercase">
                        
                        Galería
                    </span>
                    <div class="h-px w-14 bg-slate-200"></div>
                </div>

                <div>
                    <h2 class="text-3xl font-extrabold tracking-tight text-[#008D62] md:text-4xl">Calidad en cada Proyecto</h2>
                    <p class="mt-4 max-w-2xl text-sm text-zinc-600 md:text-base">
                       
                    </p>
                </div>

                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-stagger="0.1">
                    @forelse ($galleryImages as $image)
                        <button
                            type="button"
                            data-stagger-item
                            class="group relative aspect-[4/3] w-full cursor-zoom-in overflow-hidden rounded-2xl border border-black/10 bg-white shadow-sm"
                            @click="openGallery({{ $loop->index }})"
                        >
                            <img
                                src="{{ asset($image->thumb_path ?: $image->image_path) }}"
                                alt="Galería"
                                class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                                loading="lazy"
                            />
                            <span class="pointer-events-none absolute inset-0 bg-linear-to-t from-black/35 via-transparent to-transparent opacity-0 transition group-hover:opacity-100"></span>
                        </button>
                    @empty
                        <div class="col-span-full rounded-2xl border border-black/10 bg-white p-8 text-center shadow-sm">
                            <p class="text-sm text-zinc-600">Aún no hay imágenes en la galería. Entra al panel para subirlas.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="contacto" class="bg-zinc-50">
            <div class="mx-auto w-[min(1100px,calc(100%-2rem))] py-20 md:py-28" data-reveal>
                <div class="mb-12 flex items-center justify-center gap-4">
                    <div class="h-px w-14 bg-slate-200"></div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#E98332]/30 bg-[#E98332]/10 px-6 py-2.5 text-sm font-mono font-bold tracking-widest text-[#E98332] uppercase">
                        
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
                        <h2 class="text-3xl font-extrabold tracking-tight text-[#008D62] md:text-4xl">Hablemos de tu proyecto</h2>
                        <p class="mt-4 text-sm text-zinc-600 md:text-base">
                            Ubicación: {{ trim((string) ($siteSettings?->contact_address ?? '')) !== '' ? $siteSettings->contact_address : 'Libramiento carretera de Campeche a Uayamón KM. 2.6' }}
                        </p>

                        @php
                            $contactEmails = $siteSettings?->contactEmails ?? collect();
                            $contactPhones = $siteSettings?->contactPhones ?? collect();
                        @endphp

                        <form class="mt-8 space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <input type="text" name="name" placeholder="Nombre" class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-black placeholder:text-black/35 focus:border-[#008D62]/70 focus:outline-hidden" />
                                <input type="email" name="email" placeholder="Correo" class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-black placeholder:text-black/35 focus:border-[#008D62]/70 focus:outline-hidden" />
                            </div>
                            <input type="tel" name="phone" placeholder="Teléfono" class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-black placeholder:text-black/35 focus:border-[#008D62]/70 focus:outline-hidden" />
                            <textarea name="message" rows="5" placeholder="Mensaje" class="w-full resize-y rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-black placeholder:text-black/35 focus:border-[#008D62]/70 focus:outline-hidden"></textarea>
                            <button type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#008D62] px-6 py-3 text-sm font-semibold text-white hover:bg-[#008D62]/90">
                                <i class="fa-solid fa-paper-plane"></i>
                                Enviar mensaje
                            </button>
                        </form>
                    </div>

                    <div class="md:col-span-7 max-md:mt-2 md:border-l md:border-black/10 md:pl-10">
                        <h3 class="text-2xl font-extrabold tracking-tight text-slate-900">Directorio</h3>
                        <p class="mt-3 text-sm text-zinc-600">Datos de contacto.</p>

                        <div class="mt-8 space-y-6 text-zinc-700">
                            @if ($contactEmails->isNotEmpty())
                                @foreach ($contactEmails as $email)
                                    @php
                                        $label = trim((string) ($email->label ?? ''));
                                        $label = $label !== '' ? $label : 'Correo';
                                    @endphp
                                    <div>
                                        <p class="text-xs font-bold tracking-widest text-[#E98332] uppercase">{{ $label }}</p>
                                        <a class="mt-1 inline-flex font-mono text-sm text-zinc-900 transition-colors duration-200 ease-out hover:text-[#008D62]" href="mailto:{{ $email->email }}">
                                            {{ $email->email }}
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
                                        <a class="mt-1 block font-mono text-sm text-zinc-900 transition-colors duration-200 ease-out hover:text-[#008D62]" href="tel:{{ $tel }}">{{ $phoneTrimmed }}</a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="border-t border-black/10 py-10">
            <div class="mx-auto flex w-[min(1100px,calc(100%-2rem))] flex-col gap-4 text-sm font-light text-zinc-600 md:flex-row md:items-center md:justify-between">
                <p>© {{ now()->year }} Prefabricados Alesa</p>
                <div class="flex items-center gap-4">
                    <a href="#inicio" class="transition-colors duration-200 hover:text-[#008D62]">Inicio</a>
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
                class="fixed bottom-6 right-6 z-[55] grid size-14 place-items-center rounded-full bg-[#008D62] text-white shadow-lg shadow-black/20 hover:bg-[#008D62]/90"
                aria-label="Abrir WhatsApp"
                x-data="{ links: @js($whatsappLinks) }"
                @click.prevent="
                    if (!links || links.length === 0) return;
                    const idx = Math.floor(Math.random() * links.length);
                    window.open(links[idx], '_blank', 'noopener');
                "
            >
                <i class="fa-brands fa-whatsapp text-2xl al-icon al-icon-up"></i>
            </a>
        @endif

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
