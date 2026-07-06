"use client";

import { useRouter } from "next/navigation";

export default function CheckoutCancelPage() {
  const router = useRouter();
  return (
    <div className="grid min-h-[60vh] place-items-center">
      <div className="card max-w-md p-10 text-center">
        <div className="text-5xl">🛑</div>
        <h1 className="mt-4 text-2xl">Checkout cancelled</h1>
        <p className="mt-2 text-[var(--muted)]">No payment was taken. You can try again anytime.</p>
        <button onClick={() => router.push("/dashboard/courses")} className="btn-primary mt-6">
          Back to Catalog
        </button>
      </div>
    </div>
  );
}
