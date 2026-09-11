<x-layouts.main title="IELTS Test Natijasi — 81-IDUM">
@push('page_styles')
<script src="https://cdn.tailwindcss.com"></script>
<style>
    .ielts-wrap { max-width: 760px; margin: 0 auto; padding: 48px 16px 96px; }
    [data-theme='dark'] .ielts-card { background: #1e293b; border-color: #334155; color: #f8fafc; }
    [data-theme='dark'] .ielts-box-subtle { background: #0f172a; border-color: #1e293b; color: #cbd5e1; }
</style>
@endpush

<div class="ielts-wrap text-center">
    <p class="text-indigo-600 dark:text-indigo-400 font-semibold uppercase text-xs tracking-wider">Test Natijangiz</p>
    
    <div class="my-6">
        <div class="inline-flex items-center justify-center w-28 h-28 sm:w-32 sm:h-32 rounded-full bg-indigo-50 dark:bg-slate-800 border-4 border-indigo-500 shadow-lg">
            <span class="text-4xl sm:text-5xl font-extrabold text-indigo-600 dark:text-indigo-400">{{ $result->overall_band ?? '–' }}</span>
        </div>
    </div>

    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white">{{ $result->level_label ?? 'Baholanmoqda' }}</h1>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 my-8 text-left">
        @foreach ($result->section_bands ?? [] as $skill => $band)
            <div class="ielts-card border border-gray-200 dark:border-gray-700 rounded-2xl p-4 bg-white dark:bg-slate-800 shadow-sm">
                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">{{ $skill }}</p>
                <p class="text-xl sm:text-2xl font-bold mt-1 text-gray-900 dark:text-white">{{ $band ?? 'Tez orada' }}</p>
            </div>
        @endforeach
    </div>

    @php 
        $user = $result->attempt->user; 
        $isDonor = $user && $user->isDonor();
    @endphp

    @if ($isDonor)
        <div class="text-left bg-white dark:bg-slate-800 border border-indigo-200 dark:border-indigo-900/50 rounded-2xl p-6 mb-6 shadow-sm">
            <div class="flex items-center gap-2 mb-4 text-indigo-600 dark:text-indigo-400 font-bold">
                <i class="fa-solid fa-brain"></i>
                <h3 class="text-base font-bold">Batafsil AI tahlil va tavsiyalar</h3>
            </div>
            @forelse ($result->attempt->answers->whereNotNull('ai_feedback') as $answer)
                <div class="mb-4 last:mb-0 p-4 rounded-xl bg-gray-50 dark:bg-slate-900 border border-gray-100 dark:border-slate-800 text-sm">
                    <p class="font-bold text-gray-900 dark:text-white mb-1">Writing Band: <span class="text-indigo-600 dark:text-indigo-400">{{ $answer->ai_feedback['overall_band'] ?? '–' }}</span></p>
                    @if (!empty($answer->ai_feedback['criteria']))
                        <div class="grid grid-cols-2 gap-2 my-2 text-xs text-gray-600 dark:text-gray-400">
                            @foreach ($answer->ai_feedback['criteria'] as $cKey => $cVal)
                                @if ($cVal !== null)
                                    <div>{{ ucwords(str_replace('_', ' ', $cKey)) }}: <strong>{{ $cVal }}</strong></div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    <p class="text-gray-700 dark:text-gray-300 mt-2">{{ $answer->ai_feedback['comments'] ?? '' }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500">Writing bo'yicha tahlil tayyorlanmoqda...</p>
            @endforelse
        </div>
    @else
        <div class="bg-indigo-50 dark:bg-slate-800/80 border border-indigo-200 dark:border-slate-700 rounded-2xl p-5 mb-6 text-left flex items-center justify-between gap-4">
            <div>
                <h4 class="font-bold text-gray-900 dark:text-white text-sm">Batafsil AI tahlilini xohlaysizmi?</h4>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">Donor obunasi bilan barcha xatolaringiz bo'yicha alohida tahlil oling.</p>
            </div>
            <a href="{{ route('donation.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition">
                Donor bo'lish
            </a>
        </div>
    @endif

    <p class="text-xs text-gray-400 dark:text-gray-500 mb-8">
        ⚠️ Bu AI tomonidan hisoblangan taxminiy natija va rasmiy IELTS ballini bildirmaydi.
    </p>

    <div class="bg-indigo-50/50 dark:bg-slate-900/80 border border-indigo-100 dark:border-slate-800 rounded-2xl p-6 text-left shadow-sm">
        <h3 class="font-bold text-base text-gray-900 dark:text-white mb-1">Ingliz tili darajangizni oshirmoqchimisiz?</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Maktabimizdagi mavjud ingliz tili va IELTS tayyorlov kurslari bilan tanishing.</p>
        <div class="flex items-center gap-3">
            <a href="{{ route('courses') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition shadow-sm">
                <i class="fa-solid fa-graduation-cap"></i> Kurslarni ko'rish
            </a>
            <a href="{{ route('ielts.index') }}" class="inline-flex items-center gap-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800 px-5 py-2.5 rounded-xl font-semibold text-sm transition">
                Boshqa testlar
            </a>
        </div>
    </div>
</div>
</x-layouts.main>
