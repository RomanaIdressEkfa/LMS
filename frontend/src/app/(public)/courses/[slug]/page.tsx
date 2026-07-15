"use client";

import { useEffect, useState, use } from "react";
import Link from "next/link";
import { apiGet } from "@/lib/api";
import { useLang } from "@/lib/i18n";
import type { Course, Lesson } from "@/lib/types";

export default function PublicCourseDetail({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = use(params);
  const { t } = useLang();
  const [course, setCourse] = useState<Course | null>(null);
  const [loading, setLoading] = useState(true);
  const [preview, setPreview] = useState<Lesson | null>(null); // lesson playing in the modal

  useEffect(() => {
    apiGet<{ course: Course }>(`/courses/${slug}`)
      .then((d) => setCourse(d.course))
      .catch(() => setCourse(null))
      .finally(() => setLoading(false));
  }, [slug]);

  if (loading) return <p className="mx-auto max-w-[1600px] px-5 py-16 text-[var(--muted)] md:px-8">Loading…</p>;
  if (!course) {
    return (
      <div className="mx-auto max-w-3xl px-5 py-20 text-center md:px-8">
        <span className="text-5xl">🔍</span>
        <h1 className="mt-4 text-3xl">Course not found</h1>
        <Link href="/courses" className="btn-primary mt-6">{t("nav.courses")}</Link>
      </div>
    );
  }

  const price = Number(course.price);
  const firstPreview = course.lessons?.find((l) => l.is_preview && !l.locked);

  function playPreview(l: Lesson) {
    if (l.is_preview && !l.locked) setPreview(l);
  }

  return (
    <div className="grad-hero">
      <div className="mx-auto max-w-[1600px] px-5 py-8 md:px-8 md:py-10">
        <Link href="/courses" className="text-sm font-bold text-[var(--primary)] hover:underline">← {t("nav.courses")}</Link>

        <div className="mt-5 grid gap-8 lg:grid-cols-[minmax(0,1fr)_380px]">
          {/* ===== Left: main content ===== */}
          <div className="min-w-0 space-y-6">
            {/* Header */}
            <div>
              {course.category && <span className="pill grad-ph text-white">{course.category.name}</span>}
              <h1 className="mt-3 text-3xl md:text-5xl">{course.title}</h1>
              {course.subtitle && <p className="mt-3 text-lg font-semibold text-[var(--muted)]">{course.subtitle}</p>}
              <div className="mt-4 flex flex-wrap gap-4 text-sm font-semibold text-[var(--muted)]">
                <span>👨‍🏫 <b className="text-[var(--foreground)]">{course.teacher?.name}</b></span>
                <span>📚 {course.lessons?.length ?? 0} lessons</span>
                <span>🎯 {course.level}</span>
                <span>👥 {course.enrollments_count ?? 0} enrolled</span>
              </div>
            </div>

            {/* About */}
            <div className="card p-6 md:p-8">
              <h2 className="text-xl">About this course</h2>
              <p className="mt-3 whitespace-pre-line font-semibold text-[var(--muted)]">{course.description}</p>
            </div>

            {/* Curriculum */}
            <div className="card p-6 md:p-8">
              <h2 className="text-xl">Curriculum</h2>
              <div className="mt-4 divide-y divide-[var(--border)]">
                {course.lessons?.map((l, i) => {
                  const canPreview = l.is_preview && !l.locked;
                  return (
                    <button
                      key={l.id}
                      onClick={() => playPreview(l)}
                      disabled={!canPreview}
                      className={`flex w-full items-center gap-3 py-3.5 text-left transition-colors ${canPreview ? "hover:bg-[var(--primary)]/5 rounded-lg -mx-2 px-2" : ""} ${!canPreview ? "cursor-default" : ""}`}
                    >
                      <span className="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-[var(--primary-soft)] text-sm font-bold text-[var(--primary)]">{i + 1}</span>
                      <div className="flex-1">
                        <p className="font-bold text-[var(--foreground)]">{l.title}</p>
                        <p className="text-xs text-[var(--muted)]">{l.type} · {l.duration_minutes} min</p>
                      </div>
                      {canPreview ? (
                        <span className="pill bg-[var(--success)]/15 text-[var(--success)]">▶ Preview</span>
                      ) : (
                        <span className="text-lg">🔒</span>
                      )}
                    </button>
                  );
                })}
              </div>
              <p className="mt-4 text-sm font-semibold text-[var(--muted)]">🔒 Enroll to unlock all lessons and start learning.</p>
            </div>
          </div>

          {/* ===== Right: sticky enroll card ===== */}
          <div className="lg:sticky lg:top-24 lg:h-fit">
            <div className="card overflow-hidden">
              <button
                onClick={() => firstPreview && setPreview(firstPreview)}
                className="relative flex h-44 w-full items-center justify-center text-white"
                style={{ background: course.category ? `linear-gradient(135deg, ${course.category.color}, #a020f0)` : "linear-gradient(135deg, #2563ff, #a020f0)" }}
              >
                <span className="text-5xl">🎓</span>
                {firstPreview && (
                  <span className="absolute bottom-3 left-1/2 -translate-x-1/2 pill bg-black/40 text-white backdrop-blur">▶ Watch free preview</span>
                )}
              </button>
              <div className="p-6">
                <p className="text-3xl">
                  {course.is_free ? <span className="text-[var(--success)]">{t("free")}</span> : `$${price.toFixed(2)}`}
                </p>
                <Link href="/register" className="btn-primary mt-4 w-full">
                  {course.is_free ? t("home.startFree") : t("ph.cta1")}
                </Link>
                <p className="mt-2 text-center text-xs font-semibold text-[var(--muted)]">
                  <Link href="/login" className="text-[var(--primary)] hover:underline">{t("nav.login")}</Link> to enroll
                </p>
                <ul className="mt-5 space-y-2 text-sm font-semibold text-[var(--muted)]">
                  <li>✅ {course.lessons?.length ?? 0} lessons</li>
                  <li>✅ Lifetime access</li>
                  <li>✅ Learn at your own pace</li>
                  {!course.is_free && <li>✅ Certificate of completion</li>}
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* ===== Preview video modal ===== */}
      {preview && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" onClick={() => setPreview(null)}>
          <div className="w-full max-w-4xl overflow-hidden rounded-[var(--radius)] bg-black" onClick={(e) => e.stopPropagation()}>
            <div className="flex items-center justify-between bg-[var(--surface)] px-5 py-3">
              <p className="font-bold text-[var(--foreground)]">▶ {preview.title} <span className="font-semibold text-[var(--muted)]">· free preview</span></p>
              <button onClick={() => setPreview(null)} className="text-2xl leading-none text-[var(--muted)] hover:text-[var(--foreground)]">×</button>
            </div>
            {preview.video_file_url ? (
              <video controls autoPlay className="aspect-video w-full bg-black" src={preview.video_file_url} />
            ) : preview.video_url ? (
              <div className="aspect-video w-full">
                <iframe src={preview.video_url + (preview.video_url.includes("?") ? "&" : "?") + "autoplay=1"} className="h-full w-full" allow="autoplay; encrypted-media; picture-in-picture" allowFullScreen title={preview.title} />
              </div>
            ) : (
              <div className="grid aspect-video place-items-center text-white/70">No video for this preview</div>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
