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
            heroMuted: true,
            heroPlaying: false,
            openLightbox(src) { this.lightboxSrc = src; this.lightboxOpen = true; document.body.style.overflow = 'hidden'; },
            closeLightbox() { this.lightboxOpen = false; this.lightboxSrc = null; document.body.style.overflow = ''; },
            toggleHeroMute() {
                const el = this.$refs.heroVideo;
                if (!el) return;
                el.muted = !el.muted;
                this.heroMuted = el.muted;
            },
            toggleHeroPlay() {
                const el = this.$refs.heroVideo;
                if (!el) return;
                if (el.paused) {
                    el.play();
                    this.heroPlaying = true;
                    return;
                }
                el.pause();
                this.heroPlaying = false;
            },
        }"
        x-init="
            if ($refs.heroVideo) {
                $refs.heroVideo.pause();
                $refs.heroVideo.muted = true;
                heroMuted = true;
                heroPlaying = false;
            }

            if (window.gsap) {
                gsap.from('[data-animate=\"fade-up\"]', { y: 18, opacity: 0, duration: 0.9, stagger: 0.08, ease: 'power2.out' });
            }
        "
        @keydown.escape.window="closeLightbox()"
    >
        <header class="fixed inset-x-0 top-4 z-50">
            <nav class="mx-auto flex w-[min(1100px,calc(100%-2rem))] items-center justify-between rounded-2xl border border-black/10 bg-white/70 px-4 py-3 shadow-sm backdrop-blur-xl">
                <a href="#inicio" class="flex items-center gap-3">
                    <span class="grid h-9 w-12 place-items-center overflow-hidden rounded-xl">
                        <img src="{{ asset('image/logo_transparente.png') }}" alt="Prefabricados Alesa" class="h-9 w-auto object-contain" />
                    </span>
                    <span class="text-sm font-semibold tracking-wide">Prefabricados Alesa</span>
                </a>

                <div class="hidden items-center gap-6 text-sm text-black/70 md:flex">
                    <a href="#inicio" class="hover:text-black">Inicio</a>
                    <a href="#nosotros" class="hover:text-black">Nosotros</a>
                    <a href="#productos" class="hover:text-black">Productos</a>
                    <a href="#galeria" class="hover:text-black">Galería</a>
                    <a href="#contacto" class="hover:text-black">Contacto</a>
                </div>

                <div class="flex items-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="hidden rounded-xl bg-black/5 px-4 py-2 text-sm font-medium text-black hover:bg-black/10 md:inline-flex" wire:navigate>
                            Panel
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="hidden rounded-xl bg-black/5 px-4 py-2 text-sm font-medium text-black hover:bg-black/10 md:inline-flex" wire:navigate>
                                Acceso
                            </a>
                        @endif
                    @endauth

                    <a href="#contacto" class="inline-flex items-center gap-2 rounded-xl bg-[#008D62] px-4 py-2 text-sm font-semibold text-white hover:bg-[#008D62]/90">
                        <span>Cotizar</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </nav>
        </header>

        <section id="inicio" class="relative min-h-[100svh] overflow-hidden">
            <div class="absolute inset-0">
                <video
                    x-ref="heroVideo"
                    class="h-full w-full object-cover opacity-90"
                    preload="metadata"
                    playsinline
                    muted
                >
                    <source src="{{ asset($siteSettings?->hero_video_path ?: 'videos/hero.mp4') }}" type="video/mp4" />
                </video>
                <div class="absolute inset-0 bg-linear-to-b from-[#1A1A1A]/50 via-[#1A1A1A]/50 to-[#1A1A1A]"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(233,131,50,0.25),transparent_40%),radial-gradient(circle_at_80%_35%,rgba(0,141,98,0.25),transparent_45%)]"></div>
            </div>

            <div class="relative mx-auto flex w-[min(1100px,calc(100%-2rem))] flex-col justify-end pb-18 pt-36 md:pb-20">
                <div class="max-w-2xl">
                    <p data-animate="fade-up" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-medium text-white/80">
                        <span class="inline-block size-2 rounded-full bg-[#008D62]"></span>
                        Materiales de construcción y renta de maquinaria
                    </p>

                    <h1 data-animate="fade-up" class="mt-6 text-4xl font-semibold leading-tight tracking-tight md:text-6xl">
                        Prefabricados industriales con
                        <span class="text-[#E98332]">calidad</span>
                        y
                        <span class="text-[#008D62]">precisión</span>
                    </h1>

                    <p data-animate="fade-up" class="mt-5 text-base leading-relaxed text-white/75 md:text-lg">
                        Soluciones confiables para obra: productos prefabricados, materiales y maquinaria con atención profesional.
                    </p>

                    <div data-animate="fade-up" class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="#productos" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#E98332] px-6 py-3 text-sm font-semibold text-white hover:bg-[#E98332]/90">
                            <i class="fa-solid fa-layer-group"></i>
                            Ver productos
                        </a>
                        <button type="button" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/5 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10" @click="toggleHeroPlay()">
                            <span x-show="!heroPlaying"><i class="fa-solid fa-play"></i> Reproducir video</span>
                            <span x-show="heroPlaying"><i class="fa-solid fa-pause"></i> Pausar video</span>
                        </button>
                    </div>
                </div>
            </div>

            <button
                type="button"
                class="absolute bottom-6 right-6 z-10 grid size-12 place-items-center rounded-2xl border border-white/10 bg-white/5 text-white backdrop-blur-xl hover:bg-white/10"
                @click="toggleHeroMute()"
                aria-label="Mutear o desmutear video"
            >
                <i class="fa-solid" :class="heroMuted ? 'fa-volume-xmark' : 'fa-volume-high'"></i>
            </button>
        </section>

        <section id="nosotros" class="relative">
            <div class="mx-auto w-[min(1100px,calc(100%-2rem))] py-20 md:py-28">
                <div class="mb-12 flex items-center justify-center gap-4">
                    <div class="h-px w-14 bg-slate-200"></div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#E98332]/30 bg-[#E98332]/10 px-5 py-2 text-xs font-extrabold tracking-[0.32em] text-[#E98332] uppercase">
                       
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
                            <h2 class="text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">
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
                                    <p class="text-sm font-extrabold tracking-widest text-slate-900 uppercase">Historia</p>
                                    <p class="mt-3 text-sm leading-relaxed text-zinc-600 md:text-base">
                                        {{ $about?->history ?? $about?->body ?? 'Prefabricados Alesa, S.A. de C.V., se fundó en el año de 2002 y fue el 3 de Agosto de 2004 cuando iniciamos operaciones, después de dos años de llevar el proyecto poco a poco y sorteando la difícil situación económica que imperaba. Somos una empresa 100% campechana que tomando lo más alta tecnología disponible, se preocupa por competir primero con calidad; por esta razón se adquirió una máquina bloquera de origen Alemán, marca Euroblock modelo 2005, además de que cuidamos la calidad de la materia prima para la elaboración de nuestros productos.' }}
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
                                <p class="mt-4 text-sm text-zinc-600">{{ $about?->card_2_body ?? 'Tecnología alemana (Euroblock 2005) como base de producción.' }}</p>
                            </div>
                          
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="productos" class="bg-zinc-50">
            <div class="mx-auto w-[min(1100px,calc(100%-2rem))] py-20 md:py-28">
                <div class="mb-12 flex items-center justify-center gap-4">
                    <div class="h-px w-14 bg-slate-200"></div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#E98332]/30 bg-[#E98332]/10 px-5 py-2 text-xs font-extrabold tracking-[0.32em] text-[#E98332] uppercase">
                       
                        Productos
                    </span>
                    <div class="h-px w-14 bg-slate-200"></div>
                </div>

                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">Catálogo</h2>
                        <p class="mt-4 max-w-2xl text-sm text-zinc-600 md:text-base">
                            Grid dinámico con detalles. Pasa el cursor para ver el efecto.
                        </p>
                    </div>
                </div>

                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($products as $product)
                        <article class="group overflow-hidden rounded-2xl border border-black/10 bg-white shadow-sm">
                            <div class="relative aspect-[16/11] overflow-hidden">
                                <img
                                    src="{{ asset($product->image_path) }}"
                                    alt="{{ $product->title }}"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.05]"
                                    loading="lazy"
                                />
                                <div class="absolute inset-0 bg-linear-to-t from-black/55 via-transparent to-transparent opacity-90"></div>
                            </div>
                            <div class="p-6">
                                <h3 class="text-base font-semibold">{{ $product->title }}</h3>
                                <p class="mt-2 text-sm text-zinc-600">{{ $product->description }}</p>
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
            <div class="mx-auto w-[min(1100px,calc(100%-2rem))] py-20 md:py-28">
                <div class="mb-12 flex items-center justify-center gap-4">
                    <div class="h-px w-14 bg-slate-200"></div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#E98332]/30 bg-[#E98332]/10 px-5 py-2 text-xs font-extrabold tracking-[0.32em] text-[#E98332] uppercase">
                        
                        Galería
                    </span>
                    <div class="h-px w-14 bg-slate-200"></div>
                </div>

                <div>
                    <h2 class="text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">Obra y producción</h2>
                    <p class="mt-4 max-w-2xl text-sm text-zinc-600 md:text-base">
                        Layout tipo masonry con lightbox.
                    </p>
                </div>

                <div class="mt-10 columns-2 gap-4 space-y-4 sm:columns-3">
                    @forelse ($galleryImages as $image)
                        <button
                            type="button"
                            class="group relative block w-full overflow-hidden rounded-2xl border border-black/10 bg-white shadow-sm"
                            @click="openLightbox('{{ asset($image->image_path) }}')"
                        >
                            <img
                                src="{{ asset($image->image_path) }}"
                                alt="Galería"
                                class="h-auto w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                                loading="lazy"
                            />
                            <span class="pointer-events-none absolute inset-0 bg-linear-to-t from-black/35 via-transparent to-transparent opacity-0 transition group-hover:opacity-100"></span>
                        </button>
                    @empty
                        <div class="rounded-2xl border border-black/10 bg-white p-8 text-center shadow-sm">
                            <p class="text-sm text-zinc-600">Aún no hay imágenes en la galería. Entra al panel para subirlas.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="contacto" class="bg-zinc-50">
            <div class="mx-auto w-[min(1100px,calc(100%-2rem))] py-20 md:py-28">
                <div class="mb-12 flex items-center justify-center gap-4">
                    <div class="h-px w-14 bg-slate-200"></div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#E98332]/30 bg-[#E98332]/10 px-5 py-2 text-xs font-extrabold tracking-[0.32em] text-[#E98332] uppercase">
                        
                        Contacto
                    </span>
                    <div class="h-px w-14 bg-slate-200"></div>
                </div>

                <div class="grid gap-10 md:grid-cols-12 md:gap-12">
                    <div class="md:col-span-5">
                        <h2 class="text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">Hablemos de tu proyecto</h2>
                        <p class="mt-4 text-sm text-zinc-600 md:text-base">
                            Ubicación: Libramiento carretera de Campeche a Uayamón KM. 2.6
                        </p>

                        @php
                            $contactEmails = $siteSettings?->contactEmails ?? collect();
                            $contactPhones = $siteSettings?->contactPhones ?? collect();
                        @endphp

                        @if ($contactEmails->isNotEmpty() || $contactPhones->isNotEmpty())
                            <div class="mt-6 grid gap-4 rounded-2xl border border-black/10 bg-white p-5 shadow-sm">
                                @if ($contactEmails->isNotEmpty())
                                    <div>
                                        <p class="text-xs font-semibold tracking-[0.24em] text-[#E98332]">CORREOS</p>
                                        <div class="mt-3 grid gap-2 text-sm text-zinc-700">
                                            @foreach ($contactEmails as $email)
                                                <a class="inline-flex items-center gap-2 hover:text-[#008D62]" href="mailto:{{ $email->email }}">
                                                    <i class="fa-solid fa-envelope"></i>
                                                    <span>{{ $email->email }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if ($contactPhones->isNotEmpty())
                                    <div>
                                        <p class="text-xs font-semibold tracking-[0.24em] text-[#008D62]">TELÉFONOS</p>
                                        <div class="mt-3 grid gap-2 text-sm text-zinc-700">
                                            @foreach ($contactPhones as $phone)
                                                @php
                                                    $phoneTrimmed = trim((string) $phone->phone);
                                                    $tel = preg_replace('/\s+/', '', $phoneTrimmed);
                                                    $whatsappUrl = trim((string) ($phone->whatsapp_url ?? ''));
                                                @endphp
                                                <div class="flex flex-wrap items-center gap-3">
                                                    <a class="inline-flex items-center gap-2 hover:text-[#008D62]" href="tel:{{ $tel }}">
                                                        <i class="fa-solid fa-phone"></i>
                                                        <span>{{ $phoneTrimmed }}</span>
                                                    </a>
                                                    @if ($whatsappUrl !== '')
                                                        <a class="inline-flex items-center gap-2 text-[#008D62] hover:underline" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">
                                                            <i class="fa-brands fa-whatsapp"></i>
                                                            <span>WhatsApp</span>
                                                        </a>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <form class="mt-8 space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <input type="text" name="name" placeholder="Nombre" class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-black placeholder:text-black/35 focus:border-[#008D62]/70 focus:outline-hidden" />
                                <input type="email" name="email" placeholder="Correo" class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-black placeholder:text-black/35 focus:border-[#008D62]/70 focus:outline-hidden" />
                            </div>
                            <textarea name="message" rows="5" placeholder="Mensaje" class="w-full resize-y rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-black placeholder:text-black/35 focus:border-[#008D62]/70 focus:outline-hidden"></textarea>
                            <button type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#008D62] px-6 py-3 text-sm font-semibold text-white hover:bg-[#008D62]/90">
                                <i class="fa-solid fa-paper-plane"></i>
                                Enviar mensaje
                            </button>
                        </form>
                    </div>

                    <div class="md:col-span-7">
                        <div class="overflow-hidden rounded-2xl border border-black/10 bg-white shadow-sm">
                            <iframe
                                title="Mapa Prefabricados Alesa"
                                class="h-[420px] w-full"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                src="https://www.google.com/maps?q=Libramiento%20carretera%20de%20Campeche%20a%20Uayam%C3%B3n%20KM.%202.6&output=embed"
                            ></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="border-t border-black/10 py-10">
            <div class="mx-auto flex w-[min(1100px,calc(100%-2rem))] flex-col gap-4 text-sm text-zinc-600 md:flex-row md:items-center md:justify-between">
                <p>© {{ now()->year }} Prefabricados Alesa</p>
                <div class="flex items-center gap-4">
                    <a href="#inicio" class="hover:text-black">Inicio</a>
                    <a href="#contacto" class="hover:text-black">Contacto</a>
                </div>
            </div>
        </footer>

        <div
            class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 p-4"
            x-show="lightboxOpen"
            x-transition.opacity
            @click.self="closeLightbox()"
        >
            <div class="relative w-full max-w-5xl overflow-hidden rounded-2xl border border-white/10 bg-white">
                <button type="button" class="absolute right-3 top-3 z-10 grid size-10 place-items-center rounded-xl bg-black/60 text-white hover:bg-black/70" @click="closeLightbox()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <img :src="lightboxSrc" alt="Imagen" class="max-h-[80svh] w-full object-contain" />
            </div>
        </div>

        @fluxScripts
    </body>
</html>
