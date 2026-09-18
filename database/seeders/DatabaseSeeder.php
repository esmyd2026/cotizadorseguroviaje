<?php

namespace Database\Seeders;

use App\Enums\UserRole;
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
        $email = config('demo.admin_email');
        $existingAdmin = User::where('email', $email)->first();
        $password = config('demo.admin_password');

        if ($password !== null) {
            // A fixed password is configured: always (re)create the admin
            // with it, so `db:seed` is idempotent and the credentials shown
            // on the login screen never drift out of sync with the database.
            // `role` isn't mass-assignable (see the User model's #[Fillable]
            // attribute), so it's set directly rather than through the array.
            $admin = User::firstOrNew(['email' => $email]);
            $admin->name = 'Administrador';
            $admin->password = $password;
            $admin->role = UserRole::Admin;
            $admin->save();

            $this->command?->info("Usuario administrador: {$email} / {$password}");
        } elseif (! $existingAdmin) {
            // No ADMIN_PASSWORD configured: generate one, but only on first
            // creation — reseeding must never silently rotate a password
            // nobody asked to change.
            $generated = Str::password(16);

            User::factory()->admin()->create([
                'name' => 'Administrador',
                'email' => $email,
                'password' => $generated,
            ]);

            $this->command?->info("Usuario administrador creado: {$email}");
            $this->command?->warn("Contraseña generada (no se guarda en ningún archivo): {$generated}");
            $this->command?->warn('Defínela también como ADMIN_PASSWORD en .env para reproducirla en otro entorno.');
        }

        $this->call(DemoAccountsSeeder::class);
        $this->call(ContractsDemoSeeder::class);
    }
}
