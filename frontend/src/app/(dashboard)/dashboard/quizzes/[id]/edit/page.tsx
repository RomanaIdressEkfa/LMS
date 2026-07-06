"use client";

import { useEffect, useState, use } from "react";
import Link from "next/link";
import { apiGet, apiPost, apiPut, apiDelete, ApiError } from "@/lib/api";

interface Question {
  id: number;
  question: string;
  options: string[];
  correct_index: number;
  points: number;
}
interface Quiz {
  id: number;
  title: string;
  description: string | null;
  pass_mark: number;
  time_limit_minutes: number;
  published: boolean;
  questions: Question[];
}

const EMPTY_Q = { question: "", options: ["", ""], correct_index: 0, points: 1 };

export default function QuizBuilderPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = use(params);
  const [quiz, setQuiz] = useState<Quiz | null>(null);
  const [loading, setLoading] = useState(true);
  const [settings, setSettings] = useState({ title: "", description: "", pass_mark: 60 });
  const [msg, setMsg] = useState<string | null>(null);
  const [qForm, setQForm] = useState<typeof EMPTY_Q>(EMPTY_Q);
  const [editingQ, setEditingQ] = useState<number | null>(null);

  async function load() {
    const { quiz } = await apiGet<{ quiz: Quiz }>(`/quizzes/${id}/manage`);
    setQuiz(quiz);
    setSettings({ title: quiz.title, description: quiz.description ?? "", pass_mark: quiz.pass_mark });
    setLoading(false);
  }

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id]);

  async function saveSettings(e: React.FormEvent) {
    e.preventDefault();
    if (!quiz) return;
    setMsg(null);
    try {
      await apiPut(`/quizzes/${quiz.id}`, { ...settings, published: quiz.published });
      setMsg("Saved ✔");
      await load();
    } catch (err) {
      setMsg(err instanceof ApiError ? err.message : "Save failed");
    }
  }

  async function togglePublish() {
    if (!quiz) return;
    await apiPut(`/quizzes/${quiz.id}`, { ...settings, published: !quiz.published });
    await load();
  }

  async function submitQuestion(e: React.FormEvent) {
    e.preventDefault();
    if (!quiz) return;
    const payload = { ...qForm, options: qForm.options.filter((o) => o.trim() !== "") };
    if (payload.options.length < 2) { setMsg("Add at least 2 options"); return; }
    if (qForm.correct_index >= payload.options.length) payload.correct_index = 0;
    if (editingQ) await apiPut(`/quizzes/${quiz.id}/questions/${editingQ}`, payload);
    else await apiPost(`/quizzes/${quiz.id}/questions`, payload);
    setQForm(EMPTY_Q);
    setEditingQ(null);
    await load();
  }

  function editQuestion(q: Question) {
    setEditingQ(q.id);
    setQForm({ question: q.question, options: [...q.options], correct_index: q.correct_index, points: q.points });
  }

  async function deleteQuestion(q: Question) {
    if (!quiz || !confirm("Delete this question?")) return;
    await apiDelete(`/quizzes/${quiz.id}/questions/${q.id}`);
    await load();
  }

  const setOption = (i: number, val: string) =>
    setQForm((f) => ({ ...f, options: f.options.map((o, idx) => (idx === i ? val : o)) }));

  if (loading) return <p className="text-[var(--muted)]">Loading builder…</p>;
  if (!quiz) return <p className="text-[var(--muted)]">Quiz not found.</p>;

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <Link href="/dashboard/quizzes" className="text-sm font-bold text-[var(--primary)] hover:underline">← Quizzes</Link>
          <h1 className="mt-1 text-3xl">Quiz Builder</h1>
        </div>
        <button onClick={togglePublish} className={quiz.published ? "btn-ghost" : "btn-primary"}>
          {quiz.published ? "Unpublish" : "Publish"}
        </button>
      </div>

      <div className="grid gap-6 lg:grid-cols-2">
        {/* Settings */}
        <form onSubmit={saveSettings} className="card space-y-4 p-6">
          <div className="flex items-center justify-between">
            <h2 className="text-xl">Settings</h2>
            <span className={`rounded-full px-2.5 py-1 text-[11px] font-bold uppercase ${quiz.published ? "bg-[var(--success)]/15 text-[var(--success)]" : "bg-[var(--warning)]/15 text-[var(--warning)]"}`}>
              {quiz.published ? "published" : "draft"}
            </span>
          </div>
          <div>
            <label className="label">Title</label>
            <input className="input" value={settings.title} onChange={(e) => setSettings({ ...settings, title: e.target.value })} required />
          </div>
          <div>
            <label className="label">Description</label>
            <textarea className="input min-h-20" value={settings.description} onChange={(e) => setSettings({ ...settings, description: e.target.value })} />
          </div>
          <div>
            <label className="label">Pass mark (%)</label>
            <input type="number" min="0" max="100" className="input max-w-[120px]" value={settings.pass_mark} onChange={(e) => setSettings({ ...settings, pass_mark: Number(e.target.value) })} />
          </div>
          <div className="flex items-center gap-3">
            <button type="submit" className="btn-primary">Save settings</button>
            {msg && <span className="text-sm font-bold text-[var(--success)]">{msg}</span>}
          </div>
        </form>

        {/* Questions */}
        <div className="space-y-6">
          <div className="card p-6">
            <h2 className="text-xl">Questions ({quiz.questions.length})</h2>
            <div className="mt-4 space-y-3">
              {quiz.questions.length === 0 ? (
                <p className="text-sm text-[var(--muted)]">No questions yet — add one below.</p>
              ) : (
                quiz.questions.map((q, i) => (
                  <div key={q.id} className="rounded-[var(--radius)] border border-[var(--border)] p-4">
                    <div className="flex items-start justify-between gap-2">
                      <p className="font-bold">{i + 1}. {q.question}</p>
                      <div className="flex shrink-0 gap-2">
                        <button onClick={() => editQuestion(q)} className="text-sm font-bold text-[var(--primary)] hover:underline">Edit</button>
                        <button onClick={() => deleteQuestion(q)} className="text-sm font-bold text-[var(--danger)] hover:underline">Delete</button>
                      </div>
                    </div>
                    <ul className="mt-2 space-y-1 text-sm">
                      {q.options.map((opt, oi) => (
                        <li key={oi} className={oi === q.correct_index ? "font-bold text-[var(--success)]" : "text-[var(--muted)]"}>
                          {oi === q.correct_index ? "✓ " : "• "}{opt}
                        </li>
                      ))}
                    </ul>
                  </div>
                ))
              )}
            </div>
          </div>

          <form onSubmit={submitQuestion} className="card space-y-3 p-6">
            <h3 className="text-lg">{editingQ ? "Edit question" : "Add question"}</h3>
            <textarea className="input min-h-16" placeholder="Question text" value={qForm.question} onChange={(e) => setQForm({ ...qForm, question: e.target.value })} required />
            <p className="label">Options (select the correct one)</p>
            {qForm.options.map((opt, i) => (
              <div key={i} className="flex items-center gap-2">
                <input type="radio" name="correct" checked={qForm.correct_index === i} onChange={() => setQForm({ ...qForm, correct_index: i })} className="h-4 w-4 accent-[var(--success)]" />
                <input className="input" placeholder={`Option ${i + 1}`} value={opt} onChange={(e) => setOption(i, e.target.value)} />
                {qForm.options.length > 2 && (
                  <button type="button" onClick={() => setQForm({ ...qForm, options: qForm.options.filter((_, idx) => idx !== i) })} className="text-[var(--danger)]">×</button>
                )}
              </div>
            ))}
            <button type="button" onClick={() => setQForm({ ...qForm, options: [...qForm.options, ""] })} className="text-sm font-bold text-[var(--primary)] hover:underline">
              + Add option
            </button>
            <div className="flex gap-2 pt-2">
              <button type="submit" className="btn-primary">{editingQ ? "Update question" : "Add question"}</button>
              {editingQ && <button type="button" onClick={() => { setEditingQ(null); setQForm(EMPTY_Q); }} className="btn-ghost">Cancel</button>}
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
