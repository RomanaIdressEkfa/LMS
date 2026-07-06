"use client";

import { useEffect, useState } from "react";
import { apiGet, apiPost, apiPut, apiDelete, ApiError } from "@/lib/api";

interface Tenant {
  id: number;
  name: string;
  slug: string;
  owner_name: string | null;
  owner_email: string | null;
  plan: { id: number; name: string; price: string } | null;
  status: string;
  primary_color: string;
  effective_price: string | null;
  enabled_modules: string[];
}
interface Plan { id: number; name: string; price: string; }
interface ModuleDef { key: string; name: string; }

const STATUS: Record<string, string> = {
  active: "bg-[var(--success)]/15 text-[var(--success)]",
  trial: "bg-[var(--warning)]/15 text-[var(--warning)]",
  suspended: "bg-[var(--danger)]/15 text-[var(--danger)]",
};

export default function TenantsPage() {
  const [tenants, setTenants] = useState<Tenant[]>([]);
  const [plans, setPlans] = useState<Plan[]>([]);
  const [modules, setModules] = useState<ModuleDef[]>([]);
  const [loading, setLoading] = useState(true);
  const [managing, setManaging] = useState<Tenant | null>(null);
  const [showCreate, setShowCreate] = useState(false);

  async function load() {
    const [t, p, m] = await Promise.all([
      apiGet<{ tenants: Tenant[] }>("/tenants"),
      apiGet<{ plans: Plan[] }>("/plans"),
      apiGet<{ modules: ModuleDef[] }>("/modules"),
    ]);
    setTenants(t.tenants);
    setPlans(p.plans);
    setModules(m.modules);
    setLoading(false);
  }
  useEffect(() => { load(); }, []);

  if (loading) return <p className="text-[var(--muted)]">Loading tenants…</p>;

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-3xl">Tenants</h1>
          <p className="mt-1 text-[var(--muted)]">Your customer academies — set each one&apos;s plan, modules and price.</p>
        </div>
        <button onClick={() => setShowCreate(true)} className="btn-primary">+ New Tenant</button>
      </div>

      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        {tenants.map((t) => (
          <div key={t.id} className="card flex flex-col p-5">
            <div className="flex items-start justify-between">
              <div className="flex items-center gap-3">
                <div className="grid h-11 w-11 place-items-center rounded-[var(--radius)] text-lg font-bold text-white" style={{ background: t.primary_color }}>
                  {t.name.charAt(0)}
                </div>
                <div>
                  <h3 className="text-lg leading-tight">{t.name}</h3>
                  <p className="text-xs text-[var(--muted)]">{t.slug}</p>
                </div>
              </div>
              <span className={`rounded-full px-2.5 py-1 text-[11px] font-bold uppercase ${STATUS[t.status] ?? ""}`}>{t.status}</span>
            </div>
            <div className="mt-4 space-y-1 text-sm text-[var(--muted)]">
              <p>📦 Plan: <b className="text-[var(--foreground)]">{t.plan?.name ?? "—"}</b></p>
              <p>💵 Price: <b className="text-[var(--foreground)]">${t.effective_price ? Number(t.effective_price).toFixed(2) : "0.00"}</b></p>
              <p>🧩 {t.enabled_modules.length} modules enabled</p>
              {t.owner_email && <p>✉️ {t.owner_email}</p>}
            </div>
            <button onClick={() => setManaging(t)} className="btn-primary mt-4">Manage</button>
          </div>
        ))}
      </div>

      {managing && (
        <ManageTenant tenant={managing} plans={plans} modules={modules} onClose={() => setManaging(null)} onChange={load} />
      )}
      {showCreate && (
        <CreateTenant plans={plans} onClose={() => setShowCreate(false)} onCreated={() => { setShowCreate(false); load(); }} />
      )}
    </div>
  );
}

function ManageTenant({ tenant, plans, modules, onClose, onChange }: { tenant: Tenant; plans: Plan[]; modules: ModuleDef[]; onClose: () => void; onChange: () => void }) {
  const [enabled, setEnabled] = useState<string[]>(tenant.enabled_modules);
  const [price, setPrice] = useState(tenant.effective_price ?? "");
  const [planId, setPlanId] = useState(tenant.plan?.id ?? "");
  const [status, setStatus] = useState(tenant.status);
  const [msg, setMsg] = useState<string | null>(null);

  async function toggleModule(key: string) {
    const { enabled_modules } = await apiPost<{ enabled_modules: string[] }>(`/tenants/${tenant.id}/modules/toggle`, { key });
    setEnabled(enabled_modules);
    onChange();
  }
  async function saveDetails() {
    await apiPut(`/tenants/${tenant.id}`, { plan_id: planId || null, price_override: price === "" ? null : Number(price) });
    setMsg("Saved ✔"); onChange();
  }
  async function changeStatus(s: string) {
    await apiPost(`/tenants/${tenant.id}/status`, { status: s });
    setStatus(s); onChange();
  }
  async function remove() {
    if (!confirm(`Delete tenant "${tenant.name}"?`)) return;
    await apiDelete(`/tenants/${tenant.id}`); onChange(); onClose();
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onClick={onClose}>
      <div className="card max-h-[90vh] w-full max-w-lg overflow-y-auto p-6" onClick={(e) => e.stopPropagation()}>
        <div className="flex items-start justify-between">
          <h2 className="text-xl">Manage · {tenant.name}</h2>
          <button onClick={onClose} className="text-2xl leading-none text-[var(--muted)]">×</button>
        </div>

        <div className="mt-4 grid grid-cols-2 gap-3">
          <div>
            <label className="label">Plan</label>
            <select className="input" value={planId} onChange={(e) => setPlanId(e.target.value ? Number(e.target.value) : "")}>
              <option value="">Custom</option>
              {plans.map((p) => <option key={p.id} value={p.id}>{p.name} (${Number(p.price).toFixed(0)})</option>)}
            </select>
          </div>
          <div>
            <label className="label">Price override</label>
            <input type="number" min="0" step="0.01" className="input" placeholder="Plan price" value={price} onChange={(e) => setPrice(e.target.value)} />
          </div>
        </div>
        <button onClick={saveDetails} className="btn-primary mt-3 w-full">Save plan &amp; price</button>
        {msg && <p className="mt-2 text-center text-sm font-bold text-[var(--success)]">{msg}</p>}

        <p className="label mt-6">Enabled modules (per-tenant control)</p>
        <div className="grid grid-cols-2 gap-2">
          {modules.map((m) => {
            const on = enabled.includes(m.key);
            return (
              <button key={m.key} onClick={() => toggleModule(m.key)} className={`flex items-center justify-between rounded-[var(--radius)] border px-3 py-2 text-sm font-bold transition-colors ${on ? "border-[var(--success)] bg-[var(--success)]/5" : "border-[var(--border)]"}`}>
                {m.name}
                <span className={on ? "text-[var(--success)]" : "text-[var(--muted)]"}>{on ? "on" : "off"}</span>
              </button>
            );
          })}
        </div>

        <p className="label mt-6">Status</p>
        <div className="flex gap-2">
          {["trial", "active", "suspended"].map((s) => (
            <button key={s} onClick={() => changeStatus(s)} className={`flex-1 rounded-[var(--radius)] border px-3 py-2 text-sm font-bold capitalize transition-colors ${status === s ? "border-[var(--primary)] bg-[var(--primary)] text-white" : "border-[var(--border)]"}`}>
              {s}
            </button>
          ))}
        </div>

        <button onClick={remove} className="btn-ghost mt-6 w-full hover:border-[var(--danger)] hover:text-[var(--danger)]">Delete tenant</button>
      </div>
    </div>
  );
}

function CreateTenant({ plans, onClose, onCreated }: { plans: Plan[]; onClose: () => void; onCreated: () => void }) {
  const [form, setForm] = useState({ name: "", owner_name: "", owner_email: "", plan_id: "", primary_color: "#2563ff" });
  const [error, setError] = useState<string | null>(null);
  async function submit(e: React.FormEvent) {
    e.preventDefault();
    try { await apiPost("/tenants", { ...form, plan_id: form.plan_id ? Number(form.plan_id) : null }); onCreated(); }
    catch (err) { setError(err instanceof ApiError ? err.message : "Create failed"); }
  }
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onClick={onClose}>
      <form onSubmit={submit} className="card w-full max-w-md p-6" onClick={(e) => e.stopPropagation()}>
        <h2 className="text-xl">New tenant</h2>
        <div className="mt-4 space-y-3">
          <input className="input" placeholder="Academy name" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
          <input className="input" placeholder="Owner name" value={form.owner_name} onChange={(e) => setForm({ ...form, owner_name: e.target.value })} />
          <input type="email" className="input" placeholder="Owner email" value={form.owner_email} onChange={(e) => setForm({ ...form, owner_email: e.target.value })} />
          <div className="grid grid-cols-2 gap-3">
            <select className="input" value={form.plan_id} onChange={(e) => setForm({ ...form, plan_id: e.target.value })}>
              <option value="">Choose plan…</option>
              {plans.map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}
            </select>
            <input type="color" className="input h-[46px] p-1" value={form.primary_color} onChange={(e) => setForm({ ...form, primary_color: e.target.value })} />
          </div>
        </div>
        {error && <p className="mt-3 text-sm font-bold text-[var(--danger)]">{error}</p>}
        <button type="submit" className="btn-primary mt-5 w-full">Create tenant</button>
      </form>
    </div>
  );
}
