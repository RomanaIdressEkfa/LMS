"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { Logo } from "@/components/Logo";
import { useAuth } from "@/lib/auth";
import { ApiError } from "@/lib/api";

export default function LoginPage() {
  const { login } = useAuth();
  const router = useRouter();
  const [form, setForm] = useState({ login: "", password: "" });
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const [show, setShow] = useState(false);

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    setLoading(true);
    try {
      await login(form.login, form.password);
      router.push("/dashboard");
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Something went wrong.");
    } finally {
      setLoading(false);
    }
  }

  const demo = (email: string) => setForm({ login: email, password: "password" });

  return (
    <main className="min-h-dvh w-full bg-[var(--background)] px-4 py-10 flex items-center justify-center">
      <div className="card w-full max-w-5xl grid overflow-hidden md:grid-cols-2">
        {/* ---- Form side ---- */}
        <div className="p-8 sm:p-12">
          <Logo size={38} />
          <div className="mt-10">
            <p className="text-[var(--muted)]">Welcome back 👋</p>
            <h1 className="mt-1 text-3xl">Log in to your account</h1>
          </div>

          <form onSubmit={onSubmit} className="mt-8 space-y-5">
            <div>
              <label className="label" htmlFor="login">Email or Phone</label>
              <input
                id="login"
                className="input"
                placeholder="you@example.com"
                value={form.login}
                onChange={(e) => setForm({ ...form, login: e.target.value })}
                autoComplete="username"
                required
              />
            </div>

            <div>
              <label className="label" htmlFor="password">Password</label>
              <div className="relative">
                <input
                  id="password"
                  type={show ? "text" : "password"}
                  className="input pr-12"
                  placeholder="••••••••"
                  value={form.password}
                  onChange={(e) => setForm({ ...form, password: e.target.value })}
                  autoComplete="current-password"
                  required
                />
                <button
                  type="button"
                  onClick={() => setShow((s) => !s)}
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-bold text-[var(--muted)] hover:text-[var(--primary)]"
                >
                  {show ? "Hide" : "Show"}
                </button>
              </div>
            </div>

            {error && (
              <p className="rounded-[var(--radius)] bg-[var(--danger)]/10 px-4 py-3 text-sm font-bold text-[var(--danger)]">
                {error}
              </p>
            )}

            <button type="submit" disabled={loading} className="btn-primary w-full disabled:opacity-60">
              {loading ? "Logging in…" : "Login"}
            </button>
          </form>

          {/* Demo accounts for quick testing */}
          <div className="mt-8">
            <p className="text-center text-sm text-[var(--muted)]">Quick demo login</p>
            <div className="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
              {[
                ["Super", "super@novalms.test"],
                ["Admin", "admin@novalms.test"],
                ["Teacher", "teacher@novalms.test"],
                ["Student", "student@novalms.test"],
              ].map(([label, email]) => (
                <button
                  key={email}
                  type="button"
                  onClick={() => demo(email)}
                  className="rounded-[var(--radius)] border border-[var(--border)] px-3 py-2 text-sm font-bold text-[var(--foreground)] transition-colors hover:border-[var(--primary)] hover:text-[var(--primary)]"
                >
                  {label}
                </button>
              ))}
            </div>
            <p className="mt-2 text-center text-xs text-[var(--muted)]">password: <b>password</b></p>
          </div>

          <p className="mt-8 text-center text-sm text-[var(--muted)]">
            Don&apos;t have an account?{" "}
            <Link href="/register" className="font-bold text-[var(--primary)] hover:underline">
              Sign Up
            </Link>
          </p>
        </div>

        {/* ---- Illustration side ---- */}
        <div className="relative hidden md:flex flex-col items-center justify-center bg-gradient-to-br from-[#2563ff] to-[#1d4ed8] p-12 text-white">
          <div className="absolute inset-0 opacity-20" style={{ backgroundImage: "radial-gradient(circle at 20% 20%, #fff 2px, transparent 0), radial-gradient(circle at 70% 60%, #fff 2px, transparent 0)", backgroundSize: "48px 48px" }} />
          <div className="relative text-center">
            <div className="mx-auto mb-8 flex h-40 w-40 items-center justify-center rounded-full bg-white/10 backdrop-blur">
              <Logo size={96} showText={false} />
            </div>
            <h2 className="text-3xl font-display">Learn Without Limits</h2>
            <p className="mt-3 max-w-xs text-white/80">
              Create, sell and teach courses. Host live classes. Grow your academy — all in one bold platform.
            </p>
          </div>
        </div>
      </div>
    </main>
  );
}
