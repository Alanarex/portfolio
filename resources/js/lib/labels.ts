// Libellés d'affichage en français des valeurs techniques renvoyées par le serveur.
// Seul l'affichage change : les valeurs envoyées et les contrats Inertia restent inchangés.

const publication: Record<string, string> = { draft: 'Brouillon', published: 'Publié', archived: 'Archivé' };
const lifecycle: Record<string, string> = {
  unspecified: 'Non précisé',
  planned: 'Planifié',
  in_progress: 'En cours',
  completed: 'Terminé',
  maintained: 'Maintenu',
};
const visibility: Record<string, string> = { none: 'Aucun dépôt', public: 'Public', private: 'Privé' };
const mediaKind: Record<string, string> = {
  image: 'Image',
  screenshot: 'Capture d’écran',
  poster: 'Affiche',
  document: 'Document',
  cv: 'CV',
};
const localeStatus: Record<string, string> = { active: 'Active', readiness: 'En préparation' };

function label(map: Record<string, string>, value: string): string {
  return map[value] ?? value;
}

export const publicationLabel = (value: string): string => label(publication, value);
export const lifecycleLabel = (value: string): string => label(lifecycle, value);
export const visibilityLabel = (value: string): string => label(visibility, value);
export const mediaKindLabel = (value: string): string => label(mediaKind, value);
export const localeStatusLabel = (value: string): string => label(localeStatus, value);
