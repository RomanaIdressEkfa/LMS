"use client";

import { useEffect, useState, use } from "react";
import Link from "next/link";
import { apiGet, apiPost, apiPut, apiDelete, apiUpload, ApiError } from "@/lib/api";
import type { Course, Lesson } from "@/lib/types";

const EMPTY_LESSON = {
  title: "", type: "video", video_url: "", content: "", duration_minutes: 10, is_preview: false,
  question: "", question_options: ["", ""] as string[], question_correct_index: 0,
};

export default function CourseEditorPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = use(params);
  const [course, setCourse] = useState<Course | null>(null);
  const [loading, setLoading] = useState(true);
  const [savingCourse, setSavingCourse] = useState(false);
  const [msg, setMsg] = useState<string | null>(null);

  // course fields
  const [fields, setFields] = useState({ title: "", subtitle: "", description: "", level: "beginner", is_free: true, price: "" });

  // lesson form
  const [lessonForm, setLessonForm] = useState<typeof EMPTY_LESSON>(EMPTY_LESSON);
  const [editingLesson, setEditingLesson] = useState<number | null>(null);

  async function load() {
    const { course } = await apiGet<{ course: Course }>(`/courses/${id}/manage`);
    setCourse(course);
    setFields({
      title: course.title,
      subtitle: course.subtitle ?? "",
      description: course.description ?? "",
      level: course.level,
      is_free: course.is_free,
      price: String(course.price ?? ""),
    });
    setLoading(false);
  }

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id]);

  async function saveCourse(e: React.FormEvent) {
    e.preventDefault();
    if (!course) return;
    setSavingCourse(true);
    setMsg(null);
    try {
      await apiPut(`/courses/${course.id}`, {
        title: fields.title,
        subtitle: fields.subtitle || null,
        description: fields.description || null,
        level: fields.level,
        is_free: fields.is_free,
        price: fields.is_free ? 0 : Number(fields.price || 0),
      });
      setMsg("Saved ✔");
      await load();
    } catch (err) {
      setMsg(err instanceof ApiError ? err.message : "Save failed");
    } finally {
      setSavingCourse(false);
    }
  }

  async function togglePublish() {
    if (!course) return;
    const publish = course.status !== "published";
    await apiPost(`/courses/${course.id}/publish`, { publish });
    await load();
  }

  async function submitLesson(e: React.FormEvent) {
    e.preventDefault();
    if (!course) return;
    const cleanOptions = lessonForm.question_options.filter((o) => o.trim() !== "");
    const hasQuestion = lessonForm.question.trim() !== "" && cleanOptions.length >= 2;
    const payload = {
      ...lessonForm,
      duration_minutes: Number(lessonForm.duration_minutes),
      question: hasQuestion ? lessonForm.question : null,
      question_options: hasQuestion ? cleanOptions : null,
      question_correct_index: hasQuestion ? Math.min(lessonForm.question_correct_index, cleanOptions.length - 1) : null,
    };
    if (editingLesson) {
      await apiPut(`/courses/${course.id}/lessons/${editingLesson}`, payload);
    } else {
      await apiPost(`/courses/${course.id}/lessons`, payload);
    }
    setLessonForm(EMPTY_LESSON);
    setEditingLesson(null);
    await load();
  }

  function editLesson(l: Lesson) {
    setEditingLesson(l.id);
    setLessonForm({
      title: l.title,
      type: l.type,
      video_url: l.video_url ?? "",
      content: l.content ?? "",
      duration_minutes: l.duration_minutes,
      is_preview: l.is_preview,
      question: l.question ?? "",
      question_options: l.question_options && l.question_options.length >= 2 ? l.question_options : ["", ""],
      question_correct_index: l.question_correct_index ?? 0,
    });
  }

  async function uploadVideo(lessonId: number, file: File) {
    if (!course) return;
    setMsg("Uploading video…");
    try {
      const fd = new FormData();
      fd.append("video", file);
      await apiUpload(`/courses/${course.id}/lessons/${lessonId}/video`, fd);
      setMsg("Video uploaded ✔");
      await load();
    } catch (e) {
      setMsg(e instanceof ApiError ? e.message : "Upload failed");
    }
  }

  const setOption = (i: number, val: string) =>
    setLessonForm((f) => ({ ...f, question_options: f.question_options.map((o, idx) => (idx === i ? val : o)) }));

  async function deleteLesson(l: Lesson) {
    if (!course || !confirm(`Delete lesson "${l.title}"?`)) return;
    await apiDelete(`/courses/${course.id}/lessons/${l.id}`);
    await load();
  }

  if (loading) return <p className="text-[var(--muted)]">Loading editor…</p>;
  if (!course) return <p className="text-[var(--muted)]">Course not found.</p>;

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <Link href="/dashboard/teaching" className="text-sm font-bold text-[var(--primary)] hover:underline">
            ← Teaching
          </Link>
          <h1 className="mt-1 text-3xl">Edit Course</h1>
        </div>
        <div className="flex items-center gap-2">
          <Link href={`/dashboard/courses/${course.slug}`} className="btn-ghost">Preview</Link>
          <button
            onClick={togglePublish}
            className={course.status === "published" ? "btn-ghost" : "btn-primary"}
          >
            {course.status === "published" ? "Unpublish" : "Publish"}
          </button>
        </div>
      </div>

      <div className="grid gap-6 lg:grid-cols-2">
        {/* Course details */}
        <form onSubmit={saveCourse} className="card space-y-4 p-6">
          <div className="flex items-center justify-between">
            <h2 className="text-xl">Details</h2>
            <span
              className={`rounded-full px-2.5 py-1 text-[11px] font-bold uppercase ${
                course.status === "published" ? "bg-[var(--success)]/15 text-[var(--success)]" : "bg-[var(--warning)]/15 text-[var(--warning)]"
              }`}
            >
              {course.status}
            </span>
          </div>
          <div>
            <label className="label">Title</label>
            <input className="input" value={fields.title} onChange={(e) => setFields({ ...fields, title: e.target.value })} required />
          </div>
          <div>
            <label className="label">Subtitle</label>
            <input className="input" value={fields.subtitle} onChange={(e) => setFields({ ...fields, subtitle: e.target.value })} />
          </div>
          <div>
            <label className="label">Description</label>
            <textarea className="input min-h-28" value={fields.description} onChange={(e) => setFields({ ...fields, description: e.target.value })} />
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="label">Level</label>
              <select className="input" value={fields.level} onChange={(e) => setFields({ ...fields, level: e.target.value })}>
                <option value="beginner">Beginner</option>
                <option value="intermediate">Intermediate</option>
                <option value="advanced">Advanced</option>
              </select>
            </div>
            <div>
              <label className="label">Pricing</label>
              <div className="flex items-center gap-2">
                <label className="flex items-center gap-1.5 text-sm font-bold">
                  <input type="checkbox" checked={fields.is_free} onChange={(e) => setFields({ ...fields, is_free: e.target.checked })} className="h-4 w-4 accent-[var(--primary)]" />
                  Free
                </label>
                {!fields.is_free && (
                  <input type="number" min="0" step="0.01" className="input" placeholder="$" value={fields.price} onChange={(e) => setFields({ ...fields, price: e.target.value })} />
                )}
              </div>
            </div>
          </div>
          <div className="flex items-center gap-3">
            <button type="submit" disabled={savingCourse} className="btn-primary disabled:opacity-60">
              {savingCourse ? "Saving…" : "Save details"}
            </button>
            {msg && <span className="text-sm font-bold text-[var(--success)]">{msg}</span>}
          </div>
        </form>

        {/* Curriculum builder */}
        <div className="space-y-6">
          <div className="card p-6">
            <h2 className="text-xl">Curriculum ({course.lessons?.length ?? 0})</h2>
            <div className="mt-4 space-y-2">
              {course.lessons?.length ? (
                course.lessons.map((l, i) => (
                  <div key={l.id} className="flex items-center gap-3 rounded-[var(--radius)] border border-[var(--border)] p-3">
                    <span className="grid h-7 w-7 place-items-center rounded-full bg-[var(--primary)]/10 text-xs font-bold text-[var(--primary)]">{i + 1}</span>
                    <div className="flex-1">
                      <p className="font-bold text-[var(--foreground)]">{l.title}</p>
                      <p className="text-xs text-[var(--muted)]">{l.type} · {l.duration_minutes}m {l.is_preview && "· preview"}</p>
                    </div>
                    <button onClick={() => editLesson(l)} className="text-sm font-bold text-[var(--primary)] hover:underline">Edit</button>
                    <button onClick={() => deleteLesson(l)} className="text-sm font-bold text-[var(--danger)] hover:underline">Delete</button>
                  </div>
                ))
              ) : (
                <p className="text-sm text-[var(--muted)]">No lessons yet — add your first below.</p>
              )}
            </div>
          </div>

          <form onSubmit={submitLesson} className="card space-y-3 p-6">
            <h3 className="text-lg">{editingLesson ? "Edit lesson" : "Add lesson"}</h3>
            <input className="input" placeholder="Lesson title" value={lessonForm.title} onChange={(e) => setLessonForm({ ...lessonForm, title: e.target.value })} required />
            <div className="grid grid-cols-2 gap-3">
              <select className="input" value={lessonForm.type} onChange={(e) => setLessonForm({ ...lessonForm, type: e.target.value })}>
                <option value="video">Video</option>
                <option value="text">Text</option>
                <option value="pdf">PDF</option>
              </select>
              <input type="number" min="0" className="input" placeholder="Minutes" value={lessonForm.duration_minutes} onChange={(e) => setLessonForm({ ...lessonForm, duration_minutes: Number(e.target.value) })} />
            </div>
            {lessonForm.type === "video" && (
              <div>
                <input className="input" placeholder="Paste any YouTube or Vimeo link" value={lessonForm.video_url} onChange={(e) => setLessonForm({ ...lessonForm, video_url: e.target.value })} />
                <p className="mt-1 text-xs font-semibold text-[var(--muted)]">Paste a normal link like <code>youtube.com/watch?v=…</code> — it&apos;s converted automatically.</p>
                {/* Upload a video file (only after the lesson exists) */}
                {editingLesson && (
                  <div className="mt-3 rounded-xl border border-dashed border-[var(--border)] p-3">
                    <p className="text-xs font-bold text-[var(--muted)]">…or upload a video file (mp4/webm, max 200MB)</p>
                    <input type="file" accept="video/mp4,video/webm,video/quicktime" className="mt-2 text-sm"
                      onChange={(e) => { const f = e.target.files?.[0]; if (f) uploadVideo(editingLesson, f); }} />
                  </div>
                )}
              </div>
            )}
            <textarea className="input min-h-20" placeholder="Lesson notes / content" value={lessonForm.content} onChange={(e) => setLessonForm({ ...lessonForm, content: e.target.value })} />

            {/* Per-lesson quiz question (unlocks the next lesson) */}
            <div className="rounded-xl border border-[var(--border)] p-4">
              <p className="text-sm font-extrabold">🧠 Quiz question <span className="font-semibold text-[var(--muted)]">(optional — students answer to unlock the next lesson)</span></p>
              <textarea className="input mt-3 min-h-16" placeholder="Question text" value={lessonForm.question} onChange={(e) => setLessonForm({ ...lessonForm, question: e.target.value })} />
              <p className="label mt-3">Options (pick the correct one)</p>
              {lessonForm.question_options.map((opt, i) => (
                <div key={i} className="mb-2 flex items-center gap-2">
                  <input type="radio" name="lq-correct" checked={lessonForm.question_correct_index === i} onChange={() => setLessonForm({ ...lessonForm, question_correct_index: i })} className="h-4 w-4 accent-[var(--success)]" />
                  <input className="input" placeholder={`Option ${i + 1}`} value={opt} onChange={(e) => setOption(i, e.target.value)} />
                  {lessonForm.question_options.length > 2 && (
                    <button type="button" onClick={() => setLessonForm({ ...lessonForm, question_options: lessonForm.question_options.filter((_, idx) => idx !== i) })} className="text-[var(--danger)]">×</button>
                  )}
                </div>
              ))}
              {lessonForm.question_options.length < 5 && (
                <button type="button" onClick={() => setLessonForm({ ...lessonForm, question_options: [...lessonForm.question_options, ""] })} className="text-sm font-bold text-[var(--primary)] hover:underline">+ Add option</button>
              )}
            </div>

            <label className="flex items-center gap-2 text-sm font-bold">
              <input type="checkbox" checked={lessonForm.is_preview} onChange={(e) => setLessonForm({ ...lessonForm, is_preview: e.target.checked })} className="h-4 w-4 accent-[var(--primary)]" />
              Free preview (visible before enrolling)
            </label>
            <div className="flex gap-2">
              <button type="submit" className="btn-primary">{editingLesson ? "Update lesson" : "Add lesson"}</button>
              {editingLesson && (
                <button type="button" onClick={() => { setEditingLesson(null); setLessonForm(EMPTY_LESSON); }} className="btn-ghost">Cancel</button>
              )}
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
