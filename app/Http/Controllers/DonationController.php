<?php

namespace App\Http\Controllers;

use App\Models\Donation;

class DonationController extends Controller
{
    public function index()
    {
        $ranks = Donation::RANK_CONFIG();

        $topDonors = \App\Models\User::query()
            ->where("total_donated", ">", 0)
            ->whereNotNull("donation_rank")
            ->orderByDesc("total_donated")
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
