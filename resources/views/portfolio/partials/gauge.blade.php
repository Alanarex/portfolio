@php
    $idle = $idle ?? false;
    // Échelle 0–320 km/h répartie sur 270° (de -135° à +135°, 0° = haut du cadran).
    $point = function (float $radius, float $value): array {
        $angle = deg2rad(-135 + 270 * $value / 320);

        return [round(200 + $radius * sin($angle), 2), round(200 - $radius * cos($angle), 2)];
    };
    $arc = function (float $radius, float $from, float $to) use ($point): string {
        [$x0, $y0] = $point($radius, $from);
        [$x1, $y1] = $point($radius, $to);
        $large = (($to - $from) * 270 / 320) > 180 ? 1 : 0;

        return "M{$x0} {$y0} A{$radius} {$radius} 0 {$large} 1 {$x1} {$y1}";
    };
    $minis = [
        ['cx' => 134, 'cy' => 340, 'a' => '-18deg', 'low' => 'E', 'high' => 'F', 'tone' => '#e5383b', 'delay' => '0s'],
        ['cx' => 266, 'cy' => 340, 'a' => '12deg', 'low' => 'C', 'high' => 'H', 'tone' => '#e5383b', 'delay' => '-3s'],
    ];
@endphp
<div class="gauge {{ $class ?? '' }}">
    <svg viewBox="0 0 400 400" @if(! empty($label)) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif>
        {{-- Lunette : métal brossé, moletage et filet intérieur --}}
        <circle cx="200" cy="200" r="199" fill="url(#gauge-bezel)"/>
        <circle cx="200" cy="200" r="195" fill="none" stroke="#000" stroke-opacity=".45" stroke-width="6" stroke-dasharray="1.1 3.4"/>
        <circle cx="200" cy="200" r="191.5" fill="#0b090a"/>
        <circle cx="200" cy="200" r="190" fill="none" stroke="url(#gauge-bezel)" stroke-width="1.5"/>
        {{-- Fond du cadran : dégradé profond et trame carbone --}}
        <circle cx="200" cy="200" r="187" fill="url(#gauge-dial)"/>
        <circle cx="200" cy="200" r="187" fill="url(#gauge-carbon)" opacity=".55"/>
        <circle class="gauge__redglow" cx="200" cy="200" r="187" fill="url(#gauge-redglow)"/>

        {{-- Zone rouge de l’échelle des vitesses --}}
        <path d="{{ $arc(184, 280, 320) }}" fill="none" stroke="#e5383b" stroke-width="5" stroke-linecap="round"/>
        <path d="{{ $arc(184, 280, 320) }}" fill="none" stroke="#e5383b" stroke-width="12" opacity=".22"/>

        {{-- Graduations : tous les 10 km/h, repères longs tous les 20 et 40 --}}
        <g stroke-linecap="round">
            @foreach (range(0, 320, 10) as $value)
                @php
                    $major = $value % 40 === 0;
                    $mid = ! $major && $value % 20 === 0;
                    [$ox, $oy] = $point(178, $value);
                    [$ix, $iy] = $point($major ? 160 : ($mid ? 166 : 171), $value);
                @endphp
                <line x1="{{ $ox }}" y1="{{ $oy }}" x2="{{ $ix }}" y2="{{ $iy }}" stroke="{{ $value >= 280 ? '#ff5457' : '#f5f3f4' }}" stroke-width="{{ $major ? 3.2 : ($mid ? 2 : 1.2) }}" @if (! $major && ! $mid) stroke-opacity=".6" @endif/>
            @endforeach
        </g>
        <g font-family="Space Grotesk Variable, sans-serif" font-size="15" font-weight="600" fill="#f5f3f4" text-anchor="middle" dominant-baseline="central">
            @foreach (range(0, 320, 40) as $value)
                @php([$tx, $ty] = $point(143, $value))
                <text x="{{ $tx }}" y="{{ $ty }}" @if ($value === 320) fill="#ff5457" @endif>{{ $value }}</text>
            @endforeach
        </g>

        {{-- Compte-tours : segments lumineux qui suivent l’aiguille, zone rouge au bout --}}
        <path d="{{ $arc(112, 0, 320) }}" pathLength="100" fill="none" stroke="url(#gauge-rpm-track)" stroke-width="8" stroke-dasharray=".9 .55"/>
        <g mask="url(#gauge-rpm-mask)"><path class="gauge__rpm" d="{{ $arc(112, 0, 320) }}" pathLength="100" fill="none" stroke="url(#gauge-rpm)" stroke-width="8"/></g>
        <g font-family="JetBrains Mono Variable, monospace" font-size="8.5" fill="#8f8889" text-anchor="middle" dominant-baseline="central">
            @foreach (range(0, 8) as $rpm)
                @php([$rx, $ry] = $point(96, $rpm * 40))
                <text x="{{ $rx }}" y="{{ $ry }}" @if ($rpm >= 7) fill="#ff5457" @endif>{{ $rpm }}</text>
            @endforeach
        </g>

        {{-- Marque, voyants et unité --}}
        <g class="gauge__lamps" font-family="JetBrains Mono Variable, monospace" font-size="6.5" font-weight="700" text-anchor="middle" dominant-baseline="central">
            <g class="gauge__lamp" style="--c: #ffb02e"><rect x="152" y="104" width="22" height="12" rx="3" fill="none" stroke="currentColor" stroke-width="1.2"/><text x="163" y="110.5" fill="currentColor">ABS</text></g>
            <g class="gauge__lamp" style="--c: #ffb02e; --d: .15s"><rect x="177" y="104" width="22" height="12" rx="3" fill="none" stroke="currentColor" stroke-width="1.2"/><text x="188" y="110.5" fill="currentColor">ESP</text></g>
            <g class="gauge__lamp" style="--c: #e5383b; --d: .3s"><rect x="202" y="104" width="22" height="12" rx="3" fill="none" stroke="currentColor" stroke-width="1.2"/><text x="213" y="110.5" fill="currentColor">OIL</text></g>
            <g class="gauge__lamp" style="--c: #3ddc84; --d: .45s"><rect x="227" y="104" width="22" height="12" rx="3" fill="none" stroke="currentColor" stroke-width="1.2"/><text x="238" y="110.5" fill="currentColor">LED</text></g>
        </g>
        <text x="200" y="152" text-anchor="middle" font-family="JetBrains Mono Variable, monospace" font-size="8" letter-spacing="3" fill="#b1a7a6">KM/H</text>
        <text x="200" y="164" text-anchor="middle" font-family="JetBrains Mono Variable, monospace" font-size="5.5" letter-spacing="2" fill="#6b6566">RPM ×1000</text>

        {{-- Petits cadrans : carburant et température, avec une aiguille qui oscille doucement --}}
        @foreach ($minis as $mini)
            <g transform="translate({{ $mini['cx'] }} {{ $mini['cy'] }})">
                <circle r="21" fill="#0b090a" stroke="#2a2527" stroke-width="2"/>
                <path d="M-14.4 -6.9 A16 16 0 0 1 14.4 -6.9" fill="none" stroke="#6b6566" stroke-width="1.5" stroke-dasharray="1 3.6"/>
                <path d="M7.4 -14.2 A16 16 0 0 1 14.4 -6.9" fill="none" stroke="{{ $mini['tone'] }}" stroke-width="2"/>
                <text x="-12" y="9" font-family="JetBrains Mono Variable, monospace" font-size="6" fill="#b1a7a6" text-anchor="middle">{{ $mini['low'] }}</text>
                <text x="12" y="9" font-family="JetBrains Mono Variable, monospace" font-size="6" fill="#b1a7a6" text-anchor="middle">{{ $mini['high'] }}</text>
                <g class="gauge__mini" style="--a: {{ $mini['a'] }}; animation-delay: {{ $mini['delay'] }}"><path d="M-0.9 3 L0 -15 L0.9 3Z" fill="#ff5457"/></g>
                <circle r="2.6" fill="#d9d4d4"/>
            </g>
        @endforeach

        {{-- Afficheur : rapport engagé et compteur --}}
        <rect x="167" y="317" width="66" height="36" rx="7" fill="#050707" stroke="#2a2527" stroke-width="2"/>
        <rect x="170" y="320" width="60" height="30" rx="5" fill="none" stroke="#3ddc84" stroke-opacity=".12"/>
        <text x="200" y="344.5" text-anchor="middle" font-family="JetBrains Mono Variable, monospace" font-size="7" letter-spacing="1" fill="#3ddc84" opacity=".85">012 874 km</text>

        {{-- Aiguille : ombre portée, lame effilée, contrepoids --}}
        <g class="gauge__needle">
            <g @class(['gauge__idle' => $idle])>
                <path d="M196 244 L199 54 L200 44 L201 54 L204 244Z" fill="#000" opacity=".4" transform="translate(5 7)"/>
                <rect x="195.2" y="198" width="9.6" height="48" rx="3" fill="#2a2527"/>
                <path d="M196.2 236 L199.1 48 L200 36 L200.9 48 L203.8 236Z" fill="url(#gauge-needle)"/>
                <path d="M199.6 224 L199.9 44 L200.4 224Z" fill="#fff" opacity=".55"/>
                <circle cx="200" cy="40" r="2.2" fill="#ff8a8c"/>
            </g>
        </g>
        <circle cx="200" cy="200" r="27" fill="#0b090a"/>
        <circle cx="200" cy="200" r="25" fill="url(#gauge-cap)" stroke="#0b090a" stroke-width="3"/>
        <circle cx="200" cy="200" r="17" fill="none" stroke="#fff" stroke-opacity=".12" stroke-width="1"/>
        <circle cx="200" cy="200" r="9" fill="url(#gauge-cap)" stroke="#000" stroke-opacity=".5"/>
        <circle cx="200" cy="200" r="3.6" fill="#e5383b"/>

        {{-- Verre : reflets --}}
        <path d="M34 150 A174 174 0 0 1 366 150 A230 230 0 0 0 34 150Z" fill="#fff" opacity=".06"/>
        <ellipse cx="150" cy="80" rx="62" ry="20" fill="#fff" opacity=".05" transform="rotate(-24 150 80)"/>
    </svg>
    <div class="gauge__readout" aria-hidden="true"><b class="gauge__speed"></b></div>
    <div class="gauge__gear" aria-hidden="true"><span>D</span><b></b></div>
    <div class="gauge__sheen" aria-hidden="true"></div>
</div>
