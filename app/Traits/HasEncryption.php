<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Config;

trait HasEncryption
{
    /**
     * Encriptar un valor usando la configuración específica del modelo
     *
     * @param mixed $value
     * @param string|null $dataType
     * @return string|null
     */
    protected function encryptValue($value, $dataType = null)
    {
        if (!$value || !$this->isEncryptionEnabled($dataType)) {
            return $value;
        }

        try {
            $key = $this->getEncryptionKey($dataType);
            return Crypt::encryptString($value, $key);
        } catch (\Exception $e) {
            \Log::error('Error encrypting value: ' . $e->getMessage());
            return $value; // Retornar valor original en caso de error
        }
    }

    /**
     * Desencriptar un valor usando la configuración específica del modelo
     *
     * @param mixed $value
     * @param string|null $dataType
     * @return string|null
     */
    protected function decryptValue($value, $dataType = null)
    {
        if (!$value || !$this->isEncryptionEnabled($dataType)) {
            return $value;
        }

        try {
            $key = $this->getEncryptionKey($dataType);
            return Crypt::decryptString($value, $key);
        } catch (\Exception $e) {
            \Log::error('Error decrypting value: ' . $e->getMessage());
            return $value; // Retornar valor original en caso de error
        }
    }

    /**
     * Verificar si la encriptación está habilitada para un tipo de dato
     *
     * @param string|null $dataType
     * @return bool
     */
    protected function isEncryptionEnabled($dataType = null)
    {
        $encryptionConfig = Config::get('encryption');
        
        // Verificar encriptación global
        if (!$encryptionConfig['enabled']) {
            return false;
        }

        // Si no se especifica tipo de dato, usar configuración global
        if (!$dataType) {
            return true;
        }

        // Verificar configuración específica del tipo de dato
        return $encryptionConfig['data_types'][$dataType]['enabled'] ?? false;
    }

    /**
     * Obtener la clave de encriptación para un tipo de dato específico
     *
     * @param string|null $dataType
     * @return string
     */
    protected function getEncryptionKey($dataType = null)
    {
        $encryptionConfig = Config::get('encryption');
        
        if ($dataType && isset($encryptionConfig['keys'][$dataType])) {
            return $encryptionConfig['keys'][$dataType];
        }

        return $encryptionConfig['keys']['default'];
    }

    /**
     * Obtener los campos que deben ser encriptados para este modelo
     *
     * @param string $dataType
     * @return array
     */
    protected function getEncryptedFields($dataType)
    {
        $encryptionConfig = Config::get('encryption');
        return $encryptionConfig['data_types'][$dataType]['fields'] ?? [];
    }

    /**
     * Encriptar automáticamente los campos especificados al guardar
     *
     * @param array $attributes
     * @param string $dataType
     * @return array
     */
    protected function encryptFields(array $attributes, $dataType)
    {
        if (!$this->isEncryptionEnabled($dataType)) {
            return $attributes;
        }

        $encryptedFields = $this->getEncryptedFields($dataType);

        foreach ($encryptedFields as $field) {
            if (isset($attributes[$field])) {
                $attributes[$field] = $this->encryptValue($attributes[$field], $dataType);
            }
        }

        return $attributes;
    }

    /**
     * Desencriptar automáticamente los campos especificados al acceder
     *
     * @param array $attributes
     * @param string $dataType
     * @return array
     */
    protected function decryptFields(array $attributes, $dataType)
    {
        if (!$this->isEncryptionEnabled($dataType)) {
            return $attributes;
        }

        $encryptedFields = $this->getEncryptedFields($dataType);

        foreach ($encryptedFields as $field) {
            if (isset($attributes[$field])) {
                $attributes[$field] = $this->decryptValue($attributes[$field], $dataType);
            }
        }

        return $attributes;
    }
}
