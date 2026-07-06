"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { Logo } from "@/components/Logo";
import { useAuth } from "@/lib/auth";
import { ApiError } from "@/lib/api";
import type { RegisterRole } from "@/lib/types";

const ROLES: { value: RegisterRole; label: string; hint: string; icon: string }[] = [
  { value: "student", label: "Student", hint: "Learn & earn certificates", icon: "🎓" },
  { value: "teacher", label: "Instructor", hint: "Create & sell courses", icon: "🧑‍🏫" },
  { value: "organization", label: "Organization", hint: "Train your team", icon: "🏢" },
];

const PERKS = [
  "Access free & paid courses",
  "Learn live with instructors",
  "Earn certificates",
  "Track your progress",
];

export default function RegisterPage() {
  const { register } = useAuth();
  const router = useRouter();
  const [role, setRole] = useState<RegisterRole>("student");
  const [form, setForm] = useState({
    name: "",
    email: "",
    phone: "",
    password: "",
    password_confirmation: "",
  });
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    setErrors({});
    setLoading(true);
    try {
      await register({ ...form, role });
      router.push("/dashboard");
    } catch (err) {
      if (err instanceof ApiError) {
        setError(err.message);
        setErrors(err.errors ?? {});
      } else setError("Something went wrong.");
    } finally {
      setLoading(false);
    }
  }

  const field = (k: keyof typeof form) => errors[k]?.[0];

  return (
    <main className="flex min-h-dvh w-full items-center justify-center bg-[var(--background)] px-4 py-10">
      <div className="card grid w-full max-w-5xl overflow-hidden md:grid-cols-2">
        {/* ---- Form side ---- */}
        <div className="p-8 sm:p-12">
          <Link href="/"><Logo size={38} /></Link>
          <div className="mt-8">
            <h1 className="text-3xl">Create your account</h1>
            <p className="mt-1 text-[var(--muted)]">Join us and start learning today.</p>
          </div>

          {/* Role selector */}
          <div className="mt-6 grid grid-cols-3 gap-2">
            {ROLES.map((r) => (
              <button
                key={r.value}
                type="button"
                onClick={() => setRole(r.value)}
                className={`rounded-[var(--radius-sm)] border p-3 text-left transition-colors ${
                  role === r.value
                    ? "border-[var(--primary)] bg-[var(--primary-soft)]"
                    : "border-[var(--border)] hover:border-[var(--primary)]"
                }`}
              >
                <span className="text-lg">{r.icon}</span>
                <span className="mt-1 block text-sm font-bold text-[var(--foreground)]">{r.label}</span>
                <span className="mt-0.5 block text-[11px] leading-tight text-[var(--muted)]">{r.hint}</span>
              </button>
            ))}
          </div>

          <form onSubmit={onSubmit} className="mt-6 space-y-4">
            <div>
              <label className="label">Full name</label>
              <input className="input" value={form.name}
                onChange={(e) => setForm({ ...form, name: e.target.value })} required />
              {field("name") && <p className="mt-1 text-xs font-bold text-[var(--danger)]">{field("name")}</p>}
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
              <div>
                <label className="label">Email</label>
                <input type="email" className="input" value={form.email}
                  onChange={(e) => setForm({ ...form, email: e.target.value })} required />
                {field("email") && <p className="mt-1 text-xs font-bold text-[var(--danger)]">{field("email")}</p>}
              </div>
              <div>
                <label className="label">Phone <span className="font-normal">(optional)</span></label>
                <input className="input" value={form.phone}
                  onChange={(e) => setForm({ ...form, phone: e.target.value })} />
              </div>
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
              <div>
                <label className="label">Password</label>
                <input type="password" className="input" value={form.password}
                  onChange={(e) => setForm({ ...form, password: e.target.value })} required />
                {field("password") && <p className="mt-1 text-xs font-bold text-[var(--danger)]">{field("password")}</p>}
              </div>
              <div>
                <label className="label">Confirm</label>
                <input type="password" className="input" value={form.password_confirmation}
                  onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })} required />
              </div>
            </div>

            {error && (
              <p className="rounded-[var(--radius-sm)] bg-[var(--danger)]/10 px-4 py-3 text-sm font-bold text-[var(--danger)]">
                {error}
              </p>
            )}

            <button type="submit" disabled={loading} className="btn-primary w-full disabled:opacity-60">
              {loading ? "Creating…" : "Create Account"}
            </button>
          </form>

          <p className="mt-6 text-center text-sm text-[var(--muted)]">
            Already have an account?{" "}
            <Link href="/login" className="font-bold text-[var(--primary)] hover:underline">Log in</Link>
          </p>
        </div>

        {/* ---- Brand side ---- */}
        <div className="relative hidden flex-col justify-center overflow-hidden bg-gradient-to-br from-[#2563ff] to-[#1b4dd8] p-12 text-white md:flex">
          <div className="absolute inset-0 opacity-20" style={{ backgroundImage: "radial-gradient(circle at 25% 15%, #fff 2px, transparent 0), radial-gradient(circle at 75% 65%, #fff 2px, transparent 0)", backgroundSize: "44px 44px" }} />
          <div className="relative">
            <div className="mb-8 flex h-20 w-20 items-center justify-center rounded-2xl bg-white/10 backdrop-blur">
              <Logo size={56} showText={false} />
            </div>
            <h2 className="text-3xl font-display leading-tight">Start your learning journey today</h2>
            <ul className="mt-8 space-y-4">
              {PERKS.map((p) => (
                <li key={p} className="flex items-center gap-3 font-semibold text-white/90">
                  <span className="grid h-6 w-6 place-items-center rounded-full bg-white/20 text-sm">✓</span>
                  {p}
                </li>
              ))}
            </ul>
          </div>
        </div>
      </div>
    </main>
  );
}
