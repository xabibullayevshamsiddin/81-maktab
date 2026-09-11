<?php

namespace Database\Seeders;

use App\Models\IeltsPassage;
use App\Models\IeltsQuestion;
use App\Models\IeltsSection;
use App\Models\IeltsTest;
use Illuminate\Database\Seeder;

class IeltsSampleSeeder extends Seeder
{
    public function run(): void
    {
        $test = IeltsTest::create([
            'title' => 'Ingliz tili darajangizni aniqlang (Placement Test)',
            'type' => 'placement',
            'time_limit_minutes' => 25,
            'is_active' => true,
        ]);

        // --- Reading section: Uzbekistan-context passage, IELTS band 6-7 vocabulary level ---
        $reading = IeltsSection::create([
            'ielts_test_id' => $test->id,
            'skill' => 'reading',
            'order' => 1,
            'time_limit_minutes' => 15,
        ]);

        $passage = IeltsPassage::create([
            'ielts_section_id' => $reading->id,
            'title' => 'The Silk Road Legacy in Samarkand',
            'content' => <<<TEXT
For centuries, Samarkand stood at the crossroads of the ancient Silk Road, a vast network of trade routes that
connected East Asia with the Mediterranean world. Merchants travelling through the city exchanged not only
silk, spices, and precious stones, but also ideas, scientific knowledge, and artistic techniques. This constant
flow of culture transformed Samarkand into one of the most architecturally significant cities in Central Asia.

The Registan, the city's most famous landmark, consists of three monumental madrasas arranged around a
central square. Built between the 15th and 17th centuries, these structures showcase intricate tilework,
massive portals, and turquoise domes that have influenced architecture far beyond the region. Despite
suffering damage from earthquakes over the centuries, much of the Registan has been carefully restored,
allowing modern visitors to appreciate the craftsmanship of medieval artisans.

Today, Samarkand's role has shifted from a trading hub to a symbol of cultural heritage. UNESCO added the
city to its World Heritage List in 2001, recognising its universal value. Tourism has become an increasingly
important part of the local economy, though officials continue to debate how to balance preservation with
the pressures of modern development.
TEXT,
        ]);

        $questions = [
            [
                'type' => 'true_false_ng',
                'question_text' => 'Samarkand was only important for trading silk.',
                'options' => ['True', 'False', 'Not Given'],
                'correct_answer' => 'False',
            ],
            [
                'type' => 'true_false_ng',
                'question_text' => 'The Registan consists of three madrasas.',
                'options' => ['True', 'False', 'Not Given'],
                'correct_answer' => 'True',
            ],
            [
                'type' => 'true_false_ng',
                'question_text' => 'All three madrasas were built in exactly the same year.',
                'options' => ['True', 'False', 'Not Given'],
                'correct_answer' => 'Not Given',
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => 'When was Samarkand added to the UNESCO World Heritage List?',
                'options' => ['1991', '2001', '2011', '1975'],
                'correct_answer' => '2001',
            ],
            [
                'type' => 'multiple_choice',
                'question_text' => 'What is a current challenge officials face regarding Samarkand?',
                'options' => [
                    'Finding new trade routes',
                    'Balancing preservation with development',
                    'A lack of tourists',
                    'Rebuilding the Silk Road',
                ],
                'correct_answer' => 'Balancing preservation with development',
            ],
        ];

        foreach ($questions as $i => $q) {
            IeltsQuestion::create($passage->only([]) + [
                'ielts_passage_id' => $passage->id,
                'type' => $q['type'],
                'question_text' => $q['question_text'],
                'options' => $q['options'],
                'correct_answer' => $q['correct_answer'],
                'order' => $i + 1,
            ]);
        }

        // --- Writing section ---
        $writing = IeltsSection::create([
            'ielts_test_id' => $test->id,
            'skill' => 'writing',
            'order' => 2,
            'time_limit_minutes' => 20,
        ]);

        $writingPassage = IeltsPassage::create([
            'ielts_section_id' => $writing->id,
            'title' => 'Writing Task',
            'content' => 'Some people believe that studying abroad helps students develop important life skills, while others '.
                'think it is better to study in one\'s own country. Discuss both views and give your own opinion. '.
                'Write at least 250 words.',
        ]);

        IeltsQuestion::create([
            'ielts_passage_id' => $writingPassage->id,
            'type' => 'writing_task',
            'question_text' => $writingPassage->content,
            'options' => null,
            'correct_answer' => null,
            'order' => 1,
        ]);

        // --- Listening section placeholder (add your own audio_url after seeding) ---
        $listening = IeltsSection::create([
            'ielts_test_id' => $test->id,
            'skill' => 'listening',
            'order' => 3,
            'time_limit_minutes' => 15,
        ]);

        $listeningPassage = IeltsPassage::create([
            'ielts_section_id' => $listening->id,
            'title' => 'Tashkent Metro Announcement (sample)',
            'content' => null,
            'audio_url' => null, // TODO: upload your own recorded/licensed audio and set the URL here
        ]);

        IeltsQuestion::create([
            'ielts_passage_id' => $listeningPassage->id,
            'type' => 'multiple_choice',
            'question_text' => 'According to the announcement, which line should passengers use to reach the airport?',
            'options' => ['Chilonzor Line', 'Line 2', 'Line 3', 'Line 4'],
            'correct_answer' => 'Line 4',
            'order' => 1,
        ]);
    }
}
