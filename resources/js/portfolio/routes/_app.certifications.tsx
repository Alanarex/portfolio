import { createFileRoute } from "@tanstack/react-router";
import { Award, ExternalLink } from "lucide-react";
import { certifications, skills } from "@/data/portfolio";

export const Route = createFileRoute("/_app/certifications")({
  head: () => ({ meta: [{ title: "Certifications — Alaa Khalil" }] }),
  component: CertsPage,
});

function CertsPage() {
  return (
    <div className="mx-auto w-full max-w-3xl px-4 py-6">
      <header className="mb-6">
        <h1 className="text-2xl font-bold sm:text-3xl">Certifications</h1>
        <p className="mt-1 text-sm text-muted-foreground">Verified credentials from cloud, backend and CI/CD ecosystems.</p>
      </header>
      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
        {certifications.map((c) => (
          <div key={c.credential} className="rounded-2xl border border-border bg-surface p-5 shadow-card">
            <div className="mb-3 grid h-10 w-10 place-items-center rounded-xl gradient-brand text-primary-foreground"><Award className="h-5 w-5" /></div>
            <div className="font-semibold">{c.name}</div>
            <div className="text-sm text-muted-foreground">{c.issuer}</div>
            <div className="mt-2 text-xs text-muted-foreground">Issued {c.date} · #{c.credential}</div>
            <div className="mt-3 flex flex-wrap gap-1.5">
              {c.skills.map((sid) => {
                const s = skills.find((sk) => sk.id === sid);
                return s ? <span key={sid} className="rounded-full bg-primary/10 px-2 py-0.5 text-[11px] text-primary">{s.name}</span> : null;
              })}
            </div>
            <a href="#" className="mt-4 inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline"><ExternalLink className="h-3 w-3" /> Verify credential</a>
          </div>
        ))}
      </div>
    </div>
  );
}
