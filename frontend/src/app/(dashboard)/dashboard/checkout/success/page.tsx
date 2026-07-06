"use client";

import { useEffect, useState, Suspense } from "react";
import { useSearchParams, useRouter } from "next/navigation";
import { apiPost, ApiError } from "@/lib/api";

function SuccessInner() {
  const params = useSearchParams();
  const router = useRouter();
  const reference = params.get("order");
  const sessionId = params.get("session_id");
  const [state, setState] = useState<"verifying" | "done" | "failed">("verifying");
  const [message, setMessage] = useState("Confirming your payment…");

  useEffect(() => {
    if (!reference) {
      setState("failed");
      setMessage("Missing order reference.");
      return;
    }
    apiPost<{ status: string; message: string }>(`/checkout/${reference}/confirm`, { session_id: sessionId })
      .then((res) => {
        setState("done");
        setMessage(res.message ?? "Payment confirmed!");
      })
      .catch((e) => {
        setState("failed");
        setMessage(e instanceof ApiError ? e.message : "We couldn't confirm this payment yet.");
      });
  }, [reference, sessionId]);

  return (
    <div className="grid min-h-[60vh] place-items-center">
      <div className="card max-w-md p-10 text-center">
        <div className="text-5xl">{state === "done" ? "🎉" : state === "failed" ? "⚠️" : "⏳"}</div>
        <h1 className="mt-4 text-2xl">
          {state === "done" ? "You're enrolled!" : state === "failed" ? "Payment not confirmed" : "Please wait"}
        </h1>
        <p className="mt-2 text-[var(--muted)]">{message}</p>
        {reference && <p className="mt-2 text-xs text-[var(--muted)]">Order {reference}</p>}
        <div className="mt-6 flex justify-center gap-2">
          <button onClick={() => router.push("/dashboard/learn")} className="btn-primary">My Learning</button>
          <button onClick={() => router.push("/dashboard/purchases")} className="btn-ghost">My Purchases</button>
        </div>
      </div>
    </div>
  );
}

export default function CheckoutSuccessPage() {
  return (
    <Suspense fallback={<p className="text-[var(--muted)]">Loading…</p>}>
      <SuccessInner />
    </Suspense>
  );
}
