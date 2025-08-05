# Contributing to iPay OpenCart Module

Спасибо за ваш интерес к улучшению модуля iPay для OpenCart! 

## 🚀 Быстрый старт

1. **Fork** этого репозитория
2. **Clone** ваш fork локально:
   ```bash
   git clone https://github.com/your-username/ipay.git
   cd ipay
   ```
3. Создайте **новую ветку** для ваших изменений:
   ```bash
   git checkout -b feature/your-feature-name
   ```

## 📝 Правила коммитов

Мы используем **Conventional Commits** для автоматической генерации changelog:

### Формат коммитов:
```
<type>: <description>

[optional body]

[optional footer]
```

### Типы коммитов:
- `feat:` - новая функциональность
- `fix:` - исправление бага  
- `docs:` - изменения в документации
- `style:` - форматирование, стили
- `refactor:` - рефакторинг кода
- `perf:` - улучшение производительности
- `test:` - добавление тестов
- `chore:` - обслуживание, сборка

### Примеры:
```bash
feat: добавлена поддержка новых валют
fix: исправлена ошибка валидации подписи
docs: обновлена документация по установке
refactor: улучшена структура кода контроллера
```

### Настройка шаблона коммитов:
```bash
git config commit.template .gitmessage
```

## 🔄 Процесс разработки

1. **Перед началом работы** - обновите main ветку:
   ```bash
   git checkout main
   git pull origin main
   ```

2. **Создайте ветку** от актуального main:
   ```bash
   git checkout -b fix/payment-validation
   ```

3. **Внесите изменения** и тестируйте локально

4. **Коммитьте** с правильным форматом:
   ```bash
   git add .
   git commit -m "fix: исправлена валидация данных оплаты"
   ```

5. **Push** в ваш fork:
   ```bash
   git push origin fix/payment-validation
   ```

6. **Создайте Pull Request** в основной репозиторий

## 🧪 Тестирование

- Тестируйте изменения на **OpenCart 3.x**
- Проверьте работу в **тестовом режиме** iPay
- Убедитесь, что **не сломаны** существующие функции

## 📦 Автоматические релизы

Релизы создаются автоматически при:

1. **Пуше тега** вида `v*` (например `v1.2.3`)
2. **Автоматически** при накоплении изменений в main ветке

### Ручное создание релиза:
```bash
./scripts/prepare-release.sh
```

## 🏷️ Версионирование

Мы используем [Semantic Versioning](https://semver.org/):

- `MAJOR` - критические изменения, несовместимые с предыдущими версиями
- `MINOR` - новая функциональность, обратно совместимая
- `PATCH` - исправления багов

### Ключевые слова для автоматического версионирования:

- **MAJOR**: `BREAKING CHANGE`, `breaking`, `major`
- **MINOR**: `feat`, `feature`, `minor`  
- **PATCH**: `fix`, `docs`, `style`, `refactor`, `perf`, `test`

## 📋 Checklist для Pull Request

- [ ] Изменения протестированы на OpenCart 3.x
- [ ] Код соответствует стилю проекта
- [ ] Commit messages следуют Conventional Commits
- [ ] Документация обновлена (если нужно)
- [ ] Нет конфликтов с main веткой

## 🐛 Сообщения об ошибках

При создании issue укажите:

1. **Версия OpenCart** (3.0.x.x)
2. **Версия PHP** 
3. **Версия модуля iPay**
4. **Шаги для воспроизведения**
5. **Ожидаемое поведение**
6. **Фактическое поведение**
7. **Логи ошибок** (если есть)

## 📞 Связь

- 👤 **LinkedIn**: [linkedin.com/in/butuzoff](https://www.linkedin.com/in/butuzoff/)
- 💬 **Telegram**: [@butuzoff](https://t.me/butuzoff)
- 🌐 **Сайт**: [flancer.eu](https://flancer.eu)

---

**Спасибо за вклад в развитие проекта! 🚀**