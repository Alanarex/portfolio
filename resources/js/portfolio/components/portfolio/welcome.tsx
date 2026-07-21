import { Briefcase, Rocket, Code2, ArrowRight, Moon, Sun } from "lucide-react";
import { Logo } from "./logo";
import { usePreferences, type Audience, type Language, type Theme } from "@/hooks/use-preferences";
import { useT } from "@/lib/i18n";
import { cn } from "@/lib/utils";

export function Welcome({ onDone }: { onDone: () => void }) {
  const t = useT();
  const { prefs, setAudience, setLanguage, setTheme, markVisited } = usePreferences();

  const cards: { id: Audience; icon: React.ReactNode; title: string; desc: string }[] = [
    { id: "recruiter", icon: <Briefcase className="h-6 w-6" />, title: t("welcome.recruiter"), desc: t("welcome.recruiter.desc") },
    { id: "client", icon: <Rocket className="h-6 w-6" />, title: t("welcome.client"), desc: t("welcome.client.desc") },
    { id: "technical", icon: <Code2 className="h-6 w-6" />, title: t("welcome.technical"), desc: t("welcome.technical.desc") },
  ];

  const enter = (audience: Audience) => {
    setAudience(audience);
    markVisited();
    onDone();
  };

  return (
    <div className="fixed inset-0 z-[90] overflow-y-auto bg-background">
      <div className="absolute inset-0 gradient-hero pointer-events-none" />
      <div className="relative mx-auto flex min-h-screen w-full max-w-3xl flex-col items-center justify-center px-6 py-12">
        <div className="mb-8 flex flex-col items-center gap-4">
          <div className="rounded-2xl bg-surface p-3 shadow-card">
            <Logo className="h-10 w-10" />
          </div>
          <h1 className="text-center text-3xl font-bold sm:text-4xl">{t("welcome.title")}</h1>
          <p className="text-center text-base text-muted-foreground">{t("welcome.subtitle")}</p>
        </div>

        <div className="grid w-full grid-cols-1 gap-4 sm:grid-cols-3">
          {cards.map((c) => (
            <button
              key={c.id}
              onClick={() => enter(c.id)}
              className="group relative flex flex-col items-start gap-3 rounded-2xl border border-border bg-surface p-5 text-left shadow-card transition-all hover:-translate-y-1 hover:border-primary/60 hover:shadow-glow"
            >
              <div className="rounded-xl gradient-brand p-2.5 text-primary-foreground">{c.icon}</div>
              <div>
                <div className="font-semibold">{c.title}</div>
                <div className="mt-1 text-sm text-muted-foreground">{c.desc}</div>
              </div>
              <ArrowRight className="absolute right-4 top-4 h-4 w-4 text-muted-foreground transition-transform group-hover:translate-x-1 group-hover:text-primary" />
            </button>
          ))}
        </div>

        <button
          onClick={() => enter("none")}
          className="mt-4 text-sm text-muted-foreground underline-offset-4 hover:text-foreground hover:underline"
        >
          {t("welcome.skip")}
        </button>

        <div className="mt-10 grid w-full grid-cols-1 gap-6 sm:grid-cols-2">
          <div className="rounded-xl border border-border bg-surface/60 p-4">
            <div className="mb-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">{t("welcome.language")}</div>
            <div className="flex gap-2">
              {(["fr","en"] as Language[]).map((l) => (
                <button
                  key={l}
                  onClick={() => setLanguage(l)}
                  className={cn("flex-1 rounded-lg border px-3 py-2 text-sm font-medium transition-colors",
                    prefs.language === l ? "border-primary bg-primary text-primary-foreground" : "border-border hover:bg-accent")}
                >
                  {l === "fr" ? "🇫🇷 Français" : "🇬🇧 English"}
                </button>
              ))}
            </div>
          </div>
          <div className="rounded-xl border border-border bg-surface/60 p-4">
            <div className="mb-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">{t("welcome.theme")}</div>
            <div className="flex gap-2">
              {(["dark","light"] as Theme[]).map((th) => (
                <button
                  key={th}
                  onClick={() => setTheme(th)}
                  className={cn("flex-1 rounded-lg border px-3 py-2 text-sm font-medium transition-colors inline-flex items-center justify-center gap-2",
                    prefs.theme === th ? "border-primary bg-primary text-primary-foreground" : "border-border hover:bg-accent")}
                >
                  {th === "dark" ? <><Moon className="h-4 w-4" /> {t("welcome.dark")}</> : <><Sun className="h-4 w-4" /> {t("welcome.light")}</>}
                </button>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
