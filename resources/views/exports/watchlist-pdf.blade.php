<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Watchlist Export</title>
    <style>
        @page {
            header: page-header;
            footer: page-footer;
            margin-top: 35mm; /* Espace pour le header */
            margin-bottom: 15mm;
            margin-left: 10mm;
            margin-right: 10mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif; /* Meilleur support Unicode pour mPDF */
            background-color: #0f172a; /* Slate 900 */
            color: #e2e8f0; /* Slate 200 */
            font-size: 11px;
        }

        /* Tables for layout (mPDF compatible) */
        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        td {
            vertical-align: top;
            padding: 0;
        }

        /* --- HEADER --- */
        .header-bg {
            background-color: #7f1d1d; /* Red 900 */
            border-bottom: 4px solid #ef4444; /* Red 500 */
            padding: 20px;
            color: white;
        }

        .header-title {
            font-size: 26px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header-meta {
            margin-top: 5px;
            font-size: 10px;
            color: #fca5a5; /* Red 200 */
        }

        /* --- STATS BAR --- */
        .stats-container {
            background-color: #1e293b; /* Slate 800 */
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #334155;
        }

        .stat-badge {
            font-weight: bold;
            color: #fff;
            background-color: #334155;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            margin-right: 10px;
        }

        .stat-value {
            color: #38bdf8; /* Sky 400 */
        }

        /* --- DRAMA CARD --- */
        .card {
            background-color: #1e293b; /* Slate 800 */
            border: 1px solid #334155; /* Slate 700 */
            border-radius: 8px;
            margin-bottom: 15px;
            padding: 15px;
        }

        /* Poster */
        .poster-cell {
            width: 100px; /* Fixed width */
            padding-right: 15px;
        }

        .poster-img {
            width: 100px;
            height: 150px;
            border-radius: 6px;
            object-fit: cover;
            border: 1px solid #475569;
        }

        .no-poster {
            width: 100px;
            height: 150px;
            background-color: #0f172a;
            text-align: center;
            line-height: 150px;
            color: #64748b;
            font-size: 30px;
            border-radius: 6px;
            border: 1px solid #334155;
        }

        /* Content */
        .title {
            font-size: 16px;
            font-weight: bold;
            color: #fff;
            margin-bottom: 8px;
        }

        /* Badges Row */
        .badges-row {
            margin-bottom: 10px;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            color: white;
            margin-right: 5px;
            text-transform: uppercase;
        }

        .badge-status-watched { background-color: #16a34a; } /* Green 600 */
        .badge-status-watching { background-color: #ea580c; } /* Orange 600 */
        .badge-status-towatch { background-color: #475569; } /* Slate 600 */

        .badge-rating { background-color: #8b5cf6; } /* Violet 500 */
        .badge-score { background-color: #f59e0b; color: #000; } /* Amber 500 */
        .badge-year { background-color: #0f172a; border: 1px solid #334155; }

        /* Genres */
        .genres {
            font-size: 10px;
            color: #94a3b8; /* Slate 400 */
            margin-bottom: 8px;
        }

        .genre-tag {
            color: #cbd5e1;
        }

        /* Synopsis */
        .synopsis {
            font-size: 10px;
            line-height: 1.5;
            color: #cbd5e1; /* Slate 300 */
            background-color: #0f172a;
            padding: 8px;
            border-radius: 4px;
            border-left: 3px solid #334155;
            text-align: justify;
        }

        /* Footer */
        .footer-text {
            text-align: center;
            font-size: 8px;
            color: #475569;
            border-top: 1px solid #334155;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <!-- Header definition -->
    <htmlpageheader name="page-header">
        <div class="header-bg">
            <div class="header-title">🍿 KDrama Watchlist</div>
            <div class="header-meta">
                Exporté par <b>{{ $user->name }}</b> • {{ now()->format('d/m/Y') }}
            </div>
        </div>
    </htmlpageheader>

    <htmlpagefooter name="page-footer">
        <div class="footer-text">
            Page {PAGENO} sur {nbpg} • Généré par KDrama Laravel
        </div>
    </htmlpagefooter>

    <!-- Stats Block (Page 1) -->
    <div class="stats-container">
        <span class="stat-badge">TOTAL <span class="stat-value">{{ $totalItems }}</span></span>
        <span class="stat-badge">REGARDÉS <span class="stat-value">{{ $watchedCount }}</span></span>
        <span class="stat-badge">EN COURS <span class="stat-value">{{ $watchingCount }}</span></span>
        <span class="stat-badge">À VOIR <span class="stat-value">{{ $toWatchCount }}</span></span>
    </div>

    <!-- Content Loop -->
    @foreach($pages as $pageItems)
        @foreach($pageItems as $item)
            @php
                // Data Logic
                $displayTitle = $item['display_title'] ?? $item['name'] ?? 'Titre Inconnu';
                $year = !empty($item['first_air_date']) ? \Carbon\Carbon::parse($item['first_air_date'])->year : '';

                // Status Logic
                $statusClass = 'badge-status-towatch';
                $statusText = 'À VOIR';
                if (!empty($item['is_watched'])) {
                    $statusClass = 'badge-status-watched';
                    $statusText = 'VU';
                } elseif (!empty($item['is_watching'])) {
                    $statusClass = 'badge-status-watching';
                    $statusText = 'EN COURS';
                }

                // Rating Logic
                $userRating = !empty($item['rating']) ? str_repeat('★', $item['rating']) : '';
                $tmdbScore = !empty($item['vote_average']) ? number_format($item['vote_average'], 1) : '';

                // Genres
                $genres = [];
                if(!empty($item['genres']) && is_array($item['genres'])) {
                    foreach($item['genres'] as $g) $genres[] = is_array($g) ? $g['name'] : $g;
                }
                $genresStr = implode(' • ', $genres);

                $synopsis = $item['overview'] ?? '';
            @endphp

            <div class="card">
                <table>
                    <tr>
                        <!-- Left: Poster -->
                        <td class="poster-cell">
                            @if(!empty($selectedColumns['poster']))
                                @if(!empty($item['poster_url']))
                                    <img src="{{ $item['poster_url'] }}" class="poster-img">
                                @else
                                    <div class="no-poster">?</div>
                                @endif
                            @endif
                        </td>

                        <!-- Right: Info -->
                        <td>
                            <!-- Title -->
                            <div class="title">{{ $displayTitle }}</div>

                            <!-- Badges Row -->
                            <div class="badges-row">
                                @if(!empty($selectedColumns['status']))
                                    <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                @endif

                                @if(!empty($selectedColumns['year']) && $year)
                                    <span class="badge badge-year">{{ $year }}</span>
                                @endif

                                @if(!empty($selectedColumns['vote_average']) && $tmdbScore)
                                    <span class="badge badge-score">TMDB {{ $tmdbScore }}</span>
                                @endif

                                @if(!empty($selectedColumns['rating']) && $userRating)
                                    <span class="badge badge-rating">{{ $userRating }}</span>
                                @endif
                            </div>

                            <!-- Genres -->
                            @if(!empty($selectedColumns['genres']) && $genresStr)
                                <div class="genres">
                                    🎭 <span class="genre-tag">{{ $genresStr }}</span>
                                </div>
                            @endif

                            <!-- Synopsis -->
                            @if(!empty($selectedColumns['synopsis']) && $synopsis)
                                <div class="synopsis">
                                    {{ \Illuminate\Support\Str::limit($synopsis, 280) }}
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        @endforeach

        @if(!$loop->last)
            <pagebreak />
        @endif
    @endforeach

</body>
</html>
