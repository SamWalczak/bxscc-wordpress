# Photos

Code: blocks 2 and 3 of `bxscc-wordpress-enhancements.php` (grid and lightbox styling, lightbox script); extra grid variants in `css/components.css`.

## Photo grids

Wrap linked photos in a `div` with the class `bx-grid`:

```html
<div class="bx-grid">
  <a href="/wp-content/uploads/2026/07/photo-1.jpg"><img src="/wp-content/uploads/2026/07/photo-1-300x225.jpg" alt="Opening ceremony, Xiamen, July 16, 2026" /></a>
  <a href="/wp-content/uploads/2026/07/photo-2.jpg"><img src="/wp-content/uploads/2026/07/photo-2-300x225.jpg" alt="Mangrove wetland park" /></a>
  ...
</div>
```

Every tile is cropped to 4:3. If the editor wraps the photos in `<p>` or adds `<br>` between them, the grid ignores that.

| Variant | Desktop | Under 1200px | Under 900px | Under 600px |
|---|---|---|---|---|
| `bx-grid` | 5 across | 4 across, first 8 shown | 3 across, first 6 shown | 2 across, first 4 shown |
| `bx-grid bx-grid-4` | 4 across | 4 across | 4 across | 2 by 2, first 4 shown |
| `bx-grid bx-grid-all` | 4 across, all shown | 4 across, all shown | 3 across, all shown | 2 across, all shown |

The plain grid hides photos on narrower screens so a grid of ten stays two rows; the hidden ones are still in the lightbox. `bx-grid-all` is for galleries where every photo should be visible (the History page).

## Photo sizes

Code: block 15 of `bxscc-wordpress-enhancements.php`.

WordPress gives photos inserted with **Add Media** a `srcset` (the smaller copies it made at upload) and `sizes` (how wide the photo is drawn), so each screen downloads a copy close to the size it shows. Photos written by hand, or pasted in from elsewhere, named one fixed file instead, often far larger than shown: a 3200-pixel logo shown 320 pixels wide, an 1800-pixel photo shown in a 640-pixel column.

Every uploaded photo in page content without a `srcset` now gets one:

- **Which copies.** The copies WordPress already made of that upload with the same proportions, none wider than the file the page names. A small copy chosen on purpose (a 232-pixel flyer) therefore stays the largest any screen gets, so no page downloads more than it did; pages that named a large file get smaller copies where those are enough. The upload is found from the photo's own address, including addresses of a resized copy (`photo-768x512.jpg`) or of the original of a photo WordPress scaled down (`photo.jpg` for `photo-scaled.jpg`). A `wp-image-123` class copied along with pasted HTML can name a different photo, so the address decides.
- **How wide.** The photo's `width` attribute (or its file's width), capped at the content column. The cap is rounded up a little (the whole window on phones, 760 pixels on tablets, 51% of the window on desktop), never down: every photo is limited to its column's width by the theme's stylesheet, so a photo with no `width` attribute lays out exactly as it did with one file.
- **Grid tiles** are sized as tiles: half the window on phones, a third under 900 pixels, 187 pixels under 1200, an eighth of the window above.
- **Lightbox.** Unchanged: it opens the full-size file.

Photos from other sites, and photos that are not in the Media Library, are left as they are.

## Photos appear whole

Browsers draw a photo from the top down as its bytes arrive, which looked unfinished. Every photo in page content, events and sidebar widgets (block 15 marks them `bx-fade`) stays transparent until it has arrived whole, then appears: with a quarter-second fade if the page had already been drawn without it, at once if it was ready before the page's first paint (a photo already in the browser's cache). Its space is reserved from the start, so nothing moves. Without scripts, photos show as they always did (the rule applies only under `html.bx-js`, set by the first script in `<head>`). Visitors who ask for less motion get no fade.

## Lightbox

Clicking a grid photo, or any other photo in page content, opens it large over a dark backdrop:

- **Which photos.** Every image inside the content area (`.entry`), except images in the sidebar, images with the class `bx-nozoom` (for images too small to enlarge), and images that link to a web page rather than an image file (logos linking to partners stay ordinary links).
- **Full size.** For a photo with no link, the full-size file is found by removing WordPress's `-300x225` style size suffix from its address.
- **Loading.** Moving to another photo, the lightbox fetches it out of sight and changes photo and caption together once it has arrived (browsers otherwise keep painting the previous photo, under the new caption, until the new one is ready). If that takes more than a tenth of a second, a spinner and the new caption show meanwhile; photos already downloaded appear without it.
- **Groups.** Photos in the same grid, or in the same table cell, form a group; the lightbox steps through the group. A lone photo is its own group and shows no arrows.
- **Caption.** The link's `data-caption` if it has one (the banner photos: see [banner.md](banner.md)), otherwise the photo's title, or its alt text when it has no title. Line breaks in a caption are kept, so a bilingual caption shows English and Chinese on separate lines.
- **Fixed frame.** The photo area is a fixed box (80% of the window wide, 82% tall; full width on phones). Every photo scales to fit inside it, so the frame and arrows never move between photos.
- **Controls.** Arrows at the window edges; left and right arrow keys; Escape or a click outside the photo closes it; swipe left or right on touch screens.
- **Accessibility.** The lightbox is a `role="dialog"` element; the arrow buttons have labels ("Previous photo", "Next photo").

Photos get a zoom-in cursor so it is clear they can be enlarged.
