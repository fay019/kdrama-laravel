<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Watchlist Export</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #0f172a;
            color: #e2e8f0;
            line-height: 1.5;
        }

        .page {
            page-break-after: always;
            padding: 30px;
            background-color: #0f172a;
        }

        .page:last-child {
            page-break-after: avoid;
        }

        .page:not(:first-child) .header {
            display: none;
        }

        .page:not(:first-child) .stats-line {
            display: none;
        }

        .page:not(:first-child) .options-box {
            display: none;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 20px 25px;
            margin: -30px -30px 20px -30px;
            border-bottom: 5px solid #991b1b;
            border-radius: 0;
        }

        .header h1 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.9);
        }

        /* Stats - UNE LIGNE COLOREE */
        .stats-line {
            background: linear-gradient(90deg, #1e293b 0%, #0f172a 100%);
            border-left: 5px solid #ef4444;
            border-radius: 0;
            padding: 12px 15px;
            margin-bottom: 15px;
            font-size: 12px;
            color: #cbd5e1;
            line-height: 1.6;
        }

        .stats-line strong {
            color: #ef4444;
            font-weight: bold;
            font-size: 13px;
        }

        /* Options */
        .options-box {
            background-color: #1e293b;
            border-left: 4px solid #8b5cf6;
            border-radius: 0;
            padding: 12px 15px;
            margin-bottom: 15px;
            font-size: 11px;
            line-height: 1.5;
        }

        .options-box .title {
            font-weight: bold;
            color: #8b5cf6;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .options-box .option {
            color: #cbd5e1;
            margin-bottom: 3px;
        }

        /* Item Card */
        .item-row {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-left: 4px solid #ef4444;
            margin-bottom: 18px;
            border-radius: 0;
            padding: 15px;
            page-break-inside: avoid;
        }

        .item-content {
            display: flex;
            gap: 15px;
        }

        .item-poster {
            flex-shrink: 0;
            width: 135px;
        }

        .poster-img {
            width: 135px;
            height: 203px;
            object-fit: cover;
            border: 2px solid #ef4444;
            border-radius: 4px;
            display: block;
        }

        .no-poster {
            width: 135px;
            height: 203px;
            background-color: #334155;
            border: 2px solid #475569;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 24px;
        }

        .item-info {
            flex: 1;
        }

        .item-title {
            font-weight: bold;
            color: #f1f5f9;
            font-size: 14px;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .item-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 8px;
        }

        .meta-item {
            font-size: 11px;
            color: #cbd5e1;
        }

        .meta-label {
            color: #fbbf24;
            font-weight: bold;
            display: inline-block;
            margin-right: 4px;
            font-size: 12px;
        }

        .meta-value {
            color: #f1f5f9;
        }

        .genres-section {
            font-size: 11px;
            color: #a78bfa;
            margin-bottom: 8px;
            padding-top: 6px;
            border-top: 1px solid #334155;
        }

        .genres-label {
            font-weight: bold;
            color: #a78bfa;
            display: inline-block;
            margin-right: 4px;
            font-size: 12px;
        }

        .item-synopsis {
            font-size: 11px;
            color: #cbd5e1;
            font-style: italic;
            margin-top: 8px;
            padding: 8px 10px;
            background-color: #0f172a;
            border-left: 3px solid #8b5cf6;
            border-radius: 2px;
            line-height: 1.4;
            page-break-inside: avoid;
        }

        .synopsis-label {
            font-weight: bold;
            color: #8b5cf6;
            font-style: normal;
            display: block;
            margin-bottom: 4px;
        }

        /* Page number */
        .page-number {
            text-align: right;
            font-size: 10px;
            color: #64748b;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #334155;
        }

        /* Empty state */
        .empty {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
            font-size: 14px;
        }
    </style>
</head>
<body>
    @forelse($pages as $pageIndex => $pageItems)
        <div class="page">
            <!-- Header (page 1 only) -->
            @if($pageIndex === 0)
                <div class="header">
                    <h1>🍿 KDrama Hub - Watchlist</h1>
                    <div class="subtitle">Export de {{ $user->name }} • {{ now()->format('d/m/Y H:i') }}</div>
                </div>

                <!-- Stats -->
                <div class="stats-line">
                    📊 <strong>Total: {{ $totalItems }}</strong> | 📺 <strong>A regarder: {{ $toWatchCount }}</strong> | 🎬 <strong>En cours: {{ $watchingCount }}</strong> | ✅ <strong>Regardes: {{ $watchedCount }}</strong>
                </div>

                <!-- Options -->
                <div class="options-box">
                    <div class="title">⚙️ Options selectionnees</div>
                    <div class="option">• Filtres: {{ $displayOptions['filters'] }}</div>
                    <div class="option">• Colonnes: {{ $displayOptions['columns'] }}</div>
                    <div class="option">• Tri: {{ $displayOptions['sort'] }}</div>
                </div>
            @endif

            <!-- Items -->
            @if(count($pageItems) > 0)
                @foreach($pageItems as $item)
                    @php
                        $frName = $item['name'] ?? null;
                        $enName = $item['en_name'] ?? null;
                        $originalName = $item['original_name'] ?? null;

                        if (!empty($frName)) {
                            $displayTitle = $frName;
                        } elseif (!empty($enName)) {
                            $displayTitle = $enName;
                        } else {
                            $displayTitle = $originalName ?? 'N/A';
                        }

                        $year = $item['first_air_date'] ? \Carbon\Carbon::parse($item['first_air_date'])->year : 'N/A';
                        if ($item['is_watched']) {
                            $status = 'Vu';
                        } elseif ($item['is_watching'] ?? false) {
                            $status = 'En cours';
                        } else {
                            $status = 'A voir';
                        }
                        $rating = '';
                        if ($item['rating'] ?? false) {
                            $rating = match($item['rating']) {
                                1 => 'Pas bien',
                                2 => 'Bien',
                                3 => 'Tres bien',
                                default => ''
                            };
                        }
                        $vote = number_format($item['vote_average'] ?? 0, 1);
                    @endphp

                    <div class="item-row">
                        <div class="item-content">
                            <!-- Poster -->
                            @if(($selectedColumns['poster'] ?? false))
                                <div class="item-poster">
                                    @if($item['poster_base64'] ?? false)
                                        <img src="{{ $item['poster_base64'] }}" alt="Poster" class="poster-img">
                                    @else
                                        <div class="no-poster">-</div>
                                    @endif
                                </div>
                            @endif

                            <!-- Info -->
                            <div class="item-info">
                                <div class="item-title">{{ $displayTitle }}</div>

                                <div class="item-meta">
                                    @if($selectedColumns['status'] ?? true)
                                        <div class="meta-item">
                                            <span class="meta-label">📌</span>
                                            <span class="meta-value">{{ $status }}</span>
                                        </div>
                                    @endif
                                    @if($selectedColumns['year'] ?? true)
                                        <div class="meta-item">
                                            <span class="meta-label">📅</span>
                                            <span class="meta-value">{{ $year }}</span>
                                        </div>
                                    @endif
                                    @if($selectedColumns['vote_average'] ?? true)
                                        <div class="meta-item">
                                            <span class="meta-label">⭐</span>
                                            <span class="meta-value">{{ $vote }}/10</span>
                                        </div>
                                    @endif
                                    @if($selectedColumns['rating'] ?? true && !empty($rating))
                                        <div class="meta-item">
                                            <span class="meta-label">💭</span>
                                            <span class="meta-value">{{ $rating }}</span>
                                        </div>
                                    @endif
                                </div>

                                @php
                                    $genres = $item['genres'] ?? [];
                                    $genres = is_array($genres) ? $genres : [];
                                @endphp
                                @if($selectedColumns['genres'] ?? true && !empty($genres))
                                    <div class="genres-section">
                                        <span class="genres-label">🎭</span>
                                        {{ implode(', ', array_map(fn($g) => $g['name'] ?? '', $genres)) }}
                                    </div>
                                @endif

                                @php
                                    $networks = $item['networks'] ?? [];
                                    $networks = is_array($networks) ? $networks : [];
                                @endphp
                                @if($selectedColumns['networks'] ?? false && !empty($networks))
                                    <div class="genres-section">
                                        <span class="genres-label">📺</span>
                                        {{ implode(', ', array_map(fn($n) => $n['name'] ?? '', $networks)) }}
                                    </div>
                                @endif

                                @if($selectedColumns['synopsis'] ?? false && !empty($item['overview']))
                                    <div class="item-synopsis">
                                        <span class="synopsis-label">📖 Synopsis</span>
                                        {{ Str::limit($item['overview'], 180, '...') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty">
                    📭 Aucun element a afficher
                </div>
            @endif

            <!-- Page number -->
            <div class="page-number">Page {{ $pageIndex + 1 }} of {{ count($pages) }}</div>
        </div>
    @empty
        <div class="page">
            <div class="header">
                <h1>🍿 KDrama Hub - Watchlist</h1>
            </div>
            <div class="empty">
                📭 Votre watchlist est vide
            </div>
        </div>
    @endforelse
</body>
</html>
