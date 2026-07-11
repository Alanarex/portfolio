# PORT-004 visual and accessibility QA

## Selected direction

QA selected the single navy/slate direction with restrained amber accent because it provides the
strongest hierarchy and contrast without decorative noise. The cyan focus colour remains distinct
from both text and accent, so keyboard position is unambiguous. The system font stack avoids a
network dependency and remains legible across supported platforms.

The strong field border token (`#64748b`) measures 3.78:1 against the input surface (`#0c1728`),
above the 3:1 WCAG 1.4.11 threshold for component boundaries.

## Validation matrix

| Area | Evidence |
|---|---|
| Keyboard | Skip links, navigation, form controls, logout and preview actions expose a visible focus ring. |
| Forms | Login errors use `aria-invalid`, `aria-describedby` and alert roles; processing uses `aria-busy` and a disabled labelled button. |
| Responsive | Public, login and dashboard layouts were checked at 1440×900 and 390×844; the dashboard sidebar becomes a horizontal compact navigation. |
| Motion | `prefers-reduced-motion` disables smooth scrolling and reduces CSS animation/transition duration. |
| Components | Button, card, badge, alert, table, empty, loading and error contracts are defined; representative states render in public/dashboard shells. |
| Fallback | Critical public content and Three.js alternatives remain available as semantic HTML. |

Representative captures are stored under `docs/captures/implemented/`.
