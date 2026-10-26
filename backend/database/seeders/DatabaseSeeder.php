<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EquipmentStatus;
use App\Enums\Priority;
use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $baseDate = Carbon::parse('2026-08-20 09:00:00');

        // Departments — created when system was set up
        $deptDates = [
            $baseDate->copy(),
            $baseDate->copy()->addMinutes(2),
            $baseDate->copy()->addMinutes(4),
        ];
        $departments = collect([
            Department::create(['name' => 'IT-отдел', 'created_at' => $deptDates[0], 'updated_at' => $deptDates[0]]),
            Department::create(['name' => 'Бухгалтерия', 'created_at' => $deptDates[1], 'updated_at' => $deptDates[1]]),
            Department::create(['name' => 'Отдел продаж', 'created_at' => $deptDates[2], 'updated_at' => $deptDates[2]]),
        ]);

        // Categories
        $catData = [
            ['name' => 'Установка ПО', 'description' => 'Установка или обновление программного обеспечения'],
            ['name' => 'Доступ к VPN', 'description' => 'Настройка удалённого доступа через VPN'],
            ['name' => 'Ремонт', 'description' => 'Ремонт компьютерного оборудования'],
            ['name' => 'Оборудование', 'description' => 'Запрос нового оборудования'],
            ['name' => 'Другое', 'description' => 'Прочие IT-запросы'],
        ];
        $categories = collect();
        foreach ($catData as $i => $cd) {
            $t = $baseDate->copy()->addMinutes(6 + $i);
            $categories->push(Category::create([...$cd, 'created_at' => $t, 'updated_at' => $t]));
        }

        // Users — registered over a few weeks
        $userEntries = [
            ['name' => 'Иванов Алексей', 'email' => 'admin@ithub.test', 'role' => Role::Admin, 'dept' => 0, 'day' => 0, 'hour' => 9, 'min' => 15],
            ['name' => 'Петрова Ольга', 'email' => 'manager@ithub.test', 'role' => Role::Manager, 'dept' => 1, 'day' => 0, 'hour' => 10, 'min' => 42],
            ['name' => 'Сидоров Дмитрий', 'email' => 'user1@ithub.test', 'role' => Role::Employee, 'dept' => 0, 'day' => 1, 'hour' => 8, 'min' => 55],
            ['name' => 'Козлова Анна', 'email' => 'user2@ithub.test', 'role' => Role::Employee, 'dept' => 1, 'day' => 1, 'hour' => 14, 'min' => 23],
            ['name' => 'Морозов Игорь', 'email' => 'user3@ithub.test', 'role' => Role::Employee, 'dept' => 2, 'day' => 2, 'hour' => 11, 'min' => 7],
            ['name' => 'Новикова Елена', 'email' => 'user4@ithub.test', 'role' => Role::Employee, 'dept' => 0, 'day' => 3, 'hour' => 9, 'min' => 31],
            ['name' => 'Волков Артём', 'email' => 'user5@ithub.test', 'role' => Role::Employee, 'dept' => 2, 'day' => 4, 'hour' => 16, 'min' => 48],
            ['name' => 'Соколова Мария', 'email' => 'user6@ithub.test', 'role' => Role::Employee, 'dept' => 1, 'day' => 5, 'hour' => 10, 'min' => 12],
            ['name' => 'Лебедев Павел', 'email' => 'user7@ithub.test', 'role' => Role::Employee, 'dept' => 2, 'day' => 7, 'hour' => 13, 'min' => 5],
            ['name' => 'Фёдорова Татьяна', 'email' => 'user8@ithub.test', 'role' => Role::Employee, 'dept' => 0, 'day' => 10, 'hour' => 15, 'min' => 39],
        ];

        $allUsers = collect();
        foreach ($userEntries as $ue) {
            $regDate = $baseDate->copy()->addDays($ue['day'])->setHour($ue['hour'])->setMinute($ue['min'])->setSecond(rand(10, 55));
            $allUsers->push(User::create([
                'name' => $ue['name'],
                'email' => $ue['email'],
                'password' => Hash::make('password'),
                'role' => $ue['role'],
                'department_id' => $departments[$ue['dept']]->id,
                'email_verified_at' => $regDate->copy()->addMinutes(rand(1, 30)),
                'created_at' => $regDate,
                'updated_at' => $regDate,
            ]));
        }

        $admin = $allUsers[0];

        // Tickets — created over ~6 weeks with realistic times
        $ticketData = [
            ['title' => 'Установить Microsoft Office', 'description' => 'Нужно установить пакет Microsoft Office 365 на рабочий ноутбук. Лицензия уже куплена, ключ у руководителя отдела.', 'cat' => 0, 'priority' => Priority::Medium, 'status' => TicketStatus::Done, 'user' => 3, 'day' => 5, 'hour' => 9, 'min' => 14, 'resolve_days' => 2],
            ['title' => 'Не работает принтер HP на 3 этаже', 'description' => 'Принтер HP LaserJet в кабинете 305 перестал печатать. Горит красный индикатор, бумага есть. Попробовали перезагрузить — не помогло.', 'cat' => 2, 'priority' => Priority::High, 'status' => TicketStatus::InProgress, 'user' => 4, 'day' => 7, 'hour' => 10, 'min' => 43, 'resolve_days' => null],
            ['title' => 'Настроить VPN для удалённой работы', 'description' => 'Перехожу на удалённый формат работы с понедельника. Нужен доступ к VPN для подключения к корпоративной сети из дома.', 'cat' => 1, 'priority' => Priority::High, 'status' => TicketStatus::Approved, 'user' => 5, 'day' => 8, 'hour' => 14, 'min' => 22, 'resolve_days' => null],
            ['title' => 'Запрос на новый монитор', 'description' => 'Текущий монитор 21 дюйм, для работы с таблицами и отчётами нужен монитор 27 дюймов. Согласовано с руководителем.', 'cat' => 3, 'priority' => Priority::Low, 'status' => TicketStatus::Pending, 'user' => 7, 'day' => 10, 'hour' => 11, 'min' => 5, 'resolve_days' => null],
            ['title' => 'Обновить антивирус Kaspersky', 'description' => 'Антивирус показывает что лицензия истекла. Компьютер начал тормозить, возможно есть угрозы. Просьба обновить лицензию и запустить полную проверку.', 'cat' => 0, 'priority' => Priority::High, 'status' => TicketStatus::InProgress, 'user' => 2, 'day' => 11, 'hour' => 8, 'min' => 37, 'resolve_days' => null],
            ['title' => 'Создать учётную запись для нового сотрудника', 'description' => 'Выходит новый сотрудник в отдел продаж — Кузнецов Максим. Нужно создать корпоративную почту, учётную запись в AD и доступ к CRM.', 'cat' => 4, 'priority' => Priority::Medium, 'status' => TicketStatus::Done, 'user' => 1, 'day' => 12, 'hour' => 15, 'min' => 51, 'resolve_days' => 1],
            ['title' => 'Проблема с Wi-Fi в переговорной', 'description' => 'В переговорной комнате на 2 этаже очень слабый сигнал Wi-Fi. Во время видеозвонков связь постоянно обрывается.', 'cat' => 2, 'priority' => Priority::Medium, 'status' => TicketStatus::Pending, 'user' => 6, 'day' => 14, 'hour' => 13, 'min' => 28, 'resolve_days' => null],
            ['title' => 'Запрос на ноутбук для командировки', 'description' => 'Через две недели командировка в Санкт-Петербург. Нужен ноутбук для работы на выезде, свой стационарный компьютер взять не могу.', 'cat' => 3, 'priority' => Priority::Medium, 'status' => TicketStatus::Approved, 'user' => 8, 'day' => 15, 'hour' => 9, 'min' => 46, 'resolve_days' => null],
            ['title' => 'Настроить корпоративную почту на телефоне', 'description' => 'Купил новый телефон, нужно настроить доступ к рабочей почте через Exchange. Старый телефон уже сдал.', 'cat' => 4, 'priority' => Priority::Low, 'status' => TicketStatus::Done, 'user' => 4, 'day' => 16, 'hour' => 16, 'min' => 3, 'resolve_days' => 3],
            ['title' => 'Ошибка при запуске 1С:Бухгалтерия', 'description' => 'При запуске 1С выдаёт ошибку "Нарушение прав доступа". Перезагрузка компьютера не помогает. Работать невозможно, отчёт нужно сдать до пятницы.', 'cat' => 0, 'priority' => Priority::High, 'status' => TicketStatus::InProgress, 'user' => 3, 'day' => 18, 'hour' => 8, 'min' => 11, 'resolve_days' => null],
            ['title' => 'Установить Figma и Slack', 'description' => 'Для нового проекта нужны Figma (десктопное приложение) и Slack. Прав администратора для установки нет.', 'cat' => 0, 'priority' => Priority::Medium, 'status' => TicketStatus::Pending, 'user' => 5, 'day' => 19, 'hour' => 11, 'min' => 34, 'resolve_days' => null],
            ['title' => 'Замена клавиатуры', 'description' => 'На клавиатуре не работают клавиши F5, F6 и пробел. Залил чай вчера. Нужна замена.', 'cat' => 2, 'priority' => Priority::Low, 'status' => TicketStatus::Draft, 'user' => 9, 'day' => 20, 'hour' => 14, 'min' => 52, 'resolve_days' => null],
            ['title' => 'Доступ к сетевому диску отдела маркетинга', 'description' => 'Для подготовки совместной презентации нужен доступ к общей папке отдела маркетинга на сервере. Согласовано с руководителем маркетинга.', 'cat' => 4, 'priority' => Priority::Medium, 'status' => TicketStatus::Approved, 'user' => 7, 'day' => 22, 'hour' => 10, 'min' => 18, 'resolve_days' => null],
            ['title' => 'Не приходят письма от клиентов', 'description' => 'С утра не получаю входящие письма. Коллеги говорят что отправляли — но у меня ничего нет. Папка "Спам" тоже пустая.', 'cat' => 2, 'priority' => Priority::High, 'status' => TicketStatus::InProgress, 'user' => 6, 'day' => 23, 'hour' => 9, 'min' => 7, 'resolve_days' => null],
            ['title' => 'Установить драйвер для сканера', 'description' => 'Подключили новый сканер Canon в бухгалтерии, но компьютер его не видит. Нужно установить драйвер. Диск с драйверами потеряли.', 'cat' => 0, 'priority' => Priority::Medium, 'status' => TicketStatus::Pending, 'user' => 3, 'day' => 25, 'hour' => 15, 'min' => 41, 'resolve_days' => null],
            ['title' => 'Перенести данные на новый компьютер', 'description' => 'Получил новый компьютер, нужно перенести все файлы, настройки почты и закладки из браузера со старого.', 'cat' => 4, 'priority' => Priority::Medium, 'status' => TicketStatus::Draft, 'user' => 2, 'day' => 26, 'hour' => 13, 'min' => 29, 'resolve_days' => null],
            ['title' => 'Запросить лицензию на Adobe Photoshop', 'description' => 'Для обработки фотографий товаров в каталог нужна лицензия Adobe Photoshop. Пробная версия закончилась.', 'cat' => 0, 'priority' => Priority::Low, 'status' => TicketStatus::Rejected, 'user' => 8, 'day' => 28, 'hour' => 10, 'min' => 55, 'resolve_days' => null],
            ['title' => 'Компьютер выключается сам по себе', 'description' => 'Рабочий компьютер самопроизвольно выключается 2-3 раза в день. Обычно это происходит под нагрузкой. Возможно перегрев.', 'cat' => 2, 'priority' => Priority::High, 'status' => TicketStatus::InProgress, 'user' => 4, 'day' => 30, 'hour' => 8, 'min' => 48, 'resolve_days' => null],
            ['title' => 'Расширить доступ в CRM', 'description' => 'Перевели на позицию старшего менеджера. Нужен расширенный доступ в CRM: просмотр всех сделок отдела и выгрузка отчётов.', 'cat' => 4, 'priority' => Priority::Medium, 'status' => TicketStatus::Pending, 'user' => 6, 'day' => 32, 'hour' => 14, 'min' => 16, 'resolve_days' => null],
            ['title' => 'Установить Teams для видеоконференций', 'description' => 'Руководство переходит на Microsoft Teams для совещаний. Просьба установить и настроить на моём компьютере.', 'cat' => 0, 'priority' => Priority::Medium, 'status' => TicketStatus::Done, 'user' => 5, 'day' => 33, 'hour' => 11, 'min' => 23, 'resolve_days' => 2],
            ['title' => 'Заменить сетевой кабель', 'description' => 'Интернет в кабинете 210 постоянно отваливается. Подёргивание кабеля помогает на время. Скорее всего повреждён сетевой кабель от розетки до компьютера.', 'cat' => 2, 'priority' => Priority::Low, 'status' => TicketStatus::Draft, 'user' => 9, 'day' => 35, 'hour' => 16, 'min' => 37, 'resolve_days' => null],
            ['title' => 'Запрос на вторую мышь', 'description' => 'Работаю с ноутбуком и монитором. Нужна беспроводная мышь для удобной работы с внешним монитором.', 'cat' => 3, 'priority' => Priority::Low, 'status' => TicketStatus::Pending, 'user' => 7, 'day' => 37, 'hour' => 9, 'min' => 52, 'resolve_days' => null],
            ['title' => 'Обновить Windows до последней версии', 'description' => 'На компьютере стоит Windows 10. Для совместимости с новым ПО нужно обновить до Windows 11. ПК поддерживает — проверял через PC Health Check.', 'cat' => 0, 'priority' => Priority::Low, 'status' => TicketStatus::Approved, 'user' => 2, 'day' => 39, 'hour' => 13, 'min' => 8, 'resolve_days' => null],
            ['title' => 'VPN отключается каждые 30 минут', 'description' => 'После настройки VPN соединение стабильно разрывается примерно каждые 30 минут. Приходится переподключаться вручную. Очень мешает работе.', 'cat' => 1, 'priority' => Priority::High, 'status' => TicketStatus::InProgress, 'user' => 5, 'day' => 40, 'hour' => 10, 'min' => 31, 'resolve_days' => null],
            ['title' => 'Заблокировать учётную запись уволенного сотрудника', 'description' => 'Сотрудник Белов А.С. уволился 15 числа. Нужно заблокировать его учётную запись в AD, почту и доступ ко всем системам.', 'cat' => 4, 'priority' => Priority::High, 'status' => TicketStatus::Done, 'user' => 1, 'day' => 42, 'hour' => 9, 'min' => 3, 'resolve_days' => 0],
            ['title' => 'Установить 7-Zip и Notepad++', 'description' => 'Для работы с архивами и конфигурационными файлами нужны 7-Zip и Notepad++. Стандартные средства Windows неудобны.', 'cat' => 0, 'priority' => Priority::Low, 'status' => TicketStatus::Draft, 'user' => 8, 'day' => 44, 'hour' => 15, 'min' => 19, 'resolve_days' => null],
            ['title' => 'Не печатает цветной принтер', 'description' => 'Цветной принтер в отделе продаж печатает только чёрно-белым. Возможно закончились цветные картриджи.', 'cat' => 2, 'priority' => Priority::Medium, 'status' => TicketStatus::Pending, 'user' => 6, 'day' => 46, 'hour' => 11, 'min' => 44, 'resolve_days' => null],
            ['title' => 'Нужен удлинитель с сетевым фильтром', 'description' => 'В кабинете 401 не хватает розеток для всей техники. Нужен сетевой фильтр-удлинитель на 5 розеток.', 'cat' => 3, 'priority' => Priority::Low, 'status' => TicketStatus::Rejected, 'user' => 9, 'day' => 48, 'hour' => 14, 'min' => 27, 'resolve_days' => null],
            ['title' => 'Настроить автоматический бэкап', 'description' => 'Прошу настроить автоматическое резервное копирование папки с рабочими документами на сетевой диск. Один раз в день будет достаточно.', 'cat' => 4, 'priority' => Priority::Medium, 'status' => TicketStatus::Approved, 'user' => 3, 'day' => 50, 'hour' => 10, 'min' => 36, 'resolve_days' => null],
            ['title' => 'Подключить второй монитор', 'description' => 'Получил второй монитор, но не могу его подключить — нет нужного переходника с HDMI на DisplayPort. Прошу помочь с подключением.', 'cat' => 3, 'priority' => Priority::Low, 'status' => TicketStatus::Draft, 'user' => 4, 'day' => 52, 'hour' => 16, 'min' => 2, 'resolve_days' => null],
        ];

        $commentTexts = [
            'Принято в работу, посмотрю сегодня.',
            'Нужна дополнительная информация — какой номер кабинета?',
            'Готово, проверьте пожалуйста.',
            'Заказал комплектующие, ожидаем доставку 2-3 дня.',
            'Проблема решена, перезагрузите компьютер.',
            'Согласовал с руководством, приступаю.',
            'Перенёс задачу на завтра, сегодня занят срочным инцидентом.',
            'Установил, пароль для входа отправил на почту.',
            'Лицензия активирована до конца года.',
            'Подключил, проверьте связь.',
            'Уточните, какая версия ОС у вас стоит?',
            'Жду подтверждение от вашего руководителя.',
            'Запчасть в наличии, заменю завтра утром.',
            'Временно дал доступ через гостевой аккаунт.',
            'Проверил — проблема на стороне провайдера, оставил заявку им.',
        ];

        foreach ($ticketData as $i => $td) {
            $author = $allUsers[$td['user']];
            $createdAt = $baseDate->copy()->addDays($td['day'])->setHour($td['hour'])->setMinute($td['min'])->setSecond(rand(5, 55));

            // updated_at — some time after creation (status changes, edits)
            $updatedAt = $td['status'] === TicketStatus::Draft
                ? $createdAt->copy()
                : $createdAt->copy()->addHours(rand(1, 48))->addMinutes(rand(1, 59))->addSeconds(rand(1, 59));

            $resolvedAt = null;
            if ($td['resolve_days'] !== null) {
                $resolvedAt = $createdAt->copy()->addDays($td['resolve_days'])->addHours(rand(2, 9))->addMinutes(rand(5, 55))->addSeconds(rand(1, 59));
                $updatedAt = $resolvedAt->copy();
            }

            $ticket = Ticket::create([
                'title' => $td['title'],
                'description' => $td['description'],
                'category_id' => $categories[$td['cat']]->id,
                'author_id' => $author->id,
                'assignee_id' => in_array($td['status'], [TicketStatus::InProgress, TicketStatus::Done, TicketStatus::Approved])
                    ? $admin->id
                    : null,
                'department_id' => $author->department_id,
                'status' => $td['status'],
                'priority' => $td['priority'],
                'resolved_at' => $resolvedAt,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);

            // Add 1-2 comments to ~60% of tickets
            if ($i % 3 !== 2) {
                $commentTime = $createdAt->copy()->addHours(rand(1, 12))->addMinutes(rand(3, 57))->addSeconds(rand(1, 59));
                Comment::create([
                    'commentable_type' => Ticket::class,
                    'commentable_id' => $ticket->id,
                    'user_id' => $admin->id,
                    'body' => $commentTexts[$i % count($commentTexts)],
                    'created_at' => $commentTime,
                    'updated_at' => $commentTime,
                ]);

                // Second comment on some tickets
                if ($i % 4 === 0) {
                    $replyTime = $commentTime->copy()->addHours(rand(1, 6))->addMinutes(rand(2, 48));
                    Comment::create([
                        'commentable_type' => Ticket::class,
                        'commentable_id' => $ticket->id,
                        'user_id' => $author->id,
                        'body' => 'Спасибо, жду.',
                        'created_at' => $replyTime,
                        'updated_at' => $replyTime,
                    ]);
                }
            }
        }

        // Equipment — added over time
        $equipmentData = [
            ['name' => 'MacBook Pro 14"', 'type' => 'Ноутбук', 'serial_number' => 'MBP-2024-001', 'status' => EquipmentStatus::Assigned, 'assigned_to' => $allUsers[2]->id, 'day' => 0, 'hour' => 11, 'min' => 30],
            ['name' => 'Dell UltraSharp 27"', 'type' => 'Монитор', 'serial_number' => 'DLL-MON-042', 'status' => EquipmentStatus::Available, 'assigned_to' => null, 'day' => 0, 'hour' => 11, 'min' => 45],
            ['name' => 'HP LaserJet Pro M404', 'type' => 'Принтер', 'serial_number' => 'HP-PRN-2024-003', 'status' => EquipmentStatus::Available, 'assigned_to' => null, 'day' => 3, 'hour' => 14, 'min' => 12],
            ['name' => 'Lenovo ThinkPad X1 Carbon', 'type' => 'Ноутбук', 'serial_number' => 'LNV-TP-2023-015', 'status' => EquipmentStatus::Repair, 'assigned_to' => null, 'day' => 15, 'hour' => 9, 'min' => 55],
            ['name' => 'Logitech MX Master 3S', 'type' => 'Мышь', 'serial_number' => 'LGT-MX-2024-088', 'status' => EquipmentStatus::Assigned, 'assigned_to' => $allUsers[3]->id, 'day' => 22, 'hour' => 10, 'min' => 8],
        ];

        foreach ($equipmentData as $eq) {
            $eqCreated = $baseDate->copy()->addDays($eq['day'])->setHour($eq['hour'])->setMinute($eq['min'])->setSecond(rand(10, 50));
            $eqUpdated = $eq['status'] === EquipmentStatus::Available
                ? $eqCreated->copy()
                : $eqCreated->copy()->addDays(rand(1, 5))->addHours(rand(1, 8))->addMinutes(rand(1, 59));

            Equipment::create([
                'name' => $eq['name'],
                'type' => $eq['type'],
                'serial_number' => $eq['serial_number'],
                'status' => $eq['status'],
                'assigned_to' => $eq['assigned_to'],
                'created_at' => $eqCreated,
                'updated_at' => $eqUpdated,
            ]);
        }
    }
}
