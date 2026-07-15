"use client";

import { useEffect, useState } from "react";
import { apiGet, apiPut, ApiError } from "@/lib/api";
import { useAuth } from "@/lib/auth";
import { CONTENT_DEFAULTS, type SiteContent, type Loc, type Hero } from "@/lib/content";
import { DICT } from "@/lib/i18n";

/* Curated homepage i18n keys exposed for bilingual editing, grouped for the UI. */
const TEXT_GROUPS: { title: string; keys: [string, string][] }[] = [
  { title: "Hero", keys: [
    ["ph.badge", "Badge"], ["ph.h1a", "Headline start"], ["ph.h1b", "Headline highlight"], ["ph.h1c", "Headline end"],
    ["ph.heroSub", "Subtitle"], ["ph.cta1", "Primary button"], ["ph.cta2", "Secondary button"],
    ["ph.badge1", "Pill 1"], ["ph.badge2", "Pill 2"], ["ph.badge3", "Pill 3"], ["ph.techLabel", "Tech strip label"],
  ] },
  { title: "Section titles", keys: [
    ["ph.whyTitle", "Why — title"], ["ph.whySub", "Why — subtitle"], ["ph.statsTitle", "Stats — title"],
    ["ph.techTitle", "Tech — title"], ["ph.techSub", "Tech — subtitle"], ["ph.storiesTitle", "Stories — title"],
    ["ph.supportTitle", "Support — title"], ["ph.faqTitle", "FAQ — title"],
    ["ph.finalTitle", "Final CTA — title"], ["ph.finalSub", "Final CTA — subtitle"],
    ["home.featured", "Featured courses — title"], ["home.seeAll", "Featured — see-all link"],
  ] },
  { title: "Why-learn cards", keys: [
    ["ph.why1", "Card 1 title"], ["ph.why1d", "Card 1 text"], ["ph.why2", "Card 2 title"], ["ph.why2d", "Card 2 text"],
    ["ph.why3", "Card 3 title"], ["ph.why3d", "Card 3 text"], ["ph.why4", "Card 4 title"], ["ph.why4d", "Card 4 text"],
  ] },
  { title: "Support cards", keys: [
    ["ph.sup1", "Card 1 title"], ["ph.sup1d", "Card 1 text"], ["ph.sup2", "Card 2 title"], ["ph.sup2d", "Card 2 text"],
    ["ph.sup3", "Card 3 title"], ["ph.sup3d", "Card 3 text"], ["ph.sup4", "Card 4 title"], ["ph.sup4d", "Card 4 text"],
    ["ph.sup5", "Card 5 title"], ["ph.sup5d", "Card 5 text"], ["ph.sup6", "Card 6 title"], ["ph.sup6d", "Card 6 text"],
  ] },
  { title: "FAQ", keys: [
    ["ph.faqQ1", "Q1"], ["ph.faqA1", "A1"], ["ph.faqQ2", "Q2"], ["ph.faqA2", "A2"],
    ["ph.faqQ3", "Q3"], ["ph.faqA3", "A3"], ["ph.faqQ4", "Q4"], ["ph.faqA4", "A4"],
  ] },
];

type TextPair = { en: string; bn: string };

/* ---- Option lists for the styling dropdowns (match globals.css helpers) ---- */
const GRADS = ["grad-primary", "grad-purple", "grad-sunset", "grad-teal", "grad-ph", "grad-magenta"];
const PASTELS = ["pastel-purple", "pastel-blue", "pastel-green", "pastel-pink", "pastel-amber", "pastel-cyan"];

type Row = Record<string, string | Loc>;
interface FieldDef {
  key: string;
  label: string;
  type?: "text" | "textarea" | "grad" | "pastel";
  bilingual?: boolean; // prose field stored as { en, bn }
}

const asStr = (v: string | Loc | undefined): string => (typeof v === "string" ? v : "");
const asLoc = (v: string | Loc | undefined): Loc =>
  v && typeof v === "object" ? v : { en: typeof v === "string" ? v : "", bn: "" };

/* A labelled bilingual field: English + Bangla inputs editing a { en, bn } value. */
function LocField({ label, value, onChange, textarea }: { label: string; value: Loc; onChange: (v: Loc) => void; textarea?: boolean }) {
  const Input = textarea ? "textarea" : "input";
  return (
    <div>
      <label className="label">{label}</label>
      <div className="grid gap-2 sm:grid-cols-2">
        <Input className={`input ${textarea ? "min-h-16" : ""}`} placeholder="English" value={value.en} onChange={(e) => onChange({ ...value, en: e.target.value })} />
        <Input className={`input ${textarea ? "min-h-16" : ""}`} placeholder="বাংলা" value={value.bn} onChange={(e) => onChange({ ...value, bn: e.target.value })} />
      </div>
    </div>
  );
}

/* A repeatable list of object rows: add / remove / move, each row = fields. */
function ListEditor({ title, items, fields, blank, onChange }: {
  title: string;
  items: Row[];
  fields: FieldDef[];
  blank: () => Row;
  onChange: (items: Row[]) => void;
}) {
  const setVal = (i: number, key: string, val: string | Loc) => {
    const next = items.map((r, idx) => (idx === i ? { ...r, [key]: val } : r));
    onChange(next);
  };
  const remove = (i: number) => onChange(items.filter((_, idx) => idx !== i));
  const move = (i: number, dir: -1 | 1) => {
    const j = i + dir;
    if (j < 0 || j >= items.length) return;
    const next = [...items];
    [next[i], next[j]] = [next[j], next[i]];
    onChange(next);
  };

  return (
    <div>
      <div className="flex items-center justify-between">
        <h3 className="text-lg">{title}</h3>
        <button type="button" onClick={() => onChange([...items, blank()])} className="btn-ghost !py-1.5 !px-3 text-sm">+ Add</button>
      </div>
      <div className="mt-3 space-y-3">
        {items.length === 0 && <p className="text-sm text-[var(--muted)]">No items. Click “Add” to create one.</p>}
        {items.map((row, i) => (
          <div key={i} className="rounded-[var(--radius-sm)] border border-[var(--border)] bg-[var(--background)] p-4">
            <div className="mb-2 flex items-center justify-between">
              <span className="text-xs font-bold uppercase tracking-wide text-[var(--muted)]">#{i + 1}</span>
              <div className="flex items-center gap-1">
                <button type="button" onClick={() => move(i, -1)} disabled={i === 0} className="rounded px-2 py-1 text-sm font-bold text-[var(--muted)] hover:bg-[var(--surface)] disabled:opacity-30">↑</button>
                <button type="button" onClick={() => move(i, 1)} disabled={i === items.length - 1} className="rounded px-2 py-1 text-sm font-bold text-[var(--muted)] hover:bg-[var(--surface)] disabled:opacity-30">↓</button>
                <button type="button" onClick={() => remove(i)} className="rounded px-2 py-1 text-sm font-bold text-[var(--danger)] hover:bg-[var(--danger)]/10">Remove</button>
              </div>
            </div>
            <div className="grid gap-3 sm:grid-cols-2">
              {fields.map((f) => (
                <div key={f.key} className={f.type === "textarea" || f.bilingual ? "sm:col-span-2" : ""}>
                  {f.bilingual ? (
                    <LocField label={f.label} textarea={f.type === "textarea"} value={asLoc(row[f.key])} onChange={(v) => setVal(i, f.key, v)} />
                  ) : (
                    <>
                      <label className="label">{f.label}</label>
                      {f.type === "grad" || f.type === "pastel" ? (
                        <select className="input" value={asStr(row[f.key])} onChange={(e) => setVal(i, f.key, e.target.value)}>
                          {(f.type === "grad" ? GRADS : PASTELS).map((o) => <option key={o} value={o}>{o}</option>)}
                        </select>
                      ) : f.type === "textarea" ? (
                        <textarea className="input min-h-16" value={asStr(row[f.key])} onChange={(e) => setVal(i, f.key, e.target.value)} />
                      ) : (
                        <input className="input" value={asStr(row[f.key])} onChange={(e) => setVal(i, f.key, e.target.value)} />
                      )}
                    </>
                  )}
                </div>
              ))}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

/* Editor for a plain string[] (the homepage tech strip). */
function StringListEditor({ title, items, onChange }: { title: string; items: string[]; onChange: (v: string[]) => void }) {
  return (
    <div>
      <div className="flex items-center justify-between">
        <h3 className="text-lg">{title}</h3>
        <button type="button" onClick={() => onChange([...items, ""])} className="btn-ghost !py-1.5 !px-3 text-sm">+ Add</button>
      </div>
      <div className="mt-3 flex flex-wrap gap-2">
        {items.map((v, i) => (
          <div key={i} className="flex items-center gap-1 rounded-[var(--radius-sm)] border border-[var(--border)] bg-[var(--background)] p-1.5">
            <input className="input !py-1.5 !px-2 max-w-[130px] text-sm" value={v} onChange={(e) => onChange(items.map((x, idx) => (idx === i ? e.target.value : x)))} />
            <button type="button" onClick={() => onChange(items.filter((_, idx) => idx !== i))} className="px-1.5 text-[var(--danger)]">✕</button>
          </div>
        ))}
      </div>
    </div>
  );
}

const TABS = ["home", "about", "pricing", "instructors", "contact", "text"] as const;
type Tab = (typeof TABS)[number];
const TAB_LABELS: Record<Tab, string> = { home: "Home", about: "About", pricing: "Pricing", instructors: "Instructors", contact: "Contact", text: "Text (EN/BN)" };

export default function ContentPage() {
  const { can } = useAuth();
  const canManage = can("settings.manage");
  const [content, setContent] = useState<SiteContent | null>(null);
  const [text, setText] = useState<Record<string, TextPair>>({});
  const [tab, setTab] = useState<Tab>("home");
  const [saving, setSaving] = useState(false);
  const [msg, setMsg] = useState<string | null>(null);

  useEffect(() => {
    apiGet<{ content: SiteContent; text?: Record<string, TextPair> }>("/content")
      .then((d) => {
        setContent(d.content ?? CONTENT_DEFAULTS);
        if (d.text && !Array.isArray(d.text)) setText(d.text);
      })
      .catch(() => setContent(CONTENT_DEFAULTS));
  }, []);

  if (!content) return <p className="text-[var(--muted)]">Loading content…</p>;

  // Immutable helper: replace one nested section by key path.
  const patch = (updater: (c: SiteContent) => SiteContent) => setContent(updater(structuredClone(content)));

  // Effective bilingual value for a key: saved override wins, else the dictionary.
  const pair = (key: string): TextPair => ({
    en: text[key]?.en ?? DICT[key]?.en ?? "",
    bn: text[key]?.bn ?? DICT[key]?.bn ?? "",
  });
  const setPair = (key: string, side: "en" | "bn", val: string) =>
    setText((prev) => ({ ...prev, [key]: { ...pair(key), [side]: val } }));

  async function save(e: React.FormEvent) {
    e.preventDefault();
    setSaving(true);
    setMsg(null);
    try {
      if (tab === "text") {
        // Only send keys that actually differ from the built-in dictionary.
        const diff: Record<string, TextPair> = {};
        Object.keys(text).forEach((k) => {
          const p = pair(k);
          if (p.en !== (DICT[k]?.en ?? "") || p.bn !== (DICT[k]?.bn ?? "")) diff[k] = p;
        });
        const { text: saved } = await apiPut<{ text: Record<string, TextPair> }>("/content/text", { text: diff });
        setText(Array.isArray(saved) ? {} : saved);
        setMsg("Text saved ✔ — refresh a public page to see it live.");
      } else {
        const { content: saved } = await apiPut<{ content: SiteContent }>("/content", { content });
        setContent(saved);
        setMsg("Content saved ✔ — refresh a public page to see it live.");
      }
    } catch (err) {
      setMsg(err instanceof ApiError ? err.message : "Save failed");
    } finally {
      setSaving(false);
    }
  }

  const heroFields = (page: "about" | "pricing" | "instructors" | "contact", hero: Hero) => (
    <div className="card p-6">
      <h3 className="text-lg">Hero</h3>
      <div className="mt-3 space-y-3">
        <LocField label="Title (normal)" value={hero.titleA} onChange={(v) => patch((c) => { c[page].hero.titleA = v; return c; })} />
        <LocField label="Title (highlighted)" value={hero.titleHl} onChange={(v) => patch((c) => { c[page].hero.titleHl = v; return c; })} />
        <LocField label="Subtitle" textarea value={hero.subtitle} onChange={(v) => patch((c) => { c[page].hero.subtitle = v; return c; })} />
      </div>
    </div>
  );

  return (
    <form onSubmit={save} className="max-w-3xl space-y-6">
      <div>
        <h1 className="text-3xl">Site Content</h1>
        <p className="mt-1 text-[var(--muted)]">Edit the marketing content shown on your public pages. Changes go live on save.</p>
      </div>

      {!canManage && (
        <p className="text-sm font-bold text-[var(--warning)]">You can view content but need <code>settings.manage</code> to change it.</p>
      )}

      {/* Page tabs */}
      <div className="flex flex-wrap gap-2">
        {TABS.map((tb) => (
          <button key={tb} type="button" onClick={() => setTab(tb)}
            className={`rounded-[var(--radius-sm)] px-4 py-2 text-sm font-bold transition-colors ${tab === tb ? "bg-[var(--primary)] text-white" : "bg-[var(--surface)] text-[var(--foreground)] border border-[var(--border)] hover:border-[var(--primary)]"}`}>
            {TAB_LABELS[tb]}
          </button>
        ))}
      </div>

      <fieldset disabled={!canManage} className="space-y-6 disabled:opacity-70">
        {/* ---------- HOME ---------- */}
        {tab === "home" && (
          <>
            <div className="card p-6">
              <StringListEditor title="Tech strip (logos row)" items={content.home.techStrip} onChange={(v) => patch((c) => { c.home.techStrip = v; return c; })} />
            </div>
            <div className="card p-6">
              <ListEditor title="Success stats band" items={content.home.stats}
                fields={[{ key: "v", label: "Value (e.g. 75%)" }, { key: "l", label: "Label", bilingual: true }]}
                blank={() => ({ v: "", l: { en: "", bn: "" } })}
                onChange={(v) => patch((c) => { c.home.stats = v as unknown as typeof c.home.stats; return c; })} />
            </div>
            <div className="card p-6">
              <ListEditor title="Technology grid" items={content.home.stack}
                fields={[{ key: "icon", label: "Icon (emoji)" }, { key: "name", label: "Name" }, { key: "d", label: "Description", type: "textarea", bilingual: true }, { key: "bg", label: "Card colour", type: "pastel" }]}
                blank={() => ({ icon: "⭐", name: "", d: { en: "", bn: "" }, bg: "pastel-blue" })}
                onChange={(v) => patch((c) => { c.home.stack = v as unknown as typeof c.home.stack; return c; })} />
            </div>
            <div className="card p-6">
              <ListEditor title="Success stories" items={content.home.stories}
                fields={[{ key: "name", label: "Name" }, { key: "role", label: "Role", bilingual: true }, { key: "grad", label: "Avatar colour", type: "grad" }, { key: "text", label: "Quote", type: "textarea", bilingual: true }]}
                blank={() => ({ name: "", role: { en: "", bn: "" }, grad: "grad-primary", text: { en: "", bn: "" } })}
                onChange={(v) => patch((c) => { c.home.stories = v as unknown as typeof c.home.stories; return c; })} />
            </div>
          </>
        )}

        {/* ---------- ABOUT ---------- */}
        {tab === "about" && (
          <>
            {heroFields("about", content.about.hero)}
            <div className="card p-6">
              <ListEditor title="Values" items={content.about.values}
                fields={[{ key: "icon", label: "Icon (emoji)" }, { key: "title", label: "Title", bilingual: true }, { key: "text", label: "Text", type: "textarea", bilingual: true }, { key: "grad", label: "Colour", type: "grad" }]}
                blank={() => ({ icon: "⭐", title: { en: "", bn: "" }, text: { en: "", bn: "" }, grad: "grad-primary" })}
                onChange={(v) => patch((c) => { c.about.values = v as unknown as typeof c.about.values; return c; })} />
            </div>
            <div className="card p-6">
              <ListEditor title="How it works (steps)" items={content.about.steps}
                fields={[{ key: "n", label: "Number" }, { key: "title", label: "Title", bilingual: true }, { key: "text", label: "Text", type: "textarea", bilingual: true }]}
                blank={() => ({ n: String(content.about.steps.length + 1), title: { en: "", bn: "" }, text: { en: "", bn: "" } })}
                onChange={(v) => patch((c) => { c.about.steps = v as unknown as typeof c.about.steps; return c; })} />
            </div>
            <div className="card p-6">
              <ListEditor title="Stats band" items={content.about.stats}
                fields={[{ key: "v", label: "Value" }, { key: "l", label: "Label", bilingual: true }]}
                blank={() => ({ v: "", l: { en: "", bn: "" } })}
                onChange={(v) => patch((c) => { c.about.stats = v as unknown as typeof c.about.stats; return c; })} />
            </div>
          </>
        )}

        {/* ---------- PRICING ---------- */}
        {tab === "pricing" && (
          <>
            {heroFields("pricing", content.pricing.hero)}
            <div className="card p-6">
              <LocField label="Footnote (below the plans)" textarea value={content.pricing.footnote} onChange={(v) => patch((c) => { c.pricing.footnote = v; return c; })} />
              <p className="mt-2 text-xs text-[var(--muted)]">The plans themselves come from Platform → Plans.</p>
            </div>
          </>
        )}

        {/* ---------- INSTRUCTORS ---------- */}
        {tab === "instructors" && (
          <>
            {heroFields("instructors", content.instructors.hero)}
            <div className="card p-6">
              <h3 className="text-lg">“Become an instructor” banner</h3>
              <div className="mt-3 space-y-3">
                <LocField label="Title" value={content.instructors.cta.title} onChange={(v) => patch((c) => { c.instructors.cta.title = v; return c; })} />
                <LocField label="Button text" value={content.instructors.cta.button} onChange={(v) => patch((c) => { c.instructors.cta.button = v; return c; })} />
                <LocField label="Text" textarea value={content.instructors.cta.text} onChange={(v) => patch((c) => { c.instructors.cta.text = v; return c; })} />
              </div>
              <p className="mt-2 text-xs text-[var(--muted)]">The instructor list is generated from real teacher accounts.</p>
            </div>
          </>
        )}

        {/* ---------- CONTACT ---------- */}
        {tab === "contact" && (
          <>
            {heroFields("contact", content.contact.hero)}
            <div className="card p-6">
              <ListEditor title="Contact channels" items={content.contact.channels}
                fields={[{ key: "icon", label: "Icon (emoji)" }, { key: "title", label: "Title", bilingual: true }, { key: "value", label: "Value", bilingual: true }, { key: "grad", label: "Colour", type: "grad" }]}
                blank={() => ({ icon: "✉️", title: { en: "", bn: "" }, value: { en: "", bn: "" }, grad: "grad-primary" })}
                onChange={(v) => patch((c) => { c.contact.channels = v as unknown as typeof c.contact.channels; return c; })} />
            </div>
          </>
        )}

        {/* ---------- TEXT (bilingual) ---------- */}
        {tab === "text" && (
          <>
            <p className="text-sm text-[var(--muted)]">
              Homepage headline, Why / Support / FAQ and section titles — edit English and Bangla.
              Leave a field on its shipped default to keep the built-in wording.
            </p>
            {TEXT_GROUPS.map((g) => (
              <div key={g.title} className="card p-6">
                <h3 className="text-lg">{g.title}</h3>
                <div className="mt-4 space-y-4">
                  {g.keys.map(([key, label]) => {
                    const p = pair(key);
                    const long = p.en.length > 40 || p.bn.length > 40;
                    return (
                      <div key={key}>
                        <label className="label">{label}</label>
                        <div className="grid gap-2 sm:grid-cols-2">
                          {long ? (
                            <>
                              <textarea className="input min-h-16" placeholder="English" value={p.en} onChange={(e) => setPair(key, "en", e.target.value)} />
                              <textarea className="input min-h-16" placeholder="বাংলা" value={p.bn} onChange={(e) => setPair(key, "bn", e.target.value)} />
                            </>
                          ) : (
                            <>
                              <input className="input" placeholder="English" value={p.en} onChange={(e) => setPair(key, "en", e.target.value)} />
                              <input className="input" placeholder="বাংলা" value={p.bn} onChange={(e) => setPair(key, "bn", e.target.value)} />
                            </>
                          )}
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>
            ))}
          </>
        )}
      </fieldset>

      {canManage && (
        <div className="sticky bottom-4 flex items-center gap-3 rounded-[var(--radius)] border border-[var(--border)] bg-[var(--surface)]/95 p-4 shadow-[var(--shadow-card)] backdrop-blur">
          <button type="submit" disabled={saving} className="btn-primary disabled:opacity-60">{saving ? "Saving…" : tab === "text" ? "Save text" : "Save all content"}</button>
          {msg && <span className="text-sm font-bold text-[var(--success)]">{msg}</span>}
        </div>
      )}
    </form>
  );
}
