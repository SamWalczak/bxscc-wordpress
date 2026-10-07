# Performance and security

Code: blocks 3d, 4, 5, 6, 10, 11, 14, 15 and 17 of `bxscc-wordpress-enhancements.php`, plus the checks described below in `css-editor.php` and `events/`.

## Security

- **XML-RPC is off** (`xmlrpc_enabled` returns false). The site does not use it (no remote publishing apps, no pingbacks), and it is a common target for password-guessing attacks.
- **PHP cannot be edited from the dashboard**, by anyone; see [site-css.md](site-css.md#php-cannot-be-edited-from-the-dashboard). That switch lives in this plugin, so it is off only while the plugin is active. `define('DISALLOW_FILE_EDIT', true);` in `wp-config.php` does the same without depending on any plugin, and is worth setting as well.
- **Every dashboard action checks who is asking and that the request came from the form**: a capability check (`edit_pages` for events, `edit_theme_options` for Site CSS) and a WordPress nonce on every save, delete and restore. Nothing is open to visitors who are not logged in (no `nopriv` handlers, no AJAX or REST endpoints).
- **Database queries** with any outside value go through `$wpdb->prepare()` or are cast to integers.
- **What events may contain.** Titles, Time or note, and Location allow links, bold, italic and line breaks, nothing else; they are filtered to those tags when saved and again when shown. Descriptions follow WordPress's own rule for posts (filtered unless the user may post unfiltered HTML).
- **Site CSS cannot break out of its style block.** A save is refused if the CSS contains `</style` or `<script`, checked both as written and as visitors receive it (with comments removed, which could otherwise join `</sty` and `le`), and any `</style` is escaped when the CSS is printed regardless. History file names are limited to letters, digits and a few symbols, so the editor never reads or writes outside `css/history/`. That folder holds the editors' login names and notes, so it carries an `.htaccess` that refuses it to web visitors on Apache (cPanel's web server).
- **The page rewrite only touches what it means to.** The header and banner changes (block 3d) work on the finished page. The banner part acts only on the theme's slideshow inside the header, never on page content made to look like it, and only when every slide is a photo from the site's own uploads. The slide addresses it writes into a script are encoded so that no value can end the script.
- **A failed rewrite never blanks the page.** If any step fails (a regular expression that hits PHP's limits returns nothing), the page goes out exactly as WordPress made it, so the page cache never keeps an empty page.
- **YouTube previews** accept only a real video id (11 letters, digits, `-` or `_`).
- **A section placed in one of its own events' descriptions** shows once, instead of repeating until PHP runs out of memory.
- **The yearly post** made on January 1st is made once even if two visitors arrive at the same moment (an option acts as a lock). A visitor's page load has no logged-in author, so it is credited to the author of the previous year's post.

## Fewer and smaller downloads

What a first-time visitor (empty browser cache) downloads in the first 3 seconds, measured on a copy of bxscc.org in October 2026 before and after version 1.47 (photos counted at their real file sizes):

| Page | Before | After |
|---|---|---|
| Home, desktop | 5.4 MB, 43 requests | 1.4 MB, 36 requests |
| Home, phone (3× screen) | 4.9 MB, 37 requests | 3.1 MB, 30 requests |
| News and Events, desktop | 1.5 MB | 0.4 MB |
| Partners, desktop | 749 KB | 272 KB |
| A text-only page (About Us) | 225 KB, 19 requests | 143 KB, 12 requests |
| All 40 pages, desktop | 18.6 MB | 8.2 MB |

No page got heavier. Every page was also compared before and after at six screen sizes, light and dark: no element moved or changed size by more than 1 pixel (a photo whose smaller copy rounds its height differently), and the only other visible differences are the two redrawn icons, matching to within a quarter pixel.

What changed:

- **Unused files are not loaded** (block 14): the theme's jQuery UI effects (nothing calls them), `comment-reply.js` (no page has a comment form), the block editor's stylesheets and WordPress's 9 KB of "global styles" (no page uses blocks), and Font Awesome's stylesheet and 64 KB icon font. The icon font drew only the Facebook and LinkedIn squares, which `theme-fixes.css` now draws from the same outlines (see [header-and-footer.md](header-and-footer.md#social-icons)). Each was checked against every page of the site; if one is ever needed again, delete its line.
- **jQuery Migrate stays.** It looks unused, but the theme's slideshow code calls `jQuery(window).load(...)`, which jQuery 3 removed and Migrate restores. Without it the banner would not size or start.
- **Photos come in the size they are shown at** (block 15): see [photos.md](photos.md#photo-sizes) and [banner.md](banner.md#photo-size).
- **One database query for all of a page's photos.** Finding which upload a photo address belongs to takes one query for every photo on the page, and their details load together, instead of up to five queries per photo.
- **No font work for a classic theme** (block 17): WordPress worked out the fonts of a block theme's `theme.json` on every uncached page (about 6 ms) and printed nothing, since a classic theme has none.
- **The theme's own security and efficiency issues** are covered in [news-magazine-theme.md](news-magazine-theme.md).
- **Emoji scripts removed.** WordPress adds a script and styles to every page to draw emoji on old browsers that cannot; every current browser can.
- **Unused theme scripts unloaded** (block 6): the theme's jQuery Mobile swipe helpers (`wdwt_jquery_mobile`), a custom scrollbar for a widget the site does not use (`wdwt_mCustomScrollbar`, script and style), and its gallery fullscreen and lightbox loaders (`wdwt_jquery-fullscreen`, `wdwt_lightbox_loader`; the plugin has its own lightbox). Some of these also attached scroll and touch handlers that made scrolling less smooth.
- **Logo first.** The header logo gets `fetchpriority="high"` and is preloaded from `<head>`, so it is not among the last things to arrive.
- **Fonts** are self-hosted and preloaded; see [fonts.md](fonts.md).
- **Banner photos** load on demand and in viewing order; see [banner.md](banner.md).
- **Video previews** load YouTube only on click, and fetch the thumbnail only when the preview comes near the screen; see [video-previews.md](video-previews.md).
- **CSS is inline**, two blocks at the end of `<head>`, with no extra stylesheet requests and without the files' comments and spacing; see [site-css.md](site-css.md).

## Settings outside this plugin

One-time changes on the server, not code, found in the same review:

- **Autoloaded options.** WordPress loads every "autoload" option on every uncached request. On bxscc.org, 237 KB of the 307 KB came from plugins and a theme no longer active, plus a stale folder-size cache. Switching those to not autoload (`wp_set_options_autoload()`) keeps them in the database but out of every request. Housekeeping more than speed: on a local copy the difference in page time was within measuring noise.
- **Third-party files in the sidebar.** The PayPal donate button's image was loaded from baltimoresistercities.org, and PayPal's tracking pixel from paypalobjects.com, on every page. Hosting the button image in the site's Media Library and deleting the pixel removes two outside connections from every page, and PayPal's record of every visit; the button works the same.
- **Unused plugins.** An active plugin's PHP runs on every uncached request even if nothing on the site uses it.
