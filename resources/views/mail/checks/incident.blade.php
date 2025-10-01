<x-mail::message>
    # {{ __('mail.incident_found') }}

    {{ __('mail.check_name') }}: {{ $checkHistory->check->name }}

    **{{ __('mail.assertion_failed') }}:**

    {{ $checkHistory->root_cause['type'] }} {{ $checkHistory->root_cause['sign'] }} {{
    $checkHistory->root_cause['value'] }}

    <x-mail::button :url="$check_url">
        {{ __('mail.open_in_safeye') }}
    </x-mail::button>

    {{ __('mail.thanks') }},<br>
    {{ config('app.name') }}
</x-mail::message>