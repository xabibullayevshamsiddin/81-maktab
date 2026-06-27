<?php

use App\Http\Controllers\ActivationKeyController;
use App\Http\Controllers\AdminActivationKeyController;
use App\Http\Controllers\AdminDonationSettingsController;
use App\Http\Controllers\DonationController;
use Illuminate\Support\Facades\Route;

// Donation sahifalari
Route::get("donation", [DonationController::class, "index"])
    ->name("donation.index");

// Temalar showcase — barcha temalar jonli preview
Route::get("donation/temalar", [DonationController::class, "themesShowcase"])
    ->name("donation.themes");

Route::get("donation/{rank}/checkout", [DonationController::class, "showCheckout"])
    ->middleware(["auth", "active"])
    ->name("donation.checkout");

// Telegram orqali olingan kodni aktivlashtirish
Route::get("donation/activate", [ActivationKeyController::class, "showForm"])
    ->middleware(["auth", "active"])
    ->name("donation.activate.form");

Route::post("donation/activate", [ActivationKeyController::class, "activate"])
    ->middleware(["auth", "active", "throttle:4,10"])
    ->name("donation.activate");

// Admin: donation sozlamalari (narx va chegirmalar)
Route::middleware(["auth", "active", "role:super_admin"])->group(function () {
    Route::get("admin/donation-settings", [AdminDonationSettingsController::class, "index"])
        ->name("admin.donation-settings");
    Route::post("admin/donation-settings", [AdminDonationSettingsController::class, "update"])
        ->name("admin.donation-settings.update");
});

// Admin: aktivatsiya kalitlarini boshqarish
Route::middleware(["auth", "active", "role:super_admin,admin"])->group(function () {
    Route::get("admin/activation-keys", [AdminActivationKeyController::class, "index"])
        ->name("admin.activation-keys.index");
    Route::post("admin/activation-keys", [AdminActivationKeyController::class, "store"])
        ->name("admin.activation-keys.store");
    Route::delete("admin/activation-keys/{activationKey}", [AdminActivationKeyController::class, "destroy"])
        ->name("admin.activation-keys.destroy");
});