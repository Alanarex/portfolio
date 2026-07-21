import { Download, MessageSquare, Calendar, Github, ExternalLink, Activity, CheckCircle2 } from "lucide-react";
import { usePreferences } from "@/hooks/use-preferences";
import { profile, projects, experiences, services, testimonials, skills } from "@/data/portfolio";
import { useT } from "@/lib/i18n";

function Card({ title, children, action }: { title: string; children: React.ReactNode; action?: React.ReactNode }) {
  return (
    <div className="rounded-2xl border border-border bg-surface p-4 shadow-card">
      <div className="mb-3 flex items-center justify-between">
        <div className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">{title}</div>
        {action}
      </div>
      {children}
    </div>
  );
}

function Btn({ children, variant = "default", ...rest }: React.ButtonHTMLAttributes<HTMLButtonElement> & { variant?: "default" | "brand" | "ghost" }) {
  const styles = {
    default: "border border-border bg-surface hover:bg-accent",
    brand: "gradient-brand text-primary-foreground hover:opacity-90",
    ghost: "hover:bg-accent",
  }[variant];
  return <button {...rest} className={`inline-flex items-center justify-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition-all ${styles} ${rest.className ?? ""}`}>{children}</button>;
}

function RecruiterPanel() {
  const t = useT();
  const currentExp = experiences[0];
  return (
    <div className="space-y-4">
      <Card title={t("sidebar.currentPosition")}>
        <div className="font-semibold">{currentExp.role}</div>
        <div className="text-sm text-muted-foreground">{currentExp.company} · {currentExp.location}</div>
        <div className="mt-2 text-xs text-success">✓ {profile.availability}</div>
      </Card>
      <div className="grid grid-cols-2 gap-2">
        <Btn variant="brand"><Download className="h-4 w-4" /> {t("profile.cv")}</Btn>
        <Btn><MessageSquare className="h-4 w-4" /> Hire</Btn>
      </div>
      <Card title={t("sidebar.topProjects")}>
        <ul className="space-y-2">
          {projects.slice(0, 3).map((p) => (
            <li key={p.slug} className="flex items-center gap-3 text-sm">
              <div className="grid h-8 w-8 place-items-center rounded-lg text-base" style={{ background: p.cover }}>{p.logo}</div>
              <div className="min-w-0 flex-1">
                <div className="truncate font-medium">{p.name}</div>
                <div className="truncate text-xs text-muted-foreground">{p.category}</div>
              </div>
            </li>
          ))}
        </ul>
      </Card>
      <Card title={t("sidebar.languages")}>
        <ul className="space-y-1.5 text-sm">
          {profile.languages.map((l) => (
            <li key={l.name} className="flex items-center justify-between">
              <span>{l.name}</span>
              <span className="text-xs text-muted-foreground">{l.level}</span>
            </li>
          ))}
        </ul>
      </Card>
    </div>
  );
}

function ClientPanel() {
  const t = useT();
  return (
    <div className="space-y-4">
      <Card title={t("sidebar.services")}>
        <ul className="grid grid-cols-1 gap-1.5 text-sm">
          {services.slice(0, 6).map((s) => (
            <li key={s.title} className="flex items-center gap-2">
              <span>{s.icon}</span><span>{s.title}</span>
            </li>
          ))}
        </ul>
      </Card>
      <div className="grid grid-cols-2 gap-2">
        <Btn variant="brand">Start Project</Btn>
        <Btn><Calendar className="h-4 w-4" /> Book</Btn>
      </div>
      <Card title={t("sidebar.testimonials")}>
        <blockquote className="text-sm italic text-foreground/90">"{testimonials[0].quote}"</blockquote>
        <div className="mt-2 text-xs text-muted-foreground">— {testimonials[0].author}, {testimonials[0].role}</div>
      </Card>
    </div>
  );
}

function TechnicalPanel() {
  const t = useT();
  return (
    <div className="space-y-4">
      <Card title={t("sidebar.currentSprint")}>
        <div className="font-medium">{profile.currentSprint}</div>
        <div className="mt-3 h-2 overflow-hidden rounded-full bg-muted">
          <div className="h-full gradient-brand" style={{ width: "62%" }} />
        </div>
        <div className="mt-1 text-xs text-muted-foreground">Day 6 of 10 · 62% complete</div>
      </Card>
      <Card title="Latest Release">
        <div className="text-sm font-medium">Portfolio Platform v1.2</div>
        <div className="text-xs text-muted-foreground">2026-07-10 · main → production</div>
        <div className="mt-2 inline-flex items-center gap-1 rounded-full bg-success/15 px-2 py-0.5 text-[11px] text-success">
          <CheckCircle2 className="h-3 w-3" /> Deployed
        </div>
      </Card>
      <Card title={t("sidebar.repos")}>
        <ul className="space-y-1.5 text-sm">
          {projects.slice(0, 4).map((p) => (
            <li key={p.slug} className="flex items-center gap-2">
              <Github className="h-3.5 w-3.5 text-muted-foreground" />
              <span className="truncate flex-1">Alanarex/{p.slug}</span>
              <ExternalLink className="h-3 w-3 text-muted-foreground" />
            </li>
          ))}
        </ul>
      </Card>
      <Card title="Contributions">
        <div className="grid grid-cols-[repeat(20,1fr)] gap-0.5">
          {Array.from({ length: 100 }).map((_, i) => {
            const intensity = (i * 7) % 5;
            return <div key={i} className="aspect-square rounded-sm" style={{ backgroundColor: `color-mix(in oklab, var(--primary) ${intensity * 22}%, var(--muted))` }} />;
          })}
        </div>
        <div className="mt-2 text-xs text-muted-foreground">1,284 contributions in the last year</div>
      </Card>
      <Card title={t("sidebar.stack")}>
        <div className="flex flex-wrap gap-1.5">
          {skills.slice(0, 8).map((s) => (
            <span key={s.id} className="rounded-full border border-border bg-surface-elevated px-2 py-0.5 text-xs">{s.name}</span>
          ))}
        </div>
      </Card>
      <Card title={t("sidebar.monitoring")}>
        <div className="flex items-center gap-2 text-sm">
          <Activity className="h-4 w-4 text-success" />
          <span>{t("sidebar.allGreen")}</span>
        </div>
      </Card>
    </div>
  );
}

function DefaultPanel() {
  const t = useT();
  return (
    <div className="space-y-4">
      <Card title={t("sidebar.currentProject")}>
        <div className="font-semibold">{profile.currentProject}</div>
        <div className="mt-1 text-sm text-muted-foreground">{profile.currentSprint}</div>
      </Card>
      <Card title={t("sidebar.latestAchievement")}>
        <div className="text-sm">🏆 {profile.latestAchievement}</div>
      </Card>
      <Card title="Featured Projects">
        <ul className="space-y-2">
          {projects.slice(0, 3).map((p) => (
            <li key={p.slug} className="flex items-center gap-3">
              <div className="grid h-9 w-9 place-items-center rounded-lg text-base" style={{ background: p.cover }}>{p.logo}</div>
              <div className="min-w-0 flex-1 text-sm">
                <div className="truncate font-medium">{p.name}</div>
                <div className="truncate text-xs text-muted-foreground">{p.tagline}</div>
              </div>
            </li>
          ))}
        </ul>
      </Card>
      <Card title="Top Skills">
        <div className="flex flex-wrap gap-1.5">
          {skills.slice(0, 10).map((s) => (
            <span key={s.id} className="rounded-full border border-border bg-surface-elevated px-2 py-0.5 text-xs">{s.name}</span>
          ))}
        </div>
      </Card>
    </div>
  );
}

export function RightSidebar() {
  const { prefs } = usePreferences();
  return (
    <aside className="sticky top-0 hidden h-screen w-80 shrink-0 overflow-y-auto scrollbar-thin border-l border-border bg-background px-4 py-4 xl:block">
      {prefs.audience === "recruiter" && <RecruiterPanel />}
      {prefs.audience === "client" && <ClientPanel />}
      {prefs.audience === "technical" && <TechnicalPanel />}
      {prefs.audience === "none" && <DefaultPanel />}
    </aside>
  );
}
