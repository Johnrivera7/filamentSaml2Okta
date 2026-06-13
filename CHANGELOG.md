# Changelog

All notable changes to `johnriveragonzalez/saml2-okta` will be documented in this file.

## [2.0.1] - 2026-06-13

### Changed

- Clean repository history with `master` and `5.x` for Filament 5.x, and `4.x` for Filament 4.x.
- Improved README and package metadata aligned with Packagist.

## [2.0.0] - 2026-06-13

### Added

- Filament panel plugin for SAML2 authentication with Okta and other IdPs.
- Admin pages: settings, certificates, field mapper, and debug logs.
- Automatic user provisioning and SAML attribute mapping.
- Provider login button via Filament render hooks.
- English and Spanish translations.
- Filament 5.x support on `master` and `5.x` branches.
- Filament 4.x support on `4.x` branch.

### Changed

- Rewritten for Filament 4.x/5.x plugin standards (`PackageServiceProvider`, Schema API).
- Requires PHP 8.2+ and Filament 4.x or 5.x.

### Note

- Versions `1.x` on Packagist remain available for legacy Filament 3.x installs.
