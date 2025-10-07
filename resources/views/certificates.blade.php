<x-filament-panels::page>
    <div class="space-y-6">
        @if(!$certificateInfo)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">
                            No hay certificados generados
                        </h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>No se han generado certificados SAML2 aún. Ve a la configuración para generar certificados.</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="space-y-4">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Información del Certificado
                        </h3>
                        
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Dominio</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $domain ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Organización</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $organization ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Válido desde</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $certificateInfo['valid_from'] ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Válido hasta</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $certificateInfo['valid_to'] ?? 'N/A' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                @if($certificateContent)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Certificado X.509
                            </h3>
                            
                            <div class="bg-gray-50 rounded-md p-3">
                                <pre class="text-xs text-gray-700 whitespace-pre-wrap">{{ $certificateContent }}</pre>
                            </div>
                            
                            <div class="mt-4">
                                <button 
                                    onclick="downloadCertificate()"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Descargar Certificado
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                @if($privateKeyContent)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Clave Privada
                            </h3>
                            
                            <div class="bg-gray-50 rounded-md p-3">
                                <pre class="text-xs text-gray-700 whitespace-pre-wrap">{{ $privateKeyContent }}</pre>
                            </div>
                            
                            <div class="mt-4">
                                <button 
                                    onclick="downloadPrivateKey()"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Descargar Clave Privada
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <script>
        function downloadCertificate() {
            const content = @json($certificateContent ?? '');
            const filename = 'saml_certificate_{{ $domain ?? "cert" }}.pem';
            downloadTextAsFile(content, filename);
        }

        function downloadPrivateKey() {
            const content = @json($privateKeyContent ?? '');
            const filename = 'saml_private_key_{{ $domain ?? "key" }}.pem';
            downloadTextAsFile(content, filename);
        }

        function downloadTextAsFile(content, filename) {
            const element = document.createElement('a');
            const file = new Blob([content], {type: 'text/plain'});
            element.href = URL.createObjectURL(file);
            element.download = filename;
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);
        }
    </script>
</x-filament-panels::page>
