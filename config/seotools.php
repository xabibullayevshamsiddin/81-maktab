<?php

return [
    'inertia' => env('SEO_TOOLS_INERTIA', false),
    'meta' => [
        'defaults' => [
            'title' => '81-IDUM - 81-sonli Maktab',
            'titleBefore' => true,
            'description' => '81-sonli maktab - zamonaviy ta\'lim, kuchli qadriyatlar va o\'quvchi muvaffaqiyati uchun xizmat qiladi.',
            'separator' => ' | ',
            'keywords' => ['maktab', 'ta\'lim', '81-maktab', 'o\'quvchilar', 'o\'zbekiston'],
            'canonical' => true,
            'robots' => 'index, follow',
        ],
        'webmaster_tags' => [
            'google' => null,
            'bing' => null,
            'yandex' => null,
        ],
        'add_notranslate_class' => false,
    ],
    'opengraph' => [
        'defaults' => [
            'title' => '81-IDUM - 81-sonli Maktab',
            'description' => '81-sonli maktab - zamonaviy ta\'lim, kuchli qadriyatlar va o\'quvchi muvaffaqiyati uchun xizmat qiladi.',
            'url' => true,
            'type' => 'website',
            'site_name' => '81-IDUM',
            'images' => [],
        ],
    ],
    'twitter' => [
        'defaults' => [
            'card' => 'summary_large_image',
        ],
    ],
    'json-ld' => [
        'defaults' => [
            'title' => '81-IDUM - 81-sonli Maktab',
            'description' => '81-sonli maktab - zamonaviy ta\'lim, kuchli qadriyatlar va o\'quvchi muvaffaqiyati uchun xizmat qiladi.',
            'url' => true,
            'type' => 'EducationalOrganization',
            'images' => [],
        ],
    ],
];
