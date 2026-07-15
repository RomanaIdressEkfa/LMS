"use client";

import { useRouter } from "next/navigation";
import { useAuth } from "@/lib/auth";
import { useLang } from "@/lib/i18n";

export function Topbar({ onMenu }: { onMenu: () => void }) {
  const { user, logout } = useAuth();
  const { lang, setLang, t } = useLang();
  const router = useRouter();

  async function handleLogout() {
    await logout();
    router.push("/login");
  }

  const initials = user?.name
    ?.split(" ")
    .map((p) => p[0])
    .slice(0, 2)
    .join("")
    .toUpperCase();

  return (
    <header className="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-[var(--border)] bg-[var(--surface)]/80 px-4 backdrop-blur md:px-8">
      <button
        onClick={onMenu}
        className="rounded-lg border border-[var(--border)] p-2 md:hidden"
        aria-label="Open menu"
      >
        ☰
      </button>

      <div className="hidden md:block" />

      <div className="flex items-center gap-3">
        {/* Language toggle */}
        <div className="flex items-center rounded-full border border-[var(--border)] p-0.5 text-xs font-bold">
          <button onClick={() => setLang("en")} className={`rounded-full px-2.5 py-1 transition-colors ${lang === "en" ? "bg-[var(--primary)] text-white" : "text-[var(--muted)]"}`}>EN</button>
          <button onClick={() => setLang("bn")} className={`rounded-full px-2.5 py-1 transition-colors ${lang === "bn" ? "bg-[var(--primary)] text-white" : "text-[var(--muted)]"}`}>বাংলা</button>
        </div>

        <div className="flex h-9 w-9 items-center justify-center rounded-full bg-[var(--primary)] text-sm font-bold text-white">
          {initials}
        </div>
        <div className="hidden sm:block">
          <p className="text-sm font-bold leading-none text-[var(--foreground)]">{user?.name}</p>
          <p className="mt-1 text-xs leading-none text-[var(--muted)]">{user?.roles.join(", ")}</p>
        </div>
        <button
          onClick={handleLogout}
          className="ml-2 rounded-[var(--radius)] border border-[var(--border)] px-3 py-2 text-sm font-bold text-[var(--foreground)] transition-colors hover:border-[var(--danger)] hover:text-[var(--danger)]"
        >
          {t("topbar.logout")}
        </button>
      </div>
    </header>
  );
}
