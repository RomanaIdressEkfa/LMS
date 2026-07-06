"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { apiGet } from "@/lib/api";

interface Order {
  id: number;
  reference: string;
  gateway: string;
  amount: string;
  currency: string;
  status: string;
  paid_at: string | null;
  created_at: string;
  course: { id: number; title: string; slug: string };
}

const STATUS_STYLE: Record<string, string> = {
  paid: "bg-[var(--success)]/15 text-[var(--success)]",
  pending: "bg-[var(--warning)]/15 text-[var(--warning)]",
  failed: "bg-[var(--danger)]/15 text-[var(--danger)]",
  cancelled: "bg-[var(--muted)]/15 text-[var(--muted)]",
};

export default function PurchasesPage() {
  const [orders, setOrders] = useState<Order[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    apiGet<{ orders: Order[] }>("/my/orders")
      .then((d) => setOrders(d.orders))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <p className="text-[var(--muted)]">Loading orders…</p>;

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl">My Purchases</h1>
        <p className="mt-1 text-[var(--muted)]">Your order history and receipts.</p>
      </div>

      {orders.length === 0 ? (
        <div className="card grid place-items-center p-12 text-center">
          <span className="text-4xl">🧾</span>
          <p className="mt-3 font-bold">No purchases yet.</p>
          <Link href="/dashboard/courses" className="btn-primary mt-4">Browse Catalog</Link>
        </div>
      ) : (
        <div className="card overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="border-b border-[var(--border)] text-[var(--muted)]">
                <tr>
                  <th className="p-4 font-bold">Order</th>
                  <th className="p-4 font-bold">Course</th>
                  <th className="p-4 font-bold">Method</th>
                  <th className="p-4 font-bold">Amount</th>
                  <th className="p-4 font-bold">Status</th>
                </tr>
              </thead>
              <tbody>
                {orders.map((o) => (
                  <tr key={o.id} className="border-b border-[var(--border)] last:border-0">
                    <td className="p-4 font-bold text-[var(--foreground)]">{o.reference}</td>
                    <td className="p-4">
                      <Link href={`/dashboard/courses/${o.course.slug}`} className="font-bold text-[var(--primary)] hover:underline">
                        {o.course.title}
                      </Link>
                    </td>
                    <td className="p-4 capitalize text-[var(--muted)]">{o.gateway}</td>
                    <td className="p-4 font-bold">${Number(o.amount).toFixed(2)}</td>
                    <td className="p-4">
                      <span className={`rounded-full px-2.5 py-1 text-[11px] font-bold uppercase ${STATUS_STYLE[o.status] ?? ""}`}>
                        {o.status}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
}
