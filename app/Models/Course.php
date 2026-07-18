<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Course extends Model
{
    protected $fillable = [
        'teacher_id', 'category_id', 'title', 'slug', 'subtitle', 'description',
        'thumbnail', 'level', 'is_free', 'price', 'status', 'duration_minutes',
        'published_at',
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'price' => 'decimal:2',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Auto-generate a unique slug from the title on create.
        static::creating(function (Course $course) {
            if (empty($course->slug)) {
                $base = Str::slug($course->title);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $course->slug = $slug;
            }
        });
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function isEnrolled(?User $user): bool
    {
        if (! $user) {
            return false;
        }
        return $this->enrollments()->where('user_id', $user->id)->exists();
    }
}
