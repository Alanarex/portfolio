import { Link } from "@tanstack/react-router";
import { Github, Linkedin, Mail } from "lucide-react";
import { Logo } from "./logo";
import { profile, version, deployedAt } from "@/data/portfolio";
import { useT } from "@/lib/i18n";

export function Footer() {
  const t = useT();
  return (
    <footer className="mt-12 border-t border-border bg-surface/60 px-6 py-8">
      <div className="mx-auto flex max-w-5xl flex-col items-start gap-6 sm:flex-row sm:items-center sm:justify-between">
        <div className="flex items-center gap-3">
          <Logo className="h-8 w-8" />
          <div className="text-sm">
            <div className="font-semibold">{profile.name}</div>
            <div className="text-xs text-muted-foreground">© {new Date().getFullYear()} · {t("footer.rights")}</div>
          </div>
        </div>
        <nav className="flex flex-wrap gap-4 text-xs text-muted-foreground">
          <Link to="/profile" className="hover:text-foreground">About</Link>
          <Link to="/projects" className="hover:text-foreground">Projects</Link>
          <Link to="/contact" className="hover:text-foreground">Contact</Link>
          <a href="#" className="hover:text-foreground">Privacy</a>
          <a href="#" className="hover:text-foreground">Terms</a>
        </nav>
        <div className="flex items-center gap-3 text-muted-foreground">
          <a href={profile.socials[2].href} className="hover:text-foreground"><Github className="h-4 w-4" /></a>
          <a href={profile.socials[4].href} className="hover:text-foreground"><Linkedin className="h-4 w-4" /></a>
          <a href={profile.socials[0].href} className="hover:text-foreground"><Mail className="h-4 w-4" /></a>
        </div>
      </div>
      <div className="mx-auto mt-4 max-w-5xl text-center text-[11px] text-muted-foreground">
        {t("footer.version")} {version} · {t("footer.deployed")} {deployedAt}
      </div>
    </footer>
  );
}
