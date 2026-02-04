<?php

namespace App\Console\Commands;

use App\Modules\ControlAcceso\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetPasswordCommand extends Command
{
    protected $signature = 'user:reset-password {email} {password}';
    protected $description = 'Reset password for a user';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $this->info("Buscando usuario: {$email}");

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Usuario no encontrado!");
            $this->info("Creando usuario...");
            $user = User::create([
                'name' => 'Root',
                'email' => $email,
                'password' => $password, // El cast 'hashed' lo hasheará
            ]);
            $this->info("✅ Usuario creado");
        } else {
            $this->info("Usuario encontrado (ID: {$user->id})");
            $this->info("Actualizando contraseña...");

            // En Laravel 11 con cast 'hashed', pasar texto plano
            $user->password = $password;
            $user->save();

            $this->info("✅ Contraseña actualizada");
        }

        // Verificar
        $user->refresh();
        $this->newLine();
        $this->info("Verificando contraseña...");

        $check = Hash::check($password, $user->password);
        $this->info("Hash::check(): " . ($check ? "✅ OK" : "❌ FALLO"));

        $this->newLine();
        $this->info("=== RESUMEN ===");
        $this->info("Email: {$user->email}");
        $this->info("Nombre: {$user->name}");
        $this->info("ID: {$user->id}");
        $this->info("Contraseña: {$password}");
        $this->newLine();
        $this->info("✅ Listo! Intenta iniciar sesión ahora.");

        return Command::SUCCESS;
    }
}
