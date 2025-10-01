<section class="bg-white dark:bg-gray-900">
    <div class="grid max-w-screen-xl px-4 pt-20 pb-8 mx-auto lg:gap-8 xl:gap-0 lg:py-16 lg:grid-cols-12 lg:pt-28">
        <div class="mr-auto place-self-center lg:col-span-7">
            <h1
                class="max-w-2xl mb-4 text-4xl font-extrabold leading-none tracking-tight md:text-5xl xl:text-6xl dark:text-white">
                {!! __('frontend.title', ['app_name' => config('app.name')]) !!}</h1>
            <p class="max-w-2xl mb-6 font-light text-gray-500 lg:mb-8 md:text-lg lg:text-xl dark:text-gray-400">{{
                __('frontend.subtitle') }}</p>
            {{-- <x-a-link-button href="#pricing">
                Get Start <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-arrow-right size-4">
                    <path d="M5 12h14"></path>
                    <path d="m12 5 7 7-7 7"></path>
                </svg>
            </x-a-link-button> --}}
        </div>
        <div class="hidden lg:mt-0 lg:col-span-5 lg:flex">
            <img src="{{ asset('images/hero.png') }}" alt="hero image">
        </div>
    </div>
</section>