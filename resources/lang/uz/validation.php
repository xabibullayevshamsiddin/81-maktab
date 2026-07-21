<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute qabul qilnishi shart.',
    'accepted_if' => ':attribute :other qiymati :value bo\'lsa qabul qilnishi shart.',
    'active_url' => ':attribute to\'g\'ri URL manzil bo\'lishi kerak.',
    'after' => ':attribute :date kundan keyin bo\'lishi kerak.',
    'after_or_equal' => ':attribute :date kuni yoki undan keyin bo\'lishi kerak.',
    'alpha' => ':attribute faqat harflardan iborat bo\'lishi kerak.',
    'alpha_dash' => ':attribute faqat harflar, raqamlar, tire va pastki chiziqdan iborat bo\'lishi kerak.',
    'alpha_num' => ':attribute faqat harflar va raqamlardan iborat bo\'lishi kerak.',
    'array' => ':attribute massiv bo\'lishi kerak.',
    'ascii' => ':attribute faqat ASCII belgilardan iborat bo\'lishi kerak.',
    'before' => ':attribute :date kundan oldin bo\'lishi kerak.',
    'before_or_equal' => ':attribute :date kuni yoki undan oldin bo\'lishi kerak.',
    'between' => [
        'array' => ':attribute :min va :max element orasida bo\'lishi kerak.',
        'file' => ':attribute :min va :max kilobayt orasida bo\'lishi kerak.',
        'numeric' => ':attribute :min va :max orasida bo\'lishi kerak.',
        'string' => ':attribute :min va :max belgi orasida bo\'lishi kerak.',
    ],
    'boolean' => ':attribute to\'g\'ri yoki noto\'g\'ri bo\'lishi kerak.',
    'can' => ':attribute mavjud emas.',
    'confirmed' => ':attribute tasdiqlash qiymatlari mos kelmadi.',
    'contains' => ':attribute kerakli qiymatni o\'z ichiga olmadi.',
    'current_password' => 'Parol noto\'g\'ri.',
    'date' => ':attribute to\'g\'ri sana bo\'lishi kerak.',
    'date_equals' => ':attribute :date sanasiga teng bo\'lishi kerak.',
    'date_format' => ':attribute :format formatida bo\'lishi kerak.',
    'decimal' => ':attribute :decimal o\'nli kasrga ega bo\'lishi kerak.',
    'declined' => ':attribute rad etilishi shart.',
    'declined_if' => ':attribute :other qiymati :value bo\'lsa rad etilishi shart.',
    'different' => ':attribute :other bilan farq qilishi kerak.',
    'digits' => ':attribute :digits raqamdan iborat bo\'lishi kerak.',
    'digits_between' => ':attribute :min va :max raqam orasida bo\'lishi kerak.',
    'dimensions' => ':attribute noto\'g\'ri tasvir o\'lchamlari.',
    'distinct' => ':attribute takror qiymatga ega.',
    'email' => ':attribute to\'g\'ri email manzil bo\'lishi kerak.',
    'ends_with' => ':attribute quyidaglardan biri bilan tugashi kerak: :values.',
    'exists' => 'Tanlangan :attribute mavjud emas.',
    'file' => ':attribute fayl bo\'lishi kerak.',
    'filled' => ':attribute to\'ldirilishi shart.',
    'gt' => [
        'array' => ':attribute :value dan ko\'p element o\'z ichiga olishi kerak.',
        'file' => ':attribute :value kilobaytdan katta bo\'lishi kerak.',
        'numeric' => ':attribute :value dan katta bo\'lishi kerak.',
        'string' => ':attribute :value belgidan ko\'p bo\'lishi kerak.',
    ],
    'gte' => [
        'array' => ':attribute :value yoki undan ko\'p element o\'z ichiga olishi kerak.',
        'file' => ':attribute :value kilobaytdan katta yoki teng bo\'lishi kerak.',
        'numeric' => ':attribute :value dan katta yoki teng bo\'lishi kerak.',
        'string' => ':attribute :value belgi yoki undan ko\'p bo\'lishi kerak.',
    ],
    'image' => ':attribute tasvir bo\'lishi kerak.',
    'in' => 'Tanlangan :attribute noto\'g\'ri.',
    'in_array' => ':attribute :other ichida bo\'lishi kerak.',
    'integer' => ':attribute butun son bo\'lishi kerak.',
    'ip' => ':attribute to\'g\'ri IP manzili bo\'lishi kerak.',
    'ipv4' => ':attribute to\'g\'ri IPv4 manzili bo\'lishi kerak.',
    'ipv6' => ':attribute to\'g\'ri IPv6 manzili bo\'lishi kerak.',
    'json' => ':attribute to\'g\'ri JSON bo\'lishi kerak.',
    'lowercase' => ':attribute kichik harflardan iborat bo\'lishi kerak.',
    'lt' => [
        'array' => ':attribute :value dan kam element o\'z ichiga olishi kerak.',
        'file' => ':attribute :value kilobaytdan kichik bo\'lishi kerak.',
        'numeric' => ':attribute :value dan kichik bo\'lishi kerak.',
        'string' => ':attribute :value belgidan kam bo\'lishi kerak.',
    ],
    'lte' => [
        'array' => ':attribute :value yoki undan kam element o\'z ichiga olishi kerak.',
        'file' => ':attribute :value kilobaytdan kichik yoki teng bo\'lishi kerak.',
        'numeric' => ':attribute :value dan kichik yoki teng bo\'lishi kerak.',
        'string' => ':attribute :value belgi yoki undan kam bo\'lishi kerak.',
    ],
    'max' => [
        'array' => ':attribute :max dan ko\'p element o\'z ichiga olmashi kerak.',
        'file' => ':attribute :max kilobaytdan katta bo\'lmasligi kerak.',
        'numeric' => ':attribute :max dan katta bo\'lmasligi kerak.',
        'string' => ':attribute :max belgidan ko\'p bo\'lmasligi kerak.',
    ],
    'mimes' => ':attribute quyidagi turlardagi fayl bo\'lishi kerak: :values.',
    'mimetypes' => ':attribute quyidagi turlardagi fayl bo\'lishi kerak: :values.',
    'min' => [
        'array' => ':attribute hech bo\'lmaganda :min element bo\'lishi kerak.',
        'file' => ':attribute hech bo\'lmaganda :min kilobayt bo\'lishi kerak.',
        'numeric' => ':attribute hech bo\'lmaganda :min bo\'lishi kerak.',
        'string' => ':attribute hech bo\'lmaganda :min belgi bo\'lishi kerak.',
    ],
    'multiple_of' => ':attribute :value ning ko\'paytmasi bo\'lishi kerak.',
    'not_in' => 'Tanlangan :attribute noto\'g\'ri.',
    'not_regex' => ':attribute format noto\'g\'ri.',
    'numeric' => ':attribute raqam bo\'lishi kerak.',
    'password' => 'Parol noto\'g\'ri.',
    'present' => ':attribute mavjud bo\'lishi kerak.',
    'regex' => ':attribute format noto\'g\'ri.',
    'required' => ':attribute maydoni to\'ldirilishi shart.',
    'required_array_keys' => ':attribute quyidagi kalitlarni o\'z ichiga olishi kerak: :values.',
    'required_if' => ':attribute :other qiymati :value bo\'lsa to\'ldirilishi shart.',
    'required_unless' => ':attribute :other qiymati :value bo\'lmasa to\'ldirilishi shart.',
    'required_with' => ':attribute :values mavjud bo\'lsa to\'ldirilishi shart.',
    'required_with_all' => ':attribute :values mavjud bo\'lsa to\'ldirilishi shart.',
    'required_without' => ':attribute :values mavjud bo\'lmasa to\'ldirilishi shart.',
    'required_without_all' => ':attribute :values mavjud bo\'lmasa to\'ldirilishi shart.',
    'same' => ':attribute :other bilan mos kelishi kerak.',
    'size' => [
        'array' => ':attribute :size element bo\'lishi kerak.',
        'file' => ':attribute :size kilobayt bo\'lishi kerak.',
        'numeric' => ':attribute :size bo\'lishi kerak.',
        'string' => ':attribute :size belgi bo\'lishi kerak.',
    ],
    'starts_with' => ':attribute quyidaglardan biri bilan boshlanishi kerak: :values.',
    'string' => ':attribute matn bo\'lishi kerak.',
    'timezone' => ':attribute to\'g\'ri vaqt zonasi bo\'lishi kerak.',
    'unique' => ':attribute allaqachon mavjud.',
    'uploaded' => ':attribute yuklanimasdi.',
    'uppercase' => ':attribute bosh harflardan iborat bo\'lishi kerak.',
    'url' => ':attribute format noto\'g\'ri.',
    'ulid' => ':attribute to\'g\'ri ULID bo\'lishi kerak.',
    'uuid' => ':attribute to\'g\'ri UUID bo\'lishi kerak.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute names with
    | something more reader friendly like Email Address instead of "email".
    | This simply helps us make our message more expressive and clear.
    |
    */

    'attributes' => [
        'name' => 'ism',
        'email' => 'email manzil',
        'password' => 'parol',
        'password_confirmation' => 'parol tasdiqlash',
        'title' => 'sarlavha',
        'content' => 'kontent',
        'description' => 'ta\'rif',
        'phone' => 'telefon raqami',
        'address' => 'manzil',
        'city' => 'shahar',
        'state' => 'viloyat',
        'country' => 'davlat',
        'date' => 'sana',
        'time' => 'vaqt',
        'image' => 'tasvir',
        'file' => 'fayl',
        'agree' => 'rozilashish',
        'username' => 'foydalanuvchi nomi',
        'first_name' => 'ismi',
        'last_name' => 'familiyasi',
        'middle_name' => 'otasining ismi',
        'grade' => 'sinf',
        'course' => 'kurs',
        'subject' => 'fan',
        'question' => 'savol',
        'answer' => 'javob',
        'code' => 'kod',
        'token' => 'token',
        'year' => 'yil',
        'month' => 'oy',
        'day' => 'kun',
        'gender' => 'jins',
        'age' => 'yosh',
        'birth_date' => 'tug\'ilgan sana',
    ],

];
