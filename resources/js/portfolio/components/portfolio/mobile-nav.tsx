import { Link, useRouterState } from "@tanstack/react-router";
import { Home, Rocket, Search, Bell, User, MessageCircle } from "lucide-react";
import { cn } from "@/lib/utils";

const ITEMS = [
  { to: "/", icon: Home, label: "Home" },
  { to: "/projects", icon: Rocket, label: "Projects" },
  { to: "/search", icon: Search, label: "Search" },
  { to: "/highlights", icon: Bell, label: "Alerts" },
  { to: "/profile", icon: User, label: "Profile" },
];

export function MobileNav() {
  const pathname = useRouterState({ select: (s) => s.location.pathname });
  return (
    <>
      <nav className="fixed inset-x-0 bottom-0 z-40 flex items-center justify-around border-t border-border bg-surface/90 px-2 py-2 backdrop-blur-xl lg:hidden">
        {ITEMS.map((it) => {
          const Icon = it.icon;
          const active = it.to === "/" ? pathname === "/" : pathname.startsWith(it.to);
          return (
            <Link key={it.to} to={it.to} className={cn("flex flex-col items-center gap-0.5 rounded-lg px-3 py-1.5 text-[10px] font-medium", active ? "text-primary" : "text-muted-foreground")}>
              <Icon className="h-5 w-5" />
              {it.label}
            </Link>
          );
        })}
      </nav>
      <Link
        to="/contact"
        className="fixed bottom-20 right-4 z-40 grid h-12 w-12 place-items-center rounded-full gradient-brand text-primary-foreground shadow-glow transition-transform hover:scale-110 lg:bottom-6"
        aria-label="Contact"
      >
        <MessageCircle className="h-5 w-5" />
      </Link>
    </>
  );
}
