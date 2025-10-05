# Filament SAML2 Okta Plugin

Plugin de Filament para autenticación SAML2 con Okta. Permite configurar y gestionar la autenticación SAML2 directamente desde el panel de administración de Filament.

## 📋 Características

- ✅ **Configuración visual**: Interfaz intuitiva para configurar SAML2 desde el panel de Filament
- ✅ **Gestión de certificados**: Soporte para certificados X.509 y claves privadas
- ✅ **Múltiples configuraciones**: Soporte para diferentes configuraciones de Okta
- ✅ **Integración nativa**: Compatible con Laravel Socialite y Filament v3
- ✅ **Logging detallado**: Registro completo para debugging y monitoreo
- ✅ **Control de acceso**: Solo super administradores pueden configurar SAML2
- ✅ **Botón personalizable**: Configuración del botón de login con iconos y etiquetas

## 🚀 Instalación

### 1. Instalar via Composer

```bash
composer require johnriveragonzalez/saml2-okta
```

### 2. Instalar el plugin

```bash
php artisan saml2-okta:install
```

### 3. Ejecutar migraciones

```bash
php artisan migrate
```

### 4. Registrar en Filament

Agrega el plugin a tu `AdminPanelProvider`:

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

## ⚙️ Configuración

### 1. Acceder a la configuración

1. Inicia sesión como **super_admin** en el panel de Filament
2. Navega a **"Autenticación"** en el menú lateral
3. Haz clic en **"Configuración SAML2"**

### 2. Configurar Okta

Completa los siguientes campos:

#### Configuración Básica
- **Nombre de la configuración**: Identificador único
- **Client ID**: ID de cliente de Okta
- **Client Secret**: Secreto de cliente de Okta
- **Callback URL**: URL de callback (ej: `https://tu-dominio.com/saml2/callback`)

#### Configuración del Proveedor de Identidad (Okta)
- **IDP Entity ID**: Entity ID de Okta (ej: `http://www.okta.com/EXK123456`)
- **IDP SSO URL**: URL de inicio de sesión único de Okta
- **IDP SLO URL**: URL de cierre de sesión único (opcional)
- **IDP Metadata URL**: URL de metadatos (opcional)
- **IDP X.509 Certificate**: Certificado de Okta (incluir BEGIN y END CERTIFICATE)

#### Configuración del Proveedor de Servicio (Tu aplicación)
- **SP Entity ID**: Entity ID de tu aplicación (ej: `https://tu-dominio.com/saml2/metadata`)
- **SP X.509 Certificate**: Certificado de tu aplicación
- **SP Private Key**: Clave privada de tu aplicación

#### Configuración de la Interfaz
- **Activar autenticación SAML2**: Toggle para activar/desactivar
- **Etiqueta del botón**: Texto del botón (ej: "Iniciar sesión con Okta")
- **Icono del botón**: Icono de Heroicons (ej: "heroicon-o-shield-check")

### 3. Guardar configuración

Haz clic en **"Guardar configuración"** para aplicar los cambios.

## 🔧 Uso

### Flujo de autenticación

1. El usuario hace clic en "Iniciar sesión con Okta"
2. Se redirige a Okta para autenticación
3. Okta valida las credenciales
4. Okta redirige de vuelta con los datos del usuario
5. El sistema procesa la información y autentica al usuario

### Rutas disponibles

- `GET /saml2/login` - Inicia el flujo SAML2
- `GET /saml2/callback` - Procesa la respuesta de Okta
- `GET /auth/callback` - Alias para compatibilidad

### Personalización del botón

El botón se agrega automáticamente a la página de login de Filament. Puedes personalizar:

- **Etiqueta**: Texto que aparece en el botón
- **Icono**: Icono de Heroicons a mostrar
- **Estado**: Activar/desactivar la funcionalidad

## 🛠️ Desarrollo

### Estructura del plugin

```
src/
├── Commands/
│   └── InstallCommand.php          # Comando de instalación
├── Controllers/
│   └── Saml2Controller.php         # Controlador SAML2
├── Models/
│   └── Saml2OktaConfig.php         # Modelo de configuración
├── Pages/
│   └── Saml2OktaSettingsPage.php   # Página de configuración
├── Services/
│   └── Saml2Service.php            # Servicio SAML2
├── Saml2OktaPlugin.php             # Plugin principal
└── Saml2OktaServiceProvider.php    # Service Provider
```

### Comandos disponibles

```bash
# Instalar el plugin
php artisan saml2-okta:install

# Limpiar caché
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

## 📝 Configuración en Okta

### 1. Crear aplicación SAML en Okta

1. Ve a **Applications** > **Applications**
2. Haz clic en **Create App Integration**
3. Selecciona **SAML 2.0**
4. Configura la aplicación

### 2. Configurar URLs

- **Single Sign On URL**: `https://tu-dominio.com/saml2/callback`
- **Audience URI (SP Entity ID)**: `https://tu-dominio.com/saml2/metadata`
- **Default RelayState**: (opcional)

### 3. Configurar atributos

Agrega los siguientes atributos de usuario:
- `email`
- `firstName`
- `lastName`

### 4. Obtener certificado

Copia el certificado X.509 de la sección **SAML Signing Certificate**.

## 🔍 Debugging

### Logs

El plugin registra información detallada en los logs de Laravel:

```bash
tail -f storage/logs/laravel.log | grep SAML2
```

### Verificar configuración

```bash
# Verificar rutas SAML2
php artisan route:list | grep saml2

# Verificar configuración activa
php artisan tinker
>>> JohnRiveraGonzalez\Saml2Okta\Models\Saml2OktaConfig::getActiveConfig()
```

## 🚨 Solución de problemas

### Error: "Method Textarea::password does not exist"

Este error se solucionó en versiones recientes. Actualiza el plugin:

```bash
composer update johnriveragonzalez/saml2-okta
```

### Error: "Driver [saml2] not supported"

Verifica que el plugin esté registrado correctamente:

```php
// En AdminPanelProvider.php
Saml2OktaPlugin::make(),
```

### Error: "No hay configuración SAML2 activa"

1. Ve a la configuración SAML2 en el panel
2. Crea una nueva configuración
3. Activa la configuración

### Error: "SAML2 redirect error"

Verifica que todas las URLs estén configuradas correctamente:
- Callback URL debe ser accesible
- Entity IDs deben coincidir entre Okta y tu aplicación
- Certificados deben ser válidos

## 📋 Requisitos

- PHP 8.1+
- Laravel 11+
- Filament v3
- Laravel Socialite
- SocialiteProviders SAML2

## 📄 Licencia

MIT License

## 👨‍💻 Autor

**John Rivera Gonzalez**
- Email: johnriveragonzalez7@gmail.com
- GitHub: [@johnriveragonzalez](https://github.com/johnriveragonzalez)

## 🤝 Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📞 Soporte

Para soporte técnico o preguntas:
- 📧 Email: johnriveragonzalez7@gmail.com
- 🐛 Issues: [GitHub Issues](https://github.com/johnriveragonzalez/filamentSaml2Okta/issues)

---

**¡Gracias por usar Filament SAML2 Okta Plugin!** 🎉
