#!/bin/bash

# Скрипт подготовки релиза iPay OpenCart модуля
set -e

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 Подготовка релиза iPay OpenCart модуля${NC}"

# Проверяем, что мы находимся в правильной директории
if [[ ! -f "README.md" ]] || [[ ! -d "upload" ]]; then
    echo -e "${RED}❌ Ошибка: запустите скрипт из корня проекта${NC}"
    exit 1
fi

# Получаем текущую версию из последнего тега
CURRENT_VERSION=$(git describe --tags --abbrev=0 2>/dev/null | sed 's/^v//' || echo "0.0.0")
echo -e "${YELLOW}📋 Текущая версия: v${CURRENT_VERSION}${NC}"

# Предлагаем новую версию
IFS='.' read -ra VERSION_PARTS <<< "$CURRENT_VERSION"
MAJOR=${VERSION_PARTS[0]:-0}
MINOR=${VERSION_PARTS[1]:-0}
PATCH=${VERSION_PARTS[2]:-0}

echo -e "${YELLOW}🔢 Выберите тип релиза:${NC}"
echo "1) Patch (v${MAJOR}.${MINOR}.$((PATCH + 1))) - исправления багов"
echo "2) Minor (v${MAJOR}.$((MINOR + 1)).0) - новая функциональность"
echo "3) Major (v$((MAJOR + 1)).0.0) - критические изменения"
echo "4) Указать версию вручную"

read -p "Выберите вариант (1-4): " choice

case $choice in
    1)
        NEW_VERSION="${MAJOR}.${MINOR}.$((PATCH + 1))"
        ;;
    2)
        NEW_VERSION="${MAJOR}.$((MINOR + 1)).0"
        ;;
    3)
        NEW_VERSION="$((MAJOR + 1)).0.0"
        ;;
    4)
        read -p "Введите версию (без 'v'): " NEW_VERSION
        ;;
    *)
        echo -e "${RED}❌ Неверный выбор${NC}"
        exit 1
        ;;
esac

echo -e "${GREEN}✅ Новая версия: v${NEW_VERSION}${NC}"

# Проверяем статус git
if ! git diff-index --quiet HEAD --; then
    echo -e "${RED}❌ У вас есть незакоммиченные изменения${NC}"
    echo "Закоммитьте или отмените изменения перед созданием релиза"
    exit 1
fi

# Создаем директорию для сборки
BUILD_DIR="build"
RELEASE_DIR="${BUILD_DIR}/ipay-opencart-v${NEW_VERSION}"

echo -e "${YELLOW}📦 Создание сборки...${NC}"

# Очищаем старую сборку
rm -rf "$BUILD_DIR"
mkdir -p "$RELEASE_DIR"

# Копируем файлы модуля
cp -r upload/ "$RELEASE_DIR/"
cp README.md "$RELEASE_DIR/"

# Создаем информационный файл о версии
cat > "$RELEASE_DIR/VERSION.txt" << EOF
iPay OpenCart Module v${NEW_VERSION}
Дата сборки: $(date '+%Y-%m-%d %H:%M:%S')
Разработчик: flancer.eu
Сайт: https://flancer.eu
EOF

# Создаем архивы
echo -e "${YELLOW}📦 Создание архивов...${NC}"

cd "$BUILD_DIR"

# ZIP архив для OpenCart
zip -r "ipay-opencart-v${NEW_VERSION}.zip" "ipay-opencart-v${NEW_VERSION}/" > /dev/null
echo -e "${GREEN}✅ Создан: ${BUILD_DIR}/ipay-opencart-v${NEW_VERSION}.zip${NC}"

# TAR.GZ архив
tar -czf "ipay-opencart-v${NEW_VERSION}.tar.gz" "ipay-opencart-v${NEW_VERSION}/"
echo -e "${GREEN}✅ Создан: ${BUILD_DIR}/ipay-opencart-v${NEW_VERSION}.tar.gz${NC}"

cd ..

# Показываем содержимое архива
echo -e "${YELLOW}📋 Содержимое релиза:${NC}"
echo "$(cd "$RELEASE_DIR" && find . -type f | head -20)"
if [[ $(cd "$RELEASE_DIR" && find . -type f | wc -l) -gt 20 ]]; then
    echo "... и еще $(cd "$RELEASE_DIR" && find . -type f | wc -l | awk '{print $1-20}') файлов"
fi

# Генерируем changelog
echo -e "${YELLOW}📝 Генерация changelog...${NC}"

LAST_TAG=$(git describe --tags --abbrev=0 HEAD~1 2>/dev/null || echo "")
CHANGELOG_FILE="${BUILD_DIR}/CHANGELOG-v${NEW_VERSION}.md"

cat > "$CHANGELOG_FILE" << EOF
# Changelog для v${NEW_VERSION}

Дата: $(date '+%Y-%m-%d')

## Изменения

EOF

if [[ -n "$LAST_TAG" ]]; then
    echo "### Коммиты с $LAST_TAG:" >> "$CHANGELOG_FILE"
    git log --pretty=format:"- %s (%an)" $LAST_TAG..HEAD >> "$CHANGELOG_FILE"
else
    echo "### Первый релиз:" >> "$CHANGELOG_FILE"
    git log --pretty=format:"- %s (%an)" HEAD >> "$CHANGELOG_FILE"
fi

echo -e "${GREEN}✅ Changelog создан: $CHANGELOG_FILE${NC}"

# Предлагаем создать тег и пуш
echo -e "${YELLOW}🏷️  Готов создать тег и отправить в репозиторий?${NC}"
read -p "Создать тег v${NEW_VERSION} и отправить? (y/N): " confirm

if [[ $confirm =~ ^[Yy]$ ]]; then
    git tag -a "v${NEW_VERSION}" -m "Release v${NEW_VERSION}"
    git push origin "v${NEW_VERSION}"
    echo -e "${GREEN}✅ Тег v${NEW_VERSION} создан и отправлен${NC}"
    echo -e "${GREEN}🎉 GitHub Actions автоматически создаст релиз${NC}"
else
    echo -e "${YELLOW}⚠️  Тег не создан. Для ручного создания выполните:${NC}"
    echo "git tag -a 'v${NEW_VERSION}' -m 'Release v${NEW_VERSION}'"
    echo "git push origin 'v${NEW_VERSION}'"
fi

echo -e "${GREEN}🎯 Релиз готов! Файлы находятся в директории: ${BUILD_DIR}${NC}"
echo -e "${GREEN}📦 Архивы:${NC}"
echo "  - ${BUILD_DIR}/ipay-opencart-v${NEW_VERSION}.zip"
echo "  - ${BUILD_DIR}/ipay-opencart-v${NEW_VERSION}.tar.gz"
echo -e "${GREEN}📝 Changelog: ${CHANGELOG_FILE}${NC}"