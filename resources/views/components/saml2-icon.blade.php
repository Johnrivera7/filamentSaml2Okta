@php
    $iconClass = $class ?? 'w-5 h-5';
    $isHeroicon = str_starts_with($icon, 'heroicon-');
    $isCustomIcon = in_array($icon, ['okta', 'microsoft', 'google', 'auth0']);
@endphp

@if($isHeroicon)
    {{-- Heroicon --}}
    <x-dynamic-component :component="$icon" :class="$iconClass" />
@elseif($isCustomIcon)
    {{-- Custom Provider Icon --}}
    @include("saml2-okta::icons.{$icon}", ['class' => $iconClass])
@else
    {{-- Fallback to shield-check --}}
    <x-heroicon-o-shield-check :class="$iconClass" />
@endif
