<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Priority;
use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;
    private Category $category;
    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::create(['name' => 'IT']);
        $this->category = Category::create(['name' => 'Software', 'description' => 'Software requests']);
        $this->user = User::factory()->create(['department_id' => $this->department->id]);
        $this->admin = User::factory()->admin()->create(['department_id' => $this->department->id]);
    }

    public function test_user_can_create_ticket(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/tickets', [
            'title' => 'Install VS Code',
            'description' => 'Please install VS Code on my workstation',
            'category_id' => $this->category->id,
            'priority' => Priority::Medium->value,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Install VS Code')
            ->assertJsonPath('data.status', 'draft');
    }

    public function test_ticket_list_is_paginated(): void
    {
        Ticket::factory(20)->create([
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
            'department_id' => $this->department->id,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/tickets');

        $response->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_tickets_can_be_filtered_by_status(): void
    {
        Ticket::factory()->create([
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
            'department_id' => $this->department->id,
            'status' => TicketStatus::Pending,
        ]);

        Ticket::factory()->create([
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
            'department_id' => $this->department->id,
            'status' => TicketStatus::Done,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/tickets?status=pending');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_admin_can_change_ticket_status(): void
    {
        $ticket = Ticket::factory()->create([
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
            'department_id' => $this->department->id,
            'status' => TicketStatus::Draft,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/v1/tickets/{$ticket->id}/status", [
                'status' => 'pending',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_invalid_status_transition_returns_error(): void
    {
        $ticket = Ticket::factory()->create([
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
            'department_id' => $this->department->id,
            'status' => TicketStatus::Draft,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/v1/tickets/{$ticket->id}/status", [
                'status' => 'done',
            ]);

        $response->assertUnprocessable();
    }

    public function test_regular_user_cannot_change_status(): void
    {
        $ticket = Ticket::factory()->create([
            'category_id' => $this->category->id,
            'author_id' => $this->admin->id,
            'department_id' => $this->department->id,
            'status' => TicketStatus::Pending,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/tickets/{$ticket->id}/status", [
                'status' => 'approved',
            ]);

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_tickets(): void
    {
        $this->getJson('/api/v1/tickets')->assertUnauthorized();
    }

    public function test_author_can_delete_own_ticket(): void
    {
        $ticket = Ticket::factory()->create([
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
            'department_id' => $this->department->id,
        ]);

        $this->actingAs($this->user)
            ->deleteJson("/api/v1/tickets/{$ticket->id}")
            ->assertNoContent();
    }

    public function test_other_user_cannot_delete_ticket(): void
    {
        $otherUser = User::factory()->create(['department_id' => $this->department->id]);

        $ticket = Ticket::factory()->create([
            'category_id' => $this->category->id,
            'author_id' => $this->user->id,
            'department_id' => $this->department->id,
        ]);

        $this->actingAs($otherUser)
            ->deleteJson("/api/v1/tickets/{$ticket->id}")
            ->assertForbidden();
    }
}
