import Link from "next/link";

export function PublicFooter() {
  return (
    <footer className="grad-ph relative overflow-hidden text-white">
      <div className="blob grad-magenta -right-20 -top-20 h-72 w-72" />
      <div className="relative mx-auto grid max-w-[1600px] gap-8 px-5 py-14 md:grid-cols-4 md:px-8">
        <div className="md:col-span-1">
          <div className="flex items-center gap-2.5">
            <span className="grid h-9 w-9 place-items-center rounded-xl bg-white/15 text-lg">✦</span>
            <span className="text-xl font-extrabold">LMS</span>
          </div>
          <p className="mt-3 max-w-xs text-sm font-semibold text-white/80">
            Create, sell and teach courses. Grow your online academy — all in one bold platform.
          </p>
        </div>
        <FooterCol title="Platform" links={[["Courses", "/courses"], ["Instructors", "/instructors"], ["Pricing", "/pricing"]]} />
        <FooterCol title="Company" links={[["About", "/about"], ["Contact", "/contact"]]} />
        <FooterCol title="Account" links={[["Login", "/login"], ["Register", "/register"]]} />
      </div>
      <div className="relative border-t border-white/15">
        <div className="mx-auto max-w-[1600px] px-5 py-6 text-center text-sm font-semibold text-white/75 md:px-8">
          © {new Date().getFullYear()} LMS. All rights reserved.
        </div>
      </div>
    </footer>
  );
}

function FooterCol({ title, links }: { title: string; links: [string, string][] }) {
  return (
    <div>
      <p className="text-sm font-extrabold">{title}</p>
      <ul className="mt-3 space-y-2">
        {links.map(([label, href]) => (
          <li key={href}>
            <Link href={href} className="text-sm font-semibold text-white/80 hover:text-white">{label}</Link>
          </li>
        ))}
      </ul>
    </div>
  );
}
