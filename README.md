# Filament SAML2 Okta Plugin

<div align="center">
  <img src="https://raw.githubusercontent.com/Johnrivera7/filamentSaml2Okta/master/docs/logo%20header%20principal%20plugin.png" alt="Filament SAML2 Plugin" width="600">
</div>

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

### Instalación en Desarrollo

#### 1. Instalar el plugin

```bash
composer require johnriveragonzalez/saml2-okta
```

#### 2. Publicar y ejecutar migraciones

```bash
php artisan vendor:publish --tag="saml2-okta-migrations"
php artisan migrate
```

#### 3. Registrar el plugin en Filament

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

#### 4. Ejecutar comandos de instalación

```bash
php artisan saml2-okta:install
```

Este comando ejecutará automáticamente:
- ✅ Extensión del modelo User
- ✅ Extensión del UserResource
- ✅ Registro de middleware
- ✅ Configuración inicial

#### 5. Publicar traducciones (opcional)

```bash
php artisan vendor:publish --tag="saml2-okta-translations"
```

#### 6. Publicar configuración (opcional)

```bash
php artisan vendor:publish --tag="saml2-okta-config"
```

---

### 🚀 Instalación en Producción

#### Opción 1: Instalación Automática (Recomendada)

```bash
# 1. Instalar el plugin
composer require johnriveragonzalez/saml2-okta --no-dev --optimize-autoloader

# 2. Publicar y ejecutar migraciones
php artisan vendor:publish --tag="saml2-okta-migrations"
php artisan migrate --force

# 3. Ejecutar instalación automática
php artisan saml2-okta:install

# 4. Optimizar aplicación
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

#### Opción 2: Instalación Manual Paso a Paso

```bash
# 1. Instalar dependencias
composer require johnriveragonzalez/saml2-okta --no-dev --optimize-autoloader

# 2. Publicar migraciones
php artisan vendor:publish --tag="saml2-okta-migrations"

# 3. Ejecutar migraciones
php artisan migrate --force

# 4. Extender modelo User
php artisan saml2-okta:extend-user-model

# 5. Extender UserResource
php artisan saml2-okta:extend-user-resource

# 6. Registrar plugin en AdminPanelProvider.php (ver paso 3 arriba)

# 7. Optimizar aplicación
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

#### ⚠️ Consideraciones Importantes para Producción

1. **Permisos de Archivos:**
   ```bash
   # Asegurar que storage y cache sean escribibles
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

2. **Variables de Entorno:**
   - Asegúrate de tener `APP_ENV=production` en tu archivo `.env`
   - Configura `APP_DEBUG=false`
   - Usa `APP_URL` con tu dominio real (HTTPS)

3. **HTTPS Obligatorio:**
   - SAML2 requiere HTTPS en producción
   - Configura SSL/TLS en tu servidor web
   - El callback URL debe usar `https://`

4. **Optimización:**
   ```bash
   # Después de cualquier cambio en producción, ejecutar:
   php artisan optimize:clear
   php artisan optimize
   ```

5. **Base de Datos:**
   - Usa conexión segura a la base de datos
   - Realiza backup antes de ejecutar migraciones
   - Verifica que la tabla `saml2_okta_configs` se haya creado correctamente

6. **Logs y Debug:**
   - En producción, desactiva el modo debug del plugin después de configurar
   - Los logs se guardan en `storage/logs/saml2-debug-YYYY-MM-DD.log`
   - Configura rotación de logs para evitar llenar el disco

#### 🔒 Post-Instalación en Producción

1. **Configurar Okta/Proveedor SAML2:**
   - Accede a `/admin/saml2-settings`
   - Configura los datos de tu proveedor de identidad
   - Genera los certificados SAML2
   - Descarga el certificado SP para subirlo a Okta

2. **Verificar Callback URL:**
   - Debe ser: `https://tu-dominio.com/saml2/callback`
   - Configúralo en tu proveedor SAML2 (Okta, Azure AD, etc.)

3. **Probar la Autenticación:**
   - Usa el modo debug temporalmente para verificar
   - Prueba el flujo completo de login
   - Verifica que los usuarios se creen/actualicen correctamente
   - Desactiva el modo debug después de probar

4. **Configurar Roles y Permisos:**
   - Define el rol por defecto para nuevos usuarios
   - Configura el mapeo de campos SAML
   - Verifica que los usuarios tengan los permisos correctos

#### 📊 Monitoreo en Producción

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log
tail -f storage/logs/saml2-debug-*.log

# Verificar estado de la configuración
php artisan tinker
>>> \JohnRiveraGonzalez\Saml2Okta\Models\Saml2OktaConfig::where('is_active', true)->first()
```

## ⚙️ Configuración

### 1. Acceder a la configuración

Una vez instalado, ve a **SAML2 > Configuración SAML2** en el panel de Filament.

<div align="center">
  <img src="https://raw.githubusercontent.com/Johnrivera7/filamentSaml2Okta/master/docs/config%20usuarios%20debug%20certificados.png" alt="Configuración Principal SAML2" width="800">
</div>

### 2. Configurar Okta

#### **Configuración Básica y del Proveedor de Identidad:**

<div align="center">
  <img src="https://raw.githubusercontent.com/Johnrivera7/filamentSaml2Okta/master/docs/config%20basica%20y%20proveedor%20de%20identidad%20.png" alt="Configuración de Okta" width="800">
</div>

1. **Configuración de Okta:**
   - `Client ID`: ID de tu aplicación en Okta
   - `Client Secret`: Secreto de tu aplicación
   - `IDP Entity ID`: Entity ID de Okta
   - `IDP SSO URL`: URL de SSO de Okta
   - `IDP X.509 Certificate`: Certificado de Okta

#### **Configuración del Proveedor de Servicio:**

<div align="center">
  <img src="https://raw.githubusercontent.com/Johnrivera7/filamentSaml2Okta/master/docs/config%20del%20proveedor%20de%20servicio.png" alt="Configuración del Proveedor de Servicio" width="800">
</div>

2. **Configuración de la aplicación:**
   - `SP Entity ID`: Se genera automáticamente
   - `Callback URL`: Se genera automáticamente
   - `SP X.509 Certificate`: Se genera automáticamente
   - `SP Private Key`: Se genera automáticamente

### 3. Configurar usuarios

<div align="center">
  <img src="https://raw.githubusercontent.com/Johnrivera7/filamentSaml2Okta/master/docs/config%20usuarios%20debug%20certificados.png" alt="Configuración de Usuarios" width="800">
</div>

- **Auto crear usuarios**: Crear usuarios automáticamente al hacer login
- **Auto actualizar usuarios**: Actualizar datos de usuarios existentes
- **Marcar como externos**: Marcar usuarios SAML2 como externos
- **Rol por defecto**: Rol asignado a nuevos usuarios

### 4. Configurar interfaz

<div align="center">
  <img src="https://raw.githubusercontent.com/Johnrivera7/filamentSaml2Okta/master/docs/config%20mapeo%20de%20campos.png" alt="Configuración de la Interfaz" width="800">
</div>

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

<div align="center">
  <img src="https://raw.githubusercontent.com/Johnrivera7/filamentSaml2Okta/master/docs/pagina%20debug%20ver%20logs.png" alt="Debug y Logs SAML2" width="800">
</div>

- **Activar debug**: Habilitar logging detallado
- **Ver logs**: Revisar logs de autenticación SAML2
- **Analizar campos**: Ver qué campos envía Okta

### Mapeador de Campos

<div align="center">
  <img src="https://raw.githubusercontent.com/Johnrivera7/filamentSaml2Okta/master/docs/config%20mapeo%20de%20campos.png" alt="Mapeador de Campos SAML2" width="800">
</div>

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
# Instalación completa (recomendado)
php artisan saml2-okta:install

# Extender modelo User (se ejecuta automáticamente con install)
php artisan saml2-okta:extend-user-model

# Extender UserResource (se ejecuta automáticamente con install)
php artisan saml2-okta:extend-user-resource

# Limpiar middleware obsoleto (solo si actualizas desde versión antigua)
php artisan saml2-okta:unregister-middleware
```

## 🚨 Notas Importantes

- **El plugin es completamente automático** - No requiere cambios manuales en archivos
- **Configuración en base de datos** - No usa archivos .env para configuración SAML2
- **Extensión automática** - Extiende User model y UserResource automáticamente
- **Inyección automática del botón** - Usa Filament Render Hooks (no requiere middleware)
- **Sin modificaciones al Kernel.php** - No necesitas registrar middleware manualmente

### ⚠️ Si actualizas desde una versión anterior:

Si instalaste una versión anterior del plugin que usaba middleware, ejecuta:

```bash
php artisan saml2-okta:unregister-middleware
php artisan config:clear
php artisan optimize
```

Esto limpiará cualquier referencia obsoleta al middleware `InjectSaml2ButtonMiddleware`.

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