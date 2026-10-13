# Page markup

Styles: `css/components.css`. These are the patterns page content uses, so a new page looks like the rest of the site by following them. Page content is written in the block editor's **Code editor**, wrapped in a Custom HTML block (`<!-- wp:html -->` … `<!-- /wp:html -->`) so WordPress does not add stray paragraph tags.

## Two-column tables

The site's basic layout: a white table with a navy top rule (gold in dark mode), a narrow gray label column on the left and text on the right.

```html
<h1 style="text-align: center;"><strong>Section heading</strong></h1>
<table>
<tbody>
<tr>
<td colspan="2"><h3 style="text-align: center;"><strong>Row title</strong></h3></td>
</tr>
<tr>
<td><em>Label or date</em></td>
<td><p>Text…</p></td>
</tr>
</tbody>
</table>
```

- The label column is 150px wide, smaller and gray; `<em>` inside it is shown upright (italic inside that `<em>` shows as italic).
- A label's first line is aligned with the first line of the text beside it (baseline alignment), unless the row opens with a photo or video, which stays top-aligned.
- A cell spanning both columns (`colspan="2"`) is a full-width content cell, used for row titles and for single-cell rows such as the Contact page. It is the row's first cell, so the label rule above shows its `<em>` upright too; inside a `.bx-section` table, `<em>` in its paragraphs stays italic (since 1.73; the 2019 Youth Ambassadors dates line). Row titles stay upright.
- On phones (under 600px) the label sits centered above its text.
- A page that opens with a table drops it 6px so its top line meets the sidebar's. A page that opens with a photo (nothing written before it) gets the same: the plugin marks that photo `bx-lead` and `theme-fixes.css` sets its top edge on the sidebar's top line (side-by-side layout only).
- The last table on a page has no bottom margin.

A media table (2018 Youth Ambassadors page) is this same table: one row title per news source, then a row per article with its date as the label (linked to the article too) and the link and a short summary as the text. A Chinese-language row carries `lang="zh-Hans"` on its cells.

Event lists generate exactly this markup; see [events.md](events.md).

## Specialized tables

| Class | Used on | Effect |
|---|---|---|
| `table.bx-roster` with `<col class="bx-c-role">`, `bx-c-name`, `bx-c-email` | Leadership | Fixed column widths (32 / 22 / 46%) shared by every table on the page, so the officer and subcommittee tables line up. On phones (under 600px) the cells stack and the column definitions are switched off: Chrome, Edge, Samsung Internet and Firefox otherwise kept the 32% width on the stacked cells, squeezing the tables into a third of the screen (Safari did not; fixed in 1.78). |
| `table.bx-speakers`, header row `tr.bx-thead` | CHINA Town Hall | Narrow year column, labeled header row, two equal text columns (9 / 45.5 / 45.5% from 600px up since 1.78: browsers' automatic layout split the text columns unevenly, about 37/63 in Safari and 42/58 in Chrome and Firefox). On phones the header row is hidden and the third column is prefixed "Baltimore local speaker:". |
| Three-column tables (no class) | | The middle column is flexible and the third does not wrap (it wraps on phones). |

## Quotes and reflections

A quote: `<blockquote class="bx-quote">` with its paragraphs inside. It gets a navy rule beside the words (gold in dark mode; above them on phones) and the text's own color, instead of the theme's small gray italic. Used for each reflection and for Danny Evans's note in the Thanks table on the 2018 Youth Ambassadors page. Inside a table (that note) it is set in 24px with a thin gray rule like the sidebar's dividers instead of the navy one, also in dark mode; on phones the thin line sits above it (since 1.74).

Reflections: a section heading with no table under it, like the not-found page (a navy line under the heading), then one block per ambassador. The photo, with the ambassador's title and name, years of study (italic) and an optional video link under it, sits beside the quote; photo and quote swap sides from one ambassador to the next, with the sidebar's thin Hot Links divider between ambassadors. From 600px wide the quote's rule runs at least the photo's height and a shorter quote is centered on the photo; a longer quote keeps its own length, with the photo at its top (since 1.72: block 3j measures the photo and sets `--bx-photo-h`, because CSS alone cannot keep the caption under the photo while sizing the quote by it). On phones each stacks: photo, caption, quote.

```html
<div class="bx-section">
<h1 style="text-align: center;"><strong>Reflections from 2018 Youth Ambassadors</strong></h1>
<div class="bx-reflections">
<div class="bx-reflection">
<figure><img src="…" alt="" width="143" height="307" /><figcaption><strong>2018 Youth Ambassador Aaron G.</strong><em>3 years of Chinese studies</em><a href="…">Aaron's video testimonial</a></figcaption></figure>
<blockquote class="bx-quote">
<p>"…"</p>
</blockquote>
</div>
<div class="bx-reflection">…</div>
</div>
</div>
```

Keep the figure and its caption on one line: in a page without the Custom HTML wrapper, WordPress turns line breaks inside a caption into `<br>`. The photo column is 220px wide (a third of the column on narrower screens); a photo narrower than that is centered in it at its own width.

Without photos (2019 page, plugin 1.71): the title and years of study go in `<div class="bx-reflection-who">` in place of the figure, on one line for the same reason. They sit above the quote, and every quote keeps its rule on the left (above it on phones) instead of swapping sides.

```html
<div class="bx-reflection">
<div class="bx-reflection-who"><strong>2019 Youth Ambassador Elijah A.</strong><em>9 Years of Chinese Studies</em></div>
<blockquote class="bx-quote">
<p>"…"</p>
</blockquote>
</div>
```

## Button bars

| Class | Used on | Effect |
|---|---|---|
| `nav.bx-year-nav` | Yearly pages (added automatically) | Three connected segments: previous, all, next. |
| `div.bx-subnav` with links; current page's link gets `class="is-current"` | 2013 Trade Mission pages | Five connected buttons; stacked on phones. Hover and current page are navy with gold text. |

## Links in text

Links inside running text (paragraphs, list items, captions, quotes and table cells in the content area) are underlined (`components.css`, since 1.80): the link blue is only 1.69:1 against the text color, too close to tell apart by color alone (WCAG 1.4.1). Links that hold a photo or bold text, links in headings, the year index, the year bar and the sub-page buttons are left as they were. The underline is 1px, 2px below the text, and 2px thick on hover. Browsers from before 2022 to 2023, which lack `:has()`, show links without the underline, as before.

## Other

| Class | Effect |
|---|---|
| `div.bx-years` | Archives by Year rows (generated by `[bx_years]`): decade label at the left, years centered; the labels drop on narrow screens. |
| `p.bx-sched` | Schedule lines with tight spacing (2013 Trade Mission trip page). |
| `div.bx-grid`, `bx-grid-4`, `bx-grid-all` | Photo grids; see [photos.md](photos.md). |
| `div.bx-videos`, `div.bx-video` | Video previews; see [video-previews.md](video-previews.md). |
| `img.bx-nozoom` | Excludes a photo from the lightbox. |
