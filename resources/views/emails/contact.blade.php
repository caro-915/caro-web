<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message de contact - ElSayara</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .logo {
            font-size: 28px;
            font-weight: 900;
            color: #1f2937;
            letter-spacing: 2px;
        }
        .badge {
            display: inline-block;
            background: #ec4899;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 10px;
        }
        h2 {
            color: #1f2937;
            margin-top: 0;
        }
        .info-block {
            background: #f9fafb;
            border-left: 4px solid #1f2937;
            padding: 15px;
            margin: 15px 0;
        }
        .info-block strong {
            color: #1f2937;
        }
        .message-content {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            white-space: pre-wrap;
        }
        .meta {
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
            margin-top: 25px;
        }
        .footer {
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">ELSAYARA</div>
            <span class="badge">📧 Nouveau message</span>
        </div>

        <h2>{{ $subject }}</h2>

        <div class="info-block">
            <p><strong>De:</strong> {{ $contactName }}</p>
            <p><strong>Email:</strong> <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></p>
            @if($contactPhone)
                <p><strong>Téléphone:</strong> {{ $contactPhone }}</p>
            @endif
            <p><strong>Envoyé le:</strong> {{ $sentAt }}</p>
        </div>

        <h3>Message:</h3>
        <div class="message-content">{{ $body }}</div>

        <div class="meta">
            <p><strong>Informations techniques:</strong></p>
            @if($ip)
                <p>IP: {{ $ip }}</p>
            @endif
            @if($userAgent)
                <p>User-Agent: {{ Str::limit($userAgent, 100) }}</p>
            @endif
        </div>

        <div class="footer">
            <p>Ce message a été envoyé via le formulaire de contact ElSayara.</p>
            <p>Pour répondre, utilisez directement la fonction "Répondre" de votre messagerie.</p>
        </div>
    </div>
</body>
</html>
