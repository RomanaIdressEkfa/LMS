<?php

namespace Tests\Feature;

use App\Models\LiveSession;
use Tests\TestCase;

class LiveSchedulingTest extends TestCase
{
    private function sessionPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Intro Live',
            'provider' => 'zoom',
            'meeting_url' => 'https://zoom.us/j/123',
            'scheduled_at' => '2026-08-01T14:30',
            'duration_minutes' => 45,
        ], $overrides);
    }

    public function test_host_can_schedule_a_session(): void
    {
        $teacher = $this->userWithRole('teacher'); // teacher role has live.host

        $this->actingAs($teacher)->post('/dashboard/live', $this->sessionPayload())
            ->assertRedirect('/dashboard/live');

        $session = LiveSession::where('title', 'Intro Live')->first();
        $this->assertNotNull($session);
        $this->assertSame($teacher->id, $session->teacher_id);
        $this->assertSame('scheduled', $session->status);
        $this->assertSame(45, $session->duration_minutes);
    }

    public function test_student_cannot_schedule_a_session(): void
    {
        $student = $this->userWithRole('student'); // has live.view, not live.host

        $this->actingAs($student)->post('/dashboard/live', $this->sessionPayload())
            ->assertForbidden();
        $this->assertDatabaseCount('live_sessions', 0);
    }

    public function test_status_advances_through_the_lifecycle(): void
    {
        $teacher = $this->userWithRole('teacher');
        $session = LiveSession::create($this->sessionPayload([
            'teacher_id' => $teacher->id,
            'scheduled_at' => now(),
        ]));

        $this->actingAs($teacher)->post("/dashboard/live/{$session->id}/status", ['status' => 'live']);
        $this->assertSame('live', $session->fresh()->status);

        $this->actingAs($teacher)->post("/dashboard/live/{$session->id}/status", ['status' => 'ended']);
        $this->assertSame('ended', $session->fresh()->status);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $teacher = $this->userWithRole('teacher');
        $session = LiveSession::create($this->sessionPayload([
            'teacher_id' => $teacher->id, 'scheduled_at' => now(),
        ]));

        $this->actingAs($teacher)->from('/dashboard/live')
            ->post("/dashboard/live/{$session->id}/status", ['status' => 'bogus'])
            ->assertSessionHasErrors('status');
        $this->assertSame('scheduled', $session->fresh()->status);
    }

    public function test_a_host_cannot_manage_another_hosts_session(): void
    {
        $owner = $this->userWithRole('teacher');
        $other = $this->userWithRole('teacher');
        $session = LiveSession::create($this->sessionPayload([
            'teacher_id' => $owner->id, 'scheduled_at' => now(),
        ]));

        $this->actingAs($other)->put("/dashboard/live/{$session->id}", $this->sessionPayload([
            'title' => 'Stolen',
        ]))->assertForbidden();
        $this->actingAs($other)->delete("/dashboard/live/{$session->id}")->assertForbidden();

        $this->assertSame('Intro Live', $session->fresh()->title);
    }
}
