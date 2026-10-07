# Maintenance

## Updating the plugin

PHP can only be changed by uploading files (the dashboard editors are off; see [site-css.md](site-css.md#php-cannot-be-edited-from-the-dashboard)). Upload in cPanel File Manager or over SFTP, overwriting the file.

- **Order matters when a new file is required.** `bxscc-wordpress-enhancements.php` loads `events/events.php` and, in the dashboard, `css-editor.php`. Upload new or renamed required files first and the main file last; a main file that requires a missing file stops every page with a fatal error.
- **Check what arrived.** Compare the uploaded file's checksum with the copy here: `md5sum bxscc-wordpress-enhancements.php` on the server against the same command locally. Some upload tools drop the file's final newline, which changes the checksum; `head -c -1 file | md5sum` gives the checksum without it.
- **Keep the old copy** (download it first) so a bad upload can be reversed in a minute.

The version number is in the plugin header (`Version:`) at the top of `bxscc-wordpress-enhancements.php`.

## Page cache

bxscc.org sits behind an Nginx full-page cache. Visitors get a saved copy of each page; a change appears only after that copy is purged or expires. The response header `x-proxy-cache` says which: `HIT` (saved copy) or `MISS` (built fresh).

- **One page**: request its address with `/purge` in front of the path, for example `https://example.org/purge/news-and-events/`. `200` means the saved copy was removed, `404` that there was none.
- **Saving a post or page**: Nginx Helper empties that page (and the home page and archives, per its settings) the same way. This works on bxscc.org.
- **Site CSS, events and sections**: this plugin empties every page of the site the same way, page by page, after a save (block 18). It runs at the end of the request, after the browser has its answer where the server allows it.
- **What does not work on bxscc.org** (tested October 7, 2026): Nginx Helper's "Purge Entire Cache" (it deletes files in a cache folder on the same machine, `/var/run/nginx-cache`, which a shared hosting account does not have), cPanel > Cache Manager's purge all, and a wildcard `/purge/*` request (`404`). To empty everything by hand, use the plugin's own page-by-page purge from the site folder (it prints how many pages it emptied; 0 means Nginx Helper is not active or its purging is off):

```
wp eval 'echo bx_purge_all_pages(), " pages emptied\n";' --skip-themes --skip-plugins=wordfence,updraftplus,shortcodes-ultimate
```

Checking from a terminal: some host firewalls reject `curl`'s default User-Agent (HTTP 406), so send a browser-like one: `curl -s -A "Mozilla/5.0" -D - -o /dev/null https://example.org/ | grep -i x-proxy-cache`.

## WP-CLI

Run WP-CLI with `--skip-themes --skip-plugins`. The theme prints PHP warnings in the terminal otherwise, and the events migration scripts load the events code themselves.

## Changing themes

1. Empty `css/theme-fixes.css` (Appearance > Site CSS > Theme fixes), which targets News Magazine's markup only.
2. The banner (block 3c and the slideshow part of 3d) and the header and footer rewrites (3d) look for News Magazine's markup and do nothing if it is not there. Remove those blocks for tidiness.
3. Everything else keeps working: events, sections and yearly pages, Site CSS, `components.css` (content is matched as `.entry` or `.entry-content`), photos, video previews, fonts, dark mode (the button needs a new place in the header), and the performance settings.

## Troubleshooting

| Symptom | Check |
|---|---|
| Every page shows "There has been a critical error" | A required file is missing or a PHP file is broken. Restore the previous `bxscc-wordpress-enhancements.php`, or make sure `events/` and `css-editor.php` are present. WordPress also emails the site admin. |
| Event lists show as `[bx_section …]` text | The plugin is inactive, or `events/events.php` is missing. |
| A section is missing from a page | Events > Sections: the code in the page must match a section's code name, and a section with no events and no empty message hides itself. |
| A change does not show up | The page cache; see above. |
| Site CSS save refused | The message names the problem (unbalanced brace with its line, unclosed comment, forbidden text). |
| Site styling gone | `css/components.css` and `css/theme-fixes.css` must be in the plugin's `css/` folder; Appearance > Site CSS > History can restore a version. |
