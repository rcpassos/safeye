<header class="fixed w-full">
    <nav class="bg-white border-gray-200 py-2.5 dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between max-w-screen-xl px-4 mx-auto">
            <a href="/" class="flex items-center">
                <img src="{{ asset('images/logo.svg') }}" class="h-6 mr-3 sm:h-9" alt="Safeye Logo" />
                <span class="self-center text-xl font-semibold whitespace-nowrap dark:text-white">Safeye</span>
            </a>
            <div class="flex items-center lg:order-2">
                <x-a-link-button href="{{ route('filament.app.auth.login') }}">
                    Sign
                    in <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-arrow-right size-4">
                        <path d="M5 12h14"></path>
                        <path d="m12 5 7 7-7 7"></path>
                    </svg>
                </x-a-link-button>
            </div>
    </nav>
</header>
