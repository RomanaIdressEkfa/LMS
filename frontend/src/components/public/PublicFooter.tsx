import Link from "next/link";
import { Logo } from "@/components/Logo";

export function PublicFooter() {
  return (
    <footer className="border-t border-[var(--border)] bg-[var(--surface)]">
      <div className="mx-auto grid max-w-7xl gap-8 px-5 py-14 md:grid-cols-4 md:px-8">
        <div className="md:col-span-1">
          <Logo size={32} />
          <p className="mt-3 max-w-xs text-sm font-semibold text-[var(--muted)]">
            Create, sell and teach courses. Grow your online academy — all in one bold platform.
          </p>
        </div>
        <FooterCol title="Platform" links={[["Courses", "/courses"], ["Instructors", "/instructors"], ["Pricing", "/pricing"]]} />
        <FooterCol title="Company" links={[["About", "/about"], ["Contact", "/contact"]]} />
        <FooterCol title="Account" links={[["Login", "/login"], ["Register", "/register"]]} />
      </div>
      <div className="border-t border-[var(--border)]">
        <div className="mx-auto max-w-7xl px-5 py-6 text-center text-sm font-semibold text-[var(--muted)] md:px-8">
          © {new Date().getFullYear()} LMS. All rights reserved.
        </div>
      </div>
    </footer>
  );
}

function FooterCol({ title, links }: { title: string; links: [string, string][] }) {
  return (
    <div>
      <p className="text-sm font-extrabold text-[var(--foreground)]">{title}</p>
      <ul className="mt-3 space-y-2">
        {links.map(([label, href]) => (
          <li key={href}>
            <Link href={href} className="text-sm font-semibold text-[var(--muted)] hover:text-[var(--primary)]">{label}</Link>
          </li>
        ))}
      </ul>
    </div>
  );
}
