<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@gestionsegura.test');

        if (! User::where('email', $email)->exists()) {
            $password = env('ADMIN_PASSWORD');
            $generated = $password === null;
            $password ??= Str::password(16);

            User::factory()->admin()->create([
                'name' => 'Administrador',
                'email' => $email,
                'password' => $password,
            ]);

            $this->command?->info("Usuario administrador creado: {$email}");

            if ($generated) {
                $this->command?->warn("Contraseña generada (no se guarda en ningún archivo): {$password}");
                $this->command?->warn('Defínela también como ADMIN_PASSWORD en .env para reproducirla en otro entorno.');
            }
        }

        $this->call(ContractsDemoSeeder::class);
    }
}
