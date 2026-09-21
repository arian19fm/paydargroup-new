# Self-hosted fonts

All fonts are served from this project — never from Google Fonts, Bunny
Fonts or any font CDN. Files here are referenced from
`resources/scss/base/_fonts.scss`; Vite copies them into `public/build/assets`
with a content hash at build time.

| Family | Files | Weights | Format | License |
|---|---|---|---|---|
| Vazir | `vazir/Vazir-{Regular,Medium,Bold}.woff2` | 400 / 500 / 700 | WOFF2 only | Public domain / Apache 2.0 (see `vazir/LICENSE`) |

Rules:

- **WOFF2 only.** Every browser we target supports it; WOFF/TTF/EOT fallbacks
  are dead weight.
- **Only the weights the design system uses.** Add a weight only when a Figma
  text style requires it, and add the matching `@font-face` rule.
- The interim family is Vazir because it is open-licensed and available. If
  the Figma design specifies IRANYekan (commercial license) or another family,
  swap the files, update `_fonts.scss` and `$pg-font-family-base` in
  `abstracts/_variables.scss`. The license must permit web embedding.
- Farsi-digit (`FD`) variants are intentionally not used: digit shaping is a
  content/typography decision to be confirmed against the design.
