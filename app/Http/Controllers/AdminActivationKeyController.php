<?php

namespace App\Http\Controllers;

use App\Models\ActivationKey;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminActivationKeyController extends Controller
{
    public function index()
    {
        $keys = ActivationKey::query()
            ->with(["generator:id,name", "user:id,name"])
            ->latest()
            ->paginate(50);

        $stats = [
            "total" => ActivationKey::query()->count(),
            "used" => ActivationKey::query()->where("is_used", true)->count(),
            "available" => ActivationKey::query()->where("is_used", false)->count(),
        ];

        return view("admin.activation-keys.index", [
            "keys" => $keys,
            "stats" => $stats,
            "ranks" => ActivationKey::RANKS,
            "durations" => $this->getDurations(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            "rank" => "required|in:supporter,premium,vip",
            "duration" => "required|in:1month,3months,1year",
            "count" => "required|integer|min:1|max:50",
        ]);

        $rank = $request->input("rank");
        $duration = $request->input("duration");
        $count = (int) $request->input("count");
        $durations = $this->getDurations();
        $days = $durations[$duration]["days"];
        $generated = [];

        for ($i = 0; $i < $count; $i++) {
            $code = ActivationKey::generateCode();

            $key = ActivationKey::query()->create([
                "code" => $code,
                "rank" => $rank,
                "duration" => $duration,
                "duration_days" => $days,
                "generated_by" => $request->user()->id,
                "expires_at" => now()->addYear(),
            ]);

            $generated[] = $key;
        }

        Log::info("Activation keys generated", [
            "by" => $request->user()->id,
            "rank" => $rank,
            "duration" => $duration,
            "count" => $count,
        ]);

        $codes = array_map(fn($k) => $k->code, $generated);

        return back()->with("generated_codes", $codes)
            ->with("success", "{$count} ta kalit yaratildi!")
            ->with("toast_type", "success");
    }

    public function destroy(ActivationKey $activationKey)
    {
        if ($activationKey->is_used) {
            return back()->with("error", "Ishlatilgan kalitni ochira olmaysiz.")
                ->with("toast_type", "error");
        }

        $activationKey->delete();

        return back()->with("success", "Kalit ochirildi.")
            ->with("toast_type", "success");
    }

    private function getDurations(): array
    {
        return Donation::DURATIONS();
    }
}