<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    public function test_any_authenticated_user_reaches_the_dashboard_home(): void
    {
        $this->actingAs($this->userWithRole('student'))->get('/dashboard')->assertOk();
    }

    public function test_student_is_forbidden_from_admin_and_teacher_pages(): void
    {
        $student = $this->userWithRole('student');

        $this->actingAs($student)->get('/dashboard/users')->assertForbidden();     // users.view
        $this->actingAs($student)->get('/dashboard/teaching')->assertForbidden();  // courses.create
        $this->actingAs($student)->get('/dashboard/settings')->assertForbidden();  // settings.view
    }

    public function test_student_can_view_but_not_create_quizzes(): void
    {
        $student = $this->userWithRole('student');

        $this->actingAs($student)->get('/dashboard/quizzes')->assertOk();          // quizzes.view
        $this->actingAs($student)->post('/dashboard/quizzes', [
            'title' => 'Nope',
        ])->assertForbidden();                                                     // quizzes.create
    }

    public function test_teacher_can_reach_teaching_and_quiz_authoring(): void
    {
        $teacher = $this->userWithRole('teacher');

        $this->actingAs($teacher)->get('/dashboard/teaching')->assertOk();
        $this->actingAs($teacher)->get('/dashboard/quizzes')->assertOk();
        $this->actingAs($teacher)->get('/dashboard/users')->assertForbidden();
    }

    public function test_admin_can_reach_admin_pages(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->get('/dashboard/users')->assertOk();
        $this->actingAs($admin)->get('/dashboard/settings')->assertOk();
        $this->actingAs($admin)->get('/dashboard/roles')->assertOk();
        $this->actingAs($admin)->get('/dashboard/content')->assertOk();
    }

    public function test_platform_pages_require_super_admin(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get('/dashboard/platform/tenants')->assertForbidden();

        $this->actingAs($this->userWithRole('super-admin'))
            ->get('/dashboard/platform/tenants')->assertOk();
    }
}
