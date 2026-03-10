<x-app-layout>
    <div class="flex min-h-screen bg-slate-900">
        <x-admin-sidebar />
        <div class="flex-1">
            <div class="w-full bg-gradient-to-r from-slate-800 to-slate-900 border-b border-slate-700 sticky top-0 z-10 overflow-hidden">
                <div class="px-3 sm:px-6 lg:px-8 py-4">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-2 sm:gap-3">
                        <span class="text-3xl sm:text-4xl">📝</span>
                        <span>{{ __('admin.author_title') }}</span>
                    </h1>
                    <p class="text-slate-400 mt-1">{{ __('admin.author_subtitle') }}</p>
                </div>
            </div>
            <div class="w-full py-6 px-3 sm:py-8 sm:px-6 lg:px-8">
        <div class="w-full max-w-4xl mx-auto">
            @if (session('success'))
                <div class="mb-4 bg-green-900/30 border border-green-600 text-green-200 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.author.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- 📝 AUTHOR PROFILE -->
                        <div class="mb-8">
                            <h3 class="font-semibold text-lg text-white mb-4">{{ __('admin.author_profile') }}</h3>

                            <div class="mb-4">
                                <label class="block text-slate-200 font-semibold mb-2">{{ __('admin.author_name') }}</label>
                                <input type="text" name="author_name" value="{{ old('author_name', $metadata->author_name) }}" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:border-blue-500">
                                @error('author_name') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-slate-200 font-semibold mb-2">{{ __('admin.author_bio') }}</label>
                                <textarea name="author_bio" rows="4" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:border-blue-500">{{ old('author_bio', $metadata->author_bio) }}</textarea>
                                @error('author_bio') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-slate-200 font-semibold mb-2">{{ __('admin.author_email') }}</label>
                                <input type="email" name="author_email" value="{{ old('author_email', $metadata->author_email) }}" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:border-blue-500">
                                @error('author_email') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-slate-200 font-semibold mb-2">{{ __('admin.author_avatar') }}</label>
                                @if($metadata->author_avatar)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $metadata->author_avatar) }}" alt="Avatar" class="w-32 h-32 rounded object-cover">
                                    </div>
                                @endif
                                <input type="file" name="author_avatar" accept="image/*" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded text-white">
                                @error('author_avatar') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <hr class="border-slate-700 my-8">

                        <!-- 🌐 SITE INFORMATION -->
                        <div class="mb-8">
                            <h3 class="font-semibold text-lg text-white mb-4">{{ __('admin.author_site_info') }}</h3>

                            <div class="mb-4">
                                <label class="block text-slate-200 font-semibold mb-2">{{ __('admin.author_site_name') }}</label>
                                <input type="text" name="site_name" value="{{ old('site_name', $metadata->site_name) }}" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:border-blue-500">
                                @error('site_name') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-slate-200 font-semibold mb-2">{{ __('admin.author_site_tagline') }}</label>
                                <input type="text" name="site_tagline" value="{{ old('site_tagline', $metadata->site_tagline) }}" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:border-blue-500">
                                @error('site_tagline') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-slate-200 font-semibold mb-2">{{ __('admin.author_site_footer') }}</label>
                                <textarea name="site_footer_text" rows="3" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:border-blue-500">{{ old('site_footer_text', $metadata->site_footer_text) }}</textarea>
                                @error('site_footer_text') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-slate-200 font-semibold mb-2">{{ __('admin.author_site_copyright') }}</label>
                                <input type="text" name="site_copyright" value="{{ old('site_copyright', $metadata->site_copyright) }}" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:border-blue-500">
                                @error('site_copyright') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <hr class="border-slate-700 my-8">

                        <!-- 🔗 SOCIAL LINKS -->
                        <div class="mb-8">
                            <h3 class="font-semibold text-lg text-white mb-4">{{ __('admin.author_social_links') }}</h3>

                            <!-- Icon Picker Help -->
                            <div class="mb-4 p-3 bg-blue-900/30 border border-blue-600 rounded text-sm">
                                <p class="text-blue-200 font-semibold mb-2">{{ __('admin.author_icon_hint') }}</p>
                                <p class="text-blue-300 text-xs">4500+ Tabler Icons available • Live search • Click to select</p>
                            </div>

                            <div id="social-links-container" class="space-y-4">
                                @forelse($socialLinks as $index => $link)
                                    <div class="social-link-item draggable-item mb-4 p-5 bg-slate-700 rounded border border-slate-600 hover:border-slate-500 transition cursor-grab active:cursor-grabbing" data-id="{{ $link->id }}">
                                        <!-- Header avec Drag Handle, Platform et Delete -->
                                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-600">
                                            <div class="flex items-center gap-3 flex-1">
                                                <span class="drag-handle text-slate-400 cursor-grab active:cursor-grabbing text-lg" title="Drag to reorder">⋮⋮</span>
                                                <div class="flex-1">
                                                    <label class="block text-slate-200 font-semibold mb-2 text-sm">🔗 Platform Name</label>
                                                    <input type="text" name="social_links[{{ $index }}][platform]" value="{{ $link->platform }}" placeholder="Twitter, GitHub, Email..." class="w-full px-3 py-2 bg-slate-600 border border-slate-500 rounded text-white text-sm focus:border-blue-500">
                                                </div>
                                            </div>
                                            <button type="button" onclick="this.closest('.social-link-item').remove()" class="ml-3 text-slate-400 hover:text-red-400 text-2xl transition">🗑️</button>
                                        </div>

                                        <!-- Grid Responsive pour les autres champs -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <!-- URL -->
                                            <div>
                                                <label class="block text-slate-200 font-semibold mb-2 text-sm">🌐 URL</label>
                                                <input type="text" name="social_links[{{ $index }}][url]" value="{{ $link->url }}" placeholder="moussouni.dev ou https://..." class="w-full px-3 py-2 bg-slate-600 border border-slate-500 rounded text-white text-sm focus:border-blue-500" onblur="autoAddProtocol(this)">
                                                <p class="text-xs text-slate-400 mt-1">https:// will be added automatically if missing</p>
                                            </div>

                                            <!-- Icon + Picker -->
                                            <div>
                                                <label class="block text-slate-200 font-semibold mb-2 text-sm">🎨 Icon Name</label>
                                                <div class="flex gap-2">
                                                    <input type="text" name="social_links[{{ $index }}][icon]" value="{{ $link->icon }}" placeholder="brand-twitter..." class="flex-1 px-3 py-2 bg-slate-600 border border-slate-500 rounded text-white text-sm focus:border-blue-500">
                                                    <button type="button" onclick="openIconPicker(this.previousElementSibling)" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold text-sm transition">🎨</button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Visible Checkbox - Full Width -->
                                        <div class="mt-4 pt-4 border-t border-slate-600">
                                            <label class="flex items-center gap-3 cursor-pointer">
                                                <input type="checkbox" name="social_links[{{ $index }}][is_visible]" value="1" {{ $link->is_visible ? 'checked' : '' }} class="w-4 h-4 rounded bg-slate-600 border border-slate-500 text-blue-600 focus:ring-blue-500">
                                                <span class="text-slate-200 font-semibold text-sm">👁️ Visible in footer</span>
                                            </label>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8">
                                        <p class="text-slate-400 text-sm">No social links yet</p>
                                        <p class="text-slate-500 text-xs mt-1">Click "+ Add Social Link" to create one</p>
                                    </div>
                                @endforelse
                            </div>
                            <button type="button" onclick="addSocialLink()" class="mt-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm">+ Add Social Link</button>
                        </div>

                        <hr class="border-slate-700 my-8">

                        <!-- 🔍 SEO SETTINGS -->
                        <div class="mb-8">
                            <h3 class="font-semibold text-lg text-white mb-4">🔍 SEO Settings</h3>

                            <div class="mb-4">
                                <label class="block text-slate-200 font-semibold mb-2">Meta Description <span class="text-xs text-slate-400">(160 chars max)</span></label>
                                <textarea name="meta_description" rows="2" maxlength="160" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:border-blue-500 text-sm" placeholder="Brief description for Google search results">{{ old('meta_description', $metadata->meta_description) }}</textarea>
                                <div class="text-xs text-slate-400 mt-1"><span id="desc-count">{{ strlen($metadata->meta_description ?? '') }}</span>/160</div>
                                @error('meta_description') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-slate-200 font-semibold mb-2">Meta Keywords</label>
                                <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $metadata->meta_keywords) }}" placeholder="kdrama, korean drama, series" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:border-blue-500 text-sm">
                                @error('meta_keywords') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-slate-200 font-semibold mb-2">OG Title (Social Media)</label>
                                <input type="text" name="og_title" value="{{ old('og_title', $metadata->og_title) }}" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:border-blue-500">
                                @error('og_title') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-slate-200 font-semibold mb-2">OG Description</label>
                                <textarea name="og_description" rows="3" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:border-blue-500">{{ old('og_description', $metadata->og_description) }}</textarea>
                                @error('og_description') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-slate-200 font-semibold mb-2">OG Image</label>
                                @if($metadata->og_image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $metadata->og_image) }}" alt="OG Image" class="w-48 h-auto rounded">
                                    </div>
                                @endif
                                <input type="file" name="og_image" accept="image/*" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded text-white">
                                <p class="text-xs text-slate-400 mt-1">Recommended: 1200x630px</p>
                                @error('og_image') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-slate-200 font-semibold mb-2">OG Type</label>
                                <select name="og_type" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:border-blue-500">
                                    <option value="website" {{ old('og_type', $metadata->og_type) === 'website' ? 'selected' : '' }}>Website</option>
                                    <option value="blog" {{ old('og_type', $metadata->og_type) === 'blog' ? 'selected' : '' }}>Blog</option>
                                    <option value="article" {{ old('og_type', $metadata->og_type) === 'article' ? 'selected' : '' }}>Article</option>
                                </select>
                                @error('og_type') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold">{{ __('admin.settings_save') }}</button>
                            <a href="{{ route('admin.dashboard') }}" class="px-6 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded font-semibold">{{ __('common.cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables and functions for inline event handlers
        let socialLinkIndex = {{ count($socialLinks) }};

        // Auto-add https:// to URLs if missing
        function autoAddProtocol(input) {
            let url = input.value.trim();

            // Skip if empty
            if (!url) return;

            // Skip if already has protocol or is mailto
            if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('mailto:')) {
                return;
            }

            // Add https://
            input.value = 'https://' + url;
        }

        function addSocialLink() {
            const container = document.getElementById('social-links-container');
            const html = `
                <div class="social-link-item draggable-item mb-4 p-5 bg-slate-700 rounded border border-slate-600 hover:border-slate-500 transition cursor-grab active:cursor-grabbing">
                    <!-- Header avec Drag Handle, Platform et Delete -->
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-600">
                        <div class="flex items-center gap-3 flex-1">
                            <span class="drag-handle text-slate-400 cursor-grab active:cursor-grabbing text-lg" title="Drag to reorder">⋮⋮</span>
                            <div class="flex-1">
                                <label class="block text-slate-200 font-semibold mb-2 text-sm">🔗 Platform Name</label>
                                <input type="text" name="social_links[${socialLinkIndex}][platform]" class="w-full px-3 py-2 bg-slate-600 border border-slate-500 rounded text-white text-sm focus:border-blue-500" placeholder="Twitter, GitHub, Email...">
                            </div>
                        </div>
                        <button type="button" onclick="this.closest('.social-link-item').remove()" class="ml-3 text-slate-400 hover:text-red-400 text-2xl transition">🗑️</button>
                    </div>

                    <!-- Grid Responsive pour les autres champs -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- URL -->
                        <div>
                            <label class="block text-slate-200 font-semibold mb-2 text-sm">🌐 URL</label>
                            <input type="text" name="social_links[${socialLinkIndex}][url]" class="w-full px-3 py-2 bg-slate-600 border border-slate-500 rounded text-white text-sm focus:border-blue-500" placeholder="moussouni.dev ou https://..." onblur="autoAddProtocol(this)">
                            <p class="text-xs text-slate-400 mt-1">https:// will be added automatically if missing</p>
                        </div>

                        <!-- Icon + Picker -->
                        <div>
                            <label class="block text-slate-200 font-semibold mb-2 text-sm">🎨 Icon Name</label>
                            <div class="flex gap-2">
                                <input type="text" name="social_links[${socialLinkIndex}][icon]" class="flex-1 px-3 py-2 bg-slate-600 border border-slate-500 rounded text-white text-sm focus:border-blue-500" placeholder="brand-twitter...">
                                <button type="button" onclick="openIconPicker(this.previousElementSibling)" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold text-sm transition">🎨</button>
                            </div>
                        </div>
                    </div>

                    <!-- Visible Checkbox - Full Width -->
                    <div class="mt-4 pt-4 border-t border-slate-600">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="social_links[${socialLinkIndex}][is_visible]" value="1" checked class="w-4 h-4 rounded bg-slate-600 border border-slate-500 text-blue-600 focus:ring-blue-500">
                            <span class="text-slate-200 font-semibold text-sm">👁️ Visible in footer</span>
                        </label>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            socialLinkIndex++;

            // Reinitialize Sortable to make new item draggable
            window.initSortable?.();
        }

        // Character counter for meta description
        document.addEventListener('DOMContentLoaded', function() {
            const metaDesc = document.querySelector('textarea[name="meta_description"]');
            if (metaDesc) {
                metaDesc.addEventListener('input', function() {
                    document.getElementById('desc-count').textContent = this.value.length;
                });
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        // Make Sortable available after the script loads

        let sortableInstance = null;

        // Save order to database via AJAX
        async function saveSocialLinksOrder() {
            const container = document.getElementById('social-links-container');
            const items = container.querySelectorAll('.social-link-item[data-id]');

            const ids = Array.from(items).map(item => item.dataset.id);

            console.log('Saving social links order:', ids);

            try {
                const response = await fetch('{{ route("admin.author.social-links.reorder") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ ids }),
                });

                const data = await response.json();
                console.log('Order saved:', data);
            } catch (error) {
                console.error('Error saving order:', error);
            }
        }

        // Initialize Sortable for drag and drop
        window.initSortable = function() {
            const container = document.getElementById('social-links-container');
            console.log('initSortable called, container:', container);
            if (container && typeof Sortable !== 'undefined') {
                // Destroy existing instance if it exists
                if (sortableInstance) {
                    sortableInstance.destroy();
                }

                // Create new Sortable instance
                sortableInstance = Sortable.create(container, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    handle: '.drag-handle',
                    onEnd: function(evt) {
                        console.log('Drag ended:', evt);
                        saveSocialLinksOrder();
                    },
                });
                console.log('Sortable instance created:', sortableInstance);
            } else {
                console.warn('Sortable not loaded yet or container not found');
            }
        };

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', window.initSortable);
    </script>

            </div>
        </div>
    </div>

    <style>
        .sortable-ghost {
            opacity: 0.4;
            background-color: rgb(51, 65, 85);
        }
    </style>

    <!-- Icon Picker Modal -->
    <x-icon-picker-modal />
</x-app-layout>
