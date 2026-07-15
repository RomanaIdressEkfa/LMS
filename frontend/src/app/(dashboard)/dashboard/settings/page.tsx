"use client";

import { useEffect, useState } from "react";
import { apiGet, apiPut, apiUpload, apiDelete, ApiError } from "@/lib/api";
import { useAuth } from "@/lib/auth";
import { Logo } from "@/components/Logo";

interface SettingItem {
  key: string;
  label: string;
  group: string;
  type: "string" | "bool" | "color";
  value: string | boolean;
}

const GROUP_TITLES: Record<string, string> = {
  general: "General",
  auth: "Authentication",
  courses: "Courses",
  appearance: "Appearance",
};

export default function SettingsPage() {
  const { can } = useAuth();
  const [groups, setGroups] = useState<Record<string, SettingItem[]>>({});
  const [values, setValues] = useState<Record<string, string | boolean>>({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [msg, setMsg] = useState<string | null>(null);
  const [logoBusy, setLogoBusy] = useState(false);
  const [logoMsg, setLogoMsg] = useState<string | null>(null);

  const canManage = can("settings.manage");

  async function uploadLogo(file: File) {
    setLogoBusy(true);
    setLogoMsg(null);
    try {
      const fd = new FormData();
      fd.append("logo", file);
      await apiUpload("/settings/logo", fd);
      setLogoMsg("Uploaded ✔ reloading…");
      setTimeout(() => window.location.reload(), 700); // refresh so the new logo shows everywhere
    } catch (e) {
      setLogoMsg(e instanceof ApiError ? e.message : "Upload failed");
      setLogoBusy(false);
    }
  }

  async function removeLogo() {
    if (!confirm("Remove the custom logo and use the default?")) return;
    await apiDelete("/settings/logo");
    window.location.reload();
  }

  async function load() {
    const { settings } = await apiGet<{ settings: Record<string, SettingItem[]> }>("/settings");
    setGroups(settings);
    const flat: Record<string, string | boolean> = {};
    Object.values(settings).flat().forEach((s) => (flat[s.key] = s.value));
    setValues(flat);
    setLoading(false);
  }

  useEffect(() => {
    load();
  }, []);

  async function save(e: React.FormEvent) {
    e.preventDefault();
    setSaving(true);
    setMsg(null);
    try {
      await apiPut("/settings", { settings: values });
      setMsg("Settings saved ✔");
    } catch (err) {
      setMsg(err instanceof ApiError ? err.message : "Save failed");
    } finally {
      setSaving(false);
    }
  }

  if (loading) return <p className="text-[var(--muted)]">Loading settings…</p>;

  return (
    <form onSubmit={save} className="max-w-2xl space-y-6">
      <div>
        <h1 className="text-3xl">Settings</h1>
        <p className="mt-1 text-[var(--muted)]">Configure your platform.</p>
      </div>

      {/* Logo uploader */}
      {canManage && (
        <div className="card p-6">
          <h2 className="text-xl">Logo</h2>
          <p className="mt-1 text-sm text-[var(--muted)]">Upload your brand logo (PNG/SVG). Leave empty to use the default mark + name.</p>
          <div className="mt-4 flex flex-wrap items-center gap-4">
            <div className="flex h-16 items-center rounded-[var(--radius-sm)] border border-[var(--border)] bg-[var(--background)] px-5">
              <Logo size={34} />
            </div>
            <label className="btn-ghost cursor-pointer">
              {logoBusy ? "Uploading…" : "Upload logo"}
              <input type="file" accept="image/png,image/jpeg,image/svg+xml,image/webp" className="hidden"
                onChange={(e) => { const f = e.target.files?.[0]; if (f) uploadLogo(f); }} />
            </label>
            <button type="button" onClick={removeLogo} className="text-sm font-bold text-[var(--danger)] hover:underline">Remove logo</button>
            {logoMsg && <span className="text-sm font-bold text-[var(--success)]">{logoMsg}</span>}
          </div>
        </div>
      )}

      {Object.entries(groups).map(([group, items]) => (
        <div key={group} className="card p-6">
          <h2 className="text-xl">{GROUP_TITLES[group] ?? group}</h2>
          <div className="mt-4 space-y-4">
            {items.map((s) => (
              <div key={s.key} className={s.type === "bool" ? "flex items-center justify-between gap-4" : ""}>
                <label className={s.type === "bool" ? "text-sm font-bold text-[var(--foreground)]" : "label"}>{s.label}</label>
                {s.type === "bool" ? (
                  <button
                    type="button"
                    disabled={!canManage}
                    onClick={() => setValues({ ...values, [s.key]: !values[s.key] })}
                    className={`relative h-7 w-12 shrink-0 rounded-full transition-colors disabled:opacity-50 ${values[s.key] ? "bg-[var(--success)]" : "bg-[var(--muted)]/40"}`}
                  >
                    <span className={`absolute top-1 h-5 w-5 rounded-full bg-white transition-all ${values[s.key] ? "left-6" : "left-1"}`} />
                  </button>
                ) : s.type === "color" ? (
                  <div className="flex items-center gap-3">
                    <input
                      type="color"
                      disabled={!canManage}
                      value={String(values[s.key] ?? "#2563ff")}
                      onChange={(e) => setValues({ ...values, [s.key]: e.target.value })}
                      className="h-11 w-16 cursor-pointer rounded-lg border border-[var(--border)] bg-transparent p-1"
                    />
                    <input
                      className="input max-w-[140px]"
                      disabled={!canManage}
                      value={String(values[s.key] ?? "")}
                      onChange={(e) => setValues({ ...values, [s.key]: e.target.value })}
                    />
                    <span className="h-8 w-8 rounded-lg" style={{ background: String(values[s.key] ?? "#2563ff") }} />
                  </div>
                ) : (
                  <input
                    className="input"
                    disabled={!canManage}
                    value={String(values[s.key] ?? "")}
                    onChange={(e) => setValues({ ...values, [s.key]: e.target.value })}
                  />
                )}
              </div>
            ))}
          </div>
        </div>
      ))}

      {canManage && (
        <div className="flex items-center gap-3">
          <button type="submit" disabled={saving} className="btn-primary disabled:opacity-60">
            {saving ? "Saving…" : "Save settings"}
          </button>
          {msg && <span className="text-sm font-bold text-[var(--success)]">{msg}</span>}
        </div>
      )}
      {!canManage && (
        <p className="text-sm font-bold text-[var(--warning)]">You can view settings but need <code>settings.manage</code> to change them.</p>
      )}
    </form>
  );
}
