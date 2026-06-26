<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function index()
    {
        $ranks = Donation::RANK_CONFIG();

        $topDonors = \App\Models\User::query()
            ->where("total_donated", ">", 0)
            ->whereNotNull("donation_rank")
            ->orderByDesc("total_donated")
            ->take(10)
            ->get();

        return view("donation.index", [
            "ranks" => $ranks,
            "topDonors" => $topDonors,
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