import { createFileRoute, Link } from "@tanstack/react-router";
import { Eye, Github, ExternalLink } from "lucide-react";
import { projects, skills } from "@/data/portfolio";

export const Route = createFileRoute("/_app/projects")({
  head: () => ({ meta: [{ title: "Projects — Alaa Khalil" }] }),
  component: ProjectsPage,
});

function ProjectsPage() {
  return (
    <div className="mx-auto w-full max-w-4xl px-4 py-6">
      <header className="mb-6">
        <h1 className="text-2xl font-bold sm:text-3xl">Projects</h1>
        <p className="mt-1 text-sm text-muted-foreground">{projects.length} projects across SaaS, APIs, AI and DevOps.</p>
      </header>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
        {projects.map((p) => (
          <Link key={p.slug} to="/projects/$slug" params={{ slug: p.slug }} className="group overflow-hidden rounded-2xl border border-border bg-surface shadow-card transition-all hover:-translate-y-1 hover:shadow-glow">
            <div className="relative h-36" style={{ background: p.cover }}>
              <div className="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent" />
              <span className={`absolute right-3 top-3 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase ${p.status === "shipped" ? "bg-success/90 text-white" : p.status === "in-progress" ? "bg-warning/90 text-black" : "bg-muted text-muted-foreground"}`}>{p.status}</span>
              <div className="absolute left-4 bottom-3 grid h-10 w-10 place-items-center rounded-xl bg-surface/90 text-xl shadow-card">{p.logo}</div>
            </div>
            <div className="p-4">
              <div className="flex items-center justify-between">
                <h3 className="font-semibold">{p.name}</h3>
                <span className="text-[10px] uppercase tracking-wider text-muted-foreground">{p.category}</span>
              </div>
              <p className="mt-1 text-sm text-muted-foreground">{p.tagline}</p>
              <div className="mt-3 flex flex-wrap gap-1.5">
                {p.tech.slice(0, 5).map((tid) => {
                  const s = skills.find((sk) => sk.id === tid);
                  return s ? <span key={tid} className="rounded-full border border-border bg-surface-elevated px-2 py-0.5 text-[10px]">{s.name}</span> : null;
                })}
              </div>
              <div className="mt-4 flex items-center justify-between text-xs text-muted-foreground">
                <span className="inline-flex items-center gap-1"><Eye className="h-3.5 w-3.5" /> {p.views.toLocaleString()}</span>
                <div className="flex items-center gap-3">
                  {p.githubUrl && <Github className="h-3.5 w-3.5" />}
                  {p.demoUrl && <ExternalLink className="h-3.5 w-3.5" />}
                </div>
              </div>
            </div>
          </Link>
        ))}
      </div>
    </div>
  );
}
