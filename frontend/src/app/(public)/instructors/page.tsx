"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { apiGet } from "@/lib/api";

interface Instructor {
  id: number;
  name: string;
  avatar: string | null;
  bio: string | null;
  courses_count: number;
}

const GRADS = ["grad-primary", "grad-purple", "grad-sunset", "grad-teal"];

export default function InstructorsPage() {
  const [instructors, setInstructors] = useState<Instructor[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    apiGet<{ instructors: Instructor[] }>("/instructors")
      .then((d) => setInstructors(d.instructors))
      .finally(() => setLoading(false));
  }, []);

  return (
    <div>
      <section className="grad-hero border-b border-[var(--border)]">
        <div className="mx-auto max-w-7xl px-5 py-14 md:px-8">
          <h1 className="text-4xl md:text-5xl">Meet our <span className="gradient-text">instructors</span></h1>
          <p className="mt-3 max-w-xl text-lg font-semibold text-[var(--muted)]">Learn from experts who create and teach real courses.</p>
        </div>
      </section>

      <div className="mx-auto max-w-7xl px-5 py-12 md:px-8">
        {loading ? (
          <p className="text-[var(--muted)]">Loading instructors…</p>
        ) : instructors.length === 0 ? (
          <div className="card grid place-items-center p-12 text-center">
            <span className="text-4xl">🧑‍🏫</span>
            <p className="mt-3 font-bold">No instructors yet.</p>
          </div>
        ) : (
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {instructors.map((t, i) => (
              <div key={t.id} className="card p-6 text-center transition-transform hover:-translate-y-1">
                <div className={`mx-auto grid h-20 w-20 place-items-center rounded-full text-2xl font-extrabold text-white ${GRADS[i % GRADS.length]}`}>
                  {t.name.split(" ").map((n) => n[0]).slice(0, 2).join("")}
                </div>
                <h3 className="mt-4 text-lg">{t.name}</h3>
                <p className="mt-1 text-sm font-semibold text-[var(--muted)]">{t.bio ?? "Instructor"}</p>
                <span className="pill mt-4 bg-[var(--primary-soft)] text-[var(--primary)]">{t.courses_count} courses</span>
              </div>
            ))}
          </div>
        )}

        <div className="mt-12 card grad-purple flex flex-col items-center gap-4 p-10 text-center text-white sm:flex-row sm:justify-between sm:text-left">
          <div>
            <h2 className="text-2xl">Want to teach on LMS?</h2>
            <p className="mt-1 font-semibold text-white/85">Create and sell your own courses to students worldwide.</p>
          </div>
          <Link href="/register" className="rounded-xl bg-white px-6 py-3 font-bold text-[var(--purple)]">Become an instructor</Link>
        </div>
      </div>
    </div>
  );
}
