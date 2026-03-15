<?php

use App\Models\GalleryImage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Galería')] class extends Component {
    use WithFileUploads;

    public bool $showUploader = false;
    public $image = null;

    public function upload(): void
    {
        $validated = $this->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $directory = public_path('image/gallery');
        File::ensureDirectoryExists($directory);

        $extension = strtolower($this->image->getClientOriginalExtension() ?: 'jpg');
        $fileName = now()->format('YmdHis').'-'.Str::random(12).'.'.$extension;
        $target = $directory.DIRECTORY_SEPARATOR.$fileName;

        File::put($target, File::get($this->image->getRealPath()));

        GalleryImage::query()->create([
            'image_path' => 'image/gallery/'.$fileName,
        ]);

        $this->reset(['showUploader', 'image']);
    }

    public function delete(int $id): void
    {
        $image = GalleryImage::query()->findOrFail($id);
        $publicPath = $image->image_path;

        $image->delete();

        if (filled($publicPath)) {
            File::delete(public_path($publicPath));
        }
    }

    public function getImagesProperty()
    {
        return GalleryImage::query()->latest()->get();
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading>Galería</flux:heading>
            <flux:subheading>Sube imágenes (jpg, png, webp) para la sección Galería.</flux:subheading>
        </div>
        <flux:button variant="primary" wire:click="$set('showUploader', true)">Subir imagen</flux:button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($this->images as $img)
            <div class="group relative overflow-hidden rounded-xl border border-neutral-200 bg-white">
                <div class="relative aspect-square overflow-hidden bg-zinc-100">
                    <img src="{{ asset($img->image_path) }}" alt="Galería" class="h-full w-full object-cover" />
                </div>
                <div class="absolute inset-x-0 bottom-0 flex justify-end gap-2 bg-linear-to-t from-black/70 via-black/0 to-black/0 p-3 opacity-0 transition group-hover:opacity-100">
                    <flux:button variant="danger" size="sm" wire:click="delete({{ $img->id }})">Eliminar</flux:button>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-neutral-200 bg-white p-8 text-center sm:col-span-2 lg:col-span-3 xl:col-span-4">
                <flux:text>No hay imágenes todavía.</flux:text>
            </div>
        @endforelse
    </div>

    <flux:modal name="gallery-uploader" :show="$showUploader" focusable class="max-w-xl">
        <form wire:submit="upload" class="space-y-6">
            <div>
                <flux:heading size="lg">Subir imagen</flux:heading>
                <flux:subheading>Solo archivos de imagen (jpg, png, webp).</flux:subheading>
            </div>

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
                    <img src="{{ $image->temporaryUrl() }}" alt="Vista previa" class="h-64 w-full rounded-xl object-cover" />
                @endif
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled" type="button" wire:click="$set('showUploader', false)">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">Subir</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
