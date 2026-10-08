# Dead links

Code: `links/links.php` (block 20 of `bxscc-wordpress-enhancements.php` loads it; block 3d does the swap). Report and settings: **Tools > Link check**. Works with any theme.

Every link from this site to another site is checked once a week. A link that fails three weekly checks in a row counts as dead. What happens then is a setting:

- **Email a warning and wait for a choice** (the default). One email per run lists the links that died since the last warning: where each one is, the last result, and the Internet Archive's copy if it has one, with a link to the report. Nothing changes for visitors until someone chooses. The menu shows how many are waiting (**Tools > Link check 3**).
- **Use the Internet Archive's copy automatically.** Visitors get the copy straight away; the report can still undo it or replace the address.

The choices on the report, for each dead link:

- **Use the archived copy.** Visitors get the copy. Pages and events are not edited: the link is swapped while each page is built, so if the site comes back (one good check), its own link returns by itself.
- **Replace everywhere** with a new address typed in. The new address is written into every page, post, event, sidebar widget and menu item that has the old one (posts keep a revision of the old text; a link whose words are its own address shows the new address). The new address is checked from the next run on. Each replacement is listed under **Replaced with a new address** (when, old, new, how many places, by whom).
- **Keep the original link.** No swap and no more warnings for that link (for a page the checker gets wrong). **Undo** puts it back in line.

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

**Could not check:** 401, 403, 429, 451 and 999 mean the site refuses automated visitors, so whether the page exists is unknown. These links are listed separately and never called dead. Links to the server's own network are not requested either: a private, loopback, link-local, carrier-grade or reserved address, whether the link names it or redirects to it. Every request (the first and each redirect) is checked just before it is sent, against all of the host's IPv4 and IPv6 addresses, and the connection goes to exactly the addresses checked (under every spelling of the host, `example.org.` with its final dot included); a host written as a number in an unusual form (`0x7f000001`) is refused. This keeps anyone who controls a linked site (for example after its domain expires and is bought) from making this server request addresses inside its own network.

Three failures in a row, a week or more apart, are needed (the rule Wikipedia and the Internet Archive use), so a site that is down for a day is never called dead. A blocked answer neither adds to nor resets the count. One good check resets it.

## Which archived copy

The Wayback Machine's index is asked for the first good (HTTP 200) capture from the year the link went on the site (the event's date, or the post's date), and if there is none, the last good capture; either way, never one from after the link started failing. So an event from 2012 links to the page as it was in 2012. If the Archive does not answer, it is asked again on the next run.

## Making sure a copy exists

A dead link can only get a copy the Archive already has. So every working link is also looked up in the Archive's index once, and any page it has never captured is sent to its **Save Page Now** service (`skip_first_archive`, and no new capture if one was made in the last 30 days). On the next run the plugin asks how each request ended:

- captured: done for good;
- still pending: asked again the next week;
- refused (usually a site that asks not to be archived; listed under **The Internet Archive would not save**): asked again after a month.

If the Archive says it is busy, or asks for keys, the plugin stops sending pages for that run and tries again the next week; the report says so.

**archive.org keys.** Save Page Now accepts this site's requests only with the keys of an archive.org account (tested October 2026: without keys it answers HTTP 401). Without keys, pages are not sent and everything else works: checks, warnings, and the Archive's existing copies. A free account is enough; use one the committee keeps (for example under its shared email) so a successor can sign in to it.

Entering them, no terminal needed: **Tools > Link check > Internet Archive keys**. The screen says where to find the two codes (https://archive.org/account/s3.php). On **Save keys** the site first sends the home page to Save Page Now with the new keys: keys the Archive refuses are not saved; if the Archive cannot be reached, they are saved and marked untested. If the last run stopped saving pages for want of keys, it carries on in the background right away. **Remove keys** deletes them.

How they are kept: in the option `bx_links_ia_keys` (not autoloaded), encrypted with libsodium's secretbox under a key derived from the site's own secret keys in `wp-config.php` (`wp_salt('secure_auth')`), so the database alone, such as a backup, does not reveal them. The secret key is never shown again after saving; the screen shows only the last four characters of the access key, who saved them and when. Only administrators (`manage_options`) reach the screen. If the site's secret keys in `wp-config.php` are ever changed (some security tools do this to sign everyone out), the stored keys can no longer be read; the screen says so and asks for them again. Requests that carry the keys never follow a redirect, so they cannot be passed on to another address; a redirect is reported as no usable answer.

Alternatively, with server access, the keys can go in `wp-config.php`, where they take priority over the screen (which then shows that they are set there and offers no form):

```
define('BX_IA_ACCESS_KEY', '...');
define('BX_IA_SECRET_KEY', '...');
```

## When it runs

WordPress's scheduler starts a run once a week (`bx_links_weekly`, first one an hour after the plugin is installed). The run checks links in batches of about 20 seconds, each its own background request, 30 seconds apart, so it never holds up a visitor or hits the server's time limit; one request a second at most to any one site; about one request a second to the Archive's index and five seconds between Save Page Now requests. Order within a run: the checks, then copies for newly dead links, then the Save Page Now work. At the end, the warning email goes out (in the default setting), and if any swap started or ended, every page's cached copy is emptied (block 18) so pages are rebuilt with the change.

WordPress's scheduler runs when someone loads a page that is not served from the cache, which on this site happens many times a day (dashboard visits, searches, missing pages). If `DISABLE_WP_CRON` is set in `wp-config.php`, a real cron job has to run `wp cron event run --due-now`.

The email goes through WordPress (`wp_mail`) to the address on the report, by default the site's administration email.

## Command line

```
wp bx-links run                    # a whole run now, in the terminal (same six-day rule)
wp bx-links list --status=dead     # or failing, blocked, ok, new
wp bx-links save https://...       # send one page to Save Page Now and show the Archive's answer
```
