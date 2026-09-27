# Self-hosted fonts

All fonts are served from this project — never from Google Fonts, Bunny
Fonts or any font CDN. Files here are referenced from
`resources/scss/base/_fonts.scss`; Vite copies them into `public/build/assets`
with a content hash at build time.

| Family (CSS name) | Files | Weights | Used for | Source / licence |
|---|---|---|---|---|
| `Doran` | `doran/DoranFaNum-{Regular,Medium,Bold,ExtraBold}.woff2` | 400 / 500 / 700 / 800 | Display: headings, eyebrows, buttons, product names (Figma `Doran FaNum`; Persian digit shapes) | Client package `public/fonts/Doran/WebFont/Fa Num/woff2`; `doran/LICENSE.pdf` |
| `Doran Classic Dots` | `doran/DoranClassicDotsFaNum-Regular.woff2` | 400 | The dotted eyebrows (FAQ, business benefits — Figma `Doran Classic Dots FaNum`) | same package |
| `IRANYekan` | `iranyekan/IRANYekan-{Regular,Medium}.woff2` | 400 / 500 | UI + body copy with Latin digits | Client package `public/fonts/IranYekan/Source 2 v3.0 Newer/WebFonts` (v3.0); `iranyekan/LICENSE.txt` |
| `IRANYekanFN` | `iranyekan/IRANYekanFN-{Regular,Medium,ExtraBold}.woff2` | 400 / 500 / 800 | UI + body copy with Persian digits (dates, meta, benefit titles) | same package, `Farsi_numerals/WebFonts` TTFs converted to WOFF2 with fontTools |
| `Vazir` | `vazir/Vazir-{Regular,Medium,Bold}.woff2` | 400 / 500 / 700 | Stand-in for the Figma `Vazirmatn` styles (footer tagline, services list, some mobile body text) | Public domain / Apache 2.0 (`vazir/LICENSE`) |

Known gaps against the Figma file (see `docs/FRONTEND.md`):

- `Vazirmatn` is not available; the older Vazir family is used.
- The client's full font packages live in `public/fonts/` (source only — not
  referenced by the build). Only the cuts above are bundled.

Rules:

- **WOFF2 only.** Every browser we target supports it; WOFF/TTF/EOT fallbacks
  are dead weight.
- **Only the weights the design system uses.** Add a weight only when a Figma
  text style requires it, and add the matching `@font-face` rule.
- The licence must permit web embedding; keep the licence file next to the
  fonts.
