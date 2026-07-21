import { createFileRoute } from "@tanstack/react-router";
import { useState } from "react";
import { media } from "@/data/portfolio";
import { cn } from "@/lib/utils";

export const Route = createFileRoute("/_app/media")({
  head: () => ({ meta: [{ title: "Media — Alaa Khalil" }] }),
  component: MediaPage,
});

const TAGS = ["all", "events", "office", "product", "team", "media"];

function MediaPage() {
  const [tag, setTag] = useState("all");
  const filtered = media.filter((m) => tag === "all" || m.tag === tag);
  return (
    <div className="mx-auto w-full max-w-4xl px-4 py-6">
      <header className="mb-6">
        <h1 className="text-2xl font-bold sm:text-3xl">Media</h1>
        <p className="mt-1 text-sm text-muted-foreground">Talks, workspace, product screenshots and events.</p>
      </header>
      <div className="mb-4 flex gap-2 overflow-x-auto scrollbar-thin pb-1">
        {TAGS.map((t) => (
          <button key={t} onClick={() => setTag(t)} className={cn("shrink-0 rounded-full border px-3.5 py-1.5 text-xs font-medium capitalize", tag === t ? "border-primary bg-primary text-primary-foreground" : "border-border bg-surface hover:bg-accent")}>{t}</button>
        ))}
      </div>
      <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
        {filtered.map((m) => (
          <div key={m.id} className="group relative aspect-square overflow-hidden rounded-2xl shadow-card" style={{ background: m.gradient }}>
            <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />
            <div className="absolute left-3 top-3 rounded-full bg-black/40 px-2 py-0.5 text-[10px] uppercase text-white backdrop-blur">{m.kind}</div>
            <div className="absolute inset-x-3 bottom-3 text-sm font-medium text-white">{m.title}</div>
          </div>
        ))}
      </div>
    </div>
  );
}
