# Performance and security

Code: blocks 3d, 4, 4b, 5, 6, 10, 11, 14, 14b, 15, 17, 18, 21 and 22 of `bxscc-wordpress-enhancements.php`, plus the checks described below in `css-editor.php`, `events/` and `links/`.

The two October 2026 audits (versions 1.69 and 1.76) are summarized at the end.

## Security

- **XML-RPC is off** (`xmlrpc_enabled` returns false). The site does not use it (no remote publishing apps, no pingbacks), and it is a common target for password-guessing attacks. That setting covers only the methods that need a login, so the pingback methods are removed as well, and the head no longer advertises the remote-publishing address (RSD). (`system.multicall`, which bundles several calls into one request, cannot be removed this way, since WordPress's XML-RPC server adds it back itself; with logins off it has nothing harmful to bundle.)
- **Security headers on every page** (block 4b): `X-Content-Type-Options: nosniff` (browsers take each response as the type the server states), and `X-Frame-Options: SAMEORIGIN` with `Content-Security-Policy: frame-ancestors 'self'` (other sites cannot show the pages inside a frame of theirs, the basis of clickjacking). The site's own frames (the Customizer preview) work as before, and WordPress embeds (`/embed/` addresses, made for other sites) get neither frame header. A header another plugin already set through WordPress is left alone. The values never vary, so the Nginx cache stores and replays them with each page.
- **The link checker never requests the server's own network** (`links/links.php`, `bx_lk_vet`): a link, or anything it redirects to, that points at a private, loopback, link-local, carrier-grade or reserved address is not requested. Each request is checked just before it goes out (the first and every redirect), against all of the host's IPv4 and IPv6 addresses, and the connection is pinned to the addresses checked, so DNS that answers differently a moment later changes nothing. Numeric hosts in unusual notation (`0x7f000001`, `2130706433`) are refused. Until 1.69 only the first address was checked, by IPv4 name lookup: a linked site that redirected to `127.0.0.1`, or a hex-written address, reached the server's own network (confirmed on a test copy).
- **The archive.org keys go only to the Archive.** Requests that carry them never follow a redirect (until 1.69 a redirect to another host received the keys). The keys are stored encrypted and never shown again, and the functions that handle them mark them as sensitive, so PHP 8.2 and later leave them out of error traces; see [links.md](links.md).
- **PHP cannot be edited from the dashboard**, by anyone; see [site-css.md](site-css.md#php-cannot-be-edited-from-the-dashboard). That switch lives in this plugin, so it is off only while the plugin is active. `define('DISALLOW_FILE_EDIT', true);` in `wp-config.php` does the same without depending on any plugin, and is worth setting as well.
- **Every dashboard action checks who is asking and that the request came from the form**: a capability check (`edit_pages` for events, `edit_theme_options` for Site CSS) and a WordPress nonce on every save, delete and restore. Nothing is open to visitors who are not logged in (no `nopriv` handlers, no AJAX or REST endpoints).
- **No "updates" from WordPress.org** for the plugin or the theme (`Update URI: false` in their headers, since 1.76). WordPress sent both names to WordPress.org with every update check; if anyone ever published a plugin under this plugin's folder name there, or the closed theme came back, the dashboard would offer their files as an update and one click would replace the site's code. With the header, WordPress does not ask about either.
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

- **Files a page does not need are left out of it** (block 14), and come back by themselves where they are needed, so WordPress keeps working normally: the theme's jQuery UI effects (nothing calls them); `comment-reply.js` (comments stay closed); the block editor's stylesheets and WordPress's "global styles" (the colors, font sizes and layouts a block can be given), left out unless the page was built with blocks or a block widget is in the sidebar (Custom HTML and Shortcode blocks, which the home page and News and Events use, need none of it); and Font Awesome's stylesheet and 64 KB icon font, left out unless one of the theme's other social icons (Twitter, Google+, RSS, Instagram, YouTube) is switched on in its settings, or its Social Icons widget is in the sidebar. The header's Facebook, LinkedIn and Discord icons are drawn by `theme-fixes.css` (see [header-and-footer.md](header-and-footer.md#social-icons)). Until 1.69 the block styles and the icon font were left out everywhere: a new page built with blocks lost its colors and button styles, and a newly switched-on icon showed an empty square.
- **jQuery at the end of the page** (block 14b, since 1.76): jQuery, jQuery Migrate and the theme's `javascript.js` load at the end of the page instead of in `<head>`, where they held up the first paint, on every page except those with the banner slideshow (the home page and its page 2: the slideshow's script needs jQuery while the page loads). Nothing earlier in those pages needs them (checked on every page), and the plugin's own scripts that use jQuery (blocks 3f and 3g) still print after them. Measured on a slow phone connection, empty browser cache: text appears 0.2 to 0.4 seconds sooner (About Us 1160 to 920 ms, the 2018 Youth Ambassadors page 1328 to 924 ms, the program page 1308 to 1020 ms, 8 page types); no change on desktop. They stay in `<head>` on every page while a theme setting writes a script into the page (the header's "Latest posts" ticker, or code pasted under the theme's Integration or Advertising settings), and on a page whose text, or a widget's, has a script written into it; none of these is the case today. WordPress itself also puts them back in `<head>` if a script printed there depends on them.
- **jQuery Migrate stays.** It looks unused, but the theme's slideshow code calls `jQuery(window).load(...)`, which jQuery 3 removed and Migrate restores. Without it the banner would not size or start.
- **Photos come in the size they are shown at** (block 15): see [photos.md](photos.md#photo-sizes) and [banner.md](banner.md#photo-size).
- **One database query for all of a page's photos.** Finding which upload a photo address belongs to takes one query for every photo on the page, and their details load together, instead of up to five queries per photo.
- **No font work for a classic theme** (block 17): WordPress worked out the fonts of a block theme's `theme.json` on every uncached page (about 6 ms) and printed nothing, since a classic theme has none.
- **The theme's own security and efficiency issues** are covered in [news-magazine-theme.md](news-magazine-theme.md).
- **Theme resize work only when the width changes** (block 3g): the theme's layout switch and the home carousel's sizing ran on every window resize, including the height-only ones a phone fires when its browser bar slides back in while scrolling up. On the home page that cost a dropped frame at that moment (measured on a phone-sized window at 6x CPU slowdown); both depend only on the width, so they now run only when it changes.
- **Emoji scripts removed.** WordPress adds a script and styles to every page to draw emoji on old browsers that cannot; every current browser can.
- **Unused theme scripts unloaded** (block 6): the theme's jQuery Mobile swipe helpers (`wdwt_jquery_mobile`), a custom scrollbar for a widget the site does not use (`wdwt_mCustomScrollbar`, script and style), and its gallery fullscreen and lightbox loaders (`wdwt_jquery-fullscreen`, `wdwt_lightbox_loader`; the plugin has its own lightbox). Some of these also attached scroll and touch handlers that made scrolling less smooth.
- **Logo first.** The header logo gets `fetchpriority="high"` and is preloaded from `<head>`, so it is not among the last things to arrive.
- **Fonts** are self-hosted and preloaded; see [fonts.md](fonts.md).
- **Banner photos** load on demand and in viewing order; see [banner.md](banner.md).
- **Video previews** load YouTube only on click, and fetch the thumbnail only when the preview comes near the screen; see [video-previews.md](video-previews.md).
- **CSS is inline**, two blocks at the end of `<head>`, with no extra stylesheet requests and without the files' comments and spacing; see [site-css.md](site-css.md).
- **New photos are WebP** (block 21): see [photos.md](photos.md#new-uploads-are-webp).
- **No camera data in photo files** (block 22, since 1.76): the copies of the program page's main photo carried 43 KB of camera data each, a banner slide 10 to 17 KB; on a slow phone connection that photo shows about 0.2 to 0.35 seconds sooner once `tools/photo-metadata.php` has run (simulated by serving its copies that much smaller). See [photos.md](photos.md#camera-data).
- **No jump under photos without dimensions** (block 15): see [photos.md](photos.md#photo-sizes).
- **The year lists skip a lookup they do not use** (categories of the yearly posts): one query fewer on News and Events and on each yearly post. The Link check report loads its pages and event titles in two queries instead of one per place listed.

## Settings outside this plugin

One-time changes on the server, not code, found in the same review:

- **Autoloaded options.** WordPress loads every "autoload" option on every uncached request. On bxscc.org, 237 KB of the 307 KB came from plugins and a theme no longer active, plus a stale folder-size cache. Switching those to not autoload (`wp_set_options_autoload()`) keeps them in the database but out of every request. Housekeeping more than speed: on a local copy the difference in page time was within measuring noise.
- **Third-party files in the sidebar.** The PayPal donate button's image was loaded from baltimoresistercities.org, and PayPal's tracking pixel from paypalobjects.com, on every page (and again in the program page's own text). `tools/donate-button.php` (1.76 audit) puts the button image in the Media Library and points the button at it, and replaces the pixel's address with a blank 1-pixel image written into the page; the tag stays so that nothing moves, not even by half a pixel. That removes two outside connections from every page, and PayPal's record of every visit; the button looks and works the same.
- **Unused plugins.** An active plugin's PHP runs on every uncached request even if nothing on the site uses it.

## October 2026 audit (version 1.69)

Three independent reviews (the plugin's security, the theme's security, speed), each finding reproduced on a copy of the site before it was fixed. Nothing visitors see changed: all 45 page types (every page and post, the 404, search, archives) were compared as screenshots at 1280 and 390 pixels before and after, identical except one page whose screenshot also differs between two runs of the same version.

Fixed: the link checker's private-address check (bypassable by a redirect or a hex address), archive.org keys following redirects, the pingback XML-RPC methods, form fields that crashed on an array, an unencoded search term in the Events list's page links, a fatal error in the theme's thumbnail code for an address containing `%00` (see [news-magazine-theme.md](news-magazine-theme.md)), security headers added. Normal WordPress use restored where earlier speed work had narrowed it: block styles, the icon font, and Nginx Helper's Purge Cache button (see [maintenance.md](maintenance.md#page-cache)).

Faster: phones no longer download the 2560-pixel banner photo before the one they show (the start slide's preload ran before the page's viewport settings, so a phone took the page to be 980 pixels wide; on a 3x phone the start photo arrived 1.6 seconds sooner), no layout jump under the History page's first photo (layout shift 0.062 to 0), uncropped banner photos 74 MB to 28 MB (capped at 4800 pixels; see [banner.md](banner.md#uncropped-photos-in-the-lightbox)), new photos stored as WebP, and four photos whose loading order was wrong (`tools/photo-loading.php`, a content change: the main photo on History, Mission Overview and the Youth Ambassadors pages shows 0.3 to 1.2 seconds sooner on a slow phone connection).

Checked and sound: every dashboard form (capability and nonce), SQL, the Site CSS editor, the page rewrite against crafted pages and search terms, the inline scripts (no unsafe use of the address, captions or data), the shortcodes, email, cron, cache safety (no page varies by cookie or header), and the theme's earlier fixes (the removed AJAX handlers stay removed).

Recommended outside the plugin (not code): a `.htaccess` rule refusing direct requests to the theme's PHP files (27 of them stop with a fatal error when requested directly, which writes to an `error_log` each time), cache lifetimes for CSS, JavaScript and fonts, Wordfence's option against username discovery, and a one-time check that `wp-config.php` has its own secret keys (the archive.org keys are encrypted with them).

## October 2026 audit (version 1.76)

After the October content work (the program page and the 2018, 2019, 2024 and 2025 Youth Ambassadors pages, versions 1.70 to 1.75), three more independent reviews: the plugin's security, the theme's and the new content's security, and speed (measured through a local HTTP/2 proxy that serves photos at their real sizes, on a simulated slow phone and a desktop). A fourth review checked the fixes. Nothing visitors see changed: the HTML of every page type differs from 1.75 only in where four script tags sit (jQuery, above), full-page screenshots of all 45 page types at 1280 and 390 pixels are identical, the menus, dark mode, lightbox and resizing behave the same with no script errors, and the sidebar donate button is pixel-identical after `tools/donate-button.php`.

Fixed or added:

- **Faster first paint** on every page but the home page: jQuery at the end of the page (block 14b, above).
- **Camera data out of photo files** (block 22 for new uploads, `tools/photo-metadata.php` once for the library): smaller files, and no locations in the photos visitors download. See [photos.md](photos.md#camera-data).
- **No updates offered from WordPress.org** for the plugin or the theme (above).
- **The donate button** no longer loads anything from two other sites (`tools/donate-button.php`, above).
- **The theme no longer contains the Google web font loader** that the plugin was removing from every page: had the plugin's page rewrite ever failed, the loader would have come back. See [news-magazine-theme.md](news-magazine-theme.md).

Checked and sound: the code added since 1.69 (no visitor input, nothing written, nothing that varies by user, so the page cache stays safe; under 0.1 ms per page); every dashboard action tried as a visitor and as each role without the form's token (all refused, nothing changed); the link checker against 28 spellings of internal addresses behind a redirect, and the archive.org keys behind a redirect; the page rewrite against crafted search terms and addresses; the theme's switched-off endpoints and its search, archives and odd query values; the new pages (no scripts, frames, event handlers or `javascript:` links, `noopener` on every link that opens a new window, nothing loaded over http); the content scripts (refused over the web, fingerprint checks, dry runs, revisions kept). Server time 27 to 46 ms per uncached page; layout shift 0 on every measured page.

Measured but left as they are, because they would change what visitors see or could not be checked here:

- **The 2018 page's lower photos load lazily.** Two photos far down the page load at the same time as its main photo; marking them `loading="lazy"` would let the main photo arrive sooner, but WordPress would then pick a smaller copy of one of them on desktop (not pixel-identical), and the gain could not be measured (no file sizes on record for the 2018 photos).
- **Tighter photo sizes on phones**, and **trimming the theme's `style.css`** (17% of it is used): a different photo copy, and a risk of losing styling not exercised in the test.
- **A Meipian link's sharer id** (`share_user_mpuuid`, on the 2018 and program pages) identifies the account that shared the article. Removing it is a content change, and whether the shorter link still opens the article could not be checked from here.
- **`rel="noopener"` on the theme's social icons**: current browsers apply it to every link that opens a new window.

To check on the live site (not code): that `error_log` files are refused to visitors (the theme's showed the server's folder layout), that the page cache's purge address answers only the server itself, and, using `tools/photo-metadata.php`'s report, the "original image" files that still carry a location. WordPress shows login names at `/?author=1` and `/wp-json/wp/v2/users`; Wordfence's option against username discovery, on since October 8, hides them.
