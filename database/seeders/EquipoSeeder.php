<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipo;

class EquipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equipos = [
            // Liga MX
            ['nombre' => 'América', 'abreviacion' => 'AME', 'ciudad' => 'Ciudad de México', 'liga' => 'Liga MX'],
            ['nombre' => 'Guadalajara', 'abreviacion' => 'GUA', 'ciudad' => 'Guadalajara', 'liga' => 'Liga MX'],
            ['nombre' => 'Cruz Azul', 'abreviacion' => 'CRU', 'ciudad' => 'Ciudad de México', 'liga' => 'Liga MX'],
            ['nombre' => 'Pumas UNAM', 'abreviacion' => 'PUM', 'ciudad' => 'Ciudad de México', 'liga' => 'Liga MX'],
            ['nombre' => 'Monterrey', 'abreviacion' => 'MON', 'ciudad' => 'Monterrey', 'liga' => 'Liga MX'],
            ['nombre' => 'Tigres UANL', 'abreviacion' => 'TIG', 'ciudad' => 'San Nicolás de los Garza', 'liga' => 'Liga MX'],
            ['nombre' => 'Santos Laguna', 'abreviacion' => 'SAN', 'ciudad' => 'Torreón', 'liga' => 'Liga MX'],
            ['nombre' => 'Pachuca', 'abreviacion' => 'PAC', 'ciudad' => 'Pachuca', 'liga' => 'Liga MX'],
            
            // Premier League
            ['nombre' => 'Manchester United', 'abreviacion' => 'MUN', 'ciudad' => 'Manchester', 'liga' => 'Premier League'],
            ['nombre' => 'Manchester City', 'abreviacion' => 'MCI', 'ciudad' => 'Manchester', 'liga' => 'Premier League'],
            ['nombre' => 'Liverpool', 'abreviacion' => 'LIV', 'ciudad' => 'Liverpool', 'liga' => 'Premier League'],
            ['nombre' => 'Chelsea', 'abreviacion' => 'CHE', 'ciudad' => 'Londres', 'liga' => 'Premier League'],
            ['nombre' => 'Arsenal', 'abreviacion' => 'ARS', 'ciudad' => 'Londres', 'liga' => 'Premier League'],
            ['nombre' => 'Tottenham', 'abreviacion' => 'TOT', 'ciudad' => 'Londres', 'liga' => 'Premier League'],
            
            // La Liga
            ['nombre' => 'Real Madrid', 'abreviacion' => 'RMA', 'ciudad' => 'Madrid', 'liga' => 'La Liga'],
            ['nombre' => 'Barcelona', 'abreviacion' => 'BAR', 'ciudad' => 'Barcelona', 'liga' => 'La Liga'],
            ['nombre' => 'Atlético Madrid', 'abreviacion' => 'ATM', 'ciudad' => 'Madrid', 'liga' => 'La Liga'],
            ['nombre' => 'Sevilla', 'abreviacion' => 'SEV', 'ciudad' => 'Sevilla', 'liga' => 'La Liga'],
            
            // Serie A
            ['nombre' => 'Juventus', 'abreviacion' => 'JUV', 'ciudad' => 'Turín', 'liga' => 'Serie A'],
            ['nombre' => 'AC Milan', 'abreviacion' => 'MIL', 'ciudad' => 'Milán', 'liga' => 'Serie A'],
            ['nombre' => 'Inter Milan', 'abreviacion' => 'INT', 'ciudad' => 'Milán', 'liga' => 'Serie A'],
            ['nombre' => 'Napoli', 'abreviacion' => 'NAP', 'ciudad' => 'Nápoles', 'liga' => 'Serie A'],
        ];

        foreach ($equipos as $equipo) {
            Equipo::create($equipo);
        }
    }
}
