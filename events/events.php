<?php
/*
 * BXSCC events: events (title, dates, time, location, description) kept in one small table, shown through
 * Sections you place on any page with a short code, plus automatic yearly News and Media pages.
 * Part of the bxscc-wordpress-enhancements plugin (loaded from bxscc-wordpress-enhancements.php, section 13). Help: Events > How it works.
 */
if (!defined('ABSPATH')) { exit; }

define('BX_EV_DB_VERSION', '3');   // 3: time and location hold short formatted text (links, bold, italic, line breaks)

/* ============================================================================================
   1. Storage: one table, one row per event.
      dates     the event's dates as written: one or more parts, each a single day, month, season, or
                year, or a span from one to another (JSON; see bx_ev_when() for how they read)
      time      optional, shown under the date ("6:00 to 9:00pm")
      location  optional, shown under the time ("Baltimore City Hall, 100 N. Holliday St.")
      start_date / sort_date  the earliest and the latest day any part covers. Lists filter and order by
                these: an event "closes" on sort_date. A season runs from its first day to its last.
      y1 / y2   the first and last year the event touches, so a year page can find it quickly.
   ============================================================================================ */
function bx_ev_table() { global $wpdb; return $wpdb->prefix . 'bx_events'; }

function bx_ev_install() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $t = bx_ev_table();
    $c = $wpdb->get_charset_collate();
    dbDelta("CREATE TABLE $t (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  title text NOT NULL,
  dates text NOT NULL,
  time varchar(1000) NOT NULL DEFAULT '',
  location varchar(1000) NOT NULL DEFAULT '',
  start_date date NOT NULL,
  sort_date date NOT NULL,
  y1 smallint(5) unsigned NOT NULL,
  y2 smallint(5) unsigned NOT NULL,
  body longtext NOT NULL,
  updated datetime NOT NULL,
  PRIMARY KEY  (id),
  KEY sort_date (sort_date),
  KEY years (y1,y2)
) $c;");
    update_option('bx_ev_db_version', BX_EV_DB_VERSION);
}
add_action('plugins_loaded', function () {   // creates the table the first time this code runs, and updates it if BX_EV_DB_VERSION changes
    if (get_option('bx_ev_db_version') !== BX_EV_DB_VERSION) { bx_ev_install(); }
});

/* Seasons, as fixed days (close to the solstices and equinoxes). Winter belongs to the year it starts in:
   "Winter 2025" runs from December 21st, 2025 to March 19th, 2026. */
function bx_ev_seasons() {
    return array('spring' => array('03-20', '06-20', 0), 'summer' => array('06-21', '09-22', 0),
                 'fall' => array('09-23', '12-20', 0), 'winter' => array('12-21', '03-19', 1));
}

/* A point is one of: {u:day,y,m,d} {u:month,y,m} {u:season,y,s} {u:year,y}. Returns its first and last day. */
function bx_ev_point_bounds($p) {
    $y = (int) $p['y'];
    switch ($p['u']) {
        case 'day':    $d = sprintf('%04d-%02d-%02d', $y, $p['m'], $p['d']); return array($d, $d);
        case 'month':  $d = sprintf('%04d-%02d-01', $y, $p['m']); return array($d, date('Y-m-t', strtotime($d)));
        case 'season': $s = bx_ev_seasons(); $s = $s[$p['s']]; return array($y . '-' . $s[0], ($y + $s[2]) . '-' . $s[1]);
        default:       return array($y . '-01-01', $y . '-12-31');
    }
}

function bx_ev_point_ok($p) {
    if (!is_array($p) || empty($p['u']) || empty($p['y']) || $p['y'] < 1980 || $p['y'] > 2100) { return false; }
    if ($p['u'] === 'day') { return !empty($p['m']) && !empty($p['d']) && checkdate((int) $p['m'], (int) $p['d'], (int) $p['y']); }
    if ($p['u'] === 'month') { return !empty($p['m']) && $p['m'] >= 1 && $p['m'] <= 12; }
    if ($p['u'] === 'season') { return isset($p['s']) && array_key_exists($p['s'], bx_ev_seasons()); }
    return $p['u'] === 'year';
}

/* Cleans the parts and returns array(parts, earliest day, latest day); parts are ordered by their first day. */
function bx_ev_parts_bounds($parts) {
    $clean = array(); $lo = null; $hi = null;
    foreach ((array) $parts as $part) {
        if (empty($part['f']) || !bx_ev_point_ok($part['f'])) { continue; }
        $t = (!empty($part['t']) && bx_ev_point_ok($part['t'])) ? $part['t'] : null;
        list($a, $b) = bx_ev_point_bounds($part['f']);
        if ($t) {
            list($c, $d) = bx_ev_point_bounds($t);
            if ($c < $a) { list($part['f'], $t, $a, $b, $c, $d) = array($t, $part['f'], $c, $d, $a, $b); }   // typed backwards
            $b = max($b, $d);
            if ($part['f'] == $t) { $t = null; }
        }
        $clean[] = array('f' => $part['f'], 't' => $t, '_a' => $a);
        $lo = ($lo === null || $a < $lo) ? $a : $lo;
        $hi = ($hi === null || $b > $hi) ? $b : $hi;
    }
    usort($clean, function ($x, $y) { return strcmp($x['_a'], $y['_a']); });
    foreach ($clean as &$c) { unset($c['_a']); }
    return array($clean, $lo, $hi);
}

/* $e: title, dates (array of parts), time, location, body. Returns the id, or 0 if no valid date. */
function bx_ev_save($e, $id = 0) {
    global $wpdb;
    list($parts, $lo, $hi) = bx_ev_parts_bounds(isset($e['dates']) ? $e['dates'] : array());
    if (!$parts) { return 0; }
    $row = array(
        'title' => $e['title'], 'dates' => wp_json_encode($parts),
        'time' => isset($e['time']) ? $e['time'] : '', 'location' => isset($e['location']) ? $e['location'] : '',
        'start_date' => $lo, 'sort_date' => $hi, 'y1' => (int) substr($lo, 0, 4), 'y2' => (int) substr($hi, 0, 4),
        'body' => isset($e['body']) ? $e['body'] : '', 'updated' => current_time('mysql'),
    );
    if ($id) { $wpdb->update(bx_ev_table(), $row, array('id' => $id)); }
    else { $wpdb->insert(bx_ev_table(), $row); $id = (int) $wpdb->insert_id; }
    for ($y = $row['y1']; $y <= $row['y2']; $y++) { bx_ev_year_page($y); }
    bx_purge_all_pages_soon();   // the event shows on several pages: empty their cached copies (block 18 of the main file)
    return $id;
}

function bx_ev_get($id) {
    global $wpdb;
    return $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . bx_ev_table() . ' WHERE id = %d', $id));
}

function bx_ev_delete($id) {
    global $wpdb;
    $wpdb->delete(bx_ev_table(), array('id' => (int) $id));
    bx_purge_all_pages_soon();
    foreach (bx_ev_sections() as $name => $s) {   // drop it from any hand-picked section
        if (!empty($s['ids']) && in_array((int) $id, $s['ids'], true)) {
            $s['ids'] = array_values(array_diff($s['ids'], array((int) $id)));
            bx_ev_section_save($name, $s);
        }
    }
}

/* Every past year with an event has a yearly post holding [bx_year year="YYYY"]. Existing yearly posts keep their
   addresses. A year's post is an archive, so it is created only once the year is over: the current year's events
   are on the home page and News and Events, and on January 1st the year just ended gets its post (checked once a
   day). Saving an event in an earlier year that has no post yet creates that post right away. */
function bx_ev_year_post($year) {
    $found = get_posts(array('post_type' => 'post', 'name' => 'news-and-media-' . (int) $year, 'post_status' => array('publish', 'future', 'draft', 'private'), 'numberposts' => 1, 'fields' => 'ids', 'cache_results' => false));
    return $found ? $found[0] : 0;
}
function bx_ev_year_page($year) {
    $year = (int) $year;
    if ($found = bx_ev_year_post($year)) { return $found; }
    if ($year >= (int) current_time('Y')) { return 0; }   // the current year and later wait until the year is over
    // two visitors arriving at the same moment on January 1st would otherwise both make the post; adding an option
    // either succeeds or finds it already there, so only one request goes on (a lock left by a crash expires in a minute)
    $lock = 'bx_ev_year_lock_' . $year;
    if (!add_option($lock, time(), '', false)) {
        if ((int) get_option($lock) > time() - 60) { return 0; }
        update_option($lock, time(), false);
    }
    if ($found = bx_ev_year_post($year)) { delete_option($lock); return $found; }   // made by another request just now
    // the author: whoever saved the event; on January 1st (made during a visitor's page load, so nobody is logged in)
    // the author of the year before's post, or else the first administrator
    $author = get_current_user_id();
    if (!$author) {
        $prev = get_posts(array('post_type' => 'post', 'name' => 'news-and-media-' . ($year - 1), 'post_status' => 'any', 'numberposts' => 1));
        $admins = $prev ? array() : get_users(array('role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'fields' => 'ID'));
        $author = $prev ? (int) $prev[0]->post_author : (int) reset($admins);
    }
    $id = wp_insert_post(array(
        'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'News and Events – ' . $year,
        'post_name' => 'news-and-media-' . $year,
        'post_date' => $year . '-12-31 12:00:00',
        'post_author' => $author,
        'post_content' => '<!-- wp:shortcode -->[bx_year year="' . $year . '"]<!-- /wp:shortcode -->',
    ));
    delete_option($lock);
    return $id;
}
add_action('init', function () {
    if (!get_option('bx_ev_db_version') || get_option('bx_ev_years_checked') === current_time('Y-m-d')) { return; }
    update_option('bx_ev_years_checked', current_time('Y-m-d'), true);
    global $wpdb;
    $last = (int) current_time('Y') - 1;   // the year that most recently ended
    if ($wpdb->get_var($wpdb->prepare('SELECT id FROM ' . bx_ev_table() . ' WHERE y1 <= %d AND y2 >= %d LIMIT 1', $last, $last))) { bx_ev_year_page($last); }
});

/* ============================================================================================
   2. Sections: kept in one setting. Each has a title and one rule:
        upcoming  every event that has not closed yet, soonest first
        range     events that closed between "newer" and "older" months ago (0 and 4 = the last 4 months)
        picked    a hand-picked list of events
      Placed on a page with [bx_section name="..."]; the same section can go on several pages.
   ============================================================================================ */
// a changed section changes every page it is placed on (and deleting an event can change a hand-picked one)
add_action('update_option_bx_ev_sections', 'bx_purge_all_pages_soon', 10, 0);
add_action('add_option_bx_ev_sections', 'bx_purge_all_pages_soon', 10, 0);

function bx_ev_sections() { $s = get_option('bx_ev_sections', array()); return is_array($s) ? $s : array(); }

function bx_ev_section_save($name, $s) {
    $all = bx_ev_sections();
    $all[$name] = $s;
    update_option('bx_ev_sections', $all, false);
}

function bx_ev_today() { return current_time('Y-m-d'); }

function bx_ev_months_ago($n) {
    $d = new DateTime(bx_ev_today());
    if ($n > 0) { $d->modify('-' . (int) $n . ' months'); }
    return $d->format('Y-m-d');
}

function bx_ev_section_events($s) {
    global $wpdb;
    $t = bx_ev_table();
    $today = bx_ev_today();
    $limit = !empty($s['limit']) ? ' LIMIT ' . (int) $s['limit'] : '';
    switch (isset($s['type']) ? $s['type'] : '') {
        case 'upcoming':
            return $wpdb->get_results($wpdb->prepare("SELECT * FROM $t WHERE sort_date >= %s ORDER BY sort_date ASC, start_date ASC, id ASC$limit", $today));
        case 'range':
            $newer = max(0, (int) $s['newer']); $older = max($newer, (int) $s['older']);
            $hi = $newer === 0 ? $today : bx_ev_months_ago($newer);     // closed before this day
            $lo = bx_ev_months_ago($older);                             // and on or after this day
            return $wpdb->get_results($wpdb->prepare("SELECT * FROM $t WHERE sort_date < %s AND sort_date >= %s ORDER BY sort_date DESC, id ASC$limit", $hi, $lo));
        case 'picked':
            $ids = array_filter(array_map('intval', isset($s['ids']) ? (array) $s['ids'] : array()));
            if (!$ids) { return array(); }
            return $wpdb->get_results("SELECT * FROM $t WHERE id IN (" . implode(',', $ids) . ") ORDER BY sort_date DESC, id ASC$limit");
    }
    return array();
}

function bx_ev_year_events($year) {
    global $wpdb;
    $t = bx_ev_table();
    return $wpdb->get_results($wpdb->prepare("SELECT * FROM $t WHERE y1 <= %d AND y2 >= %d ORDER BY sort_date DESC, id ASC", $year, $year));
}

/* ============================================================================================
   3. How dates read. One part: "October 16th, 2014", "July 2023", "Fall 2024", "2019".
      A span: "June 6th to 8th, 2025", "July 15th to August 1st, 2025", "April to August 2023",
      "Fall 2024 to Spring 2025", "May 3rd, 2024 to Summer 2024".
      Several separate days: "May 23rd, 27th, and 31st, 2023", "February 11th and April 5th, 2019".
   ============================================================================================ */
function bx_ev_ordinal($n) {
    $n = (int) $n;
    if ($n % 100 >= 11 && $n % 100 <= 13) { return $n . 'th'; }
    $r = $n % 10;
    return $n . ($r === 1 ? 'st' : ($r === 2 ? 'nd' : ($r === 3 ? 'rd' : 'th')));
}

function bx_ev_mname($m) { return date('F', mktime(0, 0, 0, (int) $m, 1, 2000)); }

/* $withYear false leaves the year off (it is said once at the end of the span) */
function bx_ev_point_text($p, $withYear = true) {
    $y = $withYear ? (int) $p['y'] : '';
    switch ($p['u']) {
        case 'day':    return bx_ev_mname($p['m']) . ' ' . bx_ev_ordinal($p['d']) . ($withYear ? ', ' . $y : '');
        case 'month':  return trim(bx_ev_mname($p['m']) . ' ' . $y);
        case 'season': return trim(ucfirst($p['s']) . ' ' . $y);
        default:       return (string) (int) $p['y'];
    }
}

function bx_ev_part_text($part) {
    $f = $part['f']; $t = isset($part['t']) ? $part['t'] : null;
    if (!$t) { return bx_ev_point_text($f); }
    if ($f['u'] === $t['u'] && (int) $f['y'] === (int) $t['y'] && $f['u'] !== 'year') {
        if ($f['u'] === 'day' && (int) $f['m'] === (int) $t['m']) {
            return bx_ev_mname($f['m']) . ' ' . bx_ev_ordinal($f['d']) . ' to ' . bx_ev_ordinal($t['d']) . ', ' . (int) $t['y'];
        }
        return bx_ev_point_text($f, false) . ' to ' . bx_ev_point_text($t);
    }
    return bx_ev_point_text($f) . ' to ' . bx_ev_point_text($t);
}

function bx_ev_list_join($items) {
    $n = count($items);
    if ($n < 2) { return implode('', $items); }
    if ($n === 2) { return $items[0] . ' and ' . $items[1]; }
    return implode(', ', array_slice($items, 0, -1)) . ', and ' . $items[$n - 1];
}

function bx_ev_when($e) {
    $parts = is_array($e) ? $e : json_decode($e->dates, true);
    if (!$parts) { return ''; }
    if (count($parts) === 1) { return bx_ev_part_text($parts[0]); }
    // several single days, months, or seasons in one year read as one phrase
    $days = true; $months = true; $seasons = true; $year = null;
    foreach ($parts as $p) {
        if (!empty($p['t']) || ($year !== null && (int) $p['f']['y'] !== $year)) { $days = $months = $seasons = false; break; }
        if ($p['f']['u'] !== 'day') { $days = false; }
        if ($p['f']['u'] !== 'month') { $months = false; }
        if ($p['f']['u'] !== 'season') { $seasons = false; }
        $year = (int) $p['f']['y'];
    }
    if ($months) { return bx_ev_list_join(array_map(function ($p) { return bx_ev_mname($p['f']['m']); }, $parts)) . ' ' . $year; }
    if ($seasons) { return bx_ev_list_join(array_map(function ($p) { return ucfirst($p['f']['s']); }, $parts)) . ' ' . $year; }
    if ($days) {
        $sameMonth = count(array_unique(array_map(function ($p) { return (int) $p['f']['m']; }, $parts))) === 1;
        $bits = array(); $prevM = null;
        foreach ($parts as $p) {
            $bits[] = ($sameMonth && $prevM !== null) ? bx_ev_ordinal($p['f']['d']) : bx_ev_mname($p['f']['m']) . ' ' . bx_ev_ordinal($p['f']['d']);
            $prevM = (int) $p['f']['m'];
        }
        return bx_ev_list_join($bits) . ', ' . $year;
    }
    return bx_ev_list_join(array_map('bx_ev_part_text', $parts));
}

/* the gray column: date, then time, then location, each on its own line */
/* Titles, "Time or note" and "Location" are short formatted text: links, bold, italic, and line breaks, nothing else. */
function bx_ev_inline_tags() {
    return array('a' => array('href' => true, 'target' => true, 'rel' => true), 'strong' => array(), 'em' => array(), 'b' => array(), 'i' => array(), 'br' => array());
}
function bx_ev_inline($html) {   // tidies what the small editors send: paragraphs become line breaks, other tags are dropped
    $h = preg_replace('#</p>\s*<p[^>]*>#i', '<br />', (string) $html);
    $h = preg_replace('#</?(p|div)[^>]*>#i', '', $h);
    $h = str_replace('&nbsp;', ' ', $h);
    $h = wp_kses($h, bx_ev_inline_tags());
    $h = preg_replace('#^(\s*<br\s*/?>\s*)+|(\s*<br\s*/?>\s*)+$#i', '', trim($h));
    return trim(preg_replace('#<br\s*/?>#i', '<br />', $h));
}
function bx_ev_label($e) {
    $lines = array(esc_html(bx_ev_when($e)));
    if ($e->time !== '') { $lines[] = wp_kses($e->time, bx_ev_inline_tags()); }
    if ($e->location !== '') { $lines[] = wp_kses($e->location, bx_ev_inline_tags()); }
    return implode('<br />', $lines);
}

/* ============================================================================================
   4. Output: the site's usual two-column table (title across the top, date at left, description at right).
   ============================================================================================ */
function bx_ev_body($html) {
    if (stripos($html, '<p') === false) { $html = wpautop($html); }   // text typed in the editor box has no <p> tags yet
    return do_shortcode($html);
}

function bx_ev_render($events, $empty = '') {
    if (!$events) {
        return $empty === '' ? '' : '<table class="bx-events"><tbody><tr><td colspan="2"><p class="bx-ev-empty">' . esc_html($empty) . "</p></td></tr></tbody></table>\n";
    }
    $h = "<table class=\"bx-events\">\n<tbody>\n";
    foreach ($events as $e) {
        $h .= "<tr>\n<td colspan=\"2\">\n<h3 style=\"text-align: center;\"><strong>" . wp_kses($e->title, bx_ev_inline_tags()) . "</strong></h3>\n</td>\n</tr>\n";
        $h .= "<tr>\n<td><em>" . bx_ev_label($e) . "</em></td>\n<td>\n" . bx_ev_body($e->body) . "\n</td>\n</tr>\n";
    }
    return $h . "</tbody>\n</table>\n";
}

/* [bx_section name="recent"]  the section's title as a heading, then its events */
add_shortcode('bx_section', function ($atts) {
    static $depth = 0;   // a section placed in the description of one of its own events would otherwise repeat until PHP runs out of memory
    if ($depth > 1) { return ''; }
    $a = shortcode_atts(array('name' => ''), $atts, 'bx_section');
    $all = bx_ev_sections();
    if (!isset($all[$a['name']])) {
        return current_user_can('edit_pages') ? '<p><em>[No section named "' . esc_html($a['name']) . '". Check Events &rarr; Sections.]</em></p>' : '';
    }
    $s = $all[$a['name']];
    $events = bx_ev_section_events($s);
    if (!$events && (isset($s['empty']) ? $s['empty'] : '') === '') { return ''; }   // nothing to show and no message: hide the whole section
    $h = '<div class="bx-section">' . "\n";
    if (!empty($s['title'])) { $h .= '<h1 style="text-align: center;"><strong>' . esc_html($s['title']) . "</strong></h1>\n"; }
    $depth++;
    $h .= bx_ev_render($events, isset($s['empty']) ? $s['empty'] : '');
    $depth--;
    return $h . "</div>\n";
});

/* [bx_year year="2014"]  every event that touches that year, newest first */
add_shortcode('bx_year', function ($atts) {
    $a = shortcode_atts(array('year' => ''), $atts, 'bx_year');
    return bx_ev_render(bx_ev_year_events((int) $a['year']), 'No events are listed for this year yet.');
});

/* [bx_years]  "Archives by Year": links to every yearly page, grouped by decade, newest decade first */
add_shortcode('bx_years', function () {
    $byYear = array();
    foreach (get_posts(array('post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'update_post_meta_cache' => false)) as $p) {
        if (preg_match('/^news-and-media-(\d{4})$/', $p->post_name, $m)) { $byYear[(int) $m[1]] = get_permalink($p); }
    }
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
});

if (is_admin()) { require_once __DIR__ . '/admin.php'; }
