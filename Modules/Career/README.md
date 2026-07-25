# Career module

Owns professional experiences, verified experience achievements, education, certifications and
spoken-language proficiency. UI locales remain owned by `Settings`; project and feed relations
remain out of scope.

## Publication contract

- Root records use `draft`, `published` or `archived` status and deterministic `sort_order`.
- Publication requires complete French and English display copy.
- Public consumers depend on `PublicCareerReader`, which returns an immutable allowlisted
  projection and never falls back from English to French.
- Quantified achievements and certifications must be verified before they can be exposed.
- Source references, verification state, database IDs and audit metadata are always private.

## Initial content

`CareerDatabaseSeeder` imports only facts explicitly documented in `docs/content/experience.md`,
`education.md`, `certifications.md` and `languages.md`. Records are created as drafts because
verified English translations are not supplied. The idempotent seeder uses stable keys and does
not overwrite later administrator edits.

Month/year and year-only precision are stored separately; no artificial first-of-month dates are
created. Unknown TOEIC issuer, credential ID and verification URL values remain null.
