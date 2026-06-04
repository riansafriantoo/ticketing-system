@php
    use App\Models\Ticket;

    $overdueTickets = Ticket::overdue()->get();
@endphp

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'it-ticketing') — it-ticketing</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    {{-- Tailwind via CDN for MVP —- replace with compiled in production --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              sans: ['DM Sans', 'system-ui', 'sans-serif'],
              mono: ['JetBrains Mono', 'monospace'],
            },
            colors: {
              brand: {
                50:  '#f0f4ff',
                100: '#e0eaff',
                200: '#c2d4ff',
                500: '#4f6ef7',
                600: '#3b55e8',
                700: '#2a3fcc',
                800: '#1e2e9e',
                900: '#141f6e',
              },
            },
          },
        },
      }
    </script>

    <style>
      :root {
        --sidebar-w: 240px;
      }
      body { font-family: 'DM Sans', system-ui, sans-serif; }

      /* Custom scrollbar */
      ::-webkit-scrollbar { width: 5px; height: 5px; }
      ::-webkit-scrollbar-track { background: transparent; }
      ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 9999px; }

      /* Status badges */
      .badge-open        { @apply bg-blue-100 text-blue-800; }
      .badge-in_progress { @apply bg-amber-100 text-amber-800; }
      .badge-on_hold     { @apply bg-gray-100 text-gray-700; }
      .badge-resolved    { @apply bg-green-100 text-green-800; }
      .badge-closed      { @apply bg-slate-100 text-slate-600; }

      /* Priority badges */
      .badge-low      { @apply bg-green-100 text-green-800; }
      .badge-medium   { @apply bg-blue-100 text-blue-800; }
      .badge-high     { @apply bg-amber-100 text-amber-800; }
      .badge-critical { @apply bg-red-100 text-red-800; }

      /* Sidebar nav active */
      .nav-link.active {
        background: rgba(79, 110, 247, 0.12);
        color: #4f6ef7;
      }
      .nav-link.active svg { color: #4f6ef7; }

      /* Ticket row hover */
      .ticket-row:hover { background: #f8faff; }

      /* Smooth transitions */
      * { transition-property: background-color, border-color, opacity; transition-duration: 100ms; }
    </style>

    @stack('head')
</head>
<body class="h-full bg-gray-50 text-gray-900">

<div class="flex h-screen overflow-hidden">

    {{-- ── Sidebar ─────────────────────────────────────────────────────── --}}
    <aside class="flex flex-col w-60 bg-white border-r border-gray-100 flex-shrink-0 overflow-y-auto">

        {{-- Logo --}}
        <div class="flex items-center gap-2.5 px-5 h-16 border-b border-gray-100">
            <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-4.5 h-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
            </div>
            <span class="font-semibold text-gray-900 tracking-tight">it-ticketing</span>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5">
            @php $path = request()->path(); @endphp

            <a href="{{ route('tickets.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-700 font-medium hover:bg-gray-50 {{ str_starts_with($path, 'tickets') && !str_contains($path, 'create') ? 'active' : '' }}">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                All Tickets
            </a>

            <a href="{{ route('tickets.create') }}"
               class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-700 font-medium hover:bg-gray-50 {{ str_contains($path, 'create') ? 'active' : '' }}">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Ticket
            </a>

            @if(auth()->user()->isAgent())
            <div class="pt-4 pb-1 px-3">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Agent</p>
            </div>

            <a href="{{ route('tickets.index', ['assignee' => auth()->id()]) }}"
               class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-700 font-medium hover:bg-gray-50">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                My Queue
            </a>

            <a href="{{ route('tickets.index', ['overdue' => 1]) }}"
               class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-700 font-medium hover:bg-gray-50">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Overdue
            </a>
            @endif

            @if(auth()->user()->isAdmin())
            <div class="pt-4 pb-1 px-3">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Admin</p>
            </div>
            <a href="{{ route('admin.dashboard') }}"
               class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-700 font-medium hover:bg-gray-50 {{ str_starts_with($path, 'admin') ? 'active' : '' }}">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Dashboard
            </a>
            <a href="{{ route('admin.users.index') }}"
               class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-700 font-medium hover:bg-gray-50">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Users
            </a>
            @endif
        </nav>

        {{-- User info --}}
        <div class="border-t border-gray-100 p-3">
            <div class="flex items-center gap-2.5 px-2 py-2 rounded-lg hover:bg-gray-50 cursor-pointer group">
                <img src="{{ auth()->user()->avatarUrl() }}" class="w-7 h-7 rounded-full flex-shrink-0" alt="">
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-gray-400 truncate capitalize">{{ auth()->user()->roles->first()?->name ?? 'user' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Sign out" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-gray-600">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ── Main content ─────────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Top bar --}}
        <header class="h-16 flex items-center justify-between px-6 border-b border-gray-100 bg-white flex-shrink-0">
            <div>
                <h1 class="text-base font-semibold text-gray-900">@yield('heading', 'Tickets')</h1>
                <p class="text-xs text-gray-400">@yield('subheading', '')</p>
            </div>
            <div class="flex items-center gap-3">
                @yield('header-actions')
                <a href="{{ route('tickets.create') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    New Ticket
                </a>
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="mx-6 mt-4 flex items-center gap-2 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mx-6 mt-4 flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>
            {{ session('error') }}
        </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
@if($overdueTickets->count() > 0)

<audio id="slaAlertSound">
    <source src="{{ asset('sounds/notif_1.mp3') }}" type="audio/mpeg">
</audio>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

let activeSlaPopup = false;

async function checkSlaBreached() {

    try {

        const response = await fetch("{{ url('/sla/check') }}");

        const data = await response.json();

        if (data.count > 0 && !activeSlaPopup) {

            activeSlaPopup = true;

            // play sound
            document.getElementById('slaAlertSound').play();

            let html = '<div style="text-align:left;">';

            data.tickets.forEach(function(ticket) {

                html += `
                    <div style="
                        padding:10px;
                        margin-bottom:10px;
                        border:1px solid #e5e7eb;
                        border-radius:8px;
                    ">
                        <b>${ticket.number}</b><br>
                        ${ticket.subject}<br>
                        Priority: ${ticket.priority}
                    </div>
                `;
            });

            html += '</div>';

            Swal.fire({
                icon: 'warning',
                title: 'SLA Terlewati',
                html: html,
                width: 600,
                confirmButtonText: 'Lihat Ticket',
                allowOutsideClick: false
            }).then(function(result) {

                activeSlaPopup = false;

                if (result.isConfirmed) {

                    window.location.href = "{{ url('/tickets') }}";
                }
            });
        }

    } catch(error) {

        console.error(error);
    }
}

// check tiap 15 detik
setInterval(checkSlaBreached, 15000);

// first load
checkSlaBreached();

</script>

@endif
</body>
</html>