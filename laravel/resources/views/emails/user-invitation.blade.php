<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bienvenue chez Auspice Market</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #f8f9fa; border-radius: 12px; padding: 30px;">
        <h1 style="color: #1a1a1a; margin-bottom: 20px;">Bienvenue, {{ $fullName ?: 'collaborateur' }} !</h1>

        <p>Votre compte administrateur a été créé sur <strong>Auspice Market</strong>.</p>

        <div style="background: #fff; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #e9ecef;">
            <p style="margin: 0 0 10px 0;"><strong>Votre identifiant :</strong></p>
            <code style="background: #f1f3f5; padding: 8px 12px; border-radius: 4px; font-size: 16px; display: inline-block;">{{ $identifier }}</code>
        </div>

        <p>Cliquez sur le bouton ci-dessous pour définir votre mot de passe :</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $resetUrl }}" style="background: #0d6efd; color: white; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;">Définir mon mot de passe</a>
        </div>

        <p style="font-size: 13px; color: #6c757d;">Ce lien est valable pendant 60 minutes. Si vous n'avez pas demandé ce compte, ignorez cet email.</p>

        <hr style="border: none; border-top: 1px solid #e9ecef; margin: 30px 0;">
        <p style="font-size: 12px; color: #adb5bd;">Auspice Market — Santé Ivoire</p>
    </div>
</body>
</html>
