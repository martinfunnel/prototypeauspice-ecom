# Pages Auth — Documentation

## Vue d'ensemble

Pages de connexion et inscription pour les administrateurs.

**Routes :**
- `GET /login` → `AuthController@showLogin`
- `POST /login` → `AuthController@login`
- `GET /register` → `AuthController@showRegister`
- `POST /register` → `AuthController@register`
- `POST /logout` → `AuthController@logout`

**Vues :**
- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`

---

## Logique métier

### Connexion
```php
if (Auth::attempt($request->only('email', 'password'))) {
    $request->session()->regenerate();
    return redirect()->intended('/admin');
}
```

### Inscription
```php
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
]);
$user->assignRole('admin');
Auth::login($user);
```

---

## Design

- Carte centrée : bg-card, border-border, rounded-2xl, shadow-card
- Inputs : border-border, bg-background, focus:border-accent
- Bouton : bg-accent, text-accent-foreground, shadow-accent
- Erreurs : bg-destructive/10, border-destructive/20, text-destructive

---

## Prochaine page

→ [Page Admin](PAGE_ADMIN.md)
