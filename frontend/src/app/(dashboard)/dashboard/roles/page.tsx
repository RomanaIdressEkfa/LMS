"use client";

import { useEffect, useMemo, useState } from "react";
import { apiGet, apiPost, apiPut, apiDelete, ApiError } from "@/lib/api";
import { useAuth } from "@/lib/auth";
import type { Role } from "@/lib/types";

type Catalog = Record<string, string[]>;

export default function RolesPage() {
  const { can } = useAuth();
  const [roles, setRoles] = useState<Role[]>([]);
  const [catalog, setCatalog] = useState<Catalog>({});
  const [selected, setSelected] = useState<Role | null>(null);
  const [draft, setDraft] = useState<Set<string>>(new Set());
  const [newRole, setNewRole] = useState("");
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [msg, setMsg] = useState<string | null>(null);

  const canManage = can("roles.create") || can("roles.update");

  async function load() {
    const [r, c] = await Promise.all([
      apiGet<{ roles: Role[] }>("/roles"),
      apiGet<{ groups: Catalog }>("/permissions/catalog"),
    ]);
    setRoles(r.roles);
    setCatalog(c.groups);
    if (!selected && r.roles.length) select(r.roles[0]);
    setLoading(false);
  }

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  function select(role: Role) {
    setSelected(role);
    setDraft(new Set(role.permissions));
    setMsg(null);
  }

  const allPerms = useMemo(() => Object.values(catalog).flat(), [catalog]);
  const isSuper = selected?.name === "super-admin";

  function togglePerm(p: string) {
    if (isSuper) return;
    setDraft((prev) => {
      const next = new Set(prev);
      next.has(p) ? next.delete(p) : next.add(p);
      return next;
    });
  }

  function toggleGroup(perms: string[], on: boolean) {
    if (isSuper) return;
    setDraft((prev) => {
      const next = new Set(prev);
      perms.forEach((p) => (on ? next.add(p) : next.delete(p)));
      return next;
    });
  }

  async function save() {
    if (!selected) return;
    setSaving(true);
    setMsg(null);
    try {
      await apiPut(`/roles/${selected.id}`, { permissions: Array.from(draft) });
      setMsg("Saved ✔");
      await load();
    } catch (e) {
      setMsg(e instanceof ApiError ? e.message : "Save failed");
    } finally {
      setSaving(false);
    }
  }

  async function createRole() {
    if (!newRole.trim()) return;
    try {
      await apiPost("/roles", { name: newRole, permissions: [] });
      setNewRole("");
      await load();
    } catch (e) {
      setMsg(e instanceof ApiError ? e.message : "Create failed");
    }
  }

  async function removeRole(role: Role) {
    if (!confirm(`Delete role "${role.name}"?`)) return;
    try {
      await apiDelete(`/roles/${role.id}`);
      setSelected(null);
      await load();
    } catch (e) {
      setMsg(e instanceof ApiError ? e.message : "Delete failed");
    }
  }

  if (loading) return <p className="text-[var(--muted)]">Loading roles…</p>;

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl">Roles &amp; Permissions</h1>
        <p className="mt-1 text-[var(--muted)]">
          Create unlimited roles and tick exactly what each one can do. Changes
          apply instantly to every user with that role.
        </p>
      </div>

      <div className="grid gap-6 lg:grid-cols-[300px_1fr]">
        {/* Roles list */}
        <div className="card h-fit p-4">
          <div className="space-y-1">
            {roles.map((r) => (
              <button
                key={r.id}
                onClick={() => select(r)}
                className={`flex w-full items-center justify-between rounded-[var(--radius)] px-3 py-2.5 text-left text-sm font-bold transition-colors ${
                  selected?.id === r.id
                    ? "bg-[var(--primary)] text-white"
                    : "hover:bg-[var(--primary)]/8 text-[var(--foreground)]"
                }`}
              >
                <span className="capitalize">{r.name.replace(/-/g, " ")}</span>
                <span
                  className={`rounded-full px-2 py-0.5 text-[10px] ${
                    selected?.id === r.id ? "bg-white/20" : "bg-[var(--muted)]/15 text-[var(--muted)]"
                  }`}
                >
                  {r.users_count}
                </span>
              </button>
            ))}
          </div>

          {can("roles.create") && (
            <div className="mt-4 border-t border-[var(--border)] pt-4">
              <label className="label">New role</label>
              <div className="flex gap-2">
                <input
                  className="input"
                  placeholder="e.g. Content Manager"
                  value={newRole}
                  onChange={(e) => setNewRole(e.target.value)}
                />
                <button onClick={createRole} className="btn-primary px-4">+</button>
              </div>
            </div>
          )}
        </div>

        {/* Permission grid */}
        <div className="card p-6">
          {selected && (
            <>
              <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <h2 className="text-xl capitalize">{selected.name.replace(/-/g, " ")}</h2>
                  <p className="text-sm text-[var(--muted)]">
                    {isSuper
                      ? "Super Admin always has every permission."
                      : `${draft.size} of ${allPerms.length} permissions selected`}
                  </p>
                </div>
                <div className="flex items-center gap-2">
                  {msg && <span className="text-sm font-bold text-[var(--success)]">{msg}</span>}
                  {canManage && !isSuper && (
                    <button onClick={save} disabled={saving} className="btn-primary disabled:opacity-60">
                      {saving ? "Saving…" : "Save changes"}
                    </button>
                  )}
                  {can("roles.delete") && !selected.is_protected && (
                    <button
                      onClick={() => removeRole(selected)}
                      className="btn-ghost hover:border-[var(--danger)] hover:text-[var(--danger)]"
                    >
                      Delete
                    </button>
                  )}
                </div>
              </div>

              <div className="mt-6 grid gap-4 md:grid-cols-2">
                {Object.entries(catalog).map(([group, perms]) => {
                  const allOn = perms.every((p) => draft.has(p) || isSuper);
                  return (
                    <div key={group} className="rounded-[var(--radius)] border border-[var(--border)] p-4">
                      <div className="flex items-center justify-between">
                        <h3 className="font-bold">{group}</h3>
                        {!isSuper && (
                          <button
                            onClick={() => toggleGroup(perms, !allOn)}
                            className="text-xs font-bold text-[var(--primary)] hover:underline"
                          >
                            {allOn ? "Clear" : "All"}
                          </button>
                        )}
                      </div>
                      <div className="mt-3 space-y-2">
                        {perms.map((p) => {
                          const checked = isSuper || draft.has(p);
                          return (
                            <label
                              key={p}
                              className={`flex cursor-pointer items-center gap-2.5 text-sm ${
                                isSuper ? "cursor-default opacity-70" : ""
                              }`}
                            >
                              <input
                                type="checkbox"
                                checked={checked}
                                disabled={isSuper || !canManage}
                                onChange={() => togglePerm(p)}
                                className="h-4 w-4 accent-[var(--primary)]"
                              />
                              <span className="font-semibold text-[var(--foreground)]">{p}</span>
                            </label>
                          );
                        })}
                      </div>
                    </div>
                  );
                })}
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  );
}
