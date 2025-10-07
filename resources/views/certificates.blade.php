<x-filament-panels::page>
    <form wire:submit.prevent="save">
        {{ $this->form }}
    </form>

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('copy-to-clipboard', (event) => {
                const content = event.content || event[0]?.content || '';
                
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(content)
                        .then(() => {
                            console.log('Contenido copiado al portapapeles');
                        })
                        .catch(err => {
                            console.error('Error al copiar:', err);
                            // Fallback para navegadores antiguos
                            fallbackCopyToClipboard(content);
                        });
                } else {
                    // Fallback para navegadores antiguos
                    fallbackCopyToClipboard(content);
                }
            });
        });

        function fallbackCopyToClipboard(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                console.log('Contenido copiado usando fallback');
            } catch (err) {
                console.error('Error al copiar usando fallback:', err);
            }
            
            document.body.removeChild(textArea);
        }
    </script>
    @endpush
</x-filament-panels::page>
