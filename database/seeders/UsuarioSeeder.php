<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Registro;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuarios de ejemplo
        $usuarios = [
            [
                'nombre' => 'Juan Pérez',
                'correo' => 'juan@example.com',
                'contrasena' => 'password123',
                'edad' => 25,
            ],
            [
                'nombre' => 'María García',
                'correo' => 'maria@example.com',
                'contrasena' => 'password123',
                'edad' => 28,
            ],
            [
                'nombre' => 'Carlos López',
                'correo' => 'carlos@example.com',
                'contrasena' => 'password123',
                'edad' => 30,
            ],
        ];

        foreach ($usuarios as $usuario) {
            Registro::create($usuario);
        }
    }
}
