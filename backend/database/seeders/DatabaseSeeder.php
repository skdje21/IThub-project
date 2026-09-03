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

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Departments
        $departments = collect(['IT-отдел', 'Бухгалтерия', 'Отдел продаж'])->map(
            fn (string $name) => Department::create(['name' => $name])
        );

        // Categories
        $categories = collect([
            ['name' => 'Установка ПО', 'description' => 'Установка или обновление программного обеспечения'],
            ['name' => 'Доступ к VPN', 'description' => 'Настройка удалённого доступа через VPN'],
            ['name' => 'Ремонт', 'description' => 'Ремонт компьютерного оборудования'],
            ['name' => 'Оборудование', 'description' => 'Запрос нового оборудования'],
            ['name' => 'Другое', 'description' => 'Прочие IT-запросы'],
        ])->map(fn (array $data) => Category::create($data));

        // Admin
        $admin = User::factory()->create([
            'name' => 'Админ Иванов',
            'email' => 'admin@ithub.test',
            'role' => Role::Admin,
            'department_id' => $departments[0]->id,
        ]);

        // Manager
        $manager = User::factory()->create([
            'name' => 'Менеджер Петров',
            'email' => 'manager@ithub.test',
            'role' => Role::Manager,
            'department_id' => $departments[1]->id,
        ]);

        // Employees
        $employees = User::factory(8)->create([
            'department_id' => fn () => $departments->random()->id,
        ]);

        $allUsers = $employees->merge([$admin, $manager]);

        // Tickets
        $statuses = TicketStatus::cases();
        $priorities = Priority::cases();

        for ($i = 0; $i < 30; $i++) {
            $status = $statuses[array_rand($statuses)];
            $author = $allUsers->random();

            $ticket = Ticket::create([
                'title' => fake()->randomElement([
                    'Установить Microsoft Office', 'Не работает принтер', 'Нужен доступ к VPN',
                    'Запрос на новый монитор', 'Обновить антивирус', 'Создать учётную запись',
                    'Проблема с Wi-Fi', 'Запрос на ноутбук', 'Настроить почту',
                    'Сбой в 1С', 'Нужна лицензия на Figma', 'Замена клавиатуры',
                ]),
                'description' => fake()->paragraph(2),
                'category_id' => $categories->random()->id,
                'author_id' => $author->id,
                'assignee_id' => in_array($status, [TicketStatus::InProgress, TicketStatus::Done, TicketStatus::Approved])
                    ? $admin->id
                    : null,
                'department_id' => $author->department_id,
                'status' => $status,
                'priority' => $priorities[array_rand($priorities)],
                'resolved_at' => $status === TicketStatus::Done
                    ? fake()->dateTimeBetween('-2 weeks', 'now')
                    : null,
            ]);

            // Add comments to some tickets
            if (fake()->boolean(60)) {
                Comment::create([
                    'commentable_type' => Ticket::class,
                    'commentable_id' => $ticket->id,
                    'user_id' => $allUsers->random()->id,
                    'body' => fake()->sentence(8),
                ]);
            }
        }

        // Equipment
        $equipmentData = [
            ['name' => 'MacBook Pro 14"', 'type' => 'laptop', 'serial_number' => 'MBP-2024-001', 'status' => EquipmentStatus::Assigned, 'assigned_to' => $employees[0]->id],
            ['name' => 'Dell UltraSharp 27"', 'type' => 'monitor', 'serial_number' => 'DLL-MON-042', 'status' => EquipmentStatus::Available],
            ['name' => 'HP LaserJet Pro', 'type' => 'printer', 'serial_number' => 'HP-PRN-2024-003', 'status' => EquipmentStatus::Available],
            ['name' => 'Lenovo ThinkPad X1', 'type' => 'laptop', 'serial_number' => 'LNV-TP-2023-015', 'status' => EquipmentStatus::Repair],
            ['name' => 'Logitech MX Master 3S', 'type' => 'mouse', 'serial_number' => 'LGT-MX-2024-088', 'status' => EquipmentStatus::Assigned, 'assigned_to' => $employees[1]->id],
        ];

        foreach ($equipmentData as $eq) {
            Equipment::create($eq);
        }
    }
}
