<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Exam;
use App\Models\OneTimeCode;
use App\Models\Result;
use App\Models\TeacherComment;
use App\Models\User;
use App\Models\UserActivity;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    private const OTP_VERIFY_MAX_ATTEMPTS = 5;

    private const OTP_VERIFY_DECAY_SECONDS = 600;

    private const OTP_RESEND_COOLDOWN_SECONDS = 60;

    private const PASSWORD_CHANGE_MAX_ATTEMPTS = 5;

    private const PASSWORD_CHANGE_DECAY_SECONDS = 600;

    private const PASSWORD_CHANGE_CONFIRM_TTL_SECONDS = 600;

    public function show(Request $request)
    {
        $user = $request->user()->load('roleRelation');
        $panel = strtolower((string) $request->query('panel', 'settings'));

        if (! in_array($panel, ['settings', 'security', 'activity', 'appearance'], true)) {
            $panel = 'settings';
        }

        $postCommentCount = Comment::query()
            ->where('user_id', $user->id)
            ->count();

        $teacherCommentCount = TeacherComment::query()
            ->where('user_id', $user->id)
            ->count();

        $createdCourseCount = Course::query()
            ->where('created_by', $user->id)
            ->count();

        $courseEnrollmentCount = CourseEnrollment::query()
            ->where('user_id', $user->id)
            ->count();

        $canViewCourseEnrollments = Course::query()->where('created_by', $user->id)->exists();
        $pendingTeacherEnrollmentCount = $canViewCourseEnrollments
            ? CourseEnrollment::query()
                ->whereHas('course', fn ($query) => $query->where('created_by', $user->id))
                ->where('status', CourseEnrollment::STATUS_PENDING)
                ->count()
            : 0;

        $createdExamsCount = $user->canManageExams()
            ? Exam::query()->where('created_by', $user->id)->count()
            : 0;

        $postComments = collect();
        $teacherComments = collect();
        $createdCourses = collect();
        $courseEnrollments = collect();
        $pendingTeacherEnrollments = collect();
        $createdExams = collect();

        if ($panel === 'activity') {
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

            $createdCourses = Course::query()
                ->where('created_by', $user->id)
                ->with([
                    'teacher:id,full_name,subject,subject_en,image,is_active',
                    'creator:id,name,first_name,last_name,avatar,role_id,grade,is_parent',
                    'creator.roleRelation:id,name,label,level',
                ])
                ->latest()
                ->limit(20)
                ->get();

            $courseEnrollments = CourseEnrollment::query()
                ->where('user_id', $user->id)
                ->with([
                    'course.teacher:id,full_name,subject,subject_en,image,is_active',
                    'course.creator:id,name,first_name,last_name,avatar,role_id,grade,is_parent',
                    'course.creator.roleRelation:id,name,label,level',
                ])
                ->latest()
                ->limit(40)
                ->get();

            if ($canViewCourseEnrollments) {
                $pendingTeacherEnrollments = CourseEnrollment::query()
                    ->whereHas('course', fn ($query) => $query->where('created_by', $user->id))
                    ->where('status', CourseEnrollment::STATUS_PENDING)
                    ->with([
                        'course.teacher:id,full_name,subject,subject_en,image,is_active',
                        'course.creator:id,name,first_name,last_name,avatar,role_id,grade,is_parent',
                        'course.creator.roleRelation:id,name,label,level',
                        'user',
                    ])
                    ->latest()
                    ->limit(8)
                    ->get();
            }
        }

        if ($panel === 'activity' && $user->canManageExams()) {
            $createdExams = Exam::query()
                ->where('created_by', $user->id)
                ->withCount('questions')
                ->latest()
                ->limit(20)
                ->get();
        }

        $examResultsCount = $this->userResultsBaseQuery($user)->count();

        $activities = UserActivity::query()
            ->where('user_id', $user->id)
            ->latest('occurred_at')
            ->limit(30)
            ->get();

        $pendingEmail = (string) $request->session()->get('profile_email_change_pending', '');
        $passwordChangeUnlocked = $this->hasConfirmedPasswordChange($request, (int) $user->id);

        return view('profile.show', compact(
            'user',
            'postComments',
            'teacherComments',
            'createdCourses',
            'createdExams',
            'courseEnrollments',
            'canViewCourseEnrollments',
            'pendingTeacherEnrollments',
            'postCommentCount',
            'teacherCommentCount',
            'createdCourseCount',
            'courseEnrollmentCount',
            'pendingTeacherEnrollmentCount',
            'createdExamsCount',
            'examResultsCount',
            'pendingEmail',
            'passwordChangeUnlocked',
            'panel',
            'activities'
        ));
    }

    public function resultsIndex(Request $request)
    {
        $user = $request->user();

        $resultSummary = $this->userResultsBaseQuery($user)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN passed = true THEN 1 ELSE 0 END) as passed_count')
            ->selectRaw('SUM(CASE WHEN passed = false THEN 1 ELSE 0 END) as failed_count')
            ->selectRaw('AVG(points_earned) as average_points')
            ->selectRaw('MAX(points_earned) as best_points')
            ->first();

        $results = $this->userResultsBaseQuery($user)
            ->with('exam:id,title,total_points,passing_points')
            ->latest('submitted_at')
            ->paginate(12);

        return view('profile.results.index', compact('results', 'resultSummary'));
    }

    public function update(Request $request, ImageService $imageService)
    {
        $user = $request->user();

        $nameMsg = User::nameValidationMessage();

        $validated = $request->validate([
            'first_name' => User::nameValidationRules(),
            'last_name' => User::nameValidationRules(),
            'phone' => uz_phone_rules(false),
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . $request->user()->donorMaxAvatarSize()],
            'remove_avatar' => ['nullable', 'boolean'],
        ], [
            'phone.regex' => uz_phone_validation_message(),
                        'first_name.required' => 'Ism kiritilishi shart.',
            'first_name.regex' => $nameMsg,
            'last_name.required' => 'Familiya kiritilishi shart.',
            'last_name.regex' => $nameMsg,
'avatar.image' => 'Profil rasmi rasm bo‘lishi kerak.',
            'avatar.mimes' => 'Profil rasmi JPG, PNG yoki WebP formatda bo‘lishi kerak.',
            'avatar.max' => 'Rasm hajmi ' . round($request->user()->donorMaxAvatarSize() / 1024) . ' MB dan oshmasin. Donor bo‘lish orqali kattaroq rasm yuklashingiz mumkin.',
        ]);
        $validated['phone'] = uz_phone_format($validated['phone'] ?? null);

        $payload = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
            'phone' => $validated['phone'] ?? null,
        ];

        $previousAvatar = $user->avatar;

        if ($request->hasFile('avatar')) {
            try {
                $payload['avatar'] = $imageService->storeSquareWebp(
                    $request->file('avatar'),
                    'avatars',
                    320,
                    82
                );
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([
                    'avatar' => 'Profil rasmini tayyorlab bo‘lmadi. Boshqa rasm bilan qayta urinib ko‘ring.',
                ]);
            }
        } elseif ($request->boolean('remove_avatar')) {
            $payload['avatar'] = null;
        }

        $avatarChanged = array_key_exists('avatar', $payload) && ! empty($previousAvatar) && $previousAvatar !== $payload['avatar'];

        $user->update($payload);

        \App\Services\UserActivityLogger::log(
            $user,
            \App\Models\UserActivity::TYPE_PROFILE_UPDATED,
            'Profil ma\'lumotlari yangilandi',
            ['fields' => array_keys($payload)],
            ['fields' => $payload]
        );

        if ($avatarChanged) {
            \App\Services\UserActivityLogger::log(
                $user,
                \App\Models\UserActivity::TYPE_AVATAR_CHANGED,
                $payload['avatar'] ? 'Profil rasmi yangilandi' : 'Profil rasmi olib tashlandi',
                ['avatar' => $previousAvatar],
                ['avatar' => $payload['avatar']]
            );
        }

        if ($avatarChanged && ! empty($previousAvatar) && $previousAvatar !== $payload['avatar']) {
            $imageService->deleteImage($previousAvatar);
        }

        return redirect()
            ->route('profile.show', ['panel' => 'settings'])
            ->with('success', 'Profil maʼlumotlari yangilandi.')
            ->with('toast_type', 'success');
    }

    public function confirmPasswordChange(Request $request)
    {
        $user = $request->user();
        $limiterKey = $this->passwordChangeKey($request, (int) $user->id);

        if (RateLimiter::tooManyAttempts($limiterKey, self::PASSWORD_CHANGE_MAX_ATTEMPTS)) {
            if ($this->wantsJson($request)) {
                return $this->sectionErrorResponse('password', "Juda ko'p xato urinish. {$this->passwordChangeSecondsLeft($request, (int) $user->id)} soniyadan keyin qayta urinib ko'ring.", [
                    'current_password' => ["Juda ko'p xato urinish. {$this->passwordChangeSecondsLeft($request, (int) $user->id)} soniyadan keyin qayta urinib ko'ring."],
                ]);
            }

            return back()->withErrors([
                'current_password' => "Juda ko'p xato urinish. {$this->passwordChangeSecondsLeft($request, (int) $user->id)} soniyadan keyin qayta urinib ko'ring.",
            ]);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
        ], [
            'current_password.required' => 'Joriy parolni kiriting.',
        ]);

        if (! Hash::check($validated['current_password'], (string) $user->password)) {
            RateLimiter::hit($limiterKey, self::PASSWORD_CHANGE_DECAY_SECONDS);

            if ($this->wantsJson($request)) {
                return $this->sectionErrorResponse('password', 'Joriy parol noto\'g\'ri.', [
                    'current_password' => ['Joriy parol noto\'g\'ri.'],
                ]);
            }

            return back()->withErrors([
                'current_password' => 'Joriy parol noto\'g\'ri.',
            ]);
        }

        RateLimiter::clear($limiterKey);
        $this->storePasswordChangeConfirmation($request, (int) $user->id);

        if ($this->wantsJson($request)) {
            return $this->sectionSuccessResponse($request, 'password', 'Joriy parol tasdiqlandi. Endi yangi parolni kiriting.');
        }

        return redirect()
            ->route('profile.show', ['panel' => 'security'])
            ->with('success', 'Joriy parol tasdiqlandi. Endi yangi parolni kiriting.')
            ->with('toast_type', 'success');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        if (! $this->hasConfirmedPasswordChange($request, (int) $user->id)) {
            if ($this->wantsJson($request)) {
                return $this->sectionErrorResponse('password', 'Avval joriy parolni tasdiqlang.', [
                    'current_password' => ['Avval joriy parolni tasdiqlang.'],
                ]);
            }

            return redirect()
                ->route('profile.show', ['panel' => 'security'])
                ->withErrors([
                    'current_password' => 'Avval joriy parolni tasdiqlang.',
                ]);
        }

        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'password.required' => 'Yangi parolni kiriting.',
            'password.min' => 'Yangi parol kamida 8 belgidan iborat bo\'lishi kerak.',
            'password.confirmed' => 'Yangi parol tasdiqlanmadi.',
        ]);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        \App\Services\UserActivityLogger::log(
            $user,
            \App\Models\UserActivity::TYPE_PASSWORD_CHANGED,
            'Parol o\'zgartirildi'
        );

        $this->clearPasswordChangeConfirmation($request);
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        if ($this->wantsJson($request)) {
            return $this->sectionSuccessResponse($request, 'password', 'Parol muvaffaqiyatli yangilandi.');
        }

        return redirect()
            ->route('profile.show', ['panel' => 'security'])
            ->with('success', 'Parol muvaffaqiyatli yangilandi.')
            ->with('toast_type', 'success');
    }

    public function requestEmailChange(Request $request)
    {
        $user = $request->user();

        if (! $this->mailDeliveryEnabled()) {
            if ($this->wantsJson($request)) {
                return $this->sectionErrorResponse('email', $this->mailDeliveryDisabledMessage(), [
                    'email' => [$this->mailDeliveryDisabledMessage()],
                ]);
            }

            return back()
                ->withErrors(['email' => $this->mailDeliveryDisabledMessage()])
                ->withInput();
        }

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
            if ($this->wantsJson($request)) {
                return $this->sectionErrorResponse('email', 'Yangi email joriy manzildan farq qilishi kerak.', [
                    'email' => ['Yangi email joriy manzildan farq qilishi kerak.'],
                ]);
            }

            return back()
                ->withErrors(['email' => 'Yangi email joriy manzildan farq qilishi kerak.'])
                ->withInput();
        }

        if (! $this->canSendEmailChangeOtp($newEmail)) {
            if ($this->wantsJson($request)) {
                return $this->sectionErrorResponse('email', "Kod yuborishdan oldin {$this->emailChangeResendSecondsLeft($newEmail)} soniya kuting.", [
                    'email' => ["Kod yuborishdan oldin {$this->emailChangeResendSecondsLeft($newEmail)} soniya kuting."],
                ]);
            }

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
            if ($this->wantsJson($request)) {
                return $this->sectionErrorResponse('email', 'Yangi emailga kod yuborilmadi. Keyinroq urinib ko\'ring.', [
                    'email' => ['Yangi emailga kod yuborilmadi. Keyinroq urinib ko\'ring.'],
                ], 500);
            }

            return back()
                ->withErrors(['email' => 'Yangi emailga kod yuborilmadi. Keyinroq urinib ko‘ring.'])
                ->withInput();
        }

        $request->session()->put('profile_email_change_pending', $newEmail);
        if ($this->wantsJson($request)) {
            return $this->sectionSuccessResponse($request, 'email', "Tasdiqlash kodi {$newEmail} manziliga yuborildi.");
        }

        return redirect()
            ->route('profile.show', ['panel' => 'security'])
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
            if ($this->wantsJson($request)) {
                return $this->sectionErrorResponse('email', 'Avval yangi email kiriting va kod oling.');
            }

            return redirect()
                ->route('profile.show', ['panel' => 'security'])
                ->with('error', 'Avval yangi email kiriting va kod oling.')
                ->with('toast_type', 'error');
        }

        $user = $request->user();

        if (RateLimiter::tooManyAttempts($this->emailChangeVerifyKey($pending), self::OTP_VERIFY_MAX_ATTEMPTS)) {
            if ($this->wantsJson($request)) {
                return $this->sectionErrorResponse('email', "Juda ko'p xato urinish. {$this->emailChangeVerifySecondsLeft($pending)} soniyadan keyin qayta urinib ko'ring.", [
                    'code' => ["Juda ko'p xato urinish. {$this->emailChangeVerifySecondsLeft($pending)} soniyadan keyin qayta urinib ko'ring."],
                ]);
            }

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

            if ($this->wantsJson($request)) {
                return $this->sectionErrorResponse('email', "Kod noto'g'ri yoki muddati tugagan.", [
                    'code' => ["Kod noto'g'ri yoki muddati tugagan."],
                ]);
            }

            return back()->withErrors(['code' => "Kod noto'g'ri yoki muddati tugagan."]);
        }

        $meta = $otp->meta ?? [];
        if ((int) ($meta['user_id'] ?? 0) !== (int) $user->id) {
            if ($this->wantsJson($request)) {
                return $this->sectionErrorResponse('email', 'Tasdiqlash sessiyasi yaroqsiz. Qaytadan urinib ko\'ring.');
            }

            return redirect()
                ->route('profile.show', ['panel' => 'security'])
                ->with('error', 'Tasdiqlash sessiyasi yaroqsiz. Qaytadan urinib ko‘ring.')
                ->with('toast_type', 'error');
        }

        if (User::query()->where('email', $pending)->where('id', '!=', $user->id)->exists()) {
            $request->session()->forget('profile_email_change_pending');
            $otp->delete();

            if ($this->wantsJson($request)) {
                return $this->sectionErrorResponse('email', 'Bu email allaqachon boshqa hisobda ishlatilgan.', [
                    'email' => ['Bu email allaqachon boshqa hisobda ishlatilgan.'],
                ]);
            }

            return redirect()
                ->route('profile.show', ['panel' => 'security'])
                ->with('error', 'Bu email allaqachon boshqa hisobda ishlatilgan.')
                ->with('toast_type', 'error');
        }

        $oldEmail = $user->email;
        $user->update([
            'email' => $pending,
            'email_verified_at' => now(),
        ]);

        \App\Services\UserActivityLogger::log(
            $user,
            \App\Models\UserActivity::TYPE_EMAIL_CHANGED,
            'Email manzili o\'zgartirildi',
            ['old_email' => $oldEmail],
            ['new_email' => $pending]
        );

        $otp->delete();
        RateLimiter::clear($this->emailChangeVerifyKey($pending));
        $request->session()->forget('profile_email_change_pending');

        if ($this->wantsJson($request)) {
            return $this->sectionSuccessResponse($request, 'email', 'Email manzili yangilandi.');
        }

        return redirect()
            ->route('profile.show', ['panel' => 'security'])
            ->with('success', 'Email manzili yangilandi.')
            ->with('toast_type', 'success');
    }

    public function resendEmailChange(Request $request)
    {
        $pending = (string) $request->session()->get('profile_email_change_pending', '');
        if ($pending === '') {
            if ($this->wantsJson($request)) {
                return $this->sectionErrorResponse('email', 'Avval yangi email kiriting.');
            }

            return redirect()
                ->route('profile.show', ['panel' => 'security'])
                ->with('error', 'Avval yangi email kiriting.')
                ->with('toast_type', 'error');
        }

        $user = $request->user();

        if (! $this->mailDeliveryEnabled()) {
            if ($this->wantsJson($request)) {
                return $this->sectionErrorResponse('email', $this->mailDeliveryDisabledMessage(), [
                    'code' => [$this->mailDeliveryDisabledMessage()],
                ]);
            }

            return back()->withErrors([
                'code' => $this->mailDeliveryDisabledMessage(),
            ]);
        }

        if (! $this->canSendEmailChangeOtp($pending)) {
            if ($this->wantsJson($request)) {
                return $this->sectionErrorResponse('email', "Qayta yuborishdan oldin {$this->emailChangeResendSecondsLeft($pending)} soniya kuting.", [
                    'code' => ["Qayta yuborishdan oldin {$this->emailChangeResendSecondsLeft($pending)} soniya kuting."],
                ]);
            }

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
            if ($this->wantsJson($request)) {
                return $this->sectionErrorResponse('email', 'Kodni qayta yuborish mumkin emas. Emailni qayta kiriting.');
            }

            return redirect()
                ->route('profile.show', ['panel' => 'security'])
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
            if ($this->wantsJson($request)) {
                return $this->sectionErrorResponse('email', 'Kodni qayta yuborib bo\'lmadi.', [
                    'code' => ['Kodni qayta yuborib bo\'lmadi.'],
                ], 500);
            }

            return back()->withErrors(['code' => 'Kodni qayta yuborib bo‘lmadi.']);
        }

        if ($this->wantsJson($request)) {
            return $this->sectionSuccessResponse($request, 'email', 'Yangi kod yuborildi.', 'warning');
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

        if ($this->wantsJson($request)) {
            return $this->sectionSuccessResponse($request, 'email', 'Email almashtirish bekor qilindi.', 'warning');
        }

        return redirect()
            ->route('profile.show', ['panel' => 'security'])
            ->with('success', 'Email almashtirish bekor qilindi.')
            ->with('toast_type', 'warning');
    }

    private function issueEmailChangeOtp(string $email, int $userId): void
    {
        if (! $this->mailDeliveryEnabled()) {
            throw new \RuntimeException('Mail delivery is disabled.');
        }

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

    private function mailDeliveryEnabled(): bool
    {
        return (bool) config('mail.enabled', true)
            && (bool) config('mail.code_delivery_enabled', false)
            && $this->mailConfigurationReady();
    }

    private function mailDeliveryDisabledMessage(): string
    {
        return 'Email yuborish vaqtincha ishlamayapti. Keyinroq qayta urinib ko\'ring.';
    }

    private function mailConfigurationReady(): bool
    {
        return match ((string) config('mail.default', 'smtp')) {
            'resend' => $this->hasConfiguredResendApiKey(),
            'smtp' => filled(config('mail.mailers.smtp.host')),
            default => true,
        };
    }

    private function hasConfiguredResendApiKey(): bool
    {
        $apiKey = trim((string) (config('resend.api_key') ?? config('services.resend.key') ?? ''));

        if ($apiKey === '' || ! str_starts_with($apiKey, 're_')) {
            return false;
        }

        $normalizedKey = strtolower($apiKey);

        return ! str_contains($normalizedKey, 'sizning_kalitingiz')
            && ! str_contains($normalizedKey, 'your_key')
            && ! str_contains($normalizedKey, 'your-api-key');
    }

    private function passwordChangeKey(Request $request, int $userId): string
    {
        return 'profile-password-change:'.$userId.':'.$request->ip();
    }

    private function passwordChangeSecondsLeft(Request $request, int $userId): int
    {
        return RateLimiter::availableIn($this->passwordChangeKey($request, $userId));
    }

    private function storePasswordChangeConfirmation(Request $request, int $userId): void
    {
        $request->session()->put('profile_password_change_confirmation', [
            'user_id' => $userId,
            'confirmed_at' => now()->timestamp,
            'password_hash' => (string) $request->user()->password,
        ]);
    }

    private function clearPasswordChangeConfirmation(Request $request): void
    {
        $request->session()->forget('profile_password_change_confirmation');
    }

    public function exportResults(Request $request)
    {
        $user = $request->user();

        // CSV export — faqat VIP donor uchun (donorCanExport imtiyozlari)
        if (! $user->donorCanExport()) {
            return redirect()->route('profile.results.index')
                ->with('error', 'CSV export faqat VIP donorlar uchun mavjud. VIP darajaga ko\'tarilib, barcha natijalaringizni eksport qiling!')
                ->with('toast_type', 'error');
        }

        $results = $this->userResultsBaseQuery($user)
            ->with('exam:id,title,total_points,passing_points')
            ->latest('submitted_at')
            ->get();

        $filename = 'natijalar_' . Str::slug($user->name) . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return $this->streamResultsCsv($results, $filename);
    }

    public function exportSingleResult(Request $request, Result $result)
    {
        $user = $request->user();

        // CSV export — faqat VIP donor uchun
        if (! $user->donorCanExport()) {
            return redirect()->route('profile.results.index')
                ->with('error', 'CSV export faqat VIP donorlar uchun mavjud.')
                ->with('toast_type', 'error');
        }

        $result = $this->userResultsBaseQuery($user)
            ->with('exam:id,title,total_points,passing_points')
            ->findOrFail($result->id);

        $filename = 'natija_' . Str::slug((string) ($result->exam->title ?? 'imtihon')) . '_' . $result->id . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return $this->streamResultsCsv(collect([$result]), $filename);
    }

    private function streamResultsCsv($results, string $filename): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        $callback = function () use ($results) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fwrite($out, "sep=;\r\n");
            fputcsv($out, ['Imtihon', 'Ball', 'Max ball', 'Natija', "To'g'ri javoblar", 'Jami savollar', 'Holat', 'Sana'], ';', '"', '\\');

            foreach ($results as $r) {
                fputcsv($out, [
                    $r->exam->title ?? '-',
                    $r->points_earned ?? '-',
                    $r->points_max ?? '-',
                    $r->passed ? "O'tdi" : 'Yiqildi',
                    $r->score,
                    $r->total_questions,
                    $r->status === 'expired' ? 'Vaqt tugagan' : 'Topshirilgan',
                    $r->submitted_at?->format('d.m.Y H:i') ?? '-',
                ], ';', '"', '\\');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function userResultsBaseQuery(User $user)
    {
        return Result::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['submitted', 'expired']);
    }

    private function hasConfirmedPasswordChange(Request $request, int $userId): bool
    {
        $meta = $request->session()->get('profile_password_change_confirmation');

        if (! is_array($meta) || (int) ($meta['user_id'] ?? 0) !== $userId) {
            return false;
        }

        if (! hash_equals((string) ($meta['password_hash'] ?? ''), (string) $request->user()->password)) {
            $this->clearPasswordChangeConfirmation($request);

            return false;
        }

        $confirmedAt = (int) ($meta['confirmed_at'] ?? 0);
        if ($confirmedAt < (now()->timestamp - self::PASSWORD_CHANGE_CONFIRM_TTL_SECONDS)) {
            $this->clearPasswordChangeConfirmation($request);

            return false;
        }

        return true;
    }

    private function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }

    private function sectionErrorResponse(string $section, string $message, array $errors = [], int $status = 422)
    {
        return response()->json([
            'ok' => false,
            'section' => $section,
            'message' => $message,
            'errors' => $errors,
            'toast_type' => 'error',
        ], $status);
    }

    private function sectionSuccessResponse(Request $request, string $section, string $message, string $toastType = 'success')
    {
        $payload = [
            'ok' => true,
            'section' => $section,
            'message' => $message,
            'toast_type' => $toastType,
        ];

        if ($section === 'email') {
            $payload['html'] = $this->renderEmailCard($request);
            $payload['user_email'] = (string) $request->user()->email;
            $payload['pending_email'] = (string) $request->session()->get('profile_email_change_pending', '');
        }

        if ($section === 'password') {
            $payload['html'] = $this->renderPasswordCard($request);
            $payload['password_unlocked'] = $this->hasConfirmedPasswordChange($request, (int) $request->user()->id);
        }

        return response()->json($payload);
    }

    private function renderEmailCard(Request $request): string
    {
        return view('profile.partials.email-card', [
            'user' => $request->user()->loadMissing('roleRelation'),
            'pendingEmail' => (string) $request->session()->get('profile_email_change_pending', ''),
        ])->render();
    }

    private function renderPasswordCard(Request $request): string
    {
        return view('profile.partials.password-card', [
            'passwordChangeUnlocked' => $this->hasConfirmedPasswordChange($request, (int) $request->user()->id),
        ])->render();
    }

    public function updateAppearance(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            "donor_theme" => "nullable|string|max:40",
            "badge_style" => "nullable|in:default,pill,icon",
            "comment_style" => "nullable|in:border,filled",
            "chat_style" => "nullable|in:show,hide",
            "show_expiry_badge" => "nullable|in:0,1",
            "name_font_weight" => "nullable|in:600,700,800",
        ]);

        // Tema tanlash — server-side ruxsat tekshiruvi.
        // Ruxsat etilgan tema bo'lsa — qabul qilamiz, aks holda joriy tema saqlanadi.
        $theme = $user->profile_theme ?: $user->donation_rank;
        if (!empty($data["donor_theme"]) && \App\Models\Donation::themeAllowedForUser($data["donor_theme"], $user)) {
            $theme = $data["donor_theme"];
        }

        // Tema rangini username_color uchun olamiz
        $themeConfig = \App\Models\Donation::themeConfig($theme);
        $themeColor = $themeConfig["badge_color"] ?? null;

        $user->update([
            "profile_theme" => $theme,
            "username_color" => $themeColor ?? $user->username_color,
            "badge_style" => $data["badge_style"] ?? $user->badge_style,
            "comment_style" => $data["comment_style"] ?? $user->comment_style,
            "chat_style" => $data["chat_style"] ?? $user->chat_style,
            "show_expiry_badge" => $data["show_expiry_badge"] ?? $user->show_expiry_badge,
            "name_font_weight" => $data["name_font_weight"] ?? $user->name_font_weight,
        ]);

        return redirect()->route("profile.show", ["panel" => "appearance"])
            ->with("success", "Korinish saqlandi!")
            ->with("toast_type", "success");
    }
}
