"use client";

import { useEffect, useState } from "react";
import { apiGet, apiPost } from "@/lib/api";
import { useAuth } from "@/lib/auth";
import type { Module } from "@/lib/types";

export default function ModulesPage() {
  const { can } = useAuth();
  const [modules, setModules] = useState<Module[]>([]);
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState<number | null>(null);

  const canManage = can("modules.manage");

  useEffect(() => {
    apiGet<{ modules: Module[] }>("/modules")
      .then((d) => setModules(d.modules))
      .finally(() => setLoading(false));
  }, []);

  async function toggle(m: Module) {
    if (!canManage || m.is_core) return;
    setBusy(m.id);
    try {
      const { module } = await apiPost<{ module: Module }>(`/modules/${m.id}/toggle`);
      setModules((prev) => prev.map((x) => (x.id === module.id ? module : x)));
    } finally {
      setBusy(null);
    }
  }

  if (loading) return <p className="text-[var(--muted)]">Loading modules…</p>;

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl">Modules &amp; Addons</h1>
        <p className="mt-1 text-[var(--muted)]">
          Turn features on or off. Disabled modules disappear from menus, routes
          and APIs across the whole platform. Core modules can&apos;t be disabled.
        </p>
      </div>

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        {modules.map((m) => (
          <div key={m.id} className="card flex flex-col p-5">
            <div className="flex items-start justify-between">
              <div className="grid h-11 w-11 place-items-center rounded-[var(--radius)] bg-[var(--primary)]/10 text-xl">
                🧩
              </div>
              {m.is_core ? (
                <span className="rounded-full bg-[var(--muted)]/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-[var(--muted)]">
                  Core
                </span>
              ) : (
                <button
                  disabled={!canManage || busy === m.id}
                  onClick={() => toggle(m)}
                  className={`relative h-7 w-12 rounded-full transition-colors disabled:opacity-50 ${
                    m.enabled ? "bg-[var(--success)]" : "bg-[var(--muted)]/40"
                  }`}
                  aria-label={`Toggle ${m.name}`}
                >
                  <span
                    className={`absolute top-1 h-5 w-5 rounded-full bg-white transition-all ${
                      m.enabled ? "left-6" : "left-1"
                    }`}
                  />
                </button>
              )}
            </div>

            <h3 className="mt-4 text-lg">{m.name}</h3>
            <p className="mt-1 flex-1 text-sm text-[var(--muted)]">{m.description}</p>
            <div className="mt-4 flex items-center justify-between">
              <span className="text-xs font-bold uppercase tracking-wide text-[var(--muted)]">
                {m.category}
              </span>
              <span
                className={`text-xs font-bold ${
                  m.enabled ? "text-[var(--success)]" : "text-[var(--muted)]"
                }`}
              >
                {m.enabled ? "Enabled" : "Disabled"}
              </span>
            </div>
          </div>
        ))}
      </div>

      {!canManage && (
        <p className="text-sm font-bold text-[var(--warning)]">
          You can view modules but need the <code>modules.manage</code> permission to change them.
        </p>
      )}
    </div>
  );
}
