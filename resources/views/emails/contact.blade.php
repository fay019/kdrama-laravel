<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nouveau message de contact</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 30px -30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content-section {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9fafb;
            border-left: 4px solid #ef4444;
            border-radius: 4px;
        }
        .content-section h3 {
            margin-top: 0;
            color: #dc2626;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
        }
        a {
            color: #ef4444;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 Nouveau Message de Contact</h1>
        </div>

        <p>Bonjour,</p>
        <p>Vous avez reçu un nouveau message via le formulaire de contact du site KDrama Hub.</p>

        <div class="content-section">
            <h3>👤 Informations de l'expéditeur</h3>
            <p>
                <strong>Nom :</strong> {{ $name }}<br>
                <strong>Email :</strong> <a href="mailto:{{ $email }}">{{ $email }}</a>
            </p>
        </div>

        <div class="content-section">
            <h3>📌 Sujet</h3>
            <p>{{ $subject_text }}</p>
        </div>

        <div class="content-section">
            <h3>💬 Message</h3>
            <p style="white-space: pre-wrap; word-wrap: break-word;">{{ $message_text }}</p>
        </div>

        <p>
            <strong>Pour répondre :</strong> Cliquez sur <a href="mailto:{{ $email }}?subject=Re: {{ $subject_text }}">répondre par email</a> ou utilisez l'adresse {{ $email }}
        </p>

        <div class="footer">
            <p>
                Message automatique - KDrama Hub<br>
                <small>Ne répondez pas à cet email. Utilisez la fonction "Répondre" de votre client email.</small>
            </p>
        </div>
    </div>
</body>
</html>
