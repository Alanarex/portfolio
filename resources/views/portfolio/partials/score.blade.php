@php($ratio = $max > 0 ? min(max($value / $max, 0), 1) : 0)
<div class="score" role="img" aria-label="{{ $label }} {{ $value }} / {{ $max }}">
    <span class="score__label mono" aria-hidden="true">{{ $label }}</span>
    <span class="score__value" aria-hidden="true"><b>{{ $value }}</b><i> / {{ $max }}</i></span>
    <span class="score__bar" aria-hidden="true"><i style="--v: {{ round($ratio, 4) }}"></i></span>
</div>
