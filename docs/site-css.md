# Site CSS

Code: block 11 and 12 of `bxscc-wordpress-enhancements.php`, and `css-editor.php`. Stylesheets: `css/components.css`, `css/theme-fixes.css`.

## Where the styling lives

All of the site's custom styling is in two files inside the plugin. Nothing goes in **Appearance > Customize > Additional CSS**, which stays empty.

| File | Holds | Depends on the theme? |
|---|---|---|
| `css/components.css` | Styling for markup the site itself writes: content tables, event tables and sections, photo grids, video boxes, the year bar, sub-page button bars, the Leadership and speaker tables, the dark-mode button, and the color variables used everywhere. Content is matched as `.entry` (News Magazine) or `.entry-content` (most other themes). | No |
| `css/theme-fixes.css` | Patches for News Magazine's own markup: header, menu, sidebar, banner, footer, page titles, phone layout, and dark-mode overrides for theme elements. | Yes: empty it if the site changes themes. |

Both files are printed inline at the end of `<head>` (`wp_head`, priority 102), in that order, which is where Additional CSS used to print. Their rules therefore win over the theme's stylesheets exactly as before. Inline printing also means no extra file requests.

The colors are CSS variables at the top of `components.css`: `:root` for light mode, `html[data-theme="dark"]` for dark mode. Changing a brand color there changes it everywhere.

Why files in the plugin instead of Additional CSS: Additional CSS is stored in the database, tied to the active theme, has no history beyond WordPress revisions, and mixes theme patches with styling the site's own content needs. Files can be backed up, compared and versioned (this repository), and the split makes a theme change a matter of emptying one file.

## Editing from the dashboard: Appearance > Site CSS

For Administrators (capability `edit_theme_options`, constant `BX_CSS_CAP` in `css-editor.php`; change it to `edit_pages` to let Editors in).

- One tab per file, each with a short description of what belongs in it.
- A code editor (WordPress's built-in CodeMirror) with syntax highlighting and line wrapping.
- **What did you change?** is required. The note is shown in the history.
- **Checks before saving.** A save is refused, with the reason, if the CSS has an unclosed comment, an unbalanced `{` or `}` (braces inside quoted strings are ignored, and the error gives the line), or the text `</style` or `<script`. Those are the mistakes that would break every page, not only one rule.
- **History.** Every save is kept as a file in `css/history/`, named `<file>--<YYYYmmdd-HHMMSS>--<user>.css`, with the note as its first line. The 100 most recent versions per file are kept (`BX_CSS_KEEP`). The history table shows when, who and the note for each version.
- **Compare / restore.** Any earlier version opens side by side with the current file (WordPress's own diff view), with a **Restore this version** button. Restoring is itself a new history entry ("Restored the version from …"), so it can be undone the same way.
- **Edits made outside the editor are kept.** If a file was changed by upload or in cPanel since the last save, the editor says so, and the next save first files that outside version in the history ("outside"), so nothing is lost.
- **First visit.** Opening Site CSS for the first time records each file's current contents as a "Starting version", so there is always something to go back to.
- **Cache.** After a save the editor asks Nginx Helper (if installed) to empty the page cache (`rt_nginx_helper_purge_all`). If the host's cache is not reachable that way, purge it by hand; see [maintenance.md](maintenance.md#page-cache).

The CSS is sent from the browser base64-encoded. Some hosts' firewalls (ModSecurity rules) reject form posts that contain CSS or markup-like text, which made plain saves fail with HTTP 406.

## PHP cannot be edited from the dashboard

Block 12 turns off WordPress's Plugin File Editor and Theme File Editor for every user, Administrators included (the `edit_plugins` and `edit_themes` capabilities always resolve to `do_not_allow`). A typo in PHP stops the whole site, so PHP changes go through file upload (cPanel or SFTP) where they can be checked first, while CSS, which can only spoil styling, stays editable with a safety net.
