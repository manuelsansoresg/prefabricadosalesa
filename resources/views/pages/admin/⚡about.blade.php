<?php

use App\Models\AboutContent;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Nosotros')] class extends Component {
    use WithFileUploads;

    public string $headline = '';
    public string $mission = '';
    public string $vision = '';
    public string $history = '';

    public string $card_1_title = '';
    public string $card_1_body = '';
    public string $card_2_title = '';
    public string $card_2_body = '';

    public $image = null;
    public ?string $existingImageUrl = null;

    public function mount(): void
    {
        $about = AboutContent::query()->first();

        if (! $about) {
            $this->headline = 'Empresa 100% campechana con tecnología alemana';
            $this->mission = 'Fabricar productos de calidad, en cantidades suficientes y a precios justos; contribuyendo así, al desarrollo de la región.';
            $this->vision = 'Ser la opción más confiable en prefabricados en Campeche, con procesos industriales y tecnología de punta, garantizando calidad constante y entregas oportunas.';
            $this->history = 'Prefabricados Alesa, S.A. de C.V., se fundó en el año de 2002 y fue el 3 de Agosto de 2004 cuando iniciamos operaciones, después de dos años de llevar el proyecto poco a poco y sorteando la difícil situación económica que imperaba. Somos una empresa 100% campechana que tomando lo más alta tecnología disponible, se preocupa por competir primero con calidad; por esta razón se adquirió una máquina bloquera de origen Alemán, marca Euroblock modelo 2005, además de que cuidamos la calidad de la materia prima para la elaboración de nuestros productos.';
            $this->card_1_title = 'Calidad industrial';
            $this->card_1_body = 'Control y consistencia para piezas listas para instalar.';
            $this->card_2_title = 'Tecnología Euroblock';
            $this->card_2_body = 'Tecnología alemana (Euroblock 2005) como base de producción.';

            return;
        }

        $this->headline = (string) ($about->headline ?? 'Empresa 100% campechana con tecnología alemana');
        $this->mission = (string) ($about->mission ?? 'Fabricar productos de calidad, en cantidades suficientes y a precios justos; contribuyendo así, al desarrollo de la región.');
        $this->vision = (string) ($about->vision ?? 'Ser la opción más confiable en prefabricados en Campeche, con procesos industriales y tecnología de punta, garantizando calidad constante y entregas oportunas.');
        $this->history = (string) ($about->history ?? $about->body ?? 'Prefabricados Alesa, S.A. de C.V., se fundó en el año de 2002 y fue el 3 de Agosto de 2004 cuando iniciamos operaciones, después de dos años de llevar el proyecto poco a poco y sorteando la difícil situación económica que imperaba. Somos una empresa 100% campechana que tomando lo más alta tecnología disponible, se preocupa por competir primero con calidad; por esta razón se adquirió una máquina bloquera de origen Alemán, marca Euroblock modelo 2005, además de que cuidamos la calidad de la materia prima para la elaboración de nuestros productos.');
        $this->card_1_title = (string) ($about->card_1_title ?? 'Calidad industrial');
        $this->card_1_body = (string) ($about->card_1_body ?? 'Control y consistencia para piezas listas para instalar.');
        $this->card_2_title = (string) ($about->card_2_title ?? 'Tecnología Euroblock');
        $this->card_2_body = (string) ($about->card_2_body ?? 'Tecnología alemana (Euroblock 2005) como base de producción.');

        $this->existingImageUrl = asset($about->image_path ?: 'image/empresa.jpg');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'headline' => ['required', 'string', 'max:140'],
            'mission' => ['required', 'string', 'max:3000'],
            'vision' => ['required', 'string', 'max:3000'],
            'history' => ['required', 'string', 'max:10000'],
            'card_1_title' => ['required', 'string', 'max:80'],
            'card_1_body' => ['required', 'string', 'max:300'],
            'card_2_title' => ['required', 'string', 'max:80'],
            'card_2_body' => ['required', 'string', 'max:300'],
            'image' => [$this->existingImageUrl ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $about = AboutContent::query()->first() ?? new AboutContent();

        $about->fill([
            'headline' => $validated['headline'],
            'mission' => $validated['mission'],
            'vision' => $validated['vision'],
            'history' => $validated['history'],
            'body' => $validated['history'],
            'card_1_title' => $validated['card_1_title'],
            'card_1_body' => $validated['card_1_body'],
            'card_2_title' => $validated['card_2_title'],
            'card_2_body' => $validated['card_2_body'],
        ]);

        if ($this->image) {
            $oldPublicPath = $about->image_path;

            $directory = public_path('image/about');
            File::ensureDirectoryExists($directory);

            $extension = strtolower($this->image->getClientOriginalExtension() ?: 'jpg');
            $fileName = now()->format('YmdHis').'-'.Str::random(12).'.'.$extension;
            $target = $directory.DIRECTORY_SEPARATOR.$fileName;

            File::put($target, File::get($this->image->getRealPath()));

            $about->image_path = 'image/about/'.$fileName;

            if (filled($oldPublicPath)) {
                File::delete(public_path($oldPublicPath));
            }
        }

        $about->save();

        $this->existingImageUrl = filled($about->image_path) ? asset($about->image_path) : null;
        $this->reset('image');
    }
}; ?>

<div class="min-h-screen bg-[#F8F9FA] px-4 py-8">
    <div class="mx-auto flex w-full max-w-6xl flex-col gap-8">
        <div>
            <div class="text-lg font-bold text-zinc-900">Nosotros</div>
            <div class="mt-1 text-xs font-mono text-zinc-500">Administra el contenido que aparece en la sección “Nosotros” del sitio.</div>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-3xl border border-gray-100 bg-white p-8 shadow-lg">
                    <div class="text-lg font-bold text-zinc-900">Imagen</div>
                    <div class="mt-1 text-xs font-mono text-zinc-500">Sube una imagen para el bloque izquierdo (jpg, png, webp).</div>

                    <div class="mt-6 grid gap-4">
                        <div
                            class="relative flex h-40 flex-col justify-center rounded-2xl border border-dashed border-gray-200 bg-white px-6 text-xs font-mono text-zinc-500 shadow-sm"
                            x-data="{ dragging: false }"
                            x-on:dragover.prevent="dragging = true"
                            x-on:dragleave.prevent="dragging = false"
                            x-on:drop.prevent="
                                dragging = false;
                                $refs.fileInput.files = $event.dataTransfer.files;
                                $refs.fileInput.dispatchEvent(new Event('change'));
                            "
                            :class="dragging ? 'ring-2 ring-[#E98332]/40' : ''"
                        >
                            <div wire:loading.flex wire:target="image" class="absolute inset-0 z-10 rounded-2xl border border-gray-100 bg-white/70">
                                <div class="m-6 h-[calc(100%-3rem)] w-[calc(100%-3rem)] rounded-xl al-skeleton"></div>
                            </div>
                            <input x-ref="fileInput" type="file" class="hidden" wire:model="image" accept="image/jpeg,image/png,image/webp" />

                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div class="flex items-center gap-3">
                                    <flux:icon.arrow-up-tray variant="outline" class="size-6 text-[#E98332]" />
                                    <span>Arrastra y suelta o selecciona un archivo.</span>
                                </div>
                                <button
                                    type="button"
                                    class="inline-flex h-11 items-center justify-center rounded-full border border-[#E98332] bg-white px-5 text-sm font-bold text-[#E98332] shadow-sm hover:bg-orange-50"
                                    x-on:click="$refs.fileInput.click()"
                                >
                                    Elegir imagen
                                </button>
                            </div>
                        </div>

                        @if ($image)
                            <div class="relative">
                                <div wire:loading.flex wire:target="image" class="absolute inset-0 z-10 rounded-2xl border border-gray-100 bg-white/70">
                                    <div class="m-6 h-[calc(100%-3rem)] w-[calc(100%-3rem)] rounded-xl al-skeleton"></div>
                                </div>
                                <img src="{{ $image->temporaryUrl() }}" alt="Vista previa" class="h-56 w-full rounded-2xl border border-gray-100 object-cover" />
                            </div>
                        @elseif ($existingImageUrl)
                            <div class="relative">
                                <div wire:loading.flex wire:target="image" class="absolute inset-0 z-10 rounded-2xl border border-gray-100 bg-white/70">
                                    <div class="m-6 h-[calc(100%-3rem)] w-[calc(100%-3rem)] rounded-xl al-skeleton"></div>
                                </div>
                                <img src="{{ $existingImageUrl }}" alt="Imagen actual" class="h-56 w-full rounded-2xl border border-gray-100 object-cover" />
                            </div>
                        @endif
                    </div>
                </div>

                <div class="rounded-3xl border border-gray-100 bg-white p-8 shadow-lg">
                    <div class="text-lg font-bold text-zinc-900">Encabezado</div>
                    <div class="mt-1 text-xs font-mono text-zinc-500">Título principal (H2) del bloque derecho.</div>

                    <div class="mt-6 space-y-2">
                        <label class="text-sm text-zinc-700" for="headline">Título</label>
                        <input
                            id="headline"
                            type="text"
                            wire:model="headline"
                            required
                            class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                        />
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-gray-100 bg-white p-8 shadow-lg">
                <div class="text-lg font-bold text-zinc-900">Textos</div>
                <div class="mt-1 text-xs font-mono text-zinc-500">Administra Misión, Visión e Historia.</div>

                <div class="mt-6 grid gap-6 lg:grid-cols-2">
                    <div class="grid gap-6">
                        <div class="space-y-2">
                            <label class="text-sm text-zinc-700" for="mission">Misión</label>
                            <textarea
                                id="mission"
                                wire:model="mission"
                                required
                                class="h-32 w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                            ></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm text-zinc-700" for="vision">Visión</label>
                            <textarea
                                id="vision"
                                wire:model="vision"
                                required
                                class="h-32 w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                            ></textarea>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm text-zinc-700" for="history">Historia</label>
                        <textarea
                            id="history"
                            wire:model="history"
                            required
                            class="min-h-32 w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                            rows="11"
                        ></textarea>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-gray-100 bg-white p-8 shadow-lg">
                <div class="text-lg font-bold text-zinc-900">Tarjetas</div>
                <div class="mt-1 text-xs font-mono text-zinc-500">Administra título y descripción de cada tarjeta.</div>

                <div class="mt-6 grid gap-6 lg:grid-cols-2">
                    <div class="grid gap-4 rounded-2xl border border-gray-100 bg-gray-50 p-6">
                        <div class="space-y-2">
                            <label class="text-sm text-zinc-700" for="card1Title">Tarjeta 1 - Título</label>
                            <input
                                id="card1Title"
                                type="text"
                                wire:model="card_1_title"
                                required
                                class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                            />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm text-zinc-700" for="card1Body">Tarjeta 1 - Texto</label>
                            <textarea
                                id="card1Body"
                                wire:model="card_1_body"
                                required
                                class="min-h-32 w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                                rows="3"
                            ></textarea>
                        </div>
                    </div>
                    <div class="grid gap-4 rounded-2xl border border-gray-100 bg-gray-50 p-6">
                        <div class="space-y-2">
                            <label class="text-sm text-zinc-700" for="card2Title">Tarjeta 2 - Título</label>
                            <input
                                id="card2Title"
                                type="text"
                                wire:model="card_2_title"
                                required
                                class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                            />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm text-zinc-700" for="card2Body">Tarjeta 2 - Texto</label>
                            <textarea
                                id="card2Body"
                                wire:model="card_2_body"
                                required
                                class="min-h-32 w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                                rows="3"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-[#008D62] px-6 text-sm font-bold text-white shadow-sm hover:bg-[#007A55]">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
