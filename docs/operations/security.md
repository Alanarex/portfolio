# Sécurité : ce que fait le code, ce qui relève du réseau

## Dans l'application (Laravel)

| Sujet | Mécanisme |
| --- | --- |
| Verrouillage de connexion | 3 échecs pour un couple identifiant + IP → connexion refusée 1 min, puis 5, 15, 60 min aux verrouillages suivants (mémoire 24 h). Pendant le verrouillage le mot de passe n'est pas vérifié. Un compteur par IP (10 échecs / 15 min) freine le balayage d'identifiants. Message identique pour un compte inexistant. Chaque échec / verrouillage est écrit dans le journal de connexion (colonne `reason`) et dans le journal d'audit. |
| Limitation de débit | `admin-login` 5/min ; `password-reset-request` 3/min + 5/h par IP et 3/h par adresse ; `password-reset` 5/min + 20/h ; `public-contact` 3/min + 10/h ; `web-global` 240/min par IP sur toutes les routes web ; changement de mot de passe du compte limité par utilisateur. |
| Mot de passe oublié | Jeton aléatoire stocké **haché** (`password_reset_tokens`), à usage unique, expire après `AUTH_PASSWORD_RESET_EXPIRE` minutes (30). Le lien de l'e-mail est de plus **signé** (HMAC, expirant). Réponse identique que l'adresse existe ou non ; seul un compte administrateur reçoit un e-mail ; envoi en file (temps de réponse indépendant). Après réinitialisation : `auth_version` incrémenté, sessions supprimées, politique de mot de passe de 12 caractères. |
| En-têtes | `SecurityHeaders` : CSP à nonce par requête (pas d'`unsafe-inline` ni d'`unsafe-eval` côté scripts ; `wasm-unsafe-eval` pour le décodeur Meshopt du modèle 3D ; styles inline permis), `frame-ancestors 'none'`, `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`, COOP/CORP, HSTS (HTTPS seulement), `Cache-Control: no-store` sur connexion / réinitialisation / dashboard. |
| Cookies et sessions | `HttpOnly`, `SameSite=Lax`, `Secure` par défaut en production, session régénérée à la connexion, invalidée à la déconnexion, CSRF sur toutes les routes `web`. |
| XSS | Blade échappe par défaut ; les rares `{!! !!}` n'affichent que du HTML construit côté serveur à partir de texte échappé ou du JSON encodé avec `JSON_HEX_*`. |
| Timing | Comparaison de hachages par Laravel ; un compte inconnu déclenche un hachage factice pour égaliser le temps de réponse. |

## Caddy / Nginx (production)

Caddy : délais serveur (`read_header 10s`, `read_body 30s`, `write 90s`, `idle 2m`), corps de requête limité à 20 Mo, délais vers l'amont, socle d'en-têtes. Nginx : corps limité à 16 Mo, délais d'en-têtes / corps, en-têtes dédoublonnés avec ceux de Laravel (`fastcgi_hide_header`). Valider avant déploiement : `docker run --rm -v ./docker/caddy/Caddyfile:/etc/caddy/Caddyfile:ro -e APP_DOMAIN=example.test -e ACME_EMAIL=a@example.test caddy:2.10-alpine caddy validate --config /etc/caddy/Caddyfile`.

## Hors du code (à faire côté réseau)

- **DDoS volumétrique (couches 3/4) et applicatif massif** : aucune protection possible dans PHP. Placer le domaine derrière Cloudflare (proxy orange, mode SSL « Full (strict) »), activer la protection DDoS, *Bot Fight Mode* et une règle de limitation sur `/login`, `/forgot-password`, `/reset-password`, `/fr/contact`, `/en/contact`.
- **Verrouiller l'origine** : le pare-feu du VPS n'accepte 80/443 que depuis les plages Cloudflare (ou *Authenticated Origin Pulls*). Sinon un attaquant contourne Cloudflare en visitant l'IP directement.
- **IP réelle du client** : derrière Cloudflare, Caddy voit l'IP de Cloudflare. Pour que les compteurs (verrouillage, throttle) restent par visiteur, configurer Caddy `trusted_proxies` avec les plages Cloudflare et lire `CF-Connecting-IP` ; sans cela tous les visiteurs partagent une même IP côté Laravel. À faire avec l'exploitant du serveur, non modifié ici.
- **Limitation par IP côté serveur web** : Caddy n'a pas de limiteur de débit sans module tiers ; les limites fines restent dans Laravel (cache Redis).
- **SSH / pare-feu / fail2ban**, sauvegardes, mises à jour système : voir `deployment.md`.
- **Préchargement HSTS** (`preload`) : volontairement non activé (irréversible) ; à décider après quelques mois de HTTPS stable.
- **SPF / DKIM / DMARC** du domaine d'envoi : nécessaires pour que l'e-mail de réinitialisation arrive.

## Variables d'environnement (noms seulement)

| Variable | Rôle | Défaut |
| --- | --- | --- |
| `APP_URL` | Origine HTTPS exacte du site : sert à construire le lien de réinitialisation en production (évite l'empoisonnement par en-tête `Host`) | — (obligatoire) |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | Envoi réel de l'e-mail de réinitialisation (pas `log`) | `log` |
| `AUTH_PASSWORD_RESET_EXPIRE` | Validité du lien, en minutes | `30` |
| `SECURITY_CSP_REPORT_ONLY` | `true` : la CSP est seulement rapportée (rollback sans redéploiement de code) | `false` |
| `SECURITY_HSTS` | Émission de HSTS par l'application | `true` |
| `SESSION_SECURE_COOKIE` | Déjà `true` dans `compose.prod.yaml` | `true` en production |

## Risques résiduels

- `style-src 'unsafe-inline'` (attributs `style` des vues) : n'exécute pas de code mais permet l'injection de style.
- Le verrouillage est par couple identifiant + IP : un attaquant qui change d'IP repart de zéro (compensé par le compteur par IP et les limites Cloudflare). Inversement, un attaquant peut temporairement verrouiller l'administrateur *depuis sa propre IP* seulement.
- Un seul administrateur : s'il perd l'accès à sa boîte e-mail, la commande `admin:provision` reste le recours.
- Le temps de réponse de la demande de réinitialisation est quasi (pas strictement) constant.
- La file `mail` (messages de contact) n'est consommée que si le worker écoute cette file : voir la remarque dans la PR.
