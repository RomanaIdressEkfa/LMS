import { BRAND } from "@/lib/brand";

/**
 * Nova LMS wordmark + badge. The badge is a hexagon with a spark/star,
 * echoing a confident "launch your learning" motif. Pure SVG, no assets.
 */
export function Logo({
  size = 34,
  showText = true,
  className = "",
}: {
  size?: number;
  showText?: boolean;
  className?: string;
}) {
  return (
    <span className={`inline-flex items-center gap-2.5 ${className}`}>
      <svg
        width={size}
        height={size}
        viewBox="0 0 40 40"
        fill="none"
        aria-hidden="true"
        className="shrink-0"
      >
        <defs>
          <linearGradient id="nova-g" x1="4" y1="2" x2="36" y2="38" gradientUnits="userSpaceOnUse">
            <stop stopColor="#3b82f6" />
            <stop offset="1" stopColor="#1d4ed8" />
          </linearGradient>
        </defs>
        <path
          d="M20 1.5l14.7 8.5a4 4 0 012 3.46v13.08a4 4 0 01-2 3.46L20 38.5 5.3 30A4 4 0 013.3 26.54V13.46A4 4 0 015.3 10L20 1.5z"
          fill="url(#nova-g)"
        />
        {/* four-point spark */}
        <path
          d="M20 9l2.6 6.9c.24.63.87 1.26 1.5 1.5L31 20l-6.9 2.6c-.63.24-1.26.87-1.5 1.5L20 31l-2.6-6.9c-.24-.63-.87-1.26-1.5-1.5L9 20l6.9-2.6c.63-.24 1.26-.87 1.5-1.5L20 9z"
          fill="#fff"
        />
      </svg>
      {showText && (
        <span className="font-display text-[1.2rem] font-extrabold leading-none tracking-tight text-[var(--foreground)]">
          {BRAND.name}
        </span>
      )}
    </span>
  );
}
