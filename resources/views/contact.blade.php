@extends('layouts.app')

@section('title', __('contact.page_title'))

@section('content')
<!-- Page Header -->
<div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 py-16 mb-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl sm:text-5xl font-bold text-white mb-4">
                {{ __('contact.title') }}
            </h1>
            <p class="text-lg sm:text-xl text-slate-300">
                {{ __('contact.subtitle') }}
            </p>
        </div>
    </div>
</div>

<!-- Contact Section -->
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-16">
        <div class="card-dark p-6 text-center">
            <div class="text-5xl mb-3">💬</div>
            <h3 class="text-lg font-bold text-white mb-2">{{ __('contact.info_reply_title') }}</h3>
            <p class="text-slate-400 text-sm">
                {{ __('contact.info_reply_body') }}
            </p>
        </div>

        <div class="card-dark p-6 text-center">
            <div class="text-5xl mb-3">⏱️</div>
            <h3 class="text-lg font-bold text-white mb-2">{{ __('contact.info_time_title') }}</h3>
            <p class="text-slate-400 text-sm">
                {{ __('contact.info_time_body') }}<br>
                <span class="text-xs">{{ __('contact.info_time_note') }}</span>
            </p>
        </div>
    </div>

    <!-- Contact Form -->
    <div class="card-dark p-6 sm:p-10 mb-16">
        <div class="mb-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-2">{{ __('contact.form_title') }}</h2>
            <p class="text-slate-400">
                {{ __('contact.form_subtitle') }}
            </p>
        </div>

        @if($errors->any())
            <div class="bg-red-900/20 border border-red-600/50 rounded-lg p-4 mb-6">
                <h4 class="font-bold text-red-400 mb-2">{{ __('contact.errors_title') }}</h4>
                <ul class="text-red-300 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-900/20 border border-green-600/50 rounded-lg p-4 mb-6 flex items-start gap-3">
                <span class="text-2xl">✅</span>
                <div>
                    <p class="text-green-400 font-semibold">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-900/20 border border-red-600/50 rounded-lg p-4 mb-6 flex items-start gap-3">
                <span class="text-2xl">❌</span>
                <div>
                    <p class="text-red-400 font-semibold">
                        {{ session('error') }}
                    </p>
                </div>
            </div>
        @endif

        <form action="{{ route('contact.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-200 mb-2">
                        {{ __('contact.field_name') }} <span class="text-red-400">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="{{ __('contact.placeholder_name') }}"
                        required
                        class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:border-red-500 focus:ring-1 focus:ring-red-500 transition @error('name') border-red-500 @enderror"
                    >
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-200 mb-2">
                        {{ __('contact.field_email') }} <span class="text-red-400">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="{{ __('contact.placeholder_email') }}"
                        required
                        class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:border-red-500 focus:ring-1 focus:ring-red-500 transition @error('email') border-red-500 @enderror"
                    >
                </div>
            </div>

            <!-- Subject -->
            <div>
                <label for="subject" class="block text-sm font-semibold text-slate-200 mb-2">
                    {{ __('contact.field_subject') }} <span class="text-red-400">*</span>
                </label>
                <input
                    type="text"
                    id="subject"
                    name="subject"
                    value="{{ old('subject') }}"
                    placeholder="{{ __('contact.placeholder_subject') }}"
                    required
                    class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:border-red-500 focus:ring-1 focus:ring-red-500 transition @error('subject') border-red-500 @enderror"
                >
            </div>

            <!-- Message -->
            <div>
                <label for="message" class="block text-sm font-semibold text-slate-200 mb-2">
                    {{ __('contact.field_message') }} <span class="text-red-400">*</span>
                </label>
                <textarea
                    id="message"
                    name="message"
                    rows="7"
                    placeholder="{{ __('contact.placeholder_message') }}"
                    required
                    class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:border-red-500 focus:ring-1 focus:ring-red-500 transition resize-none @error('message') border-red-500 @enderror"
                >{{ old('message') }}</textarea>
                <p class="text-slate-400 text-xs mt-2 text-right">
                    <span id="charCount">0</span> / 5000 {{ __('contact.char_limit_label') }}
                </p>
            </div>

            <!-- Attachment -->
            <div>
                <label for="attachment" class="block text-sm font-semibold text-slate-200 mb-2">
                    {{ __('contact.field_attachment') }} <span class="text-slate-400 text-xs">{{ __('contact.optional') }}</span>
                </label>
                <input
                    type="file"
                    id="attachment"
                    name="attachment"
                    accept=".pdf,.csv,.xlsx,.xls,.jpg,.jpeg,.png,.gif,.doc,.docx"
                    class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:border-red-500 focus:ring-1 focus:ring-red-500 transition file:bg-red-600 file:text-white file:border-0 file:rounded file:px-3 file:py-1 file:cursor-pointer @error('attachment') border-red-500 @enderror"
                >
                <p class="text-slate-400 text-xs mt-2">
                    {{ __('contact.attachment_hint') }}
                </p>
            </div>

            <!-- Submit Button -->
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button
                    type="submit"
                    class="flex-1 px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2"
                >
                    {{ __('contact.submit_button') }}
                </button>
                <a
                    href="{{ route('home') }}"
                    class="flex-1 px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-lg transition text-center"
                >
                    {{ __('contact.back_btn') }}
                </a>
            </div>
        </form>
    </div>

    <!-- FAQ Section -->
    <div class="mt-16">
        <h2 class="text-3xl sm:text-4xl font-bold text-white mb-10 text-center">
            {{ __('contact.faq_title') }}
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card-dark p-6">
                <h4 class="font-bold text-white text-base mb-3">{{ __('contact.faq_1_title') }}</h4>
                <p class="text-slate-400 text-sm leading-relaxed">
                    {{ __('contact.faq_1_body') }}
                </p>
            </div>

            <div class="card-dark p-6">
                <h4 class="font-bold text-white text-base mb-3">{{ __('contact.faq_2_title') }}</h4>
                <p class="text-slate-400 text-sm leading-relaxed">
                    {{ __('contact.faq_2_body') }}
                </p>
            </div>

            <div class="card-dark p-6">
                <h4 class="font-bold text-white text-base mb-3">{{ __('contact.faq_3_title') }}</h4>
                <p class="text-slate-400 text-sm leading-relaxed">
                    {{ __('contact.faq_3_body') }}
                </p>
            </div>

            <div class="card-dark p-6">
                <h4 class="font-bold text-white text-base mb-3">{{ __('contact.faq_4_title') }}</h4>
                <p class="text-slate-400 text-sm leading-relaxed">
                    {{ __('contact.faq_4_body') }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Character Counter Script -->
<script>
    const messageInput = document.getElementById('message');
    const charCount = document.getElementById('charCount');

    messageInput.addEventListener('input', () => {
        charCount.textContent = messageInput.value.length;
    });

    // Set initial count if form has been submitted with validation error
    if (messageInput.value.length > 0) {
        charCount.textContent = messageInput.value.length;
    }
</script>
@endsection
