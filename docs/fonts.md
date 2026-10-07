# Fonts

Code: block 7 of `bxscc-wordpress-enhancements.php`. Files: `fonts/` (license: `fonts/OFL.txt`, SIL Open Font License 1.1).

## Self-hosted Open Sans

The theme loads Open Sans from Google Fonts with a separate loader script. The plugin serves the font from its own `fonts/` folder instead and removes the theme's Google Fonts link, the `webfont.js` loader and its configuration script from the page.

Why: no requests to Google from visitors' browsers (privacy, and one less outside service that can be slow or blocked), and the font arrives over the same connection as the page.

Only the weights the site uses are included, each with its italic: regular (400), semibold (600) and bold (700). Latin subset, woff2 format.

## Loading without a jump

- Regular and semibold are preloaded from `<head>`, so they are usually ready before text is painted.
- `font-display: optional`: if the font is not ready within a moment of first paint, that page view keeps the fallback font for good rather than swapping fonts mid-read. The next page view finds the font cached.
- The fallback is Arial adjusted to Open Sans's measurements (`size-adjust`, `ascent-override` and related), declared as "Open Sans Fallback", so a page shown in the fallback lays out in the same place.
- Body text, headings and form controls use `"Open Sans", "Open Sans Fallback", Arial, sans-serif`, overriding the theme's font settings.

## Changing the font

Replace the files in `fonts/`, update the file names in block 7, and adjust or remove the fallback metrics, which are specific to Open Sans.
