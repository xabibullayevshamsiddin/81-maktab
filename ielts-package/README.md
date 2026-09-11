# IELTS Tayyorgarlik / Daraja Sinovi moduli

Bu papkada 81-maktab loyihangizga (Laravel) qo'shish uchun tayyor IELTS moduli bor:
- 4 ta bo'lim: Reading, Listening, Writing, Speaking
- Har bir urinish (attempt) uchun vaqt nazorati va tasodifiy savol tartibi (mavjud ExamController mantig'iga o'xshash)
- Writing uchun AI (Gemini) orqali band score baholash
- Homiylar (donor) darajasiga qarab imtiyozlar
- O'zbekiston kontekstidagi namunaviy Reading matni va savollari (IELTS band 6-7 darajasida)

## O'rnatish tartibi

1. `database/migrations/` papkasidagi faylni loyihangizning `database/migrations/` papkasiga ko'chiring, so'ng:
   ```bash
   php artisan migrate
   ```
2. `app/Models/` dagi 6 ta model faylini `app/Models/` ga ko'chiring.
3. `app/Http/Controllers/IeltsController.php` ni `app/Http/Controllers/` ga ko'chiring.
4. `app/Services/Ai/IeltsGradingService.php` ni `app/Services/Ai/` ga ko'chiring. **MUHIM**: bu fayl ichida `AiService` ga bog'lanish joyi bor — o'zingizning mavjud `app/Services/Ai/AiService.php` dagi Gemini chaqiruv metodiga moslab ulang (fayl ichida `// TODO` belgilangan joy).
5. `routes/ielts.php` ni `routes/` papkasiga qo'shing va `routes/web.php` oxiriga qo'shing:
   ```php
   require __DIR__.'/ielts.php';
   ```
6. `resources/views/ielts/` papkasini ko'chiring.
7. `database/seeders/IeltsSampleSeeder.php` ni ko'chirib, ishga tushiring:
   ```bash
   php artisan db:seed --class=IeltsSampleSeeder
   ```

## Homiylar (donor) uchun imtiyozlar — taklif etilgan mantiq

Sizda allaqachon donation asosida daraja (rank) tizimi bor (1/2/3 — ochish mumkin bo'lgan kurslar soni). Shu tizimga ulab, quyidagi imtiyozlarni qo'shish tavsiya etiladi (`IeltsController` ichida `// TODO: donation rank` joylarni o'zingizning haqiqiy metod/ustun nomiga moslang, masalan `$user->donationRank()` yoki `$user->donation_rank`):

| Daraja | Oyiga urinishlar soni | Writing AI fikr-mulohaza | Qo'shimcha |
|---|---|---|---|
| Homiy emas | 1 marta | Faqat umumiy band | — |
| Rank 1 | 3 marta | Har mezon bo'yicha batafsil (Task Achievement, Coherence, Lexical, Grammar) | — |
| Rank 2 | Cheksiz | To'liq tahlil + grammatik xatolar belgilanadi | Tavsiya etilgan kurslar ro'yxati |
| Rank 3 (eng yuqori) | Cheksiz | To'liq tahlil | + AI tuzgan haftalik shaxsiy tayyorgarlik rejasi + navbatda ustuvorlik |

## Band score haqida MUHIM eslatma

Rasmiy IELTS ball konvertatsiya jadvali oshkor qilinmagan. Ushbu modulda ishlatilgan raw-score → band konvertatsiyasi **taxminiy** (ommaviy manbalarga asoslangan). Natija sahifasida albatta "Bu taxminiy baho, rasmiy IELTS natijasi emas" degan ogohlantirish ko'rsating (huquqiy va ishonch nuqtai nazaridan muhim) — bu allaqachon `result.blade.php` ichiga qo'shilgan.

## Keyingi qadamlar

- Listening uchun haqiqiy audio fayllar yuklashingiz kerak (o'zingiz yozib olgan yoki litsenziyalangan — rasmiy IELTS audiosini ishlatish mumkin emas).
- Speaking bo'limi hozircha faqat savol ko'rsatadi va ovoz yozib oladi (MediaRecorder API); avtomatik AI baholash keyingi bosqichda AiService orqali qo'shilishi mumkin (speech-to-text + baholash).
