<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #ef4444;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #ef4444;
            margin: 0;
            font-size: 28px;
        }
        .content {
            margin: 20px 0;
        }
        .password-box {
            background-color: #1e293b;
            color: #fff;
            padding: 20px;
            border-radius: 6px;
            text-align: center;
            margin: 25px 0;
            font-family: monospace;
            font-size: 18px;
            letter-spacing: 2px;
            border: 2px solid #ef4444;
        }
        .instructions {
            background-color: #f8fafc;
            padding: 15px;
            border-left: 4px solid #3b82f6;
            margin: 20px 0;
        }
        .instructions ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .instructions li {
            margin: 8px 0;
        }
        .important {
            color: #dc2626;
            font-weight: bold;
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
            color: #3b82f6;
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
            <h1>🔐 Password Reset</h1>
        </div>

        <div class="content">
            <p>Hi <strong>{{ $user->name }}</strong>,</p>

            <p>An administrator has reset your password for security purposes. Here is your new temporary password:</p>

            <div class="password-box">
                {{ $newPassword }}
            </div>

            <div class="instructions">
                <h3>🔑 What You Need To Do:</h3>
                <ol>
                    <li>Log in to your account with the temporary password above</li>
                    <li>You will be redirected to change your password</li>
                    <li>Create a <span class="important">strong and unique password</span></li>
                    <li>Confirm the new password</li>
                    <li>You'll then have access to all features</li>
                </ol>
            </div>

            <p><span class="important">⚠️ Important:</span></p>
            <ul>
                <li>This temporary password is valid for your next login only</li>
                <li>You <strong>must change</strong> your password when prompted</li>
                <li>Do not share this password with anyone</li>
                <li>Keep this email secure or delete it after logging in</li>
            </ul>

            <p>If you didn't request this password reset or have any questions, please contact the administrator immediately.</p>

            <p>Best regards,<br>
            The KDrama Hub Team 🎬</p>
        </div>

        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>KDrama Hub © {{ date('Y') }} - All rights reserved</p>
        </div>
    </div>
</body>
</html>