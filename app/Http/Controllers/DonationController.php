<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function index()
    {
        $ranks = Donation::RANK_CONFIG();

        // Top donatchilar — completed donatlarning summasi bo'yicha
        // Activation key donatlari uchun (amount=0) haqiqiy narxni hisoblaymiz
        $topDonors = \App\Models\User::query()
            ->select([
                'id', 'name', 'first_name', 'last_name', 'avatar',
                'donation_rank', 'donation_rank_expires_at',
                'profile_theme', 'badge_style', 'show_expiry_badge', 'username_color',
            ])
            ->selectRaw('(SELECT COALESCE(SUM(amount), 0) FROM donations WHERE donations.user_id = users.id AND donations.status = ?) as calculated_donated', [Donation::STATUS_COMPLETED])
            ->selectRaw('(SELECT COUNT(*) FROM donations WHERE donations.user_id = users.id AND donations.status = ?) as donation_count', [Donation::STATUS_COMPLETED])
            ->whereNotNull("donation_rank")
            ->orderByRaw('(SELECT COALESCE(SUM(amount), 0) FROM donations WHERE donations.user_id = users.id AND donations.status = ?) DESC', [Donation::STATUS_COMPLETED])
            ->paginate(10);

        // Har bir donor uchun haqiqiy narxni hisoblaymiz (activation key uchun)
        $topDonors->getCollection()->transform(function ($donor) {
            $calculatedDonated = $donor->calculated_donated ?? 0;
            $donationCount = $donor->donation_count ?? 0;
            
            // Agar calculated_donated 0 bo'lsa (activation key), donatlardan haqiqiy narxni hisoblaymiz
            if ($calculatedDonated == 0 && $donationCount > 0) {
                $donations = \App\Models\Donation::where('user_id', $donor->id)
                    ->where('status', Donation::STATUS_COMPLETED)
                    ->get();
                
                $totalValue = 0;
                foreach ($donations as $donation) {
                    if ($donation->amount > 0) {
                        // Haqiqiy to'lov bo'lsa — amount ni qo'shamiz
                        $totalValue += $donation->amount;
                    } else {
                        // Activation key bo'lsa — muddatdan narxni hisoblaymiz
                        $duration = $this->getDonationDuration($donation);
                        $totalValue += Donation::priceForDuration($donation->rank, $duration);
                    }
                }
                $donor->calculated_donated = $totalValue;
            }
            
            return $donor;
        });

        return view("donation.index", [
            "ranks" => $ranks,
            "topDonors" => $topDonors,
        ]);
    }

    /**
     * Donation muddatini aniqlash (paid_at va expires_at orqali)
     */
    private function getDonationDuration(Donation $donation): string
    {
        if (!$donation->paid_at || !$donation->expires_at) {
            return '1month';
        }

        $days = $donation->paid_at->diffInDays($donation->expires_at);

        if ($days >= 350) {
            return '1year';
        } elseif ($days >= 60) {
            return '3months';
        }

        return '1month';
    }

    /**
     * Temalar showcase — barcha temalar jonli preview bilan.
     * Foydalanuvchilarni donor bo'lishga qiziqtirish uchun.
     */
    public function themesShowcase()
    {
        $themes = Donation::THEMES();
        $user = auth()->user();

        // Har bir tema uchun ruxsat holati
        $themeAllowed = [];
        if ($user) {
            foreach ($themes as $key => $cfg) {
                $themeAllowed[$key] = Donation::themeAllowedForUser($key, $user);
            }
        }

        return view("donation.themes-showcase", [
            "themes" => $themes,
            "themeAllowed" => $themeAllowed,
            "currentUser" => $user,
        ]);
    }

    public function showCheckout(string $rank, Request $request)
    {
        if (!in_array($rank, Donation::ALL_RANKS, true)) {
            return redirect()->route("donation.index")
                ->with("error", "Notogri rank tanlandi.")
                ->with("toast_type", "error");
        }

        $config = Donation::configForRank($rank);
        $duration = $request->query('duration', '1month');
        if (! array_key_exists($duration, Donation::DURATIONS())) {
            $duration = '1month';
        }

        $durationConfig = Donation::DURATIONS()[$duration];
        $totalPrice = Donation::priceForDuration($rank, $duration);

        return view("donation.checkout", [
            "rank" => $rank,
            "config" => $config,
            "duration" => $duration,
            "durationLabel" => $durationConfig['label'],
            "totalPrice" => $totalPrice,
        ]);
    }
}
