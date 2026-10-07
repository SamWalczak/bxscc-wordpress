# Dead links

Code: `links/links.php` (block 20 of `bxscc-wordpress-enhancements.php` loads it; block 3d does the swap). Report: **Tools > Link check**. Works with any theme.

Every link from this site to another site is checked once a week. A link that fails three weekly checks in a row counts as dead, and visitors get the Internet Archive's copy of that page instead. Nothing in the database is edited: the link is swapped while each page is built, so a site that comes back gets its own link back after one good check, and the report can undo any swap.

## Why not a link-checker plugin

Most of the site's outside links (about two thirds, October 2026) are in event descriptions, which live in the plugin's own events table and reach pages through shortcodes. The link plugins scan post content (and custom fields), so they would miss them:

- **Internet Archive Wayback Machine Link Fixer** (the Archive's own plugin, 1.4.2): scans post content on save; swaps links in the visitor's browser through a REST call on each page view (uncached PHP work on every visit here, and search engines still see the dead link); young (about 1,000 sites), with reports of false alarms and of a site made unusable.
- **Broken Link Checker** (WPMU DEV): its cloud engine needs a WPMU DEV account; the local engine scans posts, comments and custom fields; it reports dead links but does not swap in archived copies.

## Where links are found

Published pages and posts, every event (title, time or note, location, description), text and HTML widgets placed in a sidebar, and menus. Links to this site and to the Internet Archive (or archive.today) are skipped.

## What counts as a failed check

The checker asks for the page the way a browser does (following up to five redirects, waiting up to 12 seconds, reading at most the first 64 KB) and counts as a failure:

- no answer: address not found, connection refused, timed out, a broken HTTPS certificate, a redirect loop;
- 404, 410, any other 4xx except those below, any 5xx;
- a page that now sends visitors to its site's home page (the usual sign of a deleted article);
- a domain that now belongs to a parking or domain-sale service.

**Could not check:** 401, 403, 429, 451 and 999 mean the site refuses automated visitors, so whether the page exists is unknown. These links are listed separately and never swapped. Links to private network addresses are not requested at all.

Three failures in a row, a week or more apart, are needed (the rule Wikipedia and the Internet Archive use), so a site that is down for a day is never swapped. A blocked answer neither adds to nor resets the count. One good check resets it, and if the link was swapped, the swap ends.

## Which archived copy

The Wayback Machine's index is asked for the first good (HTTP 200) capture from the year the link went on the site (the event's date, or the post's date), and if there is none, the last good capture; either way, never one from after the link started failing. So an event from 2012 links to the page as it was in 2012. A link with no capture keeps its original address and is listed as "the Internet Archive has no copy". If the Archive does not answer, it is asked again on the next run.

## When it runs

WordPress's scheduler starts a run once a week (`bx_links_weekly`, first one an hour after the plugin is installed). The run checks links in batches of about 20 seconds, each its own background request, 30 seconds apart, so it never holds up a visitor or hits the server's time limit; one request a second at most to any one site, and about one lookup a second to the Archive. At the end, if any swap started or ended, every page's cached copy is emptied (block 18) so pages are rebuilt with the change.

WordPress's scheduler runs when someone loads a page that is not served from the cache, which on this site happens many times a day (dashboard visits, searches, missing pages). If `DISABLE_WP_CRON` is set in `wp-config.php`, a real cron job has to run `wp cron event run --due-now`.

## The report

**Tools > Link check** (administrators): when the last run started and finished, the next run, a **Run the check now** button (it checks only links not checked in the last six days, so it cannot rush a link to "dead"), and three lists:

- **Dead:** each link, where it is (with links to edit those pages or events), when it started failing, the last result, and what visitors get. **Keep original link** stops the swap for that link for good (for a page the checker gets wrong); **Use the archived copy** restores it.
- **Failing, not dead yet:** links that failed one or two checks in a row.
- **Could not check.**

## Command line

```
wp bx-links run                    # a whole run now, in the terminal (same six-day rule)
wp bx-links list --status=dead     # or failing, blocked, ok, new
```
