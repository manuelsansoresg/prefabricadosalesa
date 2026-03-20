<?php

use App\Models\SiteSetting;
use App\Models\SiteSettingEmail;
use App\Models\SiteSettingPhone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Sitio')] class extends Component {
    use WithFileUploads;

    public array $emails = [];
    public array $phones = [];

    public string $contactAddress = '';
    public string $mapEmbedUrl = '';
    public bool $saved = false;

    public string $newEmailLabel = '';
    public string $newEmail = '';
    public string $newPhone = '';
    public string $newWhatsappUrl = '';

    public string $whatsappNumber = '';
    public string $whatsappMessage = '';

    public $heroImage = null;
    public ?string $currentHeroImagePath = null;

    public function mount(): void
    {
        $settings = SiteSetting::query()->first();

        if (! $settings) {
            return;
        }

        $heroMediaPath = (string) ($settings->hero_video_path ?? '');
        $this->currentHeroImagePath = $heroMediaPath !== '' && ! preg_match('/\.(mp4|webm)$/i', $heroMediaPath) ? $heroMediaPath : null;
        $this->contactAddress = (string) ($settings->contact_address ?? '');
        $this->mapEmbedUrl = (string) ($settings->map_embed_url ?? '');
        $this->whatsappNumber = (string) ($settings->whatsapp_number ?? '');
        $this->whatsappMessage = (string) ($settings->whatsapp_message ?? '');

        $this->emails = $settings->contactEmails()
            ->get()
            ->map(fn (SiteSettingEmail $email) => ['id' => $email->id, 'label' => (string) ($email->label ?? ''), 'email' => $email->email, 'sort_order' => (int) $email->sort_order])
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

    public function saveContactSection(): void
    {
        $this->saved = false;

        $this->validate([
            'heroImage' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'contactAddress' => ['nullable', 'string', 'max:2000'],
            'mapEmbedUrl' => ['nullable', 'string', 'max:2000'],
            'whatsappNumber' => ['nullable', 'string', 'max:30'],
            'whatsappMessage' => ['nullable', 'string', 'max:500'],

            'emails' => ['array'],
            'emails.*.id' => ['required', 'integer'],
            'emails.*.label' => ['required', 'string', 'max:60'],
            'emails.*.email' => ['required', 'email', 'max:255'],
            'emails.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'phones' => ['array'],
            'phones.*.id' => ['required', 'integer'],
            'phones.*.phone' => ['required', 'string', 'max:60'],
            'phones.*.whatsapp_url' => ['nullable', 'string', 'max:255'],
            'phones.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $address = trim($this->contactAddress);
        $mapEmbedUrl = trim($this->mapEmbedUrl);
        if ($mapEmbedUrl === '' && $address !== '') {
            $mapEmbedUrl = 'https://www.google.com/maps?q='.rawurlencode($address).'&output=embed&z=17';
            $this->mapEmbedUrl = $mapEmbedUrl;
        }

        $settings = $this->settings();
        $oldHeroPath = (string) ($settings->hero_video_path ?? '');
        $newHeroPath = null;
        $newHeroAbsolutePath = null;

        if ($this->heroImage) {
            $directory = public_path('image/hero');
            File::ensureDirectoryExists($directory);

            $extension = strtolower($this->heroImage->getClientOriginalExtension() ?: 'jpg');
            $fileName = 'hero-'.now()->format('YmdHis').'-'.Str::random(10).'.'.$extension;
            $newHeroAbsolutePath = $directory.DIRECTORY_SEPARATOR.$fileName;
            $newHeroPath = 'image/hero/'.$fileName;

            File::put($newHeroAbsolutePath, File::get($this->heroImage->getRealPath()));
        }

        try {
            DB::transaction(function () use ($settings, $address, $mapEmbedUrl, $newHeroPath) {
                $update = [
                'contact_address' => $address,
                'map_embed_url' => $mapEmbedUrl,
                'whatsapp_number' => trim($this->whatsappNumber),
                'whatsapp_message' => trim($this->whatsappMessage),
                ];

                if ($newHeroPath) {
                    $update['hero_video_path'] = $newHeroPath;
                }

                $settings->update($update);

                foreach ($this->emails as $row) {
                    $id = (int) ($row['id'] ?? 0);
                    if ($id <= 0) {
                        continue;
                    }

                    SiteSettingEmail::query()
                        ->whereKey($id)
                        ->update([
                            'label' => trim((string) ($row['label'] ?? '')),
                            'email' => trim((string) ($row['email'] ?? '')),
                            'sort_order' => (int) ($row['sort_order'] ?? 0),
                        ]);
                }

                foreach ($this->phones as $i => $row) {
                    $id = (int) ($row['id'] ?? 0);
                    if ($id <= 0) {
                        continue;
                    }

                    $normalizedWhatsapp = $this->normalizeWhatsappUrl((string) ($row['whatsapp_url'] ?? ''));
                    $this->phones[$i]['whatsapp_url'] = $normalizedWhatsapp;

                    SiteSettingPhone::query()
                        ->whereKey($id)
                        ->update([
                            'phone' => trim((string) ($row['phone'] ?? '')),
                            'whatsapp_url' => $normalizedWhatsapp !== '' ? $normalizedWhatsapp : null,
                            'sort_order' => (int) ($row['sort_order'] ?? 0),
                        ]);
                }
            });
        } catch (Throwable $e) {
            if ($newHeroAbsolutePath) {
                File::delete($newHeroAbsolutePath);
            }

            throw $e;
        }

        if ($newHeroPath) {
            if (filled($oldHeroPath) && $oldHeroPath !== $newHeroPath) {
                File::delete(public_path($oldHeroPath));
            }

            $this->currentHeroImagePath = $newHeroPath;
            $this->reset('heroImage');
        }

        $this->saved = true;
    }

    public function updated(): void
    {
        $this->saved = false;
    }

    public function addEmail(): void
    {
        $this->saved = false;
        $this->validate([
            'newEmailLabel' => ['required', 'string', 'max:60'],
            'newEmail' => ['required', 'email', 'max:255'],
        ]);

        $settings = $this->settings();

        $email = $settings->contactEmails()->create([
            'label' => trim($this->newEmailLabel),
            'email' => trim($this->newEmail),
            'sort_order' => count($this->emails),
        ]);

        $this->emails[] = ['id' => $email->id, 'label' => (string) ($email->label ?? ''), 'email' => $email->email, 'sort_order' => (int) $email->sort_order];
        $this->reset(['newEmailLabel', 'newEmail']);
    }

    public function deleteEmail(int $id): void
    {
        $this->saved = false;
        SiteSettingEmail::query()->whereKey($id)->delete();
        $this->emails = array_values(array_filter($this->emails, fn ($row) => (int) ($row['id'] ?? 0) !== $id));
    }

    public function addPhone(): void
    {
        $this->saved = false;
        $this->validate([
            'newPhone' => ['required', 'string', 'max:60'],
            'newWhatsappUrl' => ['nullable', 'string', 'max:255'],
        ]);

        $settings = $this->settings();

        $whatsappUrl = $this->normalizeWhatsappUrl($this->newWhatsappUrl);
        $whatsappUrl = $whatsappUrl !== '' ? $whatsappUrl : null;

        $phone = $settings->contactPhones()->create([
            'phone' => trim($this->newPhone),
            'whatsapp_url' => $whatsappUrl,
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

    public function deletePhone(int $id): void
    {
        $this->saved = false;
        SiteSettingPhone::query()->whereKey($id)->delete();
        $this->phones = array_values(array_filter($this->phones, fn ($row) => (int) ($row['id'] ?? 0) !== $id));
    }

}; ?>

<div class="min-h-screen bg-[#F8F9FA] px-4 py-8">
    <div class="mx-auto flex w-full max-w-6xl flex-col gap-8">
        <div>
            <div class="text-lg font-bold text-zinc-900">Sitio</div>
            <div class="mt-1 text-xs font-mono text-zinc-500">Configura la imagen del hero, la ubicación (dirección y mapa) y los datos de contacto.</div>
        </div>

        <form wire:submit.prevent="saveContactSection" class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-6">
                <div class="rounded-3xl border border-gray-100 bg-white p-8 shadow-lg">
                    <div class="text-lg font-bold text-zinc-900">Contacto</div>
                    <div class="mt-1 text-xs font-mono text-zinc-500">Agrega y edita la ubicación, correos y teléfonos.</div>

                    <div class="mt-6 space-y-8">
                        <div class="space-y-4">
                            <div class="text-lg font-bold text-zinc-900">Ubicación</div>
                            <div class="space-y-2">
                                <label class="text-sm text-zinc-700" for="contactAddress">Dirección</label>
                                <textarea
                                    id="contactAddress"
                                    rows="3"
                                    wire:model="contactAddress"
                                    class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                                ></textarea>
                            </div>
                            <div class="space-y-2" wire:ignore>
                                <label for="placesAddressSearch" class="text-sm text-zinc-700">Buscador de Dirección</label>
                                <input
                                    id="placesAddressSearch"
                                    type="text"
                                    value="{{ $contactAddress }}"
                                    autocomplete="off"
                                    data-google-maps-key="{{ urlencode((string) (config('services.google_maps.key') ?: env('GOOGLE_MAPS_API_KEY') ?: '')) }}"
                                    class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                                />
                            </div>
                            <input type="hidden" wire:model="mapEmbedUrl" />
                        </div>

                        <div class="space-y-4">
                            <div class="flex flex-col gap-3 md:flex-row md:items-end">
                                <div class="flex-1 space-y-2">
                                    <label class="text-sm text-zinc-700" for="newEmailLabel">Título</label>
                                    <input
                                        id="newEmailLabel"
                                        type="text"
                                        wire:model="newEmailLabel"
                                        class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                                    />
                                </div>
                                <div class="flex-1 space-y-2">
                                    <label class="text-sm text-zinc-700" for="newEmail">Correo</label>
                                    <input
                                        id="newEmail"
                                        type="email"
                                        wire:model="newEmail"
                                        class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                                    />
                                </div>
                                <button
                                    type="button"
                                    wire:click="addEmail"
                                    class="inline-flex h-11 items-center justify-center rounded-full border border-[#E98332] bg-white px-5 text-sm font-bold text-[#E98332] shadow-sm hover:bg-orange-50"
                                >
                                    Agregar
                                </button>
                            </div>

                            <div class="space-y-3">
                                @forelse ($emails as $index => $row)
                                    <div class="flex flex-col gap-3 rounded-2xl border border-gray-100 bg-gray-50 p-4 md:flex-row md:items-end">
                                        <div class="flex items-center gap-3">
                                            <flux:icon.bars-2 variant="outline" class="size-5 text-zinc-400" />
                                        </div>
                                        <div class="flex-1 space-y-2">
                                            <label class="text-sm text-zinc-700" for="emailLabel{{ $index }}">Título</label>
                                            <input
                                                id="emailLabel{{ $index }}"
                                                type="text"
                                                wire:model="emails.{{ $index }}.label"
                                                class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                                            />
                                        </div>
                                        <div class="flex-1 space-y-2">
                                            <label class="text-sm text-zinc-700" for="emailValue{{ $index }}">Correo</label>
                                            <input
                                                id="emailValue{{ $index }}"
                                                type="email"
                                                wire:model="emails.{{ $index }}.email"
                                                class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                                            />
                                        </div>
                                        <input type="hidden" wire:model="emails.{{ $index }}.sort_order" />
                                        <button
                                            type="button"
                                            wire:click="deleteEmail({{ (int) $row['id'] }})"
                                            class="ml-auto inline-flex items-center justify-center rounded-full p-2 text-red-600 hover:bg-red-50"
                                            aria-label="Eliminar"
                                        >
                                            <flux:icon.trash variant="outline" class="size-5" />
                                        </button>
                                    </div>
                                @empty
                                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 text-xs font-mono text-zinc-500">
                                        No hay correos aún.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="grid gap-3 md:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-sm text-zinc-700" for="newPhone">Nuevo teléfono</label>
                                    <input
                                        id="newPhone"
                                        type="text"
                                        wire:model="newPhone"
                                        class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm text-zinc-700" for="newWhatsappUrl">WhatsApp (URL o número)</label>
                                    <input
                                        id="newWhatsappUrl"
                                        type="text"
                                        wire:model="newWhatsappUrl"
                                        class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                                    />
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button
                                    type="button"
                                    wire:click="addPhone"
                                    class="inline-flex h-11 items-center justify-center rounded-full border border-[#E98332] bg-white px-5 text-sm font-bold text-[#E98332] shadow-sm hover:bg-orange-50"
                                >
                                    Agregar teléfono
                                </button>
                            </div>

                            <div class="space-y-3">
                                @forelse ($phones as $index => $row)
                                    <div class="flex flex-col gap-3 rounded-2xl border border-gray-100 bg-gray-50 p-4">
                                        <div class="flex items-start gap-3">
                                            <flux:icon.bars-2 variant="outline" class="mt-3 size-5 text-zinc-400" />
                                            <div class="grid flex-1 gap-3 md:grid-cols-2">
                                                <div class="space-y-2">
                                                    <label class="text-sm text-zinc-700" for="phoneValue{{ $index }}">Teléfono</label>
                                                    <input
                                                        id="phoneValue{{ $index }}"
                                                        type="text"
                                                        wire:model="phones.{{ $index }}.phone"
                                                        class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                                                    />
                                                </div>
                                                <div class="space-y-2">
                                                    <label class="text-sm text-zinc-700" for="phoneWa{{ $index }}">WhatsApp (URL o número)</label>
                                                    <input
                                                        id="phoneWa{{ $index }}"
                                                        type="text"
                                                        wire:model="phones.{{ $index }}.whatsapp_url"
                                                        class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                                                    />
                                                </div>
                                            </div>
                                            <input type="hidden" wire:model="phones.{{ $index }}.sort_order" />
                                            <button
                                                type="button"
                                                wire:click="deletePhone({{ (int) $row['id'] }})"
                                                class="ml-auto inline-flex items-center justify-center rounded-full p-2 text-red-600 hover:bg-red-50"
                                                aria-label="Eliminar"
                                            >
                                                <flux:icon.trash variant="outline" class="size-5" />
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 text-xs font-mono text-zinc-500">
                                        No hay teléfonos aún.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-gray-100 bg-white p-8 shadow-lg">
                    <div class="text-lg font-bold text-zinc-900">Botón flotante de WhatsApp</div>
                    <div class="mt-1 text-xs font-mono text-zinc-500">Configura el número y el mensaje prellenado del botón.</div>

                    <div class="mt-6 grid gap-4 lg:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-sm text-zinc-700" for="whatsappNumber">Número (con lada)</label>
                            <input
                                id="whatsappNumber"
                                type="text"
                                wire:model="whatsappNumber"
                                class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                            />
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm text-zinc-700" for="whatsappMessage">Mensaje</label>
                            <textarea
                                id="whatsappMessage"
                                rows="3"
                                wire:model="whatsappMessage"
                                class="w-full cursor-text rounded-lg border border-gray-100 bg-white p-3 font-mono text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-[#E98332]/60 focus:outline-hidden"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="rounded-3xl border border-gray-100 bg-white p-8 shadow-lg">
                <div class="text-lg font-bold text-zinc-900">Imagen del hero</div>
                <div class="mt-1 text-xs font-mono text-zinc-500">Formatos permitidos: jpg, png, webp. Máximo 5 MB.</div>

                @if ($heroImage)
                    <img src="{{ $heroImage->temporaryUrl() }}" alt="Hero" class="mt-6 h-40 w-full rounded-2xl border border-gray-100 bg-gray-50 object-cover" loading="lazy" />
                @elseif ($currentHeroImagePath)
                    <img src="{{ asset($currentHeroImagePath) }}" alt="Hero" class="mt-6 h-40 w-full rounded-2xl border border-gray-100 bg-gray-50 object-cover" loading="lazy" />
                @else
                    <div class="mt-6 flex h-40 items-center justify-center rounded-2xl border border-gray-100 bg-gray-50 px-6 text-xs font-mono text-zinc-500">
                        No hay imagen cargada todavía.
                    </div>
                @endif

                <div
                    class="relative mt-6 flex h-40 cursor-pointer flex-col justify-center rounded-2xl border border-dashed border-gray-200 bg-white px-6 text-xs font-mono text-zinc-500 shadow-sm"
                    x-data="{ dragging: false }"
                    x-on:dragover.prevent="dragging = true"
                    x-on:dragleave.prevent="dragging = false"
                    x-on:drop.prevent="
                        dragging = false;
                        $refs.heroImageInput.files = $event.dataTransfer.files;
                        $refs.heroImageInput.dispatchEvent(new Event('change'));
                    "
                    x-on:click="$refs.heroImageInput.click()"
                    :class="dragging ? 'ring-2 ring-[#E98332]/40' : ''"
                >
                    <div wire:loading.flex wire:target="heroImage,saveContactSection" class="absolute inset-0 z-10 rounded-2xl border border-gray-100 bg-white/70">
                        <div class="m-6 h-[calc(100%-3rem)] w-[calc(100%-3rem)] rounded-xl al-skeleton"></div>
                    </div>
                    <input x-ref="heroImageInput" type="file" class="hidden" wire:model="heroImage" accept="image/jpeg,image/png,image/webp" />

                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center gap-3">
                            <flux:icon.arrow-up-tray variant="outline" class="size-6 text-[#E98332]" />
                            <span>Arrastra y suelta o selecciona una imagen.</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 lg:col-span-2">
                @if ($saved)
                    <div class="text-xs font-mono text-emerald-700">Datos guardados.</div>
                @endif
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-[#008D62] px-6 text-sm font-bold text-white shadow-sm hover:bg-[#007A55]" wire:loading.attr="disabled" wire:target="saveContactSection,heroImage">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .pac-container {
        z-index: 99999 !important;
    }
</style>

<script>
    (function () {
        const googleScriptId = 'google-maps-places-js';

        function ensurePlacesScriptReady(googleKey, onReady) {
            if (window.google && window.google.maps && window.google.maps.places) {
                onReady();
                return;
            }

            if (!googleKey) {
                return;
            }

            if (!document.getElementById(googleScriptId)) {
                const script = document.createElement('script');
                script.id = googleScriptId;
                script.async = true;
                script.defer = true;
                script.src =
                    'https://maps.googleapis.com/maps/api/js?key=' +
                    encodeURIComponent(googleKey) +
                    '&libraries=places&v=weekly&language=es&region=MX';
                document.head.appendChild(script);
            }

            const startedAt = Date.now();
            (function waitForGoogle() {
                if (window.google && window.google.maps && window.google.maps.places) {
                    onReady();
                    return;
                }
                if (Date.now() - startedAt > 8000) return;
                setTimeout(waitForGoogle, 150);
            })();
        }

        function initPlacesSearch() {
            const input = document.getElementById('placesAddressSearch');
            if (!input || input.dataset.placesReady === '1') return;

            const googleKey = (input.dataset.googleMapsKey || '').trim();

            ensurePlacesScriptReady(googleKey, function () {
                if (!window.google || !window.google.maps || !window.google.maps.places) return;

                input.dataset.placesReady = '1';

                function findComponent() {
                    const componentEl = input.closest('[wire\\:id]');
                    const componentId = componentEl ? componentEl.getAttribute('wire:id') : null;
                    return componentId && window.Livewire ? window.Livewire.find(componentId) : null;
                }

                let typingTimer = null;
                input.addEventListener('input', function () {
                    if (typingTimer) clearTimeout(typingTimer);
                    typingTimer = setTimeout(function () {
                        const component = findComponent();
                        if (!component) return;
                        component.set('contactAddress', (input.value || '').trim());
                    }, 300);
                });

                const autocomplete = new window.google.maps.places.Autocomplete(input, {
                    fields: ['formatted_address', 'geometry', 'place_id', 'name'],
                });

                autocomplete.addListener('place_changed', function () {
                    const place = autocomplete.getPlace() || {};
                    const formattedAddress = (place.formatted_address || input.value || '').trim();

                    let embedUrl = '';

                    if (place.geometry && place.geometry.location) {
                        const lat = place.geometry.location.lat();
                        const lng = place.geometry.location.lng();
                        embedUrl = 'https://www.google.com/maps?q=' + encodeURIComponent(lat + ',' + lng) + '&output=embed&z=17';
                    } else if (formattedAddress !== '') {
                        embedUrl = 'https://www.google.com/maps?q=' + encodeURIComponent(formattedAddress) + '&output=embed&z=17';
                    }

                    const component = findComponent();

                    if (component && formattedAddress !== '') {
                        component.set('contactAddress', formattedAddress);
                    }

                    if (component && embedUrl !== '') {
                        component.set('mapEmbedUrl', embedUrl);
                    }
                });
            });
        }

        document.addEventListener('DOMContentLoaded', initPlacesSearch);
        document.addEventListener('livewire:navigated', initPlacesSearch);
        document.addEventListener('livewire:init', initPlacesSearch);
    })();
</script>
