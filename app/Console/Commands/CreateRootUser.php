<?php

namespace App\Console\Commands;

use App\Modules\ControlAcceso\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateRootUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-root {--email=} {--name=} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear usuario root';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email') ?: config('security.root_email');
        $name = $this->option('name') ?: config('security.root_name', 'Root');
        $password = $this->option('password') ?: config('security.root_password');

        if (empty($email)) {
            $this->error('Email requerido. Use --email o ROOT_USER_EMAIL.');
            return Command::FAILURE;
        }

        if (empty($password)) {
            $password = Str::random(20);
            $this->warn('No se proporcionó contraseña. Se generó una contraseña segura.');
            $this->info('Contraseña generada: ' . $password);
        }

        // En Laravel 11, el modelo tiene 'password' => 'hashed' en casts
        // Por lo tanto, debemos pasar la contraseña en texto plano
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password, // El modelo lo hasheará automáticamente
            ]
        );

        $this->info('Usuario root creado/actualizado exitosamente:');
        $this->info('Email: ' . $user->email);
        $this->info('Nombre: ' . $user->name);
        $this->info('ID: ' . $user->id);

        return Command::SUCCESS;
    }
}
