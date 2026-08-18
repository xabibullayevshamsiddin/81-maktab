<?php

namespace App\Services\Ai\Concerns;

use App\Models\Teacher;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Fan bo'yicha ustoz qidirish uchun match metodlari.
 * AiService dan ajratilgan trait.
 */
trait SubjectMatch
{
    /**
     * Fan bo'yicha ustoz qidirish: "Matematikadan kim dars beradi?", "Fizika o'qituvchisi kim?"
     */
    private function matchSubjectTeacherQuery(string $message): ?string
    {
        $q = $this->normalizeSearchText($message);

        $hasSubjectIntent = Str::contains($q, [
            'fandan', 'fani', 'fan o\'qituvchi', 'dars ber', 'dars ol', 'o\'qitadi',
            'kim o\'qit', 'kim dars', 'qaysi ustoz', 'o\'qituvchisi',
            'matematik', 'fizika', 'kimyo', 'biologiya', 'tarix', 'geografiya',
            'ingliz', 'rus tili', 'onatili', 'adabiyot', 'jismoniy', 'musiqa',
            'tasviriy', 'informatsiya', 'texnologiya', 'psixologiya',
        ]);

        if (! $hasSubjectIntent) {
            return null;
        }

        if (! Schema::hasTable('teachers')) {
            return null;
        }

        $subjects = [
            'matematik' => ['matematika', 'matematik', 'algebra', 'geometriya'],
            'fizika' => ['fizika', 'fizik'],
            'kimyo' => ['kimyo', 'kimyo fan'],
            'biologiya' => ['biologiya', 'biologik'],
            'tarix' => ['tarix', 'tarixchi'],
            'geografiya' => ['geografiya', 'geografik'],
            'ingliz' => ['ingliz', 'ingliz tili', 'english'],
            'rus tili' => ['rus tili', 'ruscha'],
            'onatili' => ['ona tili', 'onatili', 'o\'zbek tili', 'o\'zbekcha'],
            'adabiyot' => ['adabiyot', 'adabiy'],
            'jismoniy' => ['jismoniy tarbiya', 'sport', 'futbol'],
            'musiqa' => ['musiqa', 'musiqa fani'],
            'tasviriy' => ['tasviriy', 'rasm', 'chizmachilik'],
            'informatsiya' => ['informatsiya', 'ikt', 'dasturlash', 'kompyuter'],
            'psixologiya' => ['psixologiya', 'psixolog'],
        ];

        $matchedSubject = null;
        foreach ($subjects as $subject => $keywords) {
            foreach ($keywords as $kw) {
                if (Str::contains($q, $kw)) {
                    $matchedSubject = $subject;
                    break 2;
                }
            }
        }

        if ($matchedSubject === null) {
            return null;
        }

        $subjectKeywords = $subjects[$matchedSubject];

        $teachers = Teacher::query()
            ->where('is_active', true)
            ->where(function ($query) use ($subjectKeywords) {
                foreach ($subjectKeywords as $kw) {
                    $query->orWhere('subject', 'like', "%{$kw}%");
                }
            })
            ->select(['full_name', 'subject', 'experience_years', 'lavozim'])
            ->orderByDesc('experience_years')
            ->get();

        if ($teachers->isEmpty()) {
            return "**{$matchedSubject}** fani bo'yicha hozircha faol ustoz topilmadi.\n"
                ."Barcha ustozlar: ".route('teacher');
        }

        $lines = $teachers->map(function ($t) {
            $staj = (int) ($t->experience_years ?? 0);
            $detail = $staj > 0 ? " ({$staj} yil staj)" : '';
            $lavozim = trim((string) $t->lavozim);
            $lavozimText = $lavozim !== '' ? " — {$lavozim}" : '';

            return "👨‍🏫 **{$t->full_name}**{$detail}{$lavozimText}";
        })->implode("\n");

        return "📖 **{$matchedSubject}** fani bo'yicha ustozlar:\n\n{$lines}\n\n"
            ."👨‍🏫 Barcha ustozlar: ".route('teacher');
    }

    /**
     * Sinf rahbari haqida savol
     */
    private function matchClassTeacherQuery(string $message): ?string
    {
        $q = $this->normalizeSearchText($message);

        $hasClassTeacherIntent = Str::contains($q, [
            'sinf rahbari', 'sinf raxbari', 'rahbar kim', 'sinf mudiri',
            'sinf oqituvchisi', 'sinfdagi rahbar', 'klass rahbari',
        ]);

        if (! $hasClassTeacherIntent) {
            return null;
        }

        if (! Schema::hasTable('teachers')) {
            return null;
        }

        $classTeachers = Teacher::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->orWhere('lavozim', 'like', '%sinf rahbari%')
                    ->orWhere('lavozim', 'like', '%sinf raxbari%')
                    ->orWhere('lavozim', 'like', '%rahbar%')
                    ->orWhere('lavozim', 'like', '%sinf mudiri%');
            })
            ->select(['full_name', 'lavozim', 'subject'])
            ->get();

        if ($classTeachers->isEmpty()) {
            return "Hozircha sinf rahbarlari haqida ma'lumot kiritilmagan.\n"
                ."Barcha ustozlar: ".route('teacher');
        }

        $lines = $classTeachers->map(function ($t) {
            $fan = trim((string) $t->subject);
            $lavozim = trim((string) $t->lavozim);
            $detail = $fan !== '' ? " ({$fan})" : '';

            return "👤 **{$t->full_name}**{$detail}\n   💼 {$lavozim}";
        })->implode("\n\n");

        return "📋 **SINF RAHBARLARI:**\n\n{$lines}\n\n"
            ."👨‍🏫 Barcha ustozlar: ".route('teacher');
    }
}
