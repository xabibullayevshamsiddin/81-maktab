<?php

return [
    'validation' => [
        'required' => ':attribute maydoni to\'ldirilishi shart.',
        'email' => ':attribute to\'g\'ri email manzil bo\'lishi kerak.',
        'min' => ':attribute kamida :min belgidan iborat bo\'lishi kerak.',
        'max' => ':attribute :max belgidan oshmasligi kerak.',
        'unique' => 'Bu :attribute allaqachon mavjud.',
        'confirmed' => ':attribute tasdiqlanmadi.',
        'image' => ':attribute rasm bo\'lishi kerak.',
        'mimes' => ':attribute quyidagi formatlarda bo\'lishi kerak: :values.',
        'string' => ':attribute matn bo\'lishi kerak.',
        'integer' => ':attribute butun son bo\'lishi kerak.',
        'exists' => 'Tanlangan :attribute mavjud emas.',
    ],
    'attributes' => [
        'title' => 'Sarlavha',
        'content' => 'Mazmun',
        'short_content' => 'Qisqacha mazmun',
        'image' => 'Rasm',
        'category_id' => 'Kategoriya',
        'name' => 'Ism',
        'email' => 'Email',
        'phone' => 'Telefon raqam',
        'password' => 'Parol',
        'body' => 'Izoh matni',
        'author_name' => 'Muallif ismi',
    ],
];
