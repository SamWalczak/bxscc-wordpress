# Layout and navigation

Code: blocks 3b, 3e, 8 and 9 of `bxscc-wordpress-enhancements.php`; layout rules in `css/theme-fixes.css`.

## Sidebar and content end level (desktop)

On wide screens (768px and up), if the content column is shorter than the sidebar, the last cell of the last table in the content gets extra bottom padding so both columns end on the same line. It recalculates when the page finishes loading and when the window width changes (not on height-only changes, which iOS fires when its toolbar shows or hides). On narrower screens the sidebar sits below the content and nothing is padded.

Related CSS in `theme-fixes.css`: on desktop the sidebar is one window tall with its badge at the bottom, and on tablets and phones the content is placed above the sidebar in CSS, so the theme's script that used to move it has nothing to do.

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

## Page titles

`theme-fixes.css` hides the theme's "Home" title on the home page (keeping its space) and styles the yearly posts' titles like the section headings.
