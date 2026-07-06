"use client";

import { useEffect, useState } from "react";
import { apiGet, apiPost, apiPut, apiDelete, ApiError } from "@/lib/api";

interface Plan {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  price: string;
  interval: string;
  module_keys: string[];
  is_active: boolean;
  tenants_count: number;
}
interface ModuleDef { id: number; key: string; name: string; }

const BLANK = { name: "", description: "", price: "0", interval: "monthly", module_keys: [] as string[], is_active: true };

export default function PlansPage() {
  const [plans, setPlans] = useState<Plan[]>([]);
  const [modules, setModules] = useState<ModuleDef[]>([]);
  const [loading, setLoading] = useState(true);
  const [editing, setEditing] = useState<Plan | null>(null);
  const [form, setForm] = useState(BLANK);
  const [showForm, setShowForm] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function load() {
    const [p, m] = await Promise.all([
      apiGet<{ plans: Plan[] }>("/plans"),
      apiGet<{ modules: ModuleDef[] }>("/modules"),
    ]);
    setPlans(p.plans);
    setModules(m.modules);
    setLoading(false);
  }
  useEffect(() => { load(); }, []);

  function openNew() { setEditing(null); setForm(BLANK); setShowForm(true); setError(null); }
  function openEdit(p: Plan) {
    setEditing(p);
    setForm({ name: p.name, description: p.description ?? "", price: String(p.price), interval: p.interval, module_keys: p.module_keys ?? [], is_active: p.is_active });
    setShowForm(true); setError(null);
  }

  function toggleMod(key: string) {
    setForm((f) => ({ ...f, module_keys: f.module_keys.includes(key) ? f.module_keys.filter((k) => k !== key) : [...f.module_keys, key] }));
  }

  async function save(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    try {
      const payload = { ...form, price: Number(form.price) };
      if (editing) await apiPut(`/plans/${editing.id}`, payload);
      else await apiPost("/plans", payload);
      setShowForm(false);
      await load();
    } catch (err) { setError(err instanceof ApiError ? err.message : "Save failed"); }
  }

  async function remove(p: Plan) {
    if (!confirm(`Delete plan "${p.name}"?`)) return;
    try { await apiDelete(`/plans/${p.id}`); load(); }
    catch (e) { alert(e instanceof ApiError ? e.message : "Delete failed"); }
  }

  if (loading) return <p className="text-[var(--muted)]">Loading plans…</p>;

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-3xl">Plans</h1>
          <p className="mt-1 text-[var(--muted)]">Package modules into sellable plans for your customers.</p>
        </div>
        <button onClick={openNew} className="btn-primary">+ New Plan</button>
      </div>

      <div className="grid gap-4 md:grid-cols-3">
        {plans.map((p) => (
          <div key={p.id} className="card flex flex-col p-6">
            <div className="flex items-center justify-between">
              <h3 className="text-xl">{p.name}</h3>
              {!p.is_active && <span className="rounded-full bg-[var(--muted)]/15 px-2 py-0.5 text-[10px] font-bold uppercase text-[var(--muted)]">inactive</span>}
            </div>
            <p className="mt-2 text-3xl">${Number(p.price).toFixed(0)}<span className="text-sm font-bold text-[var(--muted)]">/{p.interval === "monthly" ? "mo" : p.interval === "yearly" ? "yr" : "once"}</span></p>
            {p.description && <p className="mt-2 text-sm text-[var(--muted)]">{p.description}</p>}
            <p className="mt-4 text-sm font-bold text-[var(--primary)]">{p.module_keys.length} modules included</p>
            <p className="text-xs text-[var(--muted)]">{p.tenants_count} tenant(s) on this plan</p>
            <div className="mt-4 flex gap-2">
              <button onClick={() => openEdit(p)} className="btn-ghost flex-1">Edit</button>
              <button onClick={() => remove(p)} className="btn-ghost hover:border-[var(--danger)] hover:text-[var(--danger)]">Delete</button>
            </div>
          </div>
        ))}
      </div>

      {showForm && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onClick={() => setShowForm(false)}>
          <form onSubmit={save} className="card max-h-[90vh] w-full max-w-lg overflow-y-auto p-6" onClick={(e) => e.stopPropagation()}>
            <h2 className="text-xl">{editing ? "Edit plan" : "New plan"}</h2>
            <div className="mt-4 space-y-3">
              <input className="input" placeholder="Plan name" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
              <textarea className="input min-h-16" placeholder="Description" value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} />
              <div className="grid grid-cols-2 gap-3">
                <input type="number" min="0" step="0.01" className="input" placeholder="Price" value={form.price} onChange={(e) => setForm({ ...form, price: e.target.value })} required />
                <select className="input" value={form.interval} onChange={(e) => setForm({ ...form, interval: e.target.value })}>
                  <option value="monthly">Monthly</option>
                  <option value="yearly">Yearly</option>
                  <option value="one_time">One-time</option>
                </select>
              </div>
              <p className="label">Included modules</p>
              <div className="grid grid-cols-2 gap-2">
                {modules.map((m) => (
                  <label key={m.key} className="flex items-center gap-2 text-sm font-bold">
                    <input type="checkbox" checked={form.module_keys.includes(m.key)} onChange={() => toggleMod(m.key)} className="h-4 w-4 accent-[var(--primary)]" />
                    {m.name}
                  </label>
                ))}
              </div>
              <label className="flex items-center gap-2 text-sm font-bold">
                <input type="checkbox" checked={form.is_active} onChange={(e) => setForm({ ...form, is_active: e.target.checked })} className="h-4 w-4 accent-[var(--primary)]" />
                Active (available to assign)
              </label>
            </div>
            {error && <p className="mt-3 text-sm font-bold text-[var(--danger)]">{error}</p>}
            <div className="mt-5 flex gap-2">
              <button type="submit" className="btn-primary flex-1">{editing ? "Save plan" : "Create plan"}</button>
              <button type="button" onClick={() => setShowForm(false)} className="btn-ghost">Cancel</button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
}
