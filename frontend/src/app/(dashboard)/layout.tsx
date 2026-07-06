"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "@/lib/auth";
import { Sidebar } from "@/components/dashboard/Sidebar";
import { Topbar } from "@/components/dashboard/Topbar";
import { ImpersonationBanner } from "@/components/dashboard/ImpersonationBanner";
import { Logo } from "@/components/Logo";

export default function DashboardLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const { user, loading } = useAuth();
  const router = useRouter();
  const [menuOpen, setMenuOpen] = useState(false);

  // Guard: bounce unauthenticated users to login.
  useEffect(() => {
    if (!loading && !user) router.replace("/login");
  }, [loading, user, router]);

  if (loading || !user) {
    return (
      <div className="grid min-h-dvh place-items-center bg-[var(--background)]">
        <div className="animate-pulse">
          <Logo size={44} />
        </div>
      </div>
    );
  }

  return (
    <div className="flex min-h-dvh bg-[var(--background)]">
      <Sidebar open={menuOpen} onClose={() => setMenuOpen(false)} />
      <div className="flex min-w-0 flex-1 flex-col">
        <ImpersonationBanner />
        <Topbar onMenu={() => setMenuOpen(true)} />
        <main className="flex-1 px-5 py-6 md:px-8 md:py-7">{children}</main>
      </div>
    </div>
  );
}
