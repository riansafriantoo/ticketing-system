@props(['url'])
<tr>
    <td class="header">
        {{-- <a href="{{ $url }}" style="display: inline-block;"> --}}
            @if (trim($slot) === 'Ticketing-System')
                @php
                    $logoPath = public_path('images/logo.png');
                    $logoData = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
                @endphp
                @if($logoData)
                    <img src="{{ $logoData }}" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;" alt="Ticketing System Logo">
                @else
                    Ticketing-System
                @endif
            @else
                {!! $slot !!}
            @endif
        </a>
    </td>
</tr>