@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-white/5 border-white/10 text-white placeholder-white/20 focus:border-indigo-500 focus:ring-indigo-500 rounded-2xl shadow-sm px-4 py-3 transition-all duration-300']) }}>
