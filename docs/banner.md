# Banner

Code: blocks 3c, 3d and 3i of `bxscc-wordpress-enhancements.php`; styles in `css/theme-fixes.css` (carousel sections). Applies to the News Magazine theme's home page slideshow (Web-Dorado's slider, markup `#slideshow`).

The theme's slideshow always started on the same photo, loaded every photo before showing the page, and had several bugs that made it freeze or jump. This feature keeps the theme's slideshow, with its own settings (fade length, interval, pause on hover), and fixes how it starts, loads and responds.

## Random start, then in order

Every visit starts on a randomly chosen photo and continues in order from there.

The page is cached as a whole by the server, so the choice cannot be made in PHP (every visitor would get the same cached choice). Instead:

1. Before the page is sent, every slide's photo address moves from `src` into `data-src`, so the browser downloads nothing yet. Each `src` becomes a 1-pixel placeholder.
2. A small script at the very top of `<head>` picks the start slide and preloads that one photo at high priority.
3. A script at the end of the header shows that slide in place of slide 1 before anything is painted, and updates the slide's title and description text to match.

## Loading in viewing order

As soon as the start photo has arrived, the others are fetched right away, without waiting for the rest of the page: nearest first, alternating right and left (the next slide, the previous, the one after next, and so on), four at a time. Browsers open up to six connections to one site, so two stay free for the rest of the page. A slide clicked to before its turn is fetched immediately.

## Photo size

The theme names each slide's full-size file, 2560 pixels wide and 340 to 640 KB, for every screen. Each slide now also carries a `srcset`: the copies WordPress already made of that photo (300, 768, 1024, 1536 and 2048 pixels wide, plus the full size), and `sizes` saying how wide the banner is drawn: the window less 20 pixels on phones, 748 pixels on tablets, three quarters of the window on desktop (`BX_BANNER_SIZES`). The browser takes the smallest copy that is sharp at that width on that screen, so a phone downloads the 1024 or 1536 copy and a desktop the 1024 to 2048 one, instead of the 2560 original everywhere.

The start slide's preload asks for the same copy the slide will use. A browser too old to preload by `srcset` skips the preload rather than download the full-size file first and a smaller one after.

Slides added in the theme's settings get this automatically, as long as the photo was uploaded through the Media Library (that is where the smaller copies come from).

## Placeholder while loading

A slide whose photo has not arrived yet shows a navy box with a small gold spinner, the same size as the photo, so nothing resizes when the photo appears and the arrows work the whole time. The spinner slows down for visitors who ask their system for reduced motion.

## Smooth changes

- **Crossfade on the first change.** The theme's fade only animates the incoming slide and relies on the outgoing one having an animation left over from an earlier fade. The start slide never had one, so the first change cut to navy and faded in. Every slide now gets the theme's own fade length on the first change.
- **Photo pinned to its box.** The theme centers each photo inside table-like boxes on a text baseline, which left it 1 to 2 pixels off and showed a sliver of background above or below some photos. Each photo is now positioned to fill its slide exactly.
- **Sized before first paint.** The theme's own sizing function runs as soon as the slideshow markup is parsed, so the size it computes later on page load is the same and nothing moves.
- **No reload links.** The theme wraps every slide in an empty link that reloads the page when clicked; those links are removed (or, on a slide with an uncropped photo, point to that photo: see below).

## Uncropped photos in the lightbox

The banner shows wide cropped copies. A slide whose uncropped photo is on the server opens that photo in the same lightbox as the page photos when clicked or tapped (or focused with Tab and opened with Enter); the lightbox's arrows step through the banner photos in slide order. A slide without one is not clickable, as before.

**Where the photos go.** `wp-content/uploads/carousel-originals/`, as plain files (uploaded with the File Manager or the terminal, not the Media Library, so WordPress makes no resized copies and does not replace the file with a 2560-pixel one). Each is named after its slide without WordPress's endings: the slide `carousel2-cropped-scaled.jpg` opens `carousel2.jpg`. `.jpg`, `.jpeg`, `.png` and `.webp` all work, in any letter case. A new slide gets the same feature by adding its photo here under the matching name; nothing in the plugin changes. The page cache must be emptied afterwards, because the links are written into the page.

**When they download.** They are large (3 to 13 MB, about 36 MB for the six), so none comes with the page:

1. Nothing is fetched until the page's load event has fired and every banner slide has arrived (or 30 seconds after the load event, if a slide never does).
2. Then they download one at a time, at low priority, starting with the slide on screen and going on in viewing order.
3. A slide clicked before its photo has arrived is fetched right then. A background download of a different photo is stopped, and the queue waits until the clicked photo has arrived; the stopped one is fetched again later. Arrows in the lightbox count as clicks.
4. The server lets browsers keep these files for 7 days, so a later visit takes them from the browser's cache.

The script that does this is added to the end of the page only when the page has such a slide.

**Captions.** A banner photo's caption in the lightbox is the slide's **Caption** field in the Media Library (Media > Library > `carousel2-cropped` > Caption). The text is written into the page when it is built, so empty the page cache after editing a caption. A slide with an empty Caption shows no caption.

**The banner follows the lightbox.** While the lightbox shows banner photos, the slideshow does not advance (the theme's timer is stopped, and its restarts, when the pointer leaves the banner or the window regains focus, are ignored). Moving left or right in the lightbox (arrows, keys or swipe) moves the banner to the same photo, without a crossfade since it is behind the lightbox. On closing, the banner is on the photo the lightbox showed and plays on from it, with a full interval before the next change; as always, it waits while the pointer is over it. Photos from the page content in the lightbox leave the slideshow alone.

Current photos (October 2026): slides 1, 2, 4, 6, 8 and 9. Slides 3, 5 and 7 have no uncropped photo yet.

## Arrows

- **Clicking an arrow restarts the countdown** to the next automatic change, so an automatic change never lands right after a manual one. This applies even with "pause on hover" on, because that pause only covers the photo, and moving onto an arrow counts as leaving it.
- **No frozen first clicks.** The theme's "window got focus" handler is meant to note which slide is showing, but its counter never increases, so it always noted slide 0. Firefox fires that event while the page is still loading, so with a random start the arrows stepped through slides hidden under the start slide, and the banner looked frozen for several clicks. The plugin replaces that handler, right after the theme sets it up, with one that does the same job correctly.
- **Slide 0 reachable.** The theme starts its "current slide" value as an empty string, and in JavaScript `"" == 0` is true, so a move to slide 0 was skipped until something else had changed slides. The plugin sets the value to the start slide.

## Related

- The logo is fetched at high priority and preloaded; see [performance-and-security.md](performance-and-security.md).
- Banner photos and their order are managed in the theme's own settings, not in this plugin.
