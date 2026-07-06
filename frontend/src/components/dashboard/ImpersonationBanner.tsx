"use client";

import { useEffect, useState } from "react";
import { setToken } from "@/lib/api";

/**
 * Shows a bar while an admin is impersonating another user. Clicking "Return"
 * restores the admin's original token (stashed at impersonation time).
 */
export function ImpersonationBanner() {
  const [impersonating, setImpersonating] = useState(false);

  useEffect(() => {
    setImpersonating(!!localStorage.getItem("nova_token_admin"));
  }, []);

  if (!impersonating) return null;

  function returnToAdmin() {
    const admin = localStorage.getItem("nova_token_admin");
    if (admin) {
      setToken(admin);
      localStorage.removeItem("nova_token_admin");
    }
    window.location.href = "/dashboard/users";
  }

  return (
    <div className="flex items-center justify-center gap-3 bg-[var(--warning)] px-4 py-2 text-center text-sm font-bold text-black">
      👤 You are impersonating another user.
      <button onClick={returnToAdmin} className="rounded-full bg-black/80 px-3 py-1 text-white hover:bg-black">
        Return to admin
      </button>
    </div>
  );
}
