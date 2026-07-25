# Skills module

Owns the ordered, localized skills taxonomy. Categories and skills have independent publication
and visibility controls. Project/event associations are intentionally deferred to their owning
modules.

Public consumers depend on `PublicSkillsReader`. A skill is exposed only when both it and its
category are published, visible and translated for the requested French or English locale.
Database IDs, editorial state and audit metadata are not part of the public DTO.

`SkillsDatabaseSeeder` imports the seven categories and 59 skill labels documented in
`docs/content/skills.md`. It creates draft, hidden records, does not invent proficiency scores,
icons, experience durations or English translations, and does not overwrite administrator edits.
