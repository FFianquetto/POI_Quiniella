<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Registro;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        $admin = Registro::create([
            'nombre' => 'Administrador',
            'correo' => 'admin@admin.com',
            'contrasena' => '123',
            'edad' => 30,
            'es_admin' => true,
        ]);

        $this->command->info("✅ Usuario administrador creado:");
        $this->command->info("   📧 Email: admin@admin.com");
        $this->command->info("   🔑 Contraseña: 123");
        $this->command->info("   🆔 ID: {$admin->id}");
    }
}
