<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AiKnowledgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // ═══════════════════════════════════════════════════════════════
            // MAKTAB HAQIDA
            // ═══════════════════════════════════════════════════════════════
            [
                'question' => 'Maktab qachon tashkil topgan?',
                'question_en' => 'When was the school established?',
                'answer' => "🏫 81-maktab 1963-yildan buyon faoliyat yuritib kelmoqda.\n\n📅 Yarim asrdan ortiq vaqt davomida maktab minglab o'quvchilarga sifatli ta'lim berib kelmoqda.\n\n🌟 Maktab tarixi va yutuqlari haqida batafsil \"Maktab haqida\" bo'limidan o'qishingiz mumkin.",
                'answer_en' => '🏫 School 81 has been operating since 1963.\n\n📅 For over half a century, the school has been providing quality education to thousands of students.\n\n🌟 You can read more about the school history and achievements in the "About" section.',
                'keywords' => 'tashkil, qachon, ochilgan, sana, yil, tarix',
                'category' => 'Maktab',
                'priority' => 2,
            ],
            [
                'question' => 'Maktab manzili qayerda?',
                'question_en' => 'Where is the school located?',
                'answer' => "📍 Maktabimiz quyidagi manzilda joylashgan:\n\n🏠 Toshkent shahar, Uchtepa tumani\n📌 Paxtakor MFY, Ali Qushchi ko'chasi 3-uy\n\n🗺️ Lokatsiya tuman markazidan taxminan 1,4 km uzoqlikda joylashgan.",
                'answer_en' => "📍 Our school is located at:\n\n🏠 3 Ali Qushchi Street, Pakhtakor MFY\n📌 Uchtepa district, Tashkent city\n\n🗺️ It is about 1.4 km from the district center.",
                'keywords' => 'manzil, qayerda, joylashgan, lokatsiya, xarita, uchtepa',
                'category' => 'Maktab',
                'priority' => 2,
            ],
            [
                'question' => 'Maktab direktori kim?',
                'question_en' => 'Who is the school principal?',
                'answer' => "👤 Maktab direktori — Xaydarova Ziyoda Tolipovna\n\n🎓 Menejerlik sertifikatiga ega tajribali rahbar hisoblanadi.\n\n💼 Maktabni boshqarish va rivojlantirish borasida katta tajribaga ega.",
                'answer_en' => "👤 The school principal is Ziyoda Tolipovna Khaydarova.\n\n🎓 She is an experienced leader with a management certificate.\n\n💼 She has extensive experience in managing and developing the school.",
                'keywords' => 'direktor, rahbar, boshliq, kim, ismi',
                'category' => 'Maktab',
                'priority' => 2,
            ],
            [
                'question' => 'Maktabda necha nafar o\'quvchi o\'qiydi?',
                'question_en' => 'How many students study at the school?',
                'answer' => "📚 Maktabda jami 2 097 nafar o'quvchi tahsil olmoqda.\n\n🇺🇿 O'zbek sinflarida: 1 566 nafar\n🇷🇺 Rus sinflarida: 531 nafar\n\n📊 Jami sinflar soni: 60 ta",
                'answer_en' => "📚 Currently, 2,097 students study at the school.\n\n🇺🇿 Uzbek classes: 1,566 students\n🇷🇺 Russian classes: 531 students\n\n📊 Total classes: 60",
                'keywords' => 'o\'quvchilar soni, necha kishi, oquvchi, talaba',
                'category' => 'Maktab',
                'priority' => 2,
            ],
            [
                'question' => 'Maktabda necha nafar o\'qituvchi bor?',
                'question_en' => 'How many teachers are there?',
                'answer' => "👨‍🏫 Maktabda 90 nafar yuqori malakali pedagog faoliyat yuritadi.\n\n📋 Ma'lumotlari:\n✅ Barchasi oliy ma'lumotli\n🏅 21 nafari oliy toifali\n🌍 26 nafari milliy/xalqaro sertifikatlarga ega",
                'answer_en' => "👨‍🏫 There are 90 highly qualified educators at the school.\n\n📋 Details:\n✅ All have higher education\n🏅 21 are of the highest category\n🌍 26 hold national/international certificates",
                'keywords' => 'ustozlar soni, oqituvchilar, pedagoglar, necha nafar',
                'category' => 'Maktab',
                'priority' => 2,
            ],
            [
                'question' => 'Maktab binosi va sharoitlari qanday?',
                'question_en' => 'What are the school facilities like?',
                'answer' => "🏢 Maktab binosi va sharoitlari:\n\n📐 Umumiy maydon: 16 000 m²\n🪑 O'rinlar soni: 960 ta\n\n💻 3 ta kompyuter sinfi (45 ta kompyuter)\n🔬 Fizika, kimyo, biologiya laboratoriyalari\n🍽️ 120 o'rinli oshxona\n🎭 150 o'rinli faollar zali\n🏋️ Sport zali",
                'answer_en' => "🏢 School facilities:\n\n📐 Total area: 16,000 m²\n🪑 Capacity: 960 seats\n\n💻 3 computer labs (45 computers)\n🔬 Physics, chemistry, biology labs\n🍽️ 120-seat canteen\n🎭 150-seat assembly hall\n🏋️ Gym",
                'keywords' => 'sharoitlar, bino, oshxona, sport zal, kompyuter, laboratoriya',
                'category' => 'Maktab',
                'priority' => 2,
            ],
            [
                'question' => 'Maktab yaxshimi?',
                'question_en' => 'Is the school good?',
                'answer' => "⭐ 81-maktab — Toshkentdagi eng yaxshi maktablardan biri!\n\n📊 Statistika:\n🎓 OTMga kirish: 61%\n📈 O'rtacha ball: 82.9\n🌍 26 ta xalqaro sertifikat\n\n💪 Kuchli pedagogik jamoa va boy tarix bilan faxrlanamiz!",
                'answer_en' => "⭐ School 81 is one of the best schools in Tashkent!\n\n📊 Statistics:\n🎓 University admission: 61%\n📈 Average score: 82.9\n🌍 26 international certificates\n\n💪 We are proud of our strong pedagogical team and rich history!",
                'keywords' => 'yaxshimi, sifatli, qanaqa maktab, fikrlar',
                'category' => 'Maktab',
                'priority' => 1,
            ],
            [
                'question' => 'Maktabda qanday to\'garaklar bor?',
                'question_en' => 'What clubs are available at the school?',
                'answer' => "🎨 Maktabda quyidagi to'garaklar mavjud:\n\n📚 Fan to'garaklari:\n• Matematika\n• Ingliz tili\n• IT (Informatika)\n\n⚽ Sport to'garaklari\n🎭 San'at yo'nalishlari\n\n💡 Batafsil ma'lumotni \"Kurslar\" bo'limidan topishingiz mumkin.",
                'answer_en' => "🎨 Available clubs at the school:\n\n📚 Academic clubs:\n• Mathematics\n• English\n• IT (Computer Science)\n\n⚽ Sports clubs\n🎭 Arts clubs\n\n💡 Detailed information can be found in the \"Courses\" section.",
                'keywords' => 'togaraklar, kurslar, darsdan tashqari, mashgulotlar',
                'category' => 'Maktab',
                'priority' => 1,
            ],
            [
                'question' => 'Ota-onalar uchun qanday imkoniyatlar bor?',
                'question_en' => 'What opportunities are there for parents?',
                'answer' => "👨‍👩‍👧 Ota-onalar uchun imkoniyatlar:\n\n📰 Yangiliklarni kuzatish\n👨‍🏫 O'qituvchilar bilan bog'lanish\n📊 Farzandning dars jarayonlari haqida ma'lumot olish\n💬 Muntazam muloqot tizimi\n\n🌐 Sayt orqali barcha ma'lumotlarni olish mumkin.",
                'answer_en' => "👨‍👩‍👧 Opportunities for parents:\n\n📰 Follow school news\n👨‍🏫 Contact teachers\n📊 Get information about children's lessons\n💬 Regular communication system\n\n🌐 All information available through the website.",
                'keywords' => 'ota-onalar, ota ona, majlis, boglanish',
                'category' => 'Maktab',
                'priority' => 1,
            ],
            [
                'question' => 'Maktabga qanday hujjat topshirsa bo\'ladi?',
                'question_en' => 'How to apply to the school?',
                'answer' => "📝 Maktabga qabul qilish:\n\n1️⃣ Davlat xizmatlari markaziga murojaat qiling\n2️⃣ Yoki my.uz portalidan foydalaning\n\n📞 Qo'shimcha savollar uchun:\n☎️ +99890-958-00-67",
                'answer_en' => "📝 School admission:\n\n1️⃣ Contact the Public Services Center\n2️⃣ Or use my.uz portal\n\n📞 For additional questions:\n☎️ +99890-958-00-67",
                'keywords' => 'qabul, hujjat topshirish, oqishga kirish, 1-sinf',
                'category' => 'Maktab',
                'priority' => 1,
            ],

            // ═══════════════════════════════════════════════════════════════
            // ALOQA
            // ═══════════════════════════════════════════════════════════════
            [
                'question' => 'Maktab bilan qanday bog\'lanish mumkin?',
                'question_en' => 'How to contact the school?',
                'answer' => "📞 Maktab bilan bog'lanish usullari:\n\n1️⃣ Telefon: +99890-958-00-67\n2️⃣ Saytdagi \"Aloqa\" bo'limi orqali\n3️⃣ Email manzili (Aloqa sahifasida)\n\n💬 Barcha savollaringizga javob beramiz!",
                'answer_en' => "📞 Ways to contact the school:\n\n1️⃣ Phone: +99890-958-00-67\n2️⃣ Through the \"Contact\" section on the website\n3️⃣ Email (available on the Contact page)\n\n💬 We're here to answer all your questions!",
                'keywords' => 'boglanish, aloqa, telefon, nomer, xabar, murojaat',
                'category' => 'Aloqa',
                'priority' => 2,
            ],
            [
                'question' => 'Maktab telefon raqami nima?',
                'question_en' => 'What is the school phone number?',
                'answer' => "☎️ Maktabning rasmiy telefon raqami:\n\n📞 +99890-958-00-67\n\n🕐 Qabul vaqti: Dushanba-Juma, 09:00 - 17:00\n\n📋 Qo'ng'iroq orqali barcha savollaringizga javob olasiz.",
                'answer_en' => "☎️ Official school phone number:\n\n📞 +99890-958-00-67\n\n🕐 Working hours: Monday-Friday, 09:00 - 17:00\n\n📋 You can get answers to all your questions by phone.",
                'keywords' => 'telefon, raqam, nomer, aloqa, contact',
                'category' => 'Aloqa',
                'priority' => 2,
            ],
            [
                'question' => 'Admin bilan qanday bog\'lansam bo\'ladi?',
                'question_en' => 'How can I contact the admin?',
                'answer' => "👨‍💻 Admin bilan bog'lanish:\n\n1️⃣ Aloqa sahifasiga kiring\n2️⃣ Xabar yuboring (akkauntga kirgan bo'lishingiz kerak)\n3️⃣ Yoki qo'ng'iroq qiling: +99890-958-00-67\n\n⚡ Shoshilinch holatda telefon orqali bog'laning!",
                'answer_en' => "👨‍💻 Contact admin:\n\n1️⃣ Go to the Contact page\n2️⃣ Send a message (you need to be logged in)\n3️⃣ Or call: +99890-958-00-67\n\n⚡ For urgent matters, contact by phone!",
                'keywords' => 'admin bilan boglanish, adminga yozish, support, texnik yordam, murojaat, contact admin',
                'category' => 'Aloqa',
                'priority' => 3,
            ],

            // ═══════════════════════════════════════════════════════════════
            // TA'LIM VA NATIJALAR
            // ═══════════════════════════════════════════════════════════════
            [
                'question' => 'Ta\'lim qaysi tillarda olib boriladi?',
                'question_en' => 'In which languages is education provided?',
                'answer' => "🌍 Ta'lim tillari:\n\n🇺🇿 O'zbek tili: 45 ta sinf\n🇷🇺 Rus tili: 15 ta sinf\n\n📊 Jami: 60 ta sinf\n\n🌐 Ikkala tilda ham sifatli ta'lim beriladi!",
                'answer_en' => "🌍 Languages of instruction:\n\n🇺🇿 Uzbek language: 45 classes\n🇷🇺 Russian language: 15 classes\n\n📊 Total: 60 classes\n\n🌐 Quality education in both languages!",
                'keywords' => 'til, o\'zbekcha, ruscha, tillar, sinflar',
                'category' => 'Ta\'lim',
                'priority' => 1,
            ],
            [
                'question' => 'Bitiruvchilarning natijalari qanday?',
                'question_en' => 'What are the graduates\' results?',
                'answer' => "🎓 2025-yil bitiruvchilari natijalari:\n\n👥 Jami bitiruvchi: 121 nafar\n✅ OTMga kirgan: 74 nafar (61%)\n📈 O'rtacha ball: 82.9\n\n🏆 Yuqori natijalar bilan faxrlanamiz!",
                'answer_en' => "🎓 2025 graduates' results:\n\n👥 Total graduates: 121\n✅ Entered university: 74 (61%)\n📈 Average score: 82.9\n\n🏆 We are proud of these high results!",
                'keywords' => 'natija, otm, kirish, bitiruvchilar, ball',
                'category' => 'Ta\'lim',
                'priority' => 1,
            ],

            // ═══════════════════════════════════════════════════════════════
            // SAYT VA AI
            // ═══════════════════════════════════════════════════════════════
            [
                'question' => 'Sayt muallifi kim?',
                'question_en' => 'Who created the website?',
                'answer' => "💻 Sayt mualliflari:\n\n👨‍💻 Xabibullayev Shamsiddin\n👩‍💻 Abduqodirova E'zoza\n👩‍💻 Mirzaqosimova Xadicha\n\n🏫 10-\"E\" sinf o'quvchilari tomonidan yaratilgan.",
                'answer_en' => "💻 Website creators:\n\n👨‍💻 Shamsiddin Khabibullayev\n👩‍💻 E'zoza Abdukodirova\n👩‍💻 Xadicha Mirzakosimova\n\n🏫 Created by Grade 10-E students.",
                'keywords' => 'sayt, muallif, yaratgan, dasturchi, developer, ishtirok, jamoa',
                'category' => 'Sayt',
                'priority' => 1,
            ],
            [
                'question' => 'AI yordamchi nimalarga javob beradi?',
                'question_en' => 'What can the AI assistant answer?',
                'answer' => "🤖 AI yordamchi quyidagi mavzular bo'yicha javob beradi:\n\n🏫 Maktab haqida ma'lumot\n📚 Kurslar (ochish, yozilish)\n📝 Imtihonlar (ishlash, natijalar)\n💬 Chat (global va guruh)\n❤️ Xayriya (qilish, darajalar)\n💡 Fikr-taklif (qoldirish, ovoz)\n🔖 Bookmark (saqlash)\n👤 Rollar (o'quvchi, o'qituvchi, ota-ona)\n📅 Taqvim\n📱 Telegram bot\n\n❓ Qanday savol bo'lsa, bemalol bering!",
                'answer_en' => "🤖 The AI assistant answers questions about:\n\n🏫 School information\n📚 Courses (creating, enrolling)\n📝 Exams (working, results)\n💬 Chat (global and group)\n❤️ Donations (how to donate, ranks)\n💡 Feature requests (submitting, voting)\n🔖 Bookmarks (saving)\n👤 Roles (student, teacher, parent)\n📅 Calendar\n📱 Telegram bot\n\n❓ Feel free to ask any question!",
                'keywords' => 'ai, yordamchi, nima qiladi, savol, javob, imkoniyat, nimalarni biladi',
                'category' => 'AI',
                'priority' => 5,
            ],
            [
                'question' => 'AI yordamchi bilan qanday suhbatlashaman?',
                'question_en' => 'How do I chat with the AI assistant?',
                'answer' => "💬 AI yordamchi bilan suhbat:\n\n1️⃣ Saytning pastki qismidagi chat panelini oching\n2️⃣ Savolni yozing\n3️⃣ \"Yuborish\" tugmasini bosing\n4️⃣ AI avtomatik javob beradi\n\n🔄 Agar javob yetarli bo'lmasa, qo'shimcha savol bering!",
                'answer_en' => "💬 Chat with AI assistant:\n\n1️⃣ Open the chat panel at the bottom of the page\n2️⃣ Type your question\n3️⃣ Click \"Send\"\n4️⃣ AI responds automatically\n\n🔄 If the answer is not enough, ask follow-up questions!",
                'keywords' => 'ai chat, suhbat, qanday ishlatish, yordamchi bilan suhbat',
                'category' => 'AI',
                'priority' => 3,
            ],

            // ═══════════════════════════════════════════════════════════════
            // TELEGRAM BOT (ASOSIY TALAB)
            // ═══════════════════════════════════════════════════════════════
            [
                'question' => 'Telegram bot nima uchun kerak?',
                'question_en' => 'What is the Telegram bot for?',
                'answer' => "🤖 Telegram bot sayt bilan bog'lanish uchun ishlatiladi.\n\n📱 Bot orqali:\n1️⃣ Ro'yxatdan o'tishda telefon tasdiqlash\n2️⃣ Kirishda qo'shimcha xavfsizlik\n3️⃣ Parolni tiklash\n4️⃣ Email/telefon o'zgartirishni tasdiqlash\n5️⃣ Kurs ochish so'rovlarini boshqarish (o'qituvchilar)\n6️⃣ Imtihon natijalarini olish\n\n🔐 Xavfsizlik uchun juda muhim!",
                'answer_en' => "🤖 The Telegram bot is used to connect with the site.\n\n📱 Through the bot you can:\n1️⃣ Verify phone during registration\n2️⃣ Extra security during login\n3️⃣ Reset password\n4️⃣ Confirm email/phone changes\n5️⃣ Manage course open requests (teachers)\n6️⃣ Receive exam results\n\n🔐 Very important for security!",
                'keywords' => 'telegram bot, bot nima uchun, bot vazifasi, bot qanday ishlaydi, telegram nima kerak',
                'synonyms' => 'telegram botning vazifasi, botning maqsadi, telegram nima uchun kerak, bot nima uchun ishlatiladi, telegram orqali nima qilish mumkin',
                'category' => 'Telegram bot',
                'priority' => 5,
            ],
            [
                'question' => 'Telegram bot qanday ishlaydi?',
                'question_en' => 'How does the Telegram bot work?',
                'answer' => "🔄 Telegram bot ishlash tartibi:\n\n1️⃣ Saytda telefon raqamingizni kiriting\n2️⃣ Sayt sizga bot havolasini beradi\n3️⃣ Botni oching va \"Start\" bosing\n4️⃣ \"Raqamni ulashish\" tugmasini bosing\n5️⃣ Raqamni tasdiqlang\n\n✅ Shundan keyin bot to'liq ishlaydi!\n\n📞 Kodlar va bildirishnomalar shu yerga keladi.",
                'answer_en' => "🔄 Telegram bot working process:\n\n1️⃣ Enter your phone number on the site\n2️⃣ The site gives you a bot link\n3️⃣ Open the bot and click \"Start\"\n4️⃣ Click \"Share phone number\"\n5️⃣ Confirm your number\n\n✅ After that the bot works fully!\n\n📞 Codes and notifications come here.",
                'keywords' => 'telegram bot, bot qanday ishlaydi, bot qanaqa ishlaydi, bot qanaqa ishlidi, saytning boti, sayt boti, telegram qanday ishlaydi, botdan qanday foydalanish',
                'synonyms' => 'telegram bot ishlashi, bot nima qiladi, bot uchun nima kerak, telegram orqali qanday ishlaydi, bot orqali qanday ishlaydi',
                'category' => 'Telegram bot',
                'priority' => 5,
            ],
            [
                'question' => 'Ro\'yxatdan o\'tishda Telegram qanday ishlatiladi?',
                'question_en' => 'How is Telegram used during registration?',
                'answer' => "📝 Ro'yxatdan o'tish jarayoni:\n\n1️⃣ Saytda Register sahifasiga kiring\n2️⃣ Ism, email, telefon raqamingizni kiriting\n3️⃣ Parol yarating\n4️⃣ \"Ro'yxatdan o'tish\" bosing\n5️⃣ Telegram bot havolasini oching\n6️⃣ Botda \"Start\" bosing\n7️⃣ \"Telefon raqamni ulashish\" bosing\n8️⃣ Tasdiqlang\n\n🎉 Akkauntingiz faollashadi!",
                'answer_en' => "📝 Registration process:\n\n1️⃣ Go to Register page\n2️⃣ Enter name, email, phone\n3️⃣ Create password\n4️⃣ Click \"Register\"\n5️⃣ Open Telegram bot link\n6️⃣ Click \"Start\" in bot\n7️⃣ Click \"Share phone number\"\n8️⃣ Confirm\n\n🎉 Your account will be activated!",
                'keywords' => 'royxatdan otish telegram, register telegram, nega telegram kerak, telegramsiz royxatdan otish, telegram bolmasa, qanday royxatdan otaman',
                'synonyms' => 'telegramsiz ham boladimi, telegram shart emasmi, boshqa yol bormi tasdiqlash uchun, royxatdan otkanda telegram nima qiladi',
                'category' => 'Telegram bot',
                'priority' => 5,
            ],
            [
                'question' => 'Kirishda (login) Telegram qachon so\'raladi?',
                'question_en' => 'When is Telegram requested during login?',
                'answer' => "🔐 Kirishda Telegram:\n\n✅ Agar akkauntingiz Telegram bilan bog'langan bo'lsa — 6 xonali kod yuboriladi\n\n📝 Kodni kiriting va tizimga kiring\n\n🛡️ Bu qo'shimcha xavfsizlik chorasi!\n\nℹ️ Telegram bilan bog'lanmagan bo'lsangiz, to'g'ridan-to'g'ri kirishingiz mumkin.",
                'answer_en' => "🔐 Telegram during login:\n\n✅ If your account is linked with Telegram — 6-digit code is sent\n\n📝 Enter the code and log in\n\n🛡️ This is an extra security measure!\n\nℹ️ If not linked with Telegram, you can log in directly.",
                'keywords' => 'login telegram, kirish telegram, telegram kodi, tasdiqlash kodi, kirishda telegram',
                'synonyms' => 'login qilganda telegram so\'rayapti, kirishda kod keldi, telegram kodi nima, kirish uchun telegram kerakmi',
                'category' => 'Telegram bot',
                'priority' => 5,
            ],
            [
                'question' => 'Parolni tiklash Telegram orqali qanday ishlaydi?',
                'question_en' => 'How does password reset via Telegram work?',
                'answer' => "🔑 Parolni tiklash:\n\n1️⃣ Kirish sahifasidagi \"Parolni unutdingizmi?\" bosing\n2️⃣ Telefon raqamingizni kiriting\n3️⃣ Telegram botga 6 xonali kod keladi\n4️⃣ Kodni saytdagi maydonga kiriting\n5️⃣ Yangi parol yarating\n\n⚠️ Telegram bilan bog'lanmagan bo'lsangiz, admin bilan bog'laning.",
                'answer_en' => "🔑 Password reset:\n\n1️⃣ Click \"Forgot password\" on login page\n2️⃣ Enter your phone number\n3️⃣ 6-digit code is sent to Telegram\n4️⃣ Enter the code on the site\n5️⃣ Create new password\n\n⚠️ If not linked with Telegram, contact admin.",
                'keywords' => 'parol tiklash, parolni unutdim, telegram parol, forgot password, reset password, parolni qanday tiklash',
                'synonyms' => 'parolni tiklash telegram orqali, parolni qayta o\'rnatish, yangi parol qo\'yish, parol yo\'qoldi',
                'category' => 'Telegram bot',
                'priority' => 5,
            ],
            [
                'question' => 'Email yoki telefon o\'zgartirish Telegram orqali qanday tasdiqlanadi?',
                'question_en' => 'How are email/phone changes confirmed via Telegram?',
                'answer' => "🔄 Email/telefon o'zgartirish:\n\n1️⃣ Saytda o'zgartirishni boshlang\n2️⃣ Telegram botga tasdiqlash xabari keladi\n3️⃣ \"Tasdiqlash\" yoki \"Bekor qilish\" bosing\n4️⃣ Faqat tasdiqlagandan keyin o'zgarish kuchga kiradi\n\n✅ Shu bilan xavfsiz o'zgarish amalga oshiriladi!",
                'answer_en' => "🔄 Email/phone change:\n\n1️⃣ Start the change on the site\n2️⃣ Confirmation message comes to Telegram\n3️⃣ Click \"Confirm\" or \"Cancel\"\n4️⃣ Change takes effect only after confirmation\n\n✅ Secure change completed!",
                'keywords' => 'email o\'zgartirish, telefon o\'zgartirish, telegram tasdiqlash, email change, phone change',
                'synonyms' => 'emailni qanday o\'zgartiramman, telefon raqamini o\'zgartirish, yangi email, yangi telefon, email tasdiqlash',
                'category' => 'Telegram bot',
                'priority' => 4,
            ],
            [
                'question' => 'O\'qituvchi uchun: kurs ochish so\'rovi tasdiqlanganda Telegramga xabar keladi?',
                'question_en' => 'For teachers: do they get Telegram notification when course open request is approved?',
                'answer' => "✅ Ha, albatta!\n\n📝 Kurs ochish so'rovi yuborganda:\n• Admin ko'rib chiqadi\n• Tasdiqlansa yoki rad etilsa — xabar keladi\n\n🎉 Tasdiqlanganda:\n\"📚 Kurs ochish so'rovingiz tasdiqlandi!\"\n\n➡️ Endi kurs yaratish imkoniyati ochiladi!",
                'answer_en' => "✅ Yes, absolutely!\n\n📝 When a course open request is submitted:\n• Admin reviews it\n• Whether approved or rejected — notification comes\n\n🎉 When approved:\n\"📚 Your course open request has been approved!\"\n\n➡️ Now you can create courses!",
                'keywords' => 'kurs ochish, o\'qituvchi kurs, kurs so\'rovi, telegram kurs, kurs tasdiqlash',
                'synonyms' => 'kurs ochish so\'rovini qanday yuboraman, o\'qituvchi kurs ochishi uchun nima kerak, kurs ochish ruxsati',
                'category' => 'Telegram bot',
                'priority' => 3,
            ],
            [
                'question' => 'Kursga yozilish arizasi kelganda Telegramda qanday tasdiqlash mumkin?',
                'question_en' => 'How to confirm/reject enrollment via Telegram when an application comes?',
                'answer' => "📝 Kursga yozilish arizasi:\n\n1️⃣ O'quvchi kursga yoziladi\n2️⃣ Kurs egasi (o'qituvchi) Telegramdan xabar oladi\n3️⃣ Xabarda \"✅ Tasdiqlash\" va \"❌ Rad etish\" tugmalari\n4️⃣ Tugmalarni bosib arizani boshqaring\n\n🎉 Tasdiqlanganda o'quvchiga ham xabar boradi!",
                'answer_en' => "📝 Course enrollment application:\n\n1️⃣ Student enrolls in the course\n2️⃣ Course owner (teacher) gets Telegram notification\n3️⃣ Message has \"✅ Approve\" and \"❌ Reject\" buttons\n4️⃣ Click buttons to manage the application\n\n🎉 Student also gets notification when approved!",
                'keywords' => 'kursga yozilish, ariza tasdiqlash, enrollment, kursga qanday yozilaman, kurs arizasi',
                'synonyms' => 'kursga yozildim, kurs arizasini qanday ko\'raman, o\'qituvchi arizani qanday tasdiqlaydi, kursga qabul qilish',
                'category' => 'Telegram bot',
                'priority' => 3,
            ],
            [
                'question' => 'Botga yozdim, javob kelmayapti / bot ishlamayapti',
                'question_en' => 'I wrote to the bot, no response / bot not working',
                'answer' => "⚠️ Bot muammosi:\n\n🔧 Tekshiring:\n1️⃣ Internet aloqasini tekshiring\n2️⃣ Botni qayta oching\n3️⃣ /start buyrug'ini yuboring\n4️⃣ Telefon raqamingizni qayta ulang\n5️⃣ Saytga qaytib urinib ko'ring\n\n📞 Agar muammo davom etsa:\nAdmin bilan Aloqa sahifasi orqali bog'laning!",
                'answer_en' => "⚠️ Bot problem:\n\n🔧 Check:\n1️⃣ Check internet connection\n2️⃣ Reopen the bot\n3️⃣ Send /start command\n4️⃣ Re-share your phone number\n5️⃣ Go back to the site and try again\n\n📞 If the problem persists:\nContact admin through the Contact page!",
                'keywords' => 'bot ishlamayapti, javob kelmayapti, bot muammo, telegram ishlamayapti, bot xato, bot tutilmoqda',
                'synonyms' => 'bot sekin ishlayapti, bot o\'chirilganmi, bot vaqtincha ishlamayapti, telegram bot xatosi, botga kira olmayapman',
                'category' => 'Telegram bot',
                'priority' => 3,
            ],
            [
                'question' => 'Botning nomi/username qanday topiladi?',
                'question_en' => 'How to find the bot\'s name/username?',
                'answer' => "🔍 Botni topish:\n\n1️⃣ Saytda ro'yxatdan o'tish yoki kirish jarayonida bot havolasi ko'rinadi\n2️⃣ Havola formati: t.me/bot_username\n3️⃣ Telegramda qidiruvga bot nomini kiriting\n\n💡 Maslahat: Botni \"Favorites\" ga qo'shing!",
                'answer_en' => "🔍 Finding the bot:\n\n1️⃣ Bot link is displayed during registration or login\n2️⃣ Link format: t.me/bot_username\n3️⃣ Enter bot name in Telegram search\n\n💡 Tip: Add the bot to \"Favorites\"!",
                'keywords' => 'bot username, bot nomi, qaysi botga yozaman, botni qanday topaman, t.me, bot havolasi',
                'synonyms' => 'botning to\'liq nomi, botni qidirish, telegram botni qanday topsam bo\'ladi, qaysi bot bilan ishlaymiz',
                'category' => 'Telegram bot',
                'priority' => 2,
            ],
            [
                'question' => 'Telegramsiz saytdan foydalanish mumkinmi?',
                'question_en' => 'Can I use the site without Telegram?',
                'answer' => "✅ Ha, mumkin!\n\n🌐 Saytning barcha asosiy funksiyalaridan foydalanish mumkin:\n• Kurslarni ko'rish\n• Yangiliklarni o'qish\n• Taqvimni ko'rish\n• Chatdan foydalanish\n\n⚠️ Lekin ro'yxatdan o'tish uchun Telegram kerak.\n\n💡 Telegram bilan bog'lanmagan bo'lsangiz, kirish to'g'ridan-to'g'ri ishlaydi.",
                'answer_en' => "✅ Yes, you can!\n\n🌐 You can use all main features:\n• View courses\n• Read news\n• Check calendar\n• Use chat\n\n⚠️ However, Telegram is needed for registration.\n\n💡 If not linked with Telegram, login works directly.",
                'keywords' => 'telegramsiz, telegram kerakmi, telegram bilmasdan, telegramsiz foydalanish',
                'synonyms' => 'telegram yo\'q, telegramdan foydalanmayman, telegram shartmi, telegramsiz ham bo\'ladimi',
                'category' => 'Telegram bot',
                'priority' => 3,
            ],

            // ═══════════════════════════════════════════════════════════════
            // KURSLAR
            // ═══════════════════════════════════════════════════════════════
            [
                'question' => 'Kurs qanday ochiladi (o\'qituvchi uchun)?',
                'question_en' => 'How to create a course (for teachers)?',
                'answer' => "📚 Kurs ochish (o'qituvchilar uchun):\n\n1️⃣ Kurs ochish so'rovini yuboring\n   📍 Profil → Faoliyat\n2️⃣ Admin tasdiqlashini kuting\n3️⃣ Tasdiqlangandan keyin \"Yangi kurs yaratish\" tugmasi paydo bo'ladi\n4️⃣ Kurs nomi, tavsifi, narxi, muddatini kiriting\n5️⃣ \"Saqlash\" bosing\n\n🎉 Kurs tayyor!",
                'answer_en' => "📚 Create course (for teachers):\n\n1️⃣ Submit course open request\n   📍 Profile → Activity\n2️⃣ Wait for admin approval\n3️⃣ After approval, \"Create new course\" button appears\n4️⃣ Enter course name, description, price, duration\n5️⃣ Click \"Save\"\n\n🎉 Course ready!",
                'keywords' => 'kurs ochish, o\'qituvchi kurs, kurs yaratish, yangi kurs, kurs qanday ochiladi',
                'synonyms' => 'o\'qituvchi kurs ochishi uchun nima kerak, kurs ochish so\'rovi, kurs yaratish jarayoni, kurs ochish ruxsati',
                'category' => 'Kurslar',
                'priority' => 4,
            ],
            [
                'question' => 'Kursga qanday yoziladi (o\'quvchi uchun)?',
                'question_en' => 'How to enroll in a course (for students)?',
                'answer' => "📝 Kursga yozilish:\n\n1️⃣ \"Kurslar\" bo'limiga kiring\n2️⃣ Kerakli kursni toping\n3️⃣ \"Yozilish\" tugmasini bosing\n4️⃣ Fan darajasini tanlang\n5️⃣ Izoh qoldiring (ixtiyoriy)\n6️⃣ \"Yuborish\" bosing\n\n📬 Ariza kurs egasiga yuboriladi\n✅ Telegram orqali tasdiqlash/rad etish\n🎉 Tasdiqlangandan keyin kursda qatnashing!",
                'answer_en' => "📝 Enroll in course:\n\n1️⃣ Go to \"Courses\" section\n2️⃣ Find the desired course\n3️⃣ Click \"Enroll\"\n4️⃣ Select subject level\n5️⃣ Leave a note (optional)\n6️⃣ Click \"Submit\"\n\n📬 Application sent to course owner\n✅ Approve/reject via Telegram\n🎉 Attend the course after approval!",
                'keywords' => 'kursga yozilish, kursga qanday yozilaman, enrollment, kursga ariza, kursga kirish',
                'synonyms' => 'kursga yozildim, kursga qanday qo\'shilaman, kursga a\'zo bo\'lish, kursga qabul qilish',
                'category' => 'Kurslar',
                'priority' => 4,
            ],
            [
                'question' => 'Kurs narxi va muddati qanday ko\'rinadi?',
                'question_en' => 'How are course price and duration displayed?',
                'answer' => "💰 Kurs narxi va muddati:\n\n📋 Har bir kursning sahifasida ko'rsatilgan\n📊 Kurslar ro'yxatida ham mavjud\n🆓 Ba'zi kurslar bepul bo'lishi mumkin\n\n💡 Batafsil ma'lumot uchun kurs sahifasiga kiring!",
                'answer_en' => "💰 Course price and duration:\n\n📋 Displayed on each course page\n📊 Available in course listings\n🆓 Some courses may be free\n\n💡 Visit the course page for detailed information!",
                'keywords' => 'kurs narxi, kurs muddati, kurs qancha, kurs puli, kurs vaqti',
                'synonyms' => 'kurs qancha turadi, kurs narxi qancha, kurs davomiyligi, kurs necha oy',
                'category' => 'Kurslar',
                'priority' => 2,
            ],
            [
                'question' => 'Ariza qachon tasdiqlanadi?',
                'question_en' => 'When is the application approved?',
                'answer' => "⏰ Ariza tasdiqlash muddati:\n\n👨‍🏫 Kurs egasi (o'qituvchi) tomonidan tasdiqlanadi\n📅 Odatda 1-2 kun ichida javob beriladi\n📱 Tasdiqlanganda Telegramga xabar keladi\n\n⚠️ 2 kundan ortiq javob bo'lmasa, admin bilan bog'laning!",
                'answer_en' => "⏰ Application approval time:\n\n👨‍🏫 Approved by course owner (teacher)\n📅 Usually responds within 1-2 days\n📱 Telegram notification when approved\n\n⚠️ If no response after 2 days, contact admin!",
                'keywords' => 'ariza tasdiqlash, qachon tasdiqlanadi, ariza vaqti, kutish vaqti',
                'synonyms' => 'arizani necha kunda ko\'rib chiqadi, tasdiqlash muddati, ariza qachon javob beradi',
                'category' => 'Kurslar',
                'priority' => 2,
            ],

            // ═══════════════════════════════════════════════════════════════
            // IMTIHONLAR
            // ═══════════════════════════════════════════════════════════════
            [
                'question' => 'Imtihon qanday ishlaydi?',
                'question_en' => 'How do exams work?',
                'answer' => "📝 Imtihon tartibi:\n\n1️⃣ \"Imtihonlar\" bo'limiga kiring\n2️⃣ Faol imtihonni tanlang\n3️⃣ \"Boshlash\" bosing\n4️⃣ Savollarga javob bering\n5️⃣ Vaqt tugashidan oldin \"Yakunlash\" bosing\n\n⏱️ Natija avtomatik hisoblanadi!\n📊 Telegramga ham yuboriladi!",
                'answer_en' => "📝 Exam process:\n\n1️⃣ Go to \"Exams\" section\n2️⃣ Select an active exam\n3️⃣ Click \"Start\"\n4️⃣ Answer questions\n5️⃣ Click \"Finish\" before time runs out\n\n⏱️ Results calculated automatically!\n📊 Also sent to Telegram!",
                'keywords' => 'imtihon, imtihon qanday ishlaydi, test, sinov, exam, examination',
                'synonyms' => 'imtihonni qanday topshiraman, imtihon qanday o\'tadi, test topshirish, imtihon boshlash',
                'category' => 'Imtihonlar',
                'priority' => 4,
            ],
            [
                'question' => 'Imtihonda vaqt chegarasi bormi?',
                'question_en' => 'Is there a time limit in exams?',
                'answer' => "⏱️ Ha, vaqt chegarasi bor!\n\n🕐 Vaqt imtihon boshlanganda boshlanadi\n⏰ Avtomatik tugaydi\n📧 Vaqt tugaganda javoblar avtomatik yuboriladi\n\n💡 Vaqtni kuzatib turing!",
                'answer_en' => "⏱️ Yes, there is a time limit!\n\n🕐 Time starts when exam begins\n⏰ Ends automatically\n📧 When time is up, answers auto-submitted\n\n💡 Keep track of time!",
                'keywords' => 'imtihon vaqti, vaqt chegarasi, necha daqiqa, vaqt tugashi, time limit',
                'synonyms' => 'imtihon necha daqiqa davom etadi, vaqt cheklovi bormi, imtihon vaqti tugadi',
                'category' => 'Imtihonlar',
                'priority' => 3,
            ],
            [
                'question' => 'Imtihon natijasini qayerdan ko\'rish mumkin?',
                'question_en' => 'Where can I see exam results?',
                'answer' => "📊 Imtihon natijalari:\n\n1️⃣ \"Imtihonlar\" bo'limiga kiring\n2️⃣ Yakunlangan imtihonlar ro'yxatini ko'ring\n3️⃣ Ball va o'tgan/o'tmagan holati ko'rinadi\n\n📱 Telegramga ham natija xabari yuboriladi!",
                'answer_en' => "📊 Exam results:\n\n1️⃣ Go to \"Exams\" section\n2️⃣ View completed exams list\n3️⃣ Score and pass/fail status is shown\n\n📱 Results also sent to Telegram!",
                'keywords' => 'natija, imtihon natijasi, ball, o\'tdimmi, exam result, score',
                'synonyms' => 'imtihon ballarini qayerdan ko\'raman, natijani qanday bilaman, imtihon natijasini ko\'rish',
                'category' => 'Imtihonlar',
                'priority' => 3,
            ],
            [
                'question' => 'Matnli javoblar qanday baholanadi?',
                'question_en' => 'How are text answers graded?',
                'answer' => "✍️ Matnli javoblar baholash:\n\n👨‍🏫 O'qituvchi tomonidan qo'lda baholanadi\n📝 Imtihon yakunlangandan keyin ko'rib chiqiladi\n✅ To'g'ri/noto'g'ri deb belgilanadi\n📊 Yakuniy natija baholash tugagandan keyin ko'rinadi",
                'answer_en' => "✍️ Text answer grading:\n\n👨‍🏫 Graded manually by the teacher\n📝 Reviewed after exam completion\n✅ Marked as correct/incorrect\n📊 Final result appears after grading",
                'keywords' => 'matnli javob, qo\'lda baholash, text answer, essay, open answer',
                'synonyms' => 'matnli javoblar qanday baholanadi, ochiq savol javoblari, yozma javoblar bahosi',
                'category' => 'Imtihonlar',
                'priority' => 2,
            ],

            // ═══════════════════════════════════════════════════════════════
            // CHAT
            // ═══════════════════════════════════════════════════════════════
            [
                'question' => 'Global va guruh chat farqi nima?',
                'question_en' => 'What\'s the difference between global and group chat?',
                'answer' => "💬 Chat turlari:\n\n🌍 Global chat:\n• Barcha foydalanuvchilar uchun\n• Umumiy suhbat\n\n👥 Guruh chat:\n• Faqat ma'lum bir guruh uchun\n• Kurs yoki sinf a'zolari\n• Maxfiy suhbat mumkin",
                'answer_en' => "💬 Chat types:\n\n🌍 Global chat:\n• For all users\n• Common conversation\n\n👥 Group chat:\n• For specific group only\n• Course or class members\n• Private conversation possible",
                'keywords' => 'global chat, guruh chat, chat farqi, umumiy chat, private chat',
                'synonyms' => 'global chat nima, guruh chat nima, chat turlari, qaysi chat',
                'category' => 'Chat',
                'priority' => 3,
            ],
            [
                'question' => 'Guruh chatni kim ochishi mumkin?',
                'question_en' => 'Who can create a group chat?',
                'answer' => "👥 Guruh chat yaratish:\n\n👨‍💼 Faqat admin yoki kurs egasi (o'qituvchi) ochishi mumkin\n\n👤 Oddiy foydalanuvchilar:\n• Faqat guruh a'zosi sifatida qo'shilishi mumkin\n• Guruh yarata olmaydi",
                'answer_en' => "👥 Group chat creation:\n\n👨‍💼 Only admin or course owner (teacher) can create\n\n👤 Regular users:\n• Can only join as group members\n• Cannot create groups",
                'keywords' => 'guruh ochish, chat yaratish, group create, chat kim ochadi',
                'synonyms' => 'guruh chatni qanday ochaman, chat guruhini yaratish, yangi guruh',
                'category' => 'Chat',
                'priority' => 2,
            ],
            [
                'question' => 'Guruh chatda nechta a\'zo bo\'lishi mumkin?',
                'question_en' => 'How many members can be in a group chat?',
                'answer' => "👥 Guruh a'zolari:\n\n♾️ A'zolar soni cheklangan emas!\n\n📚 Kurs a'zolari avtomatik ravishda qo'shilishi mumkin\n✅ Har kim qo'shila oladi",
                'answer_en' => "👥 Group members:\n\n♾️ No limit on number of members!\n\n📚 Course members can automatically join\n✅ Anyone can join",
                'keywords' => 'guruh a\'zolari, necha kishi, a\'zo soni, member limit',
                'synonyms' => 'guruhda nechta odam bo\'ladi, a\'zolar soni cheklanganmi, guruh limiti',
                'category' => 'Chat',
                'priority' => 1,
            ],
            [
                'question' => 'Yopiq guruhga qanday qo\'shiladi?',
                'question_en' => 'How to join a private group?',
                'answer' => "🔒 Yopiq guruhga qo'shilish:\n\n1️⃣ Guruh egasidan (o'qituvchi/admin) ruxsat olish kerak\n2️⃣ Kursga yozilganingizdan keyin avtomatik qo'shilasiz\n3️⃣ Yoki admin orqali qo'shish mumkin",
                'answer_en' => "🔒 Joining a private group:\n\n1️⃣ Need permission from group owner (teacher/admin)\n2️⃣ Automatically join after enrolling in course\n3️⃣ Or can be added by admin",
                'keywords' => 'yopiq guruh, private group, guruhga qo\'shilish, qo\'shish',
                'synonyms' => 'yopiq guruhga qanday kiraman, guruhga qo\'shilish yo\'li, maxfiy guruh',
                'category' => 'Chat',
                'priority' => 1,
            ],

            // ═══════════════════════════════════════════════════════════════
            // XAYRIYA
            // ═══════════════════════════════════════════════════════════════
            [
                'question' => 'Qanday xayriya qilish mumkin?',
                'question_en' => 'How can I donate?',
                'answer' => "❤️ Xayriya qilish:\n\n1️⃣ \"Xayriya\" bo'limiga kiring\n2️⃣ Homiylik darajasini tanlang:\n   🥉 Bronza\n   🥈 Kumush\n   🥇 Oltin\n   💎 Platina\n3️⃣ To'lov usulini tanlang:\n   💳 Click\n   💳 Payme\n   💳 Stripe\n4️⃣ To'lovni amalga oshiring\n\n🎉 Muvaffaqiyatli to'lovdan keyin daraja beriladi!",
                'answer_en' => "❤️ How to donate:\n\n1️⃣ Go to \"Donation\" section\n2️⃣ Choose donor rank:\n   🥉 Bronze\n   🥈 Silver\n   🥇 Gold\n   💎 Platinum\n3️⃣ Select payment method:\n   💳 Click\n   💳 Payme\n   💳 Stripe\n4️⃣ Complete payment\n\n🎉 Rank assigned after successful payment!",
                'keywords' => 'xayriya, donate, homiylik, pul berish, to\'lov, payment',
                'synonyms' => 'xayriya qanday qilaman, qanday homiy bo\'laman, pul qanday yuboraman, to\'lov qilish',
                'category' => 'Xayriya',
                'priority' => 4,
            ],
            [
                'question' => 'Homiylik darajalari nima beradi?',
                'question_en' => 'What do donor ranks provide?',
                'answer' => "🏆 Homiylik darajalari:\n\n🥉 Bronza — maxsus avatar ramkasi\n🥈 Kumush — maxsus rang sxemasi\n🥇 Oltin — premium temalar\n💎 Platina — barcha premium imkoniyatlar\n\n🎁 Har bir daraja chatda:\n• Maxsus badge\n• Maxsus ranglar\n• VIP imtiyozlar",
                'answer_en' => "🏆 Donor ranks:\n\n🥉 Bronze — special avatar frame\n🥈 Silver — special color scheme\n🥇 Gold — premium themes\n💎 Platinum — all premium features\n\n🎁 Each rank provides:\n• Special badge\n• Special colors\n• VIP privileges",
                'keywords' => 'homiylik darajasi, donor rank, nima beradi, bonus, imtiyoz',
                'synonyms' => 'homiylik darajalari qanday, donor darajalari, xayriya nima beradi, homiylik bonuslari',
                'category' => 'Xayriya',
                'priority' => 3,
            ],
            [
                'question' => 'Aktivatsiya kaliti nima uchun kerak?',
                'question_en' => 'What is the activation key for?',
                'answer' => "🔑 Aktivatsiya kaliti:\n\n⏰ Homiylik darajasini qo'shimcha muddatga uzaytiradi\n🔓 Premium imkoniyatlarni ochadi\n\n📥 Kalitni olish:\n• Admin orqali\n• Homiylik tizimidan",
                'answer_en' => "🔑 Activation key:\n\n⏰ Extends donor rank for additional period\n🔓 Unlocks premium features\n\n📥 How to get:\n• Through admin\n• From donation system",
                'keywords' => 'aktivatsiya kaliti, activation key, kalit nima uchun, kalit qanday ishlaydi',
                'synonyms' => 'aktivatsiya kalitini qanday olaman, kalitdan qanday foydalanaman, kalit ishlatish',
                'category' => 'Xayriya',
                'priority' => 1,
            ],

            // ═══════════════════════════════════════════════════════════════
            // FIKR-TAKLIF
            // ═══════════════════════════════════════════════════════════════
            [
                'question' => 'Taklif qanday qoldiriladi?',
                'question_en' => 'How to submit a feature request?',
                'answer' => "💡 Fikr-taklif qoldirish:\n\n1️⃣ \"Fikr-taklif\" bo'limiga kiring\n2️⃣ \"Yangi taklif\" tugmasini bosing\n3️⃣ Sarlavha va tavsifni kiriting\n4️⃣ \"Yuborish\" bosing\n\n✅ Taklifingiz saytda ko'rinadi\n🗳️ Boshqalar ovoz berishi mumkin",
                'answer_en' => "💡 Submit feature request:\n\n1️⃣ Go to \"Feature Requests\" section\n2️⃣ Click \"New Request\"\n3️⃣ Enter title and description\n4️⃣ Click \"Submit\"\n\n✅ Your request appears on the site\n🗳️ Others can vote",
                'keywords' => 'taklif, fikr, suggestion, feature request, yangi taklif, qanday qoldirish',
                'synonyms' => 'taklifni qanday yuboraman, fikr qoldirish, taklif berish, yangi g\'oya',
                'category' => 'Fikr-taklif',
                'priority' => 3,
            ],
            [
                'question' => 'Ovoz berish qanday ishlaydi?',
                'question_en' => 'How does voting work?',
                'answer' => "🗳️ Ovoz berish:\n\n👆 Bosing — ovoz qo'shildi\n👆 Yana bosing — ovoz bekor qilindi\n\n📏 Qoidalar:\n• Har bir foydalanuvchi bitta taklifga faqat bitta ovoz\n• Ko'p ovoz olgan takliflar yuqoriroq ko'rinadi",
                'answer_en' => "🗳️ Voting:\n\n👆 Click once — vote added\n👆 Click again — vote removed\n\n📏 Rules:\n• Each user can give one vote per request\n• Requests with more votes appear higher",
                'keywords' => 'ovoz berish, vote, like, qo\'llab-quvvatlash, taklif ovoz',
                'synonyms' => 'taklifga qanday ovoz beraman, ovoz berish tartibi, nechta ovoz berish mumkin',
                'category' => 'Fikr-taklif',
                'priority' => 2,
            ],
            [
                'question' => 'Taklif holati (status) nimani bildiradi?',
                'question_en' => 'What do the request statuses mean?',
                'answer' => "📊 Taklif holatlari:\n\n⏳ Kutilmoqda — hali ko'rib chiqilmagan\n✅ Qabul qilindi — admin tasdiqlagan\n❌ Rad etildi — rad etilgan\n🚀 Ishga tushirildi — amalga oshirilgan\n\n🎨 Har bir holat rang bilan belgilanadi",
                'answer_en' => "📊 Request statuses:\n\n⏳ Pending — not yet reviewed\n✅ Accepted — approved by admin\n❌ Rejected — rejected\n🚀 Implemented — completed\n\n🎨 Each status indicated by color",
                'keywords' => 'status, holat, taklif holati, pending, accepted, rejected, implemented',
                'synonyms' => 'taklif holatlari nima, status nima demak, qabul qilindi yoki rad etildi',
                'category' => 'Fikr-taklif',
                'priority' => 1,
            ],

            // ═══════════════════════════════════════════════════════════════
            // BOOKMARK
            // ═══════════════════════════════════════════════════════════════
            [
                'question' => 'Nimalarni saqlash (bookmark) mumkin?',
                'question_en' => 'What can I bookmark?',
                'answer' => "🔖 Saqlash mumkin:\n\n📰 Yangiliklar (postlar)\n👨‍🏫 O'qituvchilar\n📚 Kurslar\n\n👆 Har bir element yonidagi \"Saqlash\" tugmasini bosing",
                'answer_en' => "🔖 What to bookmark:\n\n📰 News posts\n👨‍🏫 Teachers\n📚 Courses\n\n👆 Click \"Save\" button next to each element",
                'keywords' => 'bookmark, saqlash, saved, qayd, xotira, post saqlash',
                'synonyms' => 'nimalarni saqlash mumkin, bookmark qanday ishlaydi, saqlanganlar qayerda',
                'category' => 'Bookmark',
                'priority' => 2,
            ],
            [
                'question' => 'Saqlanganlarni qayerdan ko\'rish mumkin?',
                'question_en' => 'Where can I see my bookmarks?',
                'answer' => "📂 Saqlanganlar:\n\n📍 Profil → Saqlanganlar\n\n📋 Bu yerda:\n• Barcha saqlangan postlar\n• O'qituvchilar\n• Kurslar\n\n🗑️ O'chirish uchun yana \"Saqlash\" bosing",
                'answer_en' => "📂 Bookmarks:\n\n📍 Profile → Bookmarks\n\n📋 Here you can see:\n• All saved posts\n• Teachers\n• Courses\n\n🗑️ To remove, click \"Save\" again",
                'keywords' => 'saqlanganlar, bookmarks, saved items, qayerda ko\'rish, profil saqlanganlar',
                'synonyms' => 'saqlanganlarim qayerda, bookmark sahifasi, saqlanganlarni qanday ko\'raman',
                'category' => 'Bookmark',
                'priority' => 2,
            ],

            // ═══════════════════════════════════════════════════════════════
            // ROLLAR
            // ═══════════════════════════════════════════════════════════════
            [
                'question' => 'O\'quvchi, o\'qituvchi, ota-ona akkauntlari nimasi bilan farqlanadi?',
                'question_en' => 'How do student, teacher, and parent accounts differ?',
                'answer' => "👤 Akkaunt turlari:\n\n📚 O'quvchi:\n• Kurslarga yozilish\n• Imtihon topshirish\n• Chatda yozish\n\n👨‍🏫 O'qituvchi:\n• Kurs ochish\n• Kursni boshqarish\n• Arizalarni tasdiqlash\n\n👨‍👩‍👧 Ota-ona:\n• Faqat farzand ma'lumotlarini ko'rish\n• Kursga yozila olmaydi\n• Imtihon topshira olmaydi",
                'answer_en' => "👤 Account types:\n\n📚 Student:\n• Enroll in courses\n• Take exams\n• Write in chat\n\n👨‍🏫 Teacher:\n• Create courses\n• Manage courses\n• Approve applications\n\n👨‍👩‍👧 Parent:\n• Only view child's information\n• Cannot enroll in courses\n• Cannot take exams",
                'keywords' => 'rollar, akkaunt turlari, o\'quvchi, o\'qituvchi, ota-ona, farq, role',
                'synonyms' => 'qaysi akkaunt qanday ishlaydi, akkaunt turlari nima, o\'quvchi va o\'qituvchi farqi, ota-ona akkaunti',
                'category' => 'Rollar',
                'priority' => 3,
            ],
            [
                'question' => 'Ota-ona akkaunti qanday olinadi?',
                'question_en' => 'How to get a parent account?',
                'answer' => "👨‍👩‍👧 Ota-ona akkaunti:\n\n📝 Ro'yxatdan o'tish jarayonida \"Ota-onaman\" belgisini qo'ying\n\n📋 Imkoniyatlari:\n✅ Farzandning ma'lumotlarini ko'rish\n❌ Kursga yozilish mumkin emas\n❌ Imtihon topshirish mumkin emas",
                'answer_en' => "👨‍👩‍👧 Parent account:\n\n📝 Check \"I am a parent\" during registration\n\n📋 Features:\n✅ View child's information\n❌ Cannot enroll in courses\n❌ Cannot take exams",
                'keywords' => 'ota-ona akkaunti, parent account, ota-onaman, qanday olish',
                'synonyms' => 'ota-ona akkauntini qanday olaman, ota-onaman deb belgilash, parent account qanday',
                'category' => 'Rollar',
                'priority' => 2,
            ],

            // ═══════════════════════════════════════════════════════════════
            // TAQVIM
            // ═══════════════════════════════════════════════════════════════
            [
                'question' => 'Taqvimda nimalar ko\'rsatiladi?',
                'question_en' => 'What is shown on the calendar?',
                'answer' => "📅 Taqvim:\n\n🎉 Muhim tadbirlar\n📝 Imtihon sanalari\n📚 Kurs boshlanish sanalari\n📋 Boshqa voqealar\n\n💡 Har bir tadbir haqida batafsil ma'lumot olish mumkin",
                'answer_en' => "📅 Calendar:\n\n🎉 Important events\n📝 Exam dates\n📚 Course start dates\n📋 Other events\n\n💡 Detailed information available for each event",
                'keywords' => 'taqvim, calendar, tadbir, sana, voqea, schedule',
                'synonyms' => 'taqvimda nima bor, tadbirlar qayerda, sanalar, imtihon sanalari',
                'category' => 'Taqvim',
                'priority' => 2,
            ],
            [
                'question' => 'Tadbir haqida qanday eslatma olish mumkin?',
                'question_en' => 'How to get reminders about events?',
                'answer' => "🔔 Eslatmalar:\n\n📱 Telegram orqali yuboriladi\n✅ Telegram bilan bog'langan bo'lsangiz — eslatma keladi\n📅 Taqvim sahifasida ham ko'rishingiz mumkin",
                'answer_en' => "🔔 Reminders:\n\n📱 Sent via Telegram\n✅ If linked with Telegram — you get reminders\n📅 Also visible on calendar page",
                'keywords' => 'eslatma, reminder, bildirishnoma, tadbir eslatmasi, notification',
                'synonyms' => 'tadbir haqida qanday bilaman, eslatma olish, bildirishnoma olish',
                'category' => 'Taqvim',
                'priority' => 1,
            ],

            // ═══════════════════════════════════════════════════════════════
            // TEXNIK MUAMMOLAR
            // ═══════════════════════════════════════════════════════════════
            [
                'question' => 'Sayt ishlamayapti / xatolik chiqdi',
                'question_en' => 'Site not working / error occurred',
                'answer' => "⚠️ Sayt muammosi:\n\n🔧 Tekshiring:\n1️⃣ Internet aloqasini tekshiring\n2️⃣ Brauzerni qayta yuklang\n3️⃣ Boshqa brauzerda sinab ko'ring\n4️⃣ Cache va cookieslarni tozalang\n\n📞 Agar muammo davom etsa:\nAdmin bilan Aloqa sahifasi orqali bog'laning!",
                'answer_en' => "⚠️ Site problem:\n\n🔧 Check:\n1️⃣ Check internet connection\n2️⃣ Reload the browser\n3️⃣ Try another browser\n4️⃣ Clear cache and cookies\n\n📞 If the problem persists:\nContact admin through the Contact page!",
                'keywords' => 'sayt ishlamayapti, xatolik, error, muammo, yuklanmayapti, qora ekran',
                'synonyms' => 'sayt ochilmayapti, sahifa xatosi, 500 xatosi, sayz sekin ishlayapti, sayt tutilmoqda',
                'category' => 'Texnik',
                'priority' => 2,
            ],
            [
                'question' => 'Akkauntim bloklangan',
                'question_en' => 'My account is blocked',
                'answer' => "🔒 Akkaunt bloklangan:\n\n👨‍💻 Admin bilan Aloqa sahifasi orqali bog'laning\n❓ Bloklash sababini so'rang\n🔧 Tuzatishga harakat qiling\n\n⚠️ Bloklangan akkaunt bilan tizimga kirish mumkin emas",
                'answer_en' => "🔒 Account blocked:\n\n👨‍💻 Contact admin through the Contact page\n❓ Ask for the reason for blocking\n🔧 Try to resolve it\n\n⚠️ Cannot log in with a blocked account",
                'keywords' => 'bloklangan, blocked, akkaunt blok, tizimga kira olmayapman',
                'synonyms' => 'akkauntim o\'chirilganmi, blokdan qanday chiqaman, blokni qanday ochaman',
                'category' => 'Texnik',
                'priority' => 2,
            ],
        ];

        foreach ($data as $item) {
            // Agar priority ko'rsatilmagan bo'lsa, default 0
            if (!isset($item['priority'])) {
                $item['priority'] = 0;
            }
            // Agar synonyms ko'rsatilmagan bo'lsa, bo'sh qator
            if (!isset($item['synonyms'])) {
                $item['synonyms'] = '';
            }

            \App\Models\AiKnowledge::updateOrCreate(
                ['question' => $item['question']],
                $item
            );
        }
    }
}
