<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    Mapeador de Campos SAML2
                </h3>
                
                <div class="text-sm text-gray-600 mb-6">
                    <p>Configura cómo se mapean los campos que envía Okta a tu modelo User de Laravel.</p>
                </div>

                @if(empty($fieldMappings))
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">
                                    No hay mapeos configurados
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>Ve a la configuración de SAML2 para configurar los mapeos de campos.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Campos del Modelo User</h4>
                            <p class="text-sm text-gray-600">{{ $userFields ?? 'No disponibles' }}</p>
                        </div>

                        @if(!empty($sampleSamlData))
                            <div class="bg-blue-50 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-blue-900 mb-2">Datos de Ejemplo de Okta</h4>
                                <div class="bg-white rounded-md p-3">
                                    <pre class="text-xs text-gray-700 whitespace-pre-wrap">{{ json_encode($sampleSamlData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        @endif

                        <div class="bg-green-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-green-900 mb-2">Mapeos Configurados</h4>
                            <div class="space-y-2">
                                @foreach($fieldMappings as $mapping)
                                    <div class="flex items-center justify-between bg-white rounded-md p-3 border">
                                        <div class="flex-1">
                                            <span class="text-sm font-medium text-gray-900">{{ $mapping['saml_field'] ?? 'N/A' }}</span>
                                            <span class="text-sm text-gray-500 mx-2">→</span>
                                            <span class="text-sm text-gray-900">{{ $mapping['user_field'] ?? 'N/A' }}</span>
                                            @if($mapping['required'] ?? false)
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                    Requerido
                                                </span>
                                            @endif
                                        </div>
                                        @if($mapping['default_value'] ?? null)
                                            <div class="text-xs text-gray-500">
                                                Default: {{ $mapping['default_value'] }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
