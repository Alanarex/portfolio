import { Outlet, createFileRoute } from "@tanstack/react-router";
import { useState } from "react";
import { LeftSidebar } from "@/components/portfolio/left-sidebar";
import { RightSidebar } from "@/components/portfolio/right-sidebar";
import { MobileNav } from "@/components/portfolio/mobile-nav";
import { Splash } from "@/components/portfolio/splash";
import { Welcome } from "@/components/portfolio/welcome";
import { CommandPalette } from "@/components/portfolio/command-palette";
import { Footer } from "@/components/portfolio/footer";
import { usePreferences } from "@/hooks/use-preferences";
import { useHydrated } from "@/hooks/use-hydrated";

export const Route = createFileRoute("/_app")({
  component: AppLayout,
});

function AppLayout() {
  const hydrated = useHydrated();
  const { prefs } = usePreferences();
  const [splashDone, setSplashDone] = useState(false);

  const showSplash = hydrated && !prefs.hasVisited && !splashDone;
  const showWelcome = hydrated && !prefs.hasVisited && splashDone;

  return (
    <div className="min-h-screen bg-background text-foreground">
      <div className="mx-auto flex w-full max-w-[1600px]">
        <LeftSidebar />
        <main className="flex min-w-0 flex-1 flex-col pb-20 lg:pb-0">
          <Outlet />
          <Footer />
        </main>
        <RightSidebar />
      </div>
      <MobileNav />
      <CommandPalette />
      {showSplash && <Splash onDone={() => setSplashDone(true)} />}
      {showWelcome && <Welcome onDone={() => setSplashDone(false)} />}
    </div>
  );
}
