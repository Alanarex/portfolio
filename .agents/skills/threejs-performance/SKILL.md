---
name: threejs-performance
description: Implement or review the portfolio low-poly Three.js room preview, fullscreen mode, avatar animation, hotspots, WebGL fallbacks, and performance budgets. Use only for the 3D portfolio module.
---

1. Read `docs/product/three-d-preview.md`.
2. Validate GLB node names and animation clip names before coding around assets.
3. Lazy-load Three.js and assets.
4. Use authored cameras and constrained transitions.
5. Clamp DPR and avoid heavy post-processing.
6. Pause rendering outside viewport.
7. Implement poster, mobile, reduced-motion and WebGL-failure fallbacks.
8. Keep HTML equivalents for all hotspots.
9. Test fullscreen, Escape, focus restoration and repeated avatar clicks.
10. Measure bundle size, scene payload and runtime FPS before approval.
