# The News Magazine theme

The site runs **News Magazine 1.1.12** by Web-Dorado. WordPress.org closed the theme in 2019 (last release June 7, 2019) and Web-Dorado has since become 10Web; no update will ever come. No vulnerability has been published for it (WPScan lists none), but nobody is looking either, so it was reviewed line by line in October 2026, the same way as this plugin.

What it found and what was done, in two places:

- **Block 16 of `bxscc-wordpress-enhancements.php`** switches off, from outside the theme, the parts that anyone could reach but no page of the site uses. Nothing in the theme's files changes for these.
- **Six small edits to theme files** for what cannot be fixed from outside, in the theme's own files in [`wp-content/themes/news-magazine/`](../wp-content/themes/news-magazine/).

The whole theme is in this repository: WordPress.org closed it in 2019, so it can no longer be installed from the dashboard, and this copy is the way to get it back. Its history starts from the theme as released (rebuilt from the server's copy by reversing the recorded local edits, see below), so `git log -p -- wp-content/themes/news-magazine` shows every change made to it.

Every page was compared before and after: the HTML of all 43 pages checked is byte-for-byte the same, and the theme's stylesheet and scripts are untouched, so the site looks and behaves exactly as before.

## Switched off by the plugin (block 16)

| What | Problem | Used by the site? |
|---|---|---|
| AJAX action `wdwt_lightbox` (public, no login) | Printed whatever was posted to it, unescaped, into an HTML page on the site's own address: a cross-site scripting hole. A malicious page could send an administrator's browser there and run script as the site. | No: the theme's lightbox script was already unloaded; the plugin has its own lightbox. |
| AJAX actions `wdwt_front_content_posts_section` and `wdwt_front_top_posts_section` (public) | Listed unpublished drafts, pending and scheduled posts (titles, links, excerpts) to anyone. | No: they feed the post lists of a blog-style home page; the site's home page is a static page. |
| The theme's "please review" notices | Their dismiss links ran for anyone, logged in or not, and saved whatever was sent into a setting loaded on every page. A non-numeric value also caused a PHP error. | No: both notices were dismissed long ago. |
| Appearance > News Magazine | An "upgrade to Pro" page for a product that no longer exists, and a support tab that stops with a PHP 8 error. | No. The theme's settings are all in Appearance > Customize, which is unchanged. |
| The recommended-plugins installer (TGM Plugin Activation 2.6.1) | Recommends ten Web-Dorado plugins, none installed, some with serious security histories. Its plugin-information screen also answered visitors who were not logged in, making the server call wordpress.org on their behalf. | No. |

Also in block 16: the layout widths the theme does arithmetic with (main column, sidebar widths, content area, layout number) are kept to valid numbers, both in the theme settings and in each page's Layout box. An empty or non-numeric width was a PHP 8 error: emptying "Main column width" in the Customizer took every page down with "There has been a critical error". Valid values pass through unchanged.

Not switched off, because they are admin-only: the theme's custom CSS box, footer text and slider texts print what an administrator typed without filtering. Administrators can already add any code to the site, so this is not a hole.

## Theme file edits (October 2026)

Each edit is marked in the file with a `BXSCC 2026-10` comment.

| File | Change | Why |
|---|---|---|
| `inc/admin/page_slider.php` | Declares the `$shortslider` property its constructor sets. | PHP 8.2 deprecates setting undeclared properties, and it runs on every request; a future PHP will make it an error. |
| `inc/lib/WDWT_input.php` | Removes `= false` from a parameter that comes before a required one. | PHP 8 deprecation; PHP already treated it as required, so behavior is unchanged. |
| `inc/front/front_params_output.php` | The header "latest posts" ticker queries the database only when the ticker is switched on. | It ran three database queries on every page and threw the result away (the ticker is off). |
| `inc/front/WDWT_front_functions.php` | Post-list thumbnails are measured from the image file on disk. | The theme fetched each image over HTTP just to read its size: a request from the server to itself per listed post, or to any other site a post's first image pointed at. Not triggered on the site today (no listed post has an image yet), so this prevents a slowdown and a server-side request forgery risk rather than fixing a current one. Images not on the site count as "size unknown", which the theme already handled. Since 1.69 (October 8, 2026), an address containing `%00` also counts as size unknown: decoded, it made `realpath()` throw a fatal error on PHP 8, which would have taken down every archive page listing such a post. |

| `style.css` | `Update URI: false` added to the header (1.76 audit, October 8). | WordPress sent the theme's name to WordPress.org with every update check. The theme is closed there, but if it ever came back, or another theme took its name, the dashboard would offer those files as an update, and updating would replace these fixes. With the header WordPress does not ask. |
| `inc/front/WDWT_front_params_output.php` | The Google web font loader script removed from `typography()`, with the three font lookups that only fed it (1.76 audit, October 8). | The site's font is self-hosted, and the plugin was already removing the loader from every page (block 3d); had that rewrite ever failed, the page would have gone out with the loader, contacting Google again. Pages are the same except for one blank line less where the script was. |

Wordfence reports changed theme files; mark these "Ignore until file changes", like the earlier local edits. `style.css` was already marked for its earlier edit and is reported again once the 1.76 version is installed.

## Earlier local edits

Made on the server before this review, and already in the theme here. In October 2026 Wordfence compared the server's theme with WordPress.org's original and found these four files changed, plus the added `editor-style.css`; all are intended.

| File | Change | Date | Why |
|---|---|---|---|
| `style.css` | `#logo img { width: 100%; }` commented out | Aug 20, 2024 | Keeps the logo at its real size. |
| `inc/front/front_params_output.php` | `$imgs_number = (array) $imgs_number;` added | Aug 20, 2024 | PHP 8 compatibility for the theme's slider. |
| `inc/widgets/widget-advert.php` | `$bannerPath = (array) $bannerPath;` added | Aug 20, 2024 | PHP 8 compatibility for the banner widget. |
| `functions.php`, `editor-style.css` | `add_theme_support( 'editor-styles' );` before `add_editor_style();`, and `editor-style.css` (a copy of `style.css`) added | Oct 2, 2026 | The block editor shows pages in the site's styles. |

Do not let Wordfence "Repair" these files: it would put back the original files and undo the PHP 8 fixes.

## Faster pages

- **About 5 ms less per uncached page** from block 17: WordPress worked out the fonts of a block theme's `theme.json` on every page (about 6 ms) and printed nothing, since a classic theme has none. Skipped for classic themes.
- **Three fewer database queries per page** from the ticker edit above.
- **Three fewer font lookups per page** from the font loader edit above.

Measured but left alone, because the gain does not justify the risk to the site:

- **The theme's `style.css` is 74 KB (14 KB compressed), and 86 to 92% of it styles things no current page uses.** Cutting it down would save about 11 KB on a visitor's first page (browsers cache it after that), but any page or widget added later could lose styling that the full file would have given it.
- **The theme builds its settings pages on every request** (about 1 to 2 ms) and its menu takes 3 to 4 ms to render. Changing either means rewriting theme code; and the Nginx page cache serves most visits without running PHP at all.

## Outside the code

- **`error_log` in the theme folder.** cPanel writes PHP errors into a file named `error_log` in the folder of the script that raised them. The theme's one (from WP-CLI runs that loaded the theme) shows the server's folder layout, including the cPanel account name, and can be downloaded unless the web server refuses it. Delete these files, and refuse `error_log` files site-wide with a rule in the site's main `.htaccess`. Running WP-CLI with `--skip-themes` keeps the theme from writing one again.

## The longer term

The edits above close what could be found and keep the theme running on current PHP. The theme still gets no fixes from anyone, so a new problem in it would have to be found and patched here. Replacing it with a maintained theme is the only real end to that, and would mean rebuilding the header, banner and layout, which this plugin currently adapts to News Magazine's markup (see [maintenance.md](maintenance.md#changing-themes)).
