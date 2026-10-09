# BXSCC WordPress

The WordPress code behind [bxscc.org](https://bxscc.org), the website of the Baltimore-Xiamen Sister City Committee: the site's own plugin, **BXSCC WordPress Enhancements**, and its theme, **News Magazine 1.1.12** with the committee's fixes. The rest of the site is WordPress itself, plugins installed from WordPress.org, and what is stored in the database (pages, events, menus, widgets, theme settings), none of which is here.

| Folder | What it is | On the server |
|---|---|---|
| `wp-content/plugins/bxscc-wordpress-enhancements/` | The plugin: everything the site does beyond stock WordPress and the theme | the same path |
| `wp-content/themes/news-magazine/` | News Magazine 1.1.12 by Web-Dorado, with the local fixes | the same path |
| `docs/` | How each feature works, and how to maintain the site | not needed |
| `tools/` | One-time scripts that changed the site's content (the events migration, page layouts) | not needed |

Both `wp-content` folders keep their names on the server: the plugin's documentation and scripts use its folder name, and WordPress records the active theme by its folder name.

## Features

### Plugin: BXSCC WordPress Enhancements 1.78

It has no settings screen of its own (besides the reports under Events, Appearance > Site CSS and Tools > Link check). Install it, and these features are on:

| Feature | What it does | Docs |
|---|---|---|
| **Events** | Events are small records (title, dates, time or note, location, description) in their own table. They appear in reusable *sections* on any page, and on automatic yearly archive pages. Dates can be days, months, seasons or years, single or spans. | [docs/events.md](docs/events.md) |
| **Site CSS** | All site styling lives in two files in the plugin, `css/components.css` and `css/theme-fixes.css`, editable from **Appearance > Site CSS** with a saved, comparable, restorable history of every version. Dashboard editing of PHP files is switched off. | [docs/site-css.md](docs/site-css.md) |
| **Banner** | Fixes and improvements to the theme's home page slideshow: random start slide, fast loading in viewing order, each screen downloading a photo size it needs, a placeholder while a photo loads, true crossfades, arrows that never freeze or fight the timer, and uncropped photos in the lightbox, downloaded after everything else. | [docs/banner.md](docs/banner.md) |
| **Photos** | Photo grids for page content, a lightbox for every content photo (grouped, keyboard and swipe navigation, captions), hand-written photo tags served in the size they are shown at, photos that appear whole (with the banner's loading box while one downloads) instead of drawing from the top down, new uploads stored as WebP (the uploaded file kept as the original), and camera data (including locations) taken out of the photo files pages show, without changing a pixel. | [docs/photos.md](docs/photos.md) |
| **Dead links** | Every link to another site is checked weekly. One that fails three weekly checks in a row triggers an email; the report then offers the Internet Archive's copy of the page from around when the link was added (swapped in as pages are built), a new address (written into every page and event that has the old one), or keeping the original. A setting makes the archive swap automatic. Working pages the Archive has never captured are sent to its Save Page Now service. Report and settings: **Tools > Link check**. | [docs/links.md](docs/links.md) |
| **Video previews** | YouTube videos show a thumbnail and load the player (privacy-enhanced mode) only when clicked. | [docs/video-previews.md](docs/video-previews.md) |
| **Header and footer** | Two-line site title, correct social-icon labels (including a Discord icon), current copyright year with a bilingual line. | [docs/header-and-footer.md](docs/header-and-footer.md) |
| **Dark mode** | A moon button in the header switches the site to dark colors; the choice is remembered on that device without cookies, with no flash of light colors. | [docs/dark-mode.md](docs/dark-mode.md) |
| **Layout and navigation** | Sidebar and content end level, parent-page links in the phone menu, year lists by decade and a previous / all / next bar on yearly pages (News and Events, Youth Ambassadors Program), home page tab title, a not-found page in the site's layout. | [docs/layout-and-navigation.md](docs/layout-and-navigation.md) |
| **Page markup** | The table, grid and button-bar patterns page content uses, and the CSS classes that style them. | [docs/page-markup.md](docs/page-markup.md) |
| **Fonts** | Self-hosted Open Sans (no Google Fonts requests), with fallback metrics so text does not jump. | [docs/fonts.md](docs/fonts.md) |
| **News Magazine theme** | The theme's unused public endpoints (a lightbox with a cross-site scripting hole, draft listings, a notice handler that wrote settings for anyone) and its plugin installer are switched off from the plugin, and its layout widths are kept valid. The theme's own file edits are listed below. | [docs/news-magazine-theme.md](docs/news-magazine-theme.md) |
| **Performance and security** | Scripts, styles and the icon font a page does not need are left out of it, jQuery loaded at the end of the page (except where the banner needs it), emoji scripts off, key images and fonts preloaded; XML-RPC off, security headers, no "updates" from WordPress.org for the plugin or the theme, the link checker kept out of the server's own network, and how the plugin's own forms, queries and page rewrite are protected. The two October 2026 audits are summarized there. | [docs/performance-and-security.md](docs/performance-and-security.md) |

### Theme: News Magazine 1.1.12 with local fixes

WordPress.org closed the theme in 2019 and no update will come, so it is kept here in full: it can no longer be installed from the dashboard. The repository's history starts from the theme as released, so `git log -p -- wp-content/themes/news-magazine` shows every change below.

| Change | Files | When |
|---|---|---|
| Runs on PHP 8 without errors or deprecation warnings: the slider and banner widget, an undeclared property, a parameter order | `inc/front/front_params_output.php`, `inc/widgets/widget-advert.php`, `inc/admin/page_slider.php`, `inc/lib/WDWT_input.php` | Aug 2024, Oct 2026 |
| Post-list thumbnails measured from the image file on disk, not fetched over HTTP (no requests from the server to itself or to other sites) | `inc/front/WDWT_front_functions.php` | Oct 2026 |
| The header news ticker queries the database only when it is switched on (three fewer queries per page) | `inc/front/front_params_output.php` | Oct 2026 |
| Never offered an "update" from WordPress.org, which would replace these fixes | `style.css` | Oct 2026 |
| The unused Google web font loader removed (the site's font is self-hosted) | `inc/front/WDWT_front_params_output.php` | Oct 2026 |
| The logo keeps its real size | `style.css` | Aug 2024 |
| The block editor shows pages in the site's styles | `functions.php`, `editor-style.css` | Oct 2026 |

The edits from the October 7 and 8, 2026 review are marked `BXSCC 2026-10` in the files. Reasons and details: [docs/news-magazine-theme.md](docs/news-magazine-theme.md).

## Requirements

- WordPress (in production on 7.1.3) and PHP 8 (in production on 8.3).
- The plugin's banner, header and `css/theme-fixes.css` features patch News Magazine's markup and do nothing useful on another theme. Events, Site CSS, photos, video previews, fonts, page markup styles and the performance settings work on any theme. See [Changing themes](docs/maintenance.md#changing-themes).
- No other plugins are required. If **Nginx Helper** is installed with purging on, saving Site CSS or changing an event or section empties every page's cached copy through it, page by page (block 18).

## Installation

### On a new site

1. Get the files: on GitHub, **Code > Download ZIP** (it leaves out `tools/`), or a clone.
2. Copy `wp-content/plugins/bxscc-wordpress-enhancements/` and `wp-content/themes/news-magazine/` into the site's `wp-content` folder, at the same paths (cPanel File Manager, SFTP, or the terminal).
3. In the dashboard: **Appearance > Themes**, activate **News Magazine**; **Plugins**, activate **BXSCC WordPress Enhancements**. The events table is created the first time WordPress runs with the plugin.

Pages, events, menus, widgets and the theme's settings (Appearance > Customize) are in the database: moving the site means moving its database too (a backup plugin such as UpdraftPlus does both).

### Updating bxscc.org from this repository

In the cPanel terminal, after uploading the ZIP from GitHub to the home folder (GitHub names it after the repository and branch, for example `bxscc-wordpress-main.zip`; use its real name below):

1. Unpack it in the home folder:
   ```
   cd ~ && unzip -q bxscc-wordpress-main.zip
   ```
2. Check for Site CSS edits made in the dashboard. Appearance > Site CSS saves into the plugin's `css/` files on the server, so copying the repository's files over them would replace those edits:
   ```
   md5sum ~/public_html/wp-content/plugins/bxscc-wordpress-enhancements/css/*.css ~/bxscc-wordpress-main/wp-content/plugins/bxscc-wordpress-enhancements/css/*.css
   ```
   If a pair differs and the repository has no change to that file, someone saved Site CSS since the last update: copy that change into the repository first. (Appearance > Site CSS > History keeps every saved version, so an overwritten edit can still be restored.)
3. Back up the two folders as they are now:
   ```
   tar -czf ~/bx-before-update-$(date +%F).tgz -C ~/public_html/wp-content plugins/bxscc-wordpress-enhancements themes/news-magazine
   ```
4. Copy the new files over the old ones. This adds and replaces files only: the server's `css/history/` stays, and a file removed from the repository has to be deleted by hand.
   ```
   cp -R ~/bxscc-wordpress-main/wp-content/. ~/public_html/wp-content/
   ```
5. Check that no file can be changed by other accounts on the server (`-perm /022` finds files writable by the group or by others). It should print nothing; run `chmod go-w` on anything it lists.
   ```
   find ~/public_html/wp-content/plugins/bxscc-wordpress-enhancements ~/public_html/wp-content/themes/news-magazine -perm /022
   ```
6. Wait 95 seconds, then empty the page cache. The server's PHP looks for changed files only every 90 seconds, and pages cached in that window would keep the old code's output.
   ```
   sleep 95; cd ~/public_html && wp eval 'echo bx_purge_all_pages(), " pages emptied\n";' --skip-themes --skip-plugins=wordfence,updraftplus,shortcodes-ultimate
   ```
7. If theme files changed, Wordfence reports them as modified: check that they are the intended changes and mark each "Ignore until file changes". Do not let it "Repair" them, which puts back the original theme files.
8. Delete the unpacked copy and the ZIP:
   ```
   rm -r ~/bxscc-wordpress-main ~/bxscc-wordpress-main.zip
   ```

More on updating, the page cache and troubleshooting: [docs/maintenance.md](docs/maintenance.md).

## Reference

### Files

```
wp-content/plugins/bxscc-wordpress-enhancements/
  bxscc-wordpress-enhancements.php   the plugin: every feature except Site CSS editing and Events, in numbered blocks
  css-editor.php                     Appearance > Site CSS (dashboard only)
  css/components.css                 styling for the site's own markup; theme-independent
  css/theme-fixes.css                patches for the News Magazine theme
  css/history/                       created on the server by Site CSS: every saved version (not in this repository)
  events/events.php                  events storage, date wording, sections, shortcodes, yearly pages
  events/admin.php                   the Events dashboard menu
  links/links.php                    the weekly dead-link check, warnings, the archive swap, replacing addresses,
                                     Save Page Now, Tools > Link check, and wp bx-links
  templates/404.php                  the not-found page, in the site's layout
  fonts/                             Open Sans woff2 files and their license (SIL OFL 1.1)
wp-content/themes/news-magazine/     News Magazine 1.1.12 with the local fixes
docs/                                feature documentation (file paths in it are relative to the plugin folder)
tools/migration/                     one-time scripts that moved the old hand-written event pages into the events table
tools/ya-years.php                   one-time script that put the year list on the Youth Ambassadors Program page
tools/photo-loading.php              one-time script that fixed the loading order of four photos (October 2026 audit)
tools/ya-2018.php ... ya-2025.php    one-time scripts that put the new layout on the 2018, 2019, 2024 and 2025
                                     Youth Ambassadors pages (run 2019 to 2025 with --use-include; see the note in each)
tools/ya-program.php                 one-time script that put the new layout on the Youth Ambassadors Program page
tools/photo-metadata.php             one-time script that takes the camera data out of the photos already in the
                                     Media Library, with a backup (October 2026 audit; see docs/photos.md)
tools/img-max-width.php              one-time script that keeps photos with a pixel max-width inside the column on
                                     narrow phones (October 2026 cross-browser test)
tools/https-check.php                read-only report: which http:// links have a working https:// version
                                     (writes ~/https-check.html with links to click)
tools/donate-button.php              one-time script that serves the PayPal donate button's picture from the Media
                                     Library and blanks PayPal's tracking pixel (October 2026 audit)
```

### Data the plugin stores

| Where | What |
|---|---|
| Table `{prefix}bx_events` | One row per event. |
| Option `bx_ev_sections` | The sections (heading, rule, hand-picked events, empty message). |
| Options `bx_ev_db_version`, `bx_ev_years_checked` | Table version; the last day the yearly-page check ran. |
| Option `bx_ev_year_lock_YYYY` | Exists only for the moment a yearly page is being made. |
| Options `bx_links`, `bx_links_map`, `bx_links_queue`, `bx_links_run` | Every outside link with its check history, choice and Internet Archive status; the dead links swapped for archived copies; the links left in the current run; when the last run started and finished. |
| Options `bx_links_settings`, `bx_links_pending`, `bx_links_log` | Warn or swap automatically, and where warnings go; how many dead links wait for a choice; the last 50 replacements with a new address. |
| Option `bx_links_ia_keys` | The archive.org keys entered on Tools > Link check, encrypted (see [docs/links.md](docs/links.md)); not autoloaded. |
| Transient `bx_links_lock` | Exists only while a batch of link checks runs. |
| Scheduled events `bx_links_weekly`, `bx_links_batch` | The weekly start of the link check, and its follow-on batches. |
| Transient `bx_css_err_<user id>` | Why a Site CSS save was refused, kept for one minute to show on the next screen. |
| Posts with slugs `news-and-media-YYYY` | The yearly archive pages (ordinary posts, created automatically). |
| Folder `css/history/` | Site CSS version history. |
| Post meta `_bx_donate_button` | Marks the donate button's picture in the Media Library (set by `tools/donate-button.php`, so a second run finds it). |
| Browser `localStorage` key `bx-theme` | A visitor's dark-mode choice, on their own device. |

Deactivating the plugin deletes nothing. Event lists then show as plain shortcodes and the site falls back to the theme's own look.

## License

- The plugin, `docs/` and `tools/`: GPL version 2 or later ([LICENSE](LICENSE)), the license WordPress itself uses, which plugins built on its APIs inherit.
- The theme: News Magazine, copyright 2017 web-dorado.com, GPL version 3 or later ([LICENSE-THEME](LICENSE-THEME); the theme's own notes are in its `license.txt` and `readme.txt`).
- Open Sans: SIL Open Font License 1.1 ([fonts/OFL.txt](wp-content/plugins/bxscc-wordpress-enhancements/fonts/OFL.txt)).
