<?php

namespace Database\Seeders;

use App\Models\ChatSticker;
use Illuminate\Database\Seeder;

class ChatStickerSeeder extends Seeder
{
    public function run(): void
    {
        $stickers = [
            // Umumiy stikerlar (barcha foydalanuvchilar uchun)
            ['code' => 'fire', 'emoji' => '🔥', 'category' => 'nature', 'is_donor_only' => false, 'sort_order' => 1],
            ['code' => 'clap', 'emoji' => '👏', 'category' => 'hands', 'is_donor_only' => false, 'sort_order' => 2],
            ['code' => 'smile', 'emoji' => '😄', 'category' => 'feelings', 'is_donor_only' => false, 'sort_order' => 3],
            ['code' => 'thumbs_up', 'emoji' => '👍', 'category' => 'hands', 'is_donor_only' => false, 'sort_order' => 4],
            ['code' => 'party', 'emoji' => '🎉', 'category' => 'nature', 'is_donor_only' => false, 'sort_order' => 5],
            ['code' => 'heart', 'emoji' => '❤️', 'category' => 'nature', 'is_donor_only' => false, 'sort_order' => 6],
            ['code' => 'sad', 'emoji' => '😢', 'category' => 'feelings', 'is_donor_only' => false, 'sort_order' => 7],
            ['code' => 'thanks', 'emoji' => '🙏', 'category' => 'hands', 'is_donor_only' => false, 'sort_order' => 8],
            ['code' => 'score', 'emoji' => '💯', 'category' => 'objects', 'is_donor_only' => false, 'sort_order' => 9],

            // Donor stikerlari (faqat donorlar uchun)
            ['code' => 'gem', 'emoji' => '💎', 'category' => 'nature', 'is_donor_only' => true, 'sort_order' => 10],
            ['code' => 'rocket', 'emoji' => '🚀', 'category' => 'nature', 'is_donor_only' => true, 'sort_order' => 11],
            ['code' => 'star', 'emoji' => '🌟', 'category' => 'nature', 'is_donor_only' => true, 'sort_order' => 12],
        ];

        foreach ($stickers as $sticker) {
            ChatSticker::updateOrCreate(
                ['code' => $sticker['code']],
                [
                    'image_path' => "stickers/{$sticker['category']}/{$sticker['code']}.png",
                    'category' => $sticker['category'],
                    'is_donor_only' => $sticker['is_donor_only'],
                    'sort_order' => $sticker['sort_order'],
                ]
            );
        }
    }
}
