# Соответствие официальной документации iPay API v1.14

Данный документ описывает, как модуль iPay для OpenCart соответствует [официальной документации iPay API v1.14](https://checkout.ipay.ua/doc).

## ✅ Полное соответствие документации

### 🔗 Ссылка на документацию
- **Официальная документация**: [https://checkout.ipay.ua/doc](https://checkout.ipay.ua/doc)
- **Версия API**: v1.14 (актуальная на 2025-03-21)

### 📋 Реализованные согласно документации:

#### 1. **Алгоритм подписи**
Точно следует официальному алгоритму:
```php
// Согласно документации iPay API v1.14
$salt = sha1(microtime(true));
$sign = hash_hmac('sha512', $salt, $sign_key);
```

#### 2. **Структура XML запроса PaymentCreate**
Полностью соответствует документации:
```xml
<?xml version="1.0" encoding="utf-8"?>
<payment>
    <auth>
        <mch_id>ID мерчанта</mch_id>
        <salt>Сіль підпису</salt>
        <sign>Підпис запиту</sign>
    </auth>
    <urls>
        <good>URL успеха</good>
        <bad>URL неудачи</bad>
        <auto_redirect_good>1</auto_redirect_good>
        <auto_redirect_bad>1</auto_redirect_bad>
        <retry_button>1</retry_button>
        <cancel_button>1</cancel_button>
    </urls>
    <transactions>
        <transaction>
            <amount>Сумма в копейках</amount>
            <currency>Валюта</currency>
            <desc>Описание</desc>
            <info>{"order_id":123}</info>
        </transaction>
    </transactions>
    <lifetime>24</lifetime>
    <lang>ru/ua/en</lang>
</payment>
```

#### 3. **Структура callback нотификации**
Обрабатывает согласно документации:
```xml
<?xml version="1.0" encoding="utf-8"?>
<payment ident="WGUID">
    <status>1-5</status>
    <amount>Сумма</amount>
    <currency>UAH/USD/EUR</currency>
    <timestamp>UNIX timestamp</timestamp>
    <transactions>
        <transaction tid="ID">
            <mch_id>ID мерчанта</mch_id>
            <smch_id>Юр. лицо</smch_id>
            <invoice>Сумма платежа</invoice>
            <amount>Сумма к оплате</amount>
            <desc>Описание</desc>
            <info>{"order_id":123}</info>
        </transaction>
    </transactions>
    <auth>
        <salt>Соль</salt>
        <sign>Подпись</sign>
    </auth>
    <payment_type>Manual/GooglePay/ApplePay</payment_type>
</payment>
```

#### 4. **URL адреса API**
- **Production**: `https://checkout.ipay.ua/api302`
- **Sandbox**: `https://sandbox-checkout.ipay.ua/api302`

#### 5. **Поддерживаемые валюты**
- UAH (основная)
- USD 
- EUR

#### 6. **Статусы платежей**
Согласно документации:
- `1` - Зарегистрирован
- `3` - Авторизован
- `4` - Неуспешный
- `5` - Успешный

#### 7. **Новые возможности API v1.14**
- ✅ `auto_redirect_good` - автоматическое перенаправление при успехе
- ✅ `auto_redirect_bad` - автоматическое перенаправление при неудаче
- ✅ `retry_button` - кнопка повтора платежа
- ✅ `cancel_button` - кнопка отмены
- ✅ `payment_type` - тип платежа в callback

### 🔒 Безопасность

#### 1. **HTTPS требования**
- Обязательный HTTPS для production callback'ов
- SSL верификация для исходящих запросов

#### 2. **Проверка подписи**
- Использование `hash_equals()` для защиты от timing attacks
- Валидация согласно официальному алгоритму

#### 3. **Валидация данных**
- Проверка формата Merchant ID (числовой)
- Минимальная длина ключа (32 символа)
- Экранирование XML данных

### 🧪 Sandbox тестирование

#### Тестовые карты (согласно документации):
**Успешные платежи:**
- 3333333333333331
- 3333333333332705
- 5375913862726080

**Условно успешные (до 100 грн):**
- 3333333333333430
- 5117962099480048

**Неуспешные:**
- 3333333333333349
- 4280596505234682

**Предавторизация:**
- 3333333333333356

### 📊 Логирование

При включенном debug режиме:
```php
const IPAY_DEBUG_MODE = true;
```

Логируется:
- Исходящие XML запросы
- Входящие callback уведомления
- cURL verbose логи
- Ошибки валидации
- Обновления статусов заказов

### 🔧 Настройка в iPay

1. **Callback URL**: `https://your-site.com/index.php?route=extension/payment/ipay/callback`
2. **Формат**: XML
3. **Метод**: POST
4. **Кодировка**: UTF-8

### 📈 История версий

| Версия модуля | Версия API | Дата       | Изменения |
|---------------|------------|------------|-----------|
| 1.0.0         | 3.02       | 2024       | Базовая реализация |
| 2.0.0         | v1.14      | 2025-01-25 | Полное соответствие документации |

### ⚡ Производительность

- Таймауты: 10 сек подключение, 30 сек выполнение
- Отключен CURLOPT_FOLLOWLOCATION для безопасности
- User-Agent: `OpenCart-iPay-Module/2.0`

### 🌍 Мультиязычность

Поддерживаемые языки интерфейса:
- `ru` - русский
- `ua` - украинский  
- `en` - английский

### 📝 Changelog

Все изменения документируются согласно требованиям:
- Conventional Commits
- Semantic Versioning
- Детальный CHANGELOG.md

---

**Заключение**: Модуль полностью соответствует официальной документации iPay API v1.14 и реализует все описанные в ней возможности.