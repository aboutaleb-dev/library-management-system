<x-mail::message>
# Dear User

Your borrow time for the book below will end in {{ $daysRemaining }} days.

<x-mail::panel>
{{ $bookName }}
</x-mail::panel>

Please return the book to library before that time.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
