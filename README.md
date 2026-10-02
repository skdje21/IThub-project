# IT Hub — Система управления IT-заявками

Внутренняя система для управления IT-заявками и учёта оборудования. Сотрудники подают заявки в IT-отдел (установка ПО, доступ к VPN, ремонт, запрос оборудования), заявки проходят цепочку согласования, IT-отдел отслеживает выполнение.

## Стек технологий

- **Backend:** PHP 8.3, Laravel 11, Laravel Sanctum
- **Frontend:** Vue 3, Vue Router, Pinia, Axios, Vite
- **Database:** MySQL 8
- **Cache/Queue:** Redis
- **Инфраструктура:** Docker, Nginx

## Быстрый старт

```bash
# 1. Клонировать репозиторий
git clone <repo-url> && cd ithub-project

# 2. Скопировать env
cp backend/.env.example backend/.env

# 3. Поднять контейнеры
docker-compose up -d

# 4. Установить зависимости и подготовить БД
docker exec ithub-app composer install
docker exec ithub-app php artisan key:generate
docker exec ithub-app php artisan migrate --seed

# 5. Frontend
cd frontend && npm install && npm run dev
```

Приложение будет доступно:
- **API:** http://localhost:8000/api/v1
- **Frontend:** http://localhost:5173

## Тестовые аккаунты

| Роль     | Email              | Пароль   |
|----------|--------------------|----------|
| Admin    | admin@ithub.test   | password |
| Manager  | manager@ithub.test | password |

## Структура API

### Аутентификация
| Метод | URL               | Описание            |
|-------|-------------------|----------------------|
| POST  | /api/v1/register  | Регистрация          |
| POST  | /api/v1/login     | Вход                 |
| POST  | /api/v1/logout    | Выход                |
| GET   | /api/v1/me        | Текущий пользователь |

### Заявки (Tickets)
| Метод  | URL                          | Описание              |
|--------|------------------------------|-----------------------|
| GET    | /api/v1/tickets              | Список (фильтры, пагинация) |
| POST   | /api/v1/tickets              | Создать заявку        |
| GET    | /api/v1/tickets/{id}         | Детали заявки         |
| PUT    | /api/v1/tickets/{id}         | Обновить заявку       |
| DELETE | /api/v1/tickets/{id}         | Удалить заявку        |
| PATCH  | /api/v1/tickets/{id}/status  | Сменить статус        |

### Комментарии
| Метод | URL                              | Описание           |
|-------|----------------------------------|---------------------|
| GET   | /api/v1/tickets/{id}/comments    | Список комментариев |
| POST  | /api/v1/tickets/{id}/comments    | Добавить комментарий|

### Оборудование (Equipment)
| Метод  | URL                              | Описание              |
|--------|----------------------------------|-----------------------|
| GET    | /api/v1/equipment                | Список оборудования   |
| POST   | /api/v1/equipment                | Добавить              |
| GET    | /api/v1/equipment/{id}           | Детали                |
| PUT    | /api/v1/equipment/{id}           | Обновить              |
| DELETE | /api/v1/equipment/{id}           | Удалить               |
| PATCH  | /api/v1/equipment/{id}/assign    | Назначить сотруднику  |

### Справочники и дашборд
| Метод | URL                      | Описание                    |
|-------|--------------------------|-----------------------------|
| GET   | /api/v1/departments      | Список отделов              |
| GET   | /api/v1/categories       | Категории заявок            |
| GET   | /api/v1/dashboard/stats  | Статистика по заявкам       |

## Фильтрация заявок

`GET /api/v1/tickets?status=pending&priority=high&category_id=1&assignee_id=2`

## Статусная машина заявок

```
draft → pending → approved → in_progress → done
                ↘ rejected ↗
```

## Архитектура

```
backend/
├── app/
│   ├── Enums/           # Role, TicketStatus, Priority, EquipmentStatus
│   ├── Http/
│   │   ├── Controllers/Api/V1/   # Ресурсные контроллеры
│   │   ├── Requests/             # Form Request валидация
│   │   └── Resources/            # API Resources
│   ├── Models/           # Eloquent модели с отношениями
│   ├── Notifications/    # Email-уведомления (Queue)
│   ├── Policies/         # Авторизация (Ticket, Equipment)
│   └── Services/         # Бизнес-логика (TicketService)
├── database/
│   ├── factories/        # Фабрики для тестов
│   ├── migrations/       # Миграции БД
│   └── seeders/          # Тестовые данные
└── tests/Feature/        # Feature-тесты API

frontend/
├── src/
│   ├── api/              # Axios instance
│   ├── layouts/          # AppLayout с сайдбаром
│   ├── router/           # Vue Router + guards
│   ├── stores/           # Pinia (auth)
│   └── views/            # Страницы
```

## Скриншоты

*В разработке*
