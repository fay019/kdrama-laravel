<div class="flex flex-col md:flex-row gap-6">
    <!-- Photo & Infos de base -->
    <div class="w-full md:w-1/3 flex flex-col items-center">
        @if($actor['profile_path'])
            <img src="https://image.tmdb.org/t/p/w500{{ $actor['profile_path'] }}"
                 alt="{{ $actor['name'] }}"
                 class="w-48 h-64 md:w-full md:h-auto rounded-xl object-cover shadow-lg border-2 border-slate-700">
        @else
            <div class="w-48 h-64 md:w-full md:h-auto rounded-xl bg-slate-800 flex items-center justify-center border-2 border-slate-700">
                <span class="text-6xl">👤</span>
            </div>
        @endif

        <div class="mt-4 w-full space-y-3">
            <div class="bg-slate-800/50 p-3 rounded-lg border border-slate-700">
                <p class="text-slate-400 text-xs uppercase tracking-wider font-bold">{{ __('show.actor_real_name') }}</p>
                <p class="text-white font-medium">{{ $actor['latin_name'] ?? $actor['name'] }}</p>
                @if(($actor['original_name'] ?? $actor['name']) !== ($actor['latin_name'] ?? $actor['name']))
                    <p class="text-slate-400 text-sm">{{ $actor['original_name'] ?? $actor['name'] }}</p>
                @endif
            </div>

            @if($actor['birthday'])
                <div class="bg-slate-800/50 p-3 rounded-lg border border-slate-700">
                    <p class="text-slate-400 text-xs uppercase tracking-wider font-bold">{{ __('show.actor_birth_date') }}</p>
                    <p class="text-white font-medium">
                        {{ \Carbon\Carbon::parse($actor['birthday'])->format('d/m/Y') }}
                        @if(!$actor['deathday'])
                            <span class="text-slate-500 text-sm">({{ \Carbon\Carbon::parse($actor['birthday'])->age }} {{ trans_choice('common.years', \Carbon\Carbon::parse($actor['birthday'])->age) }})</span>
                        @endif
                    </p>
                </div>
            @endif

            @if($actor['place_of_birth'])
                <div class="bg-slate-800/50 p-3 rounded-lg border border-slate-700">
                    <p class="text-slate-400 text-xs uppercase tracking-wider font-bold">{{ __('show.actor_place_of_birth') }}</p>
                    <p class="text-white font-medium text-sm">{{ $actor['place_of_birth'] }}</p>
                </div>
            @endif

            <!-- Social Media Links (Simple Icons) -->
            <div class="space-y-2">
                @if(isset($actor['external_ids']['instagram_id']) && $actor['external_ids']['instagram_id'])
                    <a href="{{ 'https://instagram.com/' . $actor['external_ids']['instagram_id'] }}" target="_blank" class="flex items-center justify-center gap-2 w-full py-2 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold hover:opacity-90 transition text-white">
                        <svg class="w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" fill="currentColor"><title>Instagram</title><path d="M7.0301.084c-1.2768.0602-2.1487.264-2.911.5634-.7888.3075-1.4575.72-2.1228 1.3877-.6652.6677-1.075 1.3368-1.3802 2.127-.2954.7638-.4956 1.6365-.552 2.914-.0564 1.2775-.0689 1.6882-.0626 4.947.0062 3.2586.0206 3.6671.0825 4.9473.061 1.2765.264 2.1482.5635 2.9107.308.7889.72 1.4573 1.388 2.1228.6679.6655 1.3365 1.0743 2.1285 1.38.7632.295 1.6361.4961 2.9134.552 1.2773.056 1.6884.069 4.9462.0627 3.2578-.0062 3.668-.0207 4.9478-.0814 1.28-.0607 2.147-.2652 2.9098-.5633.7889-.3086 1.4578-.72 2.1228-1.3881.665-.6682 1.0745-1.3378 1.3795-2.1284.2957-.7632.4966-1.636.552-2.9124.056-1.2809.0692-1.6898.063-4.948-.0063-3.2583-.021-3.6668-.0817-4.9465-.0607-1.2797-.264-2.1487-.5633-2.9117-.3084-.7889-.72-1.4568-1.3876-2.1228C21.2982 1.33 20.628.9208 19.8378.6165 19.074.321 18.2017.1197 16.9244.0645 15.6471.0093 15.236-.005 11.977.0014 8.718.0076 8.31.0215 7.0301.0839m.1402 21.6932c-1.17-.0509-1.8053-.2453-2.2287-.408-.5606-.216-.96-.4771-1.3819-.895-.422-.4178-.6811-.8186-.9-1.378-.1644-.4234-.3624-1.058-.4171-2.228-.0595-1.2645-.072-1.6442-.079-4.848-.007-3.2037.0053-3.583.0607-4.848.05-1.169.2456-1.805.408-2.2282.216-.5613.4762-.96.895-1.3816.4188-.4217.8184-.6814 1.3783-.9003.423-.1651 1.0575-.3614 2.227-.4171 1.2655-.06 1.6447-.072 4.848-.079 3.2033-.007 3.5835.005 4.8495.0608 1.169.0508 1.8053.2445 2.228.408.5608.216.96.4754 1.3816.895.4217.4194.6816.8176.9005 1.3787.1653.4217.3617 1.056.4169 2.2263.0602 1.2655.0739 1.645.0796 4.848.0058 3.203-.0055 3.5834-.061 4.848-.051 1.17-.245 1.8055-.408 2.2294-.216.5604-.4763.96-.8954 1.3814-.419.4215-.8181.6811-1.3783.9-.4224.1649-1.0577.3617-2.2262.4174-1.2656.0595-1.6448.072-4.8493.079-3.2045.007-3.5825-.006-4.848-.0608M16.953 5.5864A1.44 1.44 0 1 0 18.39 4.144a1.44 1.44 0 0 0-1.437 1.4424M5.8385 12.012c.0067 3.4032 2.7706 6.1557 6.173 6.1493 3.4026-.0065 6.157-2.7701 6.1506-6.1733-.0065-3.4032-2.771-6.1565-6.174-6.1498-3.403.0067-6.156 2.771-6.1496 6.1738M8 12.0077a4 4 0 1 1 4.008 3.9921A3.9996 3.9996 0 0 1 8 12.0077"></path></svg>
                        {{ __('show.social_instagram') }}
                    </a>
                @endif

                @if(isset($actor['external_ids']['facebook_id']) && $actor['external_ids']['facebook_id'])
                    <a href="{{ 'https://facebook.com/' . $actor['external_ids']['facebook_id'] }}" target="_blank" class="flex items-center justify-center gap-2 w-full py-2 bg-blue-600 rounded-lg font-bold hover:opacity-90 transition text-white">
                        <svg class="w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" fill="currentColor"><title>Facebook</title><path d="M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647Z"></path></svg>
                        {{ __('show.social_facebook') }}
                    </a>
                @endif

                @if(isset($actor['external_ids']['twitter_id']) && $actor['external_ids']['twitter_id'])
                    <a href="{{ 'https://twitter.com/' . $actor['external_ids']['twitter_id'] }}" target="_blank" class="flex items-center justify-center gap-2 w-full py-2 bg-slate-700 rounded-lg font-bold hover:opacity-90 transition text-white">
                        <svg class="w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" fill="currentColor"><title>X</title><path d="M14.234 10.162 22.977 0h-2.072l-7.591 8.824L7.251 0H.258l9.168 13.343L.258 24H2.33l8.016-9.318L16.749 24h6.993zm-2.837 3.299-.929-1.329L3.076 1.56h3.182l5.965 8.532.929 1.329 7.754 11.09h-3.182z"></path></svg>
                        {{ __('show.social_twitter') }}
                    </a>
                @endif

                @if(isset($actor['external_ids']['tiktok_id']) && $actor['external_ids']['tiktok_id'])
                    <a href="{{ 'https://tiktok.com/@' . $actor['external_ids']['tiktok_id'] }}" target="_blank" class="flex items-center justify-center gap-2 w-full py-2 bg-black rounded-lg font-bold hover:opacity-90 transition text-white">
                        <svg class="w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" fill="currentColor"><title>TikTok</title><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"></path></svg>
                        {{ __('show.social_tiktok') }}
                    </a>
                @endif

                @if(isset($actor['external_ids']['youtube_id']) && $actor['external_ids']['youtube_id'])
                    <a href="{{ 'https://youtube.com/' . $actor['external_ids']['youtube_id'] }}" target="_blank" class="flex items-center justify-center gap-2 w-full py-2 bg-red-600 rounded-lg font-bold hover:opacity-90 transition text-white">
                        <svg class="w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" fill="currentColor"><title>YouTube</title><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"></path></svg>
                        {{ __('show.social_youtube') }}
                    </a>
                @endif

                @if(isset($actor['external_ids']['imdb_id']) && $actor['external_ids']['imdb_id'])
                    <a href="{{ 'https://imdb.com/name/' . $actor['external_ids']['imdb_id'] }}" target="_blank" class="flex items-center justify-center gap-2 w-full py-2 bg-yellow-700 rounded-lg font-bold hover:opacity-90 transition text-white">
                        <svg class="w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" fill="currentColor"><title>IMDb</title><path d="M22.3781 0H1.6218C.7411.0583.0587.7437.0018 1.5953l-.001 20.783c.0585.8761.7125 1.543 1.5559 1.6191A.337.337 0 0 0 1.6016 24h20.7971a.4579.4579 0 0 0 .0437-.002c.8727-.0768 1.5568-.8271 1.5568-1.7085V1.7098c0-.8914-.696-1.6416-1.584-1.7078A.3294.3294 0 0 0 22.3781 0zm0 .496a1.2144 1.2144 0 0 1 1.1252 1.2139v20.5797c0 .6377-.4875 1.1602-1.1045 1.2145H1.6016c-.5967-.0543-1.0645-.5297-1.1053-1.1258V1.6284C.5371 1.0185 1.0184.5364 1.6217.496h20.7564zM4.7954 8.2603v7.3636H2.8899V8.2603h1.9055zm6.5367 0v7.3636H9.6707v-4.9704l-.6711 4.9704H7.813l-.6986-4.8618-.0066 4.8618h-1.668V8.2603h2.468c.0748.4476.1492.9694.2307 1.5734l.2712 1.8713.4407-3.4447h2.4817zm2.9772 1.3289c.0742.0404.122.108.1417.2034.0279.0953.0345.3118.0345.6442v2.8548c0 .4881-.0345.7867-.0955.8954-.0609.1152-.2304.1695-.5018.1695V9.5211c.204 0 .3457.0205.4211.0681zm-.0211 6.0347c.4543 0 .8006-.0265 1.0245-.0742.2304-.0477.4204-.1357.5694-.2648.1556-.1218.2642-.298.3251-.5219.0611-.2238.1021-.6648.1021-1.3224v-2.5832c0-.6986-.0271-1.1668-.0742-1.4039-.041-.237-.1431-.4543-.3126-.6437-.1695-.1973-.4198-.3324-.7456-.421-.3191-.0808-.8542-.1285-1.7694-.1285h-1.4244v7.3636h2.3051zm5.14-1.7827c0 .3523-.0199.5762-.0544.6708-.033.0947-.1894.1424-.3046.1424-.1086 0-.19-.0477-.2238-.1351-.041-.0887-.0609-.2986-.0609-.6238v-1.9469c0-.3324.0199-.5423.0543-.6237.0338-.0808.1086-.122.2171-.122.1153 0 .2709.0412.3114.1425.041.0947.0609.2986.0609.6032v1.8926zm-2.4747-5.5809v7.3636h1.7157l.1152-.4675c.1556.1894.3251.3324.5152.4271.1828.0881.4608.1357.678.1357.3047 0 .5629-.0748.7802-.237.2165-.1562.3589-.3462.4198-.5628.0543-.2173.0887-.543.0887-.9841v-2.0675c0-.4409-.0139-.7324-.0344-.8681-.0199-.1357-.0742-.2781-.1695-.4204-.1021-.1425-.2437-.251-.4272-.3325-.1834-.0742-.3999-.1152-.6576-.1152-.2172 0-.4952.0477-.6846.1285-.1835.0887-.353.2238-.5086.4007V8.2603h-1.8309z"></path></svg>
                        {{ __('show.social_imdb') }}
                    </a>
                @endif

                @if(isset($actor['external_ids']['wikidata_id']) && $actor['external_ids']['wikidata_id'])
                    <a href="{{ 'https://wikidata.org/wiki/' . $actor['external_ids']['wikidata_id'] }}" target="_blank" class="flex items-center justify-center gap-2 w-full py-2 bg-cyan-600 rounded-lg font-bold hover:opacity-90 transition text-white">
                        <svg class="w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" role="img" viewBox="0 0 24 24" fill="currentColor"><title>Wikidata</title><path d="M0 4.583v14.833h.865V4.583zm1.788 0v14.833h2.653V4.583zm3.518 0v14.832H7.96V4.583zm3.547 0v14.834h.866V4.583zm1.789 0v14.833h.865V4.583zm1.759 0v14.834h2.653V4.583zm3.518 0v14.834h.923V4.583zm1.788 0v14.833h2.653V4.583zm3.64 0v14.834h.865V4.583zm1.788 0v14.834H24V4.583Z"></path></svg>
                        {{ __('show.social_wikidata') }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Biographie & Travaux -->
    <div class="w-full md:w-2/3">
        <h3 class="text-2xl font-bold mb-4 text-red-500">{{ __('show.actor_biography') }}</h3>
        <div class="text-slate-300 text-sm leading-relaxed max-h-64 overflow-y-auto pr-2 custom-scrollbar">
            @if($actor['biography'])
                {{ $actor['biography'] }}
            @else
                <p class="italic text-slate-500">{{ __('show.actor_no_biography') }}</p>
            @endif
        </div>

        @if(isset($actor['combined_credits']['cast']) && count($actor['combined_credits']['cast']) > 0)
            <h3 class="text-xl font-bold mt-8 mb-4">{{ __('show.actor_recent_projects') }}</h3>
            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                @php
                    $projects = collect($actor['combined_credits']['cast'])
                        ->sortByDesc('popularity')
                        ->take(8);
                @endphp
                @foreach($projects as $project)
                    <div class="flex flex-col gap-1">
                        <div class="aspect-[2/3] bg-slate-800 rounded overflow-hidden">
                            @if($project['poster_path'])
                                <img src="https://image.tmdb.org/t/p/w185{{ $project['poster_path'] }}"
                                     alt="{{ $project['name'] ?? $project['title'] }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-xs text-slate-600 text-center p-1">
                                    {{ $project['name'] ?? $project['title'] }}
                                </div>
                            @endif
                        </div>
                        <p class="text-[10px] text-slate-300 truncate font-medium">
                            {{ $project['name'] ?? $project['title'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-8 flex justify-end">
            <button onclick="closeActorModal(); filterByActor({{ $actor['id'] }}, '{{ addslashes($actor['latin_name'] ?? $actor['name']) }}')" class="btn-primary py-2 px-6 text-sm">
                {{ __('show.actor_view_all_dramas') }}
            </button>
        </div>
    </div>
</div>
