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
    public string $description = '';
    public $image = null;

    public ?string $existingImageUrl = null;

    public function create(): void
    {
        $this->reset(['editingId', 'title', 'description', 'image', 'existingImageUrl']);
        $this->showEditor = true;
    }

    public function edit(int $id): void
    {
        $product = Product::query()->findOrFail($id);

        $this->editingId = $product->id;
        $this->title = $product->title;
        $this->description = $product->description;
        $this->existingImageUrl = asset($product->image_path);
        $this->image = null;
        $this->showEditor = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:2000'],
            'image' => [$this->editingId ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $product = $this->editingId
            ? Product::query()->findOrFail($this->editingId)
            : new Product();

        $product->fill([
            'title' => $validated['title'],
            'description' => $validated['description'],
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

        $product->save();

        $this->reset(['showEditor', 'editingId', 'title', 'description', 'image', 'existingImageUrl']);
    }

    public function delete(int $id): void
    {
        $product = Product::query()->findOrFail($id);
        $publicPath = $product->image_path;

        $product->delete();

        if (filled($publicPath)) {
            File::delete(public_path($publicPath));
        }
    }

    public function getProductsProperty()
    {
        return Product::query()->latest()->get();
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading>Productos</flux:heading>
            <flux:subheading>Administra el catálogo: imagen, título y descripción.</flux:subheading>
        </div>
        <flux:button variant="primary" type="button" wire:click="create">Nuevo producto</flux:button>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($this->products as $product)
            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white">
                <div class="relative aspect-[16/10] overflow-hidden bg-zinc-100">
                    <img src="{{ asset($product->image_path) }}" alt="{{ $product->title }}" class="h-full w-full object-cover" />
                </div>
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <flux:heading size="sm" class="truncate">{{ $product->title }}</flux:heading>
                            <flux:text class="mt-1 text-sm text-zinc-600">{{ $product->description }}</flux:text>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <flux:button variant="filled" size="sm" type="button" wire:click="edit({{ $product->id }})">Editar</flux:button>
                            <flux:button variant="danger" size="sm" type="button" wire:click="delete({{ $product->id }})">Eliminar</flux:button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-neutral-200 bg-white p-8 text-center md:col-span-2 xl:col-span-3">
                <flux:text>No hay productos todavía.</flux:text>
            </div>
        @endforelse
    </div>

    <flux:modal name="product-editor" wire:model="showEditor" focusable class="max-w-2xl">
        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Editar producto' : 'Nuevo producto' }}</flux:heading>
                <flux:subheading>Sube solo imágenes jpg, png o webp.</flux:subheading>
            </div>

            <div class="grid gap-4">
                <flux:input wire:model="title" label="Título" type="text" required />
                <flux:textarea wire:model="description" label="Descripción" rows="5" required />

                <div class="grid gap-3">
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

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled" type="button" wire:click="$set('showEditor', false)">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">Guardar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
