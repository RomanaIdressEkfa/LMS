"use client";

import { useEffect, useState, useCallback, Suspense } from "react";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { apiGet } from "@/lib/api";
import type { Category, Course, Paginated } from "@/lib/types";

function CoursesInner() {
  const params = useSearchParams();
  const [courses, setCourses] = useState<Course[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [category, setCategory] = useState(params.get("category") ?? "");
  const [price, setPrice] = useState("");
  const [search, setSearch] = useState("");
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    setLoading(true);
    const q = new URLSearchParams();
    if (category) q.set("category", category);
    if (price) q.set("price", price);
    if (search) q.set("search", search);
    const d = await apiGet<Paginated<Course>>(`/courses?${q.toString()}`);
    setCourses(d.data);
    setLoading(false);
  }, [category, price, search]);

  useEffect(() => {
    apiGet<{ categories: Category[] }>("/categories").then((d) => setCategories(d.categories)).catch(() => {});
  }, []);
  useEffect(() => {
    const t = setTimeout(load, 250);
    return () => clearTimeout(t);
  }, [load]);

  return (
    <div>
      {/* header band */}
      <section className="grad-hero border-b border-[var(--border)]">
        <div className="mx-auto max-w-7xl px-5 py-14 md:px-8">
          <h1 className="text-4xl md:text-5xl">Explore <span className="gradient-text">courses</span></h1>
          <p className="mt-3 max-w-xl text-lg font-semibold text-[var(--muted)]">Learn from real instructors. Free courses start instantly.</p>
        </div>
      </section>

      <div className="mx-auto max-w-7xl px-5 py-10 md:px-8">
        {/* filters */}
        <div className="card flex flex-wrap items-center gap-3 p-4">
          <input className="input max-w-xs" placeholder="Search courses…" value={search} onChange={(e) => setSearch(e.target.value)} />
          <select className="input max-w-[200px]" value={category} onChange={(e) => setCategory(e.target.value)}>
            <option value="">All categories</option>
            {categories.map((c) => <option key={c.id} value={c.slug}>{c.name}</option>)}
          </select>
          <div className="flex gap-2">
            {[["", "All"], ["free", "Free"], ["paid", "Paid"]].map(([v, l]) => (
              <button key={v} onClick={() => setPrice(v)}
                className={`rounded-xl border px-4 py-2 text-sm font-bold transition-colors ${price === v ? "grad-primary border-transparent text-white" : "border-[var(--border)] hover:border-[var(--primary)]"}`}>
                {l}
              </button>
            ))}
          </div>
        </div>

        {loading ? (
          <p className="mt-8 text-[var(--muted)]">Loading courses…</p>
        ) : courses.length === 0 ? (
          <div className="card mt-8 grid place-items-center p-12 text-center">
            <span className="text-4xl">🔍</span>
            <p className="mt-3 font-bold">No courses match your filters.</p>
          </div>
        ) : (
          <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {courses.map((c) => (
              <Link key={c.id} href="/register" className="card group flex flex-col overflow-hidden transition-transform hover:-translate-y-1">
                <div className="relative h-44 text-white" style={{ background: c.category ? `linear-gradient(135deg, ${c.category.color}, #7c3aed)` : "linear-gradient(135deg, #2563ff, #7c3aed)" }}>
                  <div className="absolute inset-0 flex items-center justify-center text-5xl">🎓</div>
                  <span className="pill absolute left-3 top-3 bg-black/25 text-white backdrop-blur">{c.level}</span>
                  <span className={`pill absolute right-3 top-3 ${c.is_free ? "bg-[var(--success)] text-white" : "bg-white text-[var(--primary)]"}`}>
                    {c.is_free ? "FREE" : `$${Number(c.price).toFixed(2)}`}
                  </span>
                </div>
                <div className="flex flex-1 flex-col p-6">
                  {c.category && <span className="text-xs font-bold uppercase" style={{ color: c.category.color }}>{c.category.name}</span>}
                  <h3 className="mt-1 text-lg">{c.title}</h3>
                  <p className="mt-1 flex-1 text-sm font-semibold text-[var(--muted)]">{c.subtitle}</p>
                  <div className="mt-4 flex items-center justify-between border-t border-[var(--border)] pt-3 text-xs text-[var(--muted)]">
                    <span className="font-bold text-[var(--foreground)]">{c.teacher?.name}</span>
                    <span>{c.lessons_count ?? 0} lessons</span>
                  </div>
                </div>
              </Link>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}

export default function PublicCoursesPage() {
  return (
    <Suspense fallback={<p className="p-8 text-[var(--muted)]">Loading…</p>}>
      <CoursesInner />
    </Suspense>
  );
}
