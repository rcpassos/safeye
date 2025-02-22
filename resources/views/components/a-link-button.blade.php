@props(['href'])

<a href="{{ $href }}"
    class="text-white bg-black font-medium hover:bg-gray-950 rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 sm:mr-2 lg:mr-0 focus:outline-none flex flex-row items-center gap-2 w-fit">
    {{ $slot }}
</a>
