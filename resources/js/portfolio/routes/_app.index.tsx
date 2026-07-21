import { createFileRoute } from "@tanstack/react-router";
import { useMemo, useState } from "react";
import { Search } from "lucide-react";
import { PostCard } from "@/components/portfolio/post-card";
import { posts } from "@/data/portfolio";
import { useT } from "@/lib/i18n";
import { cn } from "@/lib/utils";

export const Route = createFileRoute("/_app/")({
  head: () => ({ meta: [{ title: "Feed — Alaa Khalil" }] }),
  component: FeedPage,
});

const FILTERS = [
  { id: "all", key: "feed.filter.all" as const },
  { id: "projects", key: "feed.filter.projects" as const },
  { id: "career", key: "feed.filter.career" as const },
  { id: "education", key: "feed.filter.education" as const },
  { id: "achievements", key: "feed.filter.achievements" as const },
  { id: "releases", key: "feed.filter.releases" as const },
  { id: "github", key: "feed.filter.github" as const },
  { id: "articles", key: "feed.filter.articles" as const },
  { id: "media", key: "feed.filter.media" as const },
];

function FeedPage() {
  const t = useT();
  const [filter, setFilter] = useState("all");
  const [query, setQuery] = useState("");

  const filtered = useMemo(() => {
    return posts.filter((p) => {
      const okFilter = filter === "all" || p.category === filter;
      const q = query.trim().toLowerCase();
      const okQuery = !q || p.title.toLowerCase().includes(q) || p.body.toLowerCase().includes(q) || p.tech.some((tt) => tt.includes(q));
      return okFilter && okQuery;
    });
  }, [filter, query]);

  return (
    <div className="mx-auto w-full max-w-2xl px-4 py-6">
      {/* Search bar */}
      <div className="mb-4 flex items-center gap-2 rounded-2xl border border-border bg-surface px-4 py-2.5 shadow-card">
        <Search className="h-4 w-4 text-muted-foreground" />
        <input
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          placeholder={t("feed.search")}
          className="flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
        />
        <kbd className="hidden rounded-md border border-border bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground sm:inline">⌘K</kbd>
      </div>

      {/* Filters */}
      <div className="mb-4 flex gap-2 overflow-x-auto scrollbar-thin pb-1">
        {FILTERS.map((f) => (
          <button
            key={f.id}
            onClick={() => setFilter(f.id)}
            className={cn(
              "shrink-0 rounded-full border px-3.5 py-1.5 text-xs font-medium transition-colors",
              filter === f.id ? "border-primary bg-primary text-primary-foreground" : "border-border bg-surface hover:bg-accent",
            )}
          >
            {t(f.key)}
          </button>
        ))}
      </div>

      {/* Feed */}
      <div className="space-y-4">
        {filtered.map((p) => <PostCard key={p.id} post={p} />)}
        {filtered.length === 0 && (
          <div className="rounded-2xl border border-dashed border-border p-12 text-center text-sm text-muted-foreground">
            No posts match your filters.
          </div>
        )}
      </div>
    </div>
  );
}
