# Filament SAML2 Okta Plugin

Un plugin completo para Laravel Filament que proporciona autenticación SAML2 con Okta y otros proveedores de identidad.

## 🚀 Características

- ✅ **Autenticación SAML2 completa** con Okta y otros proveedores
- ✅ **Interfaz de administración** integrada en Filament
- ✅ **Generación automática de certificados** SAML2
- ✅ **Modo debug** con logging detallado
- ✅ **Mapeador visual de campos** SAML a User
- ✅ **Botón de login dinámico** con iconos personalizados
- ✅ **Iconos de proveedores** (Okta, Microsoft, Google, Auth0)
- ✅ **Soporte para Heroicons** y SVG personalizados
- ✅ **Configuración desde base de datos** (no archivos .env)
- ✅ **Instalación completamente automática**
- ✅ **Soporte multiidioma** (Inglés y Español)
- ✅ **Cumple normas de plugins de Filament**

## 📋 Requisitos

- Laravel 10+
- Filament 3.x
- PHP 8.1+

## 🛠️ Instalación

### 1. Instalar el plugin

```bash
composer require johnriveragonzalez/saml2-okta
```

### 2. Publicar y ejecutar migraciones

```bash
php artisan vendor:publish --tag="saml2-okta-migrations"
php artisan migrate
```

### 3. Registrar el plugin en Filament

Agregar en `app/Providers/Filament/AdminPanelProvider.php`:

```php
use JohnRiveraGonzalez\Saml2Okta\Saml2OktaPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            // ... otros plugins
            Saml2OktaPlugin::make(),
        ]);
}
```

### 4. Ejecutar comandos de instalación

```bash
php artisan saml2-okta:install
```

Este comando ejecutará automáticamente:
- ✅ Extensión del modelo User
- ✅ Extensión del UserResource
- ✅ Registro de middleware
- ✅ Configuración inicial

### 5. Publicar traducciones (opcional)

```bash
php artisan vendor:publish --tag="saml2-okta-translations"
```

### 6. Publicar configuración (opcional)

```bash
php artisan vendor:publish --tag="saml2-okta-config"
```

## ⚙️ Configuración

### 1. Acceder a la configuración

Una vez instalado, ve a **SAML2 > Configuración SAML2** en el panel de Filament.

### 2. Configurar Okta

1. **Configuración de Okta:**
   - `Client ID`: ID de tu aplicación en Okta
   - `Client Secret`: Secreto de tu aplicación
   - `IDP Entity ID`: Entity ID de Okta
   - `IDP SSO URL`: URL de SSO de Okta
   - `IDP X.509 Certificate`: Certificado de Okta

2. **Configuración de la aplicación:**
   - `SP Entity ID`: Se genera automáticamente
   - `Callback URL`: Se genera automáticamente
   - `SP X.509 Certificate`: Se genera automáticamente
   - `SP Private Key`: Se genera automáticamente

### 3. Configurar usuarios

- **Auto crear usuarios**: Crear usuarios automáticamente al hacer login
- **Auto actualizar usuarios**: Actualizar datos de usuarios existentes
- **Marcar como externos**: Marcar usuarios SAML2 como externos
- **Rol por defecto**: Rol asignado a nuevos usuarios

### 4. Configurar interfaz

- **Activar autenticación SAML2**: Habilitar/deshabilitar el botón de login
- **Etiqueta del botón**: Texto del botón (ej: "Iniciar sesión con Okta")
- **Icono del botón**: Seleccionar icono de proveedor o Heroicon

#### **Iconos de Proveedores Disponibles:**
- **Okta** - Icono oficial de Okta
- **Microsoft** - Icono oficial de Microsoft  
- **Google** - Icono oficial de Google
- **Auth0** - Icono oficial de Auth0

#### **Heroicons Disponibles:**
- **Shield Check** - `heroicon-o-shield-check`
- **Lock Closed** - `heroicon-o-lock-closed`
- **Key** - `heroicon-o-key`
- **Rocket Launch** - `heroicon-o-rocket-launch`
- **User** - `heroicon-o-user`
- **Login** - `heroicon-o-login`
- **Identification** - `heroicon-o-identification`
- **Finger Print** - `heroicon-o-finger-print`

## 🔧 Funcionalidades

### Gestión de Certificados

- **Generar certificados**: Crear certificados SAML2 automáticamente
- **Regenerar certificados**: Crear nuevos certificados
- **Ver metadatos**: Acceder a la URL de metadatos SAML2
- **Descargar certificados**: Exportar certificados para Okta

### Modo Debug

- **Activar debug**: Habilitar logging detallado
- **Ver logs**: Revisar logs de autenticación SAML2
- **Analizar campos**: Ver qué campos envía Okta

### Mapeador de Campos

- **Mapeo visual**: Configurar qué campos SAML van a qué campos User
- **Datos reales**: Ver datos reales de Okta para configurar mapeos
- **Transformaciones**: Aplicar transformaciones a los datos

## 🌐 Proveedores Compatibles

Este plugin funciona con cualquier proveedor SAML2, incluyendo:

### **Proveedores Principales (con iconos personalizados):**
- ✅ **Okta** - Icono oficial incluido
- ✅ **Microsoft / Azure AD** - Icono oficial incluido
- ✅ **Google Workspace** - Icono oficial incluido
- ✅ **Auth0** - Icono oficial incluido

### **Otros Proveedores SAML2:**
- ✅ **OneLogin** - Compatible (usa Heroicons)
- ✅ **Ping Identity** - Compatible (usa Heroicons)
- ✅ **Shibboleth** - Compatible (usa Heroicons)
- ✅ **ADFS (Active Directory Federation Services)** - Compatible (usa Heroicons)
- ✅ **Cualquier proveedor SAML2 estándar** - Compatible (usa Heroicons)

### **Nota sobre Auth0:**
Auth0 es principalmente un proveedor OAuth/OIDC, pero también soporta SAML2. Si usas Auth0 con SAML2, el plugin funcionará perfectamente y tendrás el icono oficial de Auth0 disponible.

## 📁 Estructura del Plugin

```
packages/johnriveragonzalez/saml2-okta/
├── src/
│   ├── Commands/           # Comandos Artisan
│   ├── Controllers/        # Controladores SAML2
│   ├── Models/            # Modelos de datos
│   ├── Pages/             # Páginas de Filament
│   ├── Services/          # Servicios de negocio
│   └── Saml2OktaPlugin.php # Plugin principal
├── database/migrations/    # Migraciones
├── resources/views/       # Vistas Blade
└── routes/               # Rutas web
```

## 🌐 Multiidioma

El plugin incluye soporte para múltiples idiomas:

- **Inglés** (en) - Idioma por defecto
- **Español** (es) - Traducción completa

### Cambiar idioma

1. **Publicar traducciones:**
   ```bash
   php artisan vendor:publish --tag="saml2-okta-translations"
   ```

2. **Configurar idioma en Laravel:**
   ```php
   // config/app.php
   'locale' => 'es', // Para español
   ```

3. **Personalizar traducciones:**
   Edita los archivos en `lang/vendor/saml2-okta/`

## 🔄 Comandos Disponibles

```bash
# Instalación completa
php artisan saml2-okta:install

# Extender modelo User
php artisan saml2-okta:extend-user-model

# Extender UserResource
php artisan saml2-okta:extend-user-resource

# Registrar middleware
php artisan saml2-okta:register-middleware

# Desregistrar middleware
php artisan saml2-okta:unregister-middleware
```

## 🚨 Notas Importantes

- **El plugin es completamente automático** - No requiere cambios manuales en archivos
- **Configuración en base de datos** - No usa archivos .env para configuración SAML2
- **Extensión automática** - Extiende User model y UserResource automáticamente
- **Middleware automático** - Registra middleware para inyectar botón de login

## 🎨 Personalización de Iconos

### **Agregar Nuevos Iconos de Proveedores:**

1. **Crear archivo SVG:**
   ```bash
   # Crear en resources/views/icons/
   touch resources/views/icons/tu-proveedor.blade.php
   ```

2. **Estructura del archivo:**
   ```php
   {{-- Tu Proveedor Icon --}}
   <svg viewBox="0 0 24 24" class="w-5 h-5 {{ $class ?? '' }}" fill="currentColor">
       <!-- Tu SVG aquí -->
   </svg>
   ```

3. **Actualizar opciones en configuración:**
   ```php
   Select::make('button_icon')
       ->options([
           'okta' => 'Okta',
           'microsoft' => 'Microsoft',
           'google' => 'Google',
           'auth0' => 'Auth0',
           'tu-proveedor' => 'Tu Proveedor', // ← Agregar aquí
           // ...
       ])
   ```

### **Usar Heroicons:**
```php
'button_icon' => 'heroicon-o-rocket-launch'
'button_label' => 'Iniciar sesión con SAML2'
```

### **Usar Iconos de Proveedores:**
```php
'button_icon' => 'okta'
'button_label' => 'Iniciar sesión con Okta'
```

## 🔮 Futuras Mejoras

- [ ] **Soporte genérico** - Renombrar a `filament-saml2` para soporte universal
- [ ] **Más iconos de proveedores** - OneLogin, Ping Identity, Shibboleth, etc.
- [ ] **Temas personalizables** - Personalizar apariencia del botón de login
- [ ] **Múltiples proveedores** - Soporte para varios proveedores SAML2 simultáneos
- [ ] **Iconos personalizados** - Subir iconos SVG desde la interfaz

## 📄 Licencia

MIT License

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor, abre un issue o pull request.

## 📞 Soporte

Si tienes problemas o preguntas, por favor abre un issue en el repositorio.

---

**Nota**: Este plugin está optimizado para Okta pero funciona con cualquier proveedor SAML2 estándar.