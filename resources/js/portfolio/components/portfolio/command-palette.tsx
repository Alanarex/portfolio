import { useEffect, useMemo, useState } from "react";
import { Link } from "@tanstack/react-router";
import { Search as SearchIcon, X, Rocket, Brain, Briefcase, Award, FileText } from "lucide-react";
import { projects, skills, experiences, certifications, posts } from "@/data/portfolio";
import { useT } from "@/lib/i18n";

interface Result { kind: string; icon: React.ReactNode; label: string; sub?: string; to: string; params?: Record<string,string> }

function all(): Result[] {
  return [
    ...projects.map((p) => ({ kind: "Project", icon: <Rocket className="h-4 w-4" />, label: p.name, sub: p.category, to: "/projects/$slug", params: { slug: p.slug } })),
    ...skills.map((s) => ({ kind: "Skill", icon: <Brain className="h-4 w-4" />, label: s.name, sub: s.category, to: "/skills" })),
    ...experiences.map((e) => ({ kind: "Experience", icon: <Briefcase className="h-4 w-4" />, label: e.company, sub: e.role, to: "/experience" })),
    ...certifications.map((c) => ({ kind: "Certification", icon: <Award className="h-4 w-4" />, label: c.name, sub: c.issuer, to: "/certifications" })),
    ...posts.map((p) => ({ kind: "Post", icon: <FileText className="h-4 w-4" />, label: p.title, sub: p.category, to: "/" })),
  ];
}

export function CommandPalette() {
  const t = useT();
  const [open, setOpen] = useState(false);
  const [q, setQ] = useState("");

  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "k") {
        e.preventDefault(); setOpen((o) => !o);
      } else if (e.key === "Escape") setOpen(false);
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, []);

  const results = useMemo(() => {
    const items = all();
    if (!q.trim()) return items.slice(0, 12);
    const s = q.toLowerCase();
    return items.filter((r) => r.label.toLowerCase().includes(s) || (r.sub ?? "").toLowerCase().includes(s)).slice(0, 20);
  }, [q]);

  if (!open) return null;

  return (
    <div className="fixed inset-0 z-[80] flex items-start justify-center bg-background/70 p-4 backdrop-blur-sm" onClick={() => setOpen(false)}>
      <div onClick={(e) => e.stopPropagation()} className="mt-20 w-full max-w-xl overflow-hidden rounded-2xl border border-border bg-surface shadow-glow">
        <div className="flex items-center gap-2 border-b border-border px-4">
          <SearchIcon className="h-4 w-4 text-muted-foreground" />
          <input
            autoFocus
            value={q}
            onChange={(e) => setQ(e.target.value)}
            placeholder={t("search.placeholder")}
            className="flex-1 bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground"
          />
          <button onClick={() => setOpen(false)} className="rounded-md p-1 text-muted-foreground hover:bg-accent"><X className="h-4 w-4" /></button>
        </div>
        <div className="max-h-96 overflow-y-auto scrollbar-thin p-2">
          {results.length === 0 && <div className="p-8 text-center text-sm text-muted-foreground">{t("search.empty")}</div>}
          {results.map((r, i) => (
            <Link
              key={i}
              to={r.to as never}
              params={r.params as never}
              onClick={() => setOpen(false)}
              className="flex items-center gap-3 rounded-xl px-3 py-2 text-sm hover:bg-accent"
            >
              <span className="text-muted-foreground">{r.icon}</span>
              <div className="min-w-0 flex-1">
                <div className="truncate font-medium">{r.label}</div>
                {r.sub && <div className="truncate text-xs text-muted-foreground">{r.sub}</div>}
              </div>
              <span className="text-[10px] uppercase tracking-wider text-muted-foreground">{r.kind}</span>
            </Link>
          ))}
        </div>
      </div>
    </div>
  );
}
