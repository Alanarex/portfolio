import { createFileRoute, Link, notFound } from "@tanstack/react-router";
import { ArrowLeft, Github, ExternalLink, Eye, Calendar } from "lucide-react";
import { projects, skills, posts } from "@/data/portfolio";
import { PostCard } from "@/components/portfolio/post-card";

export const Route = createFileRoute("/_app/projects/$slug")({
  loader: ({ params }) => {
    const project = projects.find((p) => p.slug === params.slug);
    if (!project) throw notFound();
    return { project };
  },
  head: ({ loaderData }) => ({ meta: [{ title: loaderData ? `${loaderData.project.name} — Alaa Khalil` : "Project" }] }),
  component: ProjectDetail,
  notFoundComponent: () => (
    <div className="p-12 text-center">
      <h2 className="text-xl font-semibold">Project not found</h2>
      <Link to="/projects" className="mt-4 inline-block text-primary hover:underline">← Back to projects</Link>
    </div>
  ),
});

const TABS = ["Timeline", "Features", "Gallery", "Demo", "Architecture", "Versions", "Statistics", "Comments"];

function ProjectDetail() {
  const { project } = Route.useLoaderData();
  const related = posts.filter((p) => p.projectSlug === project.slug);

  return (
    <div className="w-full">
      <div className="relative h-48 sm:h-64" style={{ background: project.cover }}>
        <div className="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" />
        <Link to="/projects" className="absolute left-4 top-4 inline-flex items-center gap-1 rounded-full bg-black/40 px-3 py-1.5 text-xs text-white backdrop-blur hover:bg-black/60"><ArrowLeft className="h-3.5 w-3.5" /> Projects</Link>
      </div>

      <div className="mx-auto max-w-3xl px-4">
        <div className="-mt-10 flex flex-col gap-4 sm:flex-row sm:items-end">
          <div className="grid h-20 w-20 shrink-0 place-items-center rounded-2xl border-4 border-background bg-surface text-3xl shadow-card">{project.logo}</div>
          <div className="flex-1 pt-2">
            <div className="flex items-center gap-2">
              <h1 className="text-2xl font-bold sm:text-3xl">{project.name}</h1>
              <span className={`rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase ${project.status === "shipped" ? "bg-success/15 text-success" : project.status === "in-progress" ? "bg-warning/15 text-warning" : "bg-muted text-muted-foreground"}`}>{project.status}</span>
            </div>
            <p className="text-sm text-muted-foreground">{project.tagline}</p>
          </div>
          <div className="flex gap-2">
            {project.demoUrl && <a href={project.demoUrl} className="inline-flex items-center gap-2 rounded-xl gradient-brand px-4 py-2 text-sm font-semibold text-primary-foreground shadow-glow hover:opacity-90"><ExternalLink className="h-4 w-4" /> Demo</a>}
            {project.githubUrl && <a href={project.githubUrl} className="inline-flex items-center gap-2 rounded-xl border border-border bg-surface px-4 py-2 text-sm font-semibold hover:bg-accent"><Github className="h-4 w-4" /> Code</a>}
          </div>
        </div>

        <div className="mt-4 flex flex-wrap gap-1.5">
          {project.tech.map((tid: string) => {
            const s = skills.find((sk) => sk.id === tid);
            return s ? <span key={tid} className="rounded-full border border-border bg-surface-elevated px-2.5 py-1 text-xs">{s.name}</span> : null;
          })}
        </div>

        <div className="mt-4 grid grid-cols-3 gap-2 text-center">
          <div className="rounded-xl border border-border bg-surface p-3"><Eye className="mx-auto mb-1 h-4 w-4 text-muted-foreground" /><div className="text-sm font-bold">{project.views.toLocaleString()}</div><div className="text-[10px] uppercase text-muted-foreground">Views</div></div>
          <div className="rounded-xl border border-border bg-surface p-3"><Calendar className="mx-auto mb-1 h-4 w-4 text-muted-foreground" /><div className="text-sm font-bold">{project.completedAt ?? "Ongoing"}</div><div className="text-[10px] uppercase text-muted-foreground">Timeline</div></div>
          <div className="rounded-xl border border-border bg-surface p-3"><div className="mx-auto mb-1 text-base">{project.logo}</div><div className="text-sm font-bold">{project.category}</div><div className="text-[10px] uppercase text-muted-foreground">Category</div></div>
        </div>

        <div className="mt-6 flex gap-1 overflow-x-auto border-b border-border">
          {TABS.map((tab, i) => (
            <button key={tab} className={`shrink-0 border-b-2 px-4 py-2 text-sm font-medium ${i === 0 ? "border-primary text-primary" : "border-transparent text-muted-foreground hover:text-foreground"}`}>{tab}</button>
          ))}
        </div>

        <div className="mt-6 space-y-4 pb-8">
          <h2 className="text-lg font-semibold">Timeline</h2>
          {related.length > 0 ? related.map((p) => <PostCard key={p.id} post={p} />) : (
            <div className="rounded-2xl border border-dashed border-border p-8 text-center text-sm text-muted-foreground">
              No posts yet for this project.
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
