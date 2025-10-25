<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mensaje;
use App\Models\Comentario;
use App\Models\Registro;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;

class MigrateToEncryption extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encryption:migrate 
                            {--type=all : Tipo de datos a migrar (all, messages, comments, passwords)}
                            {--dry-run : Solo mostrar qué se haría sin ejecutar cambios}
                            {--force : Forzar migración sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar datos existentes para usar encriptación';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🔐 Iniciando migración de datos para encriptación...');
        $this->newLine();

        if (!$force && !$dryRun) {
            if (!$this->confirm('¿Estás seguro de que quieres migrar los datos? Esto puede tomar tiempo.')) {
                $this->info('Migración cancelada.');
                return;
            }
        }

        // Verificar configuración
        if (!Config::get('encryption.enabled')) {
            $this->warn('⚠️  La encriptación está deshabilitada en la configuración.');
            if (!$this->confirm('¿Quieres continuar de todas formas?')) {
                return;
            }
        }

        switch ($type) {
            case 'all':
                $this->migratePasswords($dryRun);
                $this->migrateMessages($dryRun);
                $this->migrateComments($dryRun);
                break;
            case 'passwords':
                $this->migratePasswords($dryRun);
                break;
            case 'messages':
                $this->migrateMessages($dryRun);
                break;
            case 'comments':
                $this->migrateComments($dryRun);
                break;
            default:
                $this->error('Tipo de migración no válido. Usa: all, passwords, messages, comments');
                return;
        }

        $this->newLine();
        $this->info('✅ Migración completada.');
    }

    /**
     * Migrar contraseñas a hash
     */
    private function migratePasswords($dryRun)
    {
        $this->info('🔑 Migrando contraseñas...');
        
        $usuarios = Registro::all();
        $count = 0;

        foreach ($usuarios as $usuario) {
            // Verificar si la contraseña ya está hasheada
            if (strlen($usuario->contrasena) === 60 && strpos($usuario->contrasena, '$2y$') === 0) {
                continue; // Ya está hasheada
            }

            if ($dryRun) {
                $this->line("  - Usuario {$usuario->id} ({$usuario->nombre}): contraseña en texto plano");
                $count++;
            } else {
                $usuario->contrasena = Hash::make($usuario->contrasena);
                $usuario->save();
                $count++;
            }
        }

        $this->info("  📊 {$count} contraseñas procesadas.");
    }

    /**
     * Migrar mensajes a encriptación
     */
    private function migrateMessages($dryRun)
    {
        if (!Config::get('encryption.data_types.messages.enabled')) {
            $this->warn('  ⚠️  Encriptación de mensajes deshabilitada.');
            return;
        }

        $this->info('💬 Migrando mensajes...');
        
        $mensajes = Mensaje::all();
        $count = 0;

        foreach ($mensajes as $mensaje) {
            // Verificar si ya está encriptado (contiene caracteres de encriptación)
            if ($this->isEncrypted($mensaje->contenido)) {
                continue;
            }

            if ($dryRun) {
                $this->line("  - Mensaje {$mensaje->id}: contenido en texto plano");
                $count++;
            } else {
                $mensaje->contenido = $mensaje->encryptValue($mensaje->contenido, 'messages');
                $mensaje->save();
                $count++;
            }
        }

        $this->info("  📊 {$count} mensajes procesados.");
    }

    /**
     * Migrar comentarios a encriptación
     */
    private function migrateComments($dryRun)
    {
        if (!Config::get('encryption.data_types.comments.enabled')) {
            $this->warn('  ⚠️  Encriptación de comentarios deshabilitada.');
            return;
        }

        $this->info('💭 Migrando comentarios...');
        
        $comentarios = Comentario::all();
        $count = 0;

        foreach ($comentarios as $comentario) {
            // Verificar si ya está encriptado
            if ($this->isEncrypted($comentario->mensaje)) {
                continue;
            }

            if ($dryRun) {
                $this->line("  - Comentario {$comentario->id}: mensaje en texto plano");
                $count++;
            } else {
                $comentario->mensaje = $comentario->encryptValue($comentario->mensaje, 'comments');
                $comentario->save();
                $count++;
            }
        }

        $this->info("  📊 {$count} comentarios procesados.");
    }

    /**
     * Verificar si un valor está encriptado
     */
    private function isEncrypted($value)
    {
        // Verificar si contiene caracteres típicos de encriptación base64
        return preg_match('/^[A-Za-z0-9+\/]+=*$/', $value) && strlen($value) > 50;
    }
}
