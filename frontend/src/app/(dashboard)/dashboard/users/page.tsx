"use client";

import { useEffect, useState, useCallback } from "react";
import { apiGet, apiPost, apiDelete, getToken, setToken, ApiError } from "@/lib/api";
import { useAuth } from "@/lib/auth";

interface Row {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  status: string;
  roles: string[];
  created_at: string;
}

const STATUS: Record<string, string> = {
  active: "bg-[var(--success)]/15 text-[var(--success)]",
  suspended: "bg-[var(--danger)]/15 text-[var(--danger)]",
  pending: "bg-[var(--warning)]/15 text-[var(--warning)]",
};

export default function UsersPage() {
  const { can, user: me } = useAuth();
  const [rows, setRows] = useState<Row[]>([]);
  const [roles, setRoles] = useState<string[]>([]);
  const [search, setSearch] = useState("");
  const [roleFilter, setRoleFilter] = useState("");
  const [loading, setLoading] = useState(true);
  const [editing, setEditing] = useState<Row | null>(null);
  const [showCreate, setShowCreate] = useState(false);

  const load = useCallback(async () => {
    const params = new URLSearchParams();
    if (search) params.set("search", search);
    if (roleFilter) params.set("role", roleFilter);
    const data = await apiGet<{ data: Row[] }>(`/users?${params.toString()}`);
    setRows(data.data);
    setLoading(false);
  }, [search, roleFilter]);

  useEffect(() => {
    apiGet<{ roles: string[] }>("/users/assignable-roles").then((d) => setRoles(d.roles)).catch(() => {});
  }, []);
  useEffect(() => {
    const t = setTimeout(load, 250);
    return () => clearTimeout(t);
  }, [load]);

  async function toggleStatus(u: Row) {
    await apiPost(`/users/${u.id}/toggle-status`);
    load();
  }
  async function remove(u: Row) {
    if (!confirm(`Delete ${u.name}?`)) return;
    try { await apiDelete(`/users/${u.id}`); load(); }
    catch (e) { alert(e instanceof ApiError ? e.message : "Delete failed"); }
  }
  async function impersonate(u: Row) {
    if (!confirm(`Log in as ${u.name}? You can return to your admin account afterwards.`)) return;
    const res = await apiPost<{ token: string }>(`/users/${u.id}/impersonate`);
    const current = getToken();
    if (current) localStorage.setItem("nova_token_admin", current);
    setToken(res.token);
    window.location.href = "/dashboard";
  }

  if (loading) return <p className="text-[var(--muted)]">Loading users…</p>;

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-3xl">Users</h1>
          <p className="mt-1 text-[var(--muted)]">Manage people, roles and access.</p>
        </div>
        {can("users.create") && <button onClick={() => setShowCreate(true)} className="btn-primary">+ Add User</button>}
      </div>

      <div className="card flex flex-wrap items-center gap-3 p-4">
        <input className="input max-w-xs" placeholder="Search name or email…" value={search} onChange={(e) => setSearch(e.target.value)} />
        <select className="input max-w-[180px]" value={roleFilter} onChange={(e) => setRoleFilter(e.target.value)}>
          <option value="">All roles</option>
          {roles.map((r) => <option key={r} value={r}>{r}</option>)}
        </select>
      </div>

      <div className="card overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm">
            <thead className="border-b border-[var(--border)] text-[var(--muted)]">
              <tr>
                <th className="p-4 font-bold">Name</th>
                <th className="p-4 font-bold">Roles</th>
                <th className="p-4 font-bold">Status</th>
                <th className="p-4 font-bold text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((u) => (
                <tr key={u.id} className="border-b border-[var(--border)] last:border-0">
                  <td className="p-4">
                    <p className="font-bold text-[var(--foreground)]">{u.name}</p>
                    <p className="text-xs text-[var(--muted)]">{u.email}</p>
                  </td>
                  <td className="p-4">
                    <div className="flex flex-wrap gap-1">
                      {u.roles.map((r) => (
                        <span key={r} className="rounded-full bg-[var(--primary)]/10 px-2 py-0.5 text-[10px] font-bold uppercase text-[var(--primary)]">{r}</span>
                      ))}
                    </div>
                  </td>
                  <td className="p-4">
                    <span className={`rounded-full px-2.5 py-1 text-[11px] font-bold uppercase ${STATUS[u.status] ?? ""}`}>{u.status}</span>
                  </td>
                  <td className="p-4">
                    <div className="flex flex-wrap justify-end gap-2 text-xs font-bold">
                      {can("users.update") && (
                        <button onClick={() => setEditing(u)} className="text-[var(--primary)] hover:underline">Roles</button>
                      )}
                      {can("users.update") && u.id !== me?.id && (
                        <button onClick={() => toggleStatus(u)} className="text-[var(--warning)] hover:underline">
                          {u.status === "active" ? "Suspend" : "Activate"}
                        </button>
                      )}
                      {can("users.impersonate") && u.id !== me?.id && (
                        <button onClick={() => impersonate(u)} className="text-[var(--foreground)] hover:underline">Login as</button>
                      )}
                      {can("users.delete") && u.id !== me?.id && !u.roles.includes("super-admin") && (
                        <button onClick={() => remove(u)} className="text-[var(--danger)] hover:underline">Delete</button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {editing && (
        <RoleEditor user={editing} allRoles={roles} onClose={() => setEditing(null)} onSaved={() => { setEditing(null); load(); }} />
      )}
      {showCreate && (
        <CreateUser roles={roles} onClose={() => setShowCreate(false)} onCreated={() => { setShowCreate(false); load(); }} />
      )}
    </div>
  );
}

function RoleEditor({ user, allRoles, onClose, onSaved }: { user: Row; allRoles: string[]; onClose: () => void; onSaved: () => void }) {
  const [selected, setSelected] = useState<Set<string>>(new Set(user.roles));
  const [error, setError] = useState<string | null>(null);
  const toggle = (r: string) => setSelected((prev) => { const n = new Set(prev); n.has(r) ? n.delete(r) : n.add(r); return n; });
  async function save() {
    try { await apiPost(`/users/${user.id}/roles`, { roles: Array.from(selected) }); onSaved(); }
    catch (e) { setError(e instanceof ApiError ? e.message : "Save failed"); }
  }
  return (
    <Modal title={`Roles · ${user.name}`} onClose={onClose}>
      <div className="space-y-2">
        {allRoles.map((r) => (
          <label key={r} className="flex items-center gap-2.5 text-sm font-bold">
            <input type="checkbox" checked={selected.has(r)} onChange={() => toggle(r)} className="h-4 w-4 accent-[var(--primary)]" />
            <span className="capitalize">{r.replace(/-/g, " ")}</span>
          </label>
        ))}
      </div>
      {error && <p className="mt-3 text-sm font-bold text-[var(--danger)]">{error}</p>}
      <button onClick={save} className="btn-primary mt-5 w-full">Save roles</button>
    </Modal>
  );
}

function CreateUser({ roles, onClose, onCreated }: { roles: string[]; onClose: () => void; onCreated: () => void }) {
  const [form, setForm] = useState({ name: "", email: "", password: "", role: "student" });
  const [error, setError] = useState<string | null>(null);
  async function submit(e: React.FormEvent) {
    e.preventDefault();
    try { await apiPost("/users", { ...form, roles: [form.role] }); onCreated(); }
    catch (err) { setError(err instanceof ApiError ? err.message : "Create failed"); }
  }
  return (
    <Modal title="Add user" onClose={onClose}>
      <form onSubmit={submit} className="space-y-3">
        <input className="input" placeholder="Full name" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
        <input type="email" className="input" placeholder="Email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required />
        <input type="password" className="input" placeholder="Password (min 8)" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} required />
        <select className="input" value={form.role} onChange={(e) => setForm({ ...form, role: e.target.value })}>
          {roles.map((r) => <option key={r} value={r}>{r}</option>)}
        </select>
        {error && <p className="text-sm font-bold text-[var(--danger)]">{error}</p>}
        <button type="submit" className="btn-primary w-full">Create user</button>
      </form>
    </Modal>
  );
}

function Modal({ title, onClose, children }: { title: string; onClose: () => void; children: React.ReactNode }) {
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onClick={onClose}>
      <div className="card w-full max-w-sm p-6" onClick={(e) => e.stopPropagation()}>
        <div className="flex items-start justify-between">
          <h2 className="text-xl">{title}</h2>
          <button onClick={onClose} className="text-2xl leading-none text-[var(--muted)]">×</button>
        </div>
        <div className="mt-4">{children}</div>
      </div>
    </div>
  );
}
