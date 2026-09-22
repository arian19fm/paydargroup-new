# Self-hosted fonts

All fonts are served from this project — never from Google Fonts, Bunny
Fonts or any font CDN. Files here are referenced from
`resources/scss/base/_fonts.scss`; Vite copies them into `public/build/assets`
with a content hash at build time.

| Family (CSS name) | Files | Weights | Used for | Source / licence |
|---|---|---|---|---|
| `Doran` | `doran/Doran-{Regular,Medium,Bold,ExtraBold}.woff2` | 400 / 500 / 700 / 800 | Display: headings, eyebrows, buttons, product names (Figma `Doran_FaNum`) | Supplied by the client as `public/fonts/Doran` (no licence file was included — confirm web-embedding rights) |
| `IRANYekan` | `iranyekan/IRANYekan-{Regular,Medium}.woff2` | 400 / 500 | UI + body copy with Latin digits | FontIran, converted from the client's `IRANYekan/WebFonts` TTFs; `iranyekan/LICENSE.txt` (fill in the licence code) |
| `IRANYekanFN` | `iranyekan/IRANYekanFN-{Regular,Medium}.woff2` | 400 / 500 | UI + body copy with Persian digits (dates, read time, meta) | same package, `Farsi_numerals/WebFonts` |
| `Vazir` | `vazir/Vazir-{Regular,Medium,Bold}.woff2` | 400 / 500 / 700 | Stand-in for the Figma `Vazirmatn` styles (footer tagline, services list, "؟" tile) | Public domain / Apache 2.0 (`vazir/LICENSE`) |

Known gaps against the Figma file (see `docs/FRONTEND.md`):

- `Doran_FaNum` is referenced by the design; the supplied files are the plain
  `Doran` cut. Persian digit *characters* render correctly; only ASCII digits
  would show Latin shapes, so digits are converted with `fa_digits()` before
  rendering.
- `Doran_Classic_Dots_FaNum` (FAQ eyebrow only) is not available; Doran
  Regular is used.
- `Vazirmatn` is not available; the older Vazir family is used.

Rules:

- **WOFF2 only.** Every browser we target supports it; WOFF/TTF/EOT fallbacks
  are dead weight.
- **Only the weights the design system uses.** Add a weight only when a Figma
  text style requires it, and add the matching `@font-face` rule.
- The licence must permit web embedding; keep the licence file next to the
  fonts.
