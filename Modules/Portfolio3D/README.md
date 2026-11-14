# Portfolio3D module

Owns the configuration of the optional low-poly developer room (PORT-010): scene enabled state,
asset version, poster and mobile fallback paths, greeting text, performance and reduced-motion
modes, and the fixed hotspot catalogue (visibility, order, labels, descriptions, section target).

- One `scene_configurations` row (`key = main`) plus one `scene_hotspots` row per catalogue key.
- Poster and fallback values are same-origin asset paths validated by an allowlist pattern; no
  upload or geometry editing exists here.
- Public consumers receive an immutable `SceneManifestData` through `PublicSceneReader`
  (cached per locale, invalidated synchronously on every save). Empty labels fall back to the
  localized defaults in `lang/*/portfolio.php`, never to another locale's override.
- Hotspot targets are an allowlist of landing-page sections, so every hotspot has an HTML link.
- The scene is disabled until an administrator enables it (no row = disabled). It has its own
  switch, independent of the `three_d` feature flag that governs the career-road preview.
- `GET /{locale}/scene.json` serves the same manifest; it answers 404 while the scene is disabled.
- The Three.js runtime lives in `resources/js/room-scene/` and is loaded lazily.
