<?php
/**
 * Plugin Name: BXSCC WordPress Enhancements
 * Description: Site customizations for bxscc.org: dark mode, menus, home page photo grid and lightbox, banner behavior, fonts, the Site CSS editor (Appearance > Site CSS) and the Events system (Events menu). PHP is edited only through cPanel.
 * Version: 1.72
 */
if (!defined('ABSPATH')) { exit; }

/* 1. Apply a saved dark-mode preference before first paint (no light flash). Also marks the page html.bx-js, so styles
      that need the scripts (photos shown only once they have arrived, block 15) only apply when scripts run. */
add_action('wp_head', function () {
?>
<script>(function(){document.documentElement.classList.add("bx-js");try{if(localStorage.getItem("bx-theme")==="dark"){document.documentElement.setAttribute("data-theme","dark");}}catch(e){}})();</script>
<?php
}, 1);

/* 2. Photo grid and lightbox styling, on every page (only does something where a .bx-grid exists). */
add_action('wp_head', function () {
?>
<style>
.bx-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; margin: 12px 0; }
.bx-grid a { display: block; }
/* if the editor wrapped the photos in <p> or added <br> between them, flatten that so every photo is still a grid tile */
.bx-grid > p { display: contents; }
.bx-grid br { display: none; }
.bx-grid img { width: 100%; height: 100%; aspect-ratio: 4 / 3; object-fit: cover; display: block; margin: 0; }
@media (max-width: 1199px) { .bx-grid { grid-template-columns: repeat(4, 1fr); } .bx-grid a:nth-of-type(n+9) { display: none; } }
@media (max-width: 899px)  { .bx-grid { grid-template-columns: repeat(3, 1fr); } .bx-grid a:nth-of-type(n+7) { display: none; } }
@media (max-width: 599px)  { .bx-grid { grid-template-columns: repeat(2, 1fr); } .bx-grid a:nth-of-type(n+5) { display: none; } }
.bx-lightbox { position: fixed; inset: 0; background: rgba(0,0,0,0.88); display: none; align-items: center; justify-content: center; z-index: 9999; cursor: pointer; }
.bx-lightbox.is-open { display: flex; }
/* the photo area is a fixed box (80% of the window wide, 82% tall); every photo is scaled to fit inside it, so the frame never changes size */
.bx-lightbox figure { margin: 0; width: 80vw; height: 82vh; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: default; }
.bx-lightbox img { max-width: 100%; max-height: calc(100% - 40px); object-fit: contain; }
.bx-lightbox p { color: #fff; margin: 12px 0 0; font-style: italic; text-align: center; min-height: 1.4em; }
/* moving to a photo that has not arrived yet: the browser would keep showing the previous photo until the new one is
   complete, under the new caption; instead the photo area shows a spinner until then */
.bx-lightbox figure { position: relative; }
.bx-lightbox.is-loading img { visibility: hidden; }
.bx-lightbox.is-loading figure::before { content: ""; position: absolute; left: 50%; top: calc(50% - 20px); width: 40px; height: 40px; margin: -20px 0 0 -20px; border-radius: 50%; border: 3px solid rgba(255, 255, 255, 0.25); border-top-color: #fff; animation: bx-lb-spin 0.9s linear infinite; }
@keyframes bx-lb-spin { to { transform: rotate(360deg); } }
@media (prefers-reduced-motion: reduce) { .bx-lightbox.is-loading figure::before { animation-duration: 2.4s; } }
/* photos in page content appear whole instead of being drawn from the top down as they download (block 15 marks them;
   without scripts they show as usual): until a photo has arrived, its pixels are kept out of its box, and if that takes
   more than 0.3 seconds the box shows the banner's loading look, navy with a small gold spinner (an animated SVG, as an
   image cannot hold the banner's ::after spinner). The photo replaces it the moment it is complete. */
html.bx-js img.bx-fade:not(.bx-in) { object-position: -99999px 0; animation: bx-photo-wait 0.01s linear 0.3s forwards; }
@keyframes bx-photo-wait { to { background: var(--bx-navy, #17203a) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='18.5' fill='none' stroke='rgba(254,204,74,0.25)' stroke-width='3'/%3E%3Cpath d='M20 1.5a18.5 18.5 0 0 1 18.5 18.5' fill='none' stroke='%23fecc4a' stroke-width='3'%3E%3CanimateTransform attributeName='transform' type='rotate' from='0 20 20' to='360 20 20' dur='0.9s' repeatCount='indefinite'/%3E%3C/path%3E%3C/svg%3E") center / min(40px, 45%) no-repeat; } }
@keyframes bx-photo-wait-slow { to { background: var(--bx-navy, #17203a) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='18.5' fill='none' stroke='rgba(254,204,74,0.25)' stroke-width='3'/%3E%3Cpath d='M20 1.5a18.5 18.5 0 0 1 18.5 18.5' fill='none' stroke='%23fecc4a' stroke-width='3'%3E%3CanimateTransform attributeName='transform' type='rotate' from='0 20 20' to='360 20 20' dur='2.4s' repeatCount='indefinite'/%3E%3C/path%3E%3C/svg%3E") center / min(40px, 45%) no-repeat; } }
@media (prefers-reduced-motion: reduce) { html.bx-js img.bx-fade:not(.bx-in) { animation-name: bx-photo-wait-slow; } }
/* arrows pinned to the window edges, outside the photo box, in the same place for every photo */
.bx-lb-nav { position: fixed; top: 50%; transform: translateY(-50%); width: 48px; height: 48px; padding: 0; border: 0; border-radius: 50%; background: rgba(255,255,255,0.15); font-size: 0; cursor: pointer; z-index: 10000; display: flex; align-items: center; justify-content: center; }
/* chevrons drawn with borders, so they are centered geometrically rather than by a font's baseline */
.bx-lb-nav::before { content: ""; width: 12px; height: 12px; border-top: 3px solid #fff; border-right: 3px solid #fff; }
.bx-lb-prev::before { transform: translateX(3px) rotate(-135deg); }
.bx-lb-next::before { transform: translateX(-3px) rotate(45deg); }
.bx-lb-nav:hover { background: rgba(255,255,255,0.3); }
.bx-lb-prev { left: calc((10vw - 48px) / 2); }
.bx-lb-next { right: calc((10vw - 48px) / 2); }
@media (max-width: 599px) {
  .bx-lightbox figure { width: 100vw; height: 78vh; }
  .bx-lb-prev { left: 8px; }
  .bx-lb-next { right: 8px; }
}
/* four-up grid: 4 columns on desktop, 2 x 2 on phones */
.bx-grid.bx-grid-4 { grid-template-columns: repeat(4, 1fr); }
@media (max-width: 599px) { .bx-grid.bx-grid-4 { grid-template-columns: repeat(2, 1fr); } .bx-grid.bx-grid-4 a:nth-of-type(n+5) { display: none; } }
</style>
<?php
}, 20);

/* 3. Behaviour: moon switch, tagline split, social labels, lightbox. */
add_action('wp_footer', function () {
?>
<script>
(function () {
  // fix the tooltips the theme hard-codes on the social icons
  var social = { linkedin: "LinkedIn", pinterest: "Discord", facebook: "Facebook" };
  Object.keys(social).forEach(function (cls) {
    var a = document.querySelector("#social li." + cls + " a");
    if (a) { a.title = social[cls]; a.setAttribute("aria-label", social[cls]); }
  });

  // split the site tagline at its comma into two lines
  var desc = document.getElementById("site_desc");
  if (desc && desc.textContent.indexOf(",") !== -1 && !desc.querySelector(".bx-tagline2")) {
    var parts = desc.textContent.split(",");
    var second = document.createElement("span");
    second.className = "bx-tagline2";
    second.textContent = parts.slice(1).join(",").trim();
    desc.textContent = parts[0].trim();
    desc.appendChild(second);
  }

  // dark mode switch: move it to the header's top row, next to the social icons
  var KEY = "bx-theme";
  var root = document.documentElement;
  var btn = document.getElementById("bx-theme-btn");
  if (!btn) {
    var row = document.querySelector("#header_part .container");
    if (!row) { return; }
    var wrap = document.createElement("div");
    wrap.className = "bx-theme-toggle";
    wrap.innerHTML = '<button type="button" id="bx-theme-btn" aria-pressed="false" aria-label="Switch to dark mode" title="Dark mode"><svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79z"/></svg></button>';
    row.appendChild(wrap);
    btn = wrap.querySelector("button");
  }

  function apply(mode) {
    var dark = mode === "dark";
    if (dark) { root.setAttribute("data-theme", "dark"); } else { root.removeAttribute("data-theme"); }
    btn.setAttribute("aria-pressed", dark ? "true" : "false");
    btn.setAttribute("aria-label", dark ? "Switch to light mode" : "Switch to dark mode");
    btn.title = dark ? "Light mode" : "Dark mode";
  }

  var saved = null;
  try { saved = localStorage.getItem(KEY); } catch (e) {}
  apply(saved === "dark" ? "dark" : "light");

  btn.addEventListener("click", function () {
    var next = root.getAttribute("data-theme") === "dark" ? "light" : "dark";
    apply(next);
    try { localStorage.setItem(KEY, next); } catch (e) {}
  });
})();
</script>
<script>
(function () {
  // any other photo inside page content becomes a lightbox link; photos in the same table cell are one group
  document.querySelectorAll(".entry img").forEach(function (im) {
    if (im.closest(".bx-grid, a.bx-zoom, #sidebar1") || im.classList.contains("bx-nozoom")) { return; }   // bx-nozoom: photos too small to enlarge
    var a = im.closest("a");
    var isImageLink = a && /\.(jpe?g|png|gif|webp)(\?.*)?$/i.test(a.getAttribute("href") || "");
    if (a && !isImageLink) { return; }                     // linked to a web page (logos, etc.): leave alone
    if (!a) {
      var full = im.getAttribute("data-bx-full") || (im.currentSrc || im.src).replace(/-\d+x\d+(\.(jpe?g|png|gif|webp))$/i, "$1");
      a = document.createElement("a");
      a.href = full;
      im.parentNode.insertBefore(a, im);
      a.appendChild(im);
    }
    a.classList.add("bx-zoom");
    if (!im.title && im.alt) { im.title = im.alt; }
    var cell = im.closest("td");
    if (cell) { cell.setAttribute("data-bx-group", ""); }
    im.style.cursor = "zoom-in";
  });
  var links = Array.prototype.slice.call(document.querySelectorAll(".bx-grid a, a.bx-zoom"));
  if (!links.length) { return; }
  var box = document.createElement("div");
  box.className = "bx-lightbox"; box.id = "bx-lightbox"; box.setAttribute("role", "dialog"); box.setAttribute("aria-modal", "true");
  box.innerHTML = '<button type="button" class="bx-lb-nav bx-lb-prev" aria-label="Previous photo">&#8249;</button><figure><img src="" alt="" /><p></p></figure><button type="button" class="bx-lb-nav bx-lb-next" aria-label="Next photo">&#8250;</button>';
  document.body.appendChild(box);
  var img = box.querySelector("img");
  var cap = box.querySelector("p");
  var prevBtn = box.querySelector(".bx-lb-prev");
  var nextBtn = box.querySelector(".bx-lb-next");

  // a group is the nearest .bx-grid or element with data-bx-group; a lone link is its own group
  function groupOf(link) {
    var g = link.closest("[data-bx-group]");
    if (g) { return links.filter(function (l) { return l.closest("[data-bx-group]") === g; }); }
    var grid = link.closest(".bx-grid");
    if (grid) { return links.filter(function (l) { return l.closest(".bx-grid") === grid; }); }
    return [link];
  }
  var group = [], index = 0;

  // Moving to a photo: it is fetched out of sight first; once it has arrived, photo and caption change together, and
  // after the browser has drawn it, the event "bx-lightbox-shown" tells block 3i (the banner behind follows).
  // Browsers otherwise keep painting the previous photo until the new one is ready, under the new caption. If it takes
  // more than a tenth of a second (long enough for a photo already downloaded to appear without a flicker), a spinner
  // and the new caption show meanwhile.
  var want = "", pending = null, wait = 0;
  function swap(url, alt, caption, ok) {
    if (url !== want) { return; }   // passed over, or the lightbox was closed
    clearTimeout(wait);
    pending = null;
    img.src = url; img.alt = alt; cap.textContent = caption;
    box.classList.remove("is-loading");
    // two animation frames: the first is drawn with the new photo (its decoding happens in that frame), then the event
    requestAnimationFrame(function () { requestAnimationFrame(function () {
      if (url === want) { box.dispatchEvent(new CustomEvent("bx-lightbox-shown", { detail: { url: url, ok: ok } })); }
    }); });
  }
  function show(i) {
    index = (i + group.length) % group.length;
    var link = group[index];
    var thumb = link.querySelector("img");
    var url = link.href, alt = thumb ? thumb.alt : "";
    var caption = link.getAttribute("data-caption") || (thumb ? (thumb.title || "") : "");   // data-caption: banner photos (block 3i)
    var multi = group.length > 1;
    prevBtn.style.display = multi ? "" : "none";
    nextBtn.style.display = multi ? "" : "none";
    if (pending && pending.src !== url && !pending.complete) { pending.onload = pending.onerror = null; pending.removeAttribute("src"); }   // passed over: stop fetching it
    want = url;
    box.dispatchEvent(new CustomEvent("bx-lightbox-request", { detail: { url: url } }));
    clearTimeout(wait);
    var pre = pending = new Image();
    pre.onload = function () { swap(url, alt, caption, true); };
    pre.onerror = function () { swap(url, alt, caption, false); };
    pre.src = url;
    if (!pre.complete) { wait = setTimeout(function () { if (want === url) { box.classList.add("is-loading"); cap.textContent = caption; } }, 100); }
  }
  function open(link) {
    group = groupOf(link);
    show(group.indexOf(link));
    box.classList.add("is-open");
  }
  function close() {
    box.classList.remove("is-open", "is-loading");
    clearTimeout(wait);
    want = "";
    if (pending && !pending.complete) { pending.onload = pending.onerror = null; pending.removeAttribute("src"); }
    pending = null;
    img.src = "";
  }

  links.forEach(function (link) {
    link.addEventListener("click", function (event) {
      event.preventDefault();
      open(link);
    });
  });
  prevBtn.addEventListener("click", function (e) { e.stopPropagation(); show(index - 1); });
  nextBtn.addEventListener("click", function (e) { e.stopPropagation(); show(index + 1); });
  box.querySelector("figure").addEventListener("click", function (e) { e.stopPropagation(); });
  box.addEventListener("click", close);
  document.addEventListener("keydown", function (event) {
    if (!box.classList.contains("is-open")) { return; }
    if (event.key === "Escape") { close(); }
    else if (event.key === "ArrowLeft" && group.length > 1) { show(index - 1); }
    else if (event.key === "ArrowRight" && group.length > 1) { show(index + 1); }
  });
  // swipe on touch screens
  var touchX = null;
  box.addEventListener("touchstart", function (e) { touchX = e.changedTouches[0].clientX; }, { passive: true });
  box.addEventListener("touchend", function (e) {
    if (touchX === null || group.length < 2) { return; }
    var dx = e.changedTouches[0].clientX - touchX; touchX = null;
    if (dx > 40) { show(index - 1); } else if (dx < -40) { show(index + 1); }
  }, { passive: true });
})();
</script>
<?php
}, 20);

/* 3b. Any page whose content column is shorter than the sidebar beside it: pad the last cell of the last table so both
       end level. Only while the sidebar is beside the content (wider than 1024px); in the stacked tablet and phone layouts
       the sidebar is below the content, and padding by its height left a window-tall blank space at 768 to 1024px. */
add_action('wp_footer', function () {
?>
<script>
(function () {
  var aside = document.getElementById("sidebar1");
  var content = document.getElementById("content");
  var entry = document.querySelector("#content .entry");
  if (!aside || !content || !entry) { return; }
  var tables = entry.querySelectorAll("table");
  if (!tables.length) { return; }
  var cells = tables[tables.length - 1].querySelectorAll("tr:last-child td");
  var cell = cells[cells.length - 1];
  if (!cell) { return; }
  var base = parseFloat(getComputedStyle(cell).paddingBottom) || 0;
  var lastWidth = window.innerWidth;
  function align(evt) {
    if (evt && evt.type === "resize") {
      if (window.innerWidth === lastWidth) { return; }   // iOS toolbar show/hide fires resize without a width change
      lastWidth = window.innerWidth;
    }
    cell.style.paddingBottom = base + "px";
    var a = aside.getBoundingClientRect(), c = content.getBoundingClientRect();
    if (a.left < c.right - 1 && a.right > c.left + 1) { cell.style.paddingBottom = ""; return; }   // stacked: the sidebar is below, not beside
    var saved = aside.style.position;
    aside.style.position = "static";
    var asideBottom = aside.getBoundingClientRect().bottom;
    var contentBottom = content.getBoundingClientRect().bottom;
    aside.style.position = saved;
    var diff = Math.round(asideBottom - contentBottom);
    if (diff > 0) { cell.style.paddingBottom = (base + diff) + "px"; }
  }
  align();
  window.addEventListener("resize", align);
  window.addEventListener("load", align);
})();
</script>
<?php
}, 21);

/* 3c. Carousel: the theme wraps every slide in an empty link that reloads the page; remove those links.
       Also: slide loading in viewing order, and arrows that restart the slideshow timer. */
add_action('wp_footer', function () {
?>
<script>
(function () {
  document.querySelectorAll("#slideshow a[href='']").forEach(function (a) {
    var parent = a.parentNode;
    while (a.firstChild) { parent.insertBefore(a.firstChild, a); }
    parent.removeChild(a);
  });

  // Slides: the page starts on a random slide (chosen in <head>, shown by the script at the end of the header,
  // which also fetches the other slides as soon as that one arrives). A slide clicked to before its turn is
  // fetched right then.
  var spans = Array.prototype.slice.call(document.querySelectorAll("#slideshow .bwg_slider > span"));
  var n = spans.length;
  function load(i, done) {
    var img = n ? spans[((i % n) + n) % n].querySelector("img") : null;
    if (!img || !img.getAttribute("data-src")) { if (done) { done(); } return; }
    if (done) { img.addEventListener("load", done, { once: true }); img.addEventListener("error", done, { once: true }); }
    if (img.getAttribute("data-srcset")) { img.srcset = img.getAttribute("data-srcset"); img.removeAttribute("data-srcset"); }
    img.src = img.getAttribute("data-src");
    img.removeAttribute("data-src");
  }
  // The theme's fade only gives the incoming slide a transition and relies on the outgoing one still having one
  // from an earlier fade. The start slide never had one, so on the first change it vanished at once and the next
  // photo faded in from the navy background. Giving every slide the theme's own fade length makes it a crossfade.
  var faded = false;
  function crossfade() {
    if (faded) { return; }
    faded = true;
    var ms = parseInt(window.wdwts_transition_duration, 10) || 800;
    spans.forEach(function (s) { if (!s.style.transition) { s.style.transition = "opacity " + ms + "ms linear"; } });
  }
  var original = window.wdwt_bwg_change_image;
  if (typeof original === "function") {
    window.wdwt_bwg_change_image = function (currentKey, key) {
      load(parseInt(key, 10));
      crossfade();
      return original.apply(this, arguments);
    };
  }

  // Arrows: a click also restarts the countdown to the next automatic change, so an automatic change never
  // lands right after a manual one. Always, even with the theme's "pause on hover" on: that pause only covers
  // the photo itself, and moving onto an arrow counts as leaving it, so the countdown is running while you click.
  ["spider_slideshow_left", "spider_slideshow_right"].forEach(function (id) {
    var arrow = document.getElementById(id);
    if (!arrow) { return; }
    arrow.addEventListener("click", function () {
      if (typeof window.play !== "function") { return; }
      window.play();
    });
  });
})();
</script>
<?php
}, 22);

/* Banner photo widths, as laid out by the theme and theme-fixes.css: the full window less its margins on phones,
   748px on tablets, three quarters of the window on desktop. The browser picks the smallest copy that covers this. */
define('BX_BANNER_SIZES', '(max-width: 767px) calc(100vw - 20px), (max-width: 1199px) 748px, 75vw');

/* The attachment an uploads address belongs to: the file itself, the original of an image WordPress scaled down
   (name-scaled.jpg is the attachment, name.jpg its original), or one of its resized copies (name-768x512.jpg).
   bx_upload_attachment_ids() looks up every address on a page in one database query and loads those attachments'
   details in two more, instead of up to three queries per photo plus two more each for its details. */
function bx_upload_attachment_ids($urls) {
    static $cache = array();
    global $wpdb;
    $up    = wp_get_upload_dir();
    $bhost = wp_parse_url($up['baseurl'], PHP_URL_HOST);
    $bpath = rtrim((string) wp_parse_url($up['baseurl'], PHP_URL_PATH), '/') . '/';
    $want  = array();   // address => candidate paths inside uploads, best first
    foreach (array_unique($urls) as $url) {
        if (isset($cache[$url])) { continue; }
        $cache[$url] = 0;
        $host = wp_parse_url($url, PHP_URL_HOST);
        $path = (string) wp_parse_url($url, PHP_URL_PATH);
        if (($host && $host !== $bhost) || strpos($path, $bpath) !== 0) { continue; }
        $rel  = rawurldecode(substr($path, strlen($bpath)));
        $base = preg_replace('/-\d+x\d+(\.[a-z0-9]+)$/i', '$1', $rel);
        $want[$url] = array_unique(array($rel, $base, preg_replace('/(\.[a-z0-9]+)$/i', '-scaled$1', $base)));
    }
    if ($want) {
        $paths = array_unique(call_user_func_array('array_merge', array_values($want)));
        $rows  = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value IN ("
            . implode(',', array_fill(0, count($paths), '%s')) . ')', $paths));
        $found = array(); $loose = array();   // the database ignores letter case; an exact match is preferred
        foreach ($rows as $r) { $found[$r->meta_value] = (int) $r->post_id; $loose[strtolower($r->meta_value)] = (int) $r->post_id; }
        foreach ($want as $url => $tries) {
            foreach ($tries as $t) { if (isset($found[$t])) { $cache[$url] = $found[$t]; continue 2; } }
            foreach ($tries as $t) { if (isset($loose[strtolower($t)])) { $cache[$url] = $loose[strtolower($t)]; continue 2; } }
        }
        if ($found) { _prime_post_caches(array_values($found), false, true); }
    }
    $out = array();
    foreach ($urls as $url) { $out[$url] = $cache[$url]; }
    return $out;
}
function bx_upload_attachment_id($url) {
    $ids = bx_upload_attachment_ids(array($url));
    return $ids[$url];
}
/* srcset for a banner photo: every copy WordPress made with the same proportions, plus the full size */
function bx_banner_srcset($url) {
    $id = bx_upload_attachment_id($url);
    if (!$id) { return ''; }
    $meta = wp_get_attachment_metadata($id);
    if (empty($meta['width'])) { return ''; }
    $set = wp_calculate_image_srcset(array((int) $meta['width'], (int) $meta['height']), wp_get_attachment_url($id), $meta, $id);
    return $set ? $set : '';
}

/* 3d. Rewrite the header in PHP before the page is sent, so the browser never has to move anything:
      correct social tooltips, two-line title, moon button in the top row. The JS above is now only a fallback. */
add_action('template_redirect', function () {
    if (is_admin() || is_feed() || (defined('REST_REQUEST') && REST_REQUEST)) { return; }
    ob_start(function ($page) {
        $html = $page;
        $html = str_replace('title="Pinterest"', 'title="Discord" aria-label="Discord"', $html);
        $html = str_replace('title="LinkEdin"', 'title="LinkedIn" aria-label="LinkedIn"', $html);
        // footer: current year, Baltimore Sister Cities link, Chinese line (all inside the existing copyright line)
        $y = date('Y');
        // the footer line is the theme's <span id="copyright">; searching the whole page for the last "Copyright" also
        // found the copy of the footer text inside the Customizer preview's settings script, and broke that script
        $anchor = strpos($html, '<span id="copyright">');
        $cpos = $anchor !== false ? strpos($html, 'Copyright', $anchor) : false;
        if ($cpos !== false) {
            $orig = substr($html, $cpos, 80);
            $seg  = preg_replace('~^(Copyright\s*(?:&copy;|&#169;|\xC2\xA9)\s*)\d{4}~', '${1}' . $y, $orig, 1);
            if (!is_string($seg) || $seg === '') { $seg = $orig; }   // never drop text if the year swap fails
            $html = substr($html, 0, $cpos) . $seg . substr($html, $cpos + 80);
            $bsc = 'Baltimore Sister Cities, Inc.';
            $bpos = strpos($html, $bsc, $cpos);
            if ($bpos !== false && $bpos - $cpos < 400) {
                $after = $bpos + strlen($bsc);
                if (substr($html, $after, 1) === '.') { $after++; }   // keep a sentence-ending period with the English line
                $link  = '<a href="https://baltimoresistercities.org/" target="_blank" rel="noopener">' . $bsc . '</a>';
                $extra = '<br /><span lang="zh-Hans">版权所有 © ' . $y . ' 巴尔的摩–厦门姐妹城委员会，巴尔的摩姐妹城市公司（Baltimore Sister Cities, Inc.）成员。</span>';
                $html = substr($html, 0, $bpos) . $link . substr($html, $bpos + strlen($bsc), $after - $bpos - strlen($bsc)) . $extra . substr($html, $after);
            }
        }
        $html = preg_replace_callback('#<p id="site_desc">([^<]*?),\s*([^<]*)</p>#', function ($m) {
            return '<p id="site_desc">' . trim($m[1]) . '<span class="bx-tagline2">' . trim($m[2]) . '</span></p>';
        }, $html, 1);
        $moon = '<div class="bx-theme-toggle"><button type="button" id="bx-theme-btn" aria-pressed="false" aria-label="Switch to dark mode" title="Dark mode"><svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79z"/></svg></button></div>';
        $pos = strpos($html, 'id="social"');
        if ($pos !== false) {
            $end = strpos($html, '</ul>', $pos);
            if ($end !== false) { $html = substr_replace($html, '</ul>' . $moon, $end, 5); }
        }
        // Font: Open Sans is self-hosted (see block 7); remove the theme's Google Fonts link and its webfont.js loader.
        $html = preg_replace('#<link rel="stylesheet" href="https://fonts\.googleapis\.com/[^>]*>#', '', $html);
        $html = preg_replace('#<script src="https://ajax\.googleapis\.com/ajax/libs/webfont/1/webfont\.js"[^>]*></script>#', '', $html);
        $html = preg_replace('#<script>\s*WebFontConfig = \{.*?\}\)\(\);\s*</script>#s', '', $html, 1);

        // Logo and first slide: fetch at high priority and preload from <head>, so they are not the last things to arrive.
        $preloads = '';
        if (preg_match('#<img id="site-title"[^>]*src="([^"]+)"[^>]*>#', $html, $lm)) {
            $html = str_replace($lm[0], str_replace('<img ', '<img fetchpriority="high" ', $lm[0]), $html);
            $preloads .= '<link rel="preload" as="image" href="' . esc_url($lm[1]) . '" fetchpriority="high">' . "\n";
        }
        if ($preloads !== '') { $html = preg_replace('#</head>#', $preloads . '</head>', $html, 1); }
        // Size the slideshow the moment its markup has been parsed, with the theme's own function, so the later
        // window.load call computes the same number and nothing moves.
        $html = preg_replace('#</header>#', '<script>if (typeof wdwt_slider_resize === "function") { wdwt_slider_resize(); }</script></header>', $html, 1);

        // Carousel: start on a random slide, then go in order from it. The page itself is cached by Nginx, so the
        // choice is made in the visitor's browser: every slide's real address goes into data-src (the browser
        // fetches nothing yet), a script early in <head> picks the slide and preloads it at high priority,
        // and a script at the end of the header shows that slide in place of slide 1 before anything is painted.
        // Only the theme's slideshow is touched: it sits in the header, before any page content.
        $hend  = strpos($html, '</header>');
        $start = strpos($html, 'id="slideshow"');
        $stop  = $start !== false ? strpos($html, 'SLIDESHOW END', $start) : false;
        if ($start !== false && $stop !== false && $hend !== false && $stop < $hend) {
            $segment = substr($html, $start, $stop - $start);
            $uploads = preg_replace('#^https?:#', '', wp_get_upload_dir()['baseurl']) . '/';
            $urls = array(); $sets = array();
            if (preg_match_all('#<img[^>]*class="bwg_slideshow_image"[^>]*>#', $segment, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[0] as $img) {
                    $u = preg_match('# src="([^"]+)"#', $img[0], $uu) ? html_entity_decode($uu[1]) : '';
                    if (strpos(preg_replace('#^https?:#', '', $u), $uploads) !== 0) { $urls = array(); break; }   // not a photo from this site: leave the slideshow alone
                    $urls[] = $u;
                }
            }
            if ($urls) {
                $placeholder = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
                bx_upload_attachment_ids($urls);   // all slides in one lookup
                foreach ($urls as $u) { $sets[] = bx_banner_srcset($u); }
                $originals = 0;
                for ($i = count($m[0]) - 1; $i >= 0; $i--) {
                    $tag = $m[0][$i][0];
                    $extra = $sets[$i] !== '' ? ' data-srcset="' . esc_attr($sets[$i]) . '" sizes="' . BX_BANNER_SIZES . '"' : '';
                    $deferred = str_replace(' src="', $extra . ' src="' . $placeholder . '" data-src="', $tag);
                    $segment = substr_replace($segment, $deferred, $m[0][$i][1], strlen($tag));
                    // block 3i: a slide whose uncropped photo is on the server links to it, for the lightbox; the theme's
                    // empty link around the slide (removed by block 3c otherwise) becomes that link
                    $orig = bx_slide_original_url($urls[$i]);
                    $apos = $orig !== '' ? strrpos(substr($segment, 0, $m[0][$i][1]), '<a href="">') : false;
                    if ($apos !== false && trim(substr($segment, $apos + 11, $m[0][$i][1] - $apos - 11)) === '') {
                        $aid = bx_upload_attachment_id($urls[$i]);   // the slide's Caption in the Media Library is the lightbox caption
                        $cap = $aid ? trim(wp_strip_all_tags((string) wp_get_attachment_caption($aid))) : '';
                        $segment = substr_replace($segment, '<a href="' . esc_url($orig) . '" class="bx-zoom bx-slide-original" aria-label="Open the full photo"'
                            . ($cap !== '' ? ' data-caption="' . esc_attr($cap) . '"' : '') . '>', $apos, 11);
                        $originals++;
                    }
                }
                if ($originals) { $segment = preg_replace('#^id="slideshow"#', 'id="slideshow" data-bx-group=""', $segment, 1); }   // one lightbox group
                $html = substr_replace($html, $segment, $start, $stop - $start);
                if ($originals && ($bpos = strripos($html, '</body>')) !== false) { $html = substr_replace($html, bx_slide_originals_script(), $bpos, 0); }
                // the preload asks for the same size the photo will use; a browser that cannot preload by srcset skips the
                // preload rather than fetch the full-size file and then a second, smaller one
                $js = JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP;   // no value can close the script tag
                $pick = '<script>(function(){var s=' . wp_json_encode(array_values($urls), $js) . ',ss=' . wp_json_encode(array_values($sets), $js) . ';var k=Math.floor(Math.random()*s.length);window.bxSlideStart=k;'
                      . 'var l=document.createElement("link");l.rel="preload";l.as="image";if(ss[k]){if(!("imageSrcset" in l)){return;}l.imageSrcset=ss[k];l.imageSizes=' . wp_json_encode(BX_BANNER_SIZES, $js) . ';}l.href=s[k];l.setAttribute("fetchpriority","high");document.head.appendChild(l);})();</script>' . "\n";
                // placed right after the page's viewport tags: until a phone has read them it takes the page to be 980px
                // wide, and the preload would pick a copy for that width (on a 3x phone the 2560px file, and then the
                // slide itself a second, smaller one). Still in <head>, before anything is drawn.
                $he = stripos($html, '</head>');
                $vp = $he !== false ? strripos(substr($html, 0, $he), '<meta name="viewport"') : false;
                $ve = $vp !== false ? strpos($html, '>', $vp) : false;
                if ($ve !== false) { $html = substr_replace($html, "\n" . $pick, $ve + 1, 0); }
                else { $html = preg_replace_callback('#<head(\s[^>]*)?>#i', function ($h) use ($pick) { return $h[0] . "\n" . $pick; }, $html, 1); }
                $show = '<script>(function(){var sp=document.querySelectorAll("#slideshow .bwg_slider > span"),n=sp.length,k=window.bxSlideStart||0;if(!n){return;}if(k>=n){k=0;}'
                      // every slide whose photo has not arrived shows a placeholder box (navy, with a small spinner) until it does
                      . 'Array.prototype.forEach.call(sp,function(s){var im=s.querySelector("img");if(!im){return;}function done(){if(im.naturalWidth>1||!im.getAttribute("data-src")&&im.complete){s.classList.remove("bx-loading");}}'
                      . 'if(im.getAttribute("data-src")||!im.complete||im.naturalWidth<2){s.classList.add("bx-loading");}im.addEventListener("load",done);im.addEventListener("error",function(){s.classList.remove("bx-loading");});});'
                      . 'function ld(i){var im=sp[((i%n)+n)%n].querySelector("img[data-src]");if(im){var ss=im.getAttribute("data-srcset");if(ss){im.srcset=ss;im.removeAttribute("data-srcset");}im.src=im.getAttribute("data-src");im.removeAttribute("data-src");}return im;}'
                      . 'if(k!==0){sp[0].style.opacity=0;sp[0].style.zIndex=1;sp[k].style.opacity=1;sp[k].style.zIndex=2;'
                      . 'var c=document.getElementById("bwg_current_image_key");if(c){c.value=k;}'
                      . 'if(window.data&&data[k]){[[".bwg_slideshow_title_text","alt"],[".bwg_slideshow_description_text","description"]].forEach(function(p){var el=document.querySelector(p[0]);if(el){el.innerHTML=data[k][p[1]]||"";el.classList.toggle("none",!el.innerHTML);}});}}'
                      . 'window.wdwts_current_key=k;'
                      // the theme's window-focus handler (just above) always records slide 0: its counter never goes up. Firefox
                      // fires focus while the page is still loading, so it is replaced here, right after the theme sets it up,
                      // with one that does the same job correctly: restart the timer unless paused, and record the slide on top
                      . 'if(window.jQuery){jQuery(window).off("focus").on("focus",function(){if(!jQuery(".bwg_ctrl_btn").hasClass("fa-play")&&typeof window.play==="function"){window.play();}'
                      . 'for(var i=0;i<n;i++){if(getComputedStyle(sp[i]).zIndex==="2"){jQuery("#bwg_current_image_key").val(i);return;}}});}'   // the theme starts this at "" and skips any move to slide 0 while it is "" ("" == 0 in JavaScript)
                      // once the start photo is in, fetch the rest right away (not at the page's load event, which waits for everything
                      // else on the page): nearest first, alternating right and left, four at a time (browsers open up to six
                      // connections to one site, which leaves two for the rest of the page)
                      . 'var q=[],d;for(d=1;q.length<n-1;d++){[k+d,k-d].forEach(function(i){i=((i%n)+n)%n;if(i!==k&&q.indexOf(i)<0&&q.length<n-1){q.push(i);}});}'
                      . 'function nx(){if(!q.length){return;}var im=ld(q.shift());if(im&&!im.complete){im.addEventListener("load",nx,{once:true});im.addEventListener("error",nx,{once:true});}else{nx();}}'
                      . 'function go(){nx();nx();nx();nx();}var im=ld(k);if(im&&!im.complete){im.addEventListener("load",go,{once:true});im.addEventListener("error",go,{once:true});}else{go();}})();</script>';
                $html = preg_replace_callback('#</header>#', function () use ($show) { return $show . '</header>'; }, $html, 1);
            }
        }
        // dead outside links: the Internet Archive's copy instead (block 20, links/links.php)
        if (is_string($html) && function_exists('bx_lk_rewrite')) { $html = bx_lk_rewrite($html); }
        // a step that fails (a regular expression that hits PHP's limits returns nothing) must not blank the page, which
        // the page cache would then keep: send the page as WordPress made it instead
        return (is_string($html) && $html !== '') ? $html : $page;
    });
}, 1);

/* 3e. Mobile menu: each dropdown gets a first item that links to the parent page (tapping the parent row only opens the
      dropdown). The theme injects such a copy late for some parents; this relabels it when it appears, creates one where the
      theme never does, and never leaves both. */
add_action('wp_footer', function () {
?>
<script>
(function () {
  var labels = { "About Us": "About the Organization", "Programs": "Overview of Programs" };
  function fix() {
    document.querySelectorAll("#top-nav li.haschild, #top-nav li.menu-item-has-children").forEach(function (li) {
      var parent = li.querySelector(":scope > a");
      var sub = li.querySelector(":scope > ul");
      if (!parent || !sub) { return; }
      var name = parent.textContent.trim();
      var label = labels[name] || (name + " overview");
      var key = function (s) { return s.replace(/[^a-z]/gi, "").toLowerCase(); };
      var themeCopy = null;
      sub.querySelectorAll(":scope > li > a, :scope > a").forEach(function (a) {
        if (!themeCopy && !a.classList.contains("bx-parent-link") && key(a.textContent) === key(name)) { themeCopy = a; }
      });
      var created = sub.querySelector(":scope > li.bx-parent-item");
      if (themeCopy) {
        themeCopy.classList.add("bx-parent-link");
        themeCopy.textContent = label;
        if (created && created !== themeCopy.parentNode) { created.parentNode.removeChild(created); }
        return;
      }
      if (!sub.querySelector(":scope > li > a.bx-parent-link")) {
        var item = document.createElement("li");
        item.className = "menu-item bx-parent-item";
        var a = document.createElement("a");
        a.href = parent.getAttribute("href");
        a.className = "bx-parent-link";
        a.textContent = label;
        item.appendChild(a);
        sub.insertBefore(item, sub.firstChild);
      }
    });
  }
  fix();
  window.addEventListener("load", fix);
  setTimeout(fix, 400);
  setTimeout(fix, 1500);
  // whatever event the theme uses (click on desktop, touch on phones), react to the DOM change itself
  var nav = document.getElementById("top-nav");
  if (nav && window.MutationObserver) {
    var busy = false;
    new MutationObserver(function () {
      if (busy) { return; }
      busy = true;
      setTimeout(function () { fix(); busy = false; }, 0);
    }).observe(nav, { childList: true, subtree: true });
  }
  ["click", "touchend"].forEach(function (evt) {
    var btn = document.getElementById("menu-button-block");
    if (btn) { btn.addEventListener(evt, function () { setTimeout(fix, 50); setTimeout(fix, 300); }, { passive: true }); }
    document.querySelectorAll("#top-nav li.haschild > a").forEach(function (a) { a.addEventListener(evt, function () { setTimeout(fix, 50); setTimeout(fix, 300); }, { passive: true }); });
  });
})();
</script>
<?php
}, 23);

/* 3f. Desktop dropdown menus open on hover with CSS (theme-fixes.css), not with the theme's script. The theme slid each
       dropdown open and shut with jQuery; with the pointer resting near a dropdown's growing or shrinking edge, every
       step of the animation moved the edge across the pointer and started the opposite animation, so the menu
       flickered open and shut until the pointer moved. Removing the theme's hover handlers (they only ran on screens
       wider than 1024px; the tap-to-open phone and tablet menu is a separate handler and stays) leaves the CSS rules,
       which show a dropdown while its menu item is hovered or holds keyboard focus and unroll it with a mask, which
       changes what is drawn but not the dropdown's hover area, so nothing moves under the pointer. */
add_action('wp_footer', function () {
?>
<script>
jQuery(function ($) { $("#top-nav > div > ul li, #top-nav > div > div > ul li").off("mouseenter mouseleave"); });
</script>
<?php
}, 22);

/* 3g. The theme's resize handlers (its layout switch in responsive.js and the home carousel's sizing) depend only on the
       window's width, but ran on every resize, including the height-only ones a phone fires when its browser bar
       slides back in, which is what happens when scrolling up again after reaching the bottom of a page. On the home
       page that work cost a dropped frame at that moment (measured on a phone-sized window at 6x CPU slowdown). They
       now run only when the width changes. This wraps jQuery's resize dispatch, which only the theme uses here. */
add_action('wp_footer', function () {
?>
<script>
(function ($) {
  if (!$ || !$.event) { return; }
  var lastWidth = window.innerWidth;
  $.event.special.resize = $.event.special.resize || {};
  $.event.special.resize.handle = function (event) {
    if (event.bxWidthChanged === undefined) { event.bxWidthChanged = window.innerWidth !== lastWidth; lastWidth = window.innerWidth; }
    if (!event.bxWidthChanged) { return; }
    return event.handleObj.handler.apply(this, arguments);
  };
})(window.jQuery);
</script>
<?php
}, 22);

/* 3h. Menu bar on one row. From 768px wide up the menu is a bar of links, and the theme let the links wrap onto a second
       row when they did not fit (with the current six links: at every width from 768 to 1082px). theme-fixes.css keeps
       them in one row; this script makes the top-level link text smaller, only as much as needed, until the row fits
       the bar. The space around each link stays the same, and dropdown links keep their size. The script is printed
       right after the menu, so it runs before the browser first draws the bar; it runs again whenever the bar's width
       changes and when the web font finishes loading (the font changes how wide the words are). */
add_filter('wp_nav_menu', function ($html, $args) {
    static $done = false;
    if ($done || !is_object($args) || ($args->theme_location ?? '') !== 'primary-menu' || get_template() !== 'news-magazine') { return $html; }
    $done = true;
    return $html . <<<'HTML'

<script>
(function () {
  var bar = document.getElementById("top-nav-list");
  if (!bar || !window.matchMedia) { return; }
  var wide = window.matchMedia("(min-width: 768px)");   // narrower than this, the menu is the phone list, not a bar
  var items = [], links = [];
  for (var i = 0; i < bar.children.length; i++) {
    var a = bar.children[i].firstElementChild;
    if (a && a.tagName === "A") { items.push(bar.children[i]); links.push(a); }
  }
  if (!links.length) { return; }
  // pixels by which the row of links runs past the right end of the bar (zero or less: it fits)
  function overflow() { return items[items.length - 1].getBoundingClientRect().right - bar.getBoundingClientRect().right; }
  function fit() {
    links.forEach(function (a) { a.style.fontSize = ""; });   // back to the stylesheet's size
    if (!wide.matches) { return; }
    var over = overflow();
    if (over <= 0) { return; }
    // only the words shrink, so the words alone must give up the overflow: scale them by (words - overflow) / words
    var words = 0, base = [];
    links.forEach(function (a) {
      var s = getComputedStyle(a);
      words += a.getBoundingClientRect().width - parseFloat(s.paddingLeft) - parseFloat(s.paddingRight);
      base.push(parseFloat(s.fontSize));
    });
    var scale = (words - over) / words;
    // text width is almost, not exactly, proportional to font size: check, and step down a little until it fits
    for (var n = 0; n < 40 && scale > 0.05; n++) {
      links.forEach(function (a, k) { a.style.fontSize = Math.floor(base[k] * scale * 10) / 10 + "px"; });
      if (overflow() <= 0) { return; }
      scale -= 0.005;
    }
  }
  fit();
  if (window.ResizeObserver) { new ResizeObserver(fit).observe(bar); } else { window.addEventListener("resize", fit); }
  if (document.fonts) {
    if (document.fonts.addEventListener) { document.fonts.addEventListener("loadingdone", fit); }
    if (document.fonts.ready) { document.fonts.ready.then(fit); }
  }
  window.addEventListener("load", fit);
})();
</script>
HTML;
}, 10, 2);

/* 3i. Banner photos open uncropped in the lightbox. The banner shows cropped copies (a wide strip); the uncropped photos
       are plain files in wp-content/uploads/carousel-originals/, named after the slide without WordPress's endings:
       the slide carousel2-cropped-scaled.jpg opens carousel2.jpg (any of .jpg .jpeg .png .webp). A slide with no such
       file is not clickable. They are not in the Media Library, so WordPress makes no resized copies of them.
       Clicking a slide opens its photo in the same lightbox as the page photos, with the other banner photos one
       arrow away. The photos are large (3 to 13 MB), so none is fetched with the page: once the page and every
       banner slide have finished loading, they download one at a time, at low priority, starting with the slide on
       screen and then its neighbors on both sides (the one the lightbox shows, while it is open), and only while no
       photo of the page itself is downloading (one further down starts as the visitor scrolls). A slide clicked
       before its photo has arrived is fetched right then, and a background download of a different photo is stopped
       until it has; meanwhile the lightbox shows a spinner instead of the previous photo. The banner behind moves to
       a photo when the lightbox has drawn it, so both change in the same moment.
       Captions: the slide's Caption field in the Media Library (Media > Library > carousel2-cropped > Caption).
       While the lightbox shows banner photos the slideshow holds still and follows it: moving left or right in the
       lightbox moves the banner to the same photo, and on closing the banner plays on from the photo the lightbox
       was showing. Photos from page content in the lightbox leave the slideshow alone. */
function bx_slide_original_url($slide_url) {
    static $files = null;
    $up = wp_get_upload_dir();
    if ($files === null) {
        $files = array();
        $dir = $up['basedir'] . '/carousel-originals';
        foreach (is_dir($dir) ? (array) scandir($dir) : array() as $f) {
            if (preg_match('/^(.+)\.(jpe?g|png|webp)$/i', $f, $fm)) { $files[strtolower($fm[1])] = $f; }
        }
    }
    $name = strtolower(pathinfo((string) wp_parse_url($slide_url, PHP_URL_PATH), PATHINFO_FILENAME));
    $name = preg_replace('/(-cropped)?(-scaled)?(-\d+x\d+)?$/', '', $name);   // carousel2-cropped-scaled -> carousel2
    return isset($files[$name]) ? $up['baseurl'] . '/carousel-originals/' . rawurlencode($files[$name]) : '';
}
/* the script for these links; block 3d adds it at the end of the page, only on pages with such a link */
function bx_slide_originals_script() {
    return <<<'HTML'
<script>
(function () {
  var links = Array.prototype.slice.call(document.querySelectorAll("#slideshow a.bx-slide-original"));
  if (!links.length) { return; }
  var spans = Array.prototype.slice.call(document.querySelectorAll("#slideshow .bwg_slider > span"));
  var slides = spans.map(function (s) { return s.querySelector("img"); });
  var box = document.getElementById("bx-lightbox");
  var urls = links.map(function (a) { return a.href; });
  function onScreen() {   // the slide the theme shows now
    var c = document.getElementById("bwg_current_image_key");
    var k = c ? parseInt(c.value, 10) : 0;
    return isNaN(k) || k < 0 || k >= spans.length ? 0 : k;
  }

  // keyboard: only the slide on screen is a Tab stop (the others are invisible)
  function tabStops() {
    var top = spans[onScreen()];
    links.forEach(function (a) { a.tabIndex = top && top.contains(a) ? 0 : -1; });
  }
  tabStops();
  var change = window.wdwt_bwg_change_image;
  if (typeof change === "function") {
    window.wdwt_bwg_change_image = function () { var r = change.apply(this, arguments); tabStops(); return r; };
  }

  // background downloads, one at a time, after everything else
  var have = {};          // photos the browser has (or that failed: not retried)
  var bg = null;          // the background download in progress
  var running = false;
  var asked = "";         // a photo the visitor moved to in the lightbox, not on screen yet: background downloads wait
  function waitingForClick() { return asked !== ""; }
  // the page's own photos come first: one that is downloading right now (a photo further down starts when the visitor
  // scrolls toward it) makes background downloads wait, and stops one under way; it is fetched again later
  var lbImg = box ? box.querySelector("img") : null;
  function pageBusy() {
    var h = window.innerHeight;
    for (var i = 0; i < document.images.length; i++) {
      var im = document.images[i];
      if (im === lbImg || im.complete) { continue; }
      var src = im.currentSrc || im.getAttribute("src") || "";
      if (!src || src.indexOf("data:") === 0) { continue; }   // a banner slide not asked for yet
      if (im.currentSrc || im.getAttribute("loading") !== "lazy") { return true; }   // arriving, or on its way
      // a lazy photo: browsers fetch it once it is within about 1250px of the screen (no page tells when it started)
      var r = im.getBoundingClientRect();
      if ((r.width || r.height) && r.bottom > -1250 && r.top < h + 1250) { return true; }
    }
    return false;
  }
  var watch = setInterval(function () {
    if (!running) { return; }
    if (pageBusy()) {
      if (bg) { bg.im.onload = bg.im.onerror = null; bg.im.removeAttribute("src"); bg = null; }
    } else if (!bg) { next(); }
  }, 400);
  function next() {
    if (!running || bg || waitingForClick() || pageBusy()) { return; }
    // the slide on screen first, then its neighbors on both sides, nearest first, so that the photo one arrow away
    // in the lightbox (either way) is usually here already
    var k = onScreen(), n = spans.length, url = null;
    for (var d = 0; d < n && !url; d++) {
      var sides = d === 0 ? [k] : [k + d, k - d];
      for (var t = 0; t < sides.length && !url; t++) {
        var a = spans[((sides[t] % n) + n) % n].querySelector("a.bx-slide-original");
        if (a && !have[a.href]) { url = a.href; }
      }
    }
    if (!url) { clearInterval(watch); return; }   // all here: nothing left to watch for
    var im = new Image();
    im.fetchPriority = "low";
    bg = { url: url, im: im };
    im.onload = im.onerror = function () { have[url] = true; bg = null; next(); };
    im.src = url;
  }
  // the slideshow follows the lightbox: held still from the moment a banner photo is asked for, moved to each banner
  // photo when the lightbox puts it on screen (at once, no crossfade: it is behind the lightbox), so photo and banner
  // change together, and playing again from that photo on closing
  var slideshow = document.getElementById("slideshow");
  var slideOf = {};
  links.forEach(function (a) { slideOf[a.href] = spans.indexOf(a.closest(".bwg_slider > span")); });
  var held = false;
  var play = window.play;
  if (typeof play === "function") {
    // the theme restarts its timer when the pointer leaves the banner or the window regains focus; not while held
    window.play = function () { if (held) { return; } return play.apply(this, arguments); };
  }
  function hold() { if (!held) { held = true; clearInterval(window.wdwts_playInterval); slideshow.classList.add("bx-held"); } }
  function release() {
    if (!held) { return; }
    held = false;
    slideshow.classList.remove("bx-held");
    if (typeof window.play === "function") { window.play(); }   // a full interval on this photo, then on as usual
  }
  if (box) {
    box.addEventListener("bx-lightbox-request", function (e) {
      var url = e.detail.url;
      asked = url;
      if (slideOf.hasOwnProperty(url)) { hold(); }
      // one of these, not here yet: it goes first, and a background download of another photo stops
      if (urls.indexOf(url) !== -1 && !have[url] && bg && bg.url !== url) {
        bg.im.onload = bg.im.onerror = null;
        bg.im.removeAttribute("src");   // fetched again later
        bg = null;
      }
    });
    box.addEventListener("bx-lightbox-shown", function (e) {
      var url = e.detail.url;
      if (asked === url) { asked = ""; }
      if (e.detail.ok && urls.indexOf(url) !== -1) { have[url] = true; }
      var k = slideOf.hasOwnProperty(url) ? slideOf[url] : -1;
      if (k >= 0 && k !== onScreen() && typeof window.wdwt_bwg_change_image === "function" && window.data && window.data[k]) {
        window.wdwt_bwg_change_image(onScreen(), k, window.data);
      }
      next();
    });
    new MutationObserver(function () {
      if (!box.classList.contains("is-open")) { asked = ""; release(); next(); }
    }).observe(box, { attributes: true, attributeFilter: ["class"] });
  }
  // start once the page has loaded and every banner slide is in (they load after the page's first photo; give up
  // waiting for them after 30 seconds)
  var since = 0;
  function whenSlidesAreIn() {
    var pending = slides.some(function (im) { return im && (im.hasAttribute("data-src") || !im.complete); });
    if (pending && (since += 250) < 30000) { setTimeout(whenSlidesAreIn, 250); return; }
    var go = function () { running = true; next(); };
    if (window.requestIdleCallback) { requestIdleCallback(go, { timeout: 2000 }); } else { setTimeout(go, 0); }
  }
  if (document.readyState === "complete") { whenSlidesAreIn(); } else { window.addEventListener("load", whenSlidesAreIn); }
})();
</script>
HTML;
}

/* 3j. Reflections with a photo (Youth Ambassadors year pages, components.css): from 600px wide the quote's rule runs at
       least the photo's height and a shorter quote is centered on the photo. Stylesheets cannot size one column by a
       photo in the other while the caption stays under the photo, so the photo's drawn height (which changes with the
       column width) is measured here and handed over as --bx-photo-h. Without the script the quote starts level with
       the photo's top. Printed only on pages whose content has such a reflection. */
add_action('wp_footer', function () {
    $post = is_singular() ? get_queried_object() : null;
    if (!($post instanceof WP_Post) || strpos($post->post_content, 'bx-reflection') === false || stripos($post->post_content, '<figure') === false) { return; }
?>
<script>
(function () {
  var photos = [];
  document.querySelectorAll(".bx-reflection > figure img").forEach(function (im) { photos.push(im); });
  function hand(im, h) { im.closest(".bx-reflection").style.setProperty("--bx-photo-h", h + "px"); }
  photos.forEach(function (im) { hand(im, im.getBoundingClientRect().height); });   // now, before the first paint
  if (!photos.length || !("ResizeObserver" in window)) { return; }
  var ro = new ResizeObserver(function (entries) {   // a photo's box changed size (window resized, phone turned)
    entries.forEach(function (e) { hand(e.target, e.contentRect.height); });
  });
  photos.forEach(function (im) { ro.observe(im); });
})();
</script>
<?php
}, 20);

/* 4. XML-RPC is unused on this site; turning it off removes a login brute-force surface. That setting only covers
      the methods that need a login, so the pingback methods are removed too (pings are closed on every post, so
      they could only ever answer "no"), and the head no longer advertises the remote-publishing address (RSD) that
      nothing can sign in to. (system.multicall stays: WordPress's XML-RPC server adds it back itself; with logins
      off it has nothing to bundle.) */
add_filter('xmlrpc_enabled', '__return_false');
add_filter('xmlrpc_methods', function ($methods) {
    unset($methods['pingback.ping'], $methods['pingback.extensions.getPingbacks']);
    return $methods;
});
remove_action('wp_head', 'rsd_link');

/* 4b. Security headers on the site's pages (Nginx stores them with each cached page and sends them with it):
       - X-Content-Type-Options: browsers take every response as the type the server says, never guess another;
       - X-Frame-Options and CSP frame-ancestors: other sites cannot show these pages inside a frame of theirs
         (clickjacking: a framed page under a disguised layer, so a click lands on something else). The site's
         own frames are unaffected (the Customizer preview), and so are WordPress embeds (/embed/ addresses, made to
         be shown on other sites), which get neither header. Constant values, so cached pages stay identical. */
add_filter('wp_headers', function ($headers, $wp) {
    $headers += array('X-Content-Type-Options' => 'nosniff');   // += : a value something else already set stays
    if (empty($wp->query_vars['embed'])) { $headers += array('X-Frame-Options' => 'SAMEORIGIN', 'Content-Security-Policy' => "frame-ancestors 'self'"); }
    return $headers;
}, 10, 2);

/* 5. The emoji polyfill script and styles are unnecessary in current browsers. */
add_action('init', function () {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
});

/* 6. Unload theme scripts that hook wheel/touch events but serve nothing on this site
      (swipe helpers, a custom scrollbar for an unused widget, and the theme's gallery fullscreen).
      If anything on the site stops working, comment out the matching line. */
add_action('wp_enqueue_scripts', function () {
    wp_dequeue_script('wdwt_jquery_mobile');
    wp_dequeue_script('wdwt_mCustomScrollbar');
    wp_dequeue_style('wdwt_mCustomScrollbar');
    wp_dequeue_script('wdwt_jquery-fullscreen');
    wp_dequeue_script('wdwt_lightbox_loader');
}, 100);

/* 7. Self-hosted Open Sans: the google-webfonts-helper woff2 files are in this plugin's fonts/ folder (regular, 600, 700, each with
      its italic; no other weights are used). Preloaded so text paints in the real font. */
add_action('wp_head', function () {
    $base = plugins_url('fonts/', __FILE__);
    $faces = array(
        array(400, 'normal', 'open-sans-v44-latin-regular.woff2'),
        array(400, 'italic', 'open-sans-v44-latin-italic.woff2'),
        array(600, 'normal', 'open-sans-v44-latin-600.woff2'),
        array(600, 'italic', 'open-sans-v44-latin-600italic.woff2'),
        array(700, 'normal', 'open-sans-v44-latin-700.woff2'),
        array(700, 'italic', 'open-sans-v44-latin-700italic.woff2'),
    );
    echo '<link rel="preload" href="' . esc_url($base . 'open-sans-v44-latin-regular.woff2') . '" as="font" type="font/woff2" crossorigin>' . "\n";
    echo '<link rel="preload" href="' . esc_url($base . 'open-sans-v44-latin-600.woff2') . '" as="font" type="font/woff2" crossorigin>' . "\n";
    echo "<style>\n";
    foreach ($faces as $f) {
        echo '@font-face { font-family: "Open Sans"; font-style: ' . $f[1] . '; font-weight: ' . $f[0] . '; font-display: optional; src: url("' . esc_url($base . $f[2]) . '") format("woff2"); }' . "\n";
    }
    // Arial scaled to Open Sans's metrics, so a view that falls back (font not yet cached) lays out in the same place
    echo '@font-face { font-family: "Open Sans Fallback"; src: local("Arial"), local("Helvetica Neue"), local("Helvetica"); size-adjust: 105.3%; ascent-override: 101%; descent-override: 28%; line-gap-override: 0%; }' . "\n";
    echo 'body, h1, h2, h3, h4, h5, h6, input, textarea, button { font-family: "Open Sans", "Open Sans Fallback", Arial, sans-serif !important; }' . "\n";
    echo "</style>\n";
}, 2);

/* 8. Home page tab title: just the site name (the tagline repeats it). Other pages are unchanged. */
add_filter('document_title_parts', function ($parts) {
    if (is_front_page()) {
        $parts = array('title' => get_bloginfo('name'));
    }
    return $parts;
});

/* 9. Pages by year. Two sets of yearly pages share one look:
      - News and Events: one post per year ("News and Events YYYY"); the list of years is [bx_years] (events/events.php).
      - Youth Ambassadors Program: one page per program year, slug YYYY-bxscc-youth-ambassadors-program (BX_YA_SLUG);
        the list of years is [bx_ya_years], placed on the program page under its "Profiles of Past Programs" heading.
      Each list shows one row per decade, newest decade first (bx_year_decades). Each yearly page ends with a bar:
      previous year, the overview, next year (bx_year_bar; .bx-year-nav in components.css). The News and Events bar
      links the years either side and All News and Events; the Youth Ambassadors bar links the nearest program
      years either side, since there were no programs from 2020 to 2023, and Program Overview (the program page). */
define('BX_YA_SLUG', '/^(\d{4})-bxscc-youth-ambassadors-program$/');

/* $byYear: year => address. One <p> per decade: the decade's label, then its years oldest first. */
function bx_year_decades($byYear) {
    if (!$byYear) { return ''; }
    ksort($byYear);
    $decades = array();
    foreach ($byYear as $y => $url) { $decades[intdiv($y, 10) * 10][$y] = $url; }
    krsort($decades);
    $h = "<div class=\"bx-years\">\n";
    foreach ($decades as $d => $years) {
        $links = array();
        foreach ($years as $y => $url) { $links[] = '<a href="' . esc_url($url) . '">' . $y . '</a>'; }
        $h .= '<p><span class="bx-decade">' . $d . 's</span><span class="bx-yrs">' . implode(' <span aria-hidden="true">·</span> ', $links) . "</span></p>\n";
    }
    return $h . "</div>\n";
}

/* $prev and $next: array(year, address), or null for an empty segment (keeps the middle link centered). */
function bx_year_bar($label, $prev, $all_url, $all_text, $next) {
    $nav = '<nav class="bx-year-nav" aria-label="' . esc_attr($label) . '">';
    $nav .= $prev ? '<a class="bx-prev" href="' . esc_url($prev[1]) . '">&larr; ' . (int) $prev[0] . '</a>' : '<span></span>';
    $nav .= '<a class="bx-all" href="' . esc_url($all_url) . '">' . esc_html($all_text) . '</a>';
    $nav .= $next ? '<a class="bx-next" href="' . esc_url($next[1]) . '">' . (int) $next[0] . ' &rarr;</a>' : '<span></span>';
    return $nav . '</nav>';
}

add_filter('the_content', function ($content) {
    if (!is_singular('post') || !in_the_loop() || !is_main_query()) { return $content; }
    $title = wp_strip_all_tags(get_the_title());
    if (!preg_match('/News\s*(and|&|&amp;)\s*(Media|Events)\D*(\d{4})/i', html_entity_decode($title), $m)) { return $content; }
    $year = (int) $m[3];
    $byYear = array();
    foreach (get_posts(array('post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'update_post_meta_cache' => false, 'update_post_term_cache' => false)) as $p) {
        if (preg_match('/News\s*(and|&|&amp;)\s*(Media|Events)\D*(\d{4})/i', html_entity_decode(get_the_title($p)), $pm)) {
            $byYear[(int) $pm[3]] = get_permalink($p);
        }
    }
    return $content . bx_year_bar('News and Events by year',
        isset($byYear[$year - 1]) ? array($year - 1, $byYear[$year - 1]) : null,
        home_url('/news-and-events/'), 'All News and Events',
        isset($byYear[$year + 1]) ? array($year + 1, $byYear[$year + 1]) : null);
}, 20);

/* Youth Ambassadors program years: year => address of each published page whose slug is YYYY-bxscc-youth-ambassadors-program. */
function bx_ya_years() {
    static $byYear = null;
    if ($byYear === null) {
        $byYear = array();
        foreach (get_posts(array('post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1, 'update_post_meta_cache' => false, 'update_post_term_cache' => false)) as $p) {
            if (preg_match(BX_YA_SLUG, $p->post_name, $m)) { $byYear[(int) $m[1]] = get_permalink($p); }
        }
        ksort($byYear);
    }
    return $byYear;
}

add_shortcode('bx_ya_years', function () { return bx_year_decades(bx_ya_years()); });

add_filter('the_content', function ($content) {
    if (!is_singular('page') || !in_the_loop() || !is_main_query()) { return $content; }
    $post = get_post();
    if (!$post || !preg_match(BX_YA_SLUG, $post->post_name, $m)) { return $content; }
    $year = (int) $m[1];
    $prev = $next = null;
    foreach (bx_ya_years() as $y => $url) {
        if ($y < $year) { $prev = array($y, $url); }
        elseif ($y > $year && !$next) { $next = array($y, $url); }
    }
    $hub = get_page_by_path('youth-ambassadors-program');
    $all = $hub ? get_permalink($hub) : home_url('/youth-ambassadors-program/');
    return $content . bx_year_bar('Youth Ambassadors Program by year', $prev, $all, 'Program Overview', $next);
}, 20);

/* A program year published, unpublished or renamed changes the list on the program page and the bars on its
   neighbors, none of which Nginx Helper empties (it empties the page that was saved): empty every page. */
add_action('transition_post_status', function ($new, $old, $post) {
    if ($new !== $old && ($new === 'publish' || $old === 'publish') && $post->post_type === 'page' && preg_match(BX_YA_SLUG, $post->post_name)) { bx_purge_all_pages_soon(); }
}, 10, 3);
add_action('post_updated', function ($id, $after, $before) {
    if ($after->post_type === 'page' && $after->post_name !== $before->post_name && ($after->post_status === 'publish' || $before->post_status === 'publish')
        && (preg_match(BX_YA_SLUG, $after->post_name) || preg_match(BX_YA_SLUG, $before->post_name))) { bx_purge_all_pages_soon(); }
}, 10, 3);

/* 10. YouTube previews: <div class="bx-video" data-yt="VIDEO_ID" data-start="SECONDS"></div> shows the video's thumbnail
       with a play button, and loads the real player (privacy-enhanced youtube-nocookie.com) only when clicked. */
add_action('wp_head', function () {
?>
<style>
.bx-videos { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; margin: 14px 0 6px; }
@media (max-width: 599px) { .bx-videos { grid-template-columns: 1fr; } }
.bx-videos figure { margin: 0; }
.bx-video { position: relative; aspect-ratio: 16 / 9; background: #000 center / cover no-repeat; cursor: pointer; overflow: hidden; }
.bx-video::after { content: ""; position: absolute; left: 50%; top: 50%; width: 64px; height: 44px; margin: -22px 0 0 -32px; border-radius: 10px; background: rgba(23, 32, 58, 0.85); transition: background-color 0.15s ease; }
.bx-video::before { content: ""; position: absolute; left: 50%; top: 50%; z-index: 1; margin: -9px 0 0 -6px; border-style: solid; border-width: 9px 0 9px 15px; border-color: transparent transparent transparent #fecc4a; }
.bx-video:hover::after { background: rgba(23, 32, 58, 1); }
.bx-video.is-playing::before, .bx-video.is-playing::after { display: none; }
.bx-video iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }
.bx-videos figcaption { margin-top: 6px; font-size: 0.9em; font-style: italic; text-align: center; line-height: 1.4; }
</style>
<?php
}, 21);
add_action('wp_footer', function () {
?>
<script>
(function () {
  // thumbnails are fetched from YouTube only when a preview comes within about a screen of view
  var io = "IntersectionObserver" in window ? new IntersectionObserver(function (entries) {
    entries.forEach(function (e) { if (e.isIntersecting) { thumb(e.target); io.unobserve(e.target); } });
  }, { rootMargin: "600px 0px" }) : null;
  function thumb(box) { box.style.backgroundImage = "url('https://i.ytimg.com/vi/" + box.getAttribute("data-yt") + "/hqdefault.jpg')"; }
  document.querySelectorAll(".bx-video[data-yt]").forEach(function (box) {
    var id = box.getAttribute("data-yt");
    if (!/^[A-Za-z0-9_-]{11}$/.test(id)) { return; }   // YouTube video ids are 11 letters, digits, - or _
    if (io) { io.observe(box); } else { thumb(box); }
    box.setAttribute("role", "button");
    box.setAttribute("tabindex", "0");
    if (!box.getAttribute("aria-label")) { box.setAttribute("aria-label", "Play video"); }
    function play() {
      if (box.classList.contains("is-playing")) { return; }
      var start = parseInt(box.getAttribute("data-start") || "0", 10);
      var f = document.createElement("iframe");
      f.src = "https://www.youtube-nocookie.com/embed/" + id + "?autoplay=1&rel=0" + (start ? "&start=" + start : "");
      f.allow = "autoplay; encrypted-media; picture-in-picture; fullscreen";
      f.allowFullscreen = true;
      f.title = box.getAttribute("aria-label");
      box.appendChild(f);
      box.classList.add("is-playing");
    }
    box.addEventListener("click", play);
    box.addEventListener("keydown", function (e) { if (e.key === "Enter" || e.key === " ") { e.preventDefault(); play(); } });
  });
})();
</script>
<?php
}, 24);

/* 11. Site CSS, from two files in this plugin's css/ folder (nothing lives in Appearance > Customize > Additional CSS):
       css/components.css   the site's own markup (tables, grids, year bar, ...); works with any theme
       css/theme-fixes.css  patches for the News Magazine theme only; delete it if the theme ever changes
       Printed inline at the very end of <head>, the same place Additional CSS used to print, so the rules
       win over the theme's own styles exactly as before, without their comments. Edit them in Appearance > Site CSS. */
add_action('wp_head', function () {
    foreach (array('components', 'theme-fixes') as $name) {
        $file = __DIR__ . '/css/' . $name . '.css';
        if (is_readable($file)) {
            $css = preg_replace('#/\*.*?\*/#s', '', file_get_contents($file));   // the files keep their comments for whoever edits them;
            $css = trim(preg_replace('/\s+/', ' ', $css));                       // visitors get the rules only
            echo '<style id="bx-' . $name . '">' . str_ireplace('</style', '<\/style', $css) . "</style>\n";   // can never end the style block
        }
    }
}, 102);

/* 12. Site CSS editor (Appearance > Site CSS, in css-editor.php) with a saved history of every version.
       PHP files cannot be edited from the dashboard at all: WordPress's Plugin and Theme File Editors are switched
       off for everyone, so a typo typed into the dashboard can never take the site down. Use cPanel for PHP. */
if (is_admin()) { require_once __DIR__ . '/css-editor.php'; }
add_filter('map_meta_cap', function ($caps, $cap) {
    return in_array($cap, array('edit_plugins', 'edit_themes'), true) ? array('do_not_allow') : $caps;
}, 10, 2);

/* 13. Events, Sections and the automatic yearly pages (in events/; dashboard menu: Events; help: Events > How it works).
       The events table is created the first time this code runs, so there is nothing to activate. */
require_once __DIR__ . '/events/events.php';

/* 20. Dead links (in links/; report and settings: Tools > Link check). Outside links are checked weekly; one that fails
       three weekly checks in a row brings an email and a choice (the Internet Archive's copy, a new address, or the
       original), or, if set, the Archive's copy straight away. Working pages the Archive lacks are sent to it. */
require_once __DIR__ . '/links/links.php';

/* 14. Files a page does not need, left out of that page (checked against every page of the site, October 2026).
       Each comes back by itself on a page that does need it, so the site keeps working as WordPress normally does:
       - the theme's jQuery UI effects (explode, slide, transfer and their core): nothing calls them;
       - comment-reply.js: comments are closed everywhere and stay closed (Sam, October 2026);
       - Font Awesome's stylesheet and its 64 KB icon font: theme-fixes.css draws the header's Facebook, LinkedIn and
         Discord icons itself; it loads only if another of the header's social icons (Twitter, Google+, RSS,
         Instagram, YouTube) is switched on in the theme's settings, or the theme's Social Icons widget is in the
         sidebar, since those are drawn with it;
       - the block editor's styles (block library, classic theme styles, and global styles: the colors, font sizes
         and layouts a block can be given): only on a page built with blocks, or when a block widget is in the
         sidebar. Custom HTML and Shortcode blocks need none of them (News and Events and the home page use those).
       Global styles are not a plain file: WordPress builds them on every page load and prints them from the footer,
       so on a page without blocks they are switched off at the source. */
function bx_page_uses_blocks() {
    static $uses = null;
    if ($uses !== null) { return $uses; }
    $plain = array('core/html', 'core/shortcode', 'core/freeform', 'core/missing');
    $styled = function ($blocks) use (&$styled, $plain) {
        foreach ($blocks as $b) {
            if (!empty($b['blockName']) && !in_array($b['blockName'], $plain, true)) { return true; }
            if (!empty($b['innerBlocks']) && $styled($b['innerBlocks'])) { return true; }
        }
        return false;
    };
    global $wp_query;
    foreach ((array) (isset($wp_query->posts) ? $wp_query->posts : array()) as $p) {
        if ($p instanceof WP_Post && has_blocks($p->post_content) && $styled(parse_blocks($p->post_content))) { return $uses = true; }
    }
    $widgets = null;
    foreach ((array) wp_get_sidebars_widgets() as $area => $ids) {
        if ($area === 'wp_inactive_widgets' || !is_array($ids)) { continue; }
        foreach ($ids as $id) {
            if (strpos((string) $id, 'block-') !== 0) { continue; }
            if ($widgets === null) { $widgets = (array) get_option('widget_block'); }
            $n = (int) substr($id, 6);
            $c = isset($widgets[$n]['content']) ? (string) $widgets[$n]['content'] : '';
            if ($c !== '' && $styled(parse_blocks($c))) { return $uses = true; }
        }
    }
    return $uses = false;
}
add_action('wp_enqueue_scripts', function () {   // before WordPress queues global styles (priority 10)
    if (!bx_page_uses_blocks()) {
        remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
        remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
    }
}, 1);
add_action('wp_enqueue_scripts', function () {
    foreach (array('jquery-effects-explode', 'jquery-effects-slide', 'jquery-effects-transfer', 'jquery-effects-core', 'comment-reply') as $h) { wp_dequeue_script($h); }
    if (!bx_page_uses_blocks()) { foreach (array('wp-block-library', 'wp-block-library-theme', 'classic-theme-styles') as $h) { wp_dequeue_style($h); } }
    $fa = false;
    global $wdwt_front;
    if (is_object($wdwt_front) && method_exists($wdwt_front, 'get_param')) {
        foreach (array('twitter', 'google', 'rss', 'instagram', 'youtube') as $n) {   // the theme's own test (front_params_output.php)
            if ($wdwt_front->get_param('show_' . $n . '_icon') == 'on' && $wdwt_front->get_param($n . '_url') != '') { $fa = true; break; }
        }
    }
    if (!$fa) {   // the theme's Social Icons widget draws all its icons with the font
        foreach ((array) wp_get_sidebars_widgets() as $area => $ids) {
            if ($area !== 'wp_inactive_widgets' && is_array($ids) && preg_grep('/^news_magazine_social-/', $ids)) { $fa = true; break; }
        }
    }
    if (!$fa) { wp_dequeue_style('wdwt_font-awesome'); }
}, 100);

/* 15. Photos in page content come in the size they are shown at. WordPress does this for images inserted with
       Add Media; hand-written image tags named one fixed file, often far larger than shown. Each such image gets a
       srcset of the copies WordPress already made (same proportions, none wider than the file it names), and sizes
       saying how wide it is shown: its width attribute, capped at the content column. Photo-grid tiles are sized as tiles. */
add_filter('the_content', function ($c) {   // runs after shortcodes (priority 11, registered later), before WordPress's image pass (12)
    // look up every uploaded photo on the page at once (see bx_upload_attachment_ids)
    if (preg_match_all('#<img(?![^>]*\ssrcset=)[^>]*\ssrc="([^"]*/wp-content/uploads/[^"]+)"#', $c, $m)) {
        bx_upload_attachment_ids(array_map('html_entity_decode', $m[1]));
    }
    if (strpos($c, 'bx-grid') === false) { return $c; }
    $r = preg_replace_callback('#<div class="bx-grid[^"]*">.*?</div>#s', function ($m) { return str_replace('<img ', '<img data-bx-tile="1" ', $m[0]); }, $c);
    return is_string($r) ? $r : $c;
}, 11);
add_filter('wp_content_img_tag', function ($img, $context, $id) {
    $tile = strpos($img, 'data-bx-tile="1" ') !== false;
    $img = str_replace('data-bx-tile="1" ', '', $img);
    if (strpos($img, ' srcset=') !== false || !preg_match('/ src="([^"]+)"/', $img, $m) || strpos($m[1], '/wp-content/uploads/') === false) { return $img; }
    $src = html_entity_decode($m[1]);
    // the photo's own file decides which attachment it is: a wp-image-123 class copied along with pasted HTML can
    // name a different photo (WordPress then rightly gives up), so the address is looked up first
    $id   = bx_upload_attachment_id($src) ?: $id;
    $meta = $id ? wp_get_attachment_metadata($id) : null;
    if (empty($meta['width'])) { return $img; }
    if (preg_match('/-(\d+)x(\d+)\.[a-z0-9]+$/i', $src, $d)) { $w = (int) $d[1]; $h = (int) $d[2]; } else { $w = (int) $meta['width']; $h = (int) $meta['height']; }
    // never offer a file wider than the one the page names: a small copy chosen on purpose stays the largest a
    // sharp phone screen gets, so no page downloads more than before (a 232px PNG flyer would otherwise become 768px)
    $set = implode(', ', array_filter(explode(', ', (string) wp_calculate_image_srcset(array($w, $h), $src, $meta, $id)), function ($c) use ($w) {
        return preg_match('/ (\d+)w$/', $c, $cw) && (int) $cw[1] <= $w;
    }));
    if (substr_count($set, ', ') < 1) { return $img; }   // fewer than two copies: nothing to choose between
    if ($tile) {
        $sizes = '(max-width: 599px) 50vw, (max-width: 899px) 33vw, (max-width: 1199px) 187px, 12.5vw';   // 2, 3, then 4 columns
    } else {
        // A photo with no width attribute is drawn at its file's own width, and with a srcset the browser takes that
        // width from sizes. So sizes never says less than the column (phones: the window less 20px; tablets: 748px;
        // desktop: 50.25% of the window), and rounds up: every image is capped at 100% of its column (style.css), so
        // a little over is clipped back to the column and the photo lays out exactly as it did with one file.
        $shown = preg_match('/ width="(\d+)"/', $img, $wa) ? (int) $wa[1] : $w;
        $sizes = sprintf('(max-width: 767px) min(%1$dpx, 100vw), (max-width: 1199px) min(%1$dpx, 760px), min(%1$dpx, 51vw)', $shown);
    }
    return preg_replace('/^<img /', '<img srcset="' . esc_attr($set) . '" sizes="' . esc_attr($sizes) . '" ', $img);
}, 10, 3);
/* Every photo in page content, events and widgets: browsers draw a photo from the top down as it downloads, which
   looked unfinished; marked here, it shows only once it has arrived whole, with the banner's loading box meanwhile if
   that takes a moment (styles in block 2). The handlers are on the tag itself, so they are in place however early the
   photo arrives. */
add_filter('wp_content_img_tag', function ($img) {
    if (strpos($img, ' onload=') !== false || strpos($img, 'bx-fade') !== false) { return $img; }
    $img = preg_match('/\sclass="/', $img) ? preg_replace('/\sclass="/', ' class="bx-fade ', $img, 1) : preg_replace('/^<img /', '<img class="bx-fade" ', $img);
    return preg_replace('/^<img /', '<img onload="this.classList.add(\'bx-in\')" onerror="this.classList.add(\'bx-in\')" ', $img);
}, 20);
/* The lightbox opens a photo that is not a link at its full size, guessed from the address the browser shows it
   from: photo-1024x683.jpg opens photo.jpg. That guess fails for photos uploaded as WebP (block 21) that were over
   2560 pixels: their full size is photo-scaled.webp (and photo.jpg is the untouched original). Only where a guess
   from the photo's address or any of its srcset copies names a file that does not exist does the photo carry its
   real full-size address for the lightbox (data-bx-full); every page as it is today is unchanged. */
add_filter('wp_content_img_tag', function ($img) {
    if (strpos($img, 'data-bx-full') !== false || !preg_match('/ src="([^"]*\/wp-content\/uploads\/[^"]+)"/', $img, $m)) { return $img; }
    $src = html_entity_decode($m[1]);
    $urls = array($src);
    if (preg_match('/ srcset="([^"]+)"/', $img, $ss)) { foreach (explode(',', html_entity_decode($ss[1])) as $c) { $urls[] = strtok(trim($c), ' '); } }
    $base = wp_get_upload_dir()['basedir'] . '/';
    $missing = false;
    foreach (array_unique($urls) as $u) {
        $at = strpos((string) $u, '/wp-content/uploads/');
        if ($at === false) { continue; }
        $rel = rawurldecode(preg_replace('/-\d+x\d+(\.(jpe?g|png|gif|webp))$/i', '$1', (string) strtok(substr($u, $at + 20), '?#')));
        if ($rel === '' || strpos($rel, "\0") !== false || strpos($rel, '..') !== false) { continue; }
        if (!file_exists($base . $rel)) { $missing = true; break; }
    }
    if (!$missing) { return $img; }
    $aid = bx_upload_attachment_id($src);   // the address decides, never a wp-image-123 class (it can be copied along with pasted HTML)
    $full = $aid ? wp_get_attachment_url($aid) : '';
    return $full ? preg_replace('/^<img /', '<img data-bx-full="' . esc_url($full) . '" ', $img) : $img;
}, 25);
/* A photo with neither width nor height has no size until it arrives, so the text under it jumps down when it does
   (History's first photo). It gets its proportions in advance: aspect-ratio "auto W / H", from the file name's
   -WxH or else the Media Library's record. "auto" means the photo's own proportions take over once it has loaded,
   so the finished page is exactly as before; only the jump goes. Width and height attributes are deliberately not
   added: they would change which photos WordPress loads lazily. */
add_filter('wp_content_img_tag', function ($img) {
    if (preg_match('/\s(width|height)=/i', $img) || stripos($img, 'aspect-ratio') !== false || !preg_match('/ src="([^"]*\/wp-content\/uploads\/[^"]+)"/', $img, $m)) { return $img; }
    $src = html_entity_decode($m[1]);
    if (preg_match('/-(\d+)x(\d+)\.[a-z0-9]+$/i', $src, $d)) { $w = (int) $d[1]; $h = (int) $d[2]; }
    else {
        $id = bx_upload_attachment_id($src);
        $meta = $id ? wp_get_attachment_metadata($id) : null;
        if (empty($meta['width']) || empty($meta['height'])) { return $img; }
        $w = (int) $meta['width']; $h = (int) $meta['height'];
    }
    if ($w < 1 || $h < 1) { return $img; }
    $ar = 'aspect-ratio: auto ' . $w . ' / ' . $h . ';';
    if (preg_match('/\sstyle=(["\'])(.*?)\1/is', $img, $st)) {   // added to the photo's own style, in its own quotes
        $old = rtrim(trim($st[2]));
        return str_replace($st[0], ' style=' . $st[1] . $old . ($old !== '' && substr($old, -1) !== ';' ? '; ' : ($old !== '' ? ' ' : '')) . $ar . $st[1], $img);
    }
    return preg_replace('/^<img /', '<img style="' . $ar . '" ', $img);
}, 30);
/* A page that opens with a photo (nothing written before it): the photo is marked bx-lead, and theme-fixes.css sets
   its top edge on the sidebar's top line, as a page that opens with a table already was. Without the mark the photo
   sat 4 to 6 pixels lower (the theme's margin above images, or the text line an inline photo sits on). Only the main
   content of a single page or post is looked at. */
add_filter('the_content', function ($c) {   // after WordPress's image pass (12), so the tag is final apart from this class
    if (!is_singular() || !in_the_loop() || !is_main_query()) { return $c; }
    if (!preg_match('#^\s*(?:<(?:p|h[1-6]|div|figure)\b[^>]*>\s*)?(?:<a\b[^>]*>\s*)?(<img\b[^>]*>)#i', $c, $m, PREG_OFFSET_CAPTURE)) { return $c; }
    $tag = $m[1][0];
    $new = preg_match('/\sclass="/i', $tag) ? preg_replace('/\sclass="/i', ' class="bx-lead ', $tag, 1) : preg_replace('/^<img\b/i', '<img class="bx-lead"', $tag);
    return substr_replace($c, $new, $m[1][1], strlen($tag));
}, 13);

/* 16. The News Magazine theme (Web-Dorado, version 1.1.12) was closed on WordPress.org in 2019 and will get no more
       updates. Its parts that anyone could reach but no page of this site uses are switched off here, from outside the
       theme, so nothing in its files changes and every feature the site uses keeps working. Details and the reasons:
       docs/news-magazine-theme.md. Applies only while News Magazine is the active theme. */
if (get_template() === 'news-magazine') {
    add_action('after_setup_theme', function () {
        // Public AJAX actions (anyone, logged in or not) for the theme's own photo lightbox and for the post lists of a
        // blog-style home page. The site uses its own lightbox and a static home page, so nothing calls them. The
        // lightbox one printed whatever was posted to it into a page on this site (a cross-site scripting hole), and
        // the post-list ones listed unpublished drafts to anyone.
        foreach (array('wdwt_lightbox' => 'wdwt_lightbox', 'wdwt_front_content_posts_section' => 'wdwt_front_pages', 'wdwt_front_top_posts_section' => 'wdwt_front_pages') as $action => $callback) {
            remove_action('wp_ajax_' . $action, $callback);
            remove_action('wp_ajax_nopriv_' . $action, $callback);
        }
        // "Please review this theme" notices: their dismiss links saved whatever any visitor sent, without a login, into
        // a setting loaded on every page. Both notices were dismissed long ago.
        global $wp_filter;
        foreach (array('admin_init', 'admin_notices') as $hook) {
            if (empty($wp_filter[$hook])) { continue; }
            foreach ($wp_filter[$hook]->callbacks as $priority => $callbacks) {
                foreach ($callbacks as $cb) {
                    if (is_array($cb['function']) && $cb['function'][0] instanceof WDWT_Notices) { remove_action($hook, $cb['function'], $priority); }
                }
            }
        }
        // Appearance > News Magazine: only an "upgrade to Pro" page for a product that no longer exists and a support
        // tab that stops with a PHP error. The theme's settings are all in Appearance > Customize, which is unchanged.
        remove_action('admin_menu', 'WDWT_admin_menu');
        // the recommended-plugins installer (below)
        remove_action('admin_init', 'tgmpa_load_bulk_installer');
    }, 20);

    // "Install recommended plugins": ten of Web-Dorado's own plugins, none installed, some with serious security
    // histories. Its plugin-information screen also answered visitors who were not logged in.
    add_filter('tgmpa_load', '__return_false');

    // Layout widths the theme does arithmetic with ("100 - main column"): an empty or non-numeric value is a PHP 8
    // error that stops every page using it. Kept to numbers from 0 to 100, in the theme settings and in each page's
    // own Layout box; values already valid pass through unchanged.
    $bx_nm_layout = function ($a) {
        if (!is_array($a)) { return $a; }
        foreach (array('main_column' => 67, 'pwa_width' => 16, 'content_area_percent' => 75) as $k => $default) {
            if (array_key_exists($k, $a) && !(is_numeric($a[$k]) && $a[$k] >= 0 && $a[$k] <= 100)) { $a[$k] = (string) $default; }
        }
        if (array_key_exists('default_layout', $a) && !in_array((string) $a['default_layout'], array('1', '2', '3', '4', '5', '6'), true)) { $a['default_layout'] = '2'; }
        return $a;
    };
    add_filter('option_theme_news_magazine_options', $bx_nm_layout, 20);
    add_filter('sanitize_post_meta__news_magazine_meta', $bx_nm_layout);
}

/* 17. WordPress prints @font-face rules for fonts chosen in a block theme's theme.json or the Font Library. A classic
       theme has neither, but working that out (reading and merging every theme.json layer) took about 6 ms of every
       uncached page and printed nothing. Skipped for classic themes; a block theme keeps it. */
add_action('after_setup_theme', function () {
    if (!wp_is_block_theme()) { remove_action('wp_head', 'wp_print_font_faces', 50); }
});

/* 18. Page cache. Nginx keeps a copy of each page and serves it without running WordPress (response header
       x-proxy-cache: HIT). Nginx Helper empties one page's copy by requesting /purge/<page>, and does so when that
       page or post is saved; that works on bxscc.org. Its "purge everything" only deletes files in a cache folder on
       the same machine (/var/run/nginx-cache), which this hosting account does not have, so it empties nothing
       here (tested October 7, 2026). Site CSS is on every page and events are on many, and neither is a post, so
       after a change to either, every page of the site is emptied the way Nginx Helper empties one: a request to
       /purge/<page>. Nginx Helper sends those one at a time (about 60 ms each on bxscc.org, so 78 pages took about
       5 seconds), and the site does not run PHP-FPM, so a save would wait for them; they go out ten at a time
       instead. That runs once per request, at the very end (after the browser has its answer where the server
       allows it: PHP-FPM or LiteSpeed). Nothing happens if Nginx Helper is inactive or its purging is off; if it
       is set to purge some other way (deleting cache files, Redis), its own one-at-a-time purge is used. */
function bx_purge_all_pages_soon() {
    static $queued = false;
    if (!$queued) { $queued = true; add_action('shutdown', 'bx_purge_all_pages', 100); }
}
/* The same, wherever WordPress itself would expect the whole site to change:
   - Nginx Helper's own "Purge Cache" (admin bar) and its purge after updates: it deletes files in a cache folder this
     host does not have, so it said "Purge initiated" and emptied nothing; now every page is emptied after it runs;
   - menus, widgets, the Customizer (theme settings, Additional CSS), and the site title, tagline and icon: they show
     on every page, and Nginx Helper empties only the page or post that was saved. */
add_action('rt_nginx_helper_after_fastcgi_purge_all', 'bx_purge_all_pages_soon', 10, 0);
add_action('wp_update_nav_menu', 'bx_purge_all_pages_soon', 10, 0);
add_action('customize_save_after', 'bx_purge_all_pages_soon', 10, 0);
add_action('updated_option', function ($name) {
    if ($name === 'sidebars_widgets' || strpos($name, 'widget_') === 0 || in_array($name, array('blogname', 'blogdescription', 'site_icon'), true)) { bx_purge_all_pages_soon(); }
}, 10, 1);

function bx_purge_all_pages() {
    global $nginx_purger;
    $opt = get_site_option('rt_wp_nginx_helper_options');
    if (!is_object($nginx_purger) || !method_exists($nginx_purger, 'purge_url') || !is_array($opt) || empty($opt['enable_purge'])) { return 0; }
    if (function_exists('fastcgi_finish_request')) { fastcgi_finish_request(); }
    elseif (function_exists('litespeed_finish_request')) { litespeed_finish_request(); }
    ignore_user_abort(true);
    if (function_exists('set_time_limit')) { @set_time_limit(300); }
    $urls = bx_site_page_urls();
    $by_request = (!isset($opt['cache_method']) || $opt['cache_method'] === 'enable_fastcgi')
        && in_array(isset($opt['purge_method']) ? $opt['purge_method'] : 'get_request', array('get_request', 'get_request_torden'), true);
    if (!$by_request || !class_exists('\WpOrg\Requests\Requests')) {
        foreach ($urls as $url) { $nginx_purger->purge_url($url, false); }
        return count($urls);
    }
    // the purge address, built as Nginx Helper builds it (same filters): https://<host>/purge + the page's path
    $home = wp_parse_url(home_url());
    $base = untrailingslashit(apply_filters('rt_nginx_helper_fastcgi_purge_url_base',
        $home['scheme'] . '://' . $home['host'] . '/' . trim(apply_filters('rt_nginx_helper_fastcgi_purge_suffix', 'purge'), '/')));
    $requests = array();
    foreach ($urls as $url) {
        $p = wp_parse_url($url);
        $requests[] = array('url' => $base . (isset($p['path']) ? $p['path'] : '/') . (isset($p['query']) && $p['query'] !== '' ? '?' . $p['query'] : ''), 'type' => 'GET');
    }
    $options = array('timeout' => 15, 'connect_timeout' => 10, 'useragent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url('/'),
                     'verify' => ABSPATH . WPINC . '/certificates/ca-bundle.crt');
    foreach (array_chunk($requests, 10) as $batch) {
        try { \WpOrg\Requests\Requests::request_multiple($batch, $options); } catch (\Throwable $e) { /* a failed purge only leaves a page cached until it expires */ }
    }
    return count($urls);
}

/* Every address that shows a page of the site: home, each published page and post, and the archives that list posts. */
function bx_site_page_urls() {
    global $wpdb;
    $urls = array(home_url('/'));
    $ids = get_posts(array('post_type' => array('page', 'post'), 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC'));
    _prime_post_caches($ids, false, false);
    foreach ($ids as $id) { $urls[] = get_permalink($id); }
    foreach ($wpdb->get_results("SELECT DISTINCT YEAR(post_date) AS y, MONTH(post_date) AS m FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish'") as $d) {
        $urls[] = get_year_link((int) $d->y);
        $urls[] = get_month_link((int) $d->y, (int) $d->m);
    }
    $terms = get_terms(array('taxonomy' => array('category', 'post_tag'), 'hide_empty' => true));
    if (!is_wp_error($terms)) { foreach ($terms as $t) { $link = get_term_link($t); if (!is_wp_error($link)) { $urls[] = $link; } } }
    foreach (get_users(array('has_published_posts' => array('post'), 'fields' => 'ID')) as $uid) { $urls[] = get_author_posts_url((int) $uid); }
    return array_values(array_unique(array_filter($urls)));
}

/* 19. The not-found page. The theme's own (404.php) set its text straight under the menu, out of line with the
       sidebar (it skips the wrappers every page uses), and offered a search box. templates/404.php uses the page
       layout instead: an "Error Code 404" heading styled like the event section headings, the navy line under it
       (gold in dark mode), and one centered line starting with a playful 抱歉！ ("sorry!"). The address still answers 404, so
       search engines drop it. Only with News Magazine. */
add_filter('template_include', function ($template) {
    if (is_404() && get_template() === 'news-magazine' && is_readable(__DIR__ . '/templates/404.php')) { return __DIR__ . '/templates/404.php'; }
    return $template;
}, 20);
add_action('wp_head', function () {
    if (!is_404() || get_template() !== 'news-magazine') { return; }
    echo '<style id="bx-404">.bx-404{border-top:2px solid var(--bx-navy);padding:14px 18px 0;text-align:center}'
        . 'html[data-theme="dark"] .bx-404{border-top-color:var(--bx-gold)}.bx-404 p{margin:0;line-height:1.6}</style>' . "\n";
}, 120);

/* 21. Photos uploaded to the Media Library are stored and served as WebP: about a third smaller than JPEG at the same
       look, and shown by every current browser. WordPress (6.7 and later) does the work once told to, through
       image_editor_output_format: every copy it makes (thumbnail, medium, large, 1536 and 2048 pixels) is WebP, and
       so is the photo itself as pages and the lightbox show it ("full size"), re-saved at WordPress's usual quality,
       after scaling to 2560 pixels when larger (as before). The uploaded file stays on the server untouched, as the
       "original image" (Media Library > the photo > Original image), so nothing is lost. JPEG photos only (and
       iPhone HEIC photos, which WordPress otherwise turns into JPEG): PNG files are left as they are, since they are
       usually logos, screenshots or graphics with sharp edges or transparency. Photos already in the library are not
       changed. If the server's image library cannot write WebP, uploads work exactly as before. */
function bx_new_upload($seen = false) {
    static $upload = false;
    if ($seen) { $upload = true; }
    return $upload;
}
add_filter('wp_handle_upload', function ($file) { bx_new_upload(true); return $file; }, 1);   // also sideloads
add_filter('image_editor_output_format', function ($formats) {
    // only for a photo being uploaded: editing a photo already in the library (Media Library > Edit Image) names its
    // new file before it is saved and would lose it, and wp media regenerate would convert old photos. And not while
    // the server stores a photo the browser itself is about to process (WordPress 7.1, block editor over HTTPS: it
    // then switches big-photo scaling off with this exact filter): the browser makes the WebP copies, from the
    // answer it gets once that step is over, and the uploaded file stays the original.
    if (!bx_new_upload() || has_filter('big_image_size_threshold', '__return_false') === 100) { return $formats; }
    static $webp = null;
    if ($webp === null) { $webp = wp_image_editor_supports(array('mime_type' => 'image/webp')); }
    if (!$webp) { return $formats; }
    foreach (array('image/jpeg', 'image/heic', 'image/heif', 'image/heic-sequence', 'image/heif-sequence') as $m) { $formats[$m] = 'image/webp'; }
    return $formats;
});
