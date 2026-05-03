@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-bold text-sm text-white/60 mb-2']) }}>
    {{ $value ?? $slot }}
</label>
