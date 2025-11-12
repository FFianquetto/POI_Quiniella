<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Mensaje extends Model
{
    protected $table = 'mensajes';
    
    protected $fillable = [
        'chat_id',
        'registro_id_emisor',
        'contenido',
        'tipo',
        'archivo_url',
        'archivo_nombre',
        'leido',
        'entregado',
        'entregado_at',
    ];

    protected $casts = [
        'leido' => 'boolean',
        'entregado' => 'boolean',
        'entregado_at' => 'datetime',
    ];

    /**
     * Mutador: encripta el contenido antes de almacenarlo
     */
    public function setContenidoAttribute($value): void
    {
        if (!$this->shouldEncrypt() || is_null($value) || $value === '') {
            $this->attributes['contenido'] = $value;
            return;
        }

        $encrypted = $this->encryptValue($value);

        $this->attributes['contenido'] = $encrypted ?? $value;
    }

    /**
     * Accessor: desencripta el contenido al acceder
     */
    public function getContenidoAttribute($value)
    {
        if (!$this->shouldEncrypt() || is_null($value) || $value === '') {
            return $value;
        }

        try {
            $decrypted = $this->decryptValue($value);

            return $decrypted ?? $value;
        } catch (\Throwable $exception) {
            $messageId = $this->attributes['id'] ?? 'desconocido';

            \Log::warning("No se pudo desencriptar el contenido del mensaje ID {$messageId}", [
                'exception' => $exception->getMessage(),
            ]);

            return $value;
        }
    }

    /**
     * Chat al que pertenece el mensaje
     */
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Usuario que envió el mensaje
     */
    public function emisor()
    {
        return $this->belongsTo(Registro::class, 'registro_id_emisor');
    }

    /**
     * Marcar mensaje como leído
     */
    public function marcarComoLeido()
    {
        $this->leido = true;
        $this->save();
    }

    /**
     * Marcar mensaje como entregado
     */
    public function marcarComoEntregado(): void
    {
        $this->entregado = true;
        $this->entregado_at = now();
        $this->save();
    }

    /**
     * Verificar si el mensaje es de un archivo
     */
    public function esArchivo()
    {
        return in_array($this->tipo, ['imagen', 'video', 'audio', 'archivo']);
    }

    /**
     * Obtener la extensión del archivo
     */
    public function getExtension()
    {
        if ($this->archivo_nombre) {
            return pathinfo($this->archivo_nombre, PATHINFO_EXTENSION);
        }
        return null;
    }

    /**
     * Obtener el nombre del archivo sin extensión
     */
    public function getNombreSinExtension()
    {
        if ($this->archivo_nombre) {
            return pathinfo($this->archivo_nombre, PATHINFO_FILENAME);
        }
        return null;
    }

    /**
     * Determina si debemos encriptar el contenido
     */
    protected function shouldEncrypt(): bool
    {
        return (bool) config('chat.encryption.enabled', true);
    }

    /**
     * Obtiene la clave de encriptación normalizada a 32 bytes
     */
    protected function getEncryptionKey(): string
    {
        $rawKey = config('chat.encryption.key', config('app.key'));

        if (!$rawKey) {
            throw new \RuntimeException('No se ha definido una clave de encriptación para el chat.');
        }

        if (Str::startsWith($rawKey, 'base64:')) {
            $rawKey = base64_decode(substr($rawKey, 7));
        }

        return hash('sha256', $rawKey, true);
    }

    /**
     * Obtiene el cifrado configurado
     */
    protected function getCipher(): string
    {
        $cipher = config('chat.encryption.cipher', 'AES-256-ECB');

        return strtoupper($cipher);
    }

    /**
     * Encripta un valor textual usando OpenSSL y lo codifica en Base64
     */
    protected function encryptValue(string $value): ?string
    {
        $cipher = $this->getCipher();
        $key = $this->getEncryptionKey();

        $encrypted = openssl_encrypt($value, $cipher, $key, OPENSSL_RAW_DATA);

        if ($encrypted === false) {
            return null;
        }

        return base64_encode($encrypted);
    }

    /**
     * Desencripta un valor previamente encriptado con encryptValue
     */
    protected function decryptValue(string $value): ?string
    {
        $cipher = $this->getCipher();
        $key = $this->getEncryptionKey();

        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            return null;
        }

        $decrypted = openssl_decrypt($decoded, $cipher, $key, OPENSSL_RAW_DATA);

        return $decrypted === false ? null : $decrypted;
    }
}
