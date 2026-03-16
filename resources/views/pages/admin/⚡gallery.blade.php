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
    public array $uploads = [];

    private function createThumbnail(string $sourcePath, string $thumbTargetPath, int $maxSide = 700): bool
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagecreatetruecolor')) {
            return false;
        }

        $contents = @file_get_contents($sourcePath);

        if ($contents === false) {
            return false;
        }

        $sourceImage = @imagecreatefromstring($contents);

        if ($sourceImage === false) {
            return false;
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        if ($sourceWidth <= 0 || $sourceHeight <= 0) {
            imagedestroy($sourceImage);

            return false;
        }

        $scale = min($maxSide / $sourceWidth, $maxSide / $sourceHeight, 1);
        $thumbWidth = max(1, (int) round($sourceWidth * $scale));
        $thumbHeight = max(1, (int) round($sourceHeight * $scale));

        $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);

        if ($thumbImage === false) {
            imagedestroy($sourceImage);

            return false;
        }

        $isWebpTarget = str_ends_with(strtolower($thumbTargetPath), '.webp');

        if ($isWebpTarget && function_exists('imagewebp')) {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
            $transparent = imagecolorallocatealpha($thumbImage, 0, 0, 0, 127);
            imagefilledrectangle($thumbImage, 0, 0, $thumbWidth, $thumbHeight, $transparent);
        } else {
            $white = imagecolorallocate($thumbImage, 255, 255, 255);
            imagefilledrectangle($thumbImage, 0, 0, $thumbWidth, $thumbHeight, $white);
        }

        imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);

        $ok = false;

        if ($isWebpTarget && function_exists('imagewebp')) {
            $ok = imagewebp($thumbImage, $thumbTargetPath, 80);
        } elseif (function_exists('imagejpeg')) {
            $ok = imagejpeg($thumbImage, $thumbTargetPath, 82);
        }

        imagedestroy($thumbImage);
        imagedestroy($sourceImage);

        return $ok;
    }

    public function saveUploads(): void
    {
        set_time_limit(300);

        $validated = $this->validate([
            'uploads' => ['required', 'array', 'min:1', 'max:30'],
            'uploads.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $directory = public_path('image/gallery');
        File::ensureDirectoryExists($directory);

        $thumbDirectory = public_path('image/gallery/thumbs');
        File::ensureDirectoryExists($thumbDirectory);

        foreach ($validated['uploads'] as $image) {
            $extension = strtolower($image->getClientOriginalExtension() ?: 'jpg');
            $fileName = now()->format('YmdHis').'-'.Str::random(12).'.'.$extension;
            $target = $directory.DIRECTORY_SEPARATOR.$fileName;

            File::copy($image->getRealPath(), $target);

            $thumbExtension = function_exists('imagewebp') ? 'webp' : 'jpg';
            $thumbFileName = pathinfo($fileName, PATHINFO_FILENAME).'-thumb.'.$thumbExtension;
            $thumbTarget = $thumbDirectory.DIRECTORY_SEPARATOR.$thumbFileName;
            $thumbPath = null;

            try {
                if ($this->createThumbnail($target, $thumbTarget)) {
                    $thumbPath = 'image/gallery/thumbs/'.$thumbFileName;
                }
            } catch (Throwable) {
                $thumbPath = null;
            }

            GalleryImage::query()->create([
                'image_path' => 'image/gallery/'.$fileName,
                'thumb_path' => $thumbPath,
            ]);
        }

        $this->reset(['showUploader', 'uploads']);
    }

    public function delete(int $id): void
    {
        $image = GalleryImage::query()->findOrFail($id);
        $publicPath = $image->image_path;
        $thumbPublicPath = $image->thumb_path;

        $image->delete();

        if (filled($publicPath)) {
            File::delete(public_path($publicPath));
        }

        if (filled($thumbPublicPath)) {
            File::delete(public_path($thumbPublicPath));
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
        <flux:button variant="primary" type="button" wire:click="$set('showUploader', true)">Subir imágenes</flux:button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($this->images as $img)
            <div class="group relative overflow-hidden rounded-xl border border-neutral-200 bg-white">
                <div class="relative aspect-square overflow-hidden bg-zinc-100">
                    <img src="{{ asset($img->thumb_path ?: $img->image_path) }}" alt="Galería" class="h-full w-full object-cover" />
                </div>
                <div class="absolute inset-x-0 bottom-0 flex justify-end gap-2 bg-linear-to-t from-black/70 via-black/0 to-black/0 p-3 opacity-0 transition group-hover:opacity-100">
                    <flux:button variant="danger" size="sm" type="button" wire:click="delete({{ $img->id }})">Eliminar</flux:button>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-neutral-200 bg-white p-8 text-center sm:col-span-2 lg:col-span-3 xl:col-span-4">
                <flux:text>No hay imágenes todavía.</flux:text>
            </div>
        @endforelse
    </div>

    <flux:modal name="gallery-uploader" wire:model="showUploader" focusable class="max-w-xl">
        <form wire:submit.prevent="saveUploads" class="space-y-6">
            <div>
                <flux:heading size="lg">Subir imágenes</flux:heading>
                <flux:subheading>Solo archivos de imagen (jpg, png, webp).</flux:subheading>
            </div>

            <div class="grid gap-3">
                @if ($errors->has('uploads') || $errors->has('uploads.*'))
                    <flux:callout variant="danger" icon="x-circle" heading="No se pudo subir">
                        <div class="space-y-1">
                            @foreach ($errors->get('uploads') as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                            @foreach ($errors->get('uploads.*') as $messages)
                                @foreach ($messages as $message)
                                    <div>{{ $message }}</div>
                                @endforeach
                            @endforeach
                        </div>
                    </flux:callout>
                @endif

                <div
                    class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-4 text-sm text-zinc-600"
                    x-data="{ dragging: false }"
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="
                        dragging = false;
                        const files = Array.from($event.dataTransfer?.files || []);
                        if (files.length > 0) {
                            $wire.uploadMultiple('uploads', files);
                        }
                    "
                    :class="dragging ? 'ring-2 ring-[color:var(--color-accent)]' : ''"
                >
                    <input x-ref="fileInput" type="file" class="hidden" wire:model="uploads" multiple accept="image/jpeg,image/png,image/webp" />

                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Arrastra y suelta o selecciona archivos.</span>
                        </div>
                        <button type="button" class="rounded-lg bg-white px-3 py-2 font-medium text-zinc-900 shadow-sm hover:bg-zinc-50" @click="$refs.fileInput.click()">
                            Elegir imágenes
                        </button>
                    </div>
                </div>

                @if (count($uploads) > 0)
                    <div class="rounded-xl border border-zinc-200 bg-white p-3 text-sm text-zinc-700" wire:loading wire:target="uploads">
                        Preparando archivos…
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach (array_slice($uploads, 0, 4) as $img)
                            <img src="{{ $img->temporaryUrl() }}" alt="Vista previa" class="h-44 w-full rounded-xl object-cover" />
                        @endforeach
                    </div>

                    @if (count($uploads) > 4)
                        <flux:text class="text-sm text-zinc-600">+{{ count($uploads) - 4 }} más seleccionadas</flux:text>
                    @endif
                @endif
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled" type="button" wire:click="$set('showUploader', false)">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled" wire:target="saveUploads,uploads">Subir</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
