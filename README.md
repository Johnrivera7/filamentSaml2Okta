# Filament SAML2 Okta

<div align="center">

![Filament SAML2 Okta](docs/logo%20header%20principal%20plugin.png)

**Complete SAML2 SSO authentication for Filament panels — optimized for Okta, compatible with any SAML2 identity provider.**

[![Latest Version on Packagist](https://img.shields.io/packagist/v/johnriveragonzalez/saml2-okta.svg?style=flat-square)](https://packagist.org/packages/johnriveragonzalez/saml2-okta)
[![License](https://img.shields.io/packagist/l/johnriveragonzalez/saml2-okta.svg?style=flat-square)](LICENSE)
[![Filament](https://img.shields.io/badge/Filament-4.x%20%7C%205.x-ffb020?style=flat-square)](https://filamentphp.com)

</div>

Panel plugin that adds SAML2 single sign-on to your Filament admin panel. Configure IdP credentials, certificates, user mapping, and a login button — all from the Filament UI. Database-driven configuration (no SAML secrets in `.env`).

Built following the official [Filament plugin guidelines](https://filamentphp.com/docs/5.x/plugins/getting-started): `PackageServiceProvider`, `Filament\Contracts\Plugin`, and Schema-based panel pages.

---

## Features

| Feature | Description |
|---------|-------------|
| **SAML2 authentication** | Full SP-initiated flow with Okta, Azure AD, Google Workspace, Auth0, and any standard SAML2 IdP |
| **Filament admin UI** | Settings, certificates, field mapper, and debug pages inside your panel |
| **Auto certificates** | Generate and regenerate SP X.509 certificates from the panel |
| **User provisioning** | Auto-create/update users, default roles, external-user flag |
| **Field mapper** | Visual mapping from SAML attributes to your `User` model |
| **Login button** | Injected via render hook with provider icons (Okta, Microsoft, Google, Auth0) or Heroicons |
| **Debug mode** | Detailed SAML logs for troubleshooting |
| **Translations** | English and Spanish included |
| **Dark mode** | Compatible with Filament light/dark themes |

---

## Requirements

| Branch | Filament | Laravel | PHP |
|--------|----------|---------|-----|
| `master` / `5.x` | 5.x | 11+ / 12+ | 8.2+ |
| `4.x` | 4.x | 11+ | 8.2+ |

---

## Installation

### 1. Install via Composer

```bash
# Filament 5.x
composer require johnriveragonzalez/saml2-okta:^2.0

# Filament 4.x
composer require johnriveragonzalez/saml2-okta:^2.0 --prefer-source
# Require branch 4.x in your composer.json if needed
```

### 2. Publish migrations and migrate

```bash
php artisan vendor:publish --tag="saml2-okta-migrations"
php artisan migrate
```

### 3. Register the plugin

In `app/Providers/Filament/AdminPanelProvider.php`:

```php
use JohnRiveraGonzalez\Saml2Okta\Saml2OktaPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            Saml2OktaPlugin::make(),
        ]);
}
```

### 4. Run the installer

```bash
php artisan saml2-okta:install
```

This command extends your `User` model, updates `UserResource`, and completes the initial setup.

### 5. Optional publishes

```bash
php artisan vendor:publish --tag="saml2-okta-translations"
php artisan vendor:publish --tag="saml2-okta-config"
```

---

## Configuration

Open **SAML2 → Configuración SAML2** in your Filament panel.

### Main settings

![Main SAML2 settings](docs/config%20usuarios%20debug%20certificados.png)

### Identity provider (Okta / IdP)

![Identity provider configuration](docs/config%20basica%20y%20proveedor%20de%20identidad%20.png)

| Field | Description |
|-------|-------------|
| Client ID | Application client ID from your IdP |
| Client Secret | Application secret (optional update on save) |
| IDP Entity ID | Entity ID from Okta or your IdP |
| IDP SSO URL | Single sign-on URL |
| IDP X.509 Certificate | IdP public certificate |

### Service provider (your app)

![Service provider configuration](docs/config%20del%20proveedor%20de%20servicio.png)

| Field | Description |
|-------|-------------|
| SP Entity ID | Auto-generated metadata URL |
| Callback URL | `https://your-domain.com/saml2/callback` |
| SP Certificate / Private Key | Generate from the panel or paste your own |

### User settings

- Auto-create users on first login
- Auto-update existing users
- Default role for new SAML users
- Mark users as external

### Login button

![Field mapping and UI settings](docs/config%20mapeo%20de%20campos.png)

- Toggle SAML2 login on/off
- Custom button label and icon (Okta, Microsoft, Google, Auth0, or Heroicons)

### Debug & field mapper

![Debug logs page](docs/pagina%20debug%20ver%20logs.png)

- Enable debug logging to inspect SAML attributes
- Map IdP fields to `User` columns with live sample data

---

## Production checklist

1. Use **HTTPS** — SAML2 requires TLS in production.
2. Set `APP_URL` to your real domain.
3. Register callback URL in your IdP: `https://your-domain.com/saml2/callback`
4. Upload SP metadata or certificate to your IdP.
5. Disable debug mode after testing.
6. Run `php artisan optimize` after deployment.

---

## Artisan commands

```bash
php artisan saml2-okta:install
php artisan saml2-okta:extend-user-model
php artisan saml2-okta:extend-user-resource
php artisan saml2-okta:unregister-middleware   # legacy upgrades only
```

---

## Package structure

```
src/
├── Commands/
├── Controllers/
├── Models/
├── Pages/
├── Services/
├── Saml2OktaPlugin.php
└── Saml2OktaServiceProvider.php
database/migrations/
resources/views/
routes/web.php
lang/en|es/
```

---

## Compatible identity providers

**With bundled icons:** Okta, Microsoft / Azure AD, Google Workspace, Auth0

**Also compatible:** OneLogin, Ping Identity, Shibboleth, ADFS, and any SAML2-compliant IdP.

---

## Contributing

Issues and pull requests are welcome at [github.com/Johnrivera7/filamentSaml2Okta](https://github.com/Johnrivera7/filamentSaml2Okta).

---

## License

MIT © [John Rivera Gonzalez](https://github.com/Johnrivera7)

---

## Español

Plugin completo de autenticación SAML2 para paneles Filament. Instalación, configuración de Okta/IdP, certificados, mapeo de campos y botón de login desde la interfaz de administración. Compatible con Filament 4.x y 5.x. Ver secciones anteriores para instalación detallada.
