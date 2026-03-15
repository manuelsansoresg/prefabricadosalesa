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
            $this->history = 'Prefabricados Alesa, S.A. de C.V., se fundó en el año de 2002 y fue el 3 de Agosto de 2004 cuando iniciamos operaciones, después de dos años de llevar el proyecto poco a poco y sorteando la difícil situación económica que imperaba. Somos una empresa 100% campechana que tomando lo más alta tecnología disponible, se preocupa por competir primero con calidad; por esta razón se adquirió una máquina bloquera de origen Alemán, marca Euroblock modelo 2005, además de que cuidamos la calidad de la materia prima para la elaboración de nuestros productos.';
            $this->card_1_title = 'Calidad industrial';
            $this->card_1_body = 'Control y consistencia para piezas listas para instalar.';
            $this->card_2_title = 'Tecnología Euroblock';
            $this->card_2_body = 'Tecnología alemana (Euroblock 2005) como base de producción.';

            return;
        }

        $this->headline = (string) ($about->headline ?? 'Empresa 100% campechana con tecnología alemana');
        $this->mission = (string) ($about->mission ?? 'Fabricar productos de calidad, en cantidades suficientes y a precios justos; contribuyendo así, al desarrollo de la región.');
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

<div class="flex flex-col gap-6">
    <div>
        <flux:heading>Nosotros</flux:heading>
        <flux:subheading>Administra el contenido que aparece en la sección “Nosotros” del sitio.</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-zinc-200 bg-white p-6">
                <flux:heading size="sm">Imagen</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-600">Sube una imagen para el bloque izquierdo (jpg, png, webp).</flux:text>

                <div class="mt-4 grid gap-3">
                    <div
                        class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-4 text-sm text-zinc-600"
                        x-data="{ dragging: false }"
                        @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="
                            dragging = false;
                            $refs.fileInput.files = $event.dataTransfer.files;
                            $refs.fileInput.dispatchEvent(new Event('change'));
                        "
                        :class="dragging ? 'ring-2 ring-[color:var(--color-accent)]' : ''"
                    >
                        <input x-ref="fileInput" type="file" class="hidden" wire:model="image" accept="image/jpeg,image/png,image/webp" />

                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Arrastra y suelta o selecciona un archivo.</span>
                            </div>
                            <button type="button" class="rounded-lg bg-white px-3 py-2 font-medium text-zinc-900 shadow-sm hover:bg-zinc-50" @click="$refs.fileInput.click()">
                                Elegir imagen
                            </button>
                        </div>
                    </div>

                    @if ($image)
                        <img src="{{ $image->temporaryUrl() }}" alt="Vista previa" class="h-56 w-full rounded-xl object-cover" />
                    @elseif ($existingImageUrl)
                        <img src="{{ $existingImageUrl }}" alt="Imagen actual" class="h-56 w-full rounded-xl object-cover" />
                    @endif
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6">
                <flux:heading size="sm">Encabezado</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-600">Título principal (H2) del bloque derecho.</flux:text>
                <div class="mt-4">
                    <flux:input wire:model="headline" label="Título" type="text" required />
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6">
            <flux:heading size="sm">Textos</flux:heading>
            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                <flux:textarea wire:model="mission" label="Misión" rows="5" required />
                <flux:textarea wire:model="history" label="Historia" rows="5" required />
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6">
            <flux:heading size="sm">Tarjetas</flux:heading>
            <flux:text class="mt-1 text-sm text-zinc-600">Administra título y descripción de cada tarjeta.</flux:text>

            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                <div class="grid gap-3 rounded-xl border border-zinc-200 bg-white p-4">
                    <flux:input wire:model="card_1_title" label="Tarjeta 1 - Título" type="text" required />
                    <flux:textarea wire:model="card_1_body" label="Tarjeta 1 - Texto" rows="3" required />
                </div>
                <div class="grid gap-3 rounded-xl border border-zinc-200 bg-white p-4">
                    <flux:input wire:model="card_2_title" label="Tarjeta 2 - Título" type="text" required />
                    <flux:textarea wire:model="card_2_body" label="Tarjeta 2 - Texto" rows="3" required />
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <flux:button variant="primary" type="submit">Guardar</flux:button>
        </div>
    </form>
</div>
