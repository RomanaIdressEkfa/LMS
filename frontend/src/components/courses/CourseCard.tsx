import Link from "next/link";
import type { Course } from "@/lib/types";

export function CourseCard({ course }: { course: Course }) {
  const price = Number(course.price);
  return (
    <Link
      href={`/dashboard/courses/${course.slug}`}
      className="card group flex flex-col overflow-hidden transition-transform hover:-translate-y-1"
    >
      {/* Thumbnail / gradient placeholder */}
      <div
        className="relative h-40 w-full"
        style={{
          background: course.category
            ? `linear-gradient(135deg, ${course.category.color}, #1d4ed8)`
            : "linear-gradient(135deg, #2563ff, #1d4ed8)",
        }}
      >
        <div className="absolute inset-0 flex items-center justify-center text-white/90">
          <span className="text-4xl">🎓</span>
        </div>
        <span className="absolute left-3 top-3 rounded-full bg-black/25 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-white backdrop-blur">
          {course.level}
        </span>
        <span
          className={`absolute right-3 top-3 rounded-full px-2.5 py-1 text-[11px] font-bold ${
            course.is_free ? "bg-[var(--success)] text-white" : "bg-white text-[var(--primary)]"
          }`}
        >
          {course.is_free ? "FREE" : `$${price.toFixed(2)}`}
        </span>
      </div>

      <div className="flex flex-1 flex-col p-5">
        {course.category && (
          <span className="text-xs font-bold uppercase tracking-wide" style={{ color: course.category.color }}>
            {course.category.name}
          </span>
        )}
        <h3 className="mt-1 line-clamp-2 text-lg leading-tight text-[var(--foreground)]">
          {course.title}
        </h3>
        {course.subtitle && (
          <p className="mt-1 line-clamp-2 flex-1 text-sm text-[var(--muted)]">{course.subtitle}</p>
        )}

        <div className="mt-4 flex items-center justify-between border-t border-[var(--border)] pt-3 text-xs text-[var(--muted)]">
          <span className="font-bold text-[var(--foreground)]">
            {course.teacher?.name ?? "Instructor"}
          </span>
          <span>{course.lessons_count ?? 0} lessons</span>
        </div>
      </div>
    </Link>
  );
}
