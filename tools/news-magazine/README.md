# News Magazine theme patch

`news-magazine-bxscc-2026-10.patch` is the set of four edits made to the News Magazine 1.1.12 theme in October 2026. The reasons are in [docs/news-magazine-theme.md](../../docs/news-magazine-theme.md). The theme is not part of this repository; these are the only changes made to it besides the four older local edits recorded in the site's notes.

To apply it to a stock copy of the theme, from `wp-content/themes/`:

```
patch -p1 --dry-run < news-magazine-bxscc-2026-10.patch
patch -p1 < news-magazine-bxscc-2026-10.patch
```

`inc/front/WDWT_front_functions.php` uses Windows line endings (CR LF); the patch keeps them.
