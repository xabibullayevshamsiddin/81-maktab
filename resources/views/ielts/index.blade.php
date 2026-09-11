<x-layouts.main title="IELTS Tayyorgarlik — 81-IDUM">
@push('page_styles')
<script src="https://cdn.tailwindcss.com"></script>
<style>
    .ielts-wrap { max-width: 860px; margin: 0 auto; padding: 48px 16px 96px; }
    [data-theme='dark'] .ielts-card { background: #1e293b; border-color: #334155; }
    [data-theme='dark'] .ielts-card-title { color: #f8fafc; }
    [data-theme='dark'] .ielts-box-subtle { background: #0f172a; border-color: #1e293b; color: #cbd5e1; }
</style>
@endpush

<div class="ielts-wrap">
    <div class="text-center mb-10">
        <p class="text-indigo-600 dark:text-indigo-400 font-semibold uppercase text-xs tracking-wider">IELTS Tayyorgarlik</p>
        <h1 class="text-3xl sm:text-4xl font-extrabold mt-2 text-gray-900 dark:text-white">Ingliz tili darajangizni bilib oling</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-3 max-w-xl mx-auto text-sm sm:text-base">
            Reading, Listening va Writing bo'limlaridan iborat qisqa test orqali taxminiy IELTS band darajangizni aniqlang.
        </p>
    </div>

    @if (session('error'))
        <div class="bg-red-500/10 border border-red-500/30 text-red-600 dark:text-red-400 rounded-xl p-4 mb-6 text-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @php
        $user = auth()->user();
        $isDonor = $user && $user->isDonor();
    @endphp

    @if ($monthlyLimit !== null && $usedThisMonth >= $monthlyLimit)
        <div class="bg-amber-500/10 border border-amber-500/30 text-amber-700 dark:text-amber-300 rounded-xl p-4 mb-6 text-sm flex items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Bu oy uchun bepul urinishlar limitiga yetdingiz ({{ $usedThisMonth }}/{{ $monthlyLimit }}).</span>
            </div>
            @if (!$isDonor || $user->donation_rank === 'supporter')
                <a href="{{ route('donation.index') }}" class="underline font-semibold whitespace-nowrap hover:text-amber-800 dark:hover:text-amber-200">
                    Homiylik darajasini oshirish
                </a>
            @endif
        </div>
    @endif

    @if ($isDonor)
        <div class="bg-indigo-500/10 border border-indigo-500/30 text-indigo-700 dark:text-indigo-300 rounded-xl p-4 mb-6 text-sm flex items-center gap-2.5">
            <i class="fa-solid fa-star text-indigo-500 text-base"></i>
            <span>Homiy sifatida sizga barcha bo'limlar bo'yicha batafsil AI tahlil va tavsiyalar taqdim etiladi.</span>
        </div>
    @endif

    @forelse ($tests as $test)
        <div class="ielts-card border border-gray-200 rounded-2xl p-6 mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white shadow-sm transition hover:shadow-md">
            <div>
                <h3 class="ielts-card-title font-bold text-lg sm:text-xl text-gray-900">{{ $test->title }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-3">
                    <span><i class="fa-regular fa-clock mr-1"></i>{{ $test->time_limit_minutes }} daqiqa</span>
                    <span>•</span>
                    <span class="capitalize">{{ $test->type === 'placement' ? 'Placement Test' : 'Full Mock' }}</span>
                </p>
            </div>
            <form method="POST" action="{{ route('ielts.start', $test) }}">
                @csrf
                <button type="submit"
                    @disabled($monthlyLimit !== null && $usedThisMonth >= $monthlyLimit)
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-semibold disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center justify-center gap-2 w-full sm:w-auto shadow-sm">
                    <i class="fa-solid fa-play text-xs"></i> Testni boshlash
                </button>
            </form>
        </div>
    @empty
        <div class="text-center py-16 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl text-gray-500">
            <i class="fa-solid fa-file-lines text-4xl mb-3 text-gray-400"></i>
            <p class="text-base font-medium">Hozirda faol IELTS testlari mavjud emas.</p>
        </div>
    @endforelse
</div>
</x-layouts.main>
