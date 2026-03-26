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
    public bool $showVideoUploader = false;
    public array $uploads = [];
    public $video = null;
    public $videoCover = null;

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
                'media_type' => 'image',
                'image_path' => 'image/gallery/'.$fileName,
                'thumb_path' => $thumbPath,
            ]);
        }

        $this->reset(['showUploader', 'uploads']);
    }

    public function saveVideoUpload(): void
    {
        set_time_limit(300);

        $validated = $this->validate([
            'video' => ['required', 'file', 'mimetypes:video/mp4,video/webm', 'max:61440'],
            'videoCover' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $videoDirectory = public_path('videos/gallery');
        File::ensureDirectoryExists($videoDirectory);

        $coverDirectory = public_path('image/gallery/video-covers');
        File::ensureDirectoryExists($coverDirectory);

        $coverThumbDirectory = public_path('image/gallery/video-covers/thumbs');
        File::ensureDirectoryExists($coverThumbDirectory);

        $videoExtension = strtolower($validated['video']->getClientOriginalExtension() ?: 'mp4');
        $videoFileName = now()->format('YmdHis').'-'.Str::random(12).'.'.$videoExtension;
        $videoTarget = $videoDirectory.DIRECTORY_SEPARATOR.$videoFileName;

        File::copy($validated['video']->getRealPath(), $videoTarget);

        $coverExtension = strtolower($validated['videoCover']->getClientOriginalExtension() ?: 'jpg');
        $coverFileName = now()->format('YmdHis').'-'.Str::random(12).'.'.$coverExtension;
        $coverTarget = $coverDirectory.DIRECTORY_SEPARATOR.$coverFileName;

        File::copy($validated['videoCover']->getRealPath(), $coverTarget);

        $coverThumbExtension = function_exists('imagewebp') ? 'webp' : 'jpg';
        $coverThumbFileName = pathinfo($coverFileName, PATHINFO_FILENAME).'-thumb.'.$coverThumbExtension;
        $coverThumbTarget = $coverThumbDirectory.DIRECTORY_SEPARATOR.$coverThumbFileName;
        $coverThumbPath = null;

        try {
            if ($this->createThumbnail($coverTarget, $coverThumbTarget)) {
                $coverThumbPath = 'image/gallery/video-covers/thumbs/'.$coverThumbFileName;
            }
        } catch (Throwable) {
            $coverThumbPath = null;
        }

        $coverPath = 'image/gallery/video-covers/'.$coverFileName;
        $videoPath = 'videos/gallery/'.$videoFileName;

        GalleryImage::query()->create([
            'media_type' => 'video',
            'image_path' => $coverPath,
            'thumb_path' => $coverThumbPath,
            'video_path' => $videoPath,
            'video_cover_path' => $coverPath,
            'video_cover_thumb_path' => $coverThumbPath,
        ]);

        $this->reset(['showVideoUploader', 'video', 'videoCover']);
    }

    public function delete(int $id): void
    {
        $image = GalleryImage::query()->findOrFail($id);
        $paths = [];

        if (filled($image->image_path)) {
            $paths[] = $image->image_path;
        }

        if (filled($image->thumb_path)) {
            $paths[] = $image->thumb_path;
        }

        if (filled($image->video_path)) {
            $paths[] = $image->video_path;
        }

        if (filled($image->video_cover_path)) {
            $paths[] = $image->video_cover_path;
        }

        if (filled($image->video_cover_thumb_path)) {
            $paths[] = $image->video_cover_thumb_path;
        }

        $image->delete();

        $paths = collect($paths)->map(fn ($p) => (string) $p)->filter()->unique()->values()->all();

        foreach ($paths as $publicPath) {
            File::delete(public_path($publicPath));
        }
    }

    public function getImagesProperty()
    {
        $items = GalleryImage::query()->latest()->get();

        $thumbExtension = function_exists('imagewebp') ? 'webp' : 'jpg';

        $imageThumbDirectory = public_path('image/gallery/thumbs');
        File::ensureDirectoryExists($imageThumbDirectory);

        $coverThumbDirectory = public_path('image/gallery/video-covers/thumbs');
        File::ensureDirectoryExists($coverThumbDirectory);

        foreach ($items as $item) {
            $type = (string) ($item->media_type ?? 'image');

            if ($type === 'video') {
                $coverPath = trim((string) ($item->video_cover_path ?? $item->image_path ?? ''));
                $hasThumb = trim((string) ($item->video_cover_thumb_path ?? '')) !== '';

                if ($coverPath === '' || $hasThumb) {
                    continue;
                }

                $source = public_path($coverPath);

                if (! File::exists($source)) {
                    continue;
                }

                $thumbFileName = pathinfo($coverPath, PATHINFO_FILENAME).'-thumb.'.$thumbExtension;
                $target = $coverThumbDirectory.DIRECTORY_SEPARATOR.$thumbFileName;
                $publicThumbPath = 'image/gallery/video-covers/thumbs/'.$thumbFileName;

                if (! File::exists($target)) {
                    try {
                        $this->createThumbnail($source, $target);
                    } catch (Throwable) {
                    }
                }

                if (File::exists($target)) {
                    $item->forceFill([
                        'video_cover_thumb_path' => $publicThumbPath,
                        'thumb_path' => $publicThumbPath,
                    ])->save();
                    $item->video_cover_thumb_path = $publicThumbPath;
                    $item->thumb_path = $publicThumbPath;
                }

                continue;
            }

            $imagePath = trim((string) ($item->image_path ?? ''));
            $hasThumb = trim((string) ($item->thumb_path ?? '')) !== '';

            if ($imagePath === '' || $hasThumb) {
                continue;
            }

            $source = public_path($imagePath);

            if (! File::exists($source)) {
                continue;
            }

            $thumbFileName = pathinfo($imagePath, PATHINFO_FILENAME).'-thumb.'.$thumbExtension;
            $target = $imageThumbDirectory.DIRECTORY_SEPARATOR.$thumbFileName;
            $publicThumbPath = 'image/gallery/thumbs/'.$thumbFileName;

            if (! File::exists($target)) {
                try {
                    $this->createThumbnail($source, $target);
                } catch (Throwable) {
                }
            }

            if (File::exists($target)) {
                $item->forceFill([
                    'thumb_path' => $publicThumbPath,
                ])->save();
                $item->thumb_path = $publicThumbPath;
            }
        }

        return $items;
    }
}; ?>

<div class="min-h-screen bg-[#F8F9FA] px-4 py-8">
    <div class="mx-auto flex w-full max-w-6xl flex-col gap-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <div class="text-lg font-bold text-zinc-900">Galería</div>
                <div class="mt-1 text-xs font-mono text-zinc-500">Sube imágenes (jpg, png, webp) para la sección Galería.</div>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                <button type="button" wire:click="$set('showUploader', true)" class="inline-flex h-11 items-center justify-center rounded-full bg-[#008D62] px-6 text-sm font-bold text-white shadow-sm hover:bg-[#007A55]">
                    Subir imágenes
                </button>
                <button type="button" wire:click="$set('showVideoUploader', true)" class="inline-flex h-11 items-center justify-center rounded-full border border-[#008D62] bg-white px-6 text-sm font-bold text-[#008D62] shadow-sm hover:bg-emerald-50">
                    Subir video
                </button>
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse ($this->images as $img)
                <div class="group relative overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-lg">
                    <div class="relative aspect-square overflow-hidden bg-gray-50">
                        <img src="{{ asset(($img->media_type ?? 'image') === 'video' ? ($img->video_cover_thumb_path ?: $img->video_cover_path ?: $img->thumb_path ?: $img->image_path) : ($img->thumb_path ?: $img->image_path)) }}" alt="Galería" class="h-full w-full object-cover" />
                        @if (($img->media_type ?? 'image') === 'video')
                            <div class="pointer-events-none absolute inset-0 grid place-items-center">
                                <div class="grid size-12 place-items-center rounded-full bg-black/55 text-white">
                                    <i class="fa-solid fa-play"></i>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="absolute inset-x-0 bottom-0 flex justify-end bg-linear-to-t from-black/70 via-black/0 to-black/0 p-3 opacity-0 transition group-hover:opacity-100">
                        <button type="button" wire:click="delete({{ $img->id }})" class="inline-flex items-center justify-center rounded-full bg-white/90 p-2 text-red-600 shadow-sm hover:bg-red-50" aria-label="Eliminar">
                            <flux:icon.trash variant="outline" class="size-5" />
                        </button>
                    </div>
                </div>
            @empty
                <div class="rounded-3xl border border-gray-100 bg-white p-10 text-center shadow-lg sm:col-span-2 lg:col-span-3 xl:col-span-4">
                    <div class="text-xs font-mono text-zinc-500">No hay imágenes todavía.</div>
                </div>
            @endforelse
        </div>

        <flux:modal name="gallery-uploader" wire:model="showUploader" focusable class="max-w-xl">
            <form wire:submit.prevent="saveUploads" class="space-y-6">
                <div>
                    <div class="text-lg font-bold text-zinc-900">Subir imágenes</div>
                    <div class="mt-1 text-xs font-mono text-zinc-500">Solo archivos de imagen (jpg, png, webp).</div>
                </div>

                <div class="grid gap-4">
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
                        class="flex h-40 flex-col justify-center rounded-2xl border border-dashed border-gray-200 bg-white px-6 text-xs font-mono text-zinc-500 shadow-sm"
                        x-data="{ dragging: false }"
                        x-on:dragover.prevent="dragging = true"
                        x-on:dragleave.prevent="dragging = false"
                        x-on:drop.prevent="
                            dragging = false;
                            const files = Array.from($event.dataTransfer?.files || []);
                            if (files.length > 0) {
                                $wire.uploadMultiple('uploads', files);
                            }
                        "
                        :class="dragging ? 'ring-2 ring-[#E98332]/40' : ''"
                    >
                        <input x-ref="fileInput" type="file" class="hidden" wire:model="uploads" multiple accept="image/jpeg,image/png,image/webp" />

                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div class="flex items-center gap-3">
                                <flux:icon.arrow-up-tray variant="outline" class="size-6 text-[#E98332]" />
                                <span>Arrastra y suelta o selecciona archivos.</span>
                            </div>
                            <button type="button" class="inline-flex h-11 items-center justify-center rounded-full border border-[#E98332] bg-white px-5 text-sm font-bold text-[#E98332] shadow-sm hover:bg-orange-50" x-on:click="$refs.fileInput.click()">
                                Elegir imágenes
                            </button>
                        </div>
                    </div>

                    @if (count($uploads) > 0)
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 text-xs font-mono text-zinc-600" wire:loading wire:target="uploads">
                            Preparando archivos…
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach (array_slice($uploads, 0, 4) as $img)
                                <img src="{{ $img->temporaryUrl() }}" alt="Vista previa" class="h-44 w-full rounded-2xl border border-gray-100 object-cover" />
                            @endforeach
                        </div>

                        @if (count($uploads) > 4)
                            <div class="text-xs font-mono text-zinc-500">+{{ count($uploads) - 4 }} más seleccionadas</div>
                        @endif
                    @endif
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <button type="button" wire:click="$set('showUploader', false)" class="inline-flex h-11 items-center justify-center rounded-full border border-gray-200 bg-white px-6 text-sm font-bold text-zinc-700 shadow-sm hover:bg-zinc-50">
                            Cancelar
                        </button>
                    </flux:modal.close>
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveUploads,uploads" class="inline-flex h-11 items-center justify-center rounded-full bg-[#008D62] px-6 text-sm font-bold text-white shadow-sm hover:bg-[#007A55]">
                        Subir
                    </button>
                </div>
            </form>
        </flux:modal>

        <flux:modal name="gallery-video-uploader" wire:model="showVideoUploader" focusable class="max-w-xl">
            <form wire:submit.prevent="saveVideoUpload" class="space-y-6" x-data>
                <div>
                    <div class="text-lg font-bold text-zinc-900">Subir video</div>
                    <div class="mt-1 text-xs font-mono text-zinc-500">Adjunta el video (mp4, webm) y una portada (jpg, png, webp).</div>
                </div>

                <div class="grid gap-5">
                    @if ($errors->has('video') || $errors->has('videoCover'))
                        <flux:callout variant="danger" icon="x-circle" heading="No se pudo subir">
                            <div class="space-y-1">
                                @foreach ($errors->get('video') as $message)
                                    <div>{{ $message }}</div>
                                @endforeach
                                @foreach ($errors->get('videoCover') as $message)
                                    <div>{{ $message }}</div>
                                @endforeach
                            </div>
                        </flux:callout>
                    @endif

                    <div class="grid gap-2">
                        <div class="text-sm font-bold text-zinc-900">Video</div>
                        <div class="text-xs font-mono text-zinc-500">Solo mp4 o webm. Máximo 60 MB.</div>
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-dashed border-gray-200 bg-white px-6 py-5 text-xs font-mono text-zinc-500 shadow-sm">
                            <input x-ref="videoInput" type="file" class="hidden" wire:model="video" accept="video/mp4,video/webm" />
                            <div class="flex items-center gap-3">
                                <flux:icon.arrow-up-tray variant="outline" class="size-6 text-[#E98332]" />
                                <span>{{ $video ? 'Video seleccionado' : 'Selecciona un archivo de video.' }}</span>
                            </div>
                            <button type="button" class="inline-flex h-11 items-center justify-center rounded-full border border-[#E98332] bg-white px-5 text-sm font-bold text-[#E98332] shadow-sm hover:bg-orange-50" x-on:click="$refs.videoInput.click()">
                                Elegir video
                            </button>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <div class="text-sm font-bold text-zinc-900">Portada</div>
                        <div class="text-xs font-mono text-zinc-500">Se mostrará con el ícono de play en la galería.</div>
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-dashed border-gray-200 bg-white px-6 py-5 text-xs font-mono text-zinc-500 shadow-sm">
                            <input x-ref="coverInput" type="file" class="hidden" wire:model="videoCover" accept="image/jpeg,image/png,image/webp" />
                            <div class="flex items-center gap-3">
                                <flux:icon.arrow-up-tray variant="outline" class="size-6 text-[#E98332]" />
                                <span>{{ $videoCover ? 'Portada seleccionada' : 'Selecciona una imagen de portada.' }}</span>
                            </div>
                            <button type="button" class="inline-flex h-11 items-center justify-center rounded-full border border-[#E98332] bg-white px-5 text-sm font-bold text-[#E98332] shadow-sm hover:bg-orange-50" x-on:click="$refs.coverInput.click()">
                                Elegir portada
                            </button>
                        </div>

                        @if ($videoCover)
                            <img src="{{ $videoCover->temporaryUrl() }}" alt="Vista previa portada" class="h-44 w-full rounded-2xl border border-gray-100 object-cover" />
                        @endif
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <button type="button" wire:click="$set('showVideoUploader', false)" class="inline-flex h-11 items-center justify-center rounded-full border border-gray-200 bg-white px-6 text-sm font-bold text-zinc-700 shadow-sm hover:bg-zinc-50">
                            Cancelar
                        </button>
                    </flux:modal.close>
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveVideoUpload,video,videoCover" class="inline-flex h-11 items-center justify-center rounded-full bg-[#008D62] px-6 text-sm font-bold text-white shadow-sm hover:bg-[#007A55]">
                        Subir
                    </button>
                </div>
            </form>
        </flux:modal>
    </div>
</div>
