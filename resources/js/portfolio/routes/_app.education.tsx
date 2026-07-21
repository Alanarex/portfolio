import { createFileRoute } from "@tanstack/react-router";
import { education } from "@/data/portfolio";

export const Route = createFileRoute("/_app/education")({
  head: () => ({ meta: [{ title: "Education — Alaa Khalil" }] }),
  component: EducationPage,
});

function EducationPage() {
  return (
    <div className="mx-auto w-full max-w-3xl px-4 py-6">
      <header className="mb-6">
        <h1 className="text-2xl font-bold sm:text-3xl">Education</h1>
        <p className="mt-1 text-sm text-muted-foreground">Formal studies and pivotal learning milestones.</p>
      </header>
      <div className="space-y-4">
        {education.map((e) => (
          <div key={e.school} className="rounded-2xl border border-border bg-surface p-5 shadow-card">
            <div className="flex items-start justify-between">
              <div>
                <h3 className="font-semibold">{e.school}</h3>
                <div className="text-sm text-primary">{e.diploma}</div>
                <div className="mt-1 text-xs text-muted-foreground">📍 {e.location}</div>
              </div>
              <div className="text-xs text-muted-foreground">{e.start} — {e.end}</div>
            </div>
            <ul className="mt-4 space-y-1 text-sm">
              {e.highlights.map((h) => (
                <li key={h} className="flex gap-2"><span className="text-primary">▸</span>{h}</li>
              ))}
            </ul>
          </div>
        ))}
      </div>
    </div>
  );
}
