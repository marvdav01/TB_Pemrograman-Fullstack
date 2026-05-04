<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Smart Travel - Platform perjalanan cerdas dengan teknologi Weather-Adaptive Agent yang secara otomatis menyesuaikan jadwal wisata Anda berdasarkan kondisi cuaca real-time.">
    <title>Smart Travel - Weather-Adaptive Trip Orchestration</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { font-family: 'Outfit', sans-serif; }
        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #3b82f6 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-bg {
            background: radial-gradient(ellipse 80% 80% at 50% -20%, rgba(99,102,241,0.15) 0%, transparent 60%);
        }
        .card-glow:hover {
            box-shadow: 0 0 0 1px rgba(99,102,241,0.3), 0 20px 40px rgba(99,102,241,0.1);
        }
        .float { animation: float 6s ease-in-out infinite; }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
    </style>
</head>
<body class="bg-[#060818] text-white overflow-x-hidden">

    <!-- Nav -->
    <nav class="fixed top-0 left-0 right-0 z-50 backdrop-blur-xl bg-[#060818]/80 border-b border-white/5">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="/" class="flex items-center gap-4 group">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-12 h-12 object-contain drop-shadow-[0_0_15px_rgba(99,102,241,0.5)] transition-transform group-hover:scale-110">
                    <span class="font-black text-2xl tracking-tight text-white">Smart<span class="text-indigo-400">Travel</span></span>
                </a>
            </div>
            <div class="flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm font-bold text-white/70 hover:text-white transition">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-bold text-white/70 hover:text-white transition">Masuk</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-sm font-bold px-4 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-xl transition">Daftar</a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-bg min-h-screen flex items-center pt-24 pb-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="space-y-8">
                    <div class="inline-flex items-center gap-2 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-bold px-4 py-2 rounded-full uppercase tracking-widest">
                        <span class="w-2 h-2 bg-indigo-400 rounded-full animate-pulse"></span>
                        Tugas Besar Pemrograman Fullstack
                    </div>

                    <h1 class="text-5xl lg:text-6xl font-black leading-tight tracking-tight">
                        Perjalanan
                        <span class="gradient-text">Lebih Cerdas</span><br>
                        Dengan AI Cuaca
                    </h1>

                    <p class="text-lg text-white/60 leading-relaxed max-w-lg">
                        Platform perjalanan pertama yang secara <strong class="text-white">otomatis</strong> mengalihkan jadwal wisata outdoor Anda ke destinasi indoor ketika hujan terdeteksi — tanpa perlu interaksi manual.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-gradient-to-r from-indigo-600 to-blue-500 rounded-2xl font-black text-lg hover:opacity-90 transition-opacity shadow-2xl shadow-indigo-500/30">
                                Buka Dashboard →
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-gradient-to-r from-indigo-600 to-blue-500 rounded-2xl font-black text-lg hover:opacity-90 transition-opacity shadow-2xl shadow-indigo-500/30">
                                Mulai Sekarang →
                            </a>
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white/5 border border-white/10 rounded-2xl font-bold text-lg hover:bg-white/10 transition">
                                Sudah punya akun
                            </a>
                        @endauth
                    </div>

                    <div class="flex items-center gap-6 text-sm text-white/40">
                        <span>✓ Real-time WebSocket</span>
                        <span>✓ AI Rule-Based Agent</span>
                        <span>✓ Gratis</span>
                    </div>
                </div>

                <!-- Mockup Card -->
                <div class="relative flex justify-center">
                    <div class="float w-full max-w-sm">
                        <!-- Status Card -->
                        <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-white/60 text-xs font-bold uppercase tracking-widest">Trip Orchestration</span>
                                <span class="flex items-center gap-1.5 text-xs font-bold text-emerald-400">
                                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                                    Live
                                </span>
                            </div>

                            <!-- Auto-change alert -->
                            <div class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-4">
                                <p class="text-amber-400 font-black text-sm">🌧️ Hujan Terdeteksi!</p>
                                <p class="text-white/60 text-xs mt-1">Jadwal Monas dialihkan ke Museum Nasional secara otomatis.</p>
                            </div>

                            <!-- Itinerary items -->
                            <div class="space-y-3">
                                <div class="flex items-center gap-3 bg-white/5 rounded-2xl p-3">
                                    <div class="w-8 h-8 bg-emerald-500/20 rounded-xl flex items-center justify-center text-sm">🏛️</div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-white">Museum Nasional</p>
                                        <p class="text-xs text-white/40">Jakarta · Indoor</p>
                                    </div>
                                    <span class="text-[10px] bg-amber-400/20 text-amber-400 px-2 py-0.5 rounded-full font-black">CHANGED</span>
                                </div>
                                <div class="flex items-center gap-3 bg-white/5 rounded-2xl p-3">
                                    <div class="w-8 h-8 bg-orange-500/20 rounded-xl flex items-center justify-center text-sm">🌄</div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-white">Pantai Kuta</p>
                                        <p class="text-xs text-white/40">Bali · Outdoor</p>
                                    </div>
                                    <span class="text-[10px] bg-emerald-400/20 text-emerald-400 px-2 py-0.5 rounded-full font-black">PLANNED</span>
                                </div>
                                <div class="flex items-center gap-3 bg-white/5 rounded-2xl p-3">
                                    <div class="w-8 h-8 bg-orange-500/20 rounded-xl flex items-center justify-center text-sm">⛩️</div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-white">Candi Borobudur</p>
                                        <p class="text-xs text-white/40">Yogyakarta · Outdoor</p>
                                    </div>
                                    <span class="text-[10px] bg-emerald-400/20 text-emerald-400 px-2 py-0.5 rounded-full font-black">PLANNED</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Feature Section -->
    <section class="py-24 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-black tracking-tight">Fitur Utama Aplikasi</h2>
                <p class="text-white/50 mt-3 max-w-xl mx-auto">Dibangun dengan teknologi modern untuk pengalaman travel yang mulus dan cerdas.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach([
                    ['🤖', 'Weather-Adaptive Agent', 'Agent berbasis aturan (rule-based) yang secara otomatis mengubah jadwal outdoor ke indoor saat hujan terdeteksi.', 'from-indigo-500/10 to-indigo-500/5'],
                    ['⚡', 'Real-time Notification', 'Notifikasi instan via WebSocket (Laravel Reverb) saat jadwal perjalanan berubah otomatis.', 'from-blue-500/10 to-blue-500/5'],
                    ['🗺️', 'Multi-City Coverage', 'Mendukung beberapa kota sekaligus: Jakarta, Bandung, Bali, dan Yogyakarta.', 'from-cyan-500/10 to-cyan-500/5'],
                    ['📋', 'Audit Log', 'Rekam jejak lengkap setiap perubahan jadwal yang dilakukan agent, beserta alasan dan kondisi cuaca.', 'from-purple-500/10 to-purple-500/5'],
                    ['✅', 'Manual Override', 'User dapat mengkonfirmasi atau menolak perubahan yang dilakukan agent secara langsung.', 'from-emerald-500/10 to-emerald-500/5'],
                    ['🔐', 'Auth Berbasis Breeze', 'Sistem login & registrasi yang aman menggunakan Laravel Breeze + Livewire Volt.', 'from-rose-500/10 to-rose-500/5'],
                ] as [$icon, $title, $desc, $gradient])
                <div class="card-glow bg-gradient-to-br {{ $gradient }} border border-white/5 rounded-3xl p-7 transition-all duration-300">
                    <div class="text-4xl mb-4">{{ $icon }}</div>
                    <h3 class="font-black text-lg text-white mb-2">{{ $title }}</h3>
                    <p class="text-white/50 text-sm leading-relaxed">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Tech Stack -->
    <section class="py-20 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <p class="text-white/30 text-xs font-bold uppercase tracking-widest mb-8">Dibangun Dengan</p>
            <div class="flex flex-wrap items-center justify-center gap-6">
                @foreach(['Laravel 12', 'Livewire Volt', 'Tailwind CSS', 'Laravel Reverb', 'Alpine.js', 'MySQL'] as $tech)
                    <span class="px-5 py-2 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white/60">{{ $tech }}</span>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/5 py-8">
        <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-white/30 text-sm">
            <p>© {{ date('Y') }} Smart Travel. Tugas Besar Pemrograman Fullstack.</p>
            <p>Laravel {{ app()->version() }} · PHP {{ PHP_MAJOR_VERSION }}.{{ PHP_MINOR_VERSION }}</p>
        </div>
    </footer>
</body>
</html>
