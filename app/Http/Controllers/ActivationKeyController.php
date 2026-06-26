<?php

namespace App\Http\Controllers;

use App\Models\ActivationKey;
use Illuminate\Http\Request;

class ActivationKeyController extends Controller
{
    public function showForm()
    {
        return view("donation.activate");
    }

    public function activate(Request $request)
    {
        $request->validate([
            "code" => "required|string|size:8",
        ]);

        $user = $request->user();
        $code = strtoupper(trim($request->input("code")));

        $key = ActivationKey::query()->where("code", $code)->first();

        if (!$key) {
            return back()->with("error", "Bunday kalit topilmadi. 4 marta notogri kod kiritilsa, 10 daqiqaga bloklanasiz.")
                ->with("toast_type", "error");
        }

        if ($key->is_used) {
            return back()->with("error", "Bu kalit avval ishlatilgan.")
                ->with("toast_type", "error");
        }

        if ($key->expires_at && $key->expires_at->isPast()) {
            return back()->with("error", "Bu kalitning muddati otgan.")
                ->with("toast_type", "error");
        }

        $success = $key->activate($user);

        if (!$success) {
            return back()->with("error", "Kalitni aktivlashtirishda xatolik.")
                ->with("toast_type", "error");
        }

        $rankLabel = ActivationKey::RANKS[$key->rank] ?? $key->rank;
        $durationLabel = ActivationKey::DURATIONS[$key->duration]["label"] ?? $key->duration;

        return redirect()->route("profile.show")
            ->with("success", "Tabriklaymiz! Siz {$rankLabel} rankini {$durationLabel} muddatga aktivlashtirdingiz!")
            ->with("toast_type", "success");
    }
}