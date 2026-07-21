import { Link } from "@tanstack/react-router";
import { Heart, MessageCircle, Eye, Share2, Bookmark } from "lucide-react";
import avatar from "@/assets/avatar.png";
import { profile, skills, type Post } from "@/data/portfolio";
import { useT } from "@/lib/i18n";

function tagFor(id: string) {
  return skills.find((s) => s.id === id);
}

export function PostCard({ post }: { post: Post }) {
  const t = useT();
  return (
    <article className="overflow-hidden rounded-2xl border border-border bg-surface shadow-card transition-shadow hover:shadow-glow">
      <header className="flex items-center gap-3 px-5 pt-4">
        <img src={avatar} alt={profile.name} className="h-10 w-10 rounded-full object-cover" />
        <div className="min-w-0 flex-1">
          <div className="flex items-center gap-1.5">
            <span className="font-semibold">{profile.name}</span>
            <span className="text-xs text-muted-foreground">· {new Date(post.date).toLocaleDateString(undefined, { month: "short", day: "numeric", year: "numeric" })}</span>
          </div>
          <div className="text-xs text-muted-foreground">{profile.title}</div>
        </div>
        <span className="rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-primary">{post.category}</span>
      </header>

      <div className="px-5 pt-3">
        <h3 className="text-lg font-semibold leading-tight">{post.title}</h3>
        <p className="mt-1.5 text-sm text-foreground/80">{post.body}</p>
      </div>

      {post.image && (
        <div className="relative mt-4 h-56 w-full" style={{ background: post.image }}>
          <div className="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent" />
        </div>
      )}

      {post.tech.length > 0 && (
        <div className="flex flex-wrap gap-1.5 px-5 pt-3">
          {post.tech.map((id) => {
            const s = tagFor(id);
            if (!s) return null;
            return (
              <Link key={id} to="/skills" className="rounded-full border border-border bg-surface-elevated px-2 py-0.5 text-xs hover:border-primary/60 hover:text-primary">
                #{s.name}
              </Link>
            );
          })}
        </div>
      )}

      {post.projectSlug && (
        <div className="mx-5 mt-3 rounded-xl border border-border bg-surface-elevated px-3 py-2 text-xs">
          🚀 Related project:{" "}
          <Link to="/projects/$slug" params={{ slug: post.projectSlug }} className="font-medium text-primary hover:underline">
            {post.projectSlug}
          </Link>
        </div>
      )}

      <footer className="mt-4 flex items-center justify-between border-t border-border px-5 py-3 text-sm text-muted-foreground">
        <div className="flex items-center gap-4">
          <button className="inline-flex items-center gap-1.5 hover:text-primary"><Heart className="h-4 w-4" /> {post.reactions}</button>
          <button className="inline-flex items-center gap-1.5 hover:text-primary"><MessageCircle className="h-4 w-4" /> {post.comments}</button>
          <span className="inline-flex items-center gap-1.5"><Eye className="h-4 w-4" /> {post.views.toLocaleString()} {t("post.views")}</span>
        </div>
        <div className="flex items-center gap-2">
          <button className="rounded-lg p-1.5 hover:bg-accent hover:text-foreground"><Bookmark className="h-4 w-4" /></button>
          <button className="rounded-lg p-1.5 hover:bg-accent hover:text-foreground"><Share2 className="h-4 w-4" /></button>
        </div>
      </footer>
    </article>
  );
}
