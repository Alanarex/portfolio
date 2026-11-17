@php
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
@endphp
<div class="gauge {{ $class ?? '' }}">
    <svg viewBox="0 0 400 400" @if(! empty($label)) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif>
        {{-- Échelle : un simple arc, une graduation tous les 40 km/h --}}
        <path d="{{ $arc(170, 0, 320) }}" fill="none" stroke="#f5f3f4" stroke-opacity=".16" stroke-width="3"/>
        <path class="gauge__arc" d="{{ $arc(170, 0, 320) }}" pathLength="100" fill="none" stroke="#e5383b" stroke-width="3"/>
        <g stroke="#f5f3f4" stroke-opacity=".5" stroke-width="2">
            @foreach (range(0, 320, 40) as $value)
                @php
                    [$ox, $oy] = $point(170, $value);
                    [$ix, $iy] = $point(158, $value);
                @endphp
                <line x1="{{ $ox }}" y1="{{ $oy }}" x2="{{ $ix }}" y2="{{ $iy }}"/>
            @endforeach
        </g>
        {{-- Aiguille : un trait --}}
        <g class="gauge__needle">
            <line x1="200" y1="200" x2="200" y2="52" stroke="#e5383b" stroke-width="3" stroke-linecap="round"/>
        </g>
        <circle cx="200" cy="200" r="5" fill="#e5383b"/>
        <text x="200" y="332" text-anchor="middle" font-family="JetBrains Mono Variable, monospace" font-size="11" letter-spacing="4" fill="#b1a7a6">KM/H</text>
    </svg>
    <div class="gauge__readout" aria-hidden="true"><b class="gauge__speed"></b></div>
</div>
