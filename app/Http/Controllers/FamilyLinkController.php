<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FamilyLinkController extends Controller
{
    /**
     * Ota-ona uchun bog'lanish kodi generatsiya qilish
     */
    public function generateCode(Request $request)
    {
        $user = $request->user();
        abort_unless($user->is_parent, 403);

        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('family_link_code', $code)->exists());

        $user->update(['family_link_code' => $code]);

        return back()->with('success', 'Yangi kod yaratildi: ' . $code);
    }

    /**
     * O'quvchi akkaunti ota-ona kodi orqali bog'lash
     */
    public function linkByCode(Request $request)
    {
        $user = $request->user();

        // Faqat haqiqiy o'quvchi akkaunti kod kirita oladi
        if ($user->is_parent || ! $user->grade) {
            return back()->with('error', 'Faqat o\'quvchi akkaunti ota-ona kodini kirita oladi.');
        }

        $validated = $request->validate(['code' => ['required', 'string', 'size:8']]);

        $parent = User::where('family_link_code', strtoupper($validated['code']))
            ->where('is_parent', true)
            ->first();

        if (! $parent) {
            return back()->with('error', 'Kod topilmadi. To\'g\'ri kiritganingizni tekshiring.');
        }

        if ($parent->linkedStudents()->where('student_user_id', $user->id)->exists()) {
            return back()->with('error', 'Siz allaqachon shu ota-ona bilan bog\'langansiz.');
        }

        if ($parent->linkedStudents()->count() >= $parent->familyLinkLimit()) {
            return back()->with('error', 'Bu ota-ona akkaunti farzandlar limitiga yetgan.');
        }

        $parent->linkedStudents()->attach($user->id, ['linked_at' => now()]);

        // Kodni o'chirish — qayta ishlatilmasligi uchun
        $parent->update(['family_link_code' => null]);

        // O'quvchiga ham xabar
        if ($user->telegram_chat_id) {
            app(\App\Services\TelegramService::class)->sendMessage(
                (int) $user->telegram_chat_id,
                "👨‍👩‍👧 <b>{$parent->name}</b> sizning ota-onangiz sifatida akkauntingizga ulandi."
            );
        }

        return back()->with('success', "Muvaffaqiyatli bog'landingiz: {$parent->name}");
    }

    /**
     * Bog'lanishni uzish (ikkala tomon ham)
     */
    public function unlink(Request $request, int $linkUserId)
    {
        $user = $request->user();

        if ($user->is_parent) {
            $user->linkedStudents()->detach($linkUserId);
        } else {
            $user->linkedParents()->detach($linkUserId);
        }

        return back()->with('success', 'Bog\'lanish uzildi.');
    }
}
