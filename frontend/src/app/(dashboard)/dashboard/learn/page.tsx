"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { apiGet } from "@/lib/api";
import type { Enrollment } from "@/lib/types";

export default function MyLearningPage() {
  const [enrollments, setEnrollments] = useState<Enrollment[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    apiGet<{ enrollments: Enrollment[] }>("/my/enrollments")
      .then((d) => setEnrollments(d.enrollments))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <p className="text-[var(--muted)]">Loading your courses…</p>;

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl">My Learning</h1>
        <p className="mt-1 text-[var(--muted)]">Pick up where you left off.</p>
      </div>

      {enrollments.length === 0 ? (
        <div className="card grid place-items-center p-12 text-center">
          <span className="text-4xl">📚</span>
          <p className="mt-3 font-bold">You haven&apos;t enrolled in any courses yet.</p>
          <Link href="/dashboard/courses" className="btn-primary mt-4">Browse Catalog</Link>
        </div>
      ) : (
        <div className="grid gap-4 sm:grid-cols-2">
          {enrollments.map((e) => (
            <Link key={e.id} href={`/dashboard/learn/${e.course.slug}`} className="card p-5 transition-transform hover:-translate-y-1">
              <div className="flex items-center justify-between">
                <h3 className="text-lg">{e.course.title}</h3>
                <span className="text-sm font-bold text-[var(--primary)]">{e.progress}%</span>
              </div>
              <p className="mt-1 text-sm text-[var(--muted)]">{e.course.teacher?.name}</p>
              <div className="mt-4 h-2 overflow-hidden rounded-full bg-[var(--border)]">
                <div
                  className="h-full rounded-full bg-[var(--primary)] transition-all"
                  style={{ width: `${e.progress}%` }}
                />
              </div>
              <p className="mt-3 text-sm font-bold text-[var(--primary)]">
                {e.progress >= 100 ? "✅ Completed" : "Continue →"}
              </p>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
