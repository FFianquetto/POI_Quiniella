<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class EncryptionController extends Controller
{
    /**
     * Mostrar la configuración de encriptación
     */
    public function index(): View
    {
        $config = Config::get('encryption');
        
        return view('admin.encryption', compact('config'));
    }

    /**
     * Actualizar configuración de encriptación
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'enabled' => 'boolean',
            'encrypt_messages' => 'boolean',
            'encrypt_comments' => 'boolean',
            'encrypt_media_metadata' => 'boolean',
            'encrypt_user_data' => 'boolean',
            'session_encrypt' => 'boolean',
            'cookie_encrypt' => 'boolean',
        ]);

        // Actualizar configuración
        $config = Config::get('encryption');
        
        $config['enabled'] = $request->boolean('enabled');
        $config['data_types']['messages']['enabled'] = $request->boolean('encrypt_messages');
        $config['data_types']['comments']['enabled'] = $request->boolean('encrypt_comments');
        $config['data_types']['media_files']['enabled'] = $request->boolean('encrypt_media_metadata');
        $config['data_types']['user_data']['enabled'] = $request->boolean('encrypt_user_data');
        $config['session']['encrypt'] = $request->boolean('session_encrypt');
        $config['cookies']['encrypt'] = $request->boolean('cookie_encrypt');

        // Guardar configuración en archivo .env
        $this->updateEnvFile($request);

        return redirect()->route('admin.encryption')
            ->with('success', 'Configuración de encriptación actualizada correctamente.');
    }

    /**
     * Ejecutar migración de datos
     */
    public function migrate(Request $request): RedirectResponse
    {
        $request->validate([
            'migration_type' => 'required|in:all,passwords,messages,comments',
            'dry_run' => 'boolean',
        ]);

        $type = $request->input('migration_type');
        $dryRun = $request->boolean('dry_run');

        try {
            $command = "encryption:migrate --type={$type}";
            if ($dryRun) {
                $command .= ' --dry-run';
            }

            Artisan::call($command);
            $output = Artisan::output();

            $message = $dryRun ? 'Simulación de migración completada.' : 'Migración de datos completada.';
            
            return redirect()->route('admin.encryption')
                ->with('success', $message)
                ->with('migration_output', $output);

        } catch (\Exception $e) {
            return redirect()->route('admin.encryption')
                ->with('error', 'Error durante la migración: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar archivo .env con nueva configuración
     */
    private function updateEnvFile(Request $request): void
    {
        $envFile = base_path('.env');
        
        if (!file_exists($envFile)) {
            return;
        }

        $envContent = file_get_contents($envFile);
        
        // Actualizar variables de encriptación
        $envVars = [
            'ENCRYPTION_ENABLED' => $request->boolean('enabled') ? 'true' : 'false',
            'ENCRYPT_MESSAGES' => $request->boolean('encrypt_messages') ? 'true' : 'false',
            'ENCRYPT_COMMENTS' => $request->boolean('encrypt_comments') ? 'true' : 'false',
            'ENCRYPT_MEDIA_METADATA' => $request->boolean('encrypt_media_metadata') ? 'true' : 'false',
            'ENCRYPT_USER_DATA' => $request->boolean('encrypt_user_data') ? 'true' : 'false',
            'SESSION_ENCRYPT' => $request->boolean('session_encrypt') ? 'true' : 'false',
            'COOKIE_ENCRYPT' => $request->boolean('cookie_encrypt') ? 'true' : 'false',
        ];

        foreach ($envVars as $key => $value) {
            if (strpos($envContent, $key) !== false) {
                // Actualizar variable existente
                $envContent = preg_replace(
                    "/^{$key}=.*$/m",
                    "{$key}={$value}",
                    $envContent
                );
            } else {
                // Agregar nueva variable
                $envContent .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envFile, $envContent);
    }

    /**
     * Obtener estadísticas de encriptación
     */
    public function stats(): array
    {
        return [
            'total_messages' => \App\Models\Mensaje::count(),
            'total_comments' => \App\Models\Comentario::count(),
            'total_users' => \App\Models\Registro::count(),
            'encryption_enabled' => Config::get('encryption.enabled'),
            'messages_encrypted' => Config::get('encryption.data_types.messages.enabled'),
            'comments_encrypted' => Config::get('encryption.data_types.comments.enabled'),
        ];
    }
}
