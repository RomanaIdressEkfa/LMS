"use client";

import { createContext, useContext, useEffect, useState, ReactNode } from "react";
import { apiGet } from "./api";

export interface SiteSettings {
  site_name: string;
  site_logo: string | null;
  site_tagline: string;
  primary_color: string;
  home_show_stats: boolean;
  home_show_tech: boolean;
  home_show_stories: boolean;
  home_show_support: boolean;
  home_show_faq: boolean;
  [key: string]: unknown;
}

const DEFAULTS: SiteSettings = {
  site_name: "LMS",
  site_logo: null,
  site_tagline: "Learn Without Limits",
  primary_color: "#2563ff",
  home_show_stats: true,
  home_show_tech: true,
  home_show_stories: true,
  home_show_support: true,
  home_show_faq: true,
};

const Ctx = createContext<{ settings: SiteSettings; loading: boolean }>({ settings: DEFAULTS, loading: true });

/** Darken a #hex by a factor (0..1) for hover/active states. */
function darken(hex: string, f = 0.15): string {
  const m = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  if (!m) return hex;
  const c = [1, 2, 3].map((i) => Math.max(0, Math.round(parseInt(m[i], 16) * (1 - f))));
  return `#${c.map((v) => v.toString(16).padStart(2, "0")).join("")}`;
}

export function SiteConfigProvider({ children }: { children: ReactNode }) {
  const [settings, setSettings] = useState<SiteSettings>(DEFAULTS);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    apiGet<{ settings: Partial<SiteSettings> }>("/bootstrap")
      .then((d) => {
        const merged = { ...DEFAULTS, ...d.settings } as SiteSettings;
        setSettings(merged);
        // Apply the brand colour live across the whole app.
        if (merged.primary_color) {
          const root = document.documentElement.style;
          root.setProperty("--primary", merged.primary_color);
          root.setProperty("--primary-hover", darken(merged.primary_color, 0.15));
        }
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  }, []);

  return <Ctx.Provider value={{ settings, loading }}>{children}</Ctx.Provider>;
}

export function useSiteConfig() {
  return useContext(Ctx);
}
