<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-black text-3xl text-gray-900 leading-tight">⚡ Panel Agent Cuaca</h2>
                <p class="text-sm text-gray-500 mt-0.5">Jalankan <em>Weather-Adaptive Agent</em> secara manual untuk tanggal tertentu.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 hover:underline">
                ← Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-[#F8FAFC]">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Form Agent -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/40">
                    <h3 class="text-lg font-black text-gray-900">🌦️ Jalankan Agent</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Agent akan memeriksa cuaca & mengalihkan jadwal outdoor yang terdeteksi hujan.</p>
                </div>
                <form action="{{ route('agent.run') }}" method="POST" class="p-8 space-y-6">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal yang Diproses</label>
                        <input type="date" name="date" value="{{ now()->format('Y-m-d') }}"
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-base">
                    </div>
                    <button type="submit"
                            class="w-full py-3 bg-gradient-to-r from-indigo-600 to-blue-500 text-white font-black text-base rounded-xl hover:opacity-90 transition-opacity shadow-lg shadow-indigo-200/50">
                        🚀 Jalankan Agent Sekarang
                    </button>
                </form>
            </div>

            <!-- Hasil Agent -->
            @if(session('success'))
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 bg-emerald-50/40">
                    <h3 class="text-lg font-black text-emerald-700">
                        ✅ Agent selesai dijalankan untuk tanggal: <span class="text-gray-900">{{ session('date') }}</span>
                    </h3>
                    <p class="text-sm text-emerald-600 mt-1">{{ session('count') }} itinerary berhasil dialihkan.</p>
                </div>

                @if(count(session('result', [])) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-xs text-gray-400 uppercase tracking-widest border-b border-gray-100">
                                <th class="px-6 py-3 text-left">User</th>
                                <th class="px-6 py-3 text-left">Kota</th>
                                <th class="px-6 py-3 text-left">Dari (Outdoor)</th>
                                <th class="px-6 py-3 text-left">Ke (Indoor)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach(session('result') as $r)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-medium text-gray-800">{{ $r['user'] }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $r['city'] }}</td>
                                <td class="px-6 py-3 text-red-400 line-through">{{ $r['old_destination'] }}</td>
                                <td class="px-6 py-3 font-bold text-emerald-600">{{ $r['new_destination'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="px-8 py-8 text-center text-gray-400">
                    <p class="text-3xl mb-2">☀️</p>
                    <p>Tidak ada jadwal yang perlu dialihkan — tidak ada hujan atau semua jadwal sudah terjadwal ulang.</p>
                </div>
                @endif
            </div>
            @endif

            <!-- Panduan -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-base font-black text-gray-900 mb-4">📘 Cara Kerja Agent</h3>
                <ol class="space-y-3 text-sm text-gray-600">
                    <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-black text-xs shrink-0">1</span><span>Agent mengambil semua itinerary <strong>outdoor</strong> dengan status <strong>planned</strong> pada tanggal yang dipilih.</span></li>
                    <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-black text-xs shrink-0">2</span><span>Untuk setiap itinerary, agent mengecek kondisi cuaca kota dari database simulasi (atau API OpenWeatherMap jika tersedia).</span></li>
                    <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-black text-xs shrink-0">3</span><span>Jika cuaca <strong>hujan</strong>, agent mencari destinasi indoor terbaik di kota yang sama (prioritas: kategori serupa).</span></li>
                    <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-black text-xs shrink-0">4</span><span>Itinerary diperbarui, log dicatat, dan notifikasi real-time dikirim ke dashboard user via <strong>Laravel Reverb</strong>.</span></li>
                </ol>
            </div>
        </div>
    </div>
</x-app-layout>
