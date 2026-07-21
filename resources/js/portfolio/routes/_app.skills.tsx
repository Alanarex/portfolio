import { createFileRoute } from "@tanstack/react-router";
import { useState } from "react";
import { skills } from "@/data/portfolio";
import { cn } from "@/lib/utils";

export const Route = createFileRoute("/_app/skills")({
  head: () => ({ meta: [{ title: "Skills Explorer — Alaa Khalil" }] }),
  component: SkillsPage,
});

const CATEGORIES = [
  { id: "all", label: "All" },
  { id: "backend", label: "Backend" },
  { id: "frontend", label: "Frontend" },
  { id: "devops", label: "DevOps" },
  { id: "database", label: "Database" },
  { id: "ai", label: "AI" },
  { id: "tools", label: "Tools" },
];

function SkillsPage() {
  const [cat, setCat] = useState("all");
  const filtered = skills.filter((s) => cat === "all" || s.category === cat);

  return (
    <div className="mx-auto w-full max-w-4xl px-4 py-6">
      <header className="mb-6">
        <h1 className="text-2xl font-bold sm:text-3xl">Skills Explorer</h1>
        <p className="mt-1 text-sm text-muted-foreground">Every technology, mapped to projects and posts. Click a card to explore.</p>
      </header>

      <div className="mb-4 flex gap-2 overflow-x-auto scrollbar-thin pb-1">
        {CATEGORIES.map((c) => (
          <button
            key={c.id}
            onClick={() => setCat(c.id)}
            className={cn(
              "shrink-0 rounded-full border px-3.5 py-1.5 text-xs font-medium",
              cat === c.id ? "border-primary bg-primary text-primary-foreground" : "border-border bg-surface hover:bg-accent",
            )}
          >
            {c.label}
          </button>
        ))}
      </div>

      <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
        {filtered.map((s) => (
          <div key={s.id} className="group relative overflow-hidden rounded-2xl border border-border bg-surface p-4 shadow-card transition-all hover:-translate-y-1 hover:border-primary/60 hover:shadow-glow">
            <div className="absolute inset-x-0 top-0 h-1 gradient-brand opacity-0 transition-opacity group-hover:opacity-100" />
            <div className="mb-3 grid h-10 w-10 place-items-center rounded-xl gradient-brand text-primary-foreground text-sm font-bold">
              {s.name.slice(0, 2).toUpperCase()}
            </div>
            <div className="font-semibold">{s.name}</div>
            <div className="text-[10px] uppercase tracking-wider text-muted-foreground">{s.category}</div>
            <div className="mt-3 h-1.5 overflow-hidden rounded-full bg-muted">
              <div className="h-full gradient-brand" style={{ width: `${s.level}%` }} />
            </div>
            <div className="mt-3 flex justify-between text-[11px] text-muted-foreground">
              <span>{s.projects} projects</span>
              <span>{s.years}y exp</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
