import { createFileRoute } from "@tanstack/react-router";
import { GitCommit, Rocket, Package, Activity as ActivityIcon } from "lucide-react";
import { latestActivity } from "@/data/portfolio";

export const Route = createFileRoute("/_app/activity")({
  head: () => ({ meta: [{ title: "Activity — Alaa Khalil" }] }),
  component: ActivityPage,
});

function ActivityPage() {
  return (
    <div className="mx-auto w-full max-w-3xl px-4 py-6">
      <header className="mb-6">
        <h1 className="text-2xl font-bold sm:text-3xl">Activity</h1>
        <p className="mt-1 text-sm text-muted-foreground">Contributions, releases and deployments across GitHub and GitLab.</p>
      </header>

      {/* Heatmap */}
      <div className="rounded-2xl border border-border bg-surface p-5 shadow-card">
        <div className="mb-3 flex items-center justify-between">
          <div className="text-sm font-semibold">Contribution heatmap</div>
          <div className="text-xs text-muted-foreground">Last 52 weeks · 1,284 contributions</div>
        </div>
        <div className="grid grid-cols-[repeat(52,1fr)] gap-0.5">
          {Array.from({ length: 52 * 7 }).map((_, i) => {
            const intensity = (i * 13) % 5;
            return <div key={i} className="aspect-square rounded-sm" style={{ backgroundColor: `color-mix(in oklab, var(--primary) ${intensity * 22}%, var(--muted))` }} />;
          })}
        </div>
      </div>

      {/* Stats grid */}
      <div className="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        {[
          { icon: GitCommit, label: "Commits", value: "1,284" },
          { icon: Rocket, label: "Releases", value: "36" },
          { icon: Package, label: "Deployments", value: "148" },
          { icon: ActivityIcon, label: "Uptime", value: "99.98%" },
        ].map((s) => (
          <div key={s.label} className="rounded-2xl border border-border bg-surface p-4 text-center shadow-card">
            <s.icon className="mx-auto mb-2 h-5 w-5 text-primary" />
            <div className="text-lg font-bold">{s.value}</div>
            <div className="text-[10px] uppercase tracking-wider text-muted-foreground">{s.label}</div>
          </div>
        ))}
      </div>

      {/* Recent */}
      <div className="mt-6 rounded-2xl border border-border bg-surface p-5 shadow-card">
        <div className="mb-3 text-sm font-semibold">Recent activity</div>
        <ul className="space-y-2">
          {latestActivity.map((a, i) => (
            <li key={i} className="flex items-center gap-3 text-sm">
              <span className="grid h-6 w-6 place-items-center rounded-full bg-primary/10 text-[10px] font-bold text-primary">{i + 1}</span>
              {a}
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}
