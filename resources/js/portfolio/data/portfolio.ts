// Temporary frontend fixtures imported from the Lovable prototype.
// Identity fields below use verified project context. Remaining portfolio entities
// must be replaced by Laravel API data before production release.

export interface Profile {
  name: string;
  handle: string;
  title: string;
  location: string;
  availability: string;
  company: string;
  currentProject: string;
  currentSprint: string;
  latestAchievement: string;
  bio: string;
  socials: { label: string; href: string; kind: "email" | "whatsapp" | "github" | "gitlab" | "linkedin" }[];
  languages: { name: string; level: string }[];
}

export const profile: Profile = {
  name: "Alaa Khalil",
  handle: "@Alanarex",
  title: "Développeur Full-Stack PHP / Laravel",
  location: "Paris, France",
  availability: "Ouvert aux opportunités à Paris et en Île-de-France",
  company: "Cosika",
  currentProject: "Portfolio Platform",
  currentSprint: "Sprint 08 — Social Feed & AI Automation",
  latestAchievement: "Released Portfolio Platform v1.2",
  bio: "Développeur Full-Stack spécialisé en PHP, Laravel et Vue.js, avec une expérience sur des applications métier, les API REST, les tests automatisés et la CI/CD.",
  socials: [
    { label: "Email", href: "#contact", kind: "email" },
    { label: "WhatsApp", href: "#contact", kind: "whatsapp" },
    { label: "GitHub", href: "https://github.com/Alanarex", kind: "github" },
    { label: "GitLab", href: "#contact", kind: "gitlab" },
    { label: "LinkedIn", href: "#contact", kind: "linkedin" },
  ],
  languages: [
    { name: "Français", level: "Native" },
    { name: "English", level: "Fluent (C1)" },
    { name: "Arabic", level: "Conversational" },
  ],
};

export const stats = {
  projects: 24,
  posts: 412,
  experiences: 4,
  skills: 68,
};

export interface Skill {
  id: string;
  name: string;
  category: "backend" | "frontend" | "devops" | "database" | "tools" | "ai";
  level: number; // 0-100
  projects: number;
  posts: number;
  years: number;
}

export const skills: Skill[] = [
  { id: "laravel", name: "Laravel", category: "backend", level: 95, projects: 18, posts: 84, years: 7 },
  { id: "php", name: "PHP", category: "backend", level: 96, projects: 22, posts: 91, years: 9 },
  { id: "vue", name: "Vue.js", category: "frontend", level: 88, projects: 14, posts: 52, years: 6 },
  { id: "react", name: "React", category: "frontend", level: 82, projects: 9, posts: 37, years: 4 },
  { id: "tailwind", name: "Tailwind CSS", category: "frontend", level: 92, projects: 20, posts: 61, years: 5 },
  { id: "typescript", name: "TypeScript", category: "frontend", level: 85, projects: 11, posts: 44, years: 5 },
  { id: "docker", name: "Docker", category: "devops", level: 88, projects: 19, posts: 48, years: 6 },
  { id: "gh-actions", name: "GitHub Actions", category: "devops", level: 84, projects: 15, posts: 31, years: 4 },
  { id: "gitlab-ci", name: "GitLab CI", category: "devops", level: 82, projects: 12, posts: 26, years: 4 },
  { id: "redis", name: "Redis", category: "database", level: 86, projects: 14, posts: 22, years: 5 },
  { id: "mysql", name: "MySQL", category: "database", level: 90, projects: 21, posts: 39, years: 8 },
  { id: "postgres", name: "PostgreSQL", category: "database", level: 84, projects: 13, posts: 28, years: 5 },
  { id: "monitoring", name: "Monitoring", category: "devops", level: 80, projects: 10, posts: 18, years: 4 },
  { id: "testing", name: "Testing", category: "tools", level: 87, projects: 22, posts: 45, years: 6 },
  { id: "ai", name: "AI Integrations", category: "ai", level: 78, projects: 6, posts: 29, years: 2 },
  { id: "node", name: "Node.js", category: "backend", level: 80, projects: 8, posts: 24, years: 5 },
];

export interface Project {
  slug: string;
  name: string;
  tagline: string;
  category: string;
  status: "shipped" | "in-progress" | "archived";
  completedAt?: string;
  views: number;
  tech: string[];
  demoUrl?: string;
  githubUrl?: string;
  cover: string; // gradient key
  logo: string; // emoji
}

const gradients = [
  "linear-gradient(135deg,#2563ff,#7c3aed)",
  "linear-gradient(135deg,#0ea5e9,#22d3ee)",
  "linear-gradient(135deg,#f43f5e,#f59e0b)",
  "linear-gradient(135deg,#10b981,#22d3ee)",
  "linear-gradient(135deg,#8b5cf6,#ec4899)",
  "linear-gradient(135deg,#1e293b,#2563ff)",
];

export const projects: Project[] = [
  { slug: "portfolio-platform", name: "Portfolio Platform", tagline: "A living, social-media-style developer portfolio.", category: "SaaS", status: "in-progress", views: 12480, tech: ["laravel","vue","tailwind","redis","docker"], demoUrl: "#", githubUrl: "#", cover: gradients[0], logo: "🚀" },
  { slug: "sprint-radar", name: "Sprint Radar", tagline: "Team velocity & sprint analytics dashboard.", category: "SaaS", status: "shipped", completedAt: "2026-04", views: 8210, tech: ["laravel","vue","postgres","docker"], demoUrl: "#", githubUrl: "#", cover: gradients[1], logo: "📊" },
  { slug: "ledgerly", name: "Ledgerly", tagline: "Accounting API for indie SaaS.", category: "API", status: "shipped", completedAt: "2025-11", views: 15320, tech: ["laravel","mysql","redis","gh-actions"], demoUrl: "#", githubUrl: "#", cover: gradients[2], logo: "💳" },
  { slug: "flowmail", name: "Flowmail", tagline: "Transactional email with AI-authored drafts.", category: "AI", status: "in-progress", views: 5620, tech: ["laravel","ai","node","postgres"], demoUrl: "#", githubUrl: "#", cover: gradients[3], logo: "✉️" },
  { slug: "shopcart-x", name: "Shopcart X", tagline: "Headless e-commerce for boutique brands.", category: "E-commerce", status: "shipped", completedAt: "2025-08", views: 9840, tech: ["laravel","vue","mysql","docker","tailwind"], demoUrl: "#", githubUrl: "#", cover: gradients[4], logo: "🛍️" },
  { slug: "devops-lens", name: "DevOps Lens", tagline: "Unified CI/CD & incident dashboard.", category: "DevOps", status: "shipped", completedAt: "2025-05", views: 6410, tech: ["laravel","react","docker","monitoring"], demoUrl: "#", githubUrl: "#", cover: gradients[5], logo: "🔭" },
];

export interface Post {
  id: string;
  category: "projects" | "career" | "education" | "achievements" | "releases" | "github" | "articles" | "media";
  date: string;
  title: string;
  body: string;
  tech: string[];
  projectSlug?: string;
  reactions: number;
  comments: number;
  views: number;
  image?: string; // gradient
}

export const posts: Post[] = [
  { id: "p1", category: "releases", date: "2026-07-10", title: "Portfolio Platform v1.2 shipped 🎉", body: "The social feed is live. Every project, release, commit and certification now flows into one timeline. Built with Laravel + Vue, deployed on a home-cooked Docker swarm.", tech: ["laravel","vue","docker","redis"], projectSlug: "portfolio-platform", reactions: 214, comments: 32, views: 4820, image: gradients[0] },
  { id: "p2", category: "achievements", date: "2026-07-05", title: "Skills Explorer prototype", body: "Turned the tech stack page into an animated constellation. Hover a node to see recent activity, click to filter the feed.", tech: ["typescript","tailwind","react"], projectSlug: "portfolio-platform", reactions: 156, comments: 18, views: 3120 },
  { id: "p3", category: "github", date: "2026-06-30", title: "Merged: GitHub sync worker", body: "Nightly cron pulls repos, releases and contribution stats. Redis-backed queue keeps things snappy.", tech: ["laravel","redis","gh-actions"], reactions: 98, comments: 9, views: 2010 },
  { id: "p4", category: "projects", date: "2026-06-22", title: "Sprint Radar hits 8k monthly users", body: "Small teams love the velocity graphs. Next up: burn-up charts and Slack digests.", tech: ["laravel","vue","postgres"], projectSlug: "sprint-radar", reactions: 187, comments: 24, views: 3980, image: gradients[1] },
  { id: "p5", category: "articles", date: "2026-06-14", title: "Why I ditched microservices for a majestic monolith", body: "Two years, a dozen services, and one migration back. Here's what I'd do differently — and why boring architecture wins.", tech: ["laravel","docker"], reactions: 342, comments: 71, views: 8210 },
  { id: "p6", category: "career", date: "2026-05-28", title: "5 years of shipping SaaS solo", body: "Reflections on the tools, habits, and mistakes that shaped the last five years.", tech: [], reactions: 421, comments: 88, views: 11200, image: gradients[2] },
  { id: "p7", category: "releases", date: "2026-05-12", title: "Ledgerly 2.0 — multi-currency support", body: "Every SaaS eventually needs FX. Here's how we tackled precision, rounding and reconciliations.", tech: ["laravel","mysql","testing"], projectSlug: "ledgerly", reactions: 209, comments: 34, views: 5610 },
  { id: "p8", category: "media", date: "2026-04-30", title: "Talk at LaravelDay Paris", body: "45 minutes on turning side projects into revenue. Slides and video coming soon.", tech: [], reactions: 176, comments: 22, views: 4020, image: gradients[3] },
  { id: "p9", category: "github", date: "2026-04-18", title: "Open sourced: TinyQueue", body: "A tiny Redis-backed queue for Laravel. 200 lines, zero config, 100% test coverage.", tech: ["laravel","redis","testing"], reactions: 264, comments: 41, views: 6720 },
  { id: "p10", category: "education", date: "2026-03-22", title: "Finished Rust foundations course", body: "Ownership finally clicked. Building a tiny CLI to consolidate this.", tech: [], reactions: 132, comments: 15, views: 2410 },
];

export interface Experience {
  company: string;
  role: string;
  start: string;
  end: string;
  location: string;
  summary: string;
  tech: string[];
  achievements: string[];
}

export const experiences: Experience[] = [
  { company: "Independent", role: "Senior Full Stack Consultant", start: "2023", end: "Present", location: "Paris, FR", summary: "Shipping SaaS platforms, APIs and DevOps for growth-stage startups.", tech: ["laravel","vue","docker","postgres","redis"], achievements: ["Delivered 12+ production SaaS platforms","Reduced average client CI time by 62%","Mentored 6 junior engineers"] },
  { company: "Northwind Labs", role: "Lead Backend Engineer", start: "2020", end: "2023", location: "Paris, FR", summary: "Led a 5-person backend team on a B2B analytics platform serving 40k users.", tech: ["laravel","mysql","redis","gh-actions"], achievements: ["Scaled API to 8k RPS peak","Migrated from monolith to modular monolith","Championed testing culture — 92% coverage"] },
  { company: "Studio Kite", role: "Full Stack Developer", start: "2018", end: "2020", location: "Lyon, FR", summary: "Built bespoke web apps for design studios and boutique brands.", tech: ["laravel","vue","tailwind","mysql"], achievements: ["Shipped 20+ client projects","Introduced the studio's first CI pipeline"] },
  { company: "Freelance", role: "Web Developer", start: "2016", end: "2018", location: "Remote", summary: "First taste of solo product work — landing pages, CMS themes, and small tools.", tech: ["php","mysql"], achievements: ["Bootstrapped freelance career","First recurring revenue product"] },
];

export interface Education {
  school: string;
  diploma: string;
  start: string;
  end: string;
  location: string;
  highlights: string[];
}

export const education: Education[] = [
  { school: "EPITECH", diploma: "MSc — Software Engineering", start: "2013", end: "2018", location: "Paris, FR", highlights: ["Innovation Hub — 2 startups incubated","Erasmus year in Barcelona","Graduated top 10% of cohort"] },
  { school: "Lycée Louis-le-Grand", diploma: "Baccalauréat scientifique", start: "2010", end: "2013", location: "Paris, FR", highlights: ["Mention Très Bien","Math & CS specialization"] },
];

export interface Certification {
  name: string;
  issuer: string;
  date: string;
  credential: string;
  skills: string[];
}

export const certifications: Certification[] = [
  { name: "AWS Certified Solutions Architect — Associate", issuer: "Amazon Web Services", date: "2025-09", credential: "AWS-SAA-1234", skills: ["docker","monitoring"] },
  { name: "Certified Laravel Developer", issuer: "Laravel", date: "2024-03", credential: "LARA-4421", skills: ["laravel","php","testing"] },
  { name: "GitLab Certified CI/CD Specialist", issuer: "GitLab", date: "2024-01", credential: "GL-CICD-8812", skills: ["gitlab-ci","docker"] },
  { name: "Vue.js Advanced Patterns", issuer: "Vue School", date: "2023-06", credential: "VS-ADV-2201", skills: ["vue","typescript"] },
];

export interface MediaItem {
  id: string;
  kind: "photo" | "video" | "screenshot";
  title: string;
  tag: string;
  gradient: string;
}

export const media: MediaItem[] = Array.from({ length: 12 }).map((_, i) => ({
  id: `m${i}`,
  kind: (i % 3 === 0 ? "photo" : i % 3 === 1 ? "screenshot" : "video") as MediaItem["kind"],
  title: ["Conference talk","Workspace","Product screenshot","Team offsite","Speaker portrait","Office","Demo day","Whiteboard","Launch party","Sprint review","Hackathon","Podcast recording"][i],
  tag: ["events","office","product","team","events","office","product","team","events","product","events","media"][i],
  gradient: gradients[i % gradients.length],
}));

export interface Testimonial {
  quote: string;
  author: string;
  role: string;
}

export const testimonials: Testimonial[] = [
  { quote: "Alex ships. Fast, well-tested, and always thoughtful about the product.", author: "Marion L.", role: "CTO, Northwind Labs" },
  { quote: "Rare to find someone this comfortable across backend, ops and design.", author: "Théo B.", role: "Founder, Studio Kite" },
  { quote: "Our CI went from 22 minutes to 8. Our team went from anxious to shipping daily.", author: "Sara D.", role: "VP Eng, Ledgerly" },
];

export const services = [
  { icon: "🧱", title: "SaaS Platforms", desc: "End-to-end product builds, from schema to launch." },
  { icon: "🏢", title: "Business Applications", desc: "Internal tools that pay for themselves in a quarter." },
  { icon: "📊", title: "Dashboards & Analytics", desc: "Real-time metrics your team actually opens." },
  { icon: "🔌", title: "APIs & Integrations", desc: "REST, GraphQL, webhooks — properly versioned." },
  { icon: "🛒", title: "E-commerce", desc: "Headless storefronts and custom checkouts." },
  { icon: "🤖", title: "AI Integrations", desc: "LLMs woven into your product, not bolted on." },
  { icon: "⚙️", title: "DevOps & Infra", desc: "CI/CD, monitoring, cost tuning, incident response." },
  { icon: "🧭", title: "Technical Consulting", desc: "Architecture reviews and pragmatic roadmaps." },
];

export const latestActivity = [
  "Released Portfolio Platform v1.2",
  "Completed Skills Explorer",
  "Added GitHub synchronization",
  "Improved application performance by 34%",
  "Started AI-powered content generation",
];

export const version = "1.2.0";
export const deployedAt = "2026-07-10";
