"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { apiGet } from "@/lib/api";
import type { Category, Course, Paginated } from "@/lib/types";

const FEATURES = [
  { icon: "🎓", title: "Sell Courses", text: "Lessons, previews, free & paid access with instant enrollment.", grad: "grad-primary" },
  { icon: "🎥", title: "Live Classes", text: "Host live video sessions students join from their dashboard.", grad: "grad-purple" },
  { icon: "📝", title: "Quizzes", text: "Auto-graded quizzes with pass marks to test knowledge.", grad: "grad-sunset" },
  { icon: "🏅", title: "Certificates", text: "Reward completion with certificates students love.", grad: "grad-teal" },
  { icon: "💳", title: "Payments", text: "Many gateways — enable whichever you want.", grad: "grad-primary" },
  { icon: "🛡️", title: "Roles & Access", text: "Build any role with exactly the permissions it needs.", grad: "grad-purple" },
];

const STATS = [
  { value: "50+", label: "Courses", grad: "grad-primary" },
  { value: "12", label: "Modules", grad: "grad-purple" },
  { value: "8", label: "Gateways", grad: "grad-sunset" },
  { value: "∞", label: "Roles", grad: "grad-teal" },
];

const CAT_GRAD = ["grad-primary", "grad-purple", "grad-sunset", "grad-teal", "grad-primary"];

export default function HomePage() {
  const [courses, setCourses] = useState<Course[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);

  useEffect(() => {
    apiGet<Paginated<Course>>("/courses").then((d) => setCourses(d.data.slice(0, 3))).catch(() => {});
    apiGet<{ categories: Category[] }>("/categories").then((d) => setCategories(d.categories)).catch(() => {});
  }, []);

  return (
    <div>
      {/* Hero */}
      <section className="grad-hero">
        <div className="mx-auto grid max-w-7xl items-center gap-10 px-5 py-16 md:grid-cols-2 md:px-8 md:py-24">
          <div>
            <span className="pill bg-[var(--surface)] text-[var(--primary)] shadow-sm">⭐ The complete learning platform</span>
            <h1 className="mt-5 text-4xl leading-[1.05] md:text-6xl">
              Learn &amp; teach<br />
              <span className="gradient-text">without limits.</span>
            </h1>
            <p className="mt-5 max-w-md text-lg font-semibold text-[var(--muted)]">
              Create and sell courses, run live classes, build quizzes, and grow your own
              online academy — all in one bold, colorful platform.
            </p>
            <div className="mt-8 flex flex-wrap gap-3">
              <Link href="/register" className="btn-primary text-base">Start Learning Free →</Link>
              <Link href="/courses" className="btn-ghost text-base">Browse Courses</Link>
            </div>
            <div className="mt-10 grid max-w-lg grid-cols-4 gap-3">
              {STATS.map((s) => (
                <div key={s.label} className="card p-4 text-center">
                  <p className={`text-2xl md:text-3xl ${s.grad} bg-clip-text text-transparent`}>{s.value}</p>
                  <p className="text-[11px] font-bold text-[var(--muted)]">{s.label}</p>
                </div>
              ))}
            </div>
          </div>

          {/* Hero visual: floating colorful cards */}
          <div className="relative hidden md:block">
            <div className="grad-primary absolute left-6 top-4 h-40 w-64 rotate-[-6deg] rounded-3xl opacity-90 shadow-[0_30px_60px_-20px_rgba(37,99,255,0.5)]" />
            <div className="grad-purple absolute right-2 top-24 h-44 w-64 rotate-[5deg] rounded-3xl opacity-90 shadow-[0_30px_60px_-20px_rgba(124,58,237,0.5)]" />
            <div className="card relative z-10 ml-10 mt-16 w-72 p-6">
              <div className="grad-sunset grid h-12 w-12 place-items-center rounded-2xl text-2xl">🎓</div>
              <p className="mt-4 text-lg font-extrabold">Modern React</p>
              <p className="text-sm font-semibold text-[var(--muted)]">5 lessons · Intermediate</p>
              <div className="mt-4 h-2 rounded-full bg-[var(--border)]">
                <div className="grad-primary h-full w-2/3 rounded-full" />
              </div>
              <p className="mt-2 text-xs font-bold text-[var(--primary)]">66% complete</p>
            </div>
          </div>
        </div>
      </section>

      {/* Categories */}
      {categories.length > 0 && (
        <section className="mx-auto max-w-7xl px-5 py-10 md:px-8">
          <div className="flex flex-wrap gap-3">
            {categories.map((c, i) => (
              <Link key={c.id} href={`/courses?category=${c.slug}`}
                className={`pill ${CAT_GRAD[i % CAT_GRAD.length]} text-white transition-transform hover:-translate-y-0.5`}>
                {c.name}
              </Link>
            ))}
          </div>
        </section>
      )}

      {/* Features */}
      <section className="mx-auto max-w-7xl px-5 py-16 md:px-8 md:py-20">
        <div className="text-center">
          <h2 className="text-3xl md:text-4xl">Everything to run an <span className="gradient-text">academy</span></h2>
          <p className="mx-auto mt-3 max-w-xl font-semibold text-[var(--muted)]">Turn features on or off — pay for and use only what you need.</p>
        </div>
        <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {FEATURES.map((f) => (
            <div key={f.title} className="card p-7 transition-transform hover:-translate-y-1">
              <div className={`grid h-14 w-14 place-items-center rounded-2xl text-2xl text-white ${f.grad}`}>{f.icon}</div>
              <h3 className="mt-5 text-xl">{f.title}</h3>
              <p className="mt-2 font-semibold text-[var(--muted)]">{f.text}</p>
            </div>
          ))}
        </div>
      </section>

      {/* Featured courses */}
      <section className="bg-[var(--surface)]">
        <div className="mx-auto max-w-7xl px-5 py-16 md:px-8 md:py-20">
          <div className="flex flex-wrap items-end justify-between gap-3">
            <h2 className="text-3xl md:text-4xl">Featured courses</h2>
            <Link href="/courses" className="btn-ghost">See all courses →</Link>
          </div>
          <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {courses.length === 0 ? (
              <p className="text-[var(--muted)]">Loading…</p>
            ) : (
              courses.map((c) => (
                <Link key={c.id} href="/register" className="card group flex flex-col overflow-hidden transition-transform hover:-translate-y-1">
                  <div className="flex h-44 items-center justify-center text-white" style={{ background: c.category ? `linear-gradient(135deg, ${c.category.color}, #7c3aed)` : "linear-gradient(135deg, #2563ff, #7c3aed)" }}>
                    <span className="text-5xl">🎓</span>
                  </div>
                  <div className="flex flex-1 flex-col p-6">
                    {c.category && <span className="text-xs font-bold uppercase" style={{ color: c.category.color }}>{c.category.name}</span>}
                    <h3 className="mt-1 text-lg">{c.title}</h3>
                    <p className="mt-1 flex-1 text-sm font-semibold text-[var(--muted)]">{c.subtitle}</p>
                    <div className="mt-4 flex items-center justify-between border-t border-[var(--border)] pt-3">
                      <span className="text-sm font-bold">{c.teacher?.name}</span>
                      <span className={`text-sm font-extrabold ${c.is_free ? "text-[var(--success)]" : "text-[var(--primary)]"}`}>
                        {c.is_free ? "FREE" : `$${Number(c.price).toFixed(2)}`}
                      </span>
                    </div>
                  </div>
                </Link>
              ))
            )}
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="mx-auto max-w-7xl px-5 py-16 md:px-8 md:py-20">
        <div className="grad-purple relative overflow-hidden rounded-[2rem] px-8 py-16 text-center text-white md:py-20">
          <div className="absolute inset-0 opacity-20" style={{ backgroundImage: "radial-gradient(circle at 20% 20%, #fff 2px, transparent 0)", backgroundSize: "40px 40px" }} />
          <div className="relative">
            <h2 className="text-3xl md:text-4xl">Ready to build your academy?</h2>
            <p className="mx-auto mt-3 max-w-lg font-semibold text-white/85">Join as a student, teacher, or organization and start today.</p>
            <div className="mt-8 flex flex-wrap justify-center gap-3">
              <Link href="/register" className="rounded-xl bg-white px-6 py-3 font-bold text-[var(--purple)] transition-transform hover:-translate-y-0.5">Create free account</Link>
              <Link href="/login" className="rounded-xl border border-white/40 px-6 py-3 font-bold text-white hover:bg-white/10">Log in</Link>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
