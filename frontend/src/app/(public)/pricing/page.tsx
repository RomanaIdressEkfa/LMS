"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { apiGet } from "@/lib/api";
import { useContent, loc } from "@/lib/content";
import { useLang } from "@/lib/i18n";

interface Plan {
  id: number;
  name: string;
  description: string | null;
  price: string;
  interval: string;
  module_keys: string[];
}

const MODULE_LABELS: Record<string, string> = {
  courses: "Courses", roles: "Roles & Permissions", quizzes: "Quizzes",
  certificates: "Certificates", live_classes: "Live Classes", store: "Store",
  wallet: "Wallet", forums: "Forums", events: "Events", jobs: "Jobs Board",
  blog: "Blog", affiliates: "Affiliates",
};

const CARD_GRAD = ["grad-primary", "grad-purple", "grad-sunset"];

export default function PricingPage() {
  const { pricing } = useContent();
  const { lang } = useLang();
  const [plans, setPlans] = useState<Plan[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    apiGet<{ plans: Plan[] }>("/pricing").then((d) => setPlans(d.plans)).finally(() => setLoading(false));
  }, []);

  return (
    <div>
      <section className="grad-hero border-b border-[var(--border)]">
        <div className="mx-auto max-w-[1600px] px-5 py-14 text-center md:px-8">
          <h1 className="text-4xl md:text-5xl">{loc(pricing.hero.titleA, lang)} <span className="gradient-text">{loc(pricing.hero.titleHl, lang)}</span></h1>
          <p className="mx-auto mt-3 max-w-xl text-lg font-semibold text-[var(--muted)]">{loc(pricing.hero.subtitle, lang)}</p>
        </div>
      </section>

      <div className="mx-auto max-w-6xl px-5 py-14 md:px-8">
        {loading ? (
          <p className="text-center text-[var(--muted)]">Loading plans…</p>
        ) : (
          <div className="grid gap-6 md:grid-cols-3">
            {plans.map((p, i) => {
              const featured = i === 1;
              return (
                <div key={p.id} className={`card relative flex flex-col p-8 ${featured ? "ring-2 ring-[var(--primary)]" : ""}`}>
                  {featured && <span className="pill grad-primary absolute -top-3 left-1/2 -translate-x-1/2 text-white">Most popular</span>}
                  <div className={`grid h-12 w-12 place-items-center rounded-2xl text-xl text-white ${CARD_GRAD[i % 3]}`}>★</div>
                  <h3 className="mt-5 text-2xl">{p.name}</h3>
                  <p className="mt-1 text-sm font-semibold text-[var(--muted)]">{p.description}</p>
                  <p className="mt-5 text-4xl">
                    ${Number(p.price).toFixed(0)}
                    <span className="text-base font-bold text-[var(--muted)]">/{p.interval === "monthly" ? "mo" : p.interval === "yearly" ? "yr" : "once"}</span>
                  </p>
                  <Link href="/register" className={`mt-6 ${featured ? "btn-primary" : "btn-ghost"} w-full`}>Get started</Link>
                  <ul className="mt-6 space-y-2.5 border-t border-[var(--border)] pt-6">
                    {p.module_keys.map((k) => (
                      <li key={k} className="flex items-center gap-2.5 text-sm font-semibold">
                        <span className="grid h-5 w-5 place-items-center rounded-full bg-[var(--success)]/15 text-xs text-[var(--success)]">✓</span>
                        {MODULE_LABELS[k] ?? k}
                      </li>
                    ))}
                  </ul>
                </div>
              );
            })}
          </div>
        )}
        <p className="mt-10 text-center text-sm font-semibold text-[var(--muted)]">
          {loc(pricing.footnote, lang)}{" "}
          <Link href="/contact" className="font-bold text-[var(--primary)] hover:underline">Contact us</Link>.
        </p>
      </div>
    </div>
  );
}
