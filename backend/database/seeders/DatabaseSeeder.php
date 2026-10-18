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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $departments = collect([
            Department::create(['name' => 'IT-отдел']),
            Department::create(['name' => 'Бухгалтерия']),
            Department::create(['name' => 'Отдел продаж']),
        ]);

        $categories = collect([
            Category::create(['name' => 'Установка ПО', 'description' => 'Установка или обновление программного обеспечения']),
            Category::create(['name' => 'Доступ к VPN', 'description' => 'Настройка удалённого доступа через VPN']),
            Category::create(['name' => 'Ремонт', 'description' => 'Ремонт компьютерного оборудования']),
            Category::create(['name' => 'Оборудование', 'description' => 'Запрос нового оборудования']),
            Category::create(['name' => 'Другое', 'description' => 'Прочие IT-запросы']),
        ]);

        $admin = User::create([
            'name' => 'Иванов Алексей',
            'email' => 'admin@ithub.test',
            'password' => Hash::make('password'),
            'role' => Role::Admin,
            'department_id' => $departments[0]->id,
            'email_verified_at' => now(),
        ]);

        $manager = User::create([
            'name' => 'Петрова Ольга',
            'email' => 'manager@ithub.test',
            'password' => Hash::make('password'),
            'role' => Role::Manager,
            'department_id' => $departments[1]->id,
            'email_verified_at' => now(),
        ]);

        $employeeNames = [
            'Сидоров Дмитрий', 'Козлова Анна', 'Морозов Игорь',
            'Новикова Елена', 'Волков Артём', 'Соколова Мария',
            'Лебедев Павел', 'Фёдорова Татьяна',
        ];

        $employees = collect();
        foreach ($employeeNames as $i => $name) {
            $employees->push(User::create([
                'name' => $name,
                'email' => 'user' . ($i + 1) . '@ithub.test',
                'password' => Hash::make('password'),
                'role' => Role::Employee,
                'department_id' => $departments[$i % 3]->id,
                'email_verified_at' => now(),
            ]));
        }

        $allUsers = $employees->merge([$admin, $manager]);

        $ticketData = [
            ['title' => 'Установить Microsoft Office', 'description' => 'Нужно установить пакет Microsoft Office 365 на рабочий ноутбук. Лицензия уже куплена, ключ у руководителя отдела.', 'cat' => 0, 'priority' => Priority::Medium, 'status' => TicketStatus::Done],
            ['title' => 'Не работает принтер HP на 3 этаже', 'description' => 'Принтер HP LaserJet в кабинете 305 перестал печатать. Горит красный индикатор, бумага есть. Попробовали перезагрузить — не помогло.', 'cat' => 2, 'priority' => Priority::High, 'status' => TicketStatus::InProgress],
            ['title' => 'Настроить VPN для удалённой работы', 'description' => 'Перехожу на удалённый формат работы с понедельника. Нужен доступ к VPN для подключения к корпоративной сети из дома.', 'cat' => 1, 'priority' => Priority::High, 'status' => TicketStatus::Approved],
            ['title' => 'Запрос на новый монитор', 'description' => 'Текущий монитор 21 дюйм, для работы с таблицами и отчётами нужен монитор 27 дюймов. Согласовано с руководителем.', 'cat' => 3, 'priority' => Priority::Low, 'status' => TicketStatus::Pending],
            ['title' => 'Обновить антивирус Kaspersky', 'description' => 'Антивирус показывает что лицензия истекла. Компьютер начал тормозить, возможно есть угрозы. Просьба обновить лицензию и запустить полную проверку.', 'cat' => 0, 'priority' => Priority::High, 'status' => TicketStatus::InProgress],
            ['title' => 'Создать учётную запись для нового сотрудника', 'description' => 'Выходит новый сотрудник в отдел продаж — Кузнецов Максим. Нужно создать корпоративную почту, учётную запись в AD и доступ к CRM.', 'cat' => 4, 'priority' => Priority::Medium, 'status' => TicketStatus::Done],
            ['title' => 'Проблема с Wi-Fi в переговорной', 'description' => 'В переговорной комнате на 2 этаже очень слабый сигнал Wi-Fi. Во время видеозвонков связь постоянно обрывается.', 'cat' => 2, 'priority' => Priority::Medium, 'status' => TicketStatus::Pending],
            ['title' => 'Запрос на ноутбук для командировки', 'description' => 'Через две недели командировка в Санкт-Петербург. Нужен ноутбук для работы на выезде, свой стационарный компьютер взять не могу.', 'cat' => 3, 'priority' => Priority::Medium, 'status' => TicketStatus::Approved],
            ['title' => 'Настроить корпоративную почту на телефоне', 'description' => 'Купил новый телефон, нужно настроить доступ к рабочей почте через Exchange. Старый телефон уже сдал.', 'cat' => 4, 'priority' => Priority::Low, 'status' => TicketStatus::Done],
            ['title' => 'Ошибка при запуске 1С:Бухгалтерия', 'description' => 'При запуске 1С выдаёт ошибку "Нарушение прав доступа". Перезагрузка компьютера не помогает. Работать невозможно, отчёт нужно сдать до пятницы.', 'cat' => 0, 'priority' => Priority::High, 'status' => TicketStatus::InProgress],
            ['title' => 'Установить Figma и Slack', 'description' => 'Для нового проекта нужны Figma (десктопное приложение) и Slack. Прав администратора для установки нет.', 'cat' => 0, 'priority' => Priority::Medium, 'status' => TicketStatus::Pending],
            ['title' => 'Замена клавиатуры', 'description' => 'На клавиатуре не работают клавиши F5, F6 и пробел. Залил чай вчера. Нужна замена.', 'cat' => 2, 'priority' => Priority::Low, 'status' => TicketStatus::Draft],
            ['title' => 'Доступ к сетевому диску отдела маркетинга', 'description' => 'Для подготовки совместной презентации нужен доступ к общей папке отдела маркетинга на сервере. Согласовано с руководителем маркетинга.', 'cat' => 4, 'priority' => Priority::Medium, 'status' => TicketStatus::Approved],
            ['title' => 'Не приходят письма от клиентов', 'description' => 'С утра не получаю входящие письма. Коллеги говорят что отправляли — но у меня ничего нет. Папка "Спам" тоже пустая.', 'cat' => 2, 'priority' => Priority::High, 'status' => TicketStatus::InProgress],
            ['title' => 'Установить драйвер для сканера', 'description' => 'Подключили новый сканер Canon в бухгалтерии, но компьютер его не видит. Нужно установить драйвер. Диск с драйверами потеряли.', 'cat' => 0, 'priority' => Priority::Medium, 'status' => TicketStatus::Pending],
            ['title' => 'Перенести данные на новый компьютер', 'description' => 'Получил новый компьютер, нужно перенести все файлы, настройки почты и закладки из браузера со старого.', 'cat' => 4, 'priority' => Priority::Medium, 'status' => TicketStatus::Draft],
            ['title' => 'Запросить лицензию на Adobe Photoshop', 'description' => 'Для обработки фотографий товаров в каталог нужна лицензия Adobe Photoshop. Пробная версия закончилась.', 'cat' => 0, 'priority' => Priority::Low, 'status' => TicketStatus::Rejected],
            ['title' => 'Компьютер выключается сам по себе', 'description' => 'Рабочий компьютер самопроизвольно выключается 2-3 раза в день. Обычно это происходит под нагрузкой. Возможно перегрев.', 'cat' => 2, 'priority' => Priority::High, 'status' => TicketStatus::InProgress],
            ['title' => 'Расширить доступ в CRM', 'description' => 'Перевели на позицию старшего менеджера. Нужен расширенный доступ в CRM: просмотр всех сделок отдела и выгрузка отчётов.', 'cat' => 4, 'priority' => Priority::Medium, 'status' => TicketStatus::Pending],
            ['title' => 'Установить Teams для видеоконференций', 'description' => 'Руководство переходит на Microsoft Teams для совещаний. Просьба установить и настроить на моём компьютере.', 'cat' => 0, 'priority' => Priority::Medium, 'status' => TicketStatus::Done],
            ['title' => 'Заменить сетевой кабель', 'description' => 'Интернет в кабинете 210 постоянно отваливается. Подёргивание кабеля помогает на время. Скорее всего повреждён сетевой кабель от розетки до компьютера.', 'cat' => 2, 'priority' => Priority::Low, 'status' => TicketStatus::Draft],
            ['title' => 'Запрос на вторую мышь', 'description' => 'Работаю с ноутбуком и монитором. Нужна беспроводная мышь для удобной работы с внешним монитором.', 'cat' => 3, 'priority' => Priority::Low, 'status' => TicketStatus::Pending],
            ['title' => 'Обновить Windows до последней версии', 'description' => 'На компьютере стоит Windows 10. Для совместимости с новым ПО нужно обновить до Windows 11. ПК поддерживает — проверял через PC Health Check.', 'cat' => 0, 'priority' => Priority::Low, 'status' => TicketStatus::Approved],
            ['title' => 'VPN отключается каждые 30 минут', 'description' => 'После настройки VPN соединение стабильно разрывается примерно каждые 30 минут. Приходится переподключаться вручную. Очень мешает работе.', 'cat' => 1, 'priority' => Priority::High, 'status' => TicketStatus::InProgress],
            ['title' => 'Заблокировать учётную запись уволенного сотрудника', 'description' => 'Сотрудник Белов А.С. уволился 15 числа. Нужно заблокировать его учётную запись в AD, почту и доступ ко всем системам.', 'cat' => 4, 'priority' => Priority::High, 'status' => TicketStatus::Done],
            ['title' => 'Установить 7-Zip и Notepad++', 'description' => 'Для работы с архивами и конфигурационными файлами нужны 7-Zip и Notepad++. Стандартные средства Windows неудобны.', 'cat' => 0, 'priority' => Priority::Low, 'status' => TicketStatus::Draft],
            ['title' => 'Не печатает цветной принтер', 'description' => 'Цветной принтер в отделе продаж печатает только чёрно-белым. Возможно закончились цветные картриджи.', 'cat' => 2, 'priority' => Priority::Medium, 'status' => TicketStatus::Pending],
            ['title' => 'Нужен удлинитель с сетевым фильтром', 'description' => 'В кабинете 401 не хватает розеток для всей техники. Нужен сетевой фильтр-удлинитель на 5 розеток.', 'cat' => 3, 'priority' => Priority::Low, 'status' => TicketStatus::Rejected],
            ['title' => 'Настроить автоматический бэкап', 'description' => 'Прошу настроить автоматическое резервное копирование папки с рабочими документами на сетевой диск. Один раз в день будет достаточно.', 'cat' => 4, 'priority' => Priority::Medium, 'status' => TicketStatus::Approved],
            ['title' => 'Подключить второй монитор', 'description' => 'Получил второй монитор, но не могу его подключить — нет нужного переходника с HDMI на DisplayPort. Прошу помочь с подключением.', 'cat' => 3, 'priority' => Priority::Low, 'status' => TicketStatus::Draft],
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
        ];

        foreach ($ticketData as $i => $td) {
            $author = $allUsers->random();
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
                'resolved_at' => $td['status'] === TicketStatus::Done
                    ? now()->subDays(rand(1, 14))->subHours(rand(1, 8))
                    : null,
                'created_at' => now()->subDays(rand(1, 30))->subHours(rand(1, 12)),
            ]);

            if ($i % 2 === 0) {
                Comment::create([
                    'commentable_type' => Ticket::class,
                    'commentable_id' => $ticket->id,
                    'user_id' => $admin->id,
                    'body' => $commentTexts[$i % count($commentTexts)],
                ]);
            }
        }

        $equipmentData = [
            ['name' => 'MacBook Pro 14"', 'type' => 'Ноутбук', 'serial_number' => 'MBP-2024-001', 'status' => EquipmentStatus::Assigned, 'assigned_to' => $employees[0]->id],
            ['name' => 'Dell UltraSharp 27"', 'type' => 'Монитор', 'serial_number' => 'DLL-MON-042', 'status' => EquipmentStatus::Available],
            ['name' => 'HP LaserJet Pro M404', 'type' => 'Принтер', 'serial_number' => 'HP-PRN-2024-003', 'status' => EquipmentStatus::Available],
            ['name' => 'Lenovo ThinkPad X1 Carbon', 'type' => 'Ноутбук', 'serial_number' => 'LNV-TP-2023-015', 'status' => EquipmentStatus::Repair],
            ['name' => 'Logitech MX Master 3S', 'type' => 'Мышь', 'serial_number' => 'LGT-MX-2024-088', 'status' => EquipmentStatus::Assigned, 'assigned_to' => $employees[1]->id],
        ];

        foreach ($equipmentData as $eq) {
            Equipment::create($eq);
        }
    }
}
