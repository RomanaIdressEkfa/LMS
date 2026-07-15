<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Lesson extends Model
{
    protected $fillable = [
        'course_id', 'title', 'type', 'content', 'video_url', 'video_file',
        'attachment', 'duration_minutes', 'is_preview', 'sort_order',
        'question', 'question_options', 'question_correct_index',
    ];

    protected $casts = [
        'is_preview' => 'boolean',
        'question_options' => 'array',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Normalize whatever video URL a teacher pastes into a playable embed URL.
     * Handles youtube.com/watch, youtu.be, and vimeo.com links.
     */
    public function setVideoUrlAttribute(?string $value): void
    {
        $this->attributes['video_url'] = static::toEmbedUrl($value);
    }

    public static function toEmbedUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        $url = trim($url);

        // youtu.be/ID  or  youtube.com/watch?v=ID  or  /shorts/ID
        if (preg_match('~(?:youtube\.com/(?:watch\?v=|shorts/|embed/)|youtu\.be/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }
        // vimeo.com/ID
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return $url; // already an embed url or a direct file link
    }

    /** Public URL for an uploaded video file, if any. */
    public function getVideoFileUrlAttribute(): ?string
    {
        return $this->video_file ? Storage::disk('public')->url($this->video_file) : null;
    }
}
