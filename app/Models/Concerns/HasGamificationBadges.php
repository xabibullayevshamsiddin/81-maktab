<?php

namespace App\Models\Concerns;

use App\Models\Result;

/**
 * Foydalanuvchi uchun Gamification badjlarini hisoblaydi.
 * Faqat is_rated=true bo'lgan imtihonlar hisobga olinadi.
 */
trait HasGamificationBadges
{
    /**
     * Foydalanuvchining reyting imtihonlardagi natijalarini qaytaradi.
     * (faqat submitted yoki expired)
     */
    public function ratedResults(): \Illuminate\Database\Eloquent\Relations\HasManyThrough|\Illuminate\Support\Collection
    {
        return Result::query()
            ->where('user_id', $this->id)
            ->whereIn('status', ['submitted', 'expired'])
            ->whereHas('exam', fn ($q) => $q->where('is_rated', true))
            ->with('exam:id,total_points,is_rated')
            ->get();
    }

    /**
     * Barcha aktiv gamification badjlarini qaytaradi.
     *
     * @return array<int, array{key: string, label: string, icon: string, color: string, description: string}>
     */
    public function gamificationBadges(): array
    {
        $badges = [];
        $ratedResults = $this->ratedResults();

        if ($ratedResults->isEmpty()) {
            return $badges;
        }

        $totalRatedCount = $ratedResults->count();

        // Har bir natija uchun foizni hisoblaymiz
        $percentages = $ratedResults->map(function ($result) {
            $maxPoints = (int) ($result->exam?->total_points ?? $result->points_max ?? 0);
            if ($maxPoints <= 0) {
                return 0;
            }
            return round(($result->points_earned / $maxPoints) * 100, 2);
        });

        $above90 = $percentages->filter(fn ($p) => $p >= 90)->count();
        $perfect  = $percentages->filter(fn ($p) => $p >= 100)->count();

        // 🎯 Mukammal Natija — reyting imtihonida 100% ball
        if ($perfect >= 1) {
            $badges[] = [
                'key'         => 'perfect',
                'label'       => 'Mukammal!',
                'icon'        => 'fa-solid fa-bullseye',
                'color'       => '#ef4444',
                'description' => "Kamida 1 ta reyting imtihonida 100% ball oldi",
            ];
        }

        // 🥇 A'lochi — kamida 1 ta reyting imtihonida 90%+ ball
        if ($above90 >= 1) {
            $badges[] = [
                'key'         => 'top_student',
                'label'       => "A'lochi",
                'icon'        => 'fa-solid fa-medal',
                'color'       => '#f59e0b',
                'description' => "Kamida 1 ta reyting imtihonida 90%+ ball oldi",
            ];
        }

        // 🏆 Super A'lochi — kamida 5 ta reyting imtihonida 90%+ ball
        if ($above90 >= 5) {
            $badges[] = [
                'key'         => 'super_student',
                'label'       => "Super A'lochi",
                'icon'        => 'fa-solid fa-trophy',
                'color'       => '#8b5cf6',
                'description' => "5 ta va undan ko'p reyting imtihonida 90%+ ball",
            ];
        }

        // 🔥 Eng faol — 10 ta va undan ko'p reyting imtihon topshirgan
        if ($totalRatedCount >= 10) {
            $badges[] = [
                'key'         => 'most_active',
                'label'       => 'Eng Faol',
                'icon'        => 'fa-solid fa-fire',
                'color'       => '#f97316',
                'description' => "{$totalRatedCount} ta reyting imtihonini topshirdi",
            ];
        }

        // ⭐ Ishtirokchi — birinchi reyting imtihonini topshirgan
        if ($totalRatedCount >= 1) {
            $badges[] = [
                'key'         => 'participant',
                'label'       => 'Ishtirokchi',
                'icon'        => 'fa-solid fa-star',
                'color'       => '#3b82f6',
                'description' => "Birinchi reyting imtihonini topshirdi",
            ];
        }

        return $badges;
    }

    /**
     * Badjlarni HTML ko'rinishida qaytaradi (chip shaklida).
     */
    public function gamificationBadgesHtml(): string
    {
        $badges = $this->gamificationBadges();
        if (empty($badges)) {
            return '';
        }

        $html = '';
        foreach ($badges as $badge) {
            $color = htmlspecialchars($badge['color']);
            $icon  = htmlspecialchars($badge['icon']);
            $label = htmlspecialchars($badge['label']);
            $desc  = htmlspecialchars($badge['description']);
            $html .= <<<HTML
<span class="gamification-badge" title="{$desc}" style="--badge-color:{$color};">
  <i class="{$icon}"></i>
  {$label}
</span>
HTML;
        }
        return $html;
    }
}
