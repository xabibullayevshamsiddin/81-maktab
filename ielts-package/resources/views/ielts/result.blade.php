@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4 text-center">
    <p class="text-blue-600 font-semibold uppercase text-xs tracking-wide">Natijangiz</p>
    <div class="my-6">
        <div class="inline-flex items-center justify-center w-28 h-28 rounded-full bg-blue-50 border-4 border-blue-500">
            <span class="text-4xl font-bold text-blue-600">{{ $result->overall_band ?? '–' }}</span>
        </div>
    </div>
    <h1 class="text-2xl font-bold">{{ $result->level_label ?? 'Baholanmoqda' }}</h1>

    <div class="grid grid-cols-2 gap-4 my-8 text-left">
        @foreach ($result->section_bands as $skill => $band)
            <div class="border rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase">{{ $skill }}</p>
                <p class="text-xl font-semibold">{{ $band ?? 'Tez orada' }}</p>
            </div>
        @endforeach
    </div>

    @php $user = $result->attempt->user; @endphp
    @if (method_exists($user, 'donationRank') && $user->donationRank() >= 1)
        <div class="text-left bg-gray-50 border rounded-lg p-5 mb-6">
            <h3 class="font-semibold mb-2">Batafsil AI tahlil</h3>
            @foreach ($result->attempt->answers->whereNotNull('ai_feedback') as $answer)
                <div class="mb-4 text-sm">
                    <p class="font-medium">Writing band: {{ $answer->ai_feedback['overall_band'] ?? '–' }}</p>
                    <p class="text-gray-600">{{ $answer->ai_feedback['comments'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    @endif

    <p class="text-xs text-gray-400 mb-8">
        ⚠️ Bu AI tomonidan hisoblangan taxminiy natija va rasmiy IELTS ballini bildirmaydi.
    </p>

    {{-- Course recommendation hook: point low-scoring sections to your existing courses --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-5 text-left">
        <h3 class="font-semibold mb-1">Sizga tavsiya etilgan kurslar</h3>
        <p class="text-sm text-gray-600 mb-3">Natijangizga asoslanib, quyidagi kurslarni ko'rib chiqishingizni tavsiya qilamiz.</p>
        {{-- TODO: query your Courses table filtered by weakest skill, e.g. where category = 'english' --}}
    </div>
</div>
@endsection
