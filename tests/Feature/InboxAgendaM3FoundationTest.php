<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\CalendarEvent;
use App\Models\InboxItem;
use App\Models\InboxRecipient;
use App\Notifications\DosenDatabaseNotification;
use App\Services\InboxWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InboxAgendaM3FoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dosen_only_sees_own_inbox_and_can_mark_read(): void
    {
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);
        $own = InboxItem::query()->create(['lecturer_core_id' => '10', 'type' => 'INFO', 'title' => 'Untuk Saya']);
        InboxItem::query()->create(['lecturer_core_id' => '20', 'type' => 'INFO', 'title' => 'Untuk Orang Lain']);

        $this->actingAs($user)->get(route('dosen.inbox.index'))->assertOk()->assertSee('Untuk Saya')->assertDontSee('Untuk Orang Lain');
        $this->actingAs($user)->post(route('dosen.inbox.read', $own))->assertRedirect();

        $this->assertSame('READ', $own->fresh()->status);
        $this->assertNotNull($own->fresh()->read_at);
    }

    public function test_dosen_only_sees_own_agenda_and_overlap_is_detected(): void
    {
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);
        $event = CalendarEvent::query()->create(['lecturer_core_id' => '10', 'title' => 'Agenda A', 'event_type' => 'RAPAT', 'starts_at' => now()->addDay(), 'ends_at' => now()->addDay()->addHour()]);
        CalendarEvent::query()->create(['lecturer_core_id' => '10', 'title' => 'Agenda B', 'event_type' => 'RAPAT', 'starts_at' => now()->addDay()->addMinutes(30), 'ends_at' => now()->addDay()->addHours(2)]);
        CalendarEvent::query()->create(['lecturer_core_id' => '20', 'title' => 'Agenda Orang Lain', 'event_type' => 'RAPAT', 'starts_at' => now()->addDay()]);

        $this->actingAs($user)->get(route('dosen.calendar.index'))->assertOk()->assertSee('Agenda A')->assertSee('Bentrok waktu')->assertDontSee('Agenda Orang Lain');
        $this->assertTrue($event->overlaps());
    }

    public function test_cancelled_item_stays_visible_as_history(): void
    {
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);
        CalendarEvent::query()->create(['lecturer_core_id' => '10', 'title' => 'Agenda Batal', 'event_type' => 'RAPAT', 'status' => 'CANCELLED', 'starts_at' => now()->addDay()]);

        $this->actingAs($user)->get(route('dosen.calendar.index'))->assertOk()->assertSee('Agenda Batal')->assertSee('Dibatalkan');
    }

    public function test_multi_recipient_inbox_keeps_independent_recipient_statuses(): void
    {
        $admin = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => null, 'name' => 'Admin', 'role' => 'admin', 'is_active' => true]);
        $first = AppUser::query()->create(['core_user_id' => '2', 'core_lecturer_id' => '10', 'name' => 'Dosen 1', 'role' => 'dosen', 'is_active' => true]);
        $second = AppUser::query()->create(['core_user_id' => '3', 'core_lecturer_id' => '20', 'name' => 'Dosen 2', 'role' => 'dosen', 'is_active' => true]);

        $item = app(InboxWorkflowService::class)->createManual([
            'lecturer_core_ids' => ['10', '20'],
            'type' => 'INVITATION',
            'title' => 'Undangan Penguji',
            'summary' => 'Mohon konfirmasi.',
        ], $admin);

        $siblings = InboxItem::query()->where('group_id', $item->group_id)->get();
        $this->assertCount(2, $siblings);

        $own = $siblings->firstWhere('lecturer_core_id', '10');
        $other = $siblings->firstWhere('lecturer_core_id', '20');

        $this->actingAs($first)->post(route('dosen.inbox.status', $own), ['status' => 'ACCEPTED'])->assertRedirect();

        $this->assertSame('ACCEPTED', $own->fresh()->status);
        $this->assertSame('UNREAD', $other->fresh()->status);
        $this->assertSame('ACCEPTED', InboxRecipient::query()->where('inbox_item_id', $own->id)->firstOrFail()->status);
        $this->assertSame('UNREAD', InboxRecipient::query()->where('inbox_item_id', $other->id)->firstOrFail()->status);
        $this->assertSame(1, $first->notifications()->count());
        $this->assertSame(1, $second->notifications()->count());
    }

    public function test_non_invitation_cannot_be_accepted_and_notifications_can_be_marked_read_in_bulk(): void
    {
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);
        $item = InboxItem::query()->create(['lecturer_core_id' => '10', 'type' => 'INFO', 'title' => 'Informasi']);

        $this->actingAs($user)->post(route('dosen.inbox.status', $item), ['status' => 'ACCEPTED'])->assertSessionHasErrors('status');

        $user->notify(new DosenDatabaseNotification('Satu', 'Pesan', ['category' => 'test']));
        $user->notify(new DosenDatabaseNotification('Dua', 'Pesan', ['category' => 'test']));

        $this->assertSame(2, $user->unreadNotifications()->count());

        $this->actingAs($user)->post(route('dosen.notifications.read-all'))->assertRedirect();

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }
}
