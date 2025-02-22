<x-filament-panels::page>
    <x-filament-panels::form id="form" wire:submit="save">
        {{ $this->form }}

        <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="$this->hasFullWidthFormActions()" />
    </x-filament-panels::form>

    <h2 class="fi-header-heading text-xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-2xl">Members
    </h2>
    {{ $this->table }}
</x-filament-panels::page>
