# BXSCC WordPress Enhancements

The WordPress plugin behind [bxscc.org](https://bxscc.org), the website of the Baltimore-Xiamen Sister City Committee. It holds everything the site does beyond stock WordPress and its theme (News Magazine 1.1.12), so the site can be maintained from one place and handed from one volunteer to the next.

It has no settings screen of its own. Install it, and these features are on:

| Feature | What it does | Docs |
|---|---|---|
| **Events** | Events are small records (title, dates, time or note, location, description) in their own table. They appear in reusable *sections* on any page, and on automatic yearly archive pages. Dates can be days, months, seasons or years, single or spans. | [docs/events.md](docs/events.md) |
| **Site CSS** | All site styling lives in two files in the plugin, `css/components.css` and `css/theme-fixes.css`, editable from **Appearance > Site CSS** with a saved, comparable, restorable history of every version. Dashboard editing of PHP files is switched off. | [docs/site-css.md](docs/site-css.md) |
| **Banner** | Fixes and improvements to the theme's home page slideshow: random start slide, fast loading in viewing order, each screen downloading a photo size it needs, a placeholder while a photo loads, true crossfades, arrows that never freeze or fight the timer, and uncropped photos in the lightbox, downloaded after everything else. | [docs/banner.md](docs/banner.md) |
| **Photos** | Photo grids for page content, a lightbox for every content photo (grouped, keyboard and swipe navigation, captions), and hand-written photo tags served in the size they are shown at. | [docs/photos.md](docs/photos.md) |
| **Video previews** | YouTube videos show a thumbnail and load the player (privacy-enhanced mode) only when clicked. | [docs/video-previews.md](docs/video-previews.md) |
| **Header and footer** | Two-line site title, correct social-icon labels (including a Discord icon), current copyright year with a bilingual line. | [docs/header-and-footer.md](docs/header-and-footer.md) |
| **Dark mode** | A moon button in the header switches the site to dark colors; the choice is remembered on that device without cookies, with no flash of light colors. | [docs/dark-mode.md](docs/dark-mode.md) |
| **Layout and navigation** | Sidebar and content end level, parent-page links in the phone menu, previous / all / next bar on yearly pages, home page tab title, a not-found page in the site's layout. | [docs/layout-and-navigation.md](docs/layout-and-navigation.md) |
| **Page markup** | The table, grid and button-bar patterns page content uses, and the CSS classes that style them. | [docs/page-markup.md](docs/page-markup.md) |
| **Fonts** | Self-hosted Open Sans (no Google Fonts requests), with fallback metrics so text does not jump. | [docs/fonts.md](docs/fonts.md) |
| **News Magazine theme** | The theme has had no updates since 2019. Its unused public endpoints (a lightbox with a cross-site scripting hole, draft listings, a notice handler that wrote settings for anyone) and its plugin installer are switched off, and its layout widths are kept valid; four small theme-file edits are in `tools/news-magazine/`. | [docs/news-magazine-theme.md](docs/news-magazine-theme.md) |
| **Performance and security** | Unused scripts, styles and the icon font not loaded, emoji scripts off, key images and fonts preloaded; XML-RPC off, and how the plugin's own forms, queries and page rewrite are protected. | [docs/performance-and-security.md](docs/performance-and-security.md) |

Maintenance (installing, updating, page caching, troubleshooting, changing themes): [docs/maintenance.md](docs/maintenance.md).

## Requirements

- WordPress (in production on 7.1.3).
- The **News Magazine** theme by Web-Dorado, version 1.1.12. The banner, header and `css/theme-fixes.css` features patch that theme's markup and do nothing useful on another theme. Events, Site CSS, photos, video previews, fonts, page markup styles and the performance settings work on any theme. See [Changing themes](docs/maintenance.md#changing-themes).
- No other plugins are required. If **Nginx Helper** is installed with purging on, saving Site CSS or changing an event or section empties every page's cached copy through it, page by page (block 18).

## Installing

The plugin folder must be named `bxscc-wordpress-enhancements` (the events migration scripts and the documentation use that path).

1. Copy this repository's contents into `wp-content/plugins/bxscc-wordpress-enhancements/` (cPanel File Manager, SFTP, or a clone). The `tools/` folder is not needed on the server, and GitHub's **Download ZIP** leaves it out.
2. Activate **BXSCC WordPress Enhancements** under **Plugins**.
3. That is all: the events table is created automatically the first time WordPress runs with the plugin.

## Files

```
bxscc-wordpress-enhancements.php  the plugin: every feature except Site CSS editing and Events, in numbered blocks
css-editor.php                    Appearance > Site CSS (dashboard only)
css/components.css                styling for the site's own markup; theme-independent
css/theme-fixes.css               patches for the News Magazine theme
css/history/                      created by Site CSS: every saved version (not in this repository)
events/events.php                 events storage, date wording, sections, shortcodes, yearly pages
events/admin.php                  the Events dashboard menu
fonts/                            Open Sans woff2 files and their license (SIL OFL 1.1)
docs/                             feature documentation
tools/migration/                  one-time scripts used to move the old hand-written event pages into the events table
tools/news-magazine/              the patch applied to the News Magazine theme's own files (not part of the plugin)
```

## Data the plugin stores

| Where | What |
|---|---|
| Table `{prefix}bx_events` | One row per event. |
| Option `bx_ev_sections` | The sections (heading, rule, hand-picked events, empty message). |
| Options `bx_ev_db_version`, `bx_ev_years_checked` | Table version; the last day the yearly-page check ran. |
| Option `bx_ev_year_lock_YYYY` | Exists only for the moment a yearly page is being made. |
| Transient `bx_css_err_<user id>` | Why a Site CSS save was refused, kept for one minute to show on the next screen. |
| Posts with slugs `news-and-media-YYYY` | The yearly archive pages (ordinary posts, created automatically). |
| Folder `css/history/` | Site CSS version history. |
| Browser `localStorage` key `bx-theme` | A visitor's dark-mode choice, on their own device. |

Deactivating the plugin deletes nothing. Event lists then show as plain shortcodes and the site falls back to the theme's own look.

## License

Plugin code: GPL version 2 or later (see [LICENSE](LICENSE)), the license WordPress itself uses, which plugins built on WordPress's APIs inherit.
Open Sans: SIL Open Font License 1.1 (see [fonts/OFL.txt](fonts/OFL.txt)).
