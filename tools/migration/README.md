# Events migration (one-time)

These scripts moved bxscc.org's events from hand-written HTML (on the home page, News and Events, and 18 yearly posts) into the events table in October 2026. They are kept for reference and for the undo path; a new site does not need them.

Run from the WordPress root with WP-CLI. Every script takes `dry` (shows what it would do, changes nothing) or `run`.

| Script | Does |
|---|---|
| `bx-import-events.php` | Reads `~/bx-events.json` (the events and sections extracted from the old pages; not in this repository) and adds them to the table. Safe to run twice: an event with the same title and first day is skipped. Hand-picked sections are linked to the new event IDs. |
| `bx-apply-pages.php` | Replaces the home page, News and Events and the yearly posts with section and year shortcodes. WordPress keeps the previous content as a revision. |
| `bx-restore-pages.php` | Puts those pages back exactly as they were, from `~/bx-events-dump.json` (a copy made before the switch). Events stay in the table. |

```
wp eval-file ~/bx-import-events.php dry --skip-themes --skip-plugins
wp eval-file ~/bx-import-events.php run --skip-themes --skip-plugins
```

The extraction from the old HTML (dates in many hand-typed styles: "Tuesday, May 21st & Thursday, May 23rd, 2019", "2022-2023 and 2023-2024 school years", "6 to 9pm, November 16th, 2011") into date parts, times, locations and notes was done separately and reviewed event by event before import.
