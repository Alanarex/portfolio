import { createFileRoute, Link } from "@tanstack/react-router";
import { Download, MessageSquare, Rocket, Calendar, Github, Linkedin, Mail, MessageCircle } from "lucide-react";
import avatar from "@/assets/avatar.png";
import { profile, projects, skills, stats } from "@/data/portfolio";
import { useT } from "@/lib/i18n";

export const Route = createFileRoute("/_app/profile")({
  head: () => ({ meta: [{ title: "Profile — Alaa Khalil" }, { name: "description", content: profile.bio }] }),
  component: ProfilePage,
});

const TABS = ["Feed", "Projects", "Experience", "Skills", "Media", "Activity", "About"];

function ProfilePage() {
  const t = useT();
  return (
    <div className="w-full">
      {/* Cover */}
      <div className="relative h-56 sm:h-72 gradient-brand">
        <div className="absolute inset-0 gradient-hero" />
      </div>

      <div className="mx-auto max-w-3xl px-4">
        {/* Header row */}
        <div className="-mt-16 flex flex-col gap-4 sm:flex-row sm:items-end">
          <div className="h-28 w-28 shrink-0 overflow-hidden rounded-3xl border-4 border-background shadow-glow">
            <img src={avatar} alt={profile.name} className="h-full w-full object-cover" />
          </div>
          <div className="flex-1 pt-2">
            <div className="flex items-center gap-2">
              <h1 className="text-2xl font-bold sm:text-3xl">{profile.name}</h1>
              <span className="rounded-full bg-success/15 px-2 py-0.5 text-xs font-medium text-success inline-flex items-center gap-1">
                <span className="h-1.5 w-1.5 rounded-full bg-success" style={{ animation: "pulse-dot 2s ease-in-out infinite" }} />
                Available
              </span>
            </div>
            <div className="text-sm text-muted-foreground">{profile.title} · 📍 {profile.location}</div>
          </div>
          <div className="flex flex-wrap gap-2">
            <button className="inline-flex items-center gap-2 rounded-xl gradient-brand px-4 py-2 text-sm font-semibold text-primary-foreground shadow-glow hover:opacity-90"><Download className="h-4 w-4" /> {t("profile.cv")}</button>
            <button className="inline-flex items-center gap-2 rounded-xl border border-border bg-surface px-4 py-2 text-sm font-semibold hover:bg-accent"><MessageSquare className="h-4 w-4" /> {t("profile.hire")}</button>
            <button className="inline-flex items-center gap-2 rounded-xl border border-border bg-surface px-4 py-2 text-sm font-semibold hover:bg-accent"><Rocket className="h-4 w-4" /> {t("profile.start")}</button>
            <button className="inline-flex items-center gap-2 rounded-xl border border-border bg-surface px-4 py-2 text-sm font-semibold hover:bg-accent"><Calendar className="h-4 w-4" /> {t("profile.meeting")}</button>
          </div>
        </div>

        {/* Bio + socials */}
        <div className="mt-6 rounded-2xl border border-border bg-surface p-5 shadow-card">
          <p className="text-sm leading-relaxed text-foreground/90">{profile.bio}</p>
          <div className="mt-4 flex flex-wrap gap-2">
            <a href={profile.socials[0].href} className="inline-flex items-center gap-1.5 rounded-full border border-border bg-surface-elevated px-3 py-1 text-xs hover:border-primary/60"><Mail className="h-3.5 w-3.5" /> Email</a>
            <a href={profile.socials[1].href} className="inline-flex items-center gap-1.5 rounded-full border border-border bg-surface-elevated px-3 py-1 text-xs hover:border-primary/60"><MessageCircle className="h-3.5 w-3.5" /> WhatsApp</a>
            <a href={profile.socials[2].href} className="inline-flex items-center gap-1.5 rounded-full border border-border bg-surface-elevated px-3 py-1 text-xs hover:border-primary/60"><Github className="h-3.5 w-3.5" /> GitHub</a>
            <a href={profile.socials[3].href} className="inline-flex items-center gap-1.5 rounded-full border border-border bg-surface-elevated px-3 py-1 text-xs hover:border-primary/60">GitLab</a>
            <a href={profile.socials[4].href} className="inline-flex items-center gap-1.5 rounded-full border border-border bg-surface-elevated px-3 py-1 text-xs hover:border-primary/60"><Linkedin className="h-3.5 w-3.5" /> LinkedIn</a>
          </div>
        </div>

        {/* Stats */}
        <div className="mt-4 grid grid-cols-4 gap-2">
          {[["Projects", stats.projects],["Posts", stats.posts],["Experience", stats.experiences],["Skills", stats.skills]].map(([l, v]) => (
            <div key={l as string} className="rounded-xl border border-border bg-surface p-3 text-center shadow-card">
              <div className="text-lg font-bold">{v}</div>
              <div className="text-[10px] uppercase tracking-wider text-muted-foreground">{l}</div>
            </div>
          ))}
        </div>

        {/* Tabs */}
        <div className="mt-6 flex gap-1 overflow-x-auto border-b border-border">
          {TABS.map((tab, i) => (
            <button key={tab} className={`shrink-0 border-b-2 px-4 py-2 text-sm font-medium ${i === 0 ? "border-primary text-primary" : "border-transparent text-muted-foreground hover:text-foreground"}`}>{tab}</button>
          ))}
        </div>

        {/* Featured projects */}
        <div className="mt-4 grid grid-cols-1 gap-3 pb-8 sm:grid-cols-2">
          {projects.slice(0, 4).map((p) => (
            <Link key={p.slug} to="/projects/$slug" params={{ slug: p.slug }} className="group overflow-hidden rounded-2xl border border-border bg-surface shadow-card transition-all hover:-translate-y-0.5 hover:shadow-glow">
              <div className="h-24" style={{ background: p.cover }} />
              <div className="p-4">
                <div className="flex items-center gap-2">
                  <span>{p.logo}</span>
                  <div className="font-semibold">{p.name}</div>
                </div>
                <div className="mt-1 text-xs text-muted-foreground">{p.tagline}</div>
                <div className="mt-2 flex flex-wrap gap-1">
                  {p.tech.slice(0, 3).map((tid) => {
                    const s = skills.find((sk) => sk.id === tid);
                    return s ? <span key={tid} className="rounded-full bg-primary/10 px-2 py-0.5 text-[10px] text-primary">{s.name}</span> : null;
                  })}
                </div>
              </div>
            </Link>
          ))}
        </div>
      </div>
    </div>
  );
}
