<?php

namespace JohnRiveraGonzalez\Saml2Okta\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CertificateService
{
    protected string $storagePath = 'saml2-okta/certificates';
    
    public function generateCertificate(string $domain, string $organization = 'SAML2 Okta Plugin'): array
    {
        try {
            // Crear directorio si no existe
            Storage::makeDirectory($this->storagePath);
            
            $privateKeyPath = $this->getPrivateKeyPath($domain);
            $certificatePath = $this->getCertificatePath($domain);
            
            // Generar clave privada con método alternativo para Windows
            if (PHP_OS_FAMILY === 'Windows') {
                // Usar método alternativo para Windows
                $privateKey = $this->generatePrivateKeyWindows();
                
                if (!$privateKey) {
                    // Si falla, usar certificados de prueba predefinidos
                    return $this->generateTestCertificates($domain, $organizationName);
                }
            } else {
                // Método estándar para Linux/Mac
                $privateKey = openssl_pkey_new([
                    'digest_alg' => 'sha256',
                    'private_key_bits' => 2048,
                    'private_key_type' => OPENSSL_KEYTYPE_RSA,
                ]);
            }
            
            if (!$privateKey) {
                throw new \Exception('Error generando clave privada: ' . (openssl_error_string() ?: 'OpenSSL no disponible'));
            }
            
            // Extraer clave privada
            openssl_pkey_export($privateKey, $privateKeyPem);
            
            // Crear certificado
            $dn = [
                'countryName' => 'CL',
                'stateOrProvinceName' => 'Santiago',
                'localityName' => 'Santiago',
                'organizationName' => $organization,
                'organizationalUnitName' => 'IT Department',
                'commonName' => $domain,
                'emailAddress' => 'admin@' . $domain,
            ];
            
            $csr = openssl_csr_new($dn, $privateKey, ['digest_alg' => 'sha256']);
            if (!$csr) {
                throw new \Exception('Error generando CSR: ' . openssl_error_string());
            }
            
            $certificate = openssl_csr_sign($csr, null, $privateKey, 365, ['digest_alg' => 'sha256']);
            if (!$certificate) {
                throw new \Exception('Error firmando certificado: ' . openssl_error_string());
            }
            
            // Exportar certificado
            openssl_x509_export($certificate, $certificatePem);
            
            // Guardar archivos
            Storage::put($privateKeyPath, $privateKeyPem);
            Storage::put($certificatePath, $certificatePem);
            
            // Obtener información del certificado
            $certInfo = openssl_x509_parse($certificate);
            
            Log::info('Certificado SAML generado exitosamente', [
                'domain' => $domain,
                'private_key_path' => $privateKeyPath,
                'certificate_path' => $certificatePath,
                'valid_from' => date('Y-m-d H:i:s', $certInfo['validFrom_time_t']),
                'valid_to' => date('Y-m-d H:i:s', $certInfo['validTo_time_t']),
            ]);
            
            return [
                'success' => true,
                'private_key' => $privateKeyPem,
                'certificate' => $certificatePem,
                'private_key_path' => $privateKeyPath,
                'certificate_path' => $certificatePath,
                'valid_from' => date('Y-m-d H:i:s', $certInfo['validFrom_time_t']),
                'valid_to' => date('Y-m-d H:i:s', $certInfo['validTo_time_t']),
                'serial_number' => $certInfo['serialNumberHex'],
            ];
            
        } catch (\Exception $e) {
            Log::error('Error generando certificado SAML', [
                'domain' => $domain,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    public function getCertificate(string $domain): ?array
    {
        $privateKeyPath = $this->getPrivateKeyPath($domain);
        $certificatePath = $this->getCertificatePath($domain);
        
        if (!Storage::exists($privateKeyPath) || !Storage::exists($certificatePath)) {
            return null;
        }
        
        $privateKey = Storage::get($privateKeyPath);
        $certificate = Storage::get($certificatePath);
        
        // Obtener información del certificado
        $certInfo = openssl_x509_parse($certificate);
        
        return [
            'private_key' => $privateKey,
            'certificate' => $certificate,
            'private_key_path' => $privateKeyPath,
            'certificate_path' => $certificatePath,
            'valid_from' => date('Y-m-d H:i:s', $certInfo['validFrom_time_t']),
            'valid_to' => date('Y-m-d H:i:s', $certInfo['validTo_time_t']),
            'serial_number' => $certInfo['serialNumberHex'],
            'subject' => $certInfo['subject'],
        ];
    }
    
    public function regenerateCertificate(string $domain, string $organization = 'SAML2 Okta Plugin'): array
    {
        // Eliminar certificados existentes
        $this->deleteCertificate($domain);
        
        // Generar nuevos
        return $this->generateCertificate($domain, $organization);
    }
    
    public function deleteCertificate(string $domain): bool
    {
        $privateKeyPath = $this->getPrivateKeyPath($domain);
        $certificatePath = $this->getCertificatePath($domain);
        
        $deleted = true;
        
        if (Storage::exists($privateKeyPath)) {
            $deleted = $deleted && Storage::delete($privateKeyPath);
        }
        
        if (Storage::exists($certificatePath)) {
            $deleted = $deleted && Storage::delete($certificatePath);
        }
        
        return $deleted;
    }
    
    public function getCertificateInfo(string $domain): ?array
    {
        $certificatePath = $this->getCertificatePath($domain);
        
        if (!Storage::exists($certificatePath)) {
            return null;
        }
        
        $certificate = Storage::get($certificatePath);
        $certInfo = openssl_x509_parse($certificate);
        
        return [
            'valid_from' => date('Y-m-d H:i:s', $certInfo['validFrom_time_t']),
            'valid_to' => date('Y-m-d H:i:s', $certInfo['validTo_time_t']),
            'serial_number' => $certInfo['serialNumberHex'],
            'subject' => $certInfo['subject'],
            'issuer' => $certInfo['issuer'],
            'days_until_expiry' => (int) (($certInfo['validTo_time_t'] - time()) / 86400),
        ];
    }
    
    protected function getPrivateKeyPath(string $domain): string
    {
        return $this->storagePath . '/' . Str::slug($domain) . '_private_key.pem';
    }
    
    protected function getCertificatePath(string $domain): string
    {
        return $this->storagePath . '/' . Str::slug($domain) . '_certificate.pem';
    }
    
    protected function getOpenSSLConfigPath(): string
    {
        // Buscar archivo de configuración OpenSSL en Windows
        $possiblePaths = [
            'C:\OpenSSL\bin\openssl.cfg',
            'C:\Program Files\OpenSSL\bin\openssl.cfg',
            'C:\Program Files (x86)\OpenSSL\bin\openssl.cfg',
            'C:\xampp\apache\conf\openssl.cnf',
            'C:\wamp64\bin\apache\apache2.4.46\conf\openssl.cnf',
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Si no se encuentra, usar configuración por defecto
        return '';
    }
    
    protected function generatePrivateKeyWindows()
    {
        // Método alternativo para Windows usando comandos del sistema
        try {
            // Crear archivo temporal para la clave
            $tempKeyFile = tempnam(sys_get_temp_dir(), 'saml_key_');
            $tempCertFile = tempnam(sys_get_temp_dir(), 'saml_cert_');
            
            // Comando OpenSSL para generar clave privada
            $command = "openssl genrsa -out \"$tempKeyFile\" 2048 2>&1";
            $output = [];
            $returnCode = 0;
            
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new \Exception('Error ejecutando OpenSSL: ' . implode(' ', $output));
            }
            
            // Leer la clave privada generada
            $privateKeyPem = file_get_contents($tempKeyFile);
            
            // Limpiar archivos temporales
            unlink($tempKeyFile);
            if (file_exists($tempCertFile)) {
                unlink($tempCertFile);
            }
            
            // Convertir a recurso de clave privada
            $privateKey = openssl_pkey_get_private($privateKeyPem);
            
            if (!$privateKey) {
                throw new \Exception('Error convirtiendo clave privada: ' . openssl_error_string());
            }
            
            return $privateKey;
            
        } catch (\Exception $e) {
            // Si falla el método de comando, intentar método estándar
            return openssl_pkey_new([
                'digest_alg' => 'sha256',
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);
        }
    }
    
    public function getCertificateForOkta(string $domain): ?string
    {
        $certificatePath = $this->getCertificatePath($domain);
        
        if (!Storage::exists($certificatePath)) {
            return null;
        }
        
        return Storage::get($certificatePath);
    }
    
    public function getPrivateKeyForSp(string $domain): ?string
    {
        $privateKeyPath = $this->getPrivateKeyPath($domain);
        
        if (!Storage::exists($privateKeyPath)) {
            return null;
        }
        
        return Storage::get($privateKeyPath);
    }
}
