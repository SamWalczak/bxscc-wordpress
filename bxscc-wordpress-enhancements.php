<?php
/**
 * Plugin Name: BXSCC WordPress Enhancements
 * Description: Site customizations for bxscc.org: dark mode, menus, home page photo grid and lightbox, banner behavior, fonts, the Site CSS editor (Appearance > Site CSS) and the Events system (Events menu). PHP is edited only through cPanel.
 * Version: 1.49
 */
if (!defined('ABSPATH')) { exit; }

/* 1. Apply a saved dark-mode preference before first paint (no light flash). */
add_action('wp_head', function () {
?>
<script>(function(){try{if(localStorage.getItem("bx-theme")==="dark"){document.documentElement.setAttribute("data-theme","dark");}}catch(e){}})();</script>
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
      var full = (im.currentSrc || im.src).replace(/-\d+x\d+(\.(jpe?g|png|gif|webp))$/i, "$1");
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

  function show(i) {
    index = (i + group.length) % group.length;
    var link = group[index];
    var thumb = link.querySelector("img");
    img.src = link.href;
    img.alt = thumb ? thumb.alt : "";
    cap.textContent = thumb ? (thumb.title || "") : "";
    var multi = group.length > 1;
    prevBtn.style.display = multi ? "" : "none";
    nextBtn.style.display = multi ? "" : "none";
  }
  function open(link) {
    group = groupOf(link);
    show(group.indexOf(link));
    box.classList.add("is-open");
  }
  function close() {
    box.classList.remove("is-open");
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

/* 3b. Home page: if the content column is shorter than the sidebar, pad the last cell of the last table so both end level. */
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
    if (window.innerWidth < 768) { if (cell.style.paddingBottom) { cell.style.paddingBottom = ""; } return; }
    cell.style.paddingBottom = base + "px";
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
        // fetches nothing yet), a script at the top of <head> picks the slide and preloads it at high priority,
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
                for ($i = count($m[0]) - 1; $i >= 0; $i--) {
                    $tag = $m[0][$i][0];
                    $extra = $sets[$i] !== '' ? ' data-srcset="' . esc_attr($sets[$i]) . '" sizes="' . BX_BANNER_SIZES . '"' : '';
                    $deferred = str_replace(' src="', $extra . ' src="' . $placeholder . '" data-src="', $tag);
                    $segment = substr_replace($segment, $deferred, $m[0][$i][1], strlen($tag));
                }
                $html = substr_replace($html, $segment, $start, $stop - $start);
                // the preload asks for the same size the photo will use; a browser that cannot preload by srcset skips the
                // preload rather than fetch the full-size file and then a second, smaller one
                $js = JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP;   // no value can close the script tag
                $pick = '<script>(function(){var s=' . wp_json_encode(array_values($urls), $js) . ',ss=' . wp_json_encode(array_values($sets), $js) . ';var k=Math.floor(Math.random()*s.length);window.bxSlideStart=k;'
                      . 'var l=document.createElement("link");l.rel="preload";l.as="image";if(ss[k]){if(!("imageSrcset" in l)){return;}l.imageSrcset=ss[k];l.imageSizes=' . wp_json_encode(BX_BANNER_SIZES, $js) . ';}l.href=s[k];l.setAttribute("fetchpriority","high");document.head.appendChild(l);})();</script>' . "\n";
                $html = preg_replace_callback('#<head(\s[^>]*)?>#i', function ($h) use ($pick) { return $h[0] . "\n" . $pick; }, $html, 1);
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

/* 4. XML-RPC is unused on this site; turning it off removes a login brute-force surface. */
add_filter('xmlrpc_enabled', '__return_false');

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

/* 9. News and Media yearly posts: previous year, all news, next year, at the bottom of the post. */
add_filter('the_content', function ($content) {
    if (!is_singular('post') || !in_the_loop() || !is_main_query()) { return $content; }
    $title = wp_strip_all_tags(get_the_title());
    if (!preg_match('/News\s*(and|&|&amp;)\s*(Media|Events)\D*(\d{4})/i', html_entity_decode($title), $m)) { return $content; }
    $year = (int) $m[3];
    $byYear = array();
    foreach (get_posts(array('post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'update_post_meta_cache' => false)) as $p) {
        if (preg_match('/News\s*(and|&|&amp;)\s*(Media|Events)\D*(\d{4})/i', html_entity_decode(get_the_title($p)), $pm)) {
            $byYear[(int) $pm[3]] = get_permalink($p);
        }
    }
    $nav = '<nav class="bx-year-nav" aria-label="News and Events by year">';
    $nav .= isset($byYear[$year - 1]) ? '<a class="bx-prev" href="' . esc_url($byYear[$year - 1]) . '">&larr; ' . ($year - 1) . '</a>' : '<span></span>';
    $nav .= '<a class="bx-all" href="' . esc_url(home_url('/news-and-events/')) . '">All News and Events</a>';
    $nav .= isset($byYear[$year + 1]) ? '<a class="bx-next" href="' . esc_url($byYear[$year + 1]) . '">' . ($year + 1) . ' &rarr;</a>' : '<span></span>';
    $nav .= '</nav>';
    return $content . $nav;
}, 20);

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

/* 14. Files the pages do not need. Each was checked against every page of the site (October 2026):
       - the theme's jQuery UI effects (explode, slide, transfer and their core): nothing calls them;
       - comment-reply.js: no page shows a comment form;
       - Font Awesome's stylesheet and its 64 KB icon font: it drew only the Facebook and LinkedIn squares, which
         theme-fixes.css now draws itself from the same outlines;
       - the block editor's styles (block library, global styles, classic theme styles): no page uses blocks.
       If any of these is ever needed again, delete its line. Global styles (9 KB of block colour and layout classes
       no page uses) are not a plain file: WordPress builds them on every page load and prints them from the footer,
       so they are switched off at the source instead (the two remove_action lines). */
add_action('wp_enqueue_scripts', function () {
    foreach (array('jquery-effects-explode', 'jquery-effects-slide', 'jquery-effects-transfer', 'jquery-effects-core', 'comment-reply') as $h) { wp_dequeue_script($h); }
    foreach (array('wdwt_font-awesome', 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles') as $h) { wp_dequeue_style($h); }
}, 100);
remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
remove_action('wp_footer', 'wp_enqueue_global_styles', 1);

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
       after a change to either, every page of the site is emptied the way Nginx Helper empties one. That runs once
       per request, at the very end, after the browser has its answer where the server allows it (PHP-FPM or
       LiteSpeed), so a save does not wait for it. Nothing happens if Nginx Helper is inactive or its purging is off. */
function bx_purge_all_pages_soon() {
    static $queued = false;
    if (!$queued) { $queued = true; add_action('shutdown', 'bx_purge_all_pages', 100); }
}

function bx_purge_all_pages() {
    global $nginx_purger;
    $opt = get_site_option('rt_wp_nginx_helper_options');
    if (!is_object($nginx_purger) || !method_exists($nginx_purger, 'purge_url') || !is_array($opt) || empty($opt['enable_purge'])) { return 0; }
    if (function_exists('fastcgi_finish_request')) { fastcgi_finish_request(); }
    elseif (function_exists('litespeed_finish_request')) { litespeed_finish_request(); }
    ignore_user_abort(true);
    if (function_exists('set_time_limit')) { @set_time_limit(300); }
    $urls = bx_site_page_urls();
    foreach ($urls as $url) { $nginx_purger->purge_url($url, false); }
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
