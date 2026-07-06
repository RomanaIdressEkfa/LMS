"use client";

import { useAuth } from "@/lib/auth";

const STATS = [
  { label: "Enrolled Courses", value: "11", icon: "🎓", tone: "primary" },
  { label: "Certificates", value: "2", icon: "🏅", tone: "success" },
  { label: "Live Sessions", value: "3", icon: "🎥", tone: "warning" },
  { label: "Wallet Balance", value: "$591", icon: "💰", tone: "primary" },
];

export default function DashboardPage() {
  const { user } = useAuth();

  return (
    <div className="space-y-8">
      {/* Hero greeting */}
      <div className="card overflow-hidden">
        <div className="bg-gradient-to-br from-[#2563ff] to-[#1d4ed8] p-8 text-white">
          <h1 className="text-3xl">Hello, {user?.name} 👋</h1>
          <p className="mt-2 max-w-lg text-white/80">
            Welcome to your LMS dashboard. Everything you can see here is
            controlled by your role&apos;s permissions.
          </p>
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
        <h2 className="text-xl">Your access</h2>
        <p className="mt-1 text-sm text-[var(--muted)]">
          These are the permissions your role grants. The sidebar and every page
          adapt to exactly this list.
        </p>
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
