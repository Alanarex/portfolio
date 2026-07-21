import { usePreferences, type Language } from "@/hooks/use-preferences";

type Dict = Record<string, { fr: string; en: string }>;

export const dict = {
  "nav.feed": { fr: "Fil", en: "Feed" },
  "nav.highlights": { fr: "À la une", en: "Highlights" },
  "nav.projects": { fr: "Projets", en: "Projects" },
  "nav.experience": { fr: "Expérience", en: "Experience" },
  "nav.education": { fr: "Formation", en: "Education" },
  "nav.skills": { fr: "Compétences", en: "Skills" },
  "nav.certifications": { fr: "Certifications", en: "Certifications" },
  "nav.media": { fr: "Médias", en: "Media" },
  "nav.activity": { fr: "Activité", en: "Activity" },
  "nav.analytics": { fr: "Analytique", en: "Analytics" },
  "nav.contact": { fr: "Contact", en: "Contact" },
  "nav.profile": { fr: "Profil", en: "Profile" },

  "welcome.title": { fr: "Bienvenue.", en: "Welcome." },
  "welcome.subtitle": { fr: "Pourquoi êtes-vous ici aujourd'hui ?", en: "Why are you here today?" },
  "welcome.recruiter": { fr: "Recruteur / RH", en: "Recruiter / HR" },
  "welcome.recruiter.desc": { fr: "Consulter mon parcours et mon CV", en: "Review my background and CV" },
  "welcome.client": { fr: "Client potentiel", en: "Potential Client" },
  "welcome.client.desc": { fr: "Découvrir mes services", en: "Discover my services" },
  "welcome.technical": { fr: "Développeur / Tech", en: "Developer / Technical" },
  "welcome.technical.desc": { fr: "Explorer le code, les stacks et les métriques", en: "Explore code, stacks and metrics" },
  "welcome.skip": { fr: "Continuer sans personnalisation", en: "Continue without personalization" },
  "welcome.language": { fr: "Langue", en: "Language" },
  "welcome.theme": { fr: "Thème", en: "Theme" },
  "welcome.dark": { fr: "Sombre", en: "Dark" },
  "welcome.light": { fr: "Clair", en: "Light" },
  "welcome.enter": { fr: "Entrer", en: "Enter" },

  "slogan": { fr: "Building products, not just websites.", en: "Building products, not just websites." },

  "stats.projects": { fr: "Projets", en: "Projects" },
  "stats.posts": { fr: "Publications", en: "Posts" },
  "stats.experiences": { fr: "Expériences", en: "Experiences" },
  "stats.skills": { fr: "Compétences", en: "Skills" },

  "feed.filter.all": { fr: "Tout", en: "All" },
  "feed.filter.projects": { fr: "Projets", en: "Projects" },
  "feed.filter.career": { fr: "Carrière", en: "Career" },
  "feed.filter.education": { fr: "Formation", en: "Education" },
  "feed.filter.achievements": { fr: "Succès", en: "Achievements" },
  "feed.filter.releases": { fr: "Releases", en: "Releases" },
  "feed.filter.github": { fr: "GitHub", en: "GitHub" },
  "feed.filter.articles": { fr: "Articles", en: "Articles" },
  "feed.filter.media": { fr: "Médias", en: "Media" },
  "feed.search": { fr: "Rechercher projets, technos, entreprises…", en: "Search projects, tech, companies…" },

  "profile.hire": { fr: "Recruter", en: "Hire Me" },
  "profile.start": { fr: "Démarrer un projet", en: "Start Project" },
  "profile.cv": { fr: "Télécharger le CV", en: "Download CV" },
  "profile.meeting": { fr: "Réserver un appel", en: "Book Meeting" },

  "sidebar.availability": { fr: "Disponible pour freelance et CDI", en: "Available for freelance and full-time" },
  "sidebar.currentPosition": { fr: "Poste actuel", en: "Current Position" },
  "sidebar.currentProject": { fr: "Projet en cours", en: "Current Project" },
  "sidebar.currentSprint": { fr: "Sprint en cours", en: "Current Sprint" },
  "sidebar.latestAchievement": { fr: "Dernier succès", en: "Latest Achievement" },
  "sidebar.topProjects": { fr: "Projets phares", en: "Top Projects" },
  "sidebar.services": { fr: "Services", en: "Services" },
  "sidebar.stack": { fr: "Stack actuelle", en: "Current Stack" },
  "sidebar.repos": { fr: "Dépôts", en: "Repositories" },
  "sidebar.languages": { fr: "Langues", en: "Languages" },
  "sidebar.education": { fr: "Formation", en: "Education" },
  "sidebar.testimonials": { fr: "Témoignages", en: "Testimonials" },
  "sidebar.deployments": { fr: "Déploiements récents", en: "Latest Deployments" },
  "sidebar.monitoring": { fr: "Monitoring", en: "Monitoring" },
  "sidebar.allGreen": { fr: "Tous services opérationnels", en: "All systems operational" },

  "footer.rights": { fr: "Tous droits réservés.", en: "All rights reserved." },
  "footer.version": { fr: "Version", en: "Version" },
  "footer.deployed": { fr: "Déployé", en: "Deployed" },

  "cta.viewAll": { fr: "Voir tout", en: "View all" },
  "cta.readMore": { fr: "Lire la suite", en: "Read more" },

  "post.views": { fr: "vues", en: "views" },
  "post.comments": { fr: "commentaires", en: "comments" },

  "search.placeholder": { fr: "Rechercher partout… (Ctrl+K)", en: "Search everything… (Ctrl+K)" },
  "search.empty": { fr: "Aucun résultat", en: "No results" },
} satisfies Dict;

export type TranslationKey = keyof typeof dict;

export function translate(key: TranslationKey, lang: Language): string {
  return dict[key][lang] ?? key;
}

export function useT() {
  const { prefs } = usePreferences();
  return (key: TranslationKey) => translate(key, prefs.language);
}
