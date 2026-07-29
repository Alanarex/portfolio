# Profile module

Owns the single professional identity, localized profile copy, visibility controls and CV
versions. Public consumers depend on `PublicProfileReader`, which returns an immutable,
allowlisted projection for published French or English content only.

CV PDFs stay on the dedicated non-public `cv` filesystem disk and are served only through the localized controller
after rechecking the profile, CV feature flag, locale, verified/published state, archive state,
file existence and SHA-256 checksum. Publishing a version unpublishes the previous version for
that locale. Internal disk paths are never returned by public readers.
