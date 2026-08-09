# Telegram Bot - Telefon Raqamini Ko'rsatish

Oddiy Telegram boti - foydalanuvchi telefon raqamini ko'rsatadi.

## O'rnatish

1. **Python o'rnatilgan bo'lishi kerak** (3.7+)

2. **Bot token olish:**
   - Telegram'da @BotFather ni toping
   - `/new` buyrug'ini yuboring
   - Botga ism bering
   - Token ni kopyalang

3. **Dependenciyalarni o'rnating:**
   ```bash
   cd telegram-bot
   pip install -r requirements.txt
   ```

4. **Bot tokenini so'ng:**
   
   **Windows:**
   ```cmd
   set TELEGRAM_BOT_TOKEN=YOUR_TOKEN_HERE
   ```
   
   **Linux/Mac:**
   ```bash
   export TELEGRAM_BOT_TOKEN=YOUR_TOKEN_HERE
   ```

5. **Botni ishga tushiring:**
   ```bash
   python bot.py
   ```

## Buyruqlar

| Buyruq | Tavsif |
|--------|--------|
| `/start` | Botni ishga tushirish va qarshilash |
| `/myinfo` | Foydalanuvchi ma'lumotlarini ko'rsatish |
| `/help` | Yordam |

## Xususiyatlar

- 📱 Foydalanuvchi telefon raqamini ko'rsatadi
- 👤 Ism va familiya ma'lumotlari
- 🆔 Username va User ID
- 🌐 Til ma'lumoti
- 📝 Oddiy xabarlarga javob beradi

## Eslatmalar

- Foydalanuvchi telefon raqamini ko'rsatish uchun Telegram'da telefon raqamini kiritgan bo'lishi kerak
- Ba'zi foydalanuvchilar telefon raqamini ko'rsatmasligi mumkin
- Bot tokenni xavfsiz saqlang

## Muammolar yuzaga kelsa

1. Bot token noto'g'ri - @BotFather dan yangi token oling
2. Internet aloqasi mavjud emas - tekshiring
3. Python versiyasi eskirgan - yangilang
