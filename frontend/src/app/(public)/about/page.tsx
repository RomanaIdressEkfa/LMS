"use client";

import Link from "next/link";
import { useContent, loc } from "@/lib/content";
import { useLang } from "@/lib/i18n";

export default function AboutPage() {
  const { about } = useContent();
  const { lang } = useLang();
  const { hero, values: VALUES, steps: STEPS, stats: STATS } = about;

  return (
    <div>
      <section className="grad-hero border-b border-[var(--border)]">
        <div className="mx-auto max-w-4xl px-5 py-16 text-center md:px-8 md:py-20">
          <h1 className="text-4xl md:text-5xl">{loc(hero.titleA, lang)} <span className="gradient-text">{loc(hero.titleHl, lang)}</span></h1>
          <p className="mx-auto mt-4 max-w-2xl text-lg font-semibold text-[var(--muted)]">
            {loc(hero.subtitle, lang)}
          </p>
          <div className="mt-8 flex flex-wrap justify-center gap-3">
            <Link href="/register" className="btn-primary">Get started free</Link>
            <Link href="/courses" className="btn-ghost">Browse courses</Link>
          </div>
        </div>
      </section>

      {/* Values */}
      <section className="mx-auto max-w-[1600px] px-5 py-16 md:px-8">
        <h2 className="text-center text-3xl md:text-4xl">What we stand for</h2>
        <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {VALUES.map((v, i) => (
            <div key={i} className="card p-7">
              <div className={`grid h-14 w-14 place-items-center rounded-2xl text-2xl text-white ${v.grad}`}>{v.icon}</div>
              <h3 className="mt-5 text-xl">{loc(v.title, lang)}</h3>
              <p className="mt-2 font-semibold text-[var(--muted)]">{loc(v.text, lang)}</p>
            </div>
          ))}
        </div>
      </section>

      {/* How it works */}
      <section className="bg-[var(--surface)]">
        <div className="mx-auto max-w-[1600px] px-5 py-16 md:px-8">
          <h2 className="text-center text-3xl md:text-4xl">How it works</h2>
          <div className="mt-10 grid gap-6 md:grid-cols-3">
            {STEPS.map((s, i) => (
              <div key={i} className="card p-8">
                <div className="grad-primary grid h-12 w-12 place-items-center rounded-2xl text-xl font-extrabold text-white">{s.n}</div>
                <h3 className="mt-5 text-xl">{loc(s.title, lang)}</h3>
                <p className="mt-2 font-semibold text-[var(--muted)]">{loc(s.text, lang)}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Stats band */}
      <section className="mx-auto max-w-[1600px] px-5 py-16 md:px-8">
        <div className="grad-primary grid grid-cols-2 gap-6 rounded-[2rem] p-10 text-center text-white md:grid-cols-4">
          {STATS.map((s, i) => (
            <div key={i}>
              <p className="text-3xl md:text-4xl">{s.v}</p>
              <p className="mt-1 text-sm font-bold text-white/80">{loc(s.l, lang)}</p>
            </div>
          ))}
        </div>
      </section>
    </div>
  );
}
