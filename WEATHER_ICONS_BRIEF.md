# Brief — Animated SVG / 3D Weather Icons (for Antigravity)

## Goal
Design a set of **visually stunning, animated weather icons** (clean SVG, with a 3D /
depth feel — gradients, soft shadows, subtle motion) that cover **every weather state**
the app can show. Then wire them into the existing icon API and ship a **standalone
preview page** so they can be reviewed *before* they go live.

All UI text is **English**. Works in both **light and dark** backgrounds. Must respect
`prefers-reduced-motion` (freeze animation when requested).

---

## The ONE integration point (do not break this contract)
Every weather surface in the app renders its icon through a single function:

```js
WeatherFx.iconHTML(data, sizePx)   // defined in app/Views/layouts/weather-fx.js
```

- It returns an inline element sized to a **square** `sizePx` and must keep doing so.
- Current output wrapper: `<span class="wx-icon wx-icon--TYPE" style="--wx-size:Npx">…</span>`.
  You may change the *internals* (svg markup / classes) freely, but keep:
  - the function name + signature `iconHTML(data, sizePx)`,
  - a single square root element whose size is driven by `sizePx`,
  - the type chosen via the two existing helpers (below).
- Condition + day/night are decided by:
  - `WeatherFx.normCondition(data.condition)` → one of:
    **`clear`, `partly`, `clouds`, `fog`, `drizzle`, `rain`, `snow`, `thunder`**
  - `WeatherFx.isDay(data)` → `true` (day) / `false` (night)
- CSS lives in `app/Views/layouts/weather-fx.css`. Keep all new icon CSS there (or a new
  file loaded alongside it).

**Where it's called (so test these sizes):**
| Surface | File | size |
|---|---|---|
| Notice-bar weather popover (big) | `app/Views/layouts/main.js` | 110 |
| Dashboard weather widget | `app/Views/doctor/assets/js/dashboard.js` | 72 |
| Forecast window — current | `app/Views/layouts/main.js` | 64 |
| Forecast window — each of 7 days | `app/Views/layouts/main.js` | 36 |

So each icon must look crisp from **~32px up to ~120px**.

> Note: the same module also has `sceneHTML(data)` (animated full-card *background*
> scenes). That's separate — this brief is only about the **icons**. You may leave
> `sceneHTML` as-is, or give it the same visual upgrade if you want (optional).

---

## States to cover (icon per state)
Produce a distinct, polished icon for each. Day/night variants where it matters.

| Key (from normCondition) | Day | Night | Notes |
|---|---|---|---|
| `clear`   | ☀️ Sun | 🌙 Moon (+ stars) | sun should have attached rays; moon a clean crescent |
| `partly`  | Sun behind cloud | Moon behind cloud | |
| `clouds`  | Clouds / overcast | (same) | 2–3 layered clouds |
| `fog`     | Fog / mist / haze | (same) | cloud + drifting fog lines |
| `drizzle` | Light rain | (same) | cloud + few small drops |
| `rain`    | Rain | (same) | cloud + falling drops |
| `snow`    | Snow | (same) | cloud + falling flakes |
| `thunder` | Thunderstorm | (same) | cloud + lightning bolt (+ rain) |

**Real condition strings these map from** (so nothing is missed): the app's weather comes
from OpenWeatherMap descriptions (`clear sky, few/scattered/broken/overcast clouds, mist,
haze, drizzle, light/moderate/heavy rain, shower rain, thunderstorm, snow, sleet`) and
Open-Meteo WMO codes (`Clear, Mainly Clear, Partly Cloudy, Overcast, Foggy, Rime Fog,
Light/Dense Drizzle, Freezing Drizzle, Slight/Moderate/Heavy Rain, Freezing Rain,
Slight/Heavy Snow, Thunderstorm`). They all already collapse into the 8 keys above.

**Optional bonus** (only if easy): richer sub-variants — `heavy-rain`, `sleet/freezing`,
`hail`, `windy`. If you add any, also extend `WeatherFx.normCondition` to return the new
key, and add it to the preview page.

---

## Visual & animation requirements
- **SVG-based**, self-contained (no external icon libraries). Pure CSS/SVG animation
  (`transform`/`opacity` only for performance — no JS rAF loops).
- A **3D / depth feel**: radial/linear gradients, soft inner highlight + drop shadow,
  layered elements. Think "premium weather app", not flat line icons.
- **Subtle, looping motion**: sun rays rotate, clouds drift/bob, rain/snow fall, bolt
  flashes, stars twinkle, moon glows. Keep it tasteful (not distracting).
- **Theme-aware**: readable on light AND dark cards (the icon sits on colored/photographic
  backgrounds too, so give it its own contrast — e.g. soft outline/shadow).
- **Scalable**: identical look at 36px and 110px (use viewBox, relative units).
- **Reduced motion**: wrap animations so they stop under
  `@media (prefers-reduced-motion: reduce)`.

---

## Deliverable 1 — the icons
Implement them inside `WeatherFx.iconHTML` (replace the current per-type markup) + CSS in
`weather-fx.css`. Keep the contract above so the 3 live surfaces keep working untouched.

## Deliverable 2 — a standalone PREVIEW PAGE (for review before adoption)
Create `site/public/weather-icons-preview.html` — a self-contained page that:
- loads `weather-fx.js` (+ its CSS),
- renders **every** state (all 8 keys, with day & night where relevant),
- shows each icon at **4 sizes** (36, 64, 72, 110),
- shows each on **both** a light and a dark tile, with the state name labelled,
- has a "reduced motion" toggle to preview that mode.

It must be openable directly in the browser (e.g. `/<host>/public/weather-icons-preview.html`)
with no build step. This is purely for visual review — it will not be linked from the app.

---

## Acceptance checklist
- [ ] `WeatherFx.iconHTML(data, sizePx)` still returns a square element sized by `sizePx`.
- [ ] All 8 condition keys + day/night look great at 36–110px, light & dark.
- [ ] Animations are smooth, GPU-friendly, and pause under reduced-motion.
- [ ] No external libraries added; no console errors.
- [ ] Preview page renders the full set for sign-off.
- [ ] The 3 live surfaces (popover / dashboard widget / forecast window) still render
      correctly (open each and check).
