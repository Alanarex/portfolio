import { createFileRoute } from "@tanstack/react-router";
import { PostCard } from "@/components/portfolio/post-card";
import { posts } from "@/data/portfolio";

export const Route = createFileRoute("/_app/highlights")({
  head: () => ({ meta: [{ title: "Highlights — Alaa Khalil" }] }),
  component: HighlightsPage,
});

function HighlightsPage() {
  const top = [...posts].sort((a, b) => b.reactions - a.reactions).slice(0, 6);
  return (
    <div className="mx-auto w-full max-w-2xl px-4 py-6">
      <header className="mb-6">
        <h1 className="text-2xl font-bold sm:text-3xl">Highlights</h1>
        <p className="mt-1 text-sm text-muted-foreground">The proudest moments — releases, milestones, wins.</p>
      </header>
      <div className="space-y-4">
        {top.map((p) => <PostCard key={p.id} post={p} />)}
      </div>
    </div>
  );
}
