
# iPay.ua Checkout API for OpenCart 3.x / ocStore 3.x

![OpenCart](https://img.shields.io/badge/OpenCart-3.x-blue?logo=opencart)
![PHP](https://img.shields.io/badge/PHP-%3E=7.1-blue?logo=php)
![License](https://img.shields.io/badge/license-MIT-green)
![iPay Logo](https://psm7.com/awards-2022/wp-content/uploads/2022/10/1666890094-image-480x230-c-default.png)

This module allows you to integrate the **iPay.ua payment gateway (API 3.02)** into your OpenCart or ocStore e-commerce website (version 3.x).

🔗 [Developer: flancer.eu](https://flancer.eu)  
👤 [Contact on LinkedIn](https://www.linkedin.com/in/butuzoff/)  
💬 [Telegram @butuzoff](https://t.me/butuzoff)

---


## 🇺🇦 Українська версія

### Можливості
- Підтримка API iPay.ua 3.02
- Перенаправлення клієнта на сторінку оплати
- Автоматичне оновлення статусу замовлення після оплати
- Тестовий і бойовий режими
- Режим дебаг

### Встановлення
1. Завантажте архів із модулем
2. Скопіюйте вміст `upload/` в корінь сайту, жоден файл перезаписано не буде
3. В адмінці: Розширення → Розширення → Оплата → iPay Checkout API → Встановити
4. Розширення → Модифікатори → Оновити (синя кнопка)

### Налаштування
- **ID Мерчанта (mch_id)** — ваш ідентифікатор iPay
- **Ключ підпису (sign_key)** — ваш секретний ключ
- **Тестовий режим** — вкл/викл
- **Статуси замовлення** — після успіху / помилки
- **Геозона**, **Статус модуля**, **Порядок сортування**

### Дебаг
У `catalog/controller/extension/payment/ipay.php` змініть:
```php
const IPAY_DEBUG_MODE = true;
```
Усі запити пише в `system/storage/logs/error.log`

---

## 🇬🇧 English Version

### Features
- Supports iPay.ua API 3.02
- Customer is redirected to payment page
- Order status updated via callback
- Supports sandbox & live mode
- Debug logging for troubleshooting

### Installation
1. Download latest release
2. Copy contents of `upload/` to site root
3. In admin: Extensions → Installer → Upload the module
4. Extensions → Modifications → Refresh (blue button)
5. Extensions → Payments → iPay Checkout API → Install

### Configuration
- **Merchant ID (mch_id)** — provided by iPay
- **Signature Key (sign_key)** — your private key
- **Test Mode** — enable for sandbox
- **Order Status / Failed Status** — set as needed
- **Geo Zone**, **Module Status**, **Sort Order**

### Debugging
Edit `catalog/controller/extension/payment/ipay.php`:
```php
const IPAY_DEBUG_MODE = true;
```
Logs will appear in `system/storage/logs/error.log`

---

## 📜 Ліцензія 

Цей модуль поширюється на умовах ліцензії **MIT**.

Він надається «як є» (AS IS), без будь-яких гарантій, явних чи неявних, включаючи, але не обмежуючись гарантіями товарної придатності або придатності для конкретної мети. Автор не несе відповідальності за будь-які наслідки використання або несправної роботи модуля.

Ви можете:
- використовувати,
- змінювати,
- поширювати,
- включати до комерційних продуктів.

Головне — зберігайте повідомлення про авторське право та ліцензійні умови.


## 📜 License

MIT License

Copyright (c) 2025 [flancer.eu](https://flancer.eu)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
