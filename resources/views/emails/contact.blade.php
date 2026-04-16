<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ __('emails.contact.tab_title') }}</title>
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
            <h1>{{ __('emails.contact.header_title') }}</h1>
        </div>

        <p>{{ __('emails.contact.intro') }}</p>

        <div class="content-section">
            <h3>{{ __('emails.contact.sender_info_title') }}</h3>
            <p>
                <strong>{{ __('emails.contact.sender_name') }}</strong> {{ $name }}<br>
                <strong>{{ __('emails.contact.sender_email') }}</strong> <a href="mailto:{{ $email }}">{{ $email }}</a>
            </p>
        </div>

        <div class="content-section">
            <h3>{{ __('emails.contact.subject_title') }}</h3>
            <p>{{ $subject_text }}</p>
        </div>

        <div class="content-section">
            <h3>{{ __('emails.contact.message_title') }}</h3>
            <p style="white-space: pre-wrap; word-wrap: break-word;">{{ $message_text }}</p>
        </div>

        @if($page_url)
            <div class="content-section">
                <h3>Page Source</h3>
                <p><a href="{{ $page_url }}">{{ $page_url }}</a></p>
            </div>
        @endif

        @if($drama_image)
            <div class="content-section">
                <h3>Drama Image</h3>
                <img src="{{ $drama_image }}" alt="Drama Image" style="max-width: 100%; height: auto; border-radius: 4px; border: 1px solid #e5e7eb;">
            </div>
        @endif

        <p>
            <strong>{{ __('emails.contact.reply_text') }}</strong> <a href="mailto:{{ $email }}?subject=Re: {{ $subject_text }}">{{ __('emails.contact.reply_link') }}</a> {{ __('emails.contact.reply_or') }} {{ $email }}
        </p>

        <div class="footer">
            <p>
                <strong>IP :</strong> {{ $ip_address ?? 'N/A' }}<br>
                {{ __('emails.contact.footer_notice') }}<br>
                <small>{{ __('emails.contact.footer_warning') }}</small>
            </p>
        </div>
    </div>
</body>
</html>
