<?php

namespace App\Services\Ai\Concerns;

use Illuminate\Support\Str;

/**
 * Maktab qoidalari, baholash tizimi va yutuqlar uchun match metodlari.
 * AiService dan ajratilgan trait.
 */
trait SchoolInfoMatch
{
    /**
     * Maktab qoidalari va tartibi
     */
    private function matchSchoolRulesQuery(string $message): ?string
    {
        $q = $this->normalizeSearchText($message);

        $hasRulesIntent = Str::contains($q, [
            'qoida', 'qoidalar', 'qonun', 'nizom', 'intizom', 'tartib',
            'odob', 'odob-axloq', 'axloq', 'qoidalar toplami',
            'maktab nizomi', 'maktab qoidalari', 'ichki tartib',
        ]);

        if (! $hasRulesIntent) {
            return null;
        }

        return "📋 **MAKTAB ICHKI TARTIB-QOIDALARI**\n"
            ."━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n"
            ."⏰ **Dars vaqtlari:**\n"
            ."- Darsga kechikmaslik kerak.\n"
            ."- Kechikish sababini sinf rahbariga aytish shart.\n\n"
            ."📱 **Telefon qoidalari:**\n"
            ."- Dars vaqtida telefon ishlatish mumkin emas.\n"
            ."- Zarur holatda oldindan ruxsat olish kerak.\n\n"
            ."👗 **Kiyinish qoidalari:**\n"
            ."- Maktab formasida kelish shart.\n"
            ."- Ozoda va tartibli bo'lish kerak.\n\n"
            ."📚 **O'quv jarayoni:**\n"
            ."- Uy vazifasini vaqtida bajarish.\n"
            ."- Darsda faol ishtirok etish.\n"
            ."- O'qituvchilarga hurmat bilan munosabatda bo'lish.\n\n"
            ."🏫 **Maktab hududi:**\n"
            ."- Dars vaqtida ruxsatsiz maktabdan chiqish mumkin emas.\n"
            ."- Maktab mulkiga g'amxo'rlik qilish.\n\n"
            ."━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
            ."💡 Batafsil ma'lumot uchun maktab ma'muriyatiga murojaat qiling.";
    }

    /**
     * Baholash tizimi haqida savol
     */
    private function matchGradingSystemQuery(string $message): ?string
    {
        $q = $this->normalizeSearchText($message);

        $hasGradingIntent = Str::contains($q, [
            'baho', 'baholash', 'ball', 'reyting', 'ortacha baho',
            'yaxshi', 'yomon', 'qoniqarli', 'alohida', 'bali',
            'qanday baholaydi', 'baholar', 'imtihon bali',
            'ortacha ball', 'reyting tizimi', 'grad',
        ]);

        if (! $hasGradingIntent) {
            return null;
        }

        if (Str::contains($q, ['imtihon', 'test', 'exam'])) {
            return null; // Bu matchExamAssistantQuery hal qiladi
        }

        return "📊 **BAHOLASH TIZIMI**\n"
            ."━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n"
            ."Davlat ta'lim standartlari bo'yicha baholash:\n\n"
            ."- **A'lo (5)** — 90-100%\n"
            ."- **Yaxshi (4)** — 75-89%\n"
            ."- **Qoniqarli (3)** — 55-74%\n"
            ."- **Yomon (2)** — 0-54%\n\n"
            ."━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
            ."📝 **Baholash usullari:**\n"
            ."- Kunlik baholar\n"
            ."- Oraliq nazorat\n"
            ."- Yakuniy nazorat\n"
            ."- Imtihon natijalari\n\n"
            ."📊 O'rtacha baho profilingizda ko'rinadi: ".route('profile.show');
    }

    /**
     * O'quvchilar yutuqlari va muvaffaqiyatlari
     */
    private function matchAchievementsQuery(string $message): ?string
    {
        $q = $this->normalizeSearchText($message);

        $hasAchievementIntent = Str::contains($q, [
            'yutuq', 'yutuqlar', 'muvaffaqiyat', 'galaba', 'mukofot',
            'olimpiada', 'musobaqa', 'tanlov', 'konkurs',
            'eng yaxshi', 'chempion', 'sovrindor', 'munosib',
        ]);

        if (! $hasAchievementIntent) {
            return null;
        }

        return "🏆 **O'QUVCHILAR YUTUQLARI**\n"
            ."━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n"
            ."Maktabimiz o'quvchilari turli tanlov va olimpiadalarda faol ishtirok etadi:\n\n"
            ."🥇 **Fan olimpiadalari:**\n"
            ."- Matematika, Fizika, Kimyo, Biologiya fanlari bo'yicha\n"
            ."- Davlat va xalqaro darajadagi olimpiadalar\n\n"
            ."🏅 **Sport musobaqalari:**\n"
            ."- Futbol, shaxmat, voleybol\n"
            ."- Maktablararo musobaqalar\n\n"
            ."🎨 **Ijodiy tanlovlar:**\n"
            ."- Rasmlar tanlovi, adabiy tanlovlar\n"
            ."- Musiqa va raqs tanlovlari\n\n"
            ."━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
            ."📊 Natijalar: ".route('post')."\n"
            ."📅 Taqvim: ".route('calendar');
    }

    /**
     * Sinfdagi dars jadvali
     */
    private function matchClassScheduleQuery(string $message): ?string
    {
        $q = $this->normalizeSearchText($message);

        $hasScheduleIntent = Str::contains($q, [
            'jadval', 'dars jadvali', 'qaysi kun', 'qanday dars',
            'ertalabki dars', 'tushdan keyin', 'smena',
            'sinfdagi', 'nechanchi dars', 'dars raqami',
        ]);

        if (! $hasScheduleIntent) {
            return null;
        }

        if (Str::contains($q, ['taqvim', 'tadbir', 'sanada'])) {
            return null; // Bu matchCalendarAndEvents hal qiladi
        }

        return "📋 **DARS JADVALI**\n"
            ."━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n"
            ."🌅 **I-SMENA (08:00-13:05)**\n"
            ."1-dars: 08:00-08:45\n"
            ."2-dars: 08:50-09:35\n"
            ."3-dars: 09:40-10:25\n"
            ."☕ Katta tanaffus: 10:25-10:40\n"
            ."4-dars: 10:40-11:25\n"
            ."5-dars: 11:30-12:15\n"
            ."6-dars: 12:20-13:05\n\n"
            ."🌙 **II-SMENA (13:10-18:05)**\n"
            ."1-dars: 13:10-13:55\n"
            ."2-dars: 14:00-14:45\n"
            ."3-dars: 14:50-15:35\n"
            ."4-dars: 15:40-16:25\n"
            ."5-dars: 16:30-17:15\n"
            ."6-dars: 17:20-18:05\n\n"
            ."━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
            ."⏰ Dars davomiyligi: 45 daqiqa\n"
            ."📝 Aniq jadval sinf rahbaridan olish mumkin.";
    }
}
