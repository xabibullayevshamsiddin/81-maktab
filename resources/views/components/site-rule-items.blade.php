@props(['area' => 'comment'])
@php
  $presets = [
    'comment' => [
      'button' => 'Izoh qoidalari',
      'kicker' => 'Madaniyatli muhit',
      'title' => 'Izoh yozish qoidalari',
      'description' => "Fikringiz foydali, hurmatli va tushunarli bo'lsa, moderator tasdiqlashi ancha tezlashadi.",
      'items' => [
        [
          'icon' => 'fa-solid fa-handshake-angle',
          'title' => 'Hurmat bilan yozing',
          'body' => "Boshqa foydalanuvchilar, ustozlar va adminlarga nisbatan hurmatli ohangni saqlang. Qo'pol yoki kamsituvchi gaplar qabul qilinmaydi.",
        ],
        [
          'icon' => 'fa-solid fa-lightbulb',
          'title' => 'Mazmunli fikr qoldiring',
          'body' => "Faqat bir so'zli yoki mavzuga aloqasiz izohlar o'rniga, aniq savol, taklif yoki foydali fikr yozing.",
        ],
        [
          'icon' => 'fa-solid fa-ban',
          'title' => 'Spam va reklama taqiqlanadi',
          'body' => "Bir xil izohni takrorlash, reklama linklari, kanal yoki xizmat targ'iboti joylash ruxsat etilmaydi.",
        ],
        [
          'icon' => 'fa-solid fa-user-shield',
          'title' => "Shaxsiy ma'lumotni oshkor qilmang",
          'body' => "Telefon raqam, parol, karta ma'lumoti yoki boshqa nozik ma'lumotlarni izoh ichida yozmang.",
        ],
        [
          'icon' => 'fa-solid fa-scale-balanced',
          'title' => 'Moderator qarori amal qiladi',
          'body' => "Qoidalarga zid izohlar o'chirib tashlanadi yoki tasdiqlanmaydi. Zarur holatda foydalanuvchi cheklanishi mumkin.",
        ],
      ],
    ],
    'contact' => [
      'button' => 'Aloqa qoidalari',
      'kicker' => 'Toza murojaat',
      'title' => 'Xabar yuborish qoidalari',
      'description' => "Murojaatingiz aniq va tartibli bo'lsa, javob berish jarayoni ancha qulay bo'ladi.",
      'items' => [
        [
          'icon' => 'fa-solid fa-id-card',
          'title' => "Ma'lumotlarni to'g'ri kiriting",
          'body' => "Ism, email va telefon maydonlarini haqiqiy ma'lumotlar bilan to'ldiring, aks holda siz bilan bog'lana olmaymiz.",
        ],
        [
          'icon' => 'fa-solid fa-pen-ruler',
          'title' => 'Murojaatni aniq yozing',
          'body' => 'Muammoni yoki savolni qisqa, ravshan va bitta mavzu doirasida yozing. Noaniq matnlar javobni sekinlashtiradi.',
        ],
        [
          'icon' => 'fa-solid fa-clock-rotate-left',
          'title' => 'Bir xabarni qayta yubormang',
          'body' => 'Bir xil mazmundagi murojaatni ketma-ket yuborish tizimni ortiqcha band qiladi. Javobni kuting.',
        ],
        [
          'icon' => 'fa-solid fa-comments',
          'title' => 'Hurmatli uslubni saqlang',
          'body' => "Tahdid, haqorat yoki bosim ohangidagi xabarlar ko'rib chiqilmaydi. Rasmiy va odobli yozuv afzal.",
        ],
        [
          'icon' => 'fa-solid fa-triangle-exclamation',
          'title' => 'Favqulodda holat emas',
          'body' => "Bu forma tez yordam yoki zudlik bilan javob beriladigan kanal emas. Juda shoshilinch holatda telefon orqali bog'laning.",
        ],
      ],
    ],
  ];
  $config = $presets[$area] ?? $presets['comment'];
  $list = collect($config['items']);
@endphp
@if($list->isNotEmpty())
  <button
    type="button"
    class="btn btn-outline btn-sm site-rules-open site-rules-main-btn"
    data-dialog="rules-all-{{ $area }}"
    data-area="{{ $area }}"
  >
    <i class="fa-solid fa-sparkles"></i>
    <span>{{ $config['button'] }}</span>
  </button>
  <dialog
    id="rules-all-{{ $area }}"
    class="site-rules-dialog"
    data-area="{{ $area }}"
    aria-labelledby="site-rules-title-{{ $area }}"
    aria-describedby="site-rules-desc-{{ $area }}"
  >
    <div class="site-rules-dialog-shell">
      <div class="site-rules-dialog-inner">
        <div class="site-rules-dialog-head">
          <div class="site-rules-dialog-head-copy">
            <span class="site-rules-dialog-kicker">{{ $config['kicker'] }}</span>
            <strong id="site-rules-title-{{ $area }}">{{ $config['title'] }}</strong>
            <p id="site-rules-desc-{{ $area }}">
              {{ $config['description'] }}
            </p>
          </div>
          <button type="button" class="site-rules-close" aria-label="Yopish">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <div class="site-rules-dialog-body site-rules-dialog-body--stack">
          @foreach($list as $item)
            @php
              $icon = data_get($item, 'icon');
              $title = data_get($item, 'title');
              $body = data_get($item, 'body');
            @endphp
            <section class="site-rule-block">
              <span class="site-rule-block-index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
              <div class="site-rule-block-content">
                <p class="site-rule-block-title">
                  <span class="site-rule-block-icon" aria-hidden="true">
                    @if(filled($icon))
                      <i class="{{ $icon }}"></i>
                    @else
                      <i class="fa-solid fa-circle-info"></i>
                    @endif
                  </span>
                  <span>{{ $title }}</span>
                </p>
                <div class="site-rule-block-text">{!! nl2br(e($body)) !!}</div>
              </div>
            </section>
          @endforeach
        </div>
        <div class="site-rules-dialog-foot">
          <button type="button" class="btn btn-sm site-rules-close site-rules-close-btn">Tushunarli</button>
        </div>
      </div>
    </div>
  </dialog>
@endif
