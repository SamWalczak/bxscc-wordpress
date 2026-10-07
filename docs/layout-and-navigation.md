# Layout and navigation

Code: blocks 3b, 3e, 8, 9 and 19 of `bxscc-wordpress-enhancements.php`, and `templates/404.php`; layout rules in `css/theme-fixes.css`.

## Sidebar and content end level (desktop)

When the sidebar is beside the content (screens wider than 1024px), if the content column is shorter than the sidebar, the last cell of the last table in the content gets extra bottom padding so both columns end on the same line. It recalculates when the page finishes loading and when the window width changes (not on height-only changes, which iOS fires when its toolbar shows or hides). At 1024px and narrower the sidebar sits below the content and nothing is padded. (Until 1.51 the padding stopped only below 768px, so tablets from 768 to 1024px got a window-tall blank space at the end of every page.)

Related CSS in `theme-fixes.css`: in the side-by-side layout the sidebar is one window tall with its badge at the bottom (also limited to wider than 1024px since 1.51), and on tablets and phones the content is placed above the sidebar in CSS, so the theme's script that used to move it has nothing to do.

## Phone menu: links to the parent pages

In the phone menu, tapping a parent item such as "About Us" only opens its dropdown, so the parent page itself could not be reached. Each dropdown therefore gets a first item linking to the parent page:

| Parent | First item |
|---|---|
| About Us | About the Organization |
| Programs | Overview of Programs |
| any other | "(name) overview" |

The theme injects its own copy of the parent link late, for some parents only; when it appears, the plugin relabels it instead of adding a second one, so there is never a duplicate. It reacts to the menu's changes as they happen, whatever event the theme used to open it. The labels are set in the `labels` object at the top of block 3e.

## Previous / all / next bar on yearly pages

At the bottom of each yearly News and Events post, a bar links to the previous year, to News and Events, and to the next year. A year with no page on one side gets an empty segment, so the middle link stays centered. The bar is styled as three connected segments (`.bx-year-nav` in `components.css`).

A post counts as a yearly page when its title reads "News and Events" or "News and Media" followed by a four-digit year.

## Home page tab title

The browser tab on the home page shows only the site name. WordPress adds the tagline by default, which here repeats the name. Other pages keep the usual "Page title – Site name".

## Not-found page

The theme's own not-found page put its text straight under the menu, out of line with the sidebar (its template skips the wrappers every page uses), and offered a search box. Block 19 replaces it (with News Magazine only) with `templates/404.php`, which uses the theme's page layout: a "Page Not Found" heading styled like the event section headings ("Upcoming Events"), the navy line those sections have under their heading (gold in dark mode), and below it, centered, 抱歉 ("sorry", the usual polite apology on Chinese websites) and "That page was not found. Please navigate using the menu above to find out more about the BXSCC." No search form. The address still answers 404, so search engines drop it, and WordPress itself still redirects a mistyped or moved address when it finds exactly one page with that name (for example `/history/` to `/about-us/history/`) before this page is ever shown.

## Page titles

`theme-fixes.css` hides the theme's "Home" title on the home page (keeping its space) and styles the yearly posts' titles like the section headings.
