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
            // --- MAKTAB HAQIDA ---
            [
                'question' => 'Maktab qachon tashkil topgan?',
                'question_en' => 'When was the school established?',
                'answer' => '81-maktab 1963-yildan buyon faoliyat yuritib kelmoqda. Yarim asrdan ortiq vaqt davomida maktab minglab o\'quvchilarga sifatli ta\'lim berib kelmoqda.',
                'answer_en' => 'School 81 has been operating since 1963. For over half a century, the school has been providing quality education to thousands of students.',
                'keywords' => 'tashkil, qachon, ochilgan, sana, yil, tarix',
                'category' => 'Maktab',
            ],
            [
                'question' => 'Maktab manzili qayerda?',
                'question_en' => 'Where is the school located?',
                'answer' => 'Maktabimiz Toshkent shahar, Uchtepa tumani, Paxtakor MFY, Ali Qushchi ko\'chasi 3-uyda joylashgan. Lokatsiya tuman markazidan taxminan 1,4 km uzoqlikda.',
                'answer_en' => 'Our school is located at 3 Ali Qushchi Street, Pakhtakor MFY, Uchtepa district, Tashkent city. It is about 1.4 km from the district center.',
                'keywords' => 'manzil, qayerda, joylashgan, lokatsiya, xarita, uchtepa',
                'category' => 'Maktab',
            ],
            [
                'question' => 'Maktab direktori kim?',
                'question_en' => 'Who is the school principal?',
                'answer' => 'Maktab direktori — Xaydarova Ziyoda Tolipovna. U menejerlik sertifikatiga ega tajribali rahbar hisoblanadi.',
                'answer_en' => 'The school principal is Ziyoda Tolipovna Khaydarova. She is an experienced leader with a management certificate.',
                'keywords' => 'direktor, rahbar, boshliq, kim, ismi',
                'category' => 'Maktab',
            ],
            [
                'question' => 'Maktabda necha nafar o\'quvchi o\'qiydi?',
                'question_en' => 'How many students study at the school?',
                'answer' => 'Hozirgi kunda maktabda 2097 nafar o\'quvchi tahsil olmoqda. Shundan 1566 nafari o\'zbek sinflarida, 531 nafari esa rus sinflarida o\'qiydi.',
                'answer_en' => 'Currently, 2,097 students study at the school. Of these, 1,566 are in Uzbek classes and 531 are in Russian classes.',
                'keywords' => 'o\'quvchilar soni, necha kishi, oquvchi, talaba',
                'category' => 'Maktab',
            ],
            [
                'question' => 'Maktabda necha nafar o\'qituvchi bor?',
                'question_en' => 'How many teachers are there?',
                'answer' => 'Maktabda 90 nafar yuqori malakali pedagog faoliyat yuritadi. Ularning barchasi oliy ma\'lumotli, 21 nafari oliy toifali va 26 nafari milliy/xalqaro sertifikatlarga ega.',
                'answer_en' => 'There are 90 highly qualified educators at the school. All of them have higher education, 21 are of the highest category, and 26 hold national/international certificates.',
                'keywords' => 'ustozlar soni, oqituvchilar, pedagoglar, necha nafar',
                'category' => 'Maktab',
            ],
            [
                'question' => 'Maktab binosi va sharoitlari qanday?',
                'question_en' => 'What are the school facilities like?',
                'answer' => 'Maktab 960 o\'rinli binoga ega, umumiy yer maydoni 16 000 m2. Binoda 3 ta kompyuter sinfi (45 ta kompyuter), fizika, kimyo, biologiya laboratoriyalari, 120 o\'rinli oshxona, 150 o\'rinli faollar zali va sport zali mavjud.',
                'answer_en' => 'The school has a building for 960 seats with a total area of 16,000 m2. There are 3 computer labs (45 computers), physics, chemistry, and biology labs, a 120-seat canteen, a 150-seat assembly hall, and a gym.',
                'keywords' => 'sharoitlar, bino, oshxona, sport zal, kompyuter, laboratoriya',
                'category' => 'Maktab',
            ],

            // --- ALOQA ---
            [
                'question' => 'Maktab bilan qanday bog\'lanish mumkin?',
                'question_en' => 'How to contact the school?',
                'answer' => "Maktab bilan bog'lanish uchun +99890-958-00-67 raqamiga qo'ng'iroq qilishingiz yoki saytdagi 'Aloqa' bo'limi orqali xabar yuborishingiz mumkin. Shuningdek, rasmiy email manzilini ham Aloqa sahifasidan topasiz.",
                'answer_en' => "To contact the school, you can call +99890-958-00-67 or send a message through the 'Contact' section on the website. You can also find the official email on the Contact page.",
                'keywords' => 'boglanish, aloqa, telefon, nomer, xabar, murojaat',
                'category' => 'Aloqa',
            ],
            [
                'question' => 'Maktab telefon raqami nima?',
                'question_en' => 'What is the school phone number?',
                'answer' => 'Maktabning rasmiy telefon raqami: +99890-958-00-67. Ushbu raqam orqali qabul va boshqa masalalar bo\'yicha ma\'lumot olishingiz mumkin.',
                'answer_en' => 'The official school phone number is +99890-958-00-67. You can get information about admissions and other issues through this number.',
                'keywords' => 'telefon, raqam, nomer, aloqa, contact',
                'category' => 'Aloqa',
            ],
            [
                'question' => 'Maktab emaili nima?',
                'question_en' => 'What is the school email address?',
                'answer' => "Maktabning rasmiy email manzili 'Aloqa' bo'limida ko'rsatilgan. Murojaatlarni bevosita saytdagi forma orqali yuborish tezroq ko'rib chiqilishiga yordam beradi.",
                'answer_en' => "The official school email address is listed in the 'Contact' section. Sending inquiries directly through the website form helps ensure faster processing.",
                'keywords' => 'maktab emaili, school email, aloqa email, pochta',
                'category' => 'Aloqa',
            ],

            // --- TA'LIM VA NATIJALAR ---
            [
                'question' => 'Ta\'lim qaysi tillarda olib boriladi?',
                'question_en' => 'In which languages is education provided?',
                'answer' => 'Maktabda ta\'lim o\'zbek va rus tillarida olib boriladi. Jami 60 ta sinf mavjud bo\'lib, shundan 45 tasi o\'zbek, 15 tasi esa rus sinflaridir.',
                'answer_en' => 'Education at the school is provided in Uzbek and Russian languages. There are a total of 60 classes, 45 of which are Uzbek and 15 are Russian.',
                'keywords' => 'til, o\'zbekcha, ruscha, tillar, sinflar',
                'category' => 'Ta\'lim',
            ],
            [
                'question' => 'Bitiruvchilarning natijalari qanday?',
                'question_en' => 'What are the graduates\' results?',
                'answer' => '2025-yilda 121 nafar bitiruvchidan 74 nafari (61%) OTMlarga o\'qishga kirdi. OTMga kirish bo\'yicha o\'rtacha ball 82,9 ni tashkil etdi.',
                'answer_en' => 'In 2025, out of 121 graduates, 74 (61%) entered higher education institutions. The average score for university admission was 82.9.',
                'keywords' => 'natija, otm, kirish, bitiruvchilar, ball',
                'category' => 'Ta\'lim',
            ],
            [
                'question' => 'Qanday fanlar chuqurlashtirib o\'tiladi?',
                'question_en' => 'Which subjects are taught in depth?',
                'answer' => 'Maktabimizda barcha davlat standartidagi fanlar bilan birga IT (informatika), Matematika va Ingliz tili fanlarini chuqur o\'rgatishga alohida e\'tibor qaratiladi.',
                'answer_en' => 'In our school, along with all state standard subjects, special attention is paid to the in-depth teaching of IT (Computer Science), Mathematics, and English.',
                'keywords' => 'fanlar, darslar, nimalar, o\'qitiladi, ixtisoslashgan',
                'category' => 'Ta\'lim',
            ],

            // --- SAYT VA AI ---
            [
                'question' => 'Sayt muallifi kim?',
                'question_en' => 'Who created the website?',
                'answer' => 'Ushbu sayt 10-"E" sinf o\'quvchilari tomonidan ishlab chiqilgan. Mualliflar: Xabibullayev Shamsiddin, Abduqodirova E\'zoza va Mirzaqosimova Xadicha.',
                'answer_en' => 'This website was developed by Grade 10-E students: Shamsiddin Khabibullayev, E\'zoza Abdukodirova and Xadicha Mirzakosimova.',
                'keywords' => 'sayt, muallif, yaratgan, dasturchi, developer, ishtirok, jamoa',
                'category' => 'Sayt',
                'sort_order' => -20,
            ],
            [
                'question' => 'AI yordamchi nimalarga javob beradi?',
                'question_en' => 'What can the AI assistant answer?',
                'answer' => "AI yordamchi maktab ma'lumotlari, sayt mualliflari, yangiliklar, ustozlar, kurslar, imtihonlar, natijalar, taqvim, aloqa, admin bilan bog'lanish, profil, akkaunt, chat va izohlar bo'yicha javob berishga harakat qiladi.",
                'answer_en' => 'The AI assistant can help with school information, website credits, news, teachers, courses, exams, results, calendar, support/contact, profile and general study questions.',
                'keywords' => 'ai, yordamchi, nima qiladi, savol, javob, imkoniyat',
                'category' => 'AI',
                'sort_order' => -10,
            ],

            // --- BOSHQA PERSPEKTIVALAR ---
            [
                'question' => 'Maktab yaxshimi?',
                'question_en' => 'Is the school good?',
                'answer' => '81-maktab — Toshkentdagi boy tarixga va kuchli pedagogik jamoaga ega bo\'lgan maktablardan biri. OTMga kirish ko\'rsatkichi 61% ekani va ustozlarning 26 nafari xalqaro sertifikatlarga ega ekani ta\'lim sifati yuqori ekanligidan dalolat beradi.',
                'answer_en' => 'School 81 is one of the schools in Tashkent with a rich history and a strong pedagogical team. The 61% university admission rate and 26 teachers with international certificates indicate a high quality of education.',
                'keywords' => 'yaxshimi, sifatli, qanaqa maktab, fikrlar',
                'category' => 'Maktab',
            ],
            [
                'question' => 'Maktabda qanday to\'garaklar bor?',
                'question_en' => 'What clubs are available at the school?',
                'answer' => 'Maktabda o\'quvchilar uchun fan to\'garaklari (Matematika, Ingliz tili, IT) bilan birga sport to\'garaklari va san\'at yo\'nalishlari ham mavjud. Batafsil ma\'lumotni "Kurslar" bo\'limidan topishingiz mumkin.',
                'answer_en' => 'In addition to subject clubs (Mathematics, English, IT), the school offers sports and arts clubs. Detailed information can be found in the "Courses" section.',
                'keywords' => 'togaraklar, kurslar, darsdan tashqari, mashgulotlar',
                'category' => 'Ta\'lim',
            ],
            [
                'question' => 'Ota-onalar uchun qanday imkoniyatlar bor?',
                'question_en' => 'What opportunities are there for parents?',
                'answer' => 'Ota-onalar sayt orqali maktab yangiliklarini kuzatishlari, ustozlar bilan bog\'lanishlari va farzandlarining dars jarayonlari haqida ma\'lumot olishlari mumkin. Shuningdek, maktabda ota-onalar bilan muntazam muloqot tizimi yo\'lga qo\'yilgan.',
                'answer_en' => 'Parents can follow school news, contact teachers, and get information about their children\'s lessons through the website. Also, a regular communication system with parents is established at the school.',
                'keywords' => 'ota-onalar, ota ona, majlis, boglanish',
                'category' => 'Maktab',
            ],
            [
                'question' => 'Maktabga qanday hujjat topshirsa bo\'ladi?',
                'question_en' => 'How to apply to the school?',
                'answer' => 'Maktabga qabul qilish bo\'yicha barcha masalalar Davlat xizmatlari markazi yoki my.uz portaliorqali amalga oshiriladi. Qo\'shimcha savollar bo\'yicha maktab ma\'muriyati (+99890-958-00-67) bilan bog\'lanishingiz mumkin.',
                'answer_en' => 'All matters regarding admission to the school are carried out through the Public Services Center or the my.uz portal. For additional questions, you can contact the school administration (+99890-958-00-67).',
                'keywords' => 'qabul, hujjat topshirish, oqishga kirish, 1-sinf',
                'category' => 'Maktab',
            ],

            // --- AKKAUNT VA TEXNIK ---
            [
                'question' => 'Parolni unutdim, nima qilaman?',
                'question_en' => 'I forgot my password. What should I do?',
                'answer' => "Kirish sahifasidagi \"Parolni unutdingizmi?\" bo'limiga o'ting. Email manzilingizni kiriting, tasdiqlash kodini oling va yangi parol qo'ying.",
                'answer_en' => 'Use the forgot password page, enter your email, verify the code, and set a new password.',
                'keywords' => 'parol, unutdim, forgot password, reset password, kod',
                'category' => 'Akkaunt',
            ],
            [
                'question' => 'Ro\'yxatdan qanday o\'taman?',
                'question_en' => 'How do I register?',
                'answer' => "Ro'yxatdan o'tish uchun Register sahifasini oching, ism, email va parolni kiriting. So'ng emailga yuborilgan tasdiqlash kodi orqali akkauntni faollashtiring.",
                'answer_en' => 'Open the Register page, fill in your details, and verify your account with the emailed code.',
                'keywords' => 'register, royxatdan, ro yxatdan, signup, account yaratish',
                'category' => 'Akkaunt',
            ],
            [
                'question' => 'Admin bilan qanday bog\'lansam bo\'ladi?',
                'question_en' => 'How can I contact the admin?',
                'answer' => "Admin bilan bog'lanish uchun Aloqa sahifasiga kiring va murojaat yuboring. Xabar yuborish uchun akkauntga kirgan bo'lishingiz kerak. Shoshilinch holatda +99890-958-00-67 raqamiga qo'ng'iroq qiling.",
                'answer_en' => 'Use the Contact page while signed in. For urgent matters, please call +99890-958-00-67.',
                'keywords' => 'admin bilan boglanish, adminga yozish, support, texnik yordam, murojaat, contact admin',
                'category' => 'Aloqa',
                'sort_order' => -7,
            ],
        ];

        foreach ($data as $item) {
            \App\Models\AiKnowledge::updateOrCreate(
                ['question' => $item['question']],
                $item
            );
        }
    }
}
