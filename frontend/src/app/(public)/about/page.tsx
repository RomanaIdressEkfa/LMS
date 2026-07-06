import Link from "next/link";

const VALUES = [
  { icon: "🎯", title: "Learner-first", text: "Every feature is built to help students actually learn and finish.", grad: "grad-primary" },
  { icon: "🧩", title: "Only what you need", text: "Turn modules on or off — never pay for features you won't use.", grad: "grad-purple" },
  { icon: "⚡", title: "Fast & modern", text: "Built on Laravel + Next.js for a snappy, reliable experience.", grad: "grad-sunset" },
  { icon: "🌍", title: "For everyone", text: "Students, instructors and organizations — all in one platform.", grad: "grad-teal" },
];

const STEPS = [
  { n: "1", title: "Create your account", text: "Sign up as a student, instructor or organization in seconds." },
  { n: "2", title: "Build or enroll", text: "Instructors build courses; students enroll — free or paid." },
  { n: "3", title: "Learn & grow", text: "Take lessons, join live classes, pass quizzes, earn certificates." },
];

export default function AboutPage() {
  return (
    <div>
      <section className="grad-hero border-b border-[var(--border)]">
        <div className="mx-auto max-w-4xl px-5 py-16 text-center md:px-8 md:py-20">
          <h1 className="text-4xl md:text-5xl">Learning, <span className="gradient-text">reimagined</span></h1>
          <p className="mx-auto mt-4 max-w-2xl text-lg font-semibold text-[var(--muted)]">
            LMS is a modern platform for creating, selling and teaching courses — with live classes,
            quizzes, certificates and flexible pricing. Whether you&apos;re a solo instructor or a growing
            organization, everything you need is here.
          </p>
          <div className="mt-8 flex flex-wrap justify-center gap-3">
            <Link href="/register" className="btn-primary">Get started free</Link>
            <Link href="/courses" className="btn-ghost">Browse courses</Link>
          </div>
        </div>
      </section>

      {/* Values */}
      <section className="mx-auto max-w-7xl px-5 py-16 md:px-8">
        <h2 className="text-center text-3xl md:text-4xl">What we stand for</h2>
        <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {VALUES.map((v) => (
            <div key={v.title} className="card p-7">
              <div className={`grid h-14 w-14 place-items-center rounded-2xl text-2xl text-white ${v.grad}`}>{v.icon}</div>
              <h3 className="mt-5 text-xl">{v.title}</h3>
              <p className="mt-2 font-semibold text-[var(--muted)]">{v.text}</p>
            </div>
          ))}
        </div>
      </section>

      {/* How it works */}
      <section className="bg-[var(--surface)]">
        <div className="mx-auto max-w-7xl px-5 py-16 md:px-8">
          <h2 className="text-center text-3xl md:text-4xl">How it works</h2>
          <div className="mt-10 grid gap-6 md:grid-cols-3">
            {STEPS.map((s) => (
              <div key={s.n} className="card p-8">
                <div className="grad-primary grid h-12 w-12 place-items-center rounded-2xl text-xl font-extrabold text-white">{s.n}</div>
                <h3 className="mt-5 text-xl">{s.title}</h3>
                <p className="mt-2 font-semibold text-[var(--muted)]">{s.text}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Stats band */}
      <section className="mx-auto max-w-7xl px-5 py-16 md:px-8">
        <div className="grad-primary grid grid-cols-2 gap-6 rounded-[2rem] p-10 text-center text-white md:grid-cols-4">
          {[["50+", "Courses"], ["1000+", "Students"], ["12", "Modules"], ["8", "Payment options"]].map(([v, l]) => (
            <div key={l}>
              <p className="text-3xl md:text-4xl">{v}</p>
              <p className="mt-1 text-sm font-bold text-white/80">{l}</p>
            </div>
          ))}
        </div>
      </section>
    </div>
  );
}
