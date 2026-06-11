<?php

namespace Database\Seeders;

use App\Http\Controllers\AuthController;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'eloyisfrx@gmail.com';
        $password = 'Impo$$ible2Pir@ter';
        $role = 'super_admin';
        $name = 'Eloy';

        if (User::where('email', $email)->exists()) {
            $this->command->warn("L'utilisateur {$email} existe déjà.");
            return;
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
        UserRole::create(['user_id' => $user->id, 'role' => 'admin']);

        $roleModel = Role::firstOrCreate(['key' => $role], ['name' => ucfirst($role)]);
        $user->roles()->attach($roleModel->id);

        $this->command->newLine();
        $this->command->info('Compte staff créé avec succès !');
        $this->command->newLine();
        $this->command->table(
            ['Champ', 'Valeur'],
            [
                ['Identifiant', $identifier],
                ['Email', $email],
                ['Nom', $name ?? '—'],
                ['Rôle', $role],
                ['Mot de passe', $password],
            ]
        );
        $this->command->newLine();
        $this->command->warn('Conservez ces informations. Le mot de passe ne sera plus affiché.');
    }
}
