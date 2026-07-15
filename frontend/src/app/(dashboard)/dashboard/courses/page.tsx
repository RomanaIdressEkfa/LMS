"use client";

import { useEffect, useState, useCallback } from "react";
import Link from "next/link";
import { apiGet } from "@/lib/api";
import { useAuth } from "@/lib/auth";
import { useLang } from "@/lib/i18n";
import { CourseCard } from "@/components/courses/CourseCard";
import type { Category, Course, Paginated } from "@/lib/types";

export default function CatalogPage() {
  const { can } = useAuth();
  const { t } = useLang();
  const [courses, setCourses] = useState<Course[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [category, setCategory] = useState<string>("");
  const [price, setPrice] = useState<string>("");
  const [search, setSearch] = useState("");
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    setLoading(true);
    const params = new URLSearchParams();
    if (category) params.set("category", category);
    if (price) params.set("price", price);
    if (search) params.set("search", search);
    const data = await apiGet<Paginated<Course>>(`/courses?${params.toString()}`);
    setCourses(data.data);
    setLoading(false);
  }, [category, price, search]);

  useEffect(() => {
    apiGet<{ categories: Category[] }>("/categories").then((d) => setCategories(d.categories));
  }, []);

  useEffect(() => {
    const t = setTimeout(load, 250); // debounce search
    return () => clearTimeout(t);
  }, [load]);

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-3xl">{t("cat.title")}</h1>
          <p className="mt-1 text-[var(--muted)]">{t("cat.sub")}</p>
        </div>
        {can("courses.create") && (
          <Link href="/dashboard/teaching" className="btn-primary">{t("cat.create")}</Link>
        )}
      </div>

      {/* Filters */}
      <div className="card flex flex-wrap items-center gap-3 p-4">
        <input
          className="input max-w-xs"
          placeholder={t("cat.search")}
          value={search}
          onChange={(e) => setSearch(e.target.value)}
        />
        <select className="input max-w-[200px]" value={category} onChange={(e) => setCategory(e.target.value)}>
          <option value="">{t("cat.allCats")}</option>
          {categories.map((c) => (
            <option key={c.id} value={c.slug}>{c.name} ({c.courses_count ?? 0})</option>
          ))}
        </select>
        <div className="flex gap-2">
          {[["", t("cat.all")], ["free", t("cat.freeF")], ["paid", t("cat.paid")]].map(([val, label]) => (
            <button
              key={val}
              onClick={() => setPrice(val)}
              className={`rounded-[var(--radius)] border px-4 py-2 text-sm font-bold transition-colors ${
                price === val
                  ? "border-[var(--primary)] bg-[var(--primary)] text-white"
                  : "border-[var(--border)] text-[var(--foreground)] hover:border-[var(--primary)]"
              }`}
            >
              {label}
            </button>
          ))}
        </div>
      </div>

      {loading ? (
        <p className="text-[var(--muted)]">{t("cat.loading")}</p>
      ) : courses.length === 0 ? (
        <div className="card grid place-items-center p-12 text-center">
          <span className="text-4xl">🔍</span>
          <p className="mt-3 font-bold">{t("cat.none")}</p>
        </div>
      ) : (
        <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
          {courses.map((c) => (
            <CourseCard key={c.id} course={c} />
          ))}
        </div>
      )}
    </div>
  );
}
