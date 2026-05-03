<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-6 py-4 bg-gradient-to-r from-indigo-600 to-blue-500 border border-transparent rounded-2xl font-black text-sm text-white uppercase tracking-widest hover:opacity-90 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-indigo-500/20']) }}>
    {{ $slot }}
</button>
