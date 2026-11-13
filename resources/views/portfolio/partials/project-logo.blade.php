@php
    use Illuminate\Support\Str;

    // Emblème généré à partir du projet (titre, identifiant, technologie principale) : forme, anneau et initiales propres à chaque projet.
    $seed = crc32((string) $project->slug);
    $words = collect(preg_split('/[\s\-_.]+/u', (string) $project->title, -1, PREG_SPLIT_NO_EMPTY))
        ->reject(fn (string $word): bool => in_array(Str::lower($word), ['de', 'du', 'la', 'le', 'les', 'des', 'the', 'of', 'and', 'et', 'v1', 'v2', 'v3'], true))
        ->values();
    $initials = $words->count() > 1
        ? Str::upper(Str::substr($words[0], 0, 1).Str::substr($words[1], 0, 1))
        : Str::upper(Str::substr((string) ($words[0] ?? $project->title), 0, 2));
    $shapes = [0, 4, 5, 6, 8];
    $sides = $shapes[$seed % count($shapes)];
    $turn = ($seed >> 3) % 30;
    $ringDash = [['2 6', 7], ['14 8', 5], ['1 3', 9]][($seed >> 7) % 3];
    $arcStart = ($seed >> 11) % 360;
    $stack = Str::upper(Str::limit((string) ($project->technologies[0] ?? ''), 10, ''));
    $polygon = function (float $radius) use ($sides, $turn): string {
        $points = [];
        for ($i = 0; $i < $sides; $i++) {
            $angle = deg2rad(-90 + $turn + 360 * $i / $sides);
            $points[] = round(100 + $radius * cos($angle), 2).','.round(100 + $radius * sin($angle), 2);
        }

        return implode(' ', $points);
    };
    $gradient = 'logo-'.$seed;
@endphp
<svg class="logo {{ $class ?? '' }}" viewBox="0 0 200 200" role="img" aria-label="{{ __('portfolio.projects.logo', ['title' => $project->title]) }}">
    <defs>
        <linearGradient id="{{ $gradient }}" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ba181b"/><stop offset="1" stop-color="#2a0708"/></linearGradient>
    </defs>
    <circle class="logo__ring" cx="100" cy="100" r="95" fill="none" stroke="#f5f3f4" stroke-opacity=".5" stroke-width="2" stroke-dasharray="{{ $ringDash[0] }}" style="--spin: {{ $ringDash[1] }}s"/>
    @if ($sides === 0)
        <circle cx="100" cy="100" r="82" fill="none" stroke="#f5f3f4" stroke-width="3"/>
        <circle cx="100" cy="100" r="70" fill="url(#{{ $gradient }})" stroke="#ff5457" stroke-width="2"/>
        <circle cx="100" cy="100" r="82" fill="none" stroke="#e5383b" stroke-width="7" stroke-linecap="round" stroke-dasharray="70 460" transform="rotate({{ $arcStart }} 100 100)"/>
    @else
        <polygon points="{{ $polygon(84) }}" fill="none" stroke="#f5f3f4" stroke-width="3" stroke-linejoin="round"/>
        <polygon points="{{ $polygon(70) }}" fill="url(#{{ $gradient }})" stroke="#ff5457" stroke-width="2" stroke-linejoin="round"/>
        <polygon points="{{ $polygon(84) }}" fill="none" stroke="#e5383b" stroke-width="7" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="{{ round(84 * 6.28 / $sides * 1.1) }} 900" transform="rotate({{ $arcStart }} 100 100)"/>
    @endif
    <text x="100" y="{{ $stack !== '' ? 112 : 120 }}" text-anchor="middle" font-family="Space Grotesk Variable, sans-serif" font-size="{{ mb_strlen($initials) > 1 ? 56 : 66 }}" font-weight="700" letter-spacing="-2" fill="#fff">{{ $initials }}</text>
    @if ($stack !== '')
        <text x="100" y="136" text-anchor="middle" font-family="JetBrains Mono Variable, monospace" font-size="9" letter-spacing="3" fill="#ffb3b4">{{ $stack }}</text>
    @endif
</svg>
