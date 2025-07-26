<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bienvenue sur {{ config('app.name') }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: #374151;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 30px 0;
        }
        .logo {
            max-height: 60px;
            margin-bottom: 20px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .card-body {
            padding: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #ec4899, #f59e0b);
            color: white !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(236, 72, 153, 0.3);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(236, 72, 153, 0.3);
        }
        .credentials {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #9ca3af;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <!-- Remplacez par votre logo -->
            <img src="https://via.placeholder.com/180x60?text={{ urlencode(config('app.name')) }}" alt="{{ config('app.name') }}" class="logo">
        </div>

        <div class="card">
            <div class="card-body">
                <h1 style="font-size: 24px; font-weight: 700; color: #111827; margin-bottom: 24px;">
                    Bienvenue sur {{ config('app.name') }} ✨
                </h1>

                <p style="margin-bottom: 16px;">
                    Un compte a été créé pour vous avec les identifiants suivants :
                </p>

                <div class="credentials">
                    <p style="margin: 8px 0;">
                        <strong style="color: #4b5563;">Email :</strong>
                        <span style="color: #111827;">{{ $user->email }}</span>
                    </p>
                    <p style="margin: 8px 0;">
                        <strong style="color: #4b5563;">Mot de passe temporaire :</strong>
                        <span style="color: #111827; font-family: monospace;">{{ $temporaryPassword }}</span>
                    </p>
                </div>

                <p style="margin-bottom: 24px;">
                    Pour accéder à votre compte, cliquez sur le bouton ci-dessous :
                </p>

                <div style="text-align: center; margin: 30px 0;">
                    <a href="{{ route('login') }}" class="btn">
                        Se connecter à mon compte
                    </a>
                </div>

                <div style="background-color: #fef2f2; border-left: 4px solid #f87171; padding: 12px; border-radius: 4px; margin: 24px 0;">
                    <p style="color: #b91c1c; margin: 0;">
                        <strong>Conseil de sécurité :</strong> Nous vous recommandons de changer votre mot de passe après votre première connexion.
                    </p>
                </div>

                <p style="margin-top: 24px; color: #6b7280;">
                    Si vous n'avez pas créé ce compte, veuillez ignorer cet email ou contacter notre support.
                </p>
            </div>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
            <p style="margin-top: 8px;">
                <a href="{{ config('app.url') }}" style="color: #9ca3af; text-decoration: none;">Visitez notre site</a> |
                <a href="#" style="color: #9ca3af; text-decoration: none;">Confidentialité</a> |
                <a href="#" style="color: #9ca3af; text-decoration: none;">Aide</a>
            </p>
        </div>
    </div>
</body>
</html>