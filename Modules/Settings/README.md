# Settings module

Owns the site identity, supported locale records, contact visibility, social links and public
feature flags. French and English are active; Arabic is stored as a non-public readiness locale.
French remains the fixed default for V3.

Contact values and social-link URLs are encrypted at rest and private by default. Public consumers
receive only an immutable allowlisted projection through `PublicSettingsReader`; mutations clear
every locale cache synchronously.
