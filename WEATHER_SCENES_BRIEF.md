# Brief — Animated Weather Scene Backgrounds (for Antigravity)

> Companion to `WEATHER_ICONS_BRIEF.md`. The icons came out great — now give the
> **background scenes** the same treatment. **Do not break the new icons.**

## Goal
Upgrade the **animated background scene** that sits behind the weather UI so it is
**animated and matches the current weather** (sky gradient + moving sun/moon/stars/
clouds/rain/snow/lightning/fog), with a premium, depth-y feel — in both **day and night**.
English only. Must keep the **foreground text readable**. Respect `prefers-reduced-motion`.

---

## The ONE integration point (keep this contract)
Every scene is produced by a single function:

```js
WeatherFx.sceneHTML(data)   // in app/Views/layouts/weather-fx.js
```

- It returns the **full-bleed background** markup that is injected as the FIRST child of a
  host element carrying the class **`.wx-hero`**.
- The host (`.wx-hero`) is `position: relative; overflow: hidden; isolation: isolate;` and
  the foreground content sits above the scene via `.wx-hero > *:not(.wx-scene){ z-index:1 }`.
- So the returned root **must**:
  - be `position: absolute; inset: 0; z-index: 0;` and fill the host,
  - inherit the host's rounded corners (`border-radius: inherit`),
  - keep a **legibility veil** (a subtle dark gradient overlay) so white text on top stays
    readable on bright day scenes — the current code does this with `.wx-scene::after`.
  - be `pointer-events: none;`.
- Condition + day/night come from the SAME helpers as the icons:
  - `WeatherFx.normCondition(data.condition)` → `clear | partly | clouds | fog | drizzle | rain | snow | thunder`
  - `WeatherFx.isDay(data)` → day / night
- Keep `sceneHTML(data)`'s name + signature. CSS goes in `app/Views/layouts/weather-fx.css`.

**Where it's used (test all three — they have different shapes/aspect ratios):**
| Surface | File | Host element | Shape |
|---|---|---|---|
| Dashboard weather widget | `app/Views/doctor/assets/js/dashboard.js` (~L5834) | `.weather-widget.wx-hero` | wide card |
| Notice-bar weather popover | `app/Views/layouts/main.js` (~L1923, filled ~L2080) | `.weather-main.wx-hero` | medium hero |
| Forecast window — current panel | `app/Views/layouts/main.js` (~L2523) | `.wf-current-top.wx-hero` | wide banner |

> The forecast window opens from **both** the dashboard widget click and the notice-bar
> popover — it's the same window, so fixing its scene covers both entry points.

---

## States to cover (one scene per state, day/night where relevant)
Same set as the icons:

| Key | Day scene | Night scene |
|---|---|---|
| `clear`   | bright sky + glowing sun + sun rays / light haze | deep night sky + moon glow + twinkling stars |
| `partly`  | sky + sun + a few drifting clouds | night sky + moon + stars + drifting clouds |
| `clouds`  | overcast grey-blue + layered drifting clouds | darker overcast + clouds |
| `fog`     | hazy gradient + slow drifting fog bands | dark hazy + fog bands |
| `drizzle` | muted sky + light falling drizzle + soft clouds | darker + light drizzle |
| `rain`    | rainy grey-blue + falling rain streaks + clouds | dark + rain streaks |
| `snow`    | pale sky + drifting snowflakes + clouds | dark + snow |
| `thunder` | dark storm sky + rain + periodic lightning flash | (same, darker) |

These 8 keys already absorb every real condition string from the weather APIs
(OpenWeatherMap descriptions + Open-Meteo WMO codes).

---

## Visual & animation requirements
- Pure **CSS/SVG** animation (`transform`/`opacity` only — no JS rAF loops, no libraries).
- **Condition-accurate, atmospheric, with depth/parallax**: e.g. layered clouds moving at
  different speeds, rain/snow falling at varying depths, sun/moon glow that gently pulses,
  stars twinkling, lightning that flashes occasionally over a storm sky.
- **Day vs night** clearly different (warm/bright vs deep/cool + stars + moon).
- **Looping & seamless**, subtle enough that the foreground (temperature, location, pollen/
  dry-eye pills, forecast days) stays the hero — the scene is ambience, not noise.
- **Legibility**: keep/strengthen the dark veil overlay so white text + glass pills read on
  every scene (especially bright day scenes). Test the actual foreground in each surface.
- **Responsive to aspect ratio**: looks good in a wide widget, a medium popover hero, and a
  wide forecast banner. Use %/viewport-relative units, not fixed px.
- **Performance**: keep particle counts reasonable; it animates continuously on the
  dashboard. GPU-friendly transforms only.
- **Reduced motion**: freeze motion under `@media (prefers-reduced-motion: reduce)` (and the
  existing `.wx-reduced-motion` opt-in class used by the preview page).

---

## Deliverables
1. **Upgrade `WeatherFx.sceneHTML`** (+ CSS in `weather-fx.css`) for all 8 keys × day/night,
   keeping the `.wx-hero` contract above so the 3 live surfaces keep working.
2. **Preview page**: extend the existing `site/public/weather-icons-preview.html` (or add
   `site/public/weather-scenes-preview.html`) to render **every scene** at the **three host
   shapes** (widget / popover hero / forecast banner), in **day and night**, each with some
   sample white text + a glass pill on top to verify legibility. Include the reduced-motion
   toggle. Openable directly in the browser, no build step — for sign-off before adoption.

## Acceptance checklist
- [ ] `WeatherFx.sceneHTML(data)` returns a full-bleed `inset:0` background that fills any
      `.wx-hero` host and inherits its radius.
- [ ] All 8 keys × day/night look great across the 3 surface shapes.
- [ ] Foreground text/pills remain clearly readable on every scene (veil intact).
- [ ] Smooth, GPU-friendly, pauses under reduced-motion. No libraries, no console errors.
- [ ] The new **icons** still render correctly (don't regress `iconHTML`).
- [ ] Preview page shows the full set for review.
