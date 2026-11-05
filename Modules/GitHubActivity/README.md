# GitHubActivity

Fetches the public repositories and recent public events of `services.github.username`
(env `GITHUB_USERNAME`, default `Alanarex`) and stores a normalized snapshot in `github_activity_snapshots`.

- `php artisan portfolio:sync-github`: run by the scheduler every hour (`routes/console.php`).
- Optional `GITHUB_TOKEN` (fine-grained, no scope needed for public data) raises the API rate limit.
- Only public, non-fork repositories and a few event types (push, pull request opened or merged, release,
  repository created) are kept. A failed sync keeps the previous snapshot.
- The home page shows it in the "activity" section when the `activity` feature flag is on.
