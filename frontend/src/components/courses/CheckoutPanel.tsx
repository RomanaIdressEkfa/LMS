"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { apiGet, apiPost, ApiError } from "@/lib/api";
import type { Course } from "@/lib/types";

interface Gateway {
  id: number;
  key: string;
  name: string;
  currency: string;
}

type CheckoutResponse =
  | { status: "completed"; message: string }
  | { status: "redirect"; redirect_url: string; order: string }
  | { status: "pending"; message: string; order: string }
  | { status: "failed"; message: string };

/**
 * Buyer-facing checkout for a paid course. Lists enabled gateways and routes
 * the result: instant enroll (test/wallet), external redirect (Stripe), or a
 * pending message (bank transfer).
 */
export function CheckoutPanel({ course, onClose }: { course: Course; onClose: () => void }) {
  const router = useRouter();
  const [gateways, setGateways] = useState<Gateway[]>([]);
  const [selected, setSelected] = useState<string>("");
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [pending, setPending] = useState<string | null>(null);

  useEffect(() => {
    apiGet<{ gateways: Gateway[] }>("/checkout/gateways")
      .then((d) => {
        setGateways(d.gateways);
        if (d.gateways[0]) setSelected(d.gateways[0].key);
      })
      .finally(() => setLoading(false));
  }, []);

  async function pay() {
    setBusy(true);
    setError(null);
    setPending(null);
    try {
      const res = await apiPost<CheckoutResponse>(`/courses/${course.id}/checkout`, { gateway: selected });
      if (res.status === "completed") {
        router.push(`/dashboard/learn/${course.slug}`);
      } else if (res.status === "redirect") {
        window.location.href = res.redirect_url;
      } else if (res.status === "pending") {
        setPending(res.message);
      } else {
        setError(res.message);
      }
    } catch (e) {
      setError(e instanceof ApiError ? e.message : "Checkout failed.");
    } finally {
      setBusy(false);
    }
  }

  const price = Number(course.price);

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onClick={onClose}>
      <div className="card w-full max-w-md p-6" onClick={(e) => e.stopPropagation()}>
        <div className="flex items-start justify-between">
          <div>
            <h2 className="text-xl">Checkout</h2>
            <p className="mt-1 text-sm text-[var(--muted)]">{course.title}</p>
          </div>
          <button onClick={onClose} className="text-2xl leading-none text-[var(--muted)]">×</button>
        </div>

        <div className="mt-4 flex items-center justify-between rounded-[var(--radius)] bg-[var(--background)] p-4">
          <span className="font-bold">Total</span>
          <span className="text-2xl">${price.toFixed(2)}</span>
        </div>

        {loading ? (
          <p className="mt-4 text-[var(--muted)]">Loading payment methods…</p>
        ) : gateways.length === 0 ? (
          <p className="mt-4 rounded-[var(--radius)] bg-[var(--warning)]/10 px-4 py-3 text-sm font-bold text-[var(--warning)]">
            No payment methods are enabled yet. An admin can enable one under Payment Gateways.
          </p>
        ) : (
          <>
            <p className="label mt-5">Payment method</p>
            <div className="space-y-2">
              {gateways.map((g) => (
                <label
                  key={g.id}
                  className={`flex cursor-pointer items-center gap-3 rounded-[var(--radius)] border p-3 transition-colors ${
                    selected === g.key ? "border-[var(--primary)] bg-[var(--primary)]/5" : "border-[var(--border)]"
                  }`}
                >
                  <input
                    type="radio"
                    name="gateway"
                    checked={selected === g.key}
                    onChange={() => setSelected(g.key)}
                    className="h-4 w-4 accent-[var(--primary)]"
                  />
                  <span className="font-bold text-[var(--foreground)]">{g.name}</span>
                  {g.key === "test" && (
                    <span className="ml-auto rounded-full bg-[var(--muted)]/15 px-2 py-0.5 text-[10px] font-bold uppercase text-[var(--muted)]">
                      sandbox
                    </span>
                  )}
                </label>
              ))}
            </div>

            {error && (
              <p className="mt-3 rounded-[var(--radius)] bg-[var(--danger)]/10 px-4 py-2 text-sm font-bold text-[var(--danger)]">{error}</p>
            )}
            {pending && (
              <p className="mt-3 rounded-[var(--radius)] bg-[var(--warning)]/10 px-4 py-2 text-sm font-bold text-[var(--warning)]">
                {pending} Your order is pending approval — check My Purchases.
              </p>
            )}

            <button onClick={pay} disabled={busy || !selected} className="btn-primary mt-5 w-full disabled:opacity-60">
              {busy ? "Processing…" : `Pay $${price.toFixed(2)}`}
            </button>
            <p className="mt-2 text-center text-xs text-[var(--muted)]">Secure checkout · powered by LMS</p>
          </>
        )}
      </div>
    </div>
  );
}
