<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\OneTimeCode;
use App\Models\PostLike;
use App\Models\TeacherComment;
use App\Models\TeacherLike;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    private const OTP_VERIFY_MAX_ATTEMPTS = 5;

    private const OTP_VERIFY_DECAY_SECONDS = 600;

    private const OTP_RESEND_COOLDOWN_SECONDS = 60;

    public function show(Request $request)
    {
        $user = $request->user()->load('roleRelation');

        $postComments = Comment::query()
            ->where('user_id', $user->id)
            ->with(['post:id,title,slug'])
            ->latest()
            ->limit(40)
            ->get();

        $teacherComments = TeacherComment::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(40)
            ->get();

        $likedPosts = PostLike::query()
            ->where('user_id', $user->id)
            ->with(['post:id,title,slug'])
            ->latest()
            ->limit(40)
            ->get();

        $teacherLikes = TeacherLike::query()
            ->where('user_id', $user->id)
            ->with(['teacher:id,full_name,slug'])
            ->latest()
            ->limit(40)
            ->get();

        $createdCourses = Course::query()
            ->where('created_by', $user->id)
            ->with(['teacher:id,full_name'])
            ->latest()
            ->limit(20)
            ->get();

        $courseEnrollments = CourseEnrollment::query()
            ->where('user_id', $user->id)
            ->with(['course.teacher'])
            ->latest()
            ->limit(40)
            ->get();

        $canViewCourseEnrollments = Course::query()->where('created_by', $user->id)->exists();

        $pendingTeacherEnrollments = collect();
        if ($canViewCourseEnrollments) {
            $pendingTeacherEnrollments = CourseEnrollment::query()
                ->whereHas('course', fn ($q) => $q->where('created_by', $user->id))
                ->where('status', CourseEnrollment::STATUS_PENDING)
                ->with(['course.teacher', 'user'])
                ->latest()
                ->limit(8)
                ->get();
        }

        $pendingEmail = (string) $request->session()->get('profile_email_change_pending', '');

        return view('profile.show', compact(
            'user',
            'postComments',
            'teacherComments',
            'likedPosts',
            'teacherLikes',
            'createdCourses',
            'courseEnrollments',
            'canViewCourseEnrollments',
            'pendingTeacherEnrollments',
            'pendingEmail'
        ));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Profil maʼlumotlari yangilandi.')
            ->with('toast_type', 'success');
    }

    public function requestEmailChange(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $newEmail = strtolower(trim($validated['email']));
        if ($newEmail === strtolower((string) $user->email)) {
            return back()
                ->withErrors(['email' => 'Yangi email joriy manzildan farq qilishi kerak.'])
                ->withInput();
        }

        if (! $this->canSendEmailChangeOtp($newEmail)) {
            return back()
                ->withErrors([
                    'email' => "Kod yuborishdan oldin {$this->emailChangeResendSecondsLeft($newEmail)} soniya kuting.",
                ])
                ->withInput();
        }

        try {
            $this->issueEmailChangeOtp($newEmail, (int) $user->id);
        } catch (\Throwable $e) {
            Log::error('Profile email change OTP send failed', [
                'email' => $newEmail,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['email' => 'Yangi emailga kod yuborilmadi. Keyinroq urinib ko‘ring.'])
                ->withInput();
        }

        $request->session()->put('profile_email_change_pending', $newEmail);

        return redirect()
            ->route('profile.show')
            ->with('success', "Tasdiqlash kodi {$newEmail} manziliga yuborildi.")
            ->with('toast_type', 'success');
    }

    public function verifyEmailChange(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $pending = (string) $request->session()->get('profile_email_change_pending', '');
        if ($pending === '') {
            return redirect()
                ->route('profile.show')
                ->with('error', 'Avval yangi email kiriting va kod oling.')
                ->with('toast_type', 'error');
        }

        $user = $request->user();

        if (RateLimiter::tooManyAttempts($this->emailChangeVerifyKey($pending), self::OTP_VERIFY_MAX_ATTEMPTS)) {
            return back()->withErrors([
                'code' => "Juda ko'p xato urinish. {$this->emailChangeVerifySecondsLeft($pending)} soniyadan keyin qayta urinib ko'ring.",
            ]);
        }

        $otp = OneTimeCode::query()
            ->where('email', $pending)
            ->where('purpose', OneTimeCode::PURPOSE_EMAIL_CHANGE)
            ->latest('id')
            ->first();

        if (! $this->isValidOtp($otp, $validated['code'])) {
            RateLimiter::hit($this->emailChangeVerifyKey($pending), self::OTP_VERIFY_DECAY_SECONDS);

            return back()->withErrors(['code' => "Kod noto'g'ri yoki muddati tugagan."]);
        }

        $meta = $otp->meta ?? [];
        if ((int) ($meta['user_id'] ?? 0) !== (int) $user->id) {
            return redirect()
                ->route('profile.show')
                ->with('error', 'Tasdiqlash sessiyasi yaroqsiz. Qaytadan urinib ko‘ring.')
                ->with('toast_type', 'error');
        }

        if (User::query()->where('email', $pending)->where('id', '!=', $user->id)->exists()) {
            $request->session()->forget('profile_email_change_pending');
            $otp->delete();

            return redirect()
                ->route('profile.show')
                ->with('error', 'Bu email allaqachon boshqa hisobda ishlatilgan.')
                ->with('toast_type', 'error');
        }

        $user->update([
            'email' => $pending,
            'email_verified_at' => now(),
        ]);

        $otp->delete();
        RateLimiter::clear($this->emailChangeVerifyKey($pending));
        $request->session()->forget('profile_email_change_pending');

        return redirect()
            ->route('profile.show')
            ->with('success', 'Email manzili yangilandi.')
            ->with('toast_type', 'success');
    }

    public function resendEmailChange(Request $request)
    {
        $pending = (string) $request->session()->get('profile_email_change_pending', '');
        if ($pending === '') {
            return redirect()
                ->route('profile.show')
                ->with('error', 'Avval yangi email kiriting.')
                ->with('toast_type', 'error');
        }

        $user = $request->user();

        if (! $this->canSendEmailChangeOtp($pending)) {
            return back()->withErrors([
                'code' => "Qayta yuborishdan oldin {$this->emailChangeResendSecondsLeft($pending)} soniya kuting.",
            ]);
        }

        $latest = OneTimeCode::query()
            ->where('email', $pending)
            ->where('purpose', OneTimeCode::PURPOSE_EMAIL_CHANGE)
            ->latest('id')
            ->first();

        if (! $latest || (int) ($latest->meta['user_id'] ?? 0) !== (int) $user->id) {
            return redirect()
                ->route('profile.show')
                ->with('error', 'Kodni qayta yuborish mumkin emas. Emailni qayta kiriting.')
                ->with('toast_type', 'error');
        }

        try {
            $this->issueEmailChangeOtp($pending, (int) $user->id);
        } catch (\Throwable $e) {
            Log::error('Profile email change OTP resend failed', [
                'email' => $pending,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['code' => 'Kodni qayta yuborib bo‘lmadi.']);
        }

        return back()
            ->with('success', 'Yangi kod yuborildi.')
            ->with('toast_type', 'warning');
    }

    public function cancelEmailChange(Request $request)
    {
        $pending = (string) $request->session()->get('profile_email_change_pending', '');
        if ($pending !== '') {
            OneTimeCode::query()
                ->where('email', $pending)
                ->where('purpose', OneTimeCode::PURPOSE_EMAIL_CHANGE)
                ->delete();
        }

        $request->session()->forget('profile_email_change_pending');

        return redirect()
            ->route('profile.show')
            ->with('success', 'Email almashtirish bekor qilindi.')
            ->with('toast_type', 'warning');
    }

    private function issueEmailChangeOtp(string $email, int $userId): void
    {
        $code = (string) random_int(100000, 999999);

        OneTimeCode::query()
            ->where('email', $email)
            ->where('purpose', OneTimeCode::PURPOSE_EMAIL_CHANGE)
            ->delete();

        OneTimeCode::create([
            'email' => $email,
            'purpose' => OneTimeCode::PURPOSE_EMAIL_CHANGE,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
            'meta' => ['user_id' => $userId],
        ]);

        $subject = 'Email manzilini tasdiqlash';
        $title = 'Yangi emailga tasdiqlash';
        $html = '
            <div style="background:#f3f6fb;padding:24px 12px;font-family:Arial,sans-serif;">
              <div style="max-width:520px;margin:0 auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;">
                <div style="background:linear-gradient(135deg,#0ea5e9,#2563eb);padding:18px 20px;color:#fff;">
                  <h1 style="margin:0;font-size:20px;line-height:1.3;">81-maktab</h1>
                  <p style="margin:6px 0 0;font-size:13px;opacity:.95;">Email manzilini tasdiqlash</p>
                </div>
                <div style="padding:22px 20px;color:#111827;">
                  <h2 style="margin:0 0 10px;font-size:18px;">'.$title.'</h2>
                  <p style="margin:0 0 16px;color:#4b5563;font-size:14px;line-height:1.6;">
                    Profilga yangi email biriktirish uchun quyidagi 6 xonali kodni kiriting.
                  </p>
                  <div style="text-align:center;margin:18px 0 16px;">
                    <span style="display:inline-block;letter-spacing:6px;font-weight:700;font-size:30px;padding:12px 18px;border-radius:10px;background:#eef2ff;color:#1d4ed8;">'.$code.'</span>
                  </div>
                  <p style="margin:0;color:#dc2626;font-size:13px;font-weight:600;">Kod 10 daqiqa amal qiladi.</p>
                </div>
              </div>
            </div>
        ';

        Mail::html((string) $html, static function ($message) use ($email, $subject) {
            $message->to($email)->subject($subject);
        });

        RateLimiter::hit($this->emailChangeResendKey($email), self::OTP_RESEND_COOLDOWN_SECONDS);
    }

    private function isValidOtp(?OneTimeCode $otp, string $code): bool
    {
        if (! $otp) {
            return false;
        }

        if (! $otp->expires_at || now()->greaterThan($otp->expires_at)) {
            return false;
        }

        return Hash::check($code, $otp->code_hash);
    }

    private function canSendEmailChangeOtp(string $email): bool
    {
        return ! RateLimiter::tooManyAttempts($this->emailChangeResendKey($email), 1);
    }

    private function emailChangeResendSecondsLeft(string $email): int
    {
        return RateLimiter::availableIn($this->emailChangeResendKey($email));
    }

    private function emailChangeResendKey(string $email): string
    {
        return 'otp-send:'.OneTimeCode::PURPOSE_EMAIL_CHANGE.':'.strtolower($email);
    }

    private function emailChangeVerifyKey(string $email): string
    {
        return 'otp-verify:'.OneTimeCode::PURPOSE_EMAIL_CHANGE.':'.strtolower($email);
    }

    private function emailChangeVerifySecondsLeft(string $email): int
    {
        return RateLimiter::availableIn($this->emailChangeVerifyKey($email));
    }
}
