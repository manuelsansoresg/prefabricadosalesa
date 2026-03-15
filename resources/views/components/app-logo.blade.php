@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Panel" {{ $attributes }}>
        <x-slot name="logo" class="flex size-10 items-center justify-center overflow-hidden rounded-md bg-white">
            <img src="{{ asset('image/logo_transparente.png') }}" alt="Prefabricados Alesa" class="h-10 w-auto object-contain" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Laravel Starter Kit" {{ $attributes }}>
        <x-slot name="logo" class="flex size-10 items-center justify-center overflow-hidden rounded-md bg-white">
            <img src="{{ asset('image/logo_transparente.png') }}" alt="Prefabricados Alesa" class="h-10 w-auto object-contain" />
        </x-slot>
    </flux:brand>
@endif
