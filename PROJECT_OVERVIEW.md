# 81-Maktab - Ta'lim Platformasi

## Loyiha Haqida

Bu O'zbekiston uchun mo'ljallangan ta'lim platformasi. O'quvchilar, o'qituvchilar va administratorlar uchun to'liq funksional tizim.

## Asosiy Xususiyatlar

### 1. Foydalanuvchi Tizimi
- **Rol tizimi**: Super Admin, Admin, Editor, Moderator, Teacher, User
- **Hierarxik ruxsatlar**: Har bir rol o'z darajasiga ega (1-5)
- **Google autentifikatsiyasi**
- **Profil boshqaruvi**: Avatar, sinf tanlash, email o'zgartirish
- **Ota-ona hisoblari**: Ota-onalar uchun maxsus ruxsatlar

### 2. Yangiliklar Tizimi (Posts)
- Kategoriyalarga bo'lingan yangiliklar
- Video qo'llab-quvvatlash
- Izohlar va like tizimi
- Ko'rishlar statistikasi
- Slug URL tizimi

### 3. O'qituvchi Profillari
- O'qituvchilarning to'liq profili
- Fan, lavozim, toifa ma'lumotlari
- Tajriba yillari va yutuqlar
- Kurs yaratish imkoniyati
- Like va bookmark tizimi

### 4. Kurslar Tizimi
- Kurs yaratish va boshqarish
- Kursga yozilish (enrollment)
- Admin tasdiqlash tizimi
- Kurs rasmlari va tavsiflar
- Narx va davomiylik ma'lumotlari

### 5. Imtihon Tizimi
- Savol-javob imtihonlari
- Vaqt cheklovi
- Ball tizimi
- Natijalar va statistika
- Sinf bo'yicha cheklovlar
- Matnli javoblar uchun baholash

### 6. Chat Tizimi
- Shaxsiy chat
- Guruh chatlar
- Guruhga qo'shilish so'rovlari
- Foydalanuvchi blokirovka

### 7. Qo'shimcha Xususiyatlar
- **Ta'vim**: Tadbirlar kalendar
- **Aloqa**: Xabarlar tizimi
- **Bookmarklar**: Saqlanganlar
- **AI**: Bilim bazasi va interaktiv yordamchi
- **Feature Requests**: Fikr va takliflar
- **Sitemap/Robots**: SEO optimallashtirish

## Ma'lumotlar Bazasi Tuzilishi

### Asosiy Jadvalar

**Foydalanuvchilar:**
- `users` - Foydalanuvchi ma'lumotlari
- `roles` - Rollar
- `roles_user` - Foydalanuvchi-rollar munosabati

**Kontent:**
- `posts` - Yangiliklar
- `categories` - Kategoriyalar
- `comments` - Izohlar
- `post_likes` - Post like'lari

**O'qituvchilar:**
- `teachers` - O'qituvchi profillari
- `teacher_comments` - O'qituvchi izohlari
- `teacher_likes` - O'qituvchi like'lari

**Kurslar:**
- `courses` - Kurslar
- `course_enrollments` - Kursga yozilishlar

**Imtihonlar:**
- `exams` - Imtihonlar
- `questions` - Savollar
- `options` - Variantlar
- `results` - Natijalar
- `answers` - Javoblar

**Chat:**
- `chat_messages` - Chat xabarlari
- `chat_groups` - Chat guruhlari
- `chat_group_members` - Guruh a'zolari
- `chat_group_join_requests` - Guruhga qo'shilish so'rovlari

**Boshqa:**
- `calendar_events` - Tadbirlar
- `contact_messages` - Aloqa xabarlari
- `bookmarks` - Saqlanganlar
- `ai_knowledges` - AI bilim bazasi
- `ai_interactions` - AI interaktiv sessiyalari
- `feature_requests` - Fikr va takliflar
- `school_classes` - Maktab sinflari

## Routelar Tuzilishi

### Public Routelar (`routes/web/public.php`)
- `/` - Bosh sahifa
- `/about` - Biz haqida
- `/courses` - Kurslar ro'yxati
- `/courses/{course}` - Kurs tafsilotlari
- `/post` - Yangiliklar
- `/teacher` - O'qituvchilar
- `/taqvim` - Ta'vim
- `/contact` - Aloqa
- `/search` - Global qidiruv

### Member Routelar (`routes/web/member.php`)
- `/profile` - Profil
- `/profile/sinfni-tanlash` - Sinf tanlash
- `/profile/bookmarks` - Saqlanganlar
- `/profile/natijalar` - Natijalar
- `/chat/*` - Chat tizimi
- `/exams/*` - Imtihonlar
- `/feature-requests/*` - Fikr va takliflar

### Admin Routelar (`routes/web/admin.php`)
- `/dashboard` - Admin paneli
- `/admin/posts/*` - Yangiliklar boshqaruvi
- `/admin/categories/*` - Kategoriyalar
- `/admin/teachers/*` - O'qituvchilar
- `/admin/courses/*` - Kurslar
- `/admin/exams/*` - Imtihonlar
- `/admin/contact-messages/*` - Xabarlar
- `/admin/comments/*` - Izohlar
- `/admin/ai-knowledges/*` - AI bilim bazasi
- `/admin/settings` - Sozlamalar

### Teacher Routelar (`routes/web/teacher.php`)
- `/course-open/*` - Kurs yaratish va boshqarish
- `/profile/kurs-arizalari/*` - Kurs arizalari

## Controllerlar

### Asosiy Controllerlar
- `HomeController` - Bosh sahifa va public sahifalar
- `AuthController` - Autentifikatsiya
- `ProfileController` - Profil boshqaruvi
- `PostController` - Yangiliklar boshqaruvi
- `TeacherController` - O'qituvchilar boshqaruvi
- `CourseController` - Kurslar boshqaruvi
- `ExamController` - Imtihonlar tizimi
- `ChatController` - Chat tizimi
- `BookmarkController` - Saqlanganlar
- `FeatureRequestController` - Fikr va takliflar

### Admin Controllerlar
- `AdminController` - Admin paneli
- `AdminPostController` - Post boshqaruvi
- `AdminTeacherController` - O'qituvchi boshqaruvi
- `AdminCourseController` - Kurs boshqaruvi
- `AdminExamController` - Imtihon boshqaruvi
- `AdminCommentController` - Izoh boshqaruvi
- `AdminContactMessageController` - Xabar boshqaruvi
- `AdminAiKnowledgeController` - AI bilim bazasi
- `AdminSettingsController` - Sozlamalar

## Texnologiyalar

- **Framework**: Laravel 11
- **Frontend**: Blade templates, TailwindCSS
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **File Storage**: Public storage
- **Localization**: Uzbek, English, Russian
- **Deployment**: Docker, Vercel support

## Ruxsatlar Tizimi

### Rol Hierarxiyasi
1. **Super Admin** (Level 5) - Barcha ruxsatlar
2. **Admin** (Level 4) - Tizim boshqaruvi
3. **Editor** (Level 3) - Kontent boshqaruvi
4. **Moderator** (Level 2) - Izohlar va xabarlar
5. **Teacher** (Level 2) - Kurs va imtihon yaratish
6. **User** (Level 1) - Oddiy foydalanuvchi

### Asosiy Ruxsatlar
- `canAccessDashboard()` - Admin paneliga kirish
- `canManageContent()` - Kontent boshqaruvi
- `canManageInbox()` - Izohlar va xabarlar
- `canManageEducation()` - Ta'lim tizimi
- `canManageExams()` - Imtihon boshqaruvi
- `canManageSystem()` - Tizim sozlamalari

## Maxsus Xususiyatlar

### Grade Selection System
- O'quvchilar uchun sinf tanlash
- "Barcha sinflar" uchun universal ruxsat
- Ota-onalar uchun maxsus ruxsat

### Course Open Approval
- O'qituvchilar uchun kurs ochish so'rovi
- Admin tasdiqlash tizimi
- Bir kurs cheklovi

### Exam System
- Vaqt cheklovi
- Savol tartibi randomizatsiya
- Qoida buzilish hisobi
- Matnli javob baholash

## Deployment
- Docker containerization
- Vercel deployment support
- Environment configuration
- Production optimizations
