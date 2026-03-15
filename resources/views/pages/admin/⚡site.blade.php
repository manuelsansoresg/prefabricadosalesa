<?php

use App\Models\SiteSetting;
use App\Models\SiteSettingEmail;
use App\Models\SiteSettingPhone;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Sitio')] class extends Component {
    use WithFileUploads;

    public array $emails = [];
    public array $phones = [];

    public string $newEmail = '';
    public string $newPhone = '';
    public string $newWhatsappUrl = '';

    public $heroVideo = null;
    public ?string $currentHeroVideoPath = null;

    public function mount(): void
    {
        $settings = SiteSetting::query()->first();

        if (! $settings) {
            return;
        }

        $this->currentHeroVideoPath = $settings->hero_video_path;

        $this->emails = $settings->contactEmails()
            ->get()
            ->map(fn (SiteSettingEmail $email) => ['id' => $email->id, 'email' => $email->email, 'sort_order' => (int) $email->sort_order])
            ->all();

        $this->phones = $settings->contactPhones()
            ->get()
            ->map(fn (SiteSettingPhone $phone) => ['id' => $phone->id, 'phone' => $phone->phone, 'whatsapp_url' => (string) ($phone->whatsapp_url ?? ''), 'sort_order' => (int) $phone->sort_order])
            ->all();
    }

    private function normalizeWhatsappUrl(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        $digits = preg_replace('/\D+/', '', $value);
        if (! $digits) {
            return $value;
        }

        return 'https://wa.me/'.$digits;
    }

    private function settings(): SiteSetting
    {
        return SiteSetting::query()->first() ?? SiteSetting::query()->create([]);
    }

    public function addEmail(): void
    {
        $this->validate([
            'newEmail' => ['required', 'email', 'max:255'],
        ]);

        $settings = $this->settings();

        $email = $settings->contactEmails()->create([
            'email' => trim($this->newEmail),
            'sort_order' => count($this->emails),
        ]);

        $this->emails[] = ['id' => $email->id, 'email' => $email->email, 'sort_order' => (int) $email->sort_order];
        $this->reset('newEmail');
    }

    public function saveEmail(int $index): void
    {
        $this->validate([
            "emails.$index.email" => ['required', 'email', 'max:255'],
        ]);

        $payload = $this->emails[$index] ?? null;
        if (! $payload || ! isset($payload['id'])) {
            return;
        }

        SiteSettingEmail::query()
            ->whereKey((int) $payload['id'])
            ->update([
                'email' => trim((string) $payload['email']),
                'sort_order' => (int) ($payload['sort_order'] ?? 0),
            ]);
    }

    public function deleteEmail(int $id): void
    {
        SiteSettingEmail::query()->whereKey($id)->delete();
        $this->emails = array_values(array_filter($this->emails, fn ($row) => (int) ($row['id'] ?? 0) !== $id));
    }

    public function addPhone(): void
    {
        $this->validate([
            'newPhone' => ['required', 'string', 'max:60'],
            'newWhatsappUrl' => ['required', 'string', 'max:255'],
        ]);

        $settings = $this->settings();

        $phone = $settings->contactPhones()->create([
            'phone' => trim($this->newPhone),
            'whatsapp_url' => $this->normalizeWhatsappUrl($this->newWhatsappUrl),
            'sort_order' => count($this->phones),
        ]);

        $this->phones[] = [
            'id' => $phone->id,
            'phone' => $phone->phone,
            'whatsapp_url' => (string) ($phone->whatsapp_url ?? ''),
            'sort_order' => (int) $phone->sort_order,
        ];

        $this->reset(['newPhone', 'newWhatsappUrl']);
    }

    public function savePhone(int $index): void
    {
        $this->validate([
            "phones.$index.phone" => ['required', 'string', 'max:60'],
            "phones.$index.whatsapp_url" => ['required', 'string', 'max:255'],
        ]);

        $payload = $this->phones[$index] ?? null;
        if (! $payload || ! isset($payload['id'])) {
            return;
        }

        SiteSettingPhone::query()
            ->whereKey((int) $payload['id'])
            ->update([
                'phone' => trim((string) $payload['phone']),
                'whatsapp_url' => $this->normalizeWhatsappUrl((string) $payload['whatsapp_url']),
                'sort_order' => (int) ($payload['sort_order'] ?? 0),
            ]);

        $this->phones[$index]['whatsapp_url'] = $this->normalizeWhatsappUrl((string) $payload['whatsapp_url']);
    }

    public function deletePhone(int $id): void
    {
        SiteSettingPhone::query()->whereKey($id)->delete();
        $this->phones = array_values(array_filter($this->phones, fn ($row) => (int) ($row['id'] ?? 0) !== $id));
    }

    public function uploadHeroVideo(): void
    {
        $this->validate([
            'heroVideo' => ['required', 'file', 'mimes:mp4,webm', 'max:51200'],
        ]);

        $settings = SiteSetting::query()->first() ?? SiteSetting::query()->create([]);

        $directory = public_path('videos');
        File::ensureDirectoryExists($directory);

        $extension = strtolower($this->heroVideo->getClientOriginalExtension() ?: 'mp4');
        $fileName = 'hero-'.now()->format('YmdHis').'-'.Str::random(10).'.'.$extension;
        $target = $directory.DIRECTORY_SEPARATOR.$fileName;

        File::put($target, File::get($this->heroVideo->getRealPath()));

        if (filled($settings->hero_video_path) && $settings->hero_video_path !== 'videos/'.$fileName) {
            File::delete(public_path($settings->hero_video_path));
        }

        $settings->update([
            'hero_video_path' => 'videos/'.$fileName,
        ]);

        $this->currentHeroVideoPath = 'videos/'.$fileName;
        $this->reset('heroVideo');
    }
}; ?>

<div class="flex flex-col gap-8">
    <div>
        <flux:heading>Sitio</flux:heading>
        <flux:subheading>Configura el video del hero y los datos de contacto (correos y teléfonos).</flux:subheading>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 bg-white p-6">
            <flux:heading size="sm">Contacto</flux:heading>
            <flux:text class="mt-1 text-sm text-zinc-600">Agrega y edita cada correo y teléfono individualmente.</flux:text>

            <div class="mt-5 space-y-8">
                <div class="space-y-4">
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <flux:input wire:model="newEmail" label="Nuevo correo" type="email" />
                        </div>
                        <flux:button variant="primary" wire:click="addEmail">Agregar</flux:button>
                    </div>

                    <div class="space-y-3">
                        @forelse ($emails as $index => $row)
                            <div class="flex flex-col gap-3 rounded-xl border border-zinc-200 bg-white p-4 md:flex-row md:items-end">
                                <div class="flex-1">
                                    <flux:input wire:model="emails.{{ $index }}.email" label="Correo" type="email" />
                                </div>
                                <div class="w-28">
                                    <flux:input wire:model="emails.{{ $index }}.sort_order" label="Orden" type="number" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <flux:button variant="filled" wire:click="saveEmail({{ $index }})">Guardar</flux:button>
                                    <flux:button variant="danger" wire:click="deleteEmail({{ (int) $row['id'] }})">Eliminar</flux:button>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-600">
                                No hay correos aún.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="grid gap-3 md:grid-cols-2">
                        <flux:input wire:model="newPhone" label="Nuevo teléfono" type="text" />
                        <flux:input wire:model="newWhatsappUrl" label="WhatsApp (URL o número)" type="text" />
                    </div>
                    <div class="flex justify-end">
                        <flux:button variant="primary" wire:click="addPhone">Agregar teléfono</flux:button>
                    </div>

                    <div class="space-y-3">
                        @forelse ($phones as $index => $row)
                            <div class="flex flex-col gap-3 rounded-xl border border-zinc-200 bg-white p-4">
                                <div class="grid gap-3 md:grid-cols-2">
                                    <flux:input wire:model="phones.{{ $index }}.phone" label="Teléfono" type="text" />
                                    <flux:input wire:model="phones.{{ $index }}.whatsapp_url" label="WhatsApp (URL o número)" type="text" />
                                </div>
                                <div class="flex items-end justify-between gap-3">
                                    <div class="w-28">
                                        <flux:input wire:model="phones.{{ $index }}.sort_order" label="Orden" type="number" />
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <flux:button variant="filled" wire:click="savePhone({{ $index }})">Guardar</flux:button>
                                        <flux:button variant="danger" wire:click="deletePhone({{ (int) $row['id'] }})">Eliminar</flux:button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-600">
                                No hay teléfonos aún.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6">
            <flux:heading size="sm">Video del hero</flux:heading>
            <flux:text class="mt-1 text-sm text-zinc-600">Formatos permitidos: mp4, webm. Máximo 50 MB.</flux:text>

            @if ($currentHeroVideoPath)
                <video class="mt-5 w-full rounded-xl border border-zinc-200" controls preload="metadata">
                    <source src="{{ asset($currentHeroVideoPath) }}" type="video/mp4" />
                </video>
            @else
                <div class="mt-5 rounded-xl border border-dashed border-zinc-200 bg-zinc-50 p-6 text-sm text-zinc-600">
                    No hay video cargado todavía.
                </div>
            @endif

            <form wire:submit="uploadHeroVideo" class="mt-5 space-y-4">
                <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-4 text-sm text-zinc-600" x-data="{ dragging: false }"
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="
                        dragging = false;
                        $refs.fileInput.files = $event.dataTransfer.files;
                        $refs.fileInput.dispatchEvent(new Event('change'));
                    "
                    :class="dragging ? 'ring-2 ring-[color:var(--color-accent)]' : ''"
                >
                    <input x-ref="fileInput" type="file" class="hidden" wire:model="heroVideo" accept="video/mp4,video/webm" />

                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-film"></i>
                            <span>Arrastra y suelta o selecciona un video.</span>
                        </div>
                        <button type="button" class="rounded-lg bg-white px-3 py-2 font-medium text-zinc-900 shadow-sm hover:bg-zinc-50" @click="$refs.fileInput.click()">
                            Elegir video
                        </button>
                    </div>
                </div>

                <div class="flex justify-end">
                    <flux:button variant="primary" type="submit">Subir video</flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
