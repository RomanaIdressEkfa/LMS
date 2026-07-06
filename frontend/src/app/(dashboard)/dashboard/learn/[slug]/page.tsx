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
  const [done, setDone] = useState<Set<number>>(new Set());
  const [progress, setProgress] = useState(0);
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    try {
      const { course } = await apiGet<{ course: Course }>(`/courses/${slug}`);
      setCourse(course);
      setActive((prev) => prev ?? course.lessons?.[0] ?? null);
      if (course.id && course.is_enrolled) {
        const p = await apiGet<{ completed_lesson_ids: number[] }>(`/courses/${course.id}/progress`);
        setDone(new Set(p.completed_lesson_ids));
        const total = course.lessons?.length ?? 0;
        setProgress(total ? Math.round((p.completed_lesson_ids.length / total) * 100) : 0);
      }
    } catch {
      setCourse(null);
    } finally {
      setLoading(false);
    }
  }, [slug]);

  useEffect(() => {
    load();
  }, [load]);

  async function toggleComplete(lesson: Lesson) {
    if (!course) return;
    const completed = !done.has(lesson.id);
    const res = await apiPost<{ progress: number }>(
      `/courses/${course.id}/lessons/${lesson.id}/progress`,
      { completed }
    );
    setDone((prev) => {
      const next = new Set(prev);
      completed ? next.add(lesson.id) : next.delete(lesson.id);
      return next;
    });
    setProgress(res.progress);
  }

  if (loading) return <p className="text-[var(--muted)]">Loading…</p>;
  if (!course) return <p className="text-[var(--muted)]">Course not found.</p>;

  if (!course.is_enrolled) {
    return (
      <div className="card grid place-items-center p-12 text-center">
        <span className="text-4xl">🔒</span>
        <p className="mt-3 font-bold">You need to enroll to access this course.</p>
        <Link href={`/dashboard/courses/${course.slug}`} className="btn-primary mt-4">
          View course
        </Link>
      </div>
    );
  }

  return (
    <div className="grid gap-6 lg:grid-cols-[1fr_320px]">
      {/* Player */}
      <div className="space-y-4">
        <Link href="/dashboard/learn" className="text-sm font-bold text-[var(--primary)] hover:underline">
          ← My Learning
        </Link>
        <div className="card overflow-hidden">
          {active?.video_url ? (
            <div className="aspect-video w-full bg-black">
              <iframe
                src={active.video_url}
                className="h-full w-full"
                allowFullScreen
                title={active.title}
              />
            </div>
          ) : (
            <div className="grid aspect-video place-items-center bg-[var(--background)] text-[var(--muted)]">
              No video for this lesson
            </div>
          )}
          <div className="p-6">
            <h1 className="text-2xl">{active?.title}</h1>
            {active?.content && <p className="mt-3 whitespace-pre-line text-[var(--muted)]">{active.content}</p>}
            {active && (
              <button
                onClick={() => toggleComplete(active)}
                className={`mt-5 ${done.has(active.id) ? "btn-ghost" : "btn-primary"}`}
              >
                {done.has(active.id) ? "✓ Completed — mark incomplete" : "Mark as complete"}
              </button>
            )}
          </div>
        </div>
      </div>

      {/* Lesson list */}
      <div className="lg:sticky lg:top-24 lg:h-fit">
        <div className="card p-5">
          <div className="flex items-center justify-between">
            <h2 className="text-lg">Course content</h2>
            <span className="text-sm font-bold text-[var(--primary)]">{progress}%</span>
          </div>
          <div className="mt-3 h-2 overflow-hidden rounded-full bg-[var(--border)]">
            <div className="h-full rounded-full bg-[var(--primary)] transition-all" style={{ width: `${progress}%` }} />
          </div>

          <div className="mt-4 space-y-1">
            {course.lessons?.map((l, i) => {
              const isActive = active?.id === l.id;
              const isDone = done.has(l.id);
              return (
                <button
                  key={l.id}
                  onClick={() => setActive(l)}
                  className={`flex w-full items-center gap-3 rounded-[var(--radius)] px-3 py-2.5 text-left transition-colors ${
                    isActive ? "bg-[var(--primary)]/10" : "hover:bg-[var(--primary)]/5"
                  }`}
                >
                  <span
                    className={`grid h-6 w-6 shrink-0 place-items-center rounded-full text-xs font-bold ${
                      isDone ? "bg-[var(--success)] text-white" : "bg-[var(--border)] text-[var(--muted)]"
                    }`}
                  >
                    {isDone ? "✓" : i + 1}
                  </span>
                  <span className="flex-1 text-sm font-bold text-[var(--foreground)]">{l.title}</span>
                  <span className="text-xs text-[var(--muted)]">{l.duration_minutes}m</span>
                </button>
              );
            })}
          </div>
        </div>
      </div>
    </div>
  );
}
