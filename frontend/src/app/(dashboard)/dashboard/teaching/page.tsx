"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { apiGet, apiPost, ApiError } from "@/lib/api";
import type { Category, Course } from "@/lib/types";

export default function TeachingPage() {
  const router = useRouter();
  const [courses, setCourses] = useState<(Course & { enrollments_count?: number })[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState({ title: "", subtitle: "", category_id: "", level: "beginner", is_free: true, price: "" });
  const [error, setError] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);

  async function load() {
    const [c, cat] = await Promise.all([
      apiGet<{ courses: Course[] }>("/my/courses"),
      apiGet<{ categories: Category[] }>("/categories"),
    ]);
    setCourses(c.courses);
    setCategories(cat.categories);
    setLoading(false);
  }

  useEffect(() => {
    load();
  }, []);

  async function create(e: React.FormEvent) {
    e.preventDefault();
    setSaving(true);
    setError(null);
    try {
      const { course } = await apiPost<{ course: Course }>("/courses", {
        title: form.title,
        subtitle: form.subtitle || null,
        category_id: form.category_id ? Number(form.category_id) : null,
        level: form.level,
        is_free: form.is_free,
        price: form.is_free ? 0 : Number(form.price || 0),
      });
      router.push(`/dashboard/teaching/${course.id}`);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Could not create course.");
    } finally {
      setSaving(false);
    }
  }

  if (loading) return <p className="text-[var(--muted)]">Loading…</p>;

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-3xl">Teaching</h1>
          <p className="mt-1 text-[var(--muted)]">Create and manage your courses.</p>
        </div>
        <button onClick={() => setShowForm((s) => !s)} className="btn-primary">
          {showForm ? "Close" : "+ New Course"}
        </button>
      </div>

      {showForm && (
        <form onSubmit={create} className="card space-y-4 p-6">
          <h2 className="text-xl">New course</h2>
          <div>
            <label className="label">Title</label>
            <input className="input" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} required />
          </div>
          <div>
            <label className="label">Subtitle</label>
            <input className="input" value={form.subtitle} onChange={(e) => setForm({ ...form, subtitle: e.target.value })} />
          </div>
          <div className="grid gap-4 sm:grid-cols-3">
            <div>
              <label className="label">Category</label>
              <select className="input" value={form.category_id} onChange={(e) => setForm({ ...form, category_id: e.target.value })}>
                <option value="">None</option>
                {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
              </select>
            </div>
            <div>
              <label className="label">Level</label>
              <select className="input" value={form.level} onChange={(e) => setForm({ ...form, level: e.target.value })}>
                <option value="beginner">Beginner</option>
                <option value="intermediate">Intermediate</option>
                <option value="advanced">Advanced</option>
              </select>
            </div>
            <div>
              <label className="label">Pricing</label>
              <div className="flex items-center gap-2">
                <label className="flex items-center gap-1.5 text-sm font-bold">
                  <input type="checkbox" checked={form.is_free} onChange={(e) => setForm({ ...form, is_free: e.target.checked })} className="h-4 w-4 accent-[var(--primary)]" />
                  Free
                </label>
                {!form.is_free && (
                  <input type="number" min="0" step="0.01" className="input" placeholder="$" value={form.price} onChange={(e) => setForm({ ...form, price: e.target.value })} />
                )}
              </div>
            </div>
          </div>
          {error && <p className="text-sm font-bold text-[var(--danger)]">{error}</p>}
          <button type="submit" disabled={saving} className="btn-primary disabled:opacity-60">
            {saving ? "Creating…" : "Create & add lessons"}
          </button>
        </form>
      )}

      {courses.length === 0 ? (
        <div className="card grid place-items-center p-12 text-center">
          <span className="text-4xl">✏️</span>
          <p className="mt-3 font-bold">You haven&apos;t created any courses yet.</p>
        </div>
      ) : (
        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
          {courses.map((c) => (
            <Link key={c.id} href={`/dashboard/teaching/${c.id}`} className="card p-5 transition-transform hover:-translate-y-1">
              <div className="flex items-center justify-between">
                <span
                  className={`rounded-full px-2.5 py-1 text-[11px] font-bold uppercase ${
                    c.status === "published" ? "bg-[var(--success)]/15 text-[var(--success)]" : "bg-[var(--warning)]/15 text-[var(--warning)]"
                  }`}
                >
                  {c.status}
                </span>
                <span className="text-xs text-[var(--muted)]">{c.is_free ? "Free" : `$${Number(c.price).toFixed(2)}`}</span>
              </div>
              <h3 className="mt-3 text-lg">{c.title}</h3>
              <div className="mt-4 flex gap-4 border-t border-[var(--border)] pt-3 text-xs text-[var(--muted)]">
                <span>📚 {c.lessons_count ?? 0} lessons</span>
                <span>👥 {c.enrollments_count ?? 0} students</span>
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
