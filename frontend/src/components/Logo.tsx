"use client";

import { BRAND } from "@/lib/brand";
import { useSiteConfig } from "@/lib/site";

/**
 * Brand logo. If the admin has uploaded a custom logo it is shown; otherwise
 * the default SVG spark mark + site name is used. The name comes from the
 * admin-configured `site_name` setting.
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
  const { settings } = useSiteConfig();
  const name = settings.site_name || BRAND.name;

  // Custom uploaded logo (the brandmark usually includes its own text).
  if (settings.site_logo) {
    return (
      <span className={`inline-flex items-center ${className}`}>
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img src={settings.site_logo} alt={name} style={{ height: size * 1.05, maxWidth: 180 }} className="w-auto object-contain" />
      </span>
    );
  }

  return (
    <span className={`inline-flex items-center gap-2.5 ${className}`}>
      <svg width={size} height={size} viewBox="0 0 40 40" fill="none" aria-hidden="true" className="shrink-0">
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
        <path
          d="M20 9l2.6 6.9c.24.63.87 1.26 1.5 1.5L31 20l-6.9 2.6c-.63.24-1.26.87-1.5 1.5L20 31l-2.6-6.9c-.24-.63-.87-1.26-1.5-1.5L9 20l6.9-2.6c.63-.24 1.26-.87 1.5-1.5L20 9z"
          fill="#fff"
        />
      </svg>
      {showText && (
        <span className="font-display text-[1.2rem] font-extrabold leading-none tracking-tight text-[var(--foreground)]">
          {name}
        </span>
      )}
    </span>
  );
}
