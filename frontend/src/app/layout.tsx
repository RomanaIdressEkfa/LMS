import type { Metadata } from "next";
import { Sora } from "next/font/google";
import "./globals.css";
import { BRAND } from "@/lib/brand";
import { AuthProvider } from "@/lib/auth";

const sora = Sora({
  variable: "--font-sans",
  subsets: ["latin"],
  weight: ["400", "500", "600", "700", "800"],
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
    <html lang="en" className={`${sora.variable} h-full antialiased`}>
      <body className="min-h-full flex flex-col font-sans font-bold">
        <AuthProvider>{children}</AuthProvider>
      </body>
    </html>
  );
}
