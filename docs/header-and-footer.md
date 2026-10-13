# Header and footer

Code: blocks 3 and 3d of `bxscc-wordpress-enhancements.php`; layout in `css/theme-fixes.css` (header sections). Applies to the News Magazine theme.

These changes are made in PHP while the page is being generated (an output buffer on `template_redirect`), so the browser receives the finished header and nothing moves after it appears. The script in block 3 repeats the label and tagline fixes as a fallback.

## Layout

Social icons top left, the logo under them, and the site title large on the right, with the dark-mode button opposite the social icons on the top row (styles in `theme-fixes.css`).

## Two-line title

The site tagline is split at its first comma. The text after the comma is wrapped in `<span class="bx-tagline2">` and set as a second, smaller line. To change either line, edit the tagline under **Settings > General**; keep the comma.

## Social icons

The theme hard-codes the icon tooltips, with two wrong:

| Theme slot | Theme's label | Becomes |
|---|---|---|
| LinkedIn | "LinkEdin" | "LinkedIn" |
| Pinterest | "Pinterest" | "Discord" |

The theme has no Discord slot, so the Discord invite address goes in the theme's **Pinterest** field, and `theme-fixes.css` draws a Discord logo (an inline SVG mask) in that square instead of the Pinterest one. Each icon also gets an `aria-label`, so screen readers announce the right network.

The theme draws the Facebook and LinkedIn icons with the Font Awesome icon font: a 28 KB stylesheet and a 64 KB font file for two small glyphs. `theme-fixes.css` draws those two the same way as the Discord logo, as SVG masks made from Font Awesome 4.2's own outlines, sized and placed where the font put them (the glyph box is 1em tall and sits 256/1792 em below the baseline), so they look the same. The font and its stylesheet are no longer loaded ([performance-and-security.md](performance-and-security.md)). The masks take the link's text color, so hover and dark mode work as before.

The logo below the icons is 104px wide, exactly the width of the three squares and their gaps (until 1.55 it was 105px, 1px wider; the 100 x 96 file would be 104.17px wide at 100px tall, and `object-fit: cover` trims the difference). On phones the logo is centered under the title instead and keeps its 84 x 80 size.

The theme writes the logo with the description `alt="logo"`; since 1.80 the plugin replaces it with the site's name (Settings > General > Site Title), "Baltimore-Xiamen Sister City Committee", which is what a screen reader should announce there.

Each icon is centered in its square by the square itself (a flex box), and each mask is cropped to its glyph's outline, so the three glyphs share the same center whatever font metrics the browser has. Until 1.54 the Facebook and LinkedIn glyphs were placed by font metrics (as the icon font had placed them) and the Discord one by `vertical-align: middle`, which left the Discord logo about 1.5px low.

## Dark-mode button

The moon button is inserted after the social icon list. See [dark-mode.md](dark-mode.md).

## Footer

The theme's copyright line (`<span id="copyright">`) is kept and corrected each time a page is generated:

- The year becomes the current year.
- "Baltimore Sister Cities, Inc." becomes a link to baltimoresistercities.org.
- A Chinese line follows: 版权所有 © (year) 巴尔的摩–厦门姐妹城委员会，巴尔的摩姐妹城市公司（Baltimore Sister Cities, Inc.）成员。, marked `lang="zh-Hans"` so browsers and screen readers treat it as Chinese.

If the year cannot be found in the line, the line is left exactly as it was rather than risk dropping text.
