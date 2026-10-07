<?php
/**
 * Plugin Name: BXSCC WordPress Enhancements
 * Description: Site customizations for bxscc.org: dark mode, menus, home page photo grid and lightbox, banner behavior, fonts, the Site CSS editor (Appearance > Site CSS) and the Events system (Events menu). PHP is edited only through cPanel.
 * Version: 1.46
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

/* 3d. Rewrite the header in PHP before the page is sent, so the browser never has to move anything:
      correct social tooltips, two-line title, moon button in the top row. The JS above is now only a fallback. */
add_action('template_redirect', function () {
    if (is_admin() || is_feed() || (defined('REST_REQUEST') && REST_REQUEST)) { return; }
    ob_start(function ($html) {
        $html = str_replace('title="Pinterest"', 'title="Discord" aria-label="Discord"', $html);
        $html = str_replace('title="LinkEdin"', 'title="LinkedIn" aria-label="LinkedIn"', $html);
        // footer: current year, Baltimore Sister Cities link, Chinese line (all inside the existing copyright line)
        $y = date('Y');
        $cpos = strrpos($html, 'Copyright');   // the footer line is the last "Copyright" on the page
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
        // Icon font for the social squares: preload so the glyphs are there when the squares first draw.
        $fa = get_template_directory_uri() . '/inc/css/font-awesome/fonts/fontawesome-webfont.woff?v=4.2.0';
        $preloads .= '<link rel="preload" as="font" type="font/woff" href="' . esc_url($fa) . '" crossorigin>' . "\n";
        if ($preloads !== '') { $html = preg_replace('#</head>#', $preloads . '</head>', $html, 1); }
        // Size the slideshow the moment its markup has been parsed, with the theme's own function, so the later
        // window.load call computes the same number and nothing moves.
        $html = preg_replace('#</header>#', '<script>if (typeof wdwt_slider_resize === "function") { wdwt_slider_resize(); }</script></header>', $html, 1);

        // Carousel: start on a random slide, then go in order from it. The page itself is cached by Nginx, so the
        // choice is made in the visitor's browser: every slide's real address goes into data-src (the browser
        // fetches nothing yet), a script at the top of <head> picks the slide and preloads it at high priority,
        // and a script at the end of the header shows that slide in place of slide 1 before anything is painted.
        $start = strpos($html, 'id="slideshow"');
        $stop  = strpos($html, 'SLIDESHOW END');
        if ($start !== false && $stop !== false && $stop > $start) {
            $segment = substr($html, $start, $stop - $start);
            if (preg_match_all('#<img[^>]*class="bwg_slideshow_image"[^>]*>#', $segment, $m, PREG_OFFSET_CAPTURE)) {
                $placeholder = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
                $urls = array();
                foreach ($m[0] as $img) { $urls[] = preg_match('# src="([^"]+)"#', $img[0], $u) ? html_entity_decode($u[1]) : ''; }
                for ($i = count($m[0]) - 1; $i >= 0; $i--) {
                    $tag = $m[0][$i][0];
                    $deferred = str_replace(' src="', ' src="' . $placeholder . '" data-src="', $tag);
                    $segment = substr_replace($segment, $deferred, $m[0][$i][1], strlen($tag));
                }
                $html = substr_replace($html, $segment, $start, $stop - $start);
                $pick = '<script>(function(){var s=' . wp_json_encode(array_values($urls), JSON_UNESCAPED_SLASHES) . ';var k=Math.floor(Math.random()*s.length);window.bxSlideStart=k;'
                      . 'var l=document.createElement("link");l.rel="preload";l.as="image";l.href=s[k];l.setAttribute("fetchpriority","high");document.head.appendChild(l);})();</script>' . "\n";
                $html = preg_replace_callback('#<head[^>]*>#i', function ($h) use ($pick) { return $h[0] . "\n" . $pick; }, $html, 1);
                $show = '<script>(function(){var sp=document.querySelectorAll("#slideshow .bwg_slider > span"),n=sp.length,k=window.bxSlideStart||0;if(!n){return;}if(k>=n){k=0;}'
                      // every slide whose photo has not arrived shows a placeholder box (navy, with a small spinner) until it does
                      . 'Array.prototype.forEach.call(sp,function(s){var im=s.querySelector("img");if(!im){return;}function done(){if(im.naturalWidth>1||!im.getAttribute("data-src")&&im.complete){s.classList.remove("bx-loading");}}'
                      . 'if(im.getAttribute("data-src")||!im.complete||im.naturalWidth<2){s.classList.add("bx-loading");}im.addEventListener("load",done);im.addEventListener("error",function(){s.classList.remove("bx-loading");});});'
                      . 'function ld(i){var im=sp[((i%n)+n)%n].querySelector("img[data-src]");if(im){im.src=im.getAttribute("data-src");im.removeAttribute("data-src");}return im;}'
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
        return $html;
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
    foreach (get_posts(array('post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1)) as $p) {
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
  document.querySelectorAll(".bx-video[data-yt]").forEach(function (box) {
    var id = box.getAttribute("data-yt");
    box.style.backgroundImage = "url('https://i.ytimg.com/vi/" + id + "/hqdefault.jpg')";
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
       win over the theme's own styles exactly as before. Edit the files in cPanel File Manager. */
add_action('wp_head', function () {
    foreach (array('components', 'theme-fixes') as $name) {
        $file = __DIR__ . '/css/' . $name . '.css';
        if (is_readable($file)) {
            echo '<style id="bx-' . $name . '">' . "\n" . str_replace('</style', '', file_get_contents($file)) . "</style>\n";
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
