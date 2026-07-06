"use client";

import { useEffect, useState } from "react";
import { apiGet, apiPost } from "@/lib/api";
import { useAuth } from "@/lib/auth";
import type { PaymentGateway } from "@/lib/types";

export default function GatewaysPage() {
  const { can } = useAuth();
  const [gateways, setGateways] = useState<PaymentGateway[]>([]);
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState<number | null>(null);

  const canManage = can("gateways.manage");

  useEffect(() => {
    apiGet<{ gateways: PaymentGateway[] }>("/gateways")
      .then((d) => setGateways(d.gateways))
      .finally(() => setLoading(false));
  }, []);

  async function toggle(g: PaymentGateway) {
    if (!canManage) return;
    setBusy(g.id);
    try {
      const { gateway } = await apiPost<{ gateway: PaymentGateway }>(`/gateways/${g.id}/toggle`);
      setGateways((prev) => prev.map((x) => (x.id === gateway.id ? gateway : x)));
    } finally {
      setBusy(null);
    }
  }

  if (loading) return <p className="text-[var(--muted)]">Loading gateways…</p>;

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl">Payment Gateways</h1>
        <p className="mt-1 text-[var(--muted)]">
          Enable the gateways you want to offer at checkout. Only enabled
          gateways appear to buyers. Add credentials per gateway (next phase).
        </p>
      </div>

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        {gateways.map((g) => (
          <div key={g.id} className="card flex items-center justify-between p-5">
            <div className="flex items-center gap-3">
              <div className="grid h-11 w-11 place-items-center rounded-[var(--radius)] bg-[var(--primary)]/10 text-xl">
                💳
              </div>
              <div>
                <h3 className="text-lg leading-tight">{g.name}</h3>
                <p className="text-xs text-[var(--muted)]">
                  {g.currency} · {g.test_mode ? "Test mode" : "Live"}
                </p>
              </div>
            </div>
            <button
              disabled={!canManage || busy === g.id}
              onClick={() => toggle(g)}
              className={`relative h-7 w-12 rounded-full transition-colors disabled:opacity-50 ${
                g.enabled ? "bg-[var(--success)]" : "bg-[var(--muted)]/40"
              }`}
              aria-label={`Toggle ${g.name}`}
            >
              <span
                className={`absolute top-1 h-5 w-5 rounded-full bg-white transition-all ${
                  g.enabled ? "left-6" : "left-1"
                }`}
              />
            </button>
          </div>
        ))}
      </div>
    </div>
  );
}
