"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { apiGet } from "@/lib/api";
import { useLang } from "@/lib/i18n";
import { useSiteConfig } from "@/lib/site";
import { useContent, loc } from "@/lib/content";
import type { Course, Paginated } from "@/lib/types";

export default function HomePage() {
  const { t, lang } = useLang();
  const { settings } = useSiteConfig();
  const { home } = useContent();
  const TECHS = home.techStrip;
  const STACK = home.stack;
  const STORIES = home.stories;
  const [courses, setCourses] = useState<Course[]>([]);

  useEffect(() => {
    apiGet<Paginated<Course>>("/courses").then((d) => setCourses(d.data.slice(0, 3))).catch(() => {});
  }, []);

  const WHY = [
    { icon: "🏭", title: t("ph.why1"), text: t("ph.why1d"), bg: "pastel-purple" },
    { icon: "🎥", title: t("ph.why2"), text: t("ph.why2d"), bg: "pastel-blue" },
    { icon: "🚀", title: t("ph.why3"), text: t("ph.why3d"), bg: "pastel-green" },
    { icon: "💼", title: t("ph.why4"), text: t("ph.why4d"), bg: "pastel-pink" },
  ];
  const SUPPORT = [
    { icon: "💬", title: t("ph.sup1"), text: t("ph.sup1d") },
    { icon: "🔁", title: t("ph.sup2"), text: t("ph.sup2d") },
    { icon: "🧠", title: t("ph.sup3"), text: t("ph.sup3d") },
    { icon: "👤", title: t("ph.sup4"), text: t("ph.sup4d") },
    { icon: "📚", title: t("ph.sup5"), text: t("ph.sup5d") },
    { icon: "🎯", title: t("ph.sup6"), text: t("ph.sup6d") },
  ];
  const STATS = home.stats;
  const FAQ = [
    [t("ph.faqQ1"), t("ph.faqA1")], [t("ph.faqQ2"), t("ph.faqA2")],
    [t("ph.faqQ3"), t("ph.faqA3")], [t("ph.faqQ4"), t("ph.faqA4")],
  ];

  return (
    <div className="overflow-hidden">
      {/* ===== Hero ===== */}
      <section className="relative">
        <div className="blob grad-ph -left-32 -top-24 h-96 w-96" />
        <div className="blob grad-magenta right-0 top-10 h-80 w-80" />
        <div className="relative mx-auto grid max-w-[1600px] items-center gap-10 px-5 py-16 md:grid-cols-2 md:px-8 md:py-24">
          <div>
            <span className="pill grad-ph text-white shadow-md">⚡ {t("ph.badge")}</span>
            <h1 className="mt-5 text-4xl leading-[1.12] md:text-5xl">
              {t("ph.h1a")} <span className="hl hl-purple">{t("ph.h1b")}</span> {t("ph.h1c")}
            </h1>
            <p className="mt-5 max-w-md text-lg font-semibold text-[var(--muted)]">{t("ph.heroSub")}</p>
            <div className="mt-8 flex flex-wrap gap-3">
              <Link href="/register" className="btn grad-magenta text-base text-white shadow-lg">{t("ph.cta1")} →</Link>
              <Link href="/courses" className="btn-ghost text-base">{t("ph.cta2")}</Link>
            </div>
            <div className="mt-8 flex flex-wrap gap-2">
              <span className="pill grad-ph text-white">🎬 {t("ph.badge1")}</span>
              <span className="pill bg-[var(--foreground)] text-white">🏆 {t("ph.badge2")}</span>
              <span className="pill grad-magenta text-white">📦 {t("ph.badge3")}</span>
            </div>
          </div>

          {/* Hero visual — floating course card + decorative cards */}
          <div className="relative hidden md:block">
            <div className="grad-ph absolute left-4 top-2 h-44 w-64 rotate-[-6deg] rounded-3xl opacity-90 shadow-[0_30px_60px_-20px_rgba(160,32,240,0.5)]" />
            <div className="grad-magenta absolute right-0 top-28 h-40 w-60 rotate-[6deg] rounded-3xl opacity-90 shadow-[0_30px_60px_-20px_rgba(214,31,154,0.5)]" />
            <div className="card relative z-10 ml-8 mt-16 w-72 p-6">
              <div className="grad-magenta grid h-12 w-12 place-items-center rounded-2xl text-2xl">🎓</div>
              <p className="mt-4 text-lg font-extrabold">Full-Stack Track</p>
              <p className="text-sm font-semibold text-[var(--muted)]">42 lessons · 6 projects</p>
              <div className="mt-4 h-2 rounded-full bg-[var(--border)]"><div className="grad-ph h-full w-3/4 rounded-full" /></div>
              <p className="mt-2 text-xs font-bold" style={{ color: "#a020f0" }}>75% complete</p>
            </div>
          </div>
        </div>
      </section>

      {/* ===== Tech strip ===== */}
      <section className="border-y border-[var(--border)] bg-[var(--surface)]">
        <div className="mx-auto max-w-[1600px] px-5 py-6 md:px-8">
          <p className="text-center text-xs font-bold uppercase tracking-wider text-[var(--muted)]">{t("ph.techLabel")}</p>
          <div className="mt-4 flex flex-wrap items-center justify-center gap-x-8 gap-y-3">
            {TECHS.map((x) => <span key={x} className="text-lg font-extrabold text-[var(--muted)]">{x}</span>)}
          </div>
        </div>
      </section>

      {/* ===== Why learn with us ===== */}
      <section className="mx-auto max-w-[1600px] px-5 py-16 md:px-8 md:py-20">
        <div className="text-center">
          <h2 className="text-3xl md:text-4xl">{t("ph.whyTitle")}</h2>
          <p className="mx-auto mt-3 max-w-xl font-semibold text-[var(--muted)]">{t("ph.whySub")}</p>
        </div>
        <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {WHY.map((w) => (
            <div key={w.title} className={`rounded-[var(--radius)] p-7 ${w.bg} transition-transform hover:-translate-y-1`}>
              <div className="grid h-14 w-14 place-items-center rounded-2xl bg-white text-2xl shadow-sm">{w.icon}</div>
              <h3 className="mt-5 text-lg">{w.title}</h3>
              <p className="mt-2 text-sm font-semibold text-[var(--muted)]">{w.text}</p>
            </div>
          ))}
        </div>
      </section>

      {/* ===== Stats band ===== */}
      {settings.home_show_stats && (
      <section className="mx-auto max-w-[1600px] px-5 pb-4 md:px-8">
        <div className="grad-ph relative overflow-hidden rounded-[2rem] px-6 py-12 md:py-14">
          <div className="blob grad-magenta -right-16 -top-16 h-64 w-64" />
          <h2 className="relative text-center text-2xl text-white md:text-3xl">{t("ph.statsTitle")}</h2>
          <div className="relative mt-8 grid grid-cols-2 gap-6 text-center text-white md:grid-cols-4">
            {STATS.map((s, i) => (
              <div key={i}>
                <p className="text-4xl md:text-5xl">{s.v}</p>
                <p className="mt-1 text-sm font-bold text-white/85">{loc(s.l, lang)}</p>
              </div>
            ))}
          </div>
        </div>
      </section>
      )}

      {/* ===== Tech superpower grid ===== */}
      {settings.home_show_tech && (
      <section className="mx-auto max-w-[1600px] px-5 py-16 md:px-8 md:py-20">
        <div className="text-center">
          <h2 className="text-3xl md:text-4xl">{t("ph.techTitle")}</h2>
          <p className="mx-auto mt-3 max-w-xl font-semibold text-[var(--muted)]">{t("ph.techSub")}</p>
        </div>
        <div className="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
          {STACK.map((s) => (
            <div key={s.name} className={`rounded-[var(--radius)] border border-[var(--border)] p-6 ${s.bg}`}>
              <div className="grid h-12 w-12 place-items-center rounded-2xl bg-white text-xl shadow-sm">{s.icon}</div>
              <h3 className="mt-4 text-lg">{s.name}</h3>
              <p className="mt-1.5 text-sm font-semibold text-[var(--muted)]">{loc(s.d, lang)}</p>
            </div>
          ))}
        </div>
      </section>
      )}

      {/* ===== Featured courses ===== */}
      {courses.length > 0 && (
        <section className="bg-[var(--surface)]">
          <div className="mx-auto max-w-[1600px] px-5 py-16 md:px-8 md:py-20">
            <div className="flex flex-wrap items-end justify-between gap-3">
              <h2 className="text-3xl md:text-4xl">{t("home.featured")}</h2>
              <Link href="/courses" className="btn-ghost">{t("home.seeAll")}</Link>
            </div>
            <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {courses.map((c) => (
                <Link key={c.id} href={`/courses/${c.slug}`} className="card group flex flex-col overflow-hidden transition-transform hover:-translate-y-1">
                  <div className="flex h-40 items-center justify-center text-white" style={{ background: c.category ? `linear-gradient(135deg, ${c.category.color}, #a020f0)` : "linear-gradient(135deg, #2563ff, #a020f0)" }}>
                    <span className="text-5xl">🎓</span>
                  </div>
                  <div className="flex flex-1 flex-col p-6">
                    {c.category && <span className="text-xs font-bold uppercase" style={{ color: c.category.color }}>{c.category.name}</span>}
                    <h3 className="mt-1 text-lg">{c.title}</h3>
                    <p className="mt-1 flex-1 text-sm font-semibold text-[var(--muted)]">{c.subtitle}</p>
                    <div className="mt-4 flex items-center justify-between border-t border-[var(--border)] pt-3">
                      <span className="text-sm font-bold">{c.teacher?.name}</span>
                      <span className={`text-sm font-extrabold ${c.is_free ? "text-[var(--success)]" : ""}`} style={!c.is_free ? { color: "#a020f0" } : {}}>
                        {c.is_free ? t("free") : `$${Number(c.price).toFixed(2)}`}
                      </span>
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* ===== Success stories ===== */}
      {settings.home_show_stories && (
      <section className="mx-auto max-w-[1600px] px-5 py-16 md:px-8 md:py-20">
        <h2 className="text-center text-3xl md:text-4xl">{t("ph.storiesTitle")}</h2>
        <div className="mt-12 grid gap-6 md:grid-cols-3">
          {STORIES.map((s) => (
            <div key={s.name} className="card p-7">
              <div className="flex text-lg text-[var(--amber)]" style={{ color: "#f59e0b" }}>★★★★★</div>
              <p className="mt-4 font-semibold text-[var(--foreground)]">“{loc(s.text, lang)}”</p>
              <div className="mt-5 flex items-center gap-3">
                <div className={`grid h-11 w-11 place-items-center rounded-full text-sm font-extrabold text-white ${s.grad}`}>
                  {s.name.split(" ").map((n) => n[0]).join("")}
                </div>
                <div>
                  <p className="text-sm font-extrabold">{s.name}</p>
                  <p className="text-xs font-semibold text-[var(--muted)]">{loc(s.role, lang)}</p>
                </div>
              </div>
            </div>
          ))}
        </div>
      </section>
      )}

      {/* ===== Support ===== */}
      {settings.home_show_support && (
      <section className="bg-[var(--surface)]">
        <div className="mx-auto max-w-[1600px] px-5 py-16 md:px-8 md:py-20">
          <h2 className="text-center text-3xl md:text-4xl">{t("ph.supportTitle")}</h2>
          <div className="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            {SUPPORT.map((s) => (
              <div key={s.title} className="card flex items-start gap-4 p-6">
                <div className="grad-ph grid h-12 w-12 shrink-0 place-items-center rounded-2xl text-xl text-white">{s.icon}</div>
                <div>
                  <h3 className="text-lg">{s.title}</h3>
                  <p className="mt-1 text-sm font-semibold text-[var(--muted)]">{s.text}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
      )}

      {/* ===== FAQ ===== */}
      {settings.home_show_faq && (
      <section className="mx-auto max-w-3xl px-5 py-16 md:px-8 md:py-20">
        <h2 className="text-center text-3xl md:text-4xl">{t("ph.faqTitle")}</h2>
        <div className="mt-10 space-y-3">
          {FAQ.map(([q, a], i) => (
            <details key={i} className="faq" open={i === 0}>
              <summary>{q}</summary>
              <div>{a}</div>
            </details>
          ))}
        </div>
      </section>
      )}

      {/* ===== Final CTA ===== */}
      <section className="mx-auto max-w-[1600px] px-5 pb-20 md:px-8">
        <div className="grad-magenta relative overflow-hidden rounded-[2rem] px-8 py-16 text-center text-white md:py-20">
          <div className="blob grad-ph -left-16 -bottom-16 h-64 w-64" />
          <div className="relative">
            <h2 className="text-3xl md:text-4xl">{t("ph.finalTitle")}</h2>
            <p className="mx-auto mt-3 max-w-lg font-semibold text-white/85">{t("ph.finalSub")}</p>
            <div className="mt-8 flex flex-wrap justify-center gap-3">
              <Link href="/register" className="rounded-xl bg-white px-6 py-3 font-bold text-[#a020f0] transition-transform hover:-translate-y-0.5">{t("cta.createFree")}</Link>
              <Link href="/login" className="rounded-xl border border-white/40 px-6 py-3 font-bold text-white hover:bg-white/10">{t("cta.login")}</Link>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
