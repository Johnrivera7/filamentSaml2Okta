<div class="mt-6" data-saml2-button="true">
    <div class="relative">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-gray-500">O continúa con</span>
        </div>
    </div>
    <div class="mt-6">
        <a href="{{ $loginUrl }}" 
           class="w-full flex items-center justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200 whitespace-nowrap">
            <x-dynamic-component :component="'heroicon-o-' . $buttonIcon" class="w-5 h-5 mr-2" />
            <span class="truncate">{{ $buttonLabel }}</span>
        </a>
    </div>
</div>
