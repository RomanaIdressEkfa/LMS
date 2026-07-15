"use client";

import { useEffect, useState, use } from "react";
import Link from "next/link";
import { apiGet, ApiError } from "@/lib/api";
import { useContent } from "@/lib/content";

interface Certificate {
  serial: string;
  student_name: string;
  course_title: string;
  instructor_name: string | null;
  completed_date: string;
  lessons_count: number;
}

export default function CertificatePage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = use(params);
  const { footer } = useContent();
  const brand = footer.brand || "LMS";
  const [cert, setCert] = useState<Certificate | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    apiGet<{ certificate: Certificate }>(`/courses/${slug}/certificate`)
      .then((d) => setCert(d.certificate))
      .catch((e) => setError(e instanceof ApiError ? e.message : "Could not load certificate."))
      .finally(() => setLoading(false));
  }, [slug]);

  if (loading) return <p className="text-[var(--muted)]">Loading certificate…</p>;

  if (error || !cert) {
    return (
      <div className="card grid place-items-center p-12 text-center">
        <span className="text-4xl">🔒</span>
        <p className="mt-3 font-bold">{error ?? "Certificate not available."}</p>
        <Link href="/dashboard/learn" className="btn-primary mt-4">Back to My Learning</Link>
      </div>
    );
  }

  return (
    <div className="space-y-5">
      {/* Print rules: when printing, show only the certificate element. */}
      <style>{`@media print {
        body * { visibility: hidden !important; }
        #certificate, #certificate * { visibility: visible !important; }
        #certificate { position: absolute; inset: 0; margin: 0 !important; box-shadow: none !important; }
        @page { size: landscape; margin: 12mm; }
      }`}</style>

      <div className="no-print flex items-center justify-between">
        <Link href="/dashboard/learn" className="text-sm font-bold text-[var(--primary)] hover:underline">← My Learning</Link>
        <button onClick={() => window.print()} className="btn-primary">🖨️ Print / Download PDF</button>
      </div>

      {/* The certificate */}
      <div id="certificate" className="relative mx-auto max-w-3xl overflow-hidden rounded-[var(--radius)] bg-white p-1 shadow-[var(--shadow-card)]">
        <div className="grad-primary p-1">
          <div className="relative bg-white px-8 py-12 text-center sm:px-14">
            {/* corner flourishes */}
            <div className="pointer-events-none absolute left-0 top-0 h-20 w-20 border-l-4 border-t-4 border-[var(--primary)]/40" />
            <div className="pointer-events-none absolute bottom-0 right-0 h-20 w-20 border-b-4 border-r-4 border-[var(--primary)]/40" />

            <div className="flex items-center justify-center gap-2 text-[var(--primary)]">
              <span className="grid h-9 w-9 place-items-center rounded-xl bg-[var(--primary)] text-lg text-white">✦</span>
              <span className="text-xl font-extrabold tracking-tight text-[var(--foreground)]">{brand}</span>
            </div>

            <p className="mt-8 text-xs font-bold uppercase tracking-[0.3em] text-[var(--muted)]">Certificate of Completion</p>
            <p className="mt-6 text-sm text-[var(--muted)]">This certifies that</p>
            <h1 className="mt-2 text-4xl font-extrabold text-[var(--foreground)]">{cert.student_name}</h1>

            <p className="mt-6 text-sm text-[var(--muted)]">has successfully completed</p>
            <h2 className="mt-2 text-2xl font-extrabold text-[var(--primary)]">{cert.course_title}</h2>
            <p className="mt-2 text-sm text-[var(--muted)]">
              {cert.lessons_count} lesson{cert.lessons_count === 1 ? "" : "s"} · Completed {cert.completed_date}
            </p>

            <div className="mt-10 flex items-end justify-between gap-6 text-left">
              <div>
                <p className="border-t border-[var(--border)] pt-2 text-sm font-bold text-[var(--foreground)]">{cert.instructor_name ?? "—"}</p>
                <p className="text-xs text-[var(--muted)]">Instructor</p>
              </div>
              <div className="text-right">
                <p className="border-t border-[var(--border)] pt-2 font-mono text-sm font-bold text-[var(--foreground)]">{cert.serial}</p>
                <p className="text-xs text-[var(--muted)]">Certificate ID</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
