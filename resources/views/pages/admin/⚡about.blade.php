<?php

use App\Models\AboutContent;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Nosotros')] class extends Component {
    public string $body = '';

    public function mount(): void
    {
        $about = AboutContent::query()->first();

        if ($about) {
            $this->body = $about->body;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $about = AboutContent::query()->first();

        if (! $about) {
            AboutContent::query()->create([
                'body' => $validated['body'],
            ]);

            return;
        }

        $about->update([
            'body' => $validated['body'],
        ]);
    }
}; ?>

<div class="flex flex-col gap-6">
    <div>
        <flux:heading>Nosotros</flux:heading>
        <flux:subheading>Gestiona el texto principal de la sección “Nosotros”.</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:textarea wire:model="body" label="Texto" rows="10" required />

        <div class="flex justify-end">
            <flux:button variant="primary" type="submit">Guardar</flux:button>
        </div>
    </form>
</div>
