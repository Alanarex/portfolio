import { createFileRoute } from "@tanstack/react-router";
import { experiences, skills } from "@/data/portfolio";

export const Route = createFileRoute("/_app/experience")({
  head: () => ({ meta: [{ title: "Experience — Alaa Khalil" }] }),
  component: ExperiencePage,
});

function ExperiencePage() {
  return (
    <div className="mx-auto w-full max-w-3xl px-4 py-6">
      <header className="mb-6">
        <h1 className="text-2xl font-bold sm:text-3xl">Experience</h1>
        <p className="mt-1 text-sm text-muted-foreground">A timeline of the companies, teams and products.</p>
      </header>

      <div className="relative border-l border-border pl-6">
        {experiences.map((e, i) => (
          <div key={i} className="relative mb-8">
            <span className="absolute -left-[27px] top-1 grid h-4 w-4 place-items-center rounded-full gradient-brand shadow-glow">
              <span className="h-1.5 w-1.5 rounded-full bg-primary-foreground" />
            </span>
            <div className="rounded-2xl border border-border bg-surface p-5 shadow-card">
              <div className="flex items-start justify-between">
                <div>
                  <h3 className="font-semibold">{e.role}</h3>
                  <div className="text-sm text-primary">{e.company}</div>
                </div>
                <div className="text-xs text-muted-foreground">{e.start} — {e.end}</div>
              </div>
              <div className="mt-1 text-xs text-muted-foreground">📍 {e.location}</div>
              <p className="mt-3 text-sm text-foreground/80">{e.summary}</p>

              <ul className="mt-3 space-y-1 text-sm">
                {e.achievements.map((a) => (
                  <li key={a} className="flex gap-2"><span className="text-primary">▸</span>{a}</li>
                ))}
              </ul>

              <div className="mt-4 flex flex-wrap gap-1.5">
                {e.tech.map((tid) => {
                  const s = skills.find((sk) => sk.id === tid);
                  return s ? <span key={tid} className="rounded-full bg-primary/10 px-2 py-0.5 text-[11px] text-primary">{s.name}</span> : null;
                })}
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
