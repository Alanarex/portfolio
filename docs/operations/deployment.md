# Déploiement en production (VPS + Docker)

Ce guide met le portfolio en ligne sur un VPS Ubuntu LTS, avec HTTPS automatique, puis active le
déploiement automatique depuis GitHub Actions.

## Architecture

```text
Internet ──443──► caddy (HTTPS Let's Encrypt)
                    └─► web (Nginx : fichiers statiques + en-têtes de sécurité)
                          └─► app (PHP-FPM 8.5, Laravel)
                                ├─► postgres (PostgreSQL 17, réseau privé)
                                └─► redis (cache, sessions, file d'attente, mot de passe)
queue      : php artisan queue:work   (envoi des messages de contact…)
scheduler  : php artisan schedule:work (tâches planifiées)
```

Seuls les ports 80 et 443 de Caddy sont ouverts. La base et Redis ne sont jamais exposés.

- `/up` : réponse simple de Laravel (le processus PHP tourne).
- `/health` : JSON avec l'état de la base, du cache, de Redis et du stockage. Code 200 si tout va
  bien, 503 sinon. Aucun détail technique (hôte, message d'erreur) n'est renvoyé.
- `php artisan portfolio:health` : la même vérification en ligne de commande (healthcheck Docker).

## 1. Préparer le serveur (une seule fois)

Sur ton PC :

```bash
ssh-keygen -t ed25519 -C "alaa-perso" -f ~/.ssh/portfolio_admin     # ta clé à toi
```

Ajoute la clé publique `~/.ssh/portfolio_admin.pub` au moment de commander le VPS (Ubuntu LTS).
Puis, sur le serveur (`ssh -i ~/.ssh/portfolio_admin root@IP`) :

```bash
apt update && apt upgrade -y
adduser --disabled-password --gecos "" deploy
usermod -aG sudo deploy
mkdir -p /home/deploy/.ssh && cp ~/.ssh/authorized_keys /home/deploy/.ssh/
chown -R deploy:deploy /home/deploy/.ssh && chmod 700 /home/deploy/.ssh

# Pare-feu : SSH + web uniquement
apt install -y ufw unattended-upgrades
ufw allow OpenSSH && ufw allow 80/tcp && ufw allow 443/tcp && ufw allow 443/udp && ufw --force enable

# SSH : pas de mot de passe, pas de root
sed -i 's/^#\?PasswordAuthentication .*/PasswordAuthentication no/; s/^#\?PermitRootLogin .*/PermitRootLogin no/' /etc/ssh/sshd_config
systemctl restart ssh

# Docker (dépôt officiel)
curl -fsSL https://get.docker.com | sh
usermod -aG docker deploy
```

## 2. Nom de domaine

Chez ton registrar, crée un enregistrement **A** `ton-domaine.fr → IP du VPS` (et `www` → même IP).
Attends que `ping ton-domaine.fr` réponde avec la bonne IP avant l'étape 4.

## 3. Installer l'application

En tant que `deploy` :

```bash
sudo mkdir -p /opt/portfolio && sudo chown deploy:deploy /opt/portfolio
git clone https://github.com/Alanarex/portfolio.git /opt/portfolio
cd /opt/portfolio
git checkout release/v3.0.0/main
cp .env.production.example .env
chmod 600 .env
```

Remplis `.env` : `APP_URL`, `APP_DOMAIN`, `ACME_EMAIL`, `DB_PASSWORD`, `REDIS_PASSWORD`
(`openssl rand -base64 32` pour chaque mot de passe), et le SMTP pour le formulaire de contact.
`GITHUB_USERNAME` règle le compte affiché dans « Activité GitHub » (synchronisé chaque heure par le
scheduler). `GITHUB_TOKEN` est facultatif : un token fine-grained sans droit particulier augmente juste
la limite d'appels à l'API.

```bash
docker compose -f compose.prod.yaml build
docker compose -f compose.prod.yaml run --rm --no-deps app php artisan key:generate --show
# colle la valeur affichée dans APP_KEY du .env
./scripts/deploy.sh
docker compose -f compose.prod.yaml exec app php artisan admin:provision
```

Le site répond sur `https://ton-domaine.fr` (le certificat HTTPS arrive en quelques secondes).
Vérifie : `curl https://ton-domaine.fr/health`.

## 4. Sauvegardes de la base (tous les jours)

```bash
mkdir -p /opt/portfolio-backups
crontab -e
# Ajouter la ligne :
15 3 * * * /opt/portfolio-backups/backup-db.sh   # sauvegarde chaque nuit, garde les 3 plus récentes
```

Le script `/opt/portfolio-backups/backup-db.sh` (`chmod +x`) :

```bash
#!/usr/bin/env bash
set -euo pipefail
cd /opt/portfolio
out="/opt/portfolio-backups/db-$(date +%F).sql.gz"
docker compose -f compose.prod.yaml exec -T postgres sh -c 'pg_dump -U "$POSTGRES_USER" "$POSTGRES_DB"' | gzip > "$out"
# garde les 3 plus récentes
ls -1t /opt/portfolio-backups/db-*.sql.gz | tail -n +4 | xargs -r rm --
```

Télécharge une sauvegarde de temps en temps sur ton PC (`scp`) : une sauvegarde qui reste sur le
même serveur ne protège pas d'une panne du serveur.

## 5. Déploiement automatique depuis GitHub

À chaque push sur `release/v3.0.0/main`, une fois la CI **Quality** verte, le workflow
**Deploy** se connecte en SSH, récupère le commit, construit les images, lance les migrations,
redémarre et vérifie `/health`. Si `/health` échoue, la version précédente est remise en place
(les migrations, elles, ne sont pas annulées).

1. Sur ton PC, crée une clé **réservée au déploiement** (sans phrase de passe) :
   ```bash
   ssh-keygen -t ed25519 -C "github-deploy" -f ~/.ssh/portfolio_deploy -N ""
   ```
2. Sur le serveur, ajoute `~/.ssh/portfolio_deploy.pub` à `/home/deploy/.ssh/authorized_keys`.
3. Récupère l'empreinte du serveur : `ssh-keyscan -p 22 IP_DU_VPS` (vérifie-la avec celle
   affichée par ton hébergeur).
4. Dans GitHub : *Settings → Environments* → crée l'environnement `production`, puis
   *Settings → Secrets and variables → Actions* → ajoute :

   | Secret | Valeur |
   |---|---|
   | `DEPLOY_HOST` | IP ou domaine du VPS |
   | `DEPLOY_PORT` | `22` |
   | `DEPLOY_USER` | `deploy` |
   | `DEPLOY_PATH` | `/opt/portfolio` |
   | `DEPLOY_SSH_KEY` | contenu de `~/.ssh/portfolio_deploy` (clé **privée**) |
   | `DEPLOY_KNOWN_HOSTS` | sortie de `ssh-keyscan` |

Tant que `DEPLOY_HOST` est vide, le workflow s'arrête sans erreur. Ne colle jamais ces valeurs
dans un chat, un ticket ou un commit. Tu peux aussi lancer un déploiement à la main :
*Actions → Deploy → Run workflow*.

## 6. Suivi des erreurs (Sentry, optionnel)

Sentry reçoit les erreurs PHP (exception, pile d'appels, méthode et chemin de l'URL) pour te
prévenir par e-mail. Sans DSN, rien n'est envoyé.

1. Sur sentry.io, crée un projet **Laravel** (offre gratuite) et copie son **DSN**.
2. Sur le serveur, dans `/opt/portfolio/.env`, renseigne `SENTRY_LARAVEL_DSN=` (tape-le toi-même,
   ne le colle pas dans un chat ou un ticket).
3. Recharge les conteneurs : `docker compose -f compose.prod.yaml up -d --force-recreate app queue scheduler`
   puis `docker compose -f compose.prod.yaml restart web`.

Les rapports n'incluent jamais le corps des requêtes (formulaire de contact), les paramètres d'URL,
les cookies, les en-têtes, l'identité du visiteur ni les journaux (`app/Core/Observability/ScrubSentryEvent.php`).
Le traçage de performance et les profils sont désactivés.

## 7. Administration de la base (Adminer, à la demande)

Adminer est l'équivalent de phpMyAdmin pour PostgreSQL (une seule page, ~30 Mo de RAM). Il tourne sur `https://db.ton-domaine.fr`,
**uniquement quand tu le démarres**, derrière deux protections indépendantes :

1. **Caddy** : mot de passe HTTP (basic auth) avant même d'atteindre Adminer. Tant que
   `PGADMIN_AUTH_HASH` n'est pas renseigné, toute requête reçoit un 401.
2. **Adminer** : connexion PostgreSQL (utilisateur et mot de passe de la base, pas de compte à part).

PostgreSQL n'est jamais exposé : Adminer s'y connecte par le réseau privé Docker. La page envoie
`noindex` et n'est pas dans le sitemap.

**Préparation (une seule fois)**

1. Chez ton registrar : enregistrement DNS **A** `db` → IP du VPS. Attends qu'il réponde
   (`dig +short A db.ton-domaine.fr`) avant la suite, sinon Let's Encrypt échoue.
2. Génère le hash du mot de passe Caddy, sur le serveur (tu le tapes deux fois, rien ne s'affiche) :
   ```bash
   docker run --rm -it caddy:2.10-alpine caddy hash-password
   ```
3. Dans `/opt/portfolio/.env` renseigne (mot de passe long) :
   ```text
   PGADMIN_AUTH_USER=un-nom-qui-n-est-pas-admin
   PGADMIN_AUTH_HASH='le-hash-affiche'      # entre APOSTROPHES : il contient des $
   ```
   Ne colle jamais ces valeurs dans un chat, un ticket ou un commit.
4. **Vérifie** la configuration, puis recharge Caddy pour qu'il lise le nouveau bloc et les
   variables. Si `PGADMIN_AUTH_USER` est renseigné sans `PGADMIN_AUTH_HASH` (ou l'inverse), Caddy
   refuse de démarrer et **tout le site tombe** : la première commande te prévient avant :
   ```bash
   cd /opt/portfolio
   docker compose -f compose.prod.yaml run --rm --no-deps caddy caddy validate --config /etc/caddy/Caddyfile
   # doit afficher « Valid configuration », sinon corrige le .env AVANT la suite
   docker compose -f compose.prod.yaml up -d --force-recreate caddy
   ```

**Utilisation**

```bash
docker compose -f compose.prod.yaml --profile admin up -d adminer   # démarrer
docker compose -f compose.prod.yaml stop adminer                    # arrêter après usage
```

Ouvre `https://db.ton-domaine.fr`, passe le mot de passe Caddy, puis connecte-toi à Adminer :
système *PostgreSQL*, serveur `postgres` (déjà rempli), utilisateur et base = `DB_USERNAME` / `DB_DATABASE`
du `.env`, mot de passe = `DB_PASSWORD`.

**Après toute modification du Caddyfile** (déployée par GitHub), ne fais pas `caddy reload` : le
fichier est monté seul dans le conteneur et Git le remplace à chaque déploiement, donc le
conteneur garde l'ancienne version et un rechargement relit l'ancienne configuration (symptôme :
502 ou ancien comportement). Valide dans un conteneur neuf, puis recrée Caddy :

```bash
docker compose -f compose.prod.yaml run --rm --no-deps caddy caddy validate --config /etc/caddy/Caddyfile
docker compose -f compose.prod.yaml up -d --force-recreate caddy
```

**Pour durcir encore** : si ton IP est fixe, ajoute dans le bloc `db.{$APP_DOMAIN}` du Caddyfile
`@interdit not remote_ip TON.IP.ICI` suivi de `respond @interdit 403`, avant `basic_auth`.

## Commandes utiles

```bash
cd /opt/portfolio
docker compose -f compose.prod.yaml ps                 # état des services
docker compose -f compose.prod.yaml logs -f app        # journaux
docker compose -f compose.prod.yaml exec app php artisan portfolio:health
```

## Développement local

```bash
cp .env.example .env
docker compose up -d
docker compose exec app composer install
docker compose exec app npm ci && docker compose exec app npm run build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan admin:provision
```

Site : http://localhost:8080 · Mailpit : http://localhost:8025.
