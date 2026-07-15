"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { Logo } from "@/components/Logo";
import { useAuth } from "@/lib/auth";
import { useLang } from "@/lib/i18n";

/**
 * Nav item. `permission` gates visibility — an item only shows if the user
 * holds that permission (null = always visible). `tkey` is an i18n key so the
 * sidebar is bilingual.
 */
interface NavItem {
  tkey: string;
  href: string;
  icon: string;
  permission: string | null;
}

interface NavSection {
  tkey: string;
  items: NavItem[];
}

const NAV: NavSection[] = [
  {
    tkey: "side.main",
    items: [
      { tkey: "side.dashboard", href: "/dashboard", icon: "🏠", permission: "dashboard.view" },
    ],
  },
  {
    tkey: "side.education",
    items: [
      { tkey: "side.catalog", href: "/dashboard/courses", icon: "🎓", permission: "courses.view" },
      { tkey: "side.learning", href: "/dashboard/learn", icon: "📚", permission: "courses.view" },
      { tkey: "side.purchases", href: "/dashboard/purchases", icon: "🧾", permission: "courses.view" },
      { tkey: "side.teaching", href: "/dashboard/teaching", icon: "✏️", permission: "courses.create" },
      { tkey: "side.live", href: "/dashboard/live", icon: "🎥", permission: "live.view" },
      { tkey: "side.quizzes", href: "/dashboard/quizzes", icon: "📝", permission: "quizzes.view" },
    ],
  },
  {
    tkey: "side.administration",
    items: [
      { tkey: "side.users", href: "/dashboard/users", icon: "👥", permission: "users.view" },
      { tkey: "side.roles", href: "/dashboard/roles", icon: "🛡️", permission: "roles.view" },
      { tkey: "side.modules", href: "/dashboard/modules", icon: "🧩", permission: "modules.view" },
      { tkey: "side.gateways", href: "/dashboard/gateways", icon: "💳", permission: "gateways.view" },
      { tkey: "side.content", href: "/dashboard/content", icon: "📰", permission: "settings.view" },
      { tkey: "side.settings", href: "/dashboard/settings", icon: "⚙️", permission: "settings.view" },
    ],
  },
  {
    tkey: "side.platform",
    items: [
      { tkey: "side.tenants", href: "/dashboard/platform/tenants", icon: "🏢", permission: "tenants.view" },
      { tkey: "side.plans", href: "/dashboard/platform/plans", icon: "💠", permission: "tenants.view" },
    ],
  },
];

export function Sidebar({ open, onClose }: { open: boolean; onClose: () => void }) {
  const pathname = usePathname();
  const { user, can } = useAuth();
  const { t } = useLang();

  return (
    <>
      {/* Mobile backdrop */}
      {open && (
        <div className="fixed inset-0 z-30 bg-black/40 md:hidden" onClick={onClose} />
      )}

      <aside
        className={`fixed z-40 h-dvh w-72 shrink-0 overflow-y-auto border-r border-[var(--border)] bg-[var(--surface)] px-5 py-7 transition-transform md:static md:translate-x-0 ${
          open ? "translate-x-0" : "-translate-x-full"
        }`}
      >
        <div className="px-2">
          <Logo size={34} />
        </div>

        {/* Profile mini-card */}
        <div className="mt-6 rounded-[var(--radius)] border border-[var(--border)] bg-[var(--background)] p-4">
          <p className="truncate text-sm font-bold text-[var(--foreground)]">{user?.name}</p>
          <p className="mt-0.5 truncate text-xs text-[var(--muted)]">{user?.email}</p>
          <div className="mt-2 flex flex-wrap gap-1">
            {user?.roles.map((r) => (
              <span key={r} className="rounded-full bg-[var(--primary)]/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-[var(--primary)]">
                {r}
              </span>
            ))}
          </div>
        </div>

        <nav className="mt-6 space-y-6">
          {NAV.map((section) => {
            const items = section.items.filter(
              (i) => i.permission === null || can(i.permission)
            );
            if (items.length === 0) return null;
            return (
              <div key={section.tkey}>
                <p className="px-2 text-[11px] font-bold uppercase tracking-wider text-[var(--muted)]">
                  {t(section.tkey)}
                </p>
                <div className="mt-2 space-y-1">
                  {items.map((item) => {
                    const active =
                      pathname === item.href ||
                      (item.href !== "/dashboard" && pathname.startsWith(item.href));
                    return (
                      <Link
                        key={item.href}
                        href={item.href}
                        onClick={onClose}
                        className={`flex items-center gap-3 rounded-[var(--radius)] px-3 py-2.5 text-sm font-bold transition-colors ${
                          active
                            ? "bg-[var(--primary)] text-white"
                            : "text-[var(--foreground)] hover:bg-[var(--primary)]/8"
                        }`}
                      >
                        <span className="text-base">{item.icon}</span>
                        {t(item.tkey)}
                      </Link>
                    );
                  })}
                </div>
              </div>
            );
          })}
        </nav>
      </aside>
    </>
  );
}
