# Performance and security

Code: blocks 3d, 4, 5 and 6 of `bxscc-wordpress-enhancements.php`.

## Security

- **XML-RPC is off** (`xmlrpc_enabled` returns false). The site does not use it (no remote publishing apps, no pingbacks), and it is a common target for password-guessing attacks.
- **PHP cannot be edited from the dashboard**, by anyone; see [site-css.md](site-css.md#php-cannot-be-edited-from-the-dashboard).

## Fewer and faster requests

- **Emoji scripts removed.** WordPress adds a script and styles to every page to draw emoji on old browsers that cannot; every current browser can.
- **Unused theme scripts unloaded**: the theme's jQuery Mobile swipe helpers (`wdwt_jquery_mobile`), a custom scrollbar for a widget the site does not use (`wdwt_mCustomScrollbar`, script and style), and its gallery fullscreen and lightbox loaders (`wdwt_jquery-fullscreen`, `wdwt_lightbox_loader`; the plugin has its own lightbox). Some of these also attached scroll and touch handlers that made scrolling less smooth. If anything stops working, the matching line in block 6 can be commented out.
- **Logo first.** The header logo gets `fetchpriority="high"` and is preloaded from `<head>`, so it is not among the last things to arrive.
- **Icon font preloaded.** The theme's Font Awesome file, which draws the social icons, is preloaded so the icons appear with the squares instead of after them.
- **Fonts** are self-hosted and preloaded; see [fonts.md](fonts.md).
- **Banner photos** load on demand and in viewing order; see [banner.md](banner.md).
- **Video previews** load YouTube only on click; see [video-previews.md](video-previews.md).
- **CSS is inline**, two blocks at the end of `<head>`, with no extra stylesheet requests; see [site-css.md](site-css.md).
