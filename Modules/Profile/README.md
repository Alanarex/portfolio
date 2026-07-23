# Profile module

Owns the single professional identity, localized profile copy, visibility controls and CV
version metadata. Public consumers depend on `PublicProfileReader`, which returns an immutable,
allowlisted projection for published French or English content only.

CV records intentionally contain metadata only. PORT-005 exposes no upload or download route and
the public reader never returns CV metadata. Verified file delivery remains part of PORT-009.
