import { createFileRoute } from "@tanstack/react-router";
import { Eye, Rocket, Heart, MessageCircle } from "lucide-react";
import { projects, posts } from "@/data/portfolio";

export const Route = createFileRoute("/_app/analytics")({
  head: () => ({ meta: [{ title: "Analytics — Alaa Khalil" }] }),
  component: AnalyticsPage,
});

function AnalyticsPage() {
  const topProjects = [...projects].sort((a, b) => b.views - a.views).slice(0, 5);
  const topPosts = [...posts].sort((a, b) => b.reactions - a.reactions).slice(0, 5);
  return (
    <div className="mx-auto w-full max-w-3xl px-4 py-6">
      <header className="mb-6">
        <h1 className="text-2xl font-bold sm:text-3xl">Analytics</h1>
        <p className="mt-1 text-sm text-muted-foreground">Public stats. Nothing to hide.</p>
      </header>

      <div className="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        {[
          { icon: Eye, label: "Visitors (30d)", value: "24.8k" },
          { icon: Rocket, label: "Project views", value: "58.1k" },
          { icon: Heart, label: "Reactions", value: "3,412" },
          { icon: MessageCircle, label: "Comments", value: "487" },
        ].map((s) => (
          <div key={s.label} className="rounded-2xl border border-border bg-surface p-4 shadow-card">
            <s.icon className="mb-2 h-5 w-5 text-primary" />
            <div className="text-lg font-bold">{s.value}</div>
            <div className="text-[10px] uppercase tracking-wider text-muted-foreground">{s.label}</div>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div className="rounded-2xl border border-border bg-surface p-5 shadow-card">
          <div className="mb-3 text-sm font-semibold">Most-viewed projects</div>
          <ul className="space-y-2">
            {topProjects.map((p) => {
              const pct = (p.views / topProjects[0].views) * 100;
              return (
                <li key={p.slug}>
                  <div className="mb-1 flex items-center justify-between text-xs">
                    <span className="font-medium">{p.name}</span>
                    <span className="text-muted-foreground">{p.views.toLocaleString()}</span>
                  </div>
                  <div className="h-2 overflow-hidden rounded-full bg-muted">
                    <div className="h-full gradient-brand" style={{ width: `${pct}%` }} />
                  </div>
                </li>
              );
            })}
          </ul>
        </div>

        <div className="rounded-2xl border border-border bg-surface p-5 shadow-card">
          <div className="mb-3 text-sm font-semibold">Top posts</div>
          <ul className="space-y-3 text-sm">
            {topPosts.map((p) => (
              <li key={p.id} className="border-b border-border pb-2 last:border-0">
                <div className="font-medium">{p.title}</div>
                <div className="mt-1 flex gap-3 text-xs text-muted-foreground">
                  <span className="inline-flex items-center gap-1"><Heart className="h-3 w-3" /> {p.reactions}</span>
                  <span className="inline-flex items-center gap-1"><MessageCircle className="h-3 w-3" /> {p.comments}</span>
                  <span className="inline-flex items-center gap-1"><Eye className="h-3 w-3" /> {p.views.toLocaleString()}</span>
                </div>
              </li>
            ))}
          </ul>
        </div>
      </div>

      <div className="mt-4 rounded-2xl border border-border bg-surface p-5 shadow-card">
        <div className="mb-3 text-sm font-semibold">Top visitor countries</div>
        <ul className="space-y-2 text-sm">
          {[["🇫🇷 France", 42],["🇺🇸 United States", 21],["🇬🇧 United Kingdom", 12],["🇩🇪 Germany", 9],["🇨🇦 Canada", 6],["Other", 10]].map(([label, pct]) => (
            <li key={label as string}>
              <div className="mb-1 flex items-center justify-between text-xs">
                <span>{label}</span>
                <span className="text-muted-foreground">{pct}%</span>
              </div>
              <div className="h-1.5 overflow-hidden rounded-full bg-muted">
                <div className="h-full gradient-brand" style={{ width: `${pct}%` }} />
              </div>
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}
