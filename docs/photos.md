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

**A page that opens with a photo.** When a page's content starts with a photo (nothing written before it), the photo is marked `bx-lead` and its top edge sits on the sidebar's top line, as a page that opens with a table already did (see [page-markup.md](page-markup.md#two-column-tables)). Until 1.70 such a photo sat 4 to 6 pixels lower: the theme's margin above images, or the text line an inline photo sits on. A photo that was inline also loses the empty space under it that the text line left, so what follows it moves up by that much too (13 pixels on the Youth Ambassadors program page and the 2024 and 2025 pages).

**Photos with a fixed pixel cap.** A content photo written with `style="max-width: 320px"` loses the theme's "never wider than the column" rule, so on a screen narrower than the cap it sticks out and the page scrolls sideways (the Partners logo at 350px and narrower; the 2013 trade mission sponsor logos at 290px and narrower). Write caps as `max-width: min(320px, 100%)`. `tools/img-max-width.php` (1.78) changed the 37 such photos on six pages.

**Full-width photos on phones.** The theme gives a linked "alignnone" photo a 20px right margin (`a img.alignnone`), and the lightbox links every content photo, so a photo as wide as the column stuck out 10px past a phone screen and the page scrolled sideways (the 2019, 2024 and 2025 Youth Ambassadors pages and the program page). Since 1.75 theme-fixes.css drops that margin on photos inside a lightbox link (`#content .entry a.bx-zoom > img.alignnone`); nothing on the site sits beside such a photo, so nothing else moves.

**No jump while loading.** A photo written without `width` and `height` has no size until it arrives, so the text under it jumped down when it did (the first photo on History; the logos on Partners and the 2013 trade mission pages). Such a photo gets its proportions in advance as `aspect-ratio: auto W / H` (from the file name's `-WxH`, or else the Media Library), which reserves its space; `auto` means its own proportions take over once it has loaded, so finished pages are exactly as before. Width and height attributes are not added instead: they would change which photos WordPress loads lazily.

## New uploads are WebP

Code: block 21 of `bxscc-wordpress-enhancements.php`.

A JPEG photo uploaded to the Media Library (or an iPhone HEIC photo, which WordPress otherwise turns into a JPEG) is stored and shown as WebP, about a third smaller at the same look; every current browser shows WebP. WordPress (6.7 and later) does the work through `image_editor_output_format`:

- **Every copy** it makes (thumbnail, medium, large, 1536 and 2048 pixels) is WebP, so pages and their `srcset` use WebP throughout.
- **The photo itself** as pages and the lightbox show it ("full size") is re-saved as WebP at WordPress's usual quality, after being scaled to 2560 pixels if larger (as before). Measured on a test upload: a 1600-pixel JPEG of 615 KB became a 253 KB WebP; a 3000-pixel one of 1.7 MB a 442 KB WebP at 2560 pixels.
- **The upload itself is kept**, untouched, as the "original image" (Media Library, the photo's details: Original image), so a full-quality file is always there to download.
- **Block editor uploads** (WordPress 7.1 over HTTPS lets the browser resize photos): the browser makes the WebP copies itself, told to by the same setting, and the uploaded file stays the original; the server does not convert it first.
- **Only new uploads.** Editing a photo already in the library (Edit Image: crop, rotate, scale) saves in its own format as before, and `wp media regenerate` does not convert old photos.
- **PNG files stay PNG**: they are usually logos, screenshots or graphics with sharp edges or transparency, which lossy WebP would soften.
- If the server's image library cannot write WebP, uploads work exactly as before.

## Camera data

Code: block 22 of `bxscc-wordpress-enhancements.php` (new uploads, since 1.76); `tools/photo-metadata.php` (the photos already in the library, once).

Phones and cameras write data into each photo file: make and model, settings, the maker's private notes, often the place the photo was taken, sometimes a small preview. WordPress copies it into every copy it makes. On this site that was 10 to 43 KB in each copy, more than the picture itself in the smallest ones, and a location for anyone who downloads a photo.

After WordPress has made an upload's copies, block 22 takes that data out:

- **Removed:** in JPEG files, the EXIF and XMP blocks (APP1), IPTC and Photoshop data (APP13) and comments; in WebP files, the EXIF and XMP chunks.
- **Kept:** the colour profile, the blocks that say how to read the colours (JFIF, Adobe), and the picture data, copied byte for byte. Photos look exactly the same: checked by decoding test photos before and after, and by screenshots in Chromium.
- **Which files:** every copy WordPress made, and the full-size photo when WordPress made it (scaled down to 2560 pixels, or converted to WebP, which on this site happens to every JPEG upload). The upload itself is never touched: it stays as the "original image" with its data, for a full-quality download. So when WordPress shows the upload itself as the full-size photo (a WebP under 2560 pixels, for example), that file keeps its data, and so do files saved by the Media Library's image editor (Edit Image). `tools/photo-metadata.php` covers both.
- **Left as they are:** a file whose data says to turn the photo (orientation), or to read its colours a particular way without a colour profile (colour space); a file that holds a second picture (a phone's HDR photo, whose brightness map is described in its XMP); a file with anything unexpected in its structure. Taking the data out of these could change how they show. A PDF's preview pictures are also left as WordPress made them.
- **Safe writing:** the new file is written next to the old one, checked to have the same dimensions, and renamed over it, keeping its permissions and date; any failure leaves the old file in place. The recorded file sizes (Media Library: File size) are updated, under every size name that shares a file.
- **What is lost:** camera details, and any credit or copyright text written into the files the site shows. WordPress reads the title, caption, credit and copyright from the upload before this runs, so whatever it records from them stays, and the "original image" keeps everything.

**The photos already in the library.** `tools/photo-metadata.php`, run with WP-CLI (the commands are at the top of the file). The dry run reports how many files carry camera data and the MB to save, the files that will be left as they are and why, and every file with a location, including "original image" files, which are only reported, never changed. The run copies every file it changes to `~/bx-photo-metadata-backup/` first (same folders as in uploads, checked by md5), then changes it and updates the recorded sizes; to undo one, copy it back. Unlike block 22, it also does full-size files that are the upload itself (photos uploaded before WordPress kept originals, or never scaled or converted): for those, once the backup is deleted, the site keeps no copy of the camera data. It can run again at any time (files already done count as "clean"), for example after editing photos in the image editor.

## Photos appear whole

Browsers draw a photo from the top down as its bytes arrive, which looked unfinished. Every photo in page content, events and sidebar widgets (block 15 marks them `bx-fade`) is kept out of sight until it has arrived whole, then appears at once. If it is still downloading after 0.3 seconds, its space shows the banner's loading look: a navy box with a small gold spinner, the same size as the photo. A photo already in the browser's cache, or one that arrives within 0.3 seconds, never shows the box. Its space is reserved from the start, so nothing moves, and once loaded the page looks exactly as before. Without scripts, photos show as they always did (the rule applies only under `html.bx-js`, set by the first script in `<head>`). The spinner turns slowly for visitors who ask their system for reduced motion.

How it works: the photo's pixels are moved out of its box (`object-position`) while the box keeps its size, and a CSS animation that starts after 0.3 seconds gives the box the navy background and the spinner. The spinner is an animated SVG drawn as the box's background, because a photo cannot hold the `::after` element the banner's spinner uses. The photo's `onload` adds `bx-in`, which removes both.

## Lightbox

Clicking a grid photo, or any other photo in page content, opens it large over a dark backdrop:

- **Which photos.** Every image inside the content area (`.entry`), except images in the sidebar, images with the class `bx-nozoom` (for images too small to enlarge), images that link to a web page rather than an image file (logos linking to partners stay ordinary links), and, since 1.80, images inside a form or written into the page itself (`data:` addresses), such as PayPal's invisible pixel next to the donate button, which had become an empty link keyboard users tabbed through.
- **Full size.** For a photo with no link, the full-size file is found by removing WordPress's `-300x225` style size suffix from its address. Where that guess, from the photo's address or any of its smaller copies, names a file that does not exist (a photo over 2560 pixels uploaded as WebP, whose full size is `photo-scaled.webp`), the photo carries its real full-size address in `data-bx-full` (block 15); pages from before 1.69 are unchanged.
- **Loading.** Moving to another photo, the lightbox fetches it out of sight and changes photo and caption together once it has arrived (browsers otherwise keep painting the previous photo, under the new caption, until the new one is ready). If that takes more than a tenth of a second, a spinner and the new caption show meanwhile; photos already downloaded appear without it.
- **Groups.** Photos in the same grid, or in the same table cell, form a group; the lightbox steps through the group. A lone photo is its own group and shows no arrows.
- **Caption.** The link's `data-caption` if it has one (the banner photos: see [banner.md](banner.md)), otherwise the photo's title, or its alt text when it has no title. Line breaks in a caption are kept, so a bilingual caption shows English and Chinese on separate lines.
- **Fixed frame.** The photo area is a fixed box (80% of the window wide, 82% tall; full width on phones). Every photo scales to fit inside it, so the frame and arrows never move between photos.
- **Controls.** Arrows at the window edges; left and right arrow keys; Escape or a click outside the photo closes it; swipe left or right on touch screens.
- **Accessibility.** The lightbox is a `role="dialog"` element; the arrow buttons have labels ("Previous photo", "Next photo"). The photo in the lightbox takes the clicked photo's alt text, and the link around a photo is named by that alt text, so every photo in page content needs one (WordPress: select the image, **Alternative Text** in the block settings; the Media Library's Alternative Text field only fills it in when the photo is inserted). A photo that only decorates can keep `alt=""`, but it then needs `bx-nozoom`, or its link has no name.

Photos get a zoom-in cursor so it is clear they can be enlarged.
