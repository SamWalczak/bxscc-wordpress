# Layout and navigation

Code: blocks 3b, 3e, 8, 9 and 19 of `bxscc-wordpress-enhancements.php`, and `templates/404.php`; layout rules in `css/theme-fixes.css`.

## Sidebar and content end level (desktop)

When the sidebar is beside the content (screens wider than 1024px), if the content column is shorter than the sidebar, the last cell of the last table in the content gets extra bottom padding so both columns end on the same line. It recalculates when the page finishes loading and when the window width changes (not on height-only changes, which iOS fires when its toolbar shows or hides). At 1024px and narrower the sidebar sits below the content and nothing is padded. (Until 1.51 the padding stopped only below 768px, so tablets from 768 to 1024px got a window-tall blank space at the end of every page.)

Related CSS in `theme-fixes.css`: in the side-by-side layout the sidebar is one window tall with its badge at the bottom (also limited to wider than 1024px since 1.51), and on tablets and phones the content is placed above the sidebar in CSS, so the theme's script that used to move it has nothing to do.

## Sidebar donate button

The PayPal button in the sidebar widget (`custom_html-2`) gets the same room as the one on the Youth Ambassadors Program page (1.77): 22px between the text and the button, the picture centered as a block with PayPal's 1-pixel image hidden (otherwise the inline picture leaves a line of slack under itself), and 21px from the button to the next widget's line, on phones too (`theme-fixes.css`, sidebar section). The program page's own button is written in the page with the same margins.

## Sidebar on tablets and phones while a page loads

From 1024px down the sidebar is shown under the content, but the theme writes it before the content in the page. On a slow connection a browser could paint the donate box and Hot Links as if they were the page, then push them down when the content arrived (found in the October 2026 cross-browser test by pausing a page's delivery just before the content). Since 1.78 the stacked sidebar stays invisible, its space unchanged, until the page is marked `bx-parsed`: an inline script printed at the start of the footer (block 1b), so right after the content and before the footer's scripts load, with `DOMContentLoaded` as the fallback (block 1). Rule in `theme-fixes.css`, inside the stacked-layout media query, so desktop and laptop widths (1025px and up) are untouched; without scripts the sidebar shows as before.

## Phone menu: links to the parent pages

In the phone menu, tapping a parent item such as "About Us" only opens its dropdown, so the parent page itself could not be reached. Each dropdown therefore gets a first item linking to the parent page:

| Parent | First item |
|---|---|
| About Us | About the Organization |
| Programs | Overview of Programs |
| any other | "(name) overview" |

The theme injects its own copy of the parent link late, for some parents only; when it appears, the plugin relabels it instead of adding a second one, so there is never a duplicate. It reacts to the menu's changes as they happen, whatever event the theme used to open it. The labels are set in the `labels` object at the top of block 3e.

## Pages by year: lists by decade, previous / all / next bar

Two sets of pages have one page per year, and both work the same way: a list of the years, one row per decade with the newest decade first (`bx_year_decades`, styled by `.bx-years` in `components.css`; the decade labels are hidden below 700px wide), and at the bottom of each year's page a bar with the previous year, a link to the list, and the next year. A year with nothing on one side gets an empty segment, so the middle link stays centered. The bar is three connected segments (`bx_year_bar`, `.bx-year-nav` in `components.css`).

| | News and Events | Youth Ambassadors Program |
|---|---|---|
| A year's page | a post whose title reads "News and Events" or "News and Media" followed by a four-digit year (the list uses the slug `news-and-media-YYYY`) | a published page with the slug `YYYY-bxscc-youth-ambassadors-program` (any parent; today they are under News and Events) |
| The list | `[bx_years]` under the heading "Archives by Year" on News and Events | `[bx_ya_years]` under the heading "Profiles of Past Programs" at the bottom of the program page |
| The bar's sides | the year just before and just after | the nearest program year before and after (2019 links to 2024: there were no programs from 2020 to 2023) |
| The middle link | All News and Events, to News and Events | Program Overview, to the program page |

**A new program year** needs only its page: publish it with the slug `YYYY-bxscc-youth-ambassadors-program` and it appears in the list and in its neighbors' bars. Publishing, unpublishing, trashing or renaming such a page empties every cached page (block 9; Nginx Helper alone would empty only the page that was saved).

Both lists are written the same way in the page content, a section with a centered heading and the shortcode:

```
<div class="bx-section">
<h1 style="text-align: center;"><strong>Profiles of Past Programs</strong></h1>
[bx_ya_years]
</div>
```

On the program page the section follows a paragraph; `p + .bx-section` in `components.css` puts it 24px below, the same distance Archives by Year has from the section above it. The program page's section replaced a heading, four hand-written links and the "- - -" lines around them with version 1.67 (`tools/ya-years.php`, run once on the live site; it checks that the list will show exactly the years that were linked).

## Menu bar on one row

From 768px wide up the menu is a bar of links. The theme floated them, so when they did not fit, the last ones dropped onto a second row (with the current six links: at every width from 768 to 1082px). `theme-fixes.css` now lays them out in a single row that cannot wrap, and block 3h makes the top-level link text smaller, only as much as needed, until the row fits the bar: 17.7px instead of 18px on tablets (768 to 1024px), 18.4px instead of 20px at 1025px, back to 20px from 1083px. The space around each link and the dropdown links keep their size. Adding or renaming menu items needs no change here; the script measures whatever the menu holds. There is no lower limit, so many more items would mean much smaller text.

The script is printed right after the menu (through the `wp_nav_menu` filter, for the theme's `primary-menu` location only), so it runs before the browser first draws the bar; it runs again whenever the bar's width changes (a `ResizeObserver`) and when the web font finishes loading. Without JavaScript the row runs past the right end of the bar instead of making a second row.

## Desktop dropdown menus

On screens wider than 1024px a dropdown opens while its menu item is hovered or holds keyboard focus (`theme-fixes.css`). It unrolls from the top over 0.2 seconds and fades out over 0.1 seconds when the pointer leaves; visitors whose system asks for reduced motion get no animation. The theme opened and closed them with jQuery slide animations that changed the dropdown's height; with the pointer resting near a dropdown's moving edge, each step of the animation moved the edge across the pointer and started the opposite animation, so the menu flickered until the pointer moved (for example after moving quickly from About Us to just below the middle of History). Block 3f removes the theme's hover handlers; the tap-to-open menu on tablets and phones is a separate handler and is unchanged. Keyboard users now get the dropdowns too.

The unroll keeps the dropdown at its full size from the first frame and animates a CSS mask over it, so the area where the pointer counts as over the dropdown never moves (masks do not affect that in Chrome or Safari). Firefox may count only the drawn part; there, a closing dropdown ignores the pointer (`pointer-events: none`), so it cannot reopen itself under a resting pointer, and at worst a very fast move to the bottom of a dropdown in its first 0.2 seconds closes it.

## Home page tab title

The browser tab on the home page shows only the site name. WordPress adds the tagline by default, which here repeats the name. Other pages keep the usual "Page title – Site name".

## Not-found page

The theme's own not-found page put its text straight under the menu, out of line with the sidebar (its template skips the wrappers every page uses), and offered a search box. Block 19 replaces it (with News Magazine only) with `templates/404.php`, which uses the theme's page layout: an "Error Code 404" heading styled like the event section headings ("Upcoming Events"), the navy line those sections have under their heading (gold in dark mode), and below it one centered line: "抱歉！That page was not found. Please navigate using the menu above to find out more about the BXSCC." (抱歉, bàoqiàn, "sorry", is meant playfully; the full-width Chinese exclamation mark carries its own spacing, so no space follows it.) No search form. The address still answers 404, so search engines drop it, and WordPress itself still redirects a mistyped or moved address when it finds exactly one page with that name (for example `/history/` to `/about-us/history/`) before this page is ever shown.

## Page titles

`theme-fixes.css` hides the theme's "Home" title on the home page (keeping its space) and styles the yearly posts' titles like the section headings.
