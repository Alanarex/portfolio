import { Link, useRouterState } from "@tanstack/react-router";
import { Home, Star, Rocket, Briefcase, GraduationCap, Brain, Award, Image as ImageIcon, Activity, BarChart3, MessageSquare, User, Moon, Sun, Languages, Users } from "lucide-react";
import avatar from "@/assets/avatar.png";
import { Logo } from "./logo";
import { profile, stats } from "@/data/portfolio";
import { useT } from "@/lib/i18n";
import { usePreferences } from "@/hooks/use-preferences";
import { cn } from "@/lib/utils";

const NAV = [
  { to: "/", icon: Home, key: "nav.feed" as const },
  { to: "/highlights", icon: Star, key: "nav.highlights" as const },
  { to: "/projects", icon: Rocket, key: "nav.projects" as const },
  { to: "/experience", icon: Briefcase, key: "nav.experience" as const },
  { to: "/education", icon: GraduationCap, key: "nav.education" as const },
  { to: "/skills", icon: Brain, key: "nav.skills" as const },
  { to: "/certifications", icon: Award, key: "nav.certifications" as const },
  { to: "/media", icon: ImageIcon, key: "nav.media" as const },
  { to: "/activity", icon: Activity, key: "nav.activity" as const },
  { to: "/analytics", icon: BarChart3, key: "nav.analytics" as const },
  { to: "/contact", icon: MessageSquare, key: "nav.contact" as const },
  { to: "/profile", icon: User, key: "nav.profile" as const },
];

export function LeftSidebar() {
  const t = useT();
  const pathname = useRouterState({ select: (s) => s.location.pathname });
  const { prefs, setTheme, setLanguage, setAudience } = usePreferences();

  return (
    <aside className="sticky top-0 hidden h-screen w-72 shrink-0 flex-col border-r border-border bg-surface/50 backdrop-blur-xl lg:flex">
      <div className="flex items-center gap-2 border-b border-border px-5 py-4">
        <Logo className="h-8 w-8" />
        <div className="flex flex-col leading-tight">
          <span className="text-sm font-bold">{profile.name}</span>
          <span className="text-xs text-muted-foreground">{profile.handle}</span>
        </div>
      </div>

      <div className="flex-1 overflow-y-auto scrollbar-thin px-3 py-4">
        {/* Profile card */}
        <div className="mb-4 overflow-hidden rounded-2xl border border-border bg-surface shadow-card">
          <div className="h-16 gradient-brand" />
          <div className="px-4 pb-4 -mt-8">
            <div className="mb-3 h-16 w-16 overflow-hidden rounded-2xl border-4 border-surface shadow-card">
              <img src={avatar} alt={profile.name} className="h-full w-full object-cover" />
            </div>
            <div className="font-semibold">{profile.name}</div>
            <div className="text-xs text-muted-foreground">{profile.title}</div>
            <div className="mt-1 text-xs text-muted-foreground">📍 {profile.location}</div>
            <div className="mt-2 inline-flex items-center gap-1.5 rounded-full bg-success/15 px-2 py-0.5 text-[11px] font-medium text-success">
              <span className="h-1.5 w-1.5 rounded-full bg-success" style={{ animation: "pulse-dot 2s ease-in-out infinite" }} />
              {t("sidebar.availability")}
            </div>

            <div className="mt-4 grid grid-cols-4 gap-2 border-t border-border pt-3 text-center">
              {[
                { n: stats.projects, k: "stats.projects" as const },
                { n: stats.posts, k: "stats.posts" as const },
                { n: stats.experiences, k: "stats.experiences" as const },
                { n: stats.skills, k: "stats.skills" as const },
              ].map((s) => (
                <div key={s.k}>
                  <div className="text-sm font-bold">{s.n}</div>
                  <div className="text-[10px] uppercase tracking-wider text-muted-foreground">{t(s.k)}</div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Nav */}
        <nav className="space-y-0.5">
          {NAV.map((item) => {
            const active = item.to === "/" ? pathname === "/" : pathname.startsWith(item.to);
            const Icon = item.icon;
            return (
              <Link
                key={item.to}
                to={item.to}
                className={cn(
                  "flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition-colors",
                  active ? "bg-primary/10 text-primary" : "text-foreground/80 hover:bg-accent hover:text-foreground",
                )}
              >
                <Icon className={cn("h-4 w-4", active && "text-primary")} />
                {t(item.key)}
              </Link>
            );
          })}
        </nav>
      </div>

      {/* Footer controls */}
      <div className="border-t border-border p-3 space-y-2">
        <div className="flex items-center gap-1 rounded-lg bg-muted p-1">
          <button
            onClick={() => setTheme("dark")}
            className={cn("flex-1 rounded-md py-1.5 text-xs font-medium inline-flex items-center justify-center gap-1", prefs.theme === "dark" ? "bg-surface shadow-sm text-foreground" : "text-muted-foreground")}
          ><Moon className="h-3 w-3" /> Dark</button>
          <button
            onClick={() => setTheme("light")}
            className={cn("flex-1 rounded-md py-1.5 text-xs font-medium inline-flex items-center justify-center gap-1", prefs.theme === "light" ? "bg-surface shadow-sm text-foreground" : "text-muted-foreground")}
          ><Sun className="h-3 w-3" /> Light</button>
        </div>
        <div className="flex items-center gap-1 rounded-lg bg-muted p-1">
          {(["fr","en"] as const).map((l) => (
            <button key={l} onClick={() => setLanguage(l)}
              className={cn("flex-1 rounded-md py-1.5 text-xs font-medium inline-flex items-center justify-center gap-1", prefs.language === l ? "bg-surface shadow-sm text-foreground" : "text-muted-foreground")}
            ><Languages className="h-3 w-3" /> {l.toUpperCase()}</button>
          ))}
        </div>
        <div className="flex items-center gap-2 rounded-lg border border-border px-2 py-1.5 text-xs">
          <Users className="h-3.5 w-3.5 text-muted-foreground" />
          <select
            value={prefs.audience}
            onChange={(e) => setAudience(e.target.value as never)}
            className="flex-1 bg-transparent outline-none"
          >
            <option value="none">General audience</option>
            <option value="recruiter">Recruiter</option>
            <option value="client">Client</option>
            <option value="technical">Technical</option>
          </select>
        </div>
      </div>
    </aside>
  );
}
