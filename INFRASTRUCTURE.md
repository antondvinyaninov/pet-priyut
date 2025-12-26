# Инфраструктура проекта

## Обзор

Проект развернут на сервере **88.218.121.213** с использованием панели управления **Easypanel**.

**Провайдер:** hoztnode.net  
**ОС:** Ubuntu 24.04 LTS  
**Docker:** Установлен  
**Firewall:** Отключен (ufw disabled)

## SSH Доступ

**IP:** 88.218.121.213  
**User:** root  
**Команда подключения:**
```bash
ssh root@88.218.121.213
```

## VPN Сервисы

### 1. Outline VPN ✅

**Статус:** Работает

**Management Key:**
```json
{
  "apiUrl": "https://88.218.121.213:37375/rQ8eWVlqpfT2LoIa_4VGRQ",
  "certSha256": "51CAE6A9E60CA22C3B4B68F28525ADEB236456BE9691E2925A0408126E0CF108"
}
```

**Порты:**
- TCP: 37375 (управление)
- TCP/UDP: 26144 (VPN)

### 2. Pritunl VPN (OpenVPN) ✅

**URL:** https://88.218.121.213:9443  
**Username:** pritunl  
**Password:** z0q37maU46zg  
**Setup Key:** 65112339eb2a40d7a77e5a9482a78ef7  
**Организация:** LK  
**Пользователь:** vpn  
**Порт VPN:** 1194/udp

**Для роутера:** Скачать .ovpn файл из веб-интерфейса

#### Настройка роутера Keenetic для VPN

1. Установить компонент OpenVPN-клиент
2. Скачать .ovpn файл из Pritunl (https://88.218.121.213:9443)
3. В роутере: Интернет → Другие подключения → VPN-подключения
4. Добавить подключение → OpenVPN → Загрузить файл конфигурации
5. Включить опцию "Использовать для выхода в интернет"
6. Применить и подключиться

## Easypanel - Панель управления

**URL:** http://88.218.121.213:3000  
**Статус:** Работает

Easypanel - это панель управления для деплоя Docker-приложений. Позволяет:
- Деплоить приложения из GitHub
- Управлять переменными окружения
- Просматривать логи
- Управлять базами данных и сервисами

## Структура сервисов

### 1. Основное приложение (Blog)

**Название сервиса:** `test-github`
**URL:** https://test-github.crv1ic.easypanel.host
**Тип:** Astro + React приложение

#### GitHub подключение:
- **Repository:** `https://github.com/antondvinyaninov/antondvinyaninov.github.io.git`
- **Remote name:** `origin2`
- **Branch:** `main`

#### Переменные окружения:
```env
SUPABASE_URL=https://baze-supabase.crv1ic.easypanel.host
SUPABASE_ANON_KEY=<anon_key>
SUPABASE_SERVICE_KEY=<service_key>
```

### 2. Supabase

**Название сервиса:** `baze-supabase`
**URL:** https://baze-supabase.crv1ic.easypanel.host
**Studio URL:** https://baze-supabase.crv1ic.easypanel.host (Supabase Studio)

#### Доступы:
- **Username:** `supabase`
- **Password:** `this_password_is_insecure_and_should_be_updated`

#### Структура базы данных:

**Таблица `posts`:**
```sql
CREATE TABLE posts (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  slug TEXT UNIQUE NOT NULL,
  excerpt TEXT,
  category JSONB NOT NULL,
  author JSONB NOT NULL,
  date TEXT NOT NULL,
  read_time TEXT,
  views INTEGER DEFAULT 0,
  type TEXT DEFAULT 'article',
  cover_image TEXT,
  featured BOOLEAN DEFAULT false,
  likes INTEGER DEFAULT 0,
  comments INTEGER DEFAULT 0,
  created_at TIMESTAMPTZ DEFAULT NOW(),
  updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_posts_slug ON posts(slug);
CREATE INDEX idx_posts_featured ON posts(featured);
CREATE INDEX idx_posts_created_at ON posts(created_at DESC);
```

**Storage Bucket `blog-images`:**
- **Тип:** Public
- **Лимит размера файла:** 50 MB
- **Allowed MIME types:** Пусто (все типы)
- **Политики RLS:**
  - SELECT: `true` (публичный доступ на чтение)
  - INSERT: `true` (публичный доступ на загрузку через API)

## Процесс деплоя

### Первоначальная настройка

1. **Подключение GitHub репозитория:**
   - В Easypanel создать новый App
   - Выбрать "GitHub" как источник
   - Подключить репозиторий `antondvinyaninov/antondvinyaninov.github.io`
   - Выбрать ветку `main`

2. **Настройка переменных окружения:**
   - Перейти в настройки приложения
   - Добавить переменные `SUPABASE_URL`, `SUPABASE_ANON_KEY`, `SUPABASE_SERVICE_KEY`

3. **Настройка Dockerfile:**
   - Easypanel автоматически использует `Dockerfile` из корня проекта
   - Убедиться что порт 4321 открыт

4. **Настройка домена:**
   - Easypanel автоматически создает поддомен `*.crv1ic.easypanel.host`

### Обновление проекта

#### Автоматический деплой:
```bash
# Локально
git add .
git commit -m "Update"
git push origin2 main
```

Easypanel автоматически подхватит изменения и пересоберет контейнер.

#### Ручной деплой через Easypanel:
1. Зайти в панель Easypanel
2. Открыть приложение `test-github`
3. Нажать "Rebuild" или "Redeploy"

### Просмотр логов

В Easypanel:
1. Открыть приложение
2. Перейти в раздел "Logs"
3. Выбрать контейнер для просмотра логов

## Supabase настройка

### Создание Storage Bucket

1. Открыть Supabase Studio: https://baze-supabase.crv1ic.easypanel.host
2. Войти с учетными данными
3. Перейти в Storage → Create bucket
4. Настройки:
   - Name: `blog-images`
   - Public: ✅
   - File size limit: `52428800` (50 MB)
   - Allowed MIME types: оставить пустым

### Настройка RLS политик для Storage

**Политика 1: Public SELECT**
```
Policy name: Public Access
Allowed operation: SELECT
Policy definition: true
```

**Политика 2: Public INSERT**
```
Policy name: Public Upload
Allowed operation: INSERT
Policy definition: true
WITH CHECK expression: true
```

### Создание таблицы posts

Выполнить SQL из раздела "Структура базы данных" выше в SQL Editor.

### Миграция данных

```bash
# Локально запустить миграцию
curl http://localhost:4321/api/migrate-to-supabase
```

## API Endpoints

### Загрузка изображений
**POST** `/api/upload-image`
- Принимает: `FormData` с полем `image`
- Валидация: max 10MB, форматы jpg/jpeg/png/webp
- Конвертация: автоматически в WebP (quality 85%, max width 1920px)
- Возвращает: `{ url: string, savings: number }`

### Получение медиа
**GET** `/api/media.json`
- Возвращает список изображений из Supabase Storage

### Работа с постами
**GET** `/api/posts.json` - список всех постов
**GET** `/api/posts/[slug].json` - конкретный пост
**POST** `/api/posts` - создание поста
**PUT** `/api/posts/[id]` - обновление поста
**DELETE** `/api/posts/[id]` - удаление поста

## Troubleshooting

### Проблема: Изображения не загружаются

**Решение:**
1. Проверить политики RLS в Supabase Storage
2. Убедиться что bucket `blog-images` публичный
3. Проверить переменные окружения в Easypanel

### Проблема: Ошибка при деплое

**Решение:**
1. Проверить логи в Easypanel
2. Убедиться что все зависимости установлены в `package.json`
3. Проверить что `Dockerfile` корректный

### Проблема: База данных недоступна

**Решение:**
1. Проверить что Supabase сервис запущен в Easypanel
2. Проверить переменные `SUPABASE_URL` и ключи
3. Проверить логи Supabase контейнера

## Backup и восстановление

### Backup базы данных

В Supabase Studio:
1. Database → Backups
2. Create backup

### Backup изображений

Изображения хранятся в Supabase Storage bucket `blog-images`. Для backup:
1. Использовать Supabase CLI
2. Или скачать через Storage API

## Мониторинг

- **Логи приложения:** Easypanel → App → Logs
- **Логи Supabase:** Easypanel → Supabase service → Logs
- **Метрики:** Easypanel показывает CPU/Memory usage

### Полезные команды для мониторинга

```bash
# Список всех Docker контейнеров
docker ps -a

# Просмотр логов контейнера
docker logs -f <container_name>

# Использование диска
df -h

# Использование памяти
free -h

# Процессы
htop

# Сетевые подключения
netstat -tulpn

# Перезапуск контейнера
docker restart <container_name>
```

## Резервное копирование

### Важные директории

- `~/pritunl/` - данные Pritunl
- Easypanel и Supabase управляются через Docker volumes

### Создание бэкапа

```bash
# Бэкап Pritunl
tar -czf pritunl-backup-$(date +%Y%m%d).tar.gz ~/pritunl/

# Скачать на локальный компьютер
scp root@88.218.121.213:~/pritunl-backup-*.tar.gz ~/Downloads/
```

### Backup базы данных

В Supabase Studio:
1. Database → Backups
2. Create backup

### Backup изображений

Изображения хранятся в Supabase Storage bucket `blog-images`. Для backup:
1. Использовать Supabase CLI
2. Или скачать через Storage API

## Безопасность

⚠️ **Рекомендации:**

- [ ] Сменить пароль Pritunl (текущий: z0q37maU46zg)
- [ ] Сменить пароль Supabase Dashboard
- [ ] Настроить firewall (ufw) - сейчас отключен
- [ ] Регулярно обновлять систему: `apt update && apt upgrade`
- [ ] Настроить автоматические бэкапы

### Обновление системы

```bash
# Обновить пакеты
apt update && apt upgrade -y

# Перезагрузка (если требуется)
reboot
```

## Активные Docker контейнеры

- `outline` - Outline VPN
- `pritunl` - Pritunl VPN
- `easypanel` - Панель управления
- `supabase-*` - Множество контейнеров Supabase (db, auth, rest, storage, kong, studio и др.)
- `n8n` - Автоматизация и интеграции

## Контакты и доступы

### Сервер
- **IP:** 88.218.121.213
- **SSH:** `ssh root@88.218.121.213`
- **Провайдер:** hoztnode.net

### Панели управления
- **Easypanel:** http://88.218.121.213:3000
- **Supabase Studio:** https://baze-supabase.crv1ic.easypanel.host
- **Pritunl VPN:** https://88.218.121.213:9443
- **Production site:** https://test-github.crv1ic.easypanel.host

### GitHub
- **Repository:** https://github.com/antondvinyaninov/antondvinyaninov.github.io.git
- **Remote:** origin2

### Supabase
- **Username:** supabase
- **Password:** this_password_is_insecure_and_should_be_updated

### Pritunl VPN
- **Username:** pritunl
- **Password:** z0q37maU46zg
- **Setup Key:** 65112339eb2a40d7a77e5a9482a78ef7

---

**Дата создания:** 17 декабря 2025  
**Последнее обновление:** 18 декабря 2025
