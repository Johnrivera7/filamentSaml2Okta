# Iconos del Plugin SAML2 Okta

## 🎨 **Sistema de Iconos Híbrido**

El plugin soporta **dos tipos de iconos**:

1. **Iconos de Proveedores** - SVG personalizados para proveedores específicos
2. **Heroicons** - Iconos estándar de Heroicons

## 🏢 **Iconos de Proveedores Incluidos**

### ✅ **Proveedores SAML2 Populares:**

| Proveedor | Icono | Archivo | Descripción |
|-----------|-------|---------|-------------|
| **Okta** | `okta` | `icons/okta.blade.php` | Icono oficial de Okta |
| **Microsoft** | `microsoft` | `icons/microsoft.blade.php` | Icono oficial de Microsoft |
| **Google** | `google` | `icons/google.blade.php` | Icono oficial de Google |
| **Auth0** | `auth0` | `icons/auth0.blade.php` | Icono oficial de Auth0 |

### 🎯 **Cómo Usar Iconos de Proveedores:**

```php
// En la configuración del plugin
'button_icon' => 'okta'        // Para Okta
'button_icon' => 'microsoft'   // Para Microsoft
'button_icon' => 'google'      // Para Google
'button_icon' => 'auth0'       // Para Auth0
```

## 🦸 **Heroicons Soportados**

### ✅ **Iconos de Seguridad y Autenticación:**

| Icono | Código | Descripción |
|-------|--------|-------------|
| 🛡️ | `heroicon-o-shield-check` | Shield Check (por defecto) |
| 🔒 | `heroicon-o-lock-closed` | Lock Closed |
| 🗝️ | `heroicon-o-key` | Key |
| 🚀 | `heroicon-o-rocket-launch` | Rocket Launch |
| 👤 | `heroicon-o-user` | User |
| 🔑 | `heroicon-o-login` | Login |
| 🆔 | `heroicon-o-identification` | Identification |
| 👆 | `heroicon-o-finger-print` | Finger Print |

### 🎯 **Cómo Usar Heroicons:**

```php
// En la configuración del plugin
'button_icon' => 'heroicon-o-shield-check'  // Shield Check
'button_icon' => 'heroicon-o-rocket-launch' // Rocket Launch
'button_icon' => 'heroicon-o-key'           // Key
```

## 🔧 **Agregar Nuevos Iconos de Proveedores**

### 1. **Crear archivo SVG:**

```bash
# Crear archivo en resources/views/icons/
touch resources/views/icons/tu-proveedor.blade.php
```

### 2. **Estructura del archivo:**

```php
{{-- Tu Proveedor Icon --}}
<svg viewBox="0 0 24 24" class="w-5 h-5 {{ $class ?? '' }}" fill="currentColor">
    <!-- Tu SVG aquí -->
</svg>
```

### 3. **Actualizar opciones en Saml2OktaSettingsPage.php:**

```php
Select::make('button_icon')
    ->options([
        // Iconos de proveedores
        'okta' => 'Okta',
        'microsoft' => 'Microsoft',
        'google' => 'Google',
        'auth0' => 'Auth0',
        'tu-proveedor' => 'Tu Proveedor', // ← Agregar aquí
        
        // Separador
        '---' => '--- Heroicons ---',
        
        // Heroicons...
    ])
```

### 4. **Actualizar componente saml2-icon.blade.php:**

```php
@php
    $isCustomIcon = in_array($icon, ['okta', 'microsoft', 'google', 'auth0', 'tu-proveedor']); // ← Agregar aquí
@endphp
```

## 🎨 **Personalización de Iconos**

### **Tamaño:**
```php
<x-saml2-okta::saml2-icon :icon="$buttonIcon" class="w-6 h-6" />
```

### **Color:**
```php
<x-saml2-okta::saml2-icon :icon="$buttonIcon" class="w-5 h-5 text-blue-500" />
```

### **Estilos personalizados:**
```php
<x-saml2-okta::saml2-icon :icon="$buttonIcon" class="w-5 h-5 mr-2 text-white" />
```

## 🚀 **Ejemplos de Uso**

### **Para Okta:**
```php
'button_icon' => 'okta'
'button_label' => 'Iniciar sesión con Okta'
```

### **Para Microsoft:**
```php
'button_icon' => 'microsoft'
'button_label' => 'Iniciar sesión con Microsoft'
```

### **Para Google:**
```php
'button_icon' => 'google'
'button_label' => 'Iniciar sesión con Google'
```

### **Con Heroicon:**
```php
'button_icon' => 'heroicon-o-rocket-launch'
'button_label' => 'Iniciar sesión con SAML2'
```

## 📋 **Ventajas del Sistema Híbrido**

- ✅ **Iconos oficiales** de proveedores populares
- ✅ **Heroicons** para casos genéricos
- ✅ **Fácil extensión** para nuevos proveedores
- ✅ **Consistencia visual** con el diseño del plugin
- ✅ **Flexibilidad** para personalización
- ✅ **Fallback automático** a shield-check si no se encuentra el icono

**¡El sistema de iconos es completamente flexible y extensible!** 🎉
