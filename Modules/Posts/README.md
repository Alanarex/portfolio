# Posts

Short updates ("posts") shown in the "Publications" section of the public home page.

- Admin: `/dashboard/posts` (auth + admin middleware, no public link). Create, edit, publish or delete.
- Public: `PublicPostsReader::latest($locale)` returns the published posts for one locale, newest first.
- Bodies are plain text. Blade escapes them and keeps line breaks; no HTML or Markdown is rendered.
