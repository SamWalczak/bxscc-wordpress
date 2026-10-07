# Events

Code: `events/events.php` (storage, wording, sections, shortcodes, yearly pages) and `events/admin.php` (the dashboard menu).

The site lists its activities on the home page, on News and Events, and on one archive page per year. Before this feature, every list was hand-written HTML on each page, so the same event was copied (and drifted) between pages, and nothing moved from "upcoming" to "recent" unless someone remembered to move it. Now each event is entered once and every list is built from the same records.

## What an event is

One row in the table `{prefix}bx_events`:

| Field | Shown | Notes |
|---|---|---|
| Title | Across the top of the event's table row | May contain a link. |
| Dates | Left column, first line | One or more *parts*; see below. |
| Time or note | Left column, under the date | Short formatted text: a time ("6:00 to 9:00pm") or a note about the date ("Dates to be announced", "(updated date)"). Links, bold, italic and line breaks are kept; all other markup is removed. |
| Location | Left column, under the time | Same formatting rules as Time or note. |
| Description | Right column | Full WordPress editor, including Add Media. Shortcodes in it run. |

Events are deliberately not WordPress posts: they have no pages of their own, no comments and no revisions, which keeps the dashboard and the database small.

## Dates

A date is one or more parts. Each part is a single **day**, **month**, **season** or **year**, or a span from one of those to another (the two ends can be different kinds). The wording is generated, so every page reads the same way:

| Entered | Reads |
|---|---|
| Day | October 16th, 2014 |
| Month | July 2023 |
| Season | Fall 2024 |
| Year | 2019 |
| Day to day, same month | June 6th to 8th, 2025 |
| Day to day | July 15th to August 1st, 2025 |
| Month to month | April to August 2023 |
| Season to season | Fall 2024 to Spring 2025 |
| Several days, one year | May 23rd, 27th, and 31st, 2023 / February 11th and April 5th, 2019 |
| Several months, one year | June and July 2024 |

Seasons are fixed ranges close to the solstices and equinoxes: spring March 20 to June 20, summer June 21 to September 22, fall September 23 to December 20, winter December 21 to March 19. Winter belongs to the year it starts in, so "Winter 2025" runs into 2026.

For filtering and ordering, an event covers everything from the first day of its earliest part to the last day of its latest part:

- **Ordering**: by that last day (the day the event *closes*), newest first on archive pages and in Recent; soonest first in Upcoming. Ties keep the order the events were entered.
- **Upcoming**: an event is upcoming until its last day has passed.
- **Years**: an event appears on every yearly page its range touches (an event from Fall 2022 to Spring 2024 is on 2022, 2023 and 2024).

Exact dates not known yet: enter as much as is known (for example Year 2027) and put "Dates to be announced" in Time or note. Canceled events: say so in the title or description; there is no separate status.

## Sections

A section is a heading plus a rule that picks events. Sections are kept in the option `bx_ev_sections` and placed on any page with a shortcode; the same section can appear on several pages.

| Rule | Shows | Order |
|---|---|---|
| Upcoming | Every event whose last day is today or later | Soonest first |
| Range | Events that closed between *N* and *M* months ago (0 and 4 = the last four months) | Newest first |
| Hand-picked | Only the events ticked in the section editor | Newest first |

Each section can also have a maximum number of events, and a message to show when it is empty. With no message, an empty section prints nothing at all, heading included, so the page never shows an empty box.

Sections on bxscc.org: `upcoming`, `recent` (0 to 4 months), `highlights` (hand-picked), `previous` and `prepandemic` (hand-picked). The home page shows the first three; News and Events shows all five plus the yearly archive links.

## Shortcodes

| Shortcode | Output |
|---|---|
| `[bx_section name="recent"]` | The section's heading and its events, wrapped in `<div class="bx-section">`. If no section has that name, editors see a reminder and visitors see nothing. |
| `[bx_year year="2014"]` | Every event that touches that year, newest first, or "No events are listed for this year yet." |
| `[bx_years]` | "Archives by Year": links to every yearly page, one row per decade, newest decade first. |

Event lists render as the site's usual two-column tables (`<table class="bx-events">`): title across the top, date column on the left, description on the right. They are styled by the same rules as hand-written tables ([page-markup.md](page-markup.md)), so they look identical to the old hand-made lists.

## Yearly pages

Each past year with at least one event has an ordinary post titled "News and Events – YYYY", with the slug `news-and-media-YYYY` and the content `[bx_year year="YYYY"]`. They are the archive:

- The current year has no page; its events are on the home page and News and Events.
- On January 1st, the year that just ended gets its page. This is checked once a day, on the first request of the day.
- Saving an event in an earlier year that has no page yet creates that page right away.
- Existing yearly posts keep their addresses; the plugin finds them by slug.
- New pages are dated December 31st of their year, so they sort with the others.
- The previous / all / next bar at the bottom of each yearly page comes from [layout-and-navigation.md](layout-and-navigation.md).

## Dashboard

Menu **Events**, available to Editors and Administrators (capability `edit_pages`, constant `BX_EV_CAP` in `events/admin.php`).

- **All Events**: newest first, 50 per page, with a text search over titles and descriptions and a year filter. Upcoming events are marked.
- **Add Event / Edit**: title; dates (a kind selector per part, a "to" box for spans, "+ Another date" for separate parts); Time or note and Location as one-line editors with Bold, Italic and Link buttons (Enter makes a line break); the description editor. Help text on the form explains each field, including what to do when exact dates are not known.
- **Sections**: the list of sections with the shortcode to paste for each, and an editor for heading, code name, rule, limit, empty message and the hand-picked events (with a filter box).
- **How it works**: the same guidance in plain language for whoever maintains the site next.

Deleting an event cannot be undone. Deleting also removes it from any hand-picked section.

## Caching

On a site with a full-page cache (bxscc.org uses an Nginx proxy cache), saving an event does not by itself empty the cache, so a changed list can take until the cached copy expires to appear. Purge the affected pages (home, News and Events, the event's yearly pages) after editing; see [maintenance.md](maintenance.md#page-cache).

## Upgrades

The table layout is versioned (`BX_EV_DB_VERSION`). When the code's version differs from the stored option, the table is updated with WordPress's `dbDelta` on the next request. Version 3 widened Time or note and Location to 1,000 characters so they can hold links.

## Migration

`tools/migration/` holds the scripts that moved the 221 events from the old hand-written pages into the table, switched the pages to shortcodes, and can put the old pages back. See [tools/migration/README.md](../tools/migration/README.md).
