"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { Logo } from "@/components/Logo";
import { useAuth } from "@/lib/auth";

/**
 * Nav item. `permission` gates visibility — an item only shows if the user
 * holds that permission (null = always visible). This is how the sidebar
 * adapts per role automatically.
 */
interface NavItem {
  label: string;
  href: string;
  icon: string;
  permission: string | null;
}

interface NavSection {
  title: string;
  items: NavItem[];
}

const NAV: NavSection[] = [
  {
    title: "Main",
    items: [
      { label: "Dashboard", href: "/dashboard", icon: "🏠", permission: "dashboard.view" },
    ],
  },
  {
    title: "Education",
    items: [
      { label: "Course Catalog", href: "/dashboard/courses", icon: "🎓", permission: "courses.view" },
      { label: "My Learning", href: "/dashboard/learn", icon: "📚", permission: "courses.view" },
      { label: "My Purchases", href: "/dashboard/purchases", icon: "🧾", permission: "courses.view" },
      { label: "Teaching", href: "/dashboard/teaching", icon: "✏️", permission: "courses.create" },
      { label: "Live Classes", href: "/dashboard/live", icon: "🎥", permission: "live.view" },
      { label: "Quizzes", href: "/dashboard/quizzes", icon: "📝", permission: "quizzes.view" },
    ],
  },
  {
    title: "Administration",
    items: [
      { label: "Users", href: "/dashboard/users", icon: "👥", permission: "users.view" },
      { label: "Roles & Permissions", href: "/dashboard/roles", icon: "🛡️", permission: "roles.view" },
      { label: "Modules / Addons", href: "/dashboard/modules", icon: "🧩", permission: "modules.view" },
      { label: "Payment Gateways", href: "/dashboard/gateways", icon: "💳", permission: "gateways.view" },
      { label: "Settings", href: "/dashboard/settings", icon: "⚙️", permission: "settings.view" },
    ],
  },
  {
    title: "Platform",
    items: [
      { label: "Tenants", href: "/dashboard/platform/tenants", icon: "🏢", permission: "tenants.view" },
      { label: "Plans", href: "/dashboard/platform/plans", icon: "💠", permission: "tenants.view" },
    ],
  },
];

export function Sidebar({ open, onClose }: { open: boolean; onClose: () => void }) {
  const pathname = usePathname();
  const { user, can } = useAuth();

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
              <div key={section.title}>
                <p className="px-2 text-[11px] font-bold uppercase tracking-wider text-[var(--muted)]">
                  {section.title}
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
                        {item.label}
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
