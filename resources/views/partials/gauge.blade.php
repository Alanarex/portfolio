@php($idle = $idle ?? false)
<div class="gauge {{ $class ?? '' }}">
    <svg viewBox="0 0 400 400" @if(! empty($label)) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif>
        <circle cx="200" cy="200" r="199" fill="url(#gauge-bezel)"/>
        <circle cx="200" cy="200" r="191" fill="#0b090a"/>
        <circle cx="200" cy="200" r="187" fill="url(#gauge-dial)"/>
        <path d="M69.9 330.1 A184 184 0 1 1 330.1 330.1" pathLength="320" fill="none" stroke="#e5383b" stroke-width="4" stroke-dasharray="0 280 40 400"/>
        <path d="M78.4 321.6 A172 172 0 1 1 321.6 321.6" pathLength="320" fill="none" stroke="#6b6566" stroke-width="8" stroke-dasharray="1 9"/>
        <path d="M79.8 320.2 A170 170 0 1 1 320.2 320.2" pathLength="320" fill="none" stroke="#f5f3f4" stroke-width="14" stroke-dasharray="1.6 18.4"/>
        <g font-family="Space Grotesk Variable, sans-serif" font-size="15" font-weight="600" fill="#f5f3f4" text-anchor="middle" dominant-baseline="central">
            <text x="106.7" y="293.3">0</text><text x="70.5" y="225.8">40</text><text x="78" y="149.5">80</text><text x="126.7" y="90.2">120</text><text x="200" y="68">160</text><text x="273.3" y="90.2">200</text><text x="322" y="149.5">240</text><text x="329.5" y="225.8">280</text><text x="293.3" y="293.3" fill="#e5383b">320</text>
        </g>
        <text x="200" y="152" text-anchor="middle" font-family="JetBrains Mono Variable, monospace" font-size="9" letter-spacing="3" fill="#b1a7a6">KM/H</text>
        <g class="gauge__needle"><g @class(['gauge__idle' => $idle])><path d="M196.5 236 L200 46 L203.5 236Z" fill="url(#gauge-needle)"/></g></g>
        <circle cx="200" cy="200" r="26" fill="url(#gauge-cap)" stroke="#0b090a" stroke-width="3"/>
        <circle cx="200" cy="200" r="5" fill="#e5383b"/>
        <path d="M34 150 A174 174 0 0 1 366 150 A230 230 0 0 0 34 150Z" fill="#fff" opacity=".05"/>
    </svg>
    <div class="gauge__readout" aria-hidden="true"><b class="gauge__speed"></b><span class="gauge__unit">KM/H</span></div>
</div>
