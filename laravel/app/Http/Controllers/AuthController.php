<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required',
        ]);

        $raw = trim($request->input('identifier'));
        $email = str_contains($raw, '@') ? strtolower($raw) : strtolower($raw) . '@auspice.local';

        if (Auth::attempt(['email' => $email, 'password' => $request->password])) {
            $request->session()->regenerate();
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'login',
                'description' => 'Connexion de ' . Auth::user()->identifier,
                'ip_address' => $request->ip(),
            ]);
            return redirect()->intended('/admin');
        }

        return back()->withErrors(['identifier' => 'Identifiant ou mot de passe invalide.']);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'logout',
                'description' => 'Déconnexion de ' . $user->identifier,
                'ip_address' => $request->ip(),
            ]);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function claimFirstAdmin(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect('/login');
        }

        // Vérifie qu'aucun admin n'existe encore
        $hasAdmin = UserRole::whereIn('role', ['super_admin', 'admin'])->exists();
        if ($hasAdmin) {
            return back()->with('error', 'Un administrateur existe déjà.');
        }

        UserRole::create(['user_id' => $user->id, 'role' => 'super_admin']);
        UserRole::create(['user_id' => $user->id, 'role' => 'admin']);

        // Sync avec la table roles pour les permissions
        $roleSuper = Role::firstOrCreate(['key' => 'super_admin'], ['name' => 'Super Admin']);
        $roleAdmin = Role::firstOrCreate(['key' => 'admin'], ['name' => 'Administrateur']);
        $user->roles()->syncWithoutDetaching([$roleSuper->id, $roleAdmin->id]);

        return redirect('/admin')->with('success', 'Vous êtes maintenant le premier administrateur.');
    }

    public static function generateIdentifier(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $s = '';
        for ($i = 0; $i < 6; $i++) {
            $s .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        return 'AUS-' . $s;
    }

    public static function generatePassword(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $s = '';
        for ($i = 0; $i < 12; $i++) {
            $s .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        return $s;
    }
}
