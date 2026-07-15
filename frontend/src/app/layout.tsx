import type { Metadata } from "next";
import { Sora, Hind_Siliguri } from "next/font/google";
import "./globals.css";
import { BRAND } from "@/lib/brand";
import { AuthProvider } from "@/lib/auth";
import { LanguageProvider } from "@/lib/i18n";
import { SiteConfigProvider } from "@/lib/site";
import { ContentProvider } from "@/lib/content";

const sora = Sora({
  variable: "--font-sans",
  subsets: ["latin"],
  weight: ["400", "500", "600", "700", "800"],
  display: "swap",
});

// Bangla font — browser uses this for Bengali glyphs (Sora has none).
const hind = Hind_Siliguri({
  variable: "--font-bn",
  subsets: ["bengali", "latin"],
  weight: ["400", "500", "600", "700"],
  display: "swap",
});

export const metadata: Metadata = {
  title: {
    default: `${BRAND.name} — ${BRAND.tagline}`,
    template: `%s · ${BRAND.name}`,
  },
  description: BRAND.description,
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" className={`${sora.variable} ${hind.variable} h-full antialiased`} suppressHydrationWarning>
      <body className="min-h-full flex flex-col font-sans font-bold" suppressHydrationWarning>
        <SiteConfigProvider>
          <ContentProvider>
            <LanguageProvider>
              <AuthProvider>{children}</AuthProvider>
            </LanguageProvider>
          </ContentProvider>
        </SiteConfigProvider>
      </body>
    </html>
  );
}
