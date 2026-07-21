import { useEffect, useState } from "react";
import { Logo } from "./logo";
import { useT } from "@/lib/i18n";

export function Splash({ onDone }: { onDone: () => void }) {
  const t = useT();
  const [leaving, setLeaving] = useState(false);

  useEffect(() => {
    const t1 = setTimeout(() => setLeaving(true), 1600);
    const t2 = setTimeout(onDone, 2000);
    return () => { clearTimeout(t1); clearTimeout(t2); };
  }, [onDone]);

  return (
    <div
      className="fixed inset-0 z-[100] flex items-center justify-center overflow-hidden bg-background transition-opacity duration-500"
      style={{ opacity: leaving ? 0 : 1 }}
    >
      <div className="absolute inset-0 gradient-hero" />
      {/* particles */}
      <div className="absolute inset-0 overflow-hidden">
        {Array.from({ length: 24 }).map((_, i) => (
          <span
            key={i}
            className="absolute rounded-full bg-primary/40 blur-sm"
            style={{
              width: `${4 + (i % 5) * 3}px`,
              height: `${4 + (i % 5) * 3}px`,
              left: `${(i * 37) % 100}%`,
              top: `${(i * 53) % 100}%`,
              animation: `float-slow ${6 + (i % 4)}s ease-in-out ${i * 0.15}s infinite`,
              opacity: 0.4,
            }}
          />
        ))}
      </div>
      <div className="relative flex flex-col items-center gap-6" style={{ animation: "logo-reveal 1.4s cubic-bezier(.2,.7,.2,1) both" }}>
        <div className="rounded-3xl bg-surface p-6 shadow-glow">
          <Logo className="h-20 w-20" />
        </div>
        <p className="max-w-sm text-center text-lg font-medium text-foreground/90">{t("slogan")}</p>
      </div>
    </div>
  );
}
