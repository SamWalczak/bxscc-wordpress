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

## Lightbox

Clicking a grid photo, or any other photo in page content, opens it large over a dark backdrop:

- **Which photos.** Every image inside the content area (`.entry`), except images in the sidebar, images with the class `bx-nozoom` (for images too small to enlarge), and images that link to a web page rather than an image file (logos linking to partners stay ordinary links).
- **Full size.** For a photo with no link, the full-size file is found by removing WordPress's `-300x225` style size suffix from its address.
- **Groups.** Photos in the same grid, or in the same table cell, form a group; the lightbox steps through the group. A lone photo is its own group and shows no arrows.
- **Caption.** The photo's title, or its alt text when it has no title. Line breaks in a caption are kept, so a bilingual caption shows English and Chinese on separate lines.
- **Fixed frame.** The photo area is a fixed box (80% of the window wide, 82% tall; full width on phones). Every photo scales to fit inside it, so the frame and arrows never move between photos.
- **Controls.** Arrows at the window edges; left and right arrow keys; Escape or a click outside the photo closes it; swipe left or right on touch screens.
- **Accessibility.** The lightbox is a `role="dialog"` element; the arrow buttons have labels ("Previous photo", "Next photo").

Photos get a zoom-in cursor so it is clear they can be enlarged.
