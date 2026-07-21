<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
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
            SiteSetting::query()->updateOrCreate(
                ["key" => $key],
                ["value" => (string) $value]
            );
        }

        return redirect()->route("admin.donation-settings")
            ->with("success", "Narxlar va chegirmalar saqlandi!")
            ->with("toast_type", "success");
    }
}