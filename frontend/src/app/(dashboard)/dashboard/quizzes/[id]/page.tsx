"use client";

import { useEffect, useState, use } from "react";
import Link from "next/link";
import { apiGet, apiPost } from "@/lib/api";
import { useLang } from "@/lib/i18n";

interface Question {
  id: number;
  question: string;
  options: string[];
  points: number;
}
interface Quiz {
  id: number;
  title: string;
  description: string | null;
  pass_mark: number;
  questions: Question[];
}
interface Result {
  score: number;
  passed: boolean;
  pass_mark: number;
  earned: number;
  total: number;
}

export default function QuizTakePage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = use(params);
  const { t } = useLang();
  const [quiz, setQuiz] = useState<Quiz | null>(null);
  const [answers, setAnswers] = useState<Record<number, number>>({});
  const [result, setResult] = useState<Result | null>(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    apiGet<{ quiz: Quiz }>(`/quizzes/${id}/take`)
      .then((d) => setQuiz(d.quiz))
      .catch(() => setQuiz(null))
      .finally(() => setLoading(false));
  }, [id]);

  async function submit() {
    if (!quiz) return;
    setSubmitting(true);
    try {
      const { result } = await apiPost<{ result: Result }>(`/quizzes/${quiz.id}/submit`, { answers });
      setResult(result);
      window.scrollTo({ top: 0, behavior: "smooth" });
    } finally {
      setSubmitting(false);
    }
  }

  if (loading) return <p className="text-[var(--muted)]">{t("common.loading")}</p>;
  if (!quiz) return <p className="text-[var(--muted)]">{t("quiz.notFound")}</p>;

  const answeredCount = Object.keys(answers).length;

  if (result) {
    return (
      <div className="grid min-h-[60vh] place-items-center">
        <div className="card max-w-md p-10 text-center">
          <div className="text-6xl">{result.passed ? "🏆" : "💪"}</div>
          <h1 className="mt-4 text-3xl">{result.score}%</h1>
          <p className={`mt-1 text-lg font-bold ${result.passed ? "text-[var(--success)]" : "text-[var(--warning)]"}`}>
            {result.passed ? t("quiz.passed") : `${t("quiz.notPassed")} (${t("quiz.need")} ${result.pass_mark}%)`}
          </p>
          <p className="mt-2 text-sm text-[var(--muted)]">{t("quiz.gotPoints").replace("{e}", String(result.earned)).replace("{t}", String(result.total))}</p>
          <div className="mt-6 flex justify-center gap-2">
            <button onClick={() => { setResult(null); setAnswers({}); }} className="btn-primary">{t("quiz.retake")}</button>
            <Link href="/dashboard/quizzes" className="btn-ghost">{t("quiz.allQuizzes")}</Link>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-2xl space-y-6">
      <div>
        <Link href="/dashboard/quizzes" className="text-sm font-bold text-[var(--primary)] hover:underline">← {t("quiz.title")}</Link>
        <h1 className="mt-1 text-3xl">{quiz.title}</h1>
        {quiz.description && <p className="mt-1 text-[var(--muted)]">{quiz.description}</p>}
        <p className="mt-1 text-sm text-[var(--muted)]">{t("quiz.passMark")}: {quiz.pass_mark}% · {quiz.questions.length} {t("quiz.questions")}</p>
      </div>

      {quiz.questions.map((q, qi) => (
        <div key={q.id} className="card p-6">
          <p className="font-bold">{qi + 1}. {q.question}</p>
          <div className="mt-4 space-y-2">
            {q.options.map((opt, oi) => (
              <label
                key={oi}
                className={`flex cursor-pointer items-center gap-3 rounded-[var(--radius)] border p-3 transition-colors ${
                  answers[q.id] === oi ? "border-[var(--primary)] bg-[var(--primary)]/5" : "border-[var(--border)] hover:border-[var(--primary)]"
                }`}
              >
                <input
                  type="radio"
                  name={`q-${q.id}`}
                  checked={answers[q.id] === oi}
                  onChange={() => setAnswers({ ...answers, [q.id]: oi })}
                  className="h-4 w-4 accent-[var(--primary)]"
                />
                <span className="font-semibold text-[var(--foreground)]">{opt}</span>
              </label>
            ))}
          </div>
        </div>
      ))}

      <div className="sticky bottom-4 flex items-center justify-between rounded-[var(--radius)] border border-[var(--border)] bg-[var(--surface)] p-4 shadow-lg">
        <span className="text-sm font-bold text-[var(--muted)]">{answeredCount}/{quiz.questions.length} {t("quiz.answered")}</span>
        <button
          onClick={submit}
          disabled={submitting || answeredCount < quiz.questions.length}
          className="btn-primary disabled:opacity-60"
        >
          {submitting ? t("quiz.grading") : t("quiz.submit")}
        </button>
      </div>
    </div>
  );
}
