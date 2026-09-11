@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-12 px-4">
    <div class="text-center mb-10">
        <p class="text-blue-600 font-semibold uppercase text-xs tracking-wide">IELTS Tayyorgarlik</p>
        <h1 class="text-3xl font-bold mt-2">Ingliz tili darajangizni bilib oling</h1>
        <p class="text-gray-500 mt-2">Reading, Listening va Writing bo'limlaridan iborat qisqa test orqali
            taxminiy IELTS band darajangizni aniqlang.</p>
    </div>

    @if ($monthlyLimit !== null && $usedThisMonth >= $monthlyLimit)
        <div class="bg-orange-50 border border-orange-200 text-orange-700 rounded-lg p-4 mb-6 text-sm">
            Bu oy uchun urinishlar limitiga yetdingiz ({{ $usedThisMonth }}/{{ $monthlyLimit }}).
            @if ($donationRank < 2)
                <a href="{{ route('donations.index') }}" class="underline font-medium">Homiylik darajangizni oshiring</a>
                va cheksiz urinish imkoniga ega bo'ling.
            @endif
        </div>
    @endif

    @if ($donationRank >= 1)
        <div class="bg-blue-50 border border-blue-200 text-blue-700 rounded-lg p-4 mb-6 text-sm">
            🌟 Homiy sifatida sizga batafsil AI tahlil va (2-darajadan boshlab) shaxsiy tayyorgarlik rejasi taqdim etiladi.
        </div>
    @endif

    @foreach ($tests as $test)
        <div class="border rounded-xl p-6 mb-4 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-lg">{{ $test->title }}</h3>
                <p class="text-sm text-gray-500">{{ $test->time_limit_minutes }} daqiqa · {{ ucfirst($test->type) }}</p>
            </div>
            <form method="POST" action="{{ route('ielts.start', $test) }}">
                @csrf
                <button type="submit"
                    @disabled($monthlyLimit !== null && $usedThisMonth >= $monthlyLimit)
                    class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-medium disabled:opacity-40">
                    Testni boshlash
                </button>
            </form>
        </div>
    @endforeach
</div>
@endsection
