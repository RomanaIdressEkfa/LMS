<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\LiveSession;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LiveSessionController extends Controller
{
    /**
     * Upcoming/live sessions visible to the current user: public ones, plus
     * any tied to a course they're enrolled in (or teach).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $enrolledCourseIds = Enrollment::where('user_id', $user->id)->pluck('course_id');

        $sessions = LiveSession::with(['teacher:id,name', 'course:id,title,slug'])
            ->where('status', '!=', 'ended')
            ->where(function ($q) use ($enrolledCourseIds, $user) {
                $q->whereNull('course_id')
                  ->orWhereIn('course_id', $enrolledCourseIds)
                  ->orWhere('teacher_id', $user->id);
            })
            ->orderBy('scheduled_at')
            ->get()
            ->map(fn ($s) => $this->present($s, $user->id));

        return response()->json(['sessions' => $sessions]);
    }

    /** Sessions the authenticated teacher hosts. */
    public function mine(Request $request)
    {
        $sessions = LiveSession::where('teacher_id', $request->user()->id)
            ->with('course:id,title')
            ->orderByDesc('scheduled_at')
            ->get();

        return response()->json(['sessions' => $sessions]);
    }

    public function store(Request $request)
    {
        $data = $this->validateSession($request);
        $data['teacher_id'] = $request->user()->id;

        $session = LiveSession::create($data);

        return response()->json(['session' => $session], 201);
    }

    public function update(Request $request, LiveSession $live)
    {
        $this->ensureOwner($request, $live);
        $live->update($this->validateSession($request));

        return response()->json(['session' => $live->fresh()]);
    }

    /** Toggle status: scheduled → live → ended. */
    public function setStatus(Request $request, LiveSession $live)
    {
        $this->ensureOwner($request, $live);
        $data = $request->validate([
            'status' => ['required', Rule::in(['scheduled', 'live', 'ended'])],
        ]);
        $live->update($data);

        return response()->json(['session' => $live]);
    }

    public function destroy(Request $request, LiveSession $live)
    {
        $this->ensureOwner($request, $live);
        $live->delete();

        return response()->json(['message' => 'Session deleted.']);
    }

    private function present(LiveSession $s, int $userId): array
    {
        $canJoin = $s->status === 'live';
        return [
            'id' => $s->id,
            'title' => $s->title,
            'description' => $s->description,
            'provider' => $s->provider,
            'scheduled_at' => $s->scheduled_at,
            'duration_minutes' => $s->duration_minutes,
            'status' => $s->status,
            'teacher' => $s->teacher,
            'course' => $s->course,
            'is_host' => $s->teacher_id === $userId,
            // Only expose the meeting link when the session is live (or to host).
            'meeting_url' => ($canJoin || $s->teacher_id === $userId) ? $s->meeting_url : null,
        ];
    }

    private function ensureOwner(Request $request, LiveSession $live): void
    {
        abort_unless(
            $live->teacher_id === $request->user()->id || $request->user()->hasRole(['admin', 'super-admin']),
            403,
            'You can only manage your own sessions.'
        );
    }

    private function validateSession(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'course_id' => ['nullable', Rule::exists('courses', 'id')],
            'provider' => ['required', Rule::in(['custom', 'zoom', 'meet', 'agora'])],
            'meeting_url' => ['nullable', 'string', 'max:500'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:5'],
        ]);
    }
}
