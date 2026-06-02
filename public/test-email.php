<?php
/**
 * Test Email Configuration
 * 
 * Ce script teste si la configuration d'email fonctionne correctement.
 * Accédez à : http://localhost:8000/test-email.php
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/fonctions.php';

$testResult = null;
$testError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testEmail = trim((string) ($_POST['test_email'] ?? ''));
    
    if ($testEmail === '') {
        $testError = 'Veuillez entrer une adresse email.';
    } elseif (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        $testError = 'L\'adresse email n\'est pas valide.';
    } else {
        try {
            require_once __DIR__ . '/vendor/autoload.php';
            
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configuration SMTP
            $mail->isSMTP();
            $mail->Host = MAIL_SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = MAIL_SMTP_USER;
            $mail->Password = MAIL_SMTP_PASSWORD;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = MAIL_SMTP_PORT;
            $mail->CharSet = 'UTF-8';
            
            // Email de test
            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mail->addAddress($testEmail);
            
            $mail->isHTML(true);
            $mail->Subject = '🧪 Test de Configuration Email - e-llusion';
            $mail->Body = '
                <html>
                    <body style="font-family: Arial, sans-serif;">
                        <h1>Test de configuration email</h1>
                        <p>✓ Votre configuration SMTP fonctionne correctement !</p>
                        <hr>
                        <p><strong>Détails :</strong></p>
                        <ul>
                            <li><strong>Expéditeur :</strong> ' . htmlspecialchars(MAIL_FROM_ADDRESS) . '</li>
                            <li><strong>Nom :</strong> ' . htmlspecialchars(MAIL_FROM_NAME) . '</li>
                            <li><strong>Serveur SMTP :</strong> ' . htmlspecialchars(MAIL_SMTP_HOST) . ':' . MAIL_SMTP_PORT . '</li>
                            <li><strong>Date :</strong> ' . date('Y-m-d H:i:s') . '</li>
                        </ul>
                        <hr>
                        <p>Les emails de réservation fonctionneront maintenant correctement !</p>
                    </body>
                </html>
            ';
            
            if ($mail->send()) {
                $testResult = 'success';
            } else {
                $testError = 'Erreur lors de l\'envoi : ' . $mail->ErrorInfo;
            }
        } catch (Exception $e) {
            $testError = 'Erreur : ' . $e->getMessage();
        }
    }
}

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email - e-llusion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        h1 {
            color: #333;
            margin-top: 0;
        }
        .info {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #f5c6cb;
        }
        form {
            margin: 20px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }
        input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 14px;
        }
        button {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        button:hover {
            background: #764ba2;
        }
        .config-info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #2196F3;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test de Configuration Email</h1>
        
        <div class="info">
            <p>Ce formulaire permet de tester si votre configuration SMTP fonctionne correctement.</p>
        </div>
        
        <?php if ($testResult === 'success'): ?>
            <div class="success">
                <h2>✓ Email envoyé avec succès !</h2>
                <p>Vérifiez votre boîte de réception pour confirmer la réception de l'email de test.</p>
                <p><strong>Configuration valide !</strong> Les emails de réservation fonctionneront correctement.</p>
            </div>
        <?php endif; ?>
        
        <?php if ($testError): ?>
            <div class="error">
                <h2>✗ Erreur lors de l'envoi</h2>
                <p><?= htmlspecialchars($testError); ?></p>
                <p><strong>Vérifiez :</strong></p>
                <ul>
                    <li>Que <code>config.php</code> est correctement configuré</li>
                    <li>Que le mot de passe d'application Gmail est correct</li>
                    <li>Que l'authentification 2FA est activée sur Gmail</li>
                    <li>La connexion Internet et le pare-feu</li>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <label for="test_email">Adresse email de test :</label>
            <input type="email" id="test_email" name="test_email" placeholder="votre-email@example.com" required>
            <button type="submit">Envoyer un email de test</button>
        </form>
        
        <div class="config-info">
            <h3>Configuration actuelle :</h3>
            <ul>
                <li><strong>Host :</strong> <code><?= htmlspecialchars(MAIL_SMTP_HOST); ?></code></li>
                <li><strong>Port :</strong> <code><?= MAIL_SMTP_PORT; ?></code></li>
                <li><strong>Utilisateur :</strong> <code><?= htmlspecialchars(MAIL_SMTP_USER); ?></code></li>
                <li><strong>Adresse d'origine :</strong> <code><?= htmlspecialchars(MAIL_FROM_ADDRESS); ?></code></li>
                <li><strong>Nom d'origine :</strong> <code><?= htmlspecialchars(MAIL_FROM_NAME); ?></code></li>
                <li><strong>Email activé :</strong> <code><?= MAIL_ENABLED ? 'OUI' : 'NON'; ?></code></li>
            </ul>
        </div>
        
        <div class="info">
            <h3>Besoin d'aide ?</h3>
            <p>Consultez le fichier <code>EMAIL_SETUP.md</code> pour les instructions complètes de configuration.</p>
        </div>
    </div>
</body>
</html>
