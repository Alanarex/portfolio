{{-- Animation au survol d'une bande du carnet de vol : l'acteur traverse toute la bande, d'un bord à l'autre. --}}
@switch($interest)
    @case('aviation')
        <span class="strip__track strip__track--runway"></span>
        <span class="strip__actor strip__actor--plane">
            <svg viewBox="-34 -20 68 40" aria-hidden="true" focusable="false">
                <path d="M-22 0 C-22 -5 -12 -8 0 -8 L16 -8 C23 -8 26 -3 22 0 C22 3 16 6 0 6 L-16 6 C-20 6 -22 3 -22 0Z" fill="#f5f3f4"/>
                <path d="M-22 -1 L-31 -14 L-24 -14 L-14 -4Z" fill="#e5383b"/>
                <path d="M-4 3 L-14 17 L-6 17 L10 3Z" fill="#d9d4d4"/>
                <path d="M-3 -6 L-10 -14 L-4 -14 L8 -6Z" fill="#b1a7a6"/>
                <rect x="9" y="-5.5" width="10" height="3.4" rx="1.6" fill="#161a1d"/>
                <circle cx="-2" cy="-3" r="1.2" fill="#161a1d"/><circle cx="-8" cy="-3" r="1.2" fill="#161a1d"/>
            </svg>
        </span>
        @break
    @case('cars')
        <span class="strip__track strip__track--road"></span>
        <span class="strip__actor strip__actor--wheel">
            <svg viewBox="80 5 100 100" aria-hidden="true" focusable="false">
                <g class="strip__spin">
                    <circle cx="130" cy="55" r="46" fill="#0b090a" stroke="#2a2527" stroke-width="2"/>
                    <circle cx="130" cy="55" r="40" fill="none" stroke="#1a1718" stroke-width="5" stroke-dasharray="3 3"/>
                    <circle cx="130" cy="55" r="33" fill="#8f8889"/>
                    <circle cx="130" cy="55" r="30" fill="#2a2527"/>
                    <g stroke="#d9d4d4" stroke-linecap="round" stroke-width="8">
                        <line x1="130" y1="55" x2="130" y2="27"/><line x1="130" y1="55" x2="156.6" y2="46.4"/><line x1="130" y1="55" x2="146.5" y2="77.6"/><line x1="130" y1="55" x2="113.5" y2="77.6"/><line x1="130" y1="55" x2="103.4" y2="46.4"/>
                    </g>
                    <circle cx="130" cy="55" r="17" fill="none" stroke="#b1a7a6" stroke-width="3"/>
                    <path d="M130 38 A17 17 0 0 1 144.7 46.5 L138 50.5 A9 9 0 0 0 130 46Z" fill="#e5383b"/>
                    <circle cx="130" cy="55" r="7" fill="#e5383b"/><circle cx="130" cy="55" r="2.4" fill="#0b090a"/>
                </g>
            </svg>
        </span>
        @break
    @case('fishing')
        <span class="strip__track strip__track--water"></span>
        <span class="strip__splash"></span>
        <span class="strip__actor strip__actor--fish">
            <svg viewBox="-28 -16 56 32" aria-hidden="true" focusable="false">
                <path d="M-16 0 C-8 -9 8 -9 17 0 C8 9 -8 9 -16 0Z" fill="#f5f3f4"/>
                <path d="M-14 0 L-25 -9 L-22 0 L-25 9Z" fill="#e5383b"/>
                <path d="M-2 -7 L4 -13 L7 -6Z" fill="#e5383b"/>
                <path d="M-8 -5 C-6 -2 -6 2 -8 5" fill="none" stroke="#e5383b" stroke-width="1.6"/><path d="M-2 -6 C0 -2 0 2 -2 6" fill="none" stroke="#e5383b" stroke-width="1.6"/>
                <circle cx="10" cy="-1.6" r="1.7" fill="#0b090a"/>
            </svg>
        </span>
        @break
@endswitch
