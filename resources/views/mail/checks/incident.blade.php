<x-mail::message>
# An incident was found

Check name: {{ $checkHistory->check->name }}

**Assertion Failed:**

{{ $checkHistory->root_cause['type'] }} {{ $checkHistory->root_cause['sign'] }} {{ $checkHistory->root_cause['value'] }}

<x-mail::button :url="$check_url">
    Open in Safeye
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
