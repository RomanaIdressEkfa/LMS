"use client";

import { useAuth } from "@/lib/auth";
import { useLang } from "@/lib/i18n";

export default function DashboardPage() {
  const { user } = useAuth();
  const { t } = useLang();

  const STATS = [
    { label: t("dash.stat.courses"), value: "11", icon: "🎓" },
    { label: t("dash.stat.certs"), value: "2", icon: "🏅" },
    { label: t("dash.stat.live"), value: "3", icon: "🎥" },
    { label: t("dash.stat.wallet"), value: "$591", icon: "💰" },
  ];

  return (
    <div className="space-y-8">
      {/* Hero greeting */}
      <div className="card overflow-hidden">
        <div className="grad-primary p-8 text-white">
          <h1 className="text-3xl">{t("dash.hello")}, {user?.name} 👋</h1>
          <p className="mt-2 max-w-lg text-white/80">{t("dash.welcome")}</p>
        </div>
      </div>

      {/* Stat cards */}
      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {STATS.map((s) => (
          <div key={s.label} className="card flex items-center gap-4 p-5">
            <div className="grid h-12 w-12 place-items-center rounded-[var(--radius)] bg-[var(--primary)]/10 text-2xl">
              {s.icon}
            </div>
            <div>
              <p className="text-2xl">{s.value}</p>
              <p className="text-sm text-[var(--muted)]">{s.label}</p>
            </div>
          </div>
        ))}
      </div>

      {/* Permission inspector — shows the RBAC engine at work */}
      <div className="card p-6">
        <h2 className="text-xl">{t("dash.access")}</h2>
        <p className="mt-1 text-sm text-[var(--muted)]">{t("dash.accessSub")}</p>
        <div className="mt-4 flex flex-wrap gap-2">
          {user?.permissions.map((p) => (
            <span
              key={p}
              className="rounded-full border border-[var(--border)] px-3 py-1 text-xs font-bold text-[var(--foreground)]"
            >
              {p}
            </span>
          ))}
        </div>
      </div>
    </div>
  );
}
