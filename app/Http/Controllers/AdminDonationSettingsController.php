<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;

class AdminDonationSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            "donation_supporter_price" => SiteSetting::get("donation_supporter_price", "15000"),
            "donation_premium_price" => SiteSetting::get("donation_premium_price", "35000"),
            "donation_vip_price" => SiteSetting::get("donation_vip_price", "75000"),
            "donation_premium_discount_3months" => SiteSetting::get("donation_premium_discount_3months", "10"),
            "donation_premium_discount_1year" => SiteSetting::get("donation_premium_discount_1year", "20"),
            "donation_supporter_discount_3months" => SiteSetting::get("donation_supporter_discount_3months", "0"),
            "donation_supporter_discount_1year" => SiteSetting::get("donation_supporter_discount_1year", "0"),
            "donation_vip_discount_3months" => SiteSetting::get("donation_vip_discount_3months", "0"),
            "donation_vip_discount_1year" => SiteSetting::get("donation_vip_discount_1year", "0"),
        ];

        return view("admin.donation-settings", compact("settings"));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            "donation_supporter_price" => "required|integer|min:1000|max:1000000",
            "donation_premium_price" => "required|integer|min:1000|max:1000000",
            "donation_vip_price" => "required|integer|min:1000|max:1000000",
            "donation_premium_discount_3months" => "required|integer|min:0|max:50",
            "donation_premium_discount_1year" => "required|integer|min:0|max:50",
            "donation_supporter_discount_3months" => "required|integer|min:0|max:50",
            "donation_supporter_discount_1year" => "required|integer|min:0|max:50",
            "donation_vip_discount_3months" => "required|integer|min:0|max:50",
            "donation_vip_discount_1year" => "required|integer|min:0|max:50",
        ]);

        foreach ($data as $key => $value) {
            SiteSetting::set($key, (string) $value);
        }

        return redirect()->route("admin.donation-settings")
            ->with("success", "Narxlar va chegirmalar saqlandi!")
            ->with("toast_type", "success");
    }

    public function donors()
    {
        $donors = User::whereNotNull('donation_rank')
            ->with(['donations' => function ($q) {
                $q->where('status', 'completed')->latest('paid_at')->limit(1);
            }])
            ->orderByDesc('donation_rank_expires_at')
            ->paginate(30);

        return view('admin.donors', compact('donors'));
    }

    public function revoke(User $user)
    {
        if (! $user->donation_rank) {
            return back()->with('error', 'Bu foydalanuvchida faol donor holati yo\'q.');
        }

        $oldRank = $user->donation_rank;
        $oldExpiresAt = $user->donation_rank_expires_at;

        // Direct property assignment (donation_rank removed from $fillable for security)
        $user->donation_rank = null;
        $user->donation_rank_expires_at = null;
        $user->save();

        UserActivity::create([
            'user_id' => $user->id,
            'type' => UserActivity::TYPE_DONATION_REVOKED,
            'description' => "Admin tomonidan donor holati bekor qilindi (avvalgi: {$oldRank})",
            'old_value' => ['rank' => $oldRank, 'expires_at' => $oldExpiresAt?->toIso8601String()],
            'new_value' => null,
            'occurred_at' => now(),
        ]);

        // Telegram xabar yuborish
        if ($user->telegram_chat_id) {
            try {
                $config = Donation::configForRank($oldRank);
                $label = $config["label"] ?? ucfirst($oldRank);
                $userName = htmlspecialchars($user->buildNameFromParts() ?: $user->name);
                $text = "⚠️ <b>Donor holati bekor qilindi</b>\n"
                    ."━━━━━━━━━━━━━━━━━━━━\n\n"
                    ."Salom, <b>{$userName}</b>!\n\n"
                    ."Sizning <b>{$label}</b> obunangiz admin tomonidan bekor qilindi.\n\n"
                    ."🔐 Imtiyozlar cheklandi.\n"
                    ."💬 Savollar bo'lsa, admin bilan bog'laning.\n\n"
                    ."━━━━━━━━━━━━━━━━━━━━";
                $telegram = app(\App\Services\TelegramService::class);
                $telegram->sendMessage((int) $user->telegram_chat_id, $text);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return back()->with('success', "{$user->name} uchun donor holati bekor qilindi.");
    }
}