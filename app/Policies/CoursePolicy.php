<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /** Admins/super-admins can manage any course. */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(['super-admin', 'admin'])) {
            return true;
        }
        return null;
    }

    public function update(User $user, Course $course): bool
    {
        return $user->can('courses.update') && $course->teacher_id === $user->id;
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->can('courses.delete') && $course->teacher_id === $user->id;
    }

    /** Managing lessons follows the same ownership rule as editing the course. */
    public function manageLessons(User $user, Course $course): bool
    {
        return $user->can('lessons.create') && $course->teacher_id === $user->id;
    }
}
