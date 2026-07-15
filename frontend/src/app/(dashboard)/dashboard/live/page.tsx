"use client";

import { useEffect, useState } from "react";
import { apiGet, apiPost, ApiError } from "@/lib/api";
import { useAuth } from "@/lib/auth";
import { useLang } from "@/lib/i18n";

interface LiveSession {
  id: number;
  title: string;
  description: string | null;
  provider: string;
  scheduled_at: string;
  duration_minutes: number;
  status: "scheduled" | "live" | "ended";
  teacher?: { id: number; name: string };
  course?: { id: number; title: string } | null;
  is_host?: boolean;
  meeting_url: string | null;
}

const STATUS: Record<string, string> = {
  live: "bg-[var(--danger)] text-white",
  scheduled: "bg-[var(--warning)]/15 text-[var(--warning)]",
  ended: "bg-[var(--muted)]/15 text-[var(--muted)]",
};

export default function LivePage() {
  const { can } = useAuth();
  const { t } = useLang();
  const [sessions, setSessions] = useState<LiveSession[]>([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState({ title: "", description: "", provider: "custom", meeting_url: "", scheduled_at: "", duration_minutes: 60 });
  const [error, setError] = useState<string | null>(null);

  const canHost = can("live.host");

  async function load() {
    const d = await apiGet<{ sessions: LiveSession[] }>("/live");
    setSessions(d.sessions);
    setLoading(false);
  }

  useEffect(() => {
    load();
  }, []);

  async function schedule(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    try {
      await apiPost("/live", form);
      setForm({ title: "", description: "", provider: "custom", meeting_url: "", scheduled_at: "", duration_minutes: 60 });
      setShowForm(false);
      await load();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Could not schedule.");
    }
  }

  async function setStatus(s: LiveSession, status: string) {
    await apiPost(`/live/${s.id}/status`, { status });
    await load();
  }

  const fmt = (iso: string) =>
    new Date(iso).toLocaleString(undefined, { dateStyle: "medium", timeStyle: "short" });

  if (loading) return <p className="text-[var(--muted)]">{t("common.loading")}</p>;

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-3xl">{t("live.title")}</h1>
          <p className="mt-1 text-[var(--muted)]">{t("live.sub")}</p>
        </div>
        {canHost && (
          <button onClick={() => setShowForm((s) => !s)} className="btn-primary">
            {showForm ? t("live.close") : t("live.schedule")}
          </button>
        )}
      </div>

      {showForm && (
        <form onSubmit={schedule} className="card space-y-4 p-6">
          <h2 className="text-xl">Schedule a live session</h2>
          <input className="input" placeholder="Session title" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} required />
          <textarea className="input min-h-20" placeholder="Description" value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} />
          <div className="grid gap-4 sm:grid-cols-2">
            <div>
              <label className="label">When</label>
              <input type="datetime-local" className="input" value={form.scheduled_at} onChange={(e) => setForm({ ...form, scheduled_at: e.target.value })} required />
            </div>
            <div>
              <label className="label">Duration (min)</label>
              <input type="number" min="5" className="input" value={form.duration_minutes} onChange={(e) => setForm({ ...form, duration_minutes: Number(e.target.value) })} />
            </div>
          </div>
          <input className="input" placeholder="Meeting URL (Zoom/Meet/custom)" value={form.meeting_url} onChange={(e) => setForm({ ...form, meeting_url: e.target.value })} />
          {error && <p className="text-sm font-bold text-[var(--danger)]">{error}</p>}
          <button type="submit" className="btn-primary">Schedule</button>
        </form>
      )}

      {sessions.length === 0 ? (
        <div className="card grid place-items-center p-12 text-center">
          <span className="text-4xl">🎥</span>
          <p className="mt-3 font-bold">{t("live.empty")}</p>
        </div>
      ) : (
        <div className="grid gap-4 sm:grid-cols-2">
          {sessions.map((s) => (
            <div key={s.id} className="card flex flex-col p-5">
              <div className="flex items-start justify-between gap-2">
                <h3 className="text-lg leading-tight">{s.title}</h3>
                <span className={`shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold uppercase ${STATUS[s.status]}`}>
                  {s.status === "live" ? `🔴 ${t("live.badgeLive")}` : s.status === "scheduled" ? t("live.scheduled") : s.status}
                </span>
              </div>
              {s.description && <p className="mt-2 flex-1 text-sm text-[var(--muted)]">{s.description}</p>}
              <div className="mt-3 space-y-1 text-xs text-[var(--muted)]">
                <p>👨‍🏫 {s.teacher?.name}</p>
                <p>🗓️ {fmt(s.scheduled_at)} · {s.duration_minutes} min</p>
                {s.course && <p>📚 {s.course.title}</p>}
              </div>

              <div className="mt-4 flex flex-wrap gap-2">
                {s.status === "live" && s.meeting_url && (
                  <a href={s.meeting_url} target="_blank" rel="noopener noreferrer" className="btn-primary flex-1 text-center">
                    {t("live.join")} →
                  </a>
                )}
                {s.status === "scheduled" && !s.is_host && (
                  <span className="text-sm font-bold text-[var(--muted)]">{t("live.opensWhenLive")}</span>
                )}
                {s.is_host && (
                  <div className="flex gap-2">
                    {s.status !== "live" && <button onClick={() => setStatus(s, "live")} className="btn-primary">{t("live.goLive")}</button>}
                    {s.status === "live" && <button onClick={() => setStatus(s, "ended")} className="btn-ghost">{t("live.end")}</button>}
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
