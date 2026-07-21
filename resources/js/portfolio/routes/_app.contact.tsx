import { createFileRoute } from "@tanstack/react-router";
import { MessageCircle, Mail, Linkedin, Calendar, Clock, Globe } from "lucide-react";
import avatar from "@/assets/avatar.png";
import { profile } from "@/data/portfolio";

export const Route = createFileRoute("/_app/contact")({
  head: () => ({ meta: [{ title: "Contact — Alaa Khalil" }] }),
  component: ContactPage,
});

const METHODS = [
  { icon: MessageCircle, label: "WhatsApp", desc: "Coordonnées à publier depuis le dashboard", cta: "Bientôt disponible", href: "#contact", tone: "success" },
  { icon: Mail, label: "Email", desc: "Coordonnées à publier depuis le dashboard", cta: "Bientôt disponible", href: "#contact", tone: "primary" },
  { icon: Linkedin, label: "LinkedIn", desc: "Profil à publier depuis le dashboard", cta: "Bientôt disponible", href: "#contact", tone: "primary" },
  { icon: Calendar, label: "Schedule a call", desc: "30-min intro over Google Meet", cta: "Book a slot", href: "#", tone: "primary" },
];

function ContactPage() {
  return (
    <div className="mx-auto w-full max-w-2xl px-4 py-6">
      {/* Header */}
      <div className="rounded-2xl border border-border bg-surface p-5 shadow-card">
        <div className="flex items-center gap-4">
          <img src={avatar} alt={profile.name} className="h-14 w-14 rounded-full object-cover" />
          <div className="flex-1">
            <div className="font-semibold">{profile.name}</div>
            <div className="text-xs text-success inline-flex items-center gap-1"><span className="h-1.5 w-1.5 rounded-full bg-success" /> Online now</div>
          </div>
        </div>
        <h1 className="mt-4 text-xl font-bold">Let's build something together.</h1>
        <div className="mt-2 flex flex-wrap gap-3 text-xs text-muted-foreground">
          <span className="inline-flex items-center gap-1"><Clock className="h-3 w-3" /> Replies within 2h</span>
          <span className="inline-flex items-center gap-1"><Globe className="h-3 w-3" /> Paris · UTC+1</span>
          <span>· Available for new projects</span>
        </div>
      </div>

      {/* Methods */}
      <div className="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
        {METHODS.map((m) => (
          <a key={m.label} href={m.href} className="group flex items-start gap-3 rounded-2xl border border-border bg-surface p-4 shadow-card transition-all hover:-translate-y-0.5 hover:border-primary/60 hover:shadow-glow">
            <div className="grid h-10 w-10 place-items-center rounded-xl gradient-brand text-primary-foreground"><m.icon className="h-5 w-5" /></div>
            <div className="flex-1">
              <div className="font-semibold">{m.label}</div>
              <div className="text-xs text-muted-foreground">{m.desc}</div>
              <div className="mt-2 text-xs font-medium text-primary group-hover:underline">{m.cta} →</div>
            </div>
          </a>
        ))}
      </div>

      {/* Quick message */}
      <div className="mt-4 rounded-2xl border border-border bg-surface p-5 shadow-card">
        <div className="mb-3 text-sm font-semibold">Quick message</div>
        <div className="space-y-2">
          <input placeholder="Your name" className="w-full rounded-xl border border-border bg-background px-3 py-2 text-sm outline-none focus:border-primary" />
          <input placeholder="Email" className="w-full rounded-xl border border-border bg-background px-3 py-2 text-sm outline-none focus:border-primary" />
          <textarea placeholder="What are you building?" rows={4} className="w-full rounded-xl border border-border bg-background px-3 py-2 text-sm outline-none focus:border-primary" />
          <button className="w-full rounded-xl gradient-brand py-2.5 text-sm font-semibold text-primary-foreground shadow-glow hover:opacity-90">Send message</button>
        </div>
      </div>
    </div>
  );
}
