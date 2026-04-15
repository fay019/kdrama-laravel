<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.export.tab_title') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 40px 20px;
        }
        .greeting {
            font-size: 16px;
            color: #1f2937;
            margin-bottom: 20px;
        }
        .stats {
            background-color: #f9fafb;
            border-left: 4px solid #ef4444;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .stats h3 {
            margin: 0 0 15px 0;
            color: #1f2937;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }
        .stat-item:last-child {
            border-bottom: none;
        }
        .stat-item strong {
            color: #1f2937;
        }
        .stat-item span {
            color: #ef4444;
            font-weight: 600;
        }
        .footer {
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background-color: #ef4444;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            transition: background-color 0.2s;
        }
        .button:hover {
            background-color: #dc2626;
        }
        .format-badge {
            display: inline-block;
            background-color: #fef3c7;
            color: #b45309;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>{{ __('emails.export.header_title') }}</h1>
            <p>Format: <strong>{{ strtoupper($format) }}</strong>
            @if($sentByAdmin)
                <br><span style="font-size: 12px; opacity: 0.9;">{{ __('emails.export.sent_by_admin') }}</span>
            @endif
            </p>
        </div>

        <!-- Content -->
        <div class="content">
            @if($sentByAdmin)
                <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <p style="color: #92400e; margin: 0; font-size: 14px;">
                        <strong>ℹ️</strong> {{ __('emails.export.admin_notice', ['name' => $sentByAdmin->name]) }}
                    </p>
                </div>
            @endif

            <div class="greeting">
                {{ __('emails.export.greeting', ['name' => $user->name]) }}<br><br>
                <span class="format-badge">{{ strtoupper($format) }}</span>
                @if($sentByAdmin)
                    {{ __('emails.export.body_admin') }}
                @else
                    {{ __('emails.export.body_user') }}
                @endif
            </div>

            <!-- Stats -->
            <div class="stats">
                <h3>{{ __('emails.export.stats_title') }}</h3>
                <div class="stat-item">
                    <strong>{{ __('emails.export.stat_total') }}</strong>
                    <span>{{ $stats['totalItems'] ?? 0 }}</span>
                </div>
                <div class="stat-item">
                    <strong>{{ __('emails.export.stat_watched') }}</strong>
                    <span>{{ $stats['watchedCount'] ?? 0 }}</span>
                </div>
                <div class="stat-item">
                    <strong>{{ __('emails.export.stat_to_watch') }}</strong>
                    <span>{{ $stats['toWatchCount'] ?? 0 }}</span>
                </div>
            </div>

            <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
                ✅ <strong>{{ __('emails.export.file_label') }}</strong> {{ $filename }}<br>
                📅 <strong>{{ __('emails.export.date_label') }}</strong> {{ now()->format('d/m/Y à H:i') }}
            </p>

            <div class="button-container">
                @php
                    $appUrl = str_contains(config('app.url'), 'localhost')
                        ? 'http://kdrama-laravel.test'
                        : config('app.url');
                @endphp
                <a href="{{ $appUrl }}/watchlist" class="button">
                    {{ __('emails.export.cta_btn') }}
                </a>
            </div>

            <p style="color: #6b7280; font-size: 13px; margin-top: 30px; line-height: 1.6;">
                <strong>{{ __('emails.export.help_text') }}</strong> <a href="{{ $appUrl }}/contact" style="color: #ef4444; text-decoration: none;">{{ __('emails.export.contact_link') }}</a>.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0;">
                {{ __('emails.export.copyright', ['year' => now()->year]) }}<br>
                <a href="{{ $appUrl }}" style="color: #6b7280; text-decoration: none;">{{ __('emails.export.visit_link') }}</a>
            </p>
        </div>
    </div>
</body>
</html>
