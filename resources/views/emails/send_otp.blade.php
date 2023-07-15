<x-mail::message>
# Dear User

Here is your otp code.

<x-mail::panel>
@foreach (str_split($otp) as $number)
{{ $number . ' ' }}
@endforeach
</x-mail::panel>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
