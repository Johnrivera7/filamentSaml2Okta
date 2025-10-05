<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <x-filament-actions::modals />
    </form>
</x-filament-panels::page>
