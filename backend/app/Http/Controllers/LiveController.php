<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\LiveSession;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Live classes (Blade). Everyone with live.view sees the upcoming list: public
 * sessions plus any tied to a course they're enrolled in or teach. The meeting
 * link is exposed only while a session is live (or to its host). Hosts
 * (live.host) also get a "My sessions" panel to schedule/edit/start/end/delete.
 */
class LiveController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $enrolledCourseIds = Enrollment::where('user_id', $user->id)->pluck('course_id');

        $sessions = LiveSession::with('teacher:id,name', 'course:id,title,slug')
            ->where('status', '!=', 'ended')
            ->where(function ($q) use ($enrolledCourseIds, $user) {
                $q->whereNull('course_id')
                    ->orWhereIn('course_id', $enrolledCourseIds)
                    ->orWhere('teacher_id', $user->id);
            })
            ->orderBy('scheduled_at')
            ->get();

        $canHost = $user->can('live.host');
        $mine = $canHost
            ? LiveSession::where('teacher_id', $user->id)->with('course:id,title')->orderByDesc('scheduled_at')->get()
            : collect();
        $courses = $canHost ? $user->teacherCourses()->orderBy('title')->get(['id', 'title']) : collect();
        $editing = $canHost && $request->query('edit') ? $mine->firstWhere('id', (int) $request->query('edit')) : null;

        return view('dashboard.live.index', compact('sessions', 'mine', 'courses', 'editing'));
    }

    public function store(Request $request)
    {
        $data = $this->validateSession($request);
        $data['teacher_id'] = $request->user()->id;
        LiveSession::create($data);

        return redirect('/dashboard/live')->with('ok', 'Session scheduled.');
    }

    public function update(Request $request, LiveSession $live)
    {
        $this->ensureOwner($request, $live);
        $live->update($this->validateSession($request));

        return redirect('/dashboard/live')->with('ok', 'Session updated.');
    }

    /** Advance the session lifecycle: scheduled → live → ended. */
    public function setStatus(Request $request, LiveSession $live)
    {
        $this->ensureOwner($request, $live);
        $status = $request->validate([
            'status' => ['required', Rule::in(['scheduled', 'live', 'ended'])],
        ])['status'];
        $live->update(['status' => $status]);

        return back()->with('ok', match ($status) {
            'live' => 'Session is now live.',
            'ended' => 'Session ended.',
            default => 'Session reopened.',
        });
    }

    public function destroy(Request $request, LiveSession $live)
    {
        $this->ensureOwner($request, $live);
        $live->delete();

        return redirect('/dashboard/live')->with('ok', 'Session deleted.');
    }

    private function validateSession(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'course_id' => ['nullable', Rule::exists('courses', 'id')],
            'provider' => ['required', Rule::in(['custom', 'zoom', 'meet', 'agora'])],
            'meeting_url' => ['nullable', 'string', 'max:500'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:5'],
        ]);
        $data['duration_minutes'] = (int) ($data['duration_minutes'] ?? 60);

        return $data;
    }

    private function ensureOwner(Request $request, LiveSession $live): void
    {
        abort_unless(
            $live->teacher_id === $request->user()->id || $request->user()->hasRole(['admin', 'super-admin']),
            403,
            'You can only manage your own sessions.'
        );
    }
}
