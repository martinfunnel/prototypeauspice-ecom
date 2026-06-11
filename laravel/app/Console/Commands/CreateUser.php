<?php

namespace App\Console\Commands;

use App\Http\Controllers\AuthController;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    protected $signature = 'user:create
                            {email : Email de l\'utilisateur}
                            {password : Mot de passe en clair}
                            {--role=admin : Rôle (super_admin, admin, vendeur, comptable)}
                            {--name= : Nom complet}';

    protected $description = 'Crée un compte staff avec identifiant AUS-XXXXXX généré automatiquement';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));
        $password = $this->argument('password');
        $role = $this->option('role');
        $name = $this->option('name');

        if (User::where('email', $email)->exists()) {
            $this->error("Un utilisateur avec l'email {$email} existe déjà.");
            return 1;
        }

        do {
            $identifier = AuthController::generateIdentifier();
        } while (User::where('identifier', $identifier)->exists());

        $user = User::create([
            'identifier' => $identifier,
            'email' => $email,
            'name' => $name ?? $identifier,
            'full_name' => $name ?? null,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        UserRole::create(['user_id' => $user->id, 'role' => $role]);

        $roleModel = Role::firstOrCreate(['key' => $role], ['name' => ucfirst($role)]);
        $user->roles()->attach($roleModel->id);

        $this->newLine();
        $this->info('Compte staff créé avec succès !');
        $this->newLine();
        $this->table(
            ['Champ', 'Valeur'],
            [
                ['Identifiant', $identifier],
                ['Email', $email],
                ['Nom', $name ?? '—'],
                ['Rôle', $role],
                ['Mot de passe', $password],
            ]
        );
        $this->newLine();
        $this->warn('Conservez ces informations. Le mot de passe ne sera plus affiché.');

        return 0;
    }
}
