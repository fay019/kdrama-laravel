<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.password_reset.tab_title') }}</title>
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
            <h1>{{ __('emails.password_reset.header_title') }}</h1>
        </div>

        <div class="content">
            <p>{{ __('emails.password_reset.greeting', ['name' => $user->name]) }}</p>

            <p>{{ __('emails.password_reset.intro') }}</p>

            <div class="password-box">
                {{ $newPassword }}
            </div>

            <div class="instructions">
                <h3>{{ __('emails.password_reset.instructions_title') }}</h3>
                <ol>
                    <li>{{ __('emails.password_reset.step_1') }}</li>
                    <li>{{ __('emails.password_reset.step_2') }}</li>
                    <li>{{ __('emails.password_reset.step_3') }}</li>
                    <li>{{ __('emails.password_reset.step_4') }}</li>
                    <li>{{ __('emails.password_reset.step_5') }}</li>
                </ol>
            </div>

            <p><span class="important">{{ __('emails.password_reset.important_title') }}</span></p>
            <ul>
                <li>{{ __('emails.password_reset.warning_1') }}</li>
                <li>{{ __('emails.password_reset.warning_2') }}</li>
                <li>{{ __('emails.password_reset.warning_3') }}</li>
                <li>{{ __('emails.password_reset.warning_4') }}</li>
            </ul>

            <p>{{ __('emails.password_reset.contact_admin') }}</p>

            <p>{{ __('emails.password_reset.regards') }}<br>
            {{ __('emails.password_reset.team_name') }}</p>
        </div>

        <div class="footer">
            <p>{{ __('emails.password_reset.automated_notice') }}</p>
            <p>{{ __('emails.password_reset.copyright', ['year' => date('Y')]) }}</p>
        </div>
    </div>
</body>
</html>