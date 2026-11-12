# ADR-008 — Sentry pour le suivi des erreurs

## Statut

Acceptée.

## Contexte

Le site est en production sur un VPS unique. Sans outil dédié, une erreur PHP n'est visible que dans
les journaux Docker. ADR-007 interdit tout fournisseur d'analytics par défaut ; le suivi d'erreurs
n'observe pas les visiteurs mais envoie des données à un service tiers.

## Décision

- Ajouter `sentry/sentry-laravel` (SaaS, offre gratuite), activé uniquement si `SENTRY_LARAVEL_DSN` est défini.
- `send_default_pii` reste à `false`, le traçage et les profils sont désactivés.
- `App\Core\Observability\ScrubSentryEvent` retire avant envoi le corps des requêtes, les paramètres
  d'URL, cookies, en-têtes, l'utilisateur, les breadcrumbs, les données extra et le nom du serveur.
- Sentry n'est pas un outil d'analytics : le choix d'un outil d'analytics reste séparé.

## Conséquences

- Une nouvelle dépendance (5 paquets, dont `sentry/sentry`) à maintenir à jour.
- Les erreurs sont envoyées à un tiers : seuls l'exception, sa pile d'appels, la méthode et le chemin d'URL.
- Le DSN est un réglage de serveur (`.env`), jamais commité.
