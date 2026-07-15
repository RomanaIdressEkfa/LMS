"use client";

import { useEffect, useState, use, useCallback } from "react";
import Link from "next/link";
import { apiGet, apiPost } from "@/lib/api";
import type { Course, Lesson } from "@/lib/types";

export default function LessonViewerPage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = use(params);
  const [course, setCourse] = useState<Course | null>(null);
  const [active, setActive] = useState<Lesson | null>(null);
  const [progress, setProgress] = useState(0);
  const [loading, setLoading] = useState(true);

  // quiz state for the active lesson
  const [answer, setAnswer] = useState<number | null>(null);
  const [result, setResult] = useState<"correct" | "wrong" | null>(null);
  const [submitting, setSubmitting] = useState(false);

  const load = useCallback(async (keepActiveId?: number) => {
    try {
      const { course } = await apiGet<{ course: Course }>(`/courses/${slug}`);
      setCourse(course);
      const total = course.lessons?.length ?? 0;
      const done = course.lessons?.filter((l) => l.completed).length ?? 0;
      setProgress(total ? Math.round((done / total) * 100) : 0);
      // choose active lesson: keep current, else first not-completed unlocked, else first
      const lessons = course.lessons ?? [];
      const keep = keepActiveId ? lessons.find((l) => l.id === keepActiveId) : null;
      const nextToDo = lessons.find((l) => l.unlocked && !l.completed);
      setActive(keep ?? nextToDo ?? lessons[0] ?? null);
    } catch {
      setCourse(null);
    } finally {
      setLoading(false);
    }
  }, [slug]);

  useEffect(() => {
    load();
  }, [load]);

  // reset quiz UI when active lesson changes
  useEffect(() => {
    setAnswer(null);
    setResult(null);
  }, [active?.id]);

  async function submitLesson() {
    if (!course || !active) return;
    setSubmitting(true);
    setResult(null);
    try {
      const body = active.has_question ? { answer_index: answer } : {};
      const res = await apiPost<{ correct: boolean; next_lesson_id: number | null; progress: number }>(
        `/courses/${course.id}/lessons/${active.id}/answer`,
        body
      );
      if (res.correct) {
        setResult("correct");
        await load(res.next_lesson_id ?? active.id);
      } else {
        setResult("wrong");
      }
    } finally {
      setSubmitting(false);
    }
  }

  if (loading) return <p className="text-[var(--muted)]">Loading…</p>;
  if (!course) return <p className="text-[var(--muted)]">Course not found.</p>;

  if (!course.is_enrolled) {
    return (
      <div className="card grid place-items-center p-12 text-center">
        <span className="text-4xl">🔒</span>
        <p className="mt-3 font-bold">Enroll to access this course.</p>
        <Link href={`/dashboard/courses/${course.slug}`} className="btn-primary mt-4">View course</Link>
      </div>
    );
  }

  const canSubmit = active && (!active.has_question || answer !== null);

  return (
    <div className="grid gap-6 lg:grid-cols-[1fr_340px]">
      {/* Player + quiz */}
      <div className="space-y-4">
        <Link href="/dashboard/learn" className="text-sm font-bold text-[var(--primary)] hover:underline">← My Learning</Link>

        <div className="card overflow-hidden">
          {/* Video: uploaded file OR embed */}
          {active?.video_file_url ? (
            <video controls className="aspect-video w-full bg-black" src={active.video_file_url} />
          ) : active?.video_url ? (
            <div className="aspect-video w-full bg-black">
              <iframe src={active.video_url} className="h-full w-full" allowFullScreen title={active.title}
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" />
            </div>
          ) : (
            <div className="grid aspect-video place-items-center bg-[var(--background)] text-[var(--muted)]">No video for this lesson</div>
          )}

          <div className="p-6">
            <h1 className="text-2xl">{active?.title}</h1>
            {active?.content && <p className="mt-2 whitespace-pre-line text-[var(--muted)]">{active.content}</p>}

            {/* Per-lesson quiz question */}
            {active && !active.completed && active.has_question && active.question && (
              <div className="mt-6 rounded-[var(--radius)] border border-[var(--border)] bg-[var(--background)] p-5">
                <p className="font-extrabold">🧠 {active.question}</p>
                <div className="mt-3 space-y-2">
                  {(active.question_options ?? []).map((opt, i) => (
                    <label key={i}
                      className={`flex cursor-pointer items-center gap-3 rounded-xl border p-3 text-sm font-bold transition-colors ${
                        answer === i ? "border-[var(--primary)] bg-[var(--primary-soft)]" : "border-[var(--border)] bg-[var(--surface)] hover:border-[var(--primary)]"
                      }`}>
                      <input type="radio" name="ans" checked={answer === i} onChange={() => { setAnswer(i); setResult(null); }} className="h-4 w-4 accent-[var(--primary)]" />
                      {opt}
                    </label>
                  ))}
                </div>
                {result === "wrong" && <p className="mt-3 text-sm font-bold text-[var(--danger)]">❌ Wrong answer — try again.</p>}
              </div>
            )}

            {active?.completed ? (
              <p className="mt-5 inline-flex items-center gap-2 font-bold text-[var(--success)]">✓ Lesson completed</p>
            ) : (
              <button onClick={submitLesson} disabled={submitting || !canSubmit} className="btn-primary mt-5 disabled:opacity-50">
                {submitting ? "Checking…" : active?.has_question ? "Submit answer & continue" : "Mark complete & continue"}
              </button>
            )}
          </div>
        </div>
      </div>

      {/* Lesson list with unlock state */}
      <div className="lg:sticky lg:top-24 lg:h-fit">
        <div className="card p-5">
          <div className="flex items-center justify-between">
            <h2 className="text-lg">Course content</h2>
            <span className="text-sm font-bold text-[var(--primary)]">{progress}%</span>
          </div>
          <div className="mt-3 h-2 overflow-hidden rounded-full bg-[var(--border)]">
            <div className="grad-primary h-full rounded-full transition-all" style={{ width: `${progress}%` }} />
          </div>

          <div className="mt-4 space-y-1">
            {course.lessons?.map((l, i) => {
              const isActive = active?.id === l.id;
              const locked = !l.unlocked;
              return (
                <button
                  key={l.id}
                  onClick={() => !locked && setActive(l)}
                  disabled={locked}
                  className={`flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition-colors ${
                    isActive ? "bg-[var(--primary-soft)]" : locked ? "opacity-60" : "hover:bg-[var(--primary)]/5"
                  } ${locked ? "cursor-not-allowed" : ""}`}
                >
                  <span className={`grid h-6 w-6 shrink-0 place-items-center rounded-full text-xs font-bold ${
                    l.completed ? "bg-[var(--success)] text-white" : locked ? "bg-[var(--border)] text-[var(--muted)]" : "bg-[var(--primary)] text-white"
                  }`}>
                    {l.completed ? "✓" : locked ? "🔒" : i + 1}
                  </span>
                  <span className="flex-1 text-sm font-bold text-[var(--foreground)]">{l.title}</span>
                  <span className="text-xs text-[var(--muted)]">{l.duration_minutes}m</span>
                </button>
              );
            })}
          </div>
          <p className="mt-4 text-center text-xs font-semibold text-[var(--muted)]">
            🔒 lessons unlock as you complete each one.
          </p>
        </div>
      </div>
    </div>
  );
}
