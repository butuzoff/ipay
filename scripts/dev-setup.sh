#!/bin/bash

# Скрипт настройки среды разработки для iPay OpenCart модуля
set -e

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${GREEN}🔧 Настройка среды разработки iPay OpenCart модуля${NC}"

# Проверяем, что мы находимся в правильной директории
if [[ ! -f "README.md" ]] || [[ ! -d "upload" ]]; then
    echo -e "${RED}❌ Ошибка: запустите скрипт из корня проекта${NC}"
    exit 1
fi

# Настройка Git hooks и шаблонов
echo -e "${YELLOW}📝 Настройка Git...${NC}"

# Настраиваем шаблон коммитов
if [[ -f ".gitmessage" ]]; then
    git config commit.template .gitmessage
    echo -e "${GREEN}✅ Шаблон коммитов настроен${NC}"
fi

# Настраиваем автор коммитов если не настроен
if ! git config user.name > /dev/null 2>&1; then
    echo -e "${YELLOW}⚙️  Настройте ваше имя для Git:${NC}"
    read -p "Введите ваше имя: " git_name
    git config user.name "$git_name"
fi

if ! git config user.email > /dev/null 2>&1; then
    echo -e "${YELLOW}⚙️  Настройте ваш email для Git:${NC}"
    read -p "Введите ваш email: " git_email
    git config user.email "$git_email"
fi

# Создаем папки для разработки если не существуют
echo -e "${YELLOW}📁 Создание структуры директорий...${NC}"

mkdir -p {logs,temp,backup}

# Создаем символические ссылки для удобной разработки (опционально)
echo -e "${YELLOW}🔗 Хотите создать символические ссылки для тестирования?${NC}"
read -p "Укажите путь к корню OpenCart для тестирования (или Enter для пропуска): " opencart_path

if [[ -n "$opencart_path" ]] && [[ -d "$opencart_path" ]]; then
    echo -e "${BLUE}🔗 Создание символических ссылок...${NC}"
    
    # Создаем символические ссылки
    ln -sfn "$(pwd)/upload/admin" "$opencart_path/admin/controller/extension/payment/ipay_link"
    ln -sfn "$(pwd)/upload/catalog" "$opencart_path/catalog/controller/extension/payment/ipay_link"
    
    echo -e "${GREEN}✅ Символические ссылки созданы${NC}"
    echo -e "${YELLOW}⚠️  Не забудьте удалить их перед продакшном!${NC}"
fi

# Создаем файл для локальных настроек разработки
if [[ ! -f ".env.local" ]]; then
    cat > ".env.local" << EOF
# Локальные настройки разработки
# Этот файл не попадает в git

# Пути для тестирования
OPENCART_PATH="$opencart_path"

# Настройки iPay для тестирования
IPAY_TEST_MCH_ID="your_test_merchant_id"
IPAY_TEST_SIGN_KEY="your_test_signature_key"

# Настройки базы данных для тестов (если нужны)
TEST_DB_HOST="localhost"
TEST_DB_NAME="opencart_test"
TEST_DB_USER="test_user"
TEST_DB_PASS="test_password"
EOF
    echo -e "${GREEN}✅ Создан файл .env.local для локальных настроек${NC}"
fi

# Создаем pre-commit hook для проверки формата коммитов
if [[ ! -f ".git/hooks/commit-msg" ]]; then
    cat > ".git/hooks/commit-msg" << 'EOF'
#!/bin/bash
# Pre-commit hook для проверки формата коммитов

commit_regex='^(feat|fix|docs|style|refactor|perf|test|chore)(\(.+\))?: .{1,50}'

if ! grep -qE "$commit_regex" "$1"; then
    echo "❌ Неверный формат коммита!"
    echo ""
    echo "Используйте формат: <type>: <description>"
    echo ""
    echo "Типы:"
    echo "  feat:     новая функциональность"
    echo "  fix:      исправление бага"
    echo "  docs:     изменения в документации"
    echo "  style:    форматирование кода"
    echo "  refactor: рефакторинг"
    echo "  perf:     улучшение производительности"
    echo "  test:     добавление тестов"
    echo "  chore:    обслуживание проекта"
    echo ""
    echo "Примеры:"
    echo "  feat: добавлена поддержка новых валют"
    echo "  fix: исправлена ошибка валидации"
    echo ""
    exit 1
fi
EOF
    chmod +x ".git/hooks/commit-msg"
    echo -e "${GREEN}✅ Установлен pre-commit hook для проверки коммитов${NC}"
fi

# Проверяем наличие необходимых инструментов
echo -e "${YELLOW}🔍 Проверка инструментов разработки...${NC}"

# Проверяем PHP
if command -v php > /dev/null 2>&1; then
    PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
    echo -e "${GREEN}✅ PHP $PHP_VERSION установлен${NC}"
    
    if [[ $(echo "$PHP_VERSION >= 7.1" | bc -l) -eq 1 ]]; then
        echo -e "${GREEN}✅ Версия PHP подходит для OpenCart 3.x${NC}"
    else
        echo -e "${YELLOW}⚠️  Рекомендуется PHP 7.1+ для OpenCart 3.x${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  PHP не найден${NC}"
fi

# Проверяем curl
if command -v curl > /dev/null 2>&1; then
    echo -e "${GREEN}✅ curl установлен${NC}"
else
    echo -e "${YELLOW}⚠️  curl не найден (нужен для тестирования API)${NC}"
fi

# Проверяем git
if command -v git > /dev/null 2>&1; then
    echo -e "${GREEN}✅ git установлен${NC}"
else
    echo -e "${RED}❌ git не найден${NC}"
fi

# Информация о следующих шагах
echo -e "${GREEN}🎉 Настройка завершена!${NC}"
echo ""
echo -e "${BLUE}📋 Следующие шаги:${NC}"
echo "1. Скопируйте файлы из upload/ в ваш тестовый OpenCart"
echo "2. Настройте модуль в админке OpenCart"
echo "3. Настройте тестовые данные iPay в .env.local"
echo "4. Начинайте разработку!"
echo ""
echo -e "${BLUE}🔧 Полезные команды:${NC}"
echo "  ./scripts/prepare-release.sh  - подготовка релиза"
echo "  git log --oneline             - просмотр истории коммитов"
echo "  git status                    - статус изменений"
echo ""
echo -e "${BLUE}📚 Документация:${NC}"
echo "  README.md        - основная документация"
echo "  CONTRIBUTING.md  - руководство по разработке"

# Показываем текущие настройки git
echo ""
echo -e "${BLUE}⚙️  Текущие настройки Git:${NC}"
echo "  Имя: $(git config user.name)"
echo "  Email: $(git config user.email)"
echo "  Шаблон коммитов: $(git config commit.template || echo 'не настроен')"