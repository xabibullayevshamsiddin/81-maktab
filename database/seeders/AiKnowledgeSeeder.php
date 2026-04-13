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
            [
                'question' => 'Maktab qachon tashkil topgan?',
                'question_en' => 'When was the school established?',
                'answer' => '81-IDUM maktabi o\'z faoliyatini 1980-yillarda boshlagan va hozirda zamonaviy ta\'lim markazlaridan biri hisoblanadi.',
                'answer_en' => 'School 81-IDUM started its activity in the 1980s and is now one of the modern educational centers.',
                'keywords' => 'tashkil, qachon, ochilgan, sana, yil',
            ],
            [
                'question' => 'Maktab manzili qayerda?',
                'question_en' => 'Where is the school located?',
                'answer' => 'Maktabimiz Toshkent viloyati, Zangiota tumanida joylashgan. Batafsil xaritani "Aloqa" bo\'limidan topishingiz mumkin.',
                'answer_en' => 'Our school is located in Zangiota district, Tashkent region. You can find a detailed map in the "Contact" section.',
                'keywords' => 'manzil, qayerda, joylashgan, lokatsiya, xarita',
            ],
            [
                'question' => 'Qanday fanlar o\'tiladi?',
                'question_en' => 'What subjects are taught?',
                'answer' => 'Maktabimizda barcha davlat standartidagi fanlar bilan birga IT, Matematika va Ingliz tili chuqurlashtirib o\'tiladi.',
                'answer_en' => 'In our school, IT, Mathematics and English are taught in depth along with all state standard subjects.',
                'keywords' => 'fanlar, darslar, nimalar, o\'qitiladi',
            ],
        ];

        foreach ($data as $item) {
            \App\Models\AiKnowledge::create($item);
        }
    }
}
