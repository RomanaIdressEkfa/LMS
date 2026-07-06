"use client";

import { useEffect, useState, use } from "react";
import { useRouter } from "next/navigation";
import { apiGet, apiPost, ApiError } from "@/lib/api";
import { CheckoutPanel } from "@/components/courses/CheckoutPanel";
import type { Course } from "@/lib/types";

export default function CourseDetailPage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = use(params);
  const router = useRouter();
  const [course, setCourse] = useState<Course | null>(null);
  const [loading, setLoading] = useState(true);
  const [enrolling, setEnrolling] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);
  const [showCheckout, setShowCheckout] = useState(false);

  async function load() {
    try {
      const { course } = await apiGet<{ course: Course }>(`/courses/${slug}`);
      setCourse(course);
    } catch {
      setCourse(null);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [slug]);

  async function enroll() {
    if (!course) return;
    // Paid course → open the checkout flow instead of enrolling directly.
    if (!course.is_free) {
      setShowCheckout(true);
      return;
    }
    setEnrolling(true);
    setNotice(null);
    try {
      await apiPost(`/courses/${course.id}/enroll`);
      await load();
      router.push(`/dashboard/learn/${course.slug}`);
    } catch (e) {
      setNotice(e instanceof ApiError ? e.message : "Could not enroll.");
    } finally {
      setEnrolling(false);
    }
  }

  if (loading) return <p className="text-[var(--muted)]">Loading course…</p>;
  if (!course) return <p className="text-[var(--muted)]">Course not found.</p>;

  const price = Number(course.price);

  return (
    <div className="grid gap-6 lg:grid-cols-[1fr_340px]">
      {/* Main */}
      <div className="space-y-6">
        <div className="card overflow-hidden">
          <div
            className="flex h-48 items-center justify-center text-white"
            style={{
              background: course.category
                ? `linear-gradient(135deg, ${course.category.color}, #1d4ed8)`
                : "linear-gradient(135deg, #2563ff, #1d4ed8)",
            }}
          >
            <span className="text-6xl">🎓</span>
          </div>
          <div className="p-6">
            {course.category && (
              <span className="text-xs font-bold uppercase tracking-wide" style={{ color: course.category.color }}>
                {course.category.name}
              </span>
            )}
            <h1 className="mt-1 text-3xl">{course.title}</h1>
            {course.subtitle && <p className="mt-2 text-[var(--muted)]">{course.subtitle}</p>}
            <div className="mt-4 flex flex-wrap gap-4 text-sm text-[var(--muted)]">
              <span>👨‍🏫 <b className="text-[var(--foreground)]">{course.teacher?.name}</b></span>
              <span>📚 {course.lessons?.length ?? 0} lessons</span>
              <span>🎯 {course.level}</span>
              <span>👥 {course.enrollments_count ?? 0} enrolled</span>
            </div>
          </div>
        </div>

        <div className="card p-6">
          <h2 className="text-xl">About this course</h2>
          <p className="mt-3 whitespace-pre-line text-[var(--muted)]">{course.description}</p>
        </div>

        {/* Curriculum */}
        <div className="card p-6">
          <h2 className="text-xl">Curriculum</h2>
          <div className="mt-4 divide-y divide-[var(--border)]">
            {course.lessons?.map((l, i) => (
              <div key={l.id} className="flex items-center gap-3 py-3">
                <span className="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-[var(--primary)]/10 text-sm font-bold text-[var(--primary)]">
                  {i + 1}
                </span>
                <div className="flex-1">
                  <p className="font-bold text-[var(--foreground)]">{l.title}</p>
                  <p className="text-xs text-[var(--muted)]">{l.type} · {l.duration_minutes} min</p>
                </div>
                {l.is_preview && !l.locked && (
                  <span className="rounded-full bg-[var(--success)]/15 px-2 py-0.5 text-[11px] font-bold text-[var(--success)]">
                    Preview
                  </span>
                )}
                <span className="text-lg">{l.locked ? "🔒" : "▶️"}</span>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Sidebar enroll card */}
      <div className="lg:sticky lg:top-24 lg:h-fit">
        <div className="card p-6">
          <p className="text-3xl">
            {course.is_free ? <span className="text-[var(--success)]">Free</span> : `$${price.toFixed(2)}`}
          </p>

          {course.is_enrolled ? (
            <button
              onClick={() => router.push(`/dashboard/learn/${course.slug}`)}
              className="btn-primary mt-4 w-full"
            >
              Continue Learning →
            </button>
          ) : course.is_owner ? (
            <button
              onClick={() => router.push(`/dashboard/teaching/${course.id}`)}
              className="btn-ghost mt-4 w-full"
            >
              Edit your course
            </button>
          ) : (
            <button onClick={enroll} disabled={enrolling} className="btn-primary mt-4 w-full disabled:opacity-60">
              {enrolling ? "Enrolling…" : course.is_free ? "Enroll for Free" : "Buy Course"}
            </button>
          )}

          {notice && (
            <p className="mt-3 rounded-[var(--radius)] bg-[var(--warning)]/10 px-3 py-2 text-sm font-bold text-[var(--warning)]">
              {notice}
            </p>
          )}

          <ul className="mt-6 space-y-2 text-sm text-[var(--muted)]">
            <li>✅ {course.lessons?.length ?? 0} lessons</li>
            <li>✅ Lifetime access</li>
            <li>✅ Learn at your own pace</li>
            {!course.is_free && <li>✅ Certificate of completion</li>}
          </ul>
        </div>
      </div>

      {showCheckout && (
        <CheckoutPanel course={course} onClose={() => setShowCheckout(false)} />
      )}
    </div>
  );
}
