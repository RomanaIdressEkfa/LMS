"use client";

import { useState } from "react";
import { useContent, loc } from "@/lib/content";
import { useLang } from "@/lib/i18n";

export default function ContactPage() {
  const { contact } = useContent();
  const { lang } = useLang();
  const { hero, channels: CHANNELS } = contact;
  const [form, setForm] = useState({ name: "", email: "", message: "" });
  const [sent, setSent] = useState(false);

  function submit(e: React.FormEvent) {
    e.preventDefault();
    // Front-end demo: acknowledge the message. Wire to an API/email later.
    setSent(true);
  }

  return (
    <div>
      <section className="grad-hero border-b border-[var(--border)]">
        <div className="mx-auto max-w-4xl px-5 py-14 text-center md:px-8">
          <h1 className="text-4xl md:text-5xl">{loc(hero.titleA, lang)} <span className="gradient-text">{loc(hero.titleHl, lang)}</span></h1>
          <p className="mx-auto mt-3 max-w-xl text-lg font-semibold text-[var(--muted)]">{loc(hero.subtitle, lang)}</p>
        </div>
      </section>

      <div className="mx-auto grid max-w-6xl gap-8 px-5 py-14 md:px-8 lg:grid-cols-[1fr_1.3fr]">
        {/* channels */}
        <div className="space-y-4">
          {CHANNELS.map((c, i) => (
            <div key={i} className="card flex items-center gap-4 p-5">
              <div className={`grid h-12 w-12 shrink-0 place-items-center rounded-2xl text-xl text-white ${c.grad}`}>{c.icon}</div>
              <div>
                <p className="font-extrabold">{loc(c.title, lang)}</p>
                <p className="text-sm font-semibold text-[var(--muted)]">{loc(c.value, lang)}</p>
              </div>
            </div>
          ))}
        </div>

        {/* form */}
        <div className="card p-8">
          {sent ? (
            <div className="grid place-items-center py-12 text-center">
              <span className="text-5xl">🎉</span>
              <h2 className="mt-4 text-2xl">Message sent!</h2>
              <p className="mt-2 font-semibold text-[var(--muted)]">Thanks {form.name || "there"} — we&apos;ll get back to you soon.</p>
              <button onClick={() => { setSent(false); setForm({ name: "", email: "", message: "" }); }} className="btn-ghost mt-6">Send another</button>
            </div>
          ) : (
            <form onSubmit={submit} className="space-y-4">
              <h2 className="text-2xl">Send us a message</h2>
              <div className="grid gap-4 sm:grid-cols-2">
                <div>
                  <label className="label">Your name</label>
                  <input className="input" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
                </div>
                <div>
                  <label className="label">Email</label>
                  <input type="email" className="input" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required />
                </div>
              </div>
              <div>
                <label className="label">Message</label>
                <textarea className="input min-h-36" value={form.message} onChange={(e) => setForm({ ...form, message: e.target.value })} required />
              </div>
              <button type="submit" className="btn-primary w-full">Send message</button>
            </form>
          )}
        </div>
      </div>
    </div>
  );
}
