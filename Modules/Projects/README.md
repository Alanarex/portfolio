# Projects module

Owns portfolio projects, localized case studies and private media metadata. Public page composition
and file delivery remain assigned to PORT-008 and PORT-009.

## Publication and privacy contract

- A project slug is unique and immutable after creation.
- Publication requires verified lifecycle data, at least one technology, and complete French and
  English title, summary and role copy.
- Public case-study sections are explicitly public, verified and complete in both locales.
- Repository URLs are encrypted at rest. Only URLs marked `public` and explicitly enabled are
  included in `PublicProjectReader`; private repository metadata never leaves the administrator
  boundary.
- Draft and archived projects are absent from every public reader.

## Media contract

- Files are stored through `MediaStorage` on the private `local` disk by default; the application
  rejects the web-linked `public` disk.
- Stored names are generated UUIDs; client filenames and storage paths are encrypted at rest.
- Images allow JPEG, PNG and WebP up to 8 MiB. Documents and CV assets allow PDF up to 20 MiB.
- MIME content, client extension, size, full GD image decoding and bounded pixel dimensions are
  checked. Image dimensions are limited to 6,000 pixels per side and 10 million pixels total.
  PHP `fileinfo` and `gd` are required Composer platform dependencies.
- Public media require French and English alternative text. Public DTOs expose a delivery key and
  allowlisted presentation metadata, never disk, path, checksum or original filename.
- Deletions first enter a non-public pending state, then remove private files, and only then remove
  metadata. A storage failure therefore remains visible to administrators and can be retried without
  exposing the pending project or media through public readers.

## Initial content

`ProjectsDatabaseSeeder` imports only `docs/content/projects.md` facts. Records remain drafts,
unknown lifecycle values remain `unspecified`, private/demo URLs are absent and missing English
copy is not invented. The idempotent seeder does not overwrite administrator edits.
