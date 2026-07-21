import { createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from "react";
import { useHydrated } from "./use-hydrated";

export type Theme = "dark" | "light";
export type Language = "fr" | "en";
export type Audience = "recruiter" | "client" | "technical" | "none";

export interface Preferences {
  theme: Theme;
  language: Language;
  audience: Audience;
  hasVisited: boolean;
}

const DEFAULT: Preferences = {
  theme: "dark",
  language: "fr",
  audience: "none",
  hasVisited: false,
};

const STORAGE_KEY = "ak.prefs.v1";

interface Ctx {
  prefs: Preferences;
  setTheme: (t: Theme) => void;
  setLanguage: (l: Language) => void;
  setAudience: (a: Audience) => void;
  markVisited: () => void;
  reset: () => void;
}

const PreferencesContext = createContext<Ctx | null>(null);

export function PreferencesProvider({ children }: { children: ReactNode }) {
  const [prefs, setPrefs] = useState<Preferences>(DEFAULT);
  const hydrated = useHydrated();

  useEffect(() => {
    if (!hydrated) return;
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (raw) setPrefs({ ...DEFAULT, ...JSON.parse(raw) });
    } catch {
      /* ignore */
    }
  }, [hydrated]);

  useEffect(() => {
    if (!hydrated) return;
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
    } catch {
      /* ignore */
    }
    const root = document.documentElement;
    root.classList.toggle("dark", prefs.theme === "dark");
    root.lang = prefs.language;
  }, [prefs, hydrated]);

  const setTheme = useCallback((theme: Theme) => setPrefs((p) => ({ ...p, theme })), []);
  const setLanguage = useCallback((language: Language) => setPrefs((p) => ({ ...p, language })), []);
  const setAudience = useCallback((audience: Audience) => setPrefs((p) => ({ ...p, audience })), []);
  const markVisited = useCallback(() => setPrefs((p) => ({ ...p, hasVisited: true })), []);
  const reset = useCallback(() => setPrefs(DEFAULT), []);

  const value = useMemo<Ctx>(
    () => ({ prefs, setTheme, setLanguage, setAudience, markVisited, reset }),
    [prefs, setTheme, setLanguage, setAudience, markVisited, reset],
  );

  return <PreferencesContext.Provider value={value}>{children}</PreferencesContext.Provider>;
}

export function usePreferences() {
  const ctx = useContext(PreferencesContext);
  if (!ctx) throw new Error("usePreferences must be used within PreferencesProvider");
  return ctx;
}
