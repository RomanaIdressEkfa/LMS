"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { apiGet, apiPost } from "@/lib/api";
import { useAuth } from "@/lib/auth";

interface Quiz {
  id: number;
  title: string;
  description: string | null;
  pass_mark: number;
  questions_count: number;
  attempts_count?: number;
  published?: boolean;
  best_score?: number | null;
  course?: { id: number; title: string } | null;
}

export default function QuizzesPage() {
  const { can } = useAuth();
  const router = useRouter();
  const [available, setAvailable] = useState<Quiz[]>([]);
  const [mine, setMine] = useState<Quiz[]>([]);
  const [loading, setLoading] = useState(true);

  const canCreate = can("quizzes.create");

  async function load() {
    const reqs: Promise<void>[] = [
      apiGet<{ quizzes: Quiz[] }>("/quizzes").then((d) => setAvailable(d.quizzes)),
    ];
    if (canCreate) {
      reqs.push(apiGet<{ quizzes: Quiz[] }>("/my/quizzes").then((d) => setMine(d.quizzes)));
    }
    await Promise.all(reqs);
    setLoading(false);
  }

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  async function createQuiz() {
    const { quiz } = await apiPost<{ quiz: Quiz }>("/quizzes", { title: "Untitled Quiz", pass_mark: 60 });
    router.push(`/dashboard/quizzes/${quiz.id}/edit`);
  }

  if (loading) return <p className="text-[var(--muted)]">Loading quizzes…</p>;

  return (
    <div className="space-y-8">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-3xl">Quizzes</h1>
          <p className="mt-1 text-[var(--muted)]">Test your knowledge and earn passing scores.</p>
        </div>
        {canCreate && <button onClick={createQuiz} className="btn-primary">+ New Quiz</button>}
      </div>

      {/* Teacher's own quizzes */}
      {canCreate && (
        <section>
          <h2 className="text-xl">My quizzes</h2>
          {mine.length === 0 ? (
            <p className="mt-2 text-sm text-[var(--muted)]">You haven&apos;t created any quizzes yet.</p>
          ) : (
            <div className="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
              {mine.map((q) => (
                <Link key={q.id} href={`/dashboard/quizzes/${q.id}/edit`} className="card p-5 transition-transform hover:-translate-y-1">
                  <div className="flex items-center justify-between">
                    <span className={`rounded-full px-2.5 py-1 text-[11px] font-bold uppercase ${q.published ? "bg-[var(--success)]/15 text-[var(--success)]" : "bg-[var(--warning)]/15 text-[var(--warning)]"}`}>
                      {q.published ? "published" : "draft"}
                    </span>
                    <span className="text-xs text-[var(--muted)]">{q.attempts_count ?? 0} attempts</span>
                  </div>
                  <h3 className="mt-3 text-lg">{q.title}</h3>
                  <p className="mt-1 text-sm text-[var(--muted)]">{q.questions_count} questions · pass {q.pass_mark}%</p>
                </Link>
              ))}
            </div>
          )}
        </section>
      )}

      {/* Available to take */}
      <section>
        <h2 className="text-xl">Available quizzes</h2>
        {available.length === 0 ? (
          <div className="card mt-3 grid place-items-center p-12 text-center">
            <span className="text-4xl">📝</span>
            <p className="mt-3 font-bold">No published quizzes yet.</p>
          </div>
        ) : (
          <div className="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            {available.map((q) => (
              <div key={q.id} className="card flex flex-col p-5">
                <h3 className="text-lg">{q.title}</h3>
                {q.description && <p className="mt-1 flex-1 text-sm text-[var(--muted)]">{q.description}</p>}
                <div className="mt-3 flex items-center justify-between text-xs text-[var(--muted)]">
                  <span>{q.questions_count} questions · pass {q.pass_mark}%</span>
                  {q.best_score != null && (
                    <span className={`font-bold ${q.best_score >= q.pass_mark ? "text-[var(--success)]" : "text-[var(--warning)]"}`}>
                      Best: {q.best_score}%
                    </span>
                  )}
                </div>
                <Link href={`/dashboard/quizzes/${q.id}`} className="btn-primary mt-4 text-center">
                  {q.best_score != null ? "Retake" : "Start quiz"}
                </Link>
              </div>
            ))}
          </div>
        )}
      </section>
    </div>
  );
}
