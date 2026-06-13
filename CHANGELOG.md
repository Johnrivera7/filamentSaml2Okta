# Changelog

All notable changes to this project will be documented in this file.

## [2.0.0] - 2026-06-13

### Added

- Soporte oficial para Filament v5 siguiendo las [guías de plugins de Filament](https://filamentphp.com/docs/5.x/plugins/getting-started).
- Rama `4.x` con soporte para Filament v4.
- `Saml2OktaServiceProvider` basado en `Spatie\LaravelPackageTools\PackageServiceProvider`.
- Método estático `Saml2OktaPlugin::get()` para acceder a la configuración del plugin.

### Changed

- Requisitos mínimos: PHP 8.2+, Laravel 11+, Filament 5.x (rama `main`).
- Páginas del panel migradas a la API `Schema` de Filament v4/v5.
- El render hook del botón SAML2 se registra en `boot()` del plugin, no en `register()`.
- Eliminados logs de depuración del plugin en producción.

### Removed

- Clase duplicada `src/Filament/Saml2OktaPlugin.php`.
- Dependencia directa de Filament 3.x en la rama principal.

## [1.x] - Historial anterior

Versiones anteriores compatibles con Filament 3.x disponibles en tags `1.x`.
