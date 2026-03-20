<?php

use App\Models\Product;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Productos')] class extends Component {
    use WithFileUploads;

    public bool $showEditor = false;
    public ?int $editingId = null;

    public string $title = '';
    public string $unit = '';
    public string $description = '';
    public array $techSpecs = [];
    public $image = null;
    public $datasheet = null;

    public ?string $existingImageUrl = null;
    public ?string $existingDatasheetUrl = null;

    public function create(): void
    {
        $this->reset(['editingId', 'title', 'unit', 'description', 'techSpecs', 'image', 'datasheet', 'existingImageUrl', 'existingDatasheetUrl']);
        $this->techSpecs = [['label' => '', 'value' => '']];
        $this->showEditor = true;
    }

    public function edit(int $id): void
    {
        $product = Product::query()->findOrFail($id);

        $this->editingId = $product->id;
        $this->title = $product->title;
        $this->unit = (string) ($product->unit ?? '');
        $this->description = $product->description;
        $this->techSpecs = is_array($product->tech_specs) ? array_values($product->tech_specs) : [];
        if ($this->techSpecs === []) {
            $this->techSpecs = [['label' => '', 'value' => '']];
        }
        $this->existingImageUrl = asset($product->image_path);
        $this->existingDatasheetUrl = filled($product->datasheet_path) ? asset($product->datasheet_path) : null;
        $this->image = null;
        $this->datasheet = null;
        $this->showEditor = true;
    }

    public function addTechSpecRow(): void
    {
        $this->techSpecs[] = ['label' => '', 'value' => ''];
    }

    public function removeTechSpecRow(int $index): void
    {
        if (! isset($this->techSpecs[$index])) {
            return;
        }

        unset($this->techSpecs[$index]);
        $this->techSpecs = array_values($this->techSpecs);

        if ($this->techSpecs === []) {
            $this->techSpecs = [['label' => '', 'value' => '']];
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:120'],
            'unit' => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:2000'],
            'techSpecs' => ['array', 'max:50'],
            'techSpecs.*.label' => ['nullable', 'string', 'max:80'],
            'techSpecs.*.value' => ['nullable', 'string', 'max:200'],
            'image' => [$this->editingId ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'datasheet' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $product = $this->editingId
            ? Product::query()->findOrFail($this->editingId)
            : new Product();

        $techSpecs = collect($validated['techSpecs'] ?? [])
            ->map(function ($row) {
                return [
                    'label' => trim((string) ($row['label'] ?? '')),
                    'value' => trim((string) ($row['value'] ?? '')),
                ];
            })
            ->filter(fn ($row) => filled($row['label']) && filled($row['value']))
            ->values()
            ->all();

        $product->fill([
            'title' => $validated['title'],
            'unit' => trim((string) ($validated['unit'] ?? '')) !== '' ? trim((string) $validated['unit']) : null,
            'description' => trim((string) ($validated['description'] ?? '')),
            'tech_specs' => $techSpecs === [] ? null : $techSpecs,
        ]);

        if ($this->image) {
            $oldPublicPath = $product->image_path;

            $directory = public_path('image/products');
            File::ensureDirectoryExists($directory);

            $extension = strtolower($this->image->getClientOriginalExtension() ?: 'jpg');
            $fileName = now()->format('YmdHis').'-'.Str::random(12).'.'.$extension;
            $target = $directory.DIRECTORY_SEPARATOR.$fileName;

            File::copy($this->image->getRealPath(), $target);

            $product->image_path = 'image/products/'.$fileName;

            if (filled($oldPublicPath)) {
                File::delete(public_path($oldPublicPath));
            }
        }

        if ($this->datasheet) {
            $oldPublicPath = $product->datasheet_path;

            $directory = public_path('files/products');
            File::ensureDirectoryExists($directory);

            $extension = strtolower($this->datasheet->getClientOriginalExtension() ?: 'pdf');
            $fileName = now()->format('YmdHis').'-'.Str::random(12).'.'.$extension;
            $target = $directory.DIRECTORY_SEPARATOR.$fileName;

            File::copy($this->datasheet->getRealPath(), $target);

            $product->datasheet_path = 'files/products/'.$fileName;

            if (filled($oldPublicPath)) {
                File::delete(public_path($oldPublicPath));
            }
        }

        $product->save();

        $this->reset(['showEditor', 'editingId', 'title', 'unit', 'description', 'techSpecs', 'image', 'datasheet', 'existingImageUrl', 'existingDatasheetUrl']);
    }

    public function deleteDatasheet(): void
    {
        if (! $this->editingId) {
            return;
        }

        $product = Product::query()->findOrFail($this->editingId);
        $datasheetPublicPath = (string) ($product->datasheet_path ?? '');

        $product->update([
            'datasheet_path' => null,
        ]);

        if (filled($datasheetPublicPath)) {
            File::delete(public_path($datasheetPublicPath));
        }

        $this->existingDatasheetUrl = null;
        $this->datasheet = null;
    }

    public function delete(int $id): void
    {
        $product = Product::query()->findOrFail($id);
        $publicPath = $product->image_path;
        $datasheetPublicPath = $product->datasheet_path;

        $product->delete();

        if (filled($publicPath)) {
            File::delete(public_path($publicPath));
        }

        if (filled($datasheetPublicPath)) {
            File::delete(public_path($datasheetPublicPath));
        }
    }

    public function getProductsProperty()
    {
        return Product::query()->latest()->get();
    }
}; ?>

<div class="min-h-screen bg-[#F8F9FA] px-4 py-8">
    <div class="mx-auto flex w-full max-w-6xl flex-col gap-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <div class="text-lg font-bold text-zinc-900">Productos</div>
                <div class="mt-1 text-xs font-mono text-zinc-500">Administra el catálogo: imagen, título y descripción.</div>
            </div>
            <button type="button" wire:click="create" class="inline-flex h-11 items-center justify-center rounded-full bg-[#008D62] px-6 text-sm font-bold text-white shadow-sm hover:bg-[#007A55]">
                Nuevo producto
            </button>
        </div>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($this->products as $product)
                <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-lg">
                    <div class="relative aspect-[16/10] overflow-hidden bg-gray-50">
                        <img src="{{ asset($product->image_path) }}" alt="{{ $product->title }}" class="h-full w-full object-cover" />
                    </div>
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="truncate text-sm font-bold text-zinc-900">{{ $product->title }}</div>
                                <div class="mt-1 text-xs font-mono text-zinc-500">{{ $product->description }}</div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <button type="button" wire:click="edit({{ $product->id }})" class="inline-flex h-9 items-center justify-center rounded-full border border-[#E98332] bg-white px-4 text-sm font-bold text-[#E98332] shadow-sm hover:bg-orange-50">
                                    Editar
                                </button>
                                <button type="button" wire:click="delete({{ $product->id }})" class="inline-flex items-center justify-center rounded-full p-2 text-red-600 hover:bg-red-50" aria-label="Eliminar">
                                    <flux:icon.trash variant="outline" class="size-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-3xl border border-gray-100 bg-white p-10 text-center shadow-lg md:col-span-2 xl:col-span-3">
                    <div class="text-xs font-mono text-zinc-500">No hay productos todavía.</div>
                </div>
            @endforelse
        </div>

        <flux:modal name="product-editor" wire:model="showEditor" focusable class="max-w-2xl">
            <form wire:submit.prevent="save" class="space-y-6">
                <div>
                    <div class="text-lg font-bold text-zinc-900">{{ $editingId ? 'Editar producto' : 'Nuevo producto' }}</div>
                    <div class="mt-1 text-xs font-mono text-zinc-500">Configura unidad, ficha técnica y especificaciones del producto.</div>
                </div>

                <div class="grid gap-6">
                    <div class="grid gap-4">
                        <div class="space-y-2">
                            <label class="text-sm text-zinc-700" for="productTitle">Título</label>
                            <input id="productTitle" type="text" wire:model="title" required class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm text-zinc-700" for="productUnit">Unidad (opcional)</label>
                            <input id="productUnit" type="text" wire:model="unit" class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm text-zinc-700" for="productDesc">Descripción (opcional)</label>
                            <textarea id="productDesc" wire:model="description" rows="5" class="min-h-32 w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"></textarea>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-lg font-bold text-zinc-900">Atributos dinámicos</div>
                            <button type="button" wire:click="addTechSpecRow" class="inline-flex h-10 items-center justify-center rounded-full border border-[#E98332] bg-white px-4 text-sm font-bold text-[#E98332] shadow-sm hover:bg-orange-50">
                                Agregar fila
                            </button>
                        </div>
                        <div class="mt-1 text-xs font-mono text-zinc-500">Agrega características técnicas (ej. Resistencia | 200kg/cm²).</div>

                        <div class="mt-6 grid gap-3">
                            @foreach ($techSpecs as $index => $row)
                                <div class="flex flex-col gap-3 rounded-2xl border border-gray-100 bg-gray-50 p-4 lg:flex-row lg:items-end">
                                    <div class="flex items-center gap-3">
                                        <flux:icon.bars-2 variant="outline" class="size-5 text-zinc-400" />
                                    </div>
                                    <div class="flex-1 space-y-2">
                                        <label class="text-sm text-zinc-700" for="techLabel{{ $index }}">Nombre</label>
                                        <input id="techLabel{{ $index }}" type="text" wire:model="techSpecs.{{ $index }}.label" class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden" />
                                    </div>
                                    <div class="flex-1 space-y-2">
                                        <label class="text-sm text-zinc-700" for="techValue{{ $index }}">Valor</label>
                                        <input id="techValue{{ $index }}" type="text" wire:model="techSpecs.{{ $index }}.value" class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden" />
                                    </div>
                                    <button type="button" wire:click="removeTechSpecRow({{ $index }})" class="ml-auto inline-flex items-center justify-center rounded-full p-2 text-red-600 hover:bg-red-50" aria-label="Quitar">
                                        <flux:icon.trash variant="outline" class="size-5" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid gap-4">
                        <div class="text-lg font-bold text-zinc-900">Imagen</div>
                        <div class="text-xs font-mono text-zinc-500">Sube una imagen (jpg, png, webp).</div>
                        <div
                            class="flex h-40 flex-col justify-center rounded-2xl border border-dashed border-gray-200 bg-white px-6 text-xs font-mono text-zinc-500 shadow-sm"
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
                            <input x-ref="fileInput" type="file" class="hidden" wire:model="image" accept="image/jpeg,image/png,image/webp" />
                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div class="flex items-center gap-3">
                                    <flux:icon.arrow-up-tray variant="outline" class="size-6 text-[#E98332]" />
                                    <span>Arrastra y suelta o selecciona un archivo.</span>
                                </div>
                                <button type="button" class="inline-flex h-11 items-center justify-center rounded-full border border-[#E98332] bg-white px-5 text-sm font-bold text-[#E98332] shadow-sm hover:bg-orange-50" x-on:click="$refs.fileInput.click()">
                                    Elegir imagen
                                </button>
                            </div>
                        </div>

                        @if ($image)
                            <img src="{{ $image->temporaryUrl() }}" alt="Vista previa" class="h-56 w-full rounded-2xl border border-gray-100 object-cover" />
                        @elseif ($existingImageUrl)
                            <img src="{{ $existingImageUrl }}" alt="Imagen actual" class="h-56 w-full rounded-2xl border border-gray-100 object-cover" />
                        @endif
                    </div>

                    <div class="grid gap-4">
                        <div class="text-lg font-bold text-zinc-900">Ficha técnica (PDF)</div>
                        <div class="text-xs font-mono text-zinc-500">Sube un archivo PDF opcional.</div>

                        <div
                            class="flex h-40 flex-col justify-center rounded-2xl border border-dashed border-gray-200 bg-white px-6 text-xs font-mono text-zinc-500 shadow-sm"
                            x-data="{ dragging: false }"
                            x-on:dragover.prevent="dragging = true"
                            x-on:dragleave.prevent="dragging = false"
                            x-on:drop.prevent="
                                dragging = false;
                                $refs.fileInputPdf.files = $event.dataTransfer.files;
                                $refs.fileInputPdf.dispatchEvent(new Event('change'));
                            "
                            :class="dragging ? 'ring-2 ring-[#E98332]/40' : ''"
                        >
                            <input x-ref="fileInputPdf" type="file" class="hidden" wire:model="datasheet" accept="application/pdf" />
                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-file-pdf text-[#E98332]"></i>
                                    <span>Arrastra y suelta o selecciona un PDF.</span>
                                </div>
                                <button type="button" class="inline-flex h-11 items-center justify-center rounded-full border border-[#E98332] bg-white px-5 text-sm font-bold text-[#E98332] shadow-sm hover:bg-orange-50" x-on:click="$refs.fileInputPdf.click()">
                                    Elegir PDF
                                </button>
                            </div>
                        </div>

                        @if ($existingDatasheetUrl)
                            <div class="flex items-center justify-between gap-3">
                                <a class="text-xs font-mono text-[#008D62] hover:underline" href="{{ $existingDatasheetUrl }}" target="_blank" rel="noopener">Ver PDF actual</a>
                                <button type="button" wire:click="deleteDatasheet" class="inline-flex items-center justify-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50">
                                    <flux:icon.trash variant="outline" class="size-4" />
                                    Borrar
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <button type="button" wire:click="$set('showEditor', false)" class="inline-flex h-11 items-center justify-center rounded-full border border-gray-200 bg-white px-6 text-sm font-bold text-zinc-700 shadow-sm hover:bg-zinc-50">
                            Cancelar
                        </button>
                    </flux:modal.close>
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-[#008D62] px-6 text-sm font-bold text-white shadow-sm hover:bg-[#007A55]">
                        Guardar
                    </button>
                </div>
            </form>
        </flux:modal>
    </div>
</div>
