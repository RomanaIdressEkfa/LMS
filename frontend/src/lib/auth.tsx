"use client";

import {
  createContext,
  useContext,
  useEffect,
  useState,
  useCallback,
  ReactNode,
} from "react";
import { apiGet, apiPost, setToken, getToken } from "./api";
import type { AuthResponse, User } from "./types";

interface AuthContextValue {
  user: User | null;
  loading: boolean;
  login: (login: string, password: string) => Promise<User>;
  register: (payload: RegisterPayload) => Promise<User>;
  logout: () => Promise<void>;
  can: (permission: string) => boolean;
  hasRole: (role: string) => boolean;
  refresh: () => Promise<void>;
}

interface RegisterPayload {
  name: string;
  email: string;
  phone?: string;
  password: string;
  password_confirmation: string;
  role: string;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  const refresh = useCallback(async () => {
    if (!getToken()) {
      setUser(null);
      setLoading(false);
      return;
    }
    try {
      const { user } = await apiGet<{ user: User }>("/me");
      setUser(user);
    } catch {
      setToken(null);
      setUser(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    refresh();
  }, [refresh]);

  const login = useCallback(async (login: string, password: string) => {
    const res = await apiPost<AuthResponse>("/login", { login, password });
    setToken(res.token);
    setUser(res.user);
    return res.user;
  }, []);

  const register = useCallback(async (payload: RegisterPayload) => {
    const res = await apiPost<AuthResponse>("/register", payload);
    setToken(res.token);
    setUser(res.user);
    return res.user;
  }, []);

  const logout = useCallback(async () => {
    try {
      await apiPost("/logout");
    } catch {
      /* ignore network errors on logout */
    }
    setToken(null);
    setUser(null);
  }, []);

  const can = useCallback(
    (permission: string) => !!user?.permissions.includes(permission),
    [user]
  );
  const hasRole = useCallback(
    (role: string) => !!user?.roles.includes(role),
    [user]
  );

  return (
    <AuthContext.Provider
      value={{ user, loading, login, register, logout, can, hasRole, refresh }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth must be used within <AuthProvider>");
  return ctx;
}
