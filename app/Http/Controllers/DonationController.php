<?php

namespace App\Http\Controllers;

use App\Models\Donation;

class DonationController extends Controller
{
    public function index()
    {
        $ranks = Donation::RANK_CONFIG();

        // Top donatchilar — completed donatlarning summasi bo'yicha
        $topDonors = \App\Models\User::query()
            ->select([
                'id', 'name', 'first_name', 'last_name', 'avatar',
                'donation_rank', 'donation_rank_expires_at',
                'profile_theme', 'badge_style', 'show_expiry_badge', 'username_color',
            ])
            ->selectRaw('(SELECT COALESCE(SUM(amount), 0) FROM donations WHERE donations.user_id = users.id AND donations.status = ?) as calculated_donated', [Donation::STATUS_COMPLETED])
            ->selectRaw('(SELECT COUNT(*) FROM donations WHERE donations.user_id = users.id AND donations.status = ?) as donation_count', [Donation::STATUS_COMPLETED])
            ->whereRaw('(SELECT SUM(amount) FROM donations WHERE donations.user_id = users.id AND donations.status = ?) > 0', [Donation::STATUS_COMPLETED])
            ->whereNotNull("donation_rank")
            ->orderByRaw('(SELECT SUM(amount) FROM donations WHERE donations.user_id = users.id AND donations.status = ?) DESC', [Donation::STATUS_COMPLETED])
            ->paginate(10);

        return view("donation.index", [
            "ranks" => $ranks,
            "topDonors" => $topDonors,
        ]);
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

    public function showCheckout(string $rank)
    {
        if (!in_array($rank, Donation::ALL_RANKS, true)) {
            return redirect()->route("donation.index")
                ->with("error", "Notogri rank tanlandi.")
                ->with("toast_type", "error");
        }

        $config = Donation::configForRank($rank);

        return view("donation.checkout", [
            "rank" => $rank,
            "config" => $config,
        ]);
    }
}
