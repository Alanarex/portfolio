# ADR-007: Autonomous Continuous PORT Delivery

## Status

Accepted

## Context

The initial PORT-002 to PORT-011 sequence was designed as a manually restarted workflow with several product-owner gates. The product owner now prefers one continuous Codex orchestration that selects, implements, validates, publishes, merges, closes and advances through every remaining PORT issue without requiring a new prompt between features.

## Decision

Run PORT-003 through PORT-011 as one continuous autonomous delivery chain.

The orchestrator must still use one issue, one branch and one pull request per feature. It must update `main` after each merge, clean completed worktrees, then immediately select and start the next open issue whose dependency is complete.

Autonomous merge is permitted when:

- every acceptance criterion is satisfied;
- all local and GitHub Actions checks pass;
- no unresolved QA/review finding remains;
- migrations and rollback are verified where applicable;
- no secret, private source code or unrelated change is present;
- the branch is current with `main`;
- branch protection is respected.

The orchestrator must not pause for ordinary design or privacy choices. It uses these conservative defaults unless a later issue explicitly overrides them:

### Visual defaults

- dark-first professional interface;
- neutral navy/slate surfaces with one restrained warm accent;
- accessible system-first typography, with a bundled/openly licensed font only when justified;
- WCAG-oriented contrast, visible focus, reduced motion and responsive layouts;
- generate representative screenshots and let QA select the strongest compliant direction.

### Privacy defaults

- exact address and phone hidden;
- public contact through the contact form;
- public email shown only when explicitly configured as public;
- no analytics vendor by default;
- no private GitHub/GitLab activity exposed;
- CV download enabled only when a verified published file exists;
- no persistence of contact messages unless required; if persisted, use minimal retention and deletion rules.

### 3D and Blender defaults

- never restore archived GLB files;
- prefer original procedural low-poly assets generated through code or Blender scripting;
- use only self-created or clearly licensed third-party assets with provenance records;
- if personal likeness references are unavailable, complete v1 with a generic stylized developer avatar and preserve the animation/node contract;
- personalized likeness becomes an optional later asset revision, not a blocker for PORT-011 completion;
- no audio, heavy post-processing, physics, dynamic hair or cloth.

## Hard stop conditions

Continuous execution stops only for a blocker that cannot be safely resolved autonomously:

- failing tests or CI after reasonable diagnosis and repair attempts;
- unresolved security, privacy, licensing or data-loss risk;
- destructive migration without verified rollback;
- production credentials or production deployment approval;
- branch protection or GitHub permissions preventing publication/merge;
- an accepted ADR conflict requiring a product-level reversal;
- unavailable external asset/tooling that cannot be replaced by an allowed procedural or licensed fallback.

## Consequences

- Codex may run for a long sequence and consume more tokens and compute;
- each feature remains independently reviewable and reversible;
- visual and privacy decisions use documented conservative defaults rather than waiting for manual approval;
- final v1 3D can ship with a generic stylized avatar when personalized references are absent;
- the chain ends after PORT-011, followed by a separately tracked release-readiness issue.
