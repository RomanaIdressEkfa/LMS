export function ComingSoon({
  title,
  phase,
  children,
}: {
  title: string;
  phase: string;
  children?: React.ReactNode;
}) {
  return (
    <div className="space-y-4">
      <h1 className="text-3xl">{title}</h1>
      <div className="card grid place-items-center p-12 text-center">
        <div className="text-5xl">🚧</div>
        <h2 className="mt-4 text-xl">Coming in {phase}</h2>
        <p className="mt-2 max-w-md text-[var(--muted)]">
          {children ??
            "This module is scaffolded and permission-gated. We'll build out its full UI in an upcoming phase."}
        </p>
      </div>
    </div>
  );
}
