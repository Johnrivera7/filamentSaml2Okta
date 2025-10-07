<x-filament-panels::page>
    <div class="space-y-6">
        @if(!$debugMode)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            Modo Debug Desactivado
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>Para ver los logs de debug, primero debes activar el modo debug en la configuración de SAML2.</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            @if(empty($logs))
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">
                                No hay logs disponibles
                            </h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>No se han generado logs de debug aún. Prueba hacer login con Okta para generar logs.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Logs de Debug SAML2 ({{ count($logs) }} registros)
                            </h3>
                            
                            <div class="space-y-3">
                                @foreach($logs as $log)
                                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-sm font-medium text-gray-900">
                                                        {{ $log['file'] }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        {{ date('Y-m-d H:i:s', $log['date']) }}
                                                    </span>
                                                </div>
                                                
                                                @if(isset($log['data']['type']))
                                                    <div class="mt-1">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                            @if($log['data']['type'] === 'request') bg-blue-100 text-blue-800
                                                            @elseif($log['data']['type'] === 'response') bg-green-100 text-green-800
                                                            @elseif($log['data']['type'] === 'user') bg-purple-100 text-purple-800
                                                            @elseif($log['data']['type'] === 'error') bg-red-100 text-red-800
                                                            @else bg-gray-100 text-gray-800
                                                            @endif">
                                                            {{ ucfirst($log['data']['type']) }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <div class="flex-shrink-0">
                                                <button 
                                                    onclick="toggleLog('{{ $loop->index }}')"
                                                    class="text-sm text-blue-600 hover:text-blue-800">
                                                    Ver detalles
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div id="log-{{ $loop->index }}" class="hidden mt-3">
                                            <div class="bg-gray-50 rounded-md p-3">
                                                <pre class="text-xs text-gray-700 whitespace-pre-wrap">{{ json_encode($log['data'], JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>

    <script>
        function toggleLog(index) {
            const logElement = document.getElementById('log-' + index);
            if (logElement.classList.contains('hidden')) {
                logElement.classList.remove('hidden');
            } else {
                logElement.classList.add('hidden');
            }
        }
    </script>
</x-filament-panels::page>
