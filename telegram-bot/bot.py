#!/usr/bin/env python3
"""
Simple Telegram Bot - Shows user's phone number
"""

import os
import logging
from telegram import Update, BotCommand
from telegram.ext import Application, CommandHandler, MessageHandler, filters, ContextTypes

# Logging sozlash
logging.basicConfig(
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    level=logging.INFO
)
logger = logging.getLogger(__name__)

# Bot token
BOT_TOKEN = os.getenv('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE')


async def start(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:
    """Start buyrug'i - Foydalanuvchini qarshilaydi"""
    user = update.effective_user
    
    # Telefon raqamini tekshirish
    phone_number = user.phone_number if user.phone_number else "Mavjud emas"
    
    welcome_message = (
        f"Salom, {user.first_name}! 👋\n\n"
        f"Sizning Telegram profilingiz:\n\n"
        f"👤 Ism: {user.first_name}\n"
        f"👤 Familiya: {user.last_name or 'Kiritilmagan'}\n"
        f"🆔 Username: @{user.username or 'Mavjud emas'}\n"
        f"📱 Telefon raqam: {phone_number}\n\n"
        f"Ma'lumotlarni ko'rish uchun /myinfo buyrug'ini yuboring."
    )
    
    await update.message.reply_text(welcome_message)


async def my_info(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:
    """Foydalanuvchi ma'lumotlarini ko'rsatadi"""
    user = update.effective_user
    
    # Telefon raqamini tekshirish
    phone_number = user.phone_number if user.phone_number else "Mavjud emas"
    
    info_message = (
        f"📱 Sizning ma'lumotlaringiz:\n\n"
        f"👤 Ism: {user.first_name}\n"
        f"👤 Familiya: {user.last_name or 'Kiritilmagan'}\n"
        f"🆔 Username: @{user.username or 'Mavjud emas'}\n"
        f"📱 Telefon raqam: {phone_number}\n"
        f"🆔 User ID: {user.id}\n"
        f"🌐 Til: {user.language_code or 'Aniqlanmagan'}"
    )
    
    await update.message.reply_text(info_message)


async def help_command(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:
    """Yordam"""
    help_text = (
        "🤖 Telegram Bot - Yordam\n\n"
        "Buyruqlar:\n"
        "/start - Botni ishga tushirish\n"
        "/myinfo - Sizning ma'lumotlaringiz\n"
        "/help - Bu yordam\n\n"
        "Shunchaki istalgan xabar yuboring va bot javob beradi!"
    )
    
    await update.message.reply_text(help_text)


async def handle_message(update: Update, context: ContextTypes.DEFAULT_TYPE) -> None:
    """Oddiy xabarlarga javob berish"""
    user = update.effective_user
    message_text = update.message.text
    
    phone_number = user.phone_number if user.phone_number else "Mavjud emas"
    
    response = (
        f"📝 Siz yozdingiz: {message_text}\n\n"
        f"📱 Telefon raqam: {phone_number}\n\n"
        f"Buyruqlar: /start, /myinfo, /help"
    )
    
    await update.message.reply_text(response)


async def post_init(application: Application) -> None:
    """Bot ishga tushgandan keyin buyruqlarni ro'yxatdan o'tkazish"""
    await application.bot.set_my_commands([
        BotCommand("start", "Botni ishga tushirish"),
        BotCommand("myinfo", "Sizning ma'lumotlaringiz"),
        BotCommand("help", "Yordam"),
    ])


def main() -> None:
    """Botni ishga tushirish"""
    # Bot token tekshirish
    if BOT_TOKEN == 'YOUR_BOT_TOKEN_HERE':
        print("❌ Xatolik: TELEGRAM_BOT_TOKEN o'zgaruvchisini so'ng!")
        print("1. @BotFather ga /new buyrug'ini yuboring")
        print("2. Bot tokenini oling")
        print("3. TELEGRAM_BOT_TOKEN o'zgaruvchisini qo'shing")
        print("4. Botni qayta ishga tushiring")
        return
    
    # Application yaratish
    application = Application.builder().token(BOT_TOKEN).post_init(post_init).build()
    
    # Handlerlarni qo'shish
    application.add_handler(CommandHandler("start", start))
    application.add_handler(CommandHandler("myinfo", my_info))
    application.add_handler(CommandHandler("help", help_command))
    application.add_handler(MessageHandler(filters.TEXT & ~filters.COMMAND, handle_message))
    
    # Botni ishga tushirish
    print("🤖 Bot ishga tushdi!")
    print("To'xtatish uchun Ctrl+C bosing")
    application.run_polling(allowed_updates=Update.ALL_TYPES)


if __name__ == '__main__':
    main()
