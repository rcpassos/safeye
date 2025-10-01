<footer class="bg-white dark:bg-gray-800">
    <div class="max-w-screen-xl p-4 py-6 mx-auto lg:py-16 md:p-8 lg:p-10">
        <div class="grid grid-cols-2 gap-8">
            <div>
                <h3 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">{{ __('common.links') }}
                </h3>
                <ul class="text-gray-500 dark:text-gray-400">
                    <li class="mb-4">
                        <a href="#" class=" hover:underline">{{ __('common.login') }}</a>
                    </li>
                    <li class="mb-4">
                        <a href="#" class="hover:underline">{{ __('common.pricing') }}</a>
                    </li>
                    <li class="mb-4">
                        <a href="mailto:{{ config('app.support_email') }}" class="hover:underline">{{
                            __('common.contact_us') }}</a>
                    </li>
                </ul>
            </div>
            <div>
                <h3 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">{{ __('common.legal') }}
                </h3>
                <ul class="text-gray-500 dark:text-gray-400">
                    <li class="mb-4">
                        <a href="{{ route('app.privacy') }}" class="hover:underline">{{ __('common.privacy_policy')
                            }}</a>
                    </li>
                    <li class="mb-4">
                        <a href="{{ route('app.terms') }}" class="hover:underline">{{ __('common.terms_of_service')
                            }}</a>
                    </li>
                </ul>
            </div>
        </div>
        <hr class="my-6 border-gray-200 sm:mx-auto dark:border-gray-700 lg:my-8">
        <div class="text-center">
            <a href="#"
                class="flex items-center justify-center mb-5 text-2xl font-semibold text-gray-900 dark:text-white">
                <img src="{{ asset('images/logo.svg') }}" class="h-6 mr-3 sm:h-9" alt="{{ config('app.name') }} Logo" />
                {{ config('app.name') }}
            </a>
            <p class="text-md text-gray-500 sm:text-center dark:text-gray-400">
                {{ __('common.tagline') }}
            </p>
            <br />
            <span class="block text-sm text-center text-gray-500 dark:text-gray-400">© {{ now()->year }} {{
                config('app.name') }}. {{ __('common.all_rights_reserved') }}
            </span>
        </div>
    </div>
</footer>