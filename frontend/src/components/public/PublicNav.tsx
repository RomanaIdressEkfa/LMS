"use client";

import { useState } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { Logo } from "@/components/Logo";
import { useAuth } from "@/lib/auth";

const LINKS = [
  { href: "/courses", label: "Courses" },
  { href: "/instructors", label: "Instructors" },
  { href: "/pricing", label: "Pricing" },
  { href: "/about", label: "About" },
  { href: "/contact", label: "Contact" },
];

export function PublicNav() {
  const { user } = useAuth();
  const pathname = usePathname();
  const [open, setOpen] = useState(false);

  return (
    <header className="sticky top-0 z-30 border-b border-[var(--border)] bg-[var(--surface)]/85 backdrop-blur">
      <nav className="mx-auto flex h-16 max-w-7xl items-center justify-between px-5 md:px-8">
        <Link href="/"><Logo size={34} /></Link>

        <div className="hidden items-center gap-7 md:flex">
          {LINKS.map((l) => (
            <Link
              key={l.href}
              href={l.href}
              className={`text-sm font-bold transition-colors ${
                pathname === l.href ? "text-[var(--primary)]" : "text-[var(--muted)] hover:text-[var(--foreground)]"
              }`}
            >
              {l.label}
            </Link>
          ))}
        </div>

        <div className="flex items-center gap-2">
          {user ? (
            <Link href="/dashboard" className="btn-primary px-4 py-2 text-sm">Go to Dashboard</Link>
          ) : (
            <>
              <Link href="/login" className="hidden rounded-lg px-4 py-2 text-sm font-bold text-[var(--foreground)] hover:text-[var(--primary)] sm:block">Login</Link>
              <Link href="/register" className="btn-primary px-4 py-2 text-sm">Start Learning</Link>
            </>
          )}
          <button onClick={() => setOpen((o) => !o)} className="rounded-lg border border-[var(--border)] p-2 md:hidden" aria-label="Menu">☰</button>
        </div>
      </nav>

      {open && (
        <div className="border-t border-[var(--border)] bg-[var(--surface)] px-5 py-3 md:hidden">
          {LINKS.map((l) => (
            <Link key={l.href} href={l.href} onClick={() => setOpen(false)} className="block py-2 font-bold text-[var(--foreground)]">
              {l.label}
            </Link>
          ))}
        </div>
      )}
    </header>
  );
}
