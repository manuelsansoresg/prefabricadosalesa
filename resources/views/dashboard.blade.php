<x-layouts::app title="Inicio">
    <div class="mx-auto w-full max-w-5xl">
        <div class="rounded-2xl border border-zinc-200 bg-white p-6">
            <flux:heading size="lg">Bienvenido al panel</flux:heading>
            <flux:subheading>Accesos rápidos para administrar el sitio.</flux:subheading>
        </div>

        @if (auth()->user()->hasRole('admin'))
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('admin.about') }}" wire:navigate class="rounded-2xl border border-zinc-200 bg-white p-5 hover:bg-zinc-50">
                    <flux:heading size="sm">Nosotros</flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-600">Encabezado, misión, historia, tarjetas e imagen.</flux:text>
                </a>
                <a href="{{ route('admin.products') }}" wire:navigate class="rounded-2xl border border-zinc-200 bg-white p-5 hover:bg-zinc-50">
                    <flux:heading size="sm">Productos</flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-600">Catálogo: imagen, título y descripción.</flux:text>
                </a>
                <a href="{{ route('admin.gallery') }}" wire:navigate class="rounded-2xl border border-zinc-200 bg-white p-5 hover:bg-zinc-50">
                    <flux:heading size="sm">Galería</flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-600">Sube y elimina imágenes.</flux:text>
                </a>
                <a href="{{ route('admin.site') }}" wire:navigate class="rounded-2xl border border-zinc-200 bg-white p-5 hover:bg-zinc-50">
                    <flux:heading size="sm">Sitio</flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-600">Imagen del hero y datos de contacto.</flux:text>
                </a>
            </div>
        @endif

        <div class="mt-6 rounded-2xl border border-zinc-200 bg-white p-6">
            <flux:heading size="sm">Cuenta</flux:heading>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <a href="{{ route('profile.edit') }}" wire:navigate class="rounded-xl border border-zinc-200 bg-white p-4 hover:bg-zinc-50">
                    <flux:heading size="sm">Perfil</flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-600">Actualiza tus datos y correo.</flux:text>
                </a>
                <a href="{{ route('security.edit') }}" wire:navigate class="rounded-xl border border-zinc-200 bg-white p-4 hover:bg-zinc-50">
                    <flux:heading size="sm">Seguridad</flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-600">Contraseña y autenticación de dos factores.</flux:text>
                </a>
            </div>
        </div>
    </div>
</x-layouts::app>
