<?php
/* Dashboard screens for BXSCC Events: All Events, Add Event, Sections, How it works. */
if (!defined('ABSPATH')) { exit; }

define('BX_EV_CAP', 'edit_pages');   // Editors and Administrators

add_action('admin_menu', function () {
    add_menu_page('Events', 'Events', BX_EV_CAP, 'bx-events', 'bx_ev_screen_list', 'dashicons-calendar-alt', 5);
    add_submenu_page('bx-events', 'All Events', 'All Events', BX_EV_CAP, 'bx-events', 'bx_ev_screen_list');
    add_submenu_page('bx-events', 'Add Event', 'Add Event', BX_EV_CAP, 'bx-event-edit', 'bx_ev_screen_edit');
    add_submenu_page('bx-events', 'Sections', 'Sections', BX_EV_CAP, 'bx-sections', 'bx_ev_screen_sections');
    add_submenu_page('bx-events', 'How it works', 'How it works', BX_EV_CAP, 'bx-events-help', 'bx_ev_screen_help');
});

function bx_ev_url($page, $args = array()) { return add_query_arg(array_merge(array('page' => $page), $args), admin_url('admin.php')); }

function bx_ev_notice() {
    if (empty($_GET['bx_msg'])) { return; }
    $msgs = array('saved' => 'Event saved.', 'deleted' => 'Event deleted.', 'ssaved' => 'Section saved.', 'sdeleted' => 'Section deleted.', 'bad' => 'Please fill in the title and at least one complete date.');
    $k = sanitize_key($_GET['bx_msg']);
    if (isset($msgs[$k])) { echo '<div class="notice notice-' . ($k === 'bad' ? 'error' : 'success') . ' is-dismissible"><p>' . esc_html($msgs[$k]) . '</p></div>'; }
}

/* ---------------------------------------------------------------- All Events */
function bx_ev_screen_list() {
    global $wpdb;
    $t = bx_ev_table();
    $q = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
    $yr = isset($_GET['yr']) ? (int) $_GET['yr'] : 0;
    $pg = max(1, isset($_GET['paged']) ? (int) $_GET['paged'] : 1);
    $per = 50;
    $where = '1=1'; $params = array();
    if ($q !== '') { $where .= ' AND (title LIKE %s OR body LIKE %s)'; $like = '%' . $wpdb->esc_like($q) . '%'; $params[] = $like; $params[] = $like; }
    if ($yr) { $where .= ' AND y1 <= %d AND y2 >= %d'; $params[] = $yr; $params[] = $yr; }
    $count_sql = "SELECT COUNT(*) FROM $t WHERE $where";
    $total = (int) ($params ? $wpdb->get_var($wpdb->prepare($count_sql, $params)) : $wpdb->get_var($count_sql));
    $sql = "SELECT id, title, dates, sort_date FROM $t WHERE $where ORDER BY sort_date DESC, id DESC LIMIT " . (($pg - 1) * $per) . ", $per";
    $rows = $params ? $wpdb->get_results($wpdb->prepare($sql, $params)) : $wpdb->get_results($sql);
    $years = $wpdb->get_col("SELECT DISTINCT y1 FROM $t UNION SELECT DISTINCT y2 FROM $t ORDER BY 1 DESC");
    $today = bx_ev_today();
    ?>
    <div class="wrap">
      <h1 class="wp-heading-inline">Events</h1>
      <a class="page-title-action" href="<?php echo esc_url(bx_ev_url('bx-event-edit')); ?>">Add Event</a>
      <hr class="wp-header-end">
      <?php bx_ev_notice(); ?>
      <form method="get" style="margin: 12px 0;">
        <input type="hidden" name="page" value="bx-events">
        <select name="yr"><option value="0">All years</option>
          <?php foreach ($years as $y) { echo '<option value="' . (int) $y . '"' . selected($yr, (int) $y, false) . '>' . (int) $y . '</option>'; } ?>
        </select>
        <input type="search" name="s" value="<?php echo esc_attr($q); ?>" placeholder="Search titles and descriptions">
        <button class="button">Filter</button>
        <span style="margin-left: 8px; color: #646970;"><?php echo (int) $total; ?> events</span>
      </form>
      <table class="widefat striped">
        <thead><tr><th style="width: 55%;">Title</th><th>Date</th><th style="width: 90px;"></th></tr></thead>
        <tbody>
        <?php if (!$rows) { echo '<tr><td colspan="3">No events found.</td></tr>'; } ?>
        <?php foreach ($rows as $r) {
            $edit = bx_ev_url('bx-event-edit', array('id' => $r->id));
            $del = wp_nonce_url(admin_url('admin-post.php?action=bx_ev_delete&id=' . (int) $r->id), 'bx_ev_delete_' . (int) $r->id);
            $tag = $r->sort_date >= $today ? ' <span style="color:#2271b1;">(upcoming)</span>' : '';
            echo '<tr><td><a class="row-title" href="' . esc_url($edit) . '">' . wp_kses_post($r->title) . '</a>' . $tag . '</td>';
            echo '<td>' . esc_html(bx_ev_when($r)) . '</td>';
            echo '<td><a href="' . esc_url($edit) . '">Edit</a> | <a href="' . esc_url($del) . '" style="color:#b32d2e;" onclick="return confirm(\'Delete this event for good?\');">Delete</a></td></tr>';
        } ?>
        </tbody>
      </table>
      <?php
      $pages = (int) ceil($total / $per);
      if ($pages > 1) {
          echo '<p>';
          for ($i = 1; $i <= $pages; $i++) {
              echo $i === $pg ? '<strong style="margin-right:8px;">' . $i . '</strong>' : '<a style="margin-right:8px;" href="' . esc_url(bx_ev_url('bx-events', array('paged' => $i, 's' => $q, 'yr' => $yr))) . '">' . $i . '</a>';
          }
          echo '</p>';
      }
      ?>
    </div>
    <?php
}

/* ---------------------------------------------------------------- Add / Edit Event */
/* One date point: a kind (day / month / season / year) and the fields for it. JavaScript shows only the
   fields the chosen kind needs; without JavaScript all fields show and the kind decides which are read. */
function bx_ev_point_fields($name, $p) {
    $u = $p ? $p['u'] : 'day';
    $kinds = array('day' => 'Day', 'month' => 'Month', 'season' => 'Season', 'year' => 'Year');
    $h = '<span class="bx-pt">';
    $h .= '<select class="bx-u" name="' . $name . '[u]">';
    foreach ($kinds as $k => $l) { $h .= '<option value="' . $k . '"' . selected($u, $k, false) . '>' . $l . '</option>'; }
    $h .= '</select> ';
    $h .= '<select class="bx-m" name="' . $name . '[m]"><option value="0">Month</option>';
    for ($i = 1; $i <= 12; $i++) { $h .= '<option value="' . $i . '"' . selected($p && isset($p['m']) ? (int) $p['m'] : 0, $i, false) . '>' . bx_ev_mname($i) . '</option>'; }
    $h .= '</select> <select class="bx-d" name="' . $name . '[d]"><option value="0">Day</option>';
    for ($i = 1; $i <= 31; $i++) { $h .= '<option value="' . $i . '"' . selected($p && isset($p['d']) ? (int) $p['d'] : 0, $i, false) . '>' . $i . '</option>'; }
    $h .= '</select> <select class="bx-s" name="' . $name . '[s]">';
    foreach (array('spring', 'summer', 'fall', 'winter') as $se) { $h .= '<option value="' . $se . '"' . selected($p && isset($p['s']) ? $p['s'] : '', $se, false) . '>' . ucfirst($se) . '</option>'; }
    $h .= '</select> <input class="bx-y" type="number" name="' . $name . '[y]" value="' . esc_attr($p ? (int) $p['y'] : '') . '" min="1980" max="2100" placeholder="Year" style="width:85px;">';
    return $h . '</span>';
}

function bx_ev_part_row($i, $part) {
    $to = !empty($part['t']);
    $h = '<div class="bx-part" style="margin: 0 0 10px; padding: 8px 10px; background: #fff; border: 1px solid #dcdcde;">';
    $h .= bx_ev_point_fields('dates[' . $i . '][f]', $part ? $part['f'] : null);
    $h .= ' <label style="margin: 0 6px;"><input type="checkbox" class="bx-to" name="dates[' . $i . '][span]" value="1"' . checked($to, true, false) . '> to</label> ';
    $h .= '<span class="bx-tobox"' . ($to ? '' : ' style="display:none;"') . '>' . bx_ev_point_fields('dates[' . $i . '][t]', $to ? $part['t'] : null) . '</span>';
    $h .= ' <a href="#" class="bx-rm" style="margin-left: 8px; color: #b32d2e;">Remove</a></div>';
    return $h;
}

function bx_ev_screen_edit() {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $e = $id ? bx_ev_get($id) : null;
    if ($id && !$e) { echo '<div class="wrap"><p>That event no longer exists.</p></div>'; return; }
    $parts = $e ? json_decode($e->dates, true) : array();
    if (!$parts) { $parts = array(null); }
    ?>
    <div class="wrap">
      <h1><?php echo $e ? 'Edit Event' : 'Add Event'; ?></h1>
      <?php bx_ev_notice(); ?>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="bx_ev_save">
        <input type="hidden" name="id" value="<?php echo (int) $id; ?>">
        <?php wp_nonce_field('bx_ev_save'); ?>
        <table class="form-table" role="presentation">
          <tr><th><label for="bx_title">Title</label></th>
              <td><input type="text" id="bx_title" name="title" class="large-text" required value="<?php echo esc_attr($e ? html_entity_decode($e->title, ENT_QUOTES, 'UTF-8') : ''); ?>"></td></tr>
          <tr><th>Date</th>
              <td><div id="bx_parts"><?php foreach ($parts as $i => $part) { echo bx_ev_part_row($i, $part); } ?></div>
                  <p><a href="#" class="button" id="bx_add">+ Another date</a></p>
                  <p class="description">Pick Day, Month, Season, or Year depending on how exactly the date is known. Tick "to" for a span (a trip, an exhibit, "Fall 2024 to Spring 2025"). Use "+ Another date" for separate days, such as May 15th and May 17th.<br>
                  <strong>Exact dates not known yet?</strong> Choose as much as is known (for example Year 2027, or Spring 2027) and type <em>Dates to be announced</em> in <strong>Time or note</strong> below.<br>
                  Lists put events in order by their last day; a season runs from its first day to its last (fall: September 23rd to December 20th; winter starts December 21st and runs into the next year).</p></td></tr>
          <tr><th><label for="bx_time">Time or note</label></th>
              <td><?php bx_ev_small_editor('bx_time', 'time', $e ? $e->time : ''); ?>
                  <p class="description">Optional. Shown under the date. Use it for the time of day (<em>6:00 to 9:00pm</em>) or a short note about the date (<em>Dates to be announced</em>). The B, I and link buttons format it; Enter starts a new line.</p></td></tr>
          <tr><th><label for="bx_loc">Location</label></th>
              <td><?php bx_ev_small_editor('bx_loc', 'location', $e ? $e->location : ''); ?>
                  <p class="description">Optional. Shown under the time, for example <em>Baltimore City Hall, 100 North Holliday Street</em>. The link button can link the place's website.</p></td></tr>
          <tr><th>Description</th>
              <td><?php wp_editor($e ? $e->body : '', 'bx_body', array('textarea_name' => 'body', 'textarea_rows' => 14, 'media_buttons' => true)); ?>
                  <p class="description">Notes such as "Canceled" go in the title or here. The <strong>Add Media</strong> button adds photos and PDFs.</p></td></tr>
        </table>
        <?php submit_button($e ? 'Update Event' : 'Save Event'); ?>
      </form>
      <template id="bx_tpl"><?php echo bx_ev_part_row('__i__', null); ?></template>
    </div>
    <script>
    (function () {
      function show(pt) {
        var u = pt.querySelector('.bx-u').value;
        pt.querySelector('.bx-m').style.display = (u === 'day' || u === 'month') ? '' : 'none';
        pt.querySelector('.bx-d').style.display = u === 'day' ? '' : 'none';
        pt.querySelector('.bx-s').style.display = u === 'season' ? '' : 'none';
      }
      function wire(row) {
        row.querySelectorAll('.bx-pt').forEach(function (pt) { show(pt); pt.querySelector('.bx-u').addEventListener('change', function () { show(pt); }); });
        row.querySelector('.bx-to').addEventListener('change', function () { row.querySelector('.bx-tobox').style.display = this.checked ? '' : 'none'; });
        row.querySelector('.bx-rm').addEventListener('click', function (ev) { ev.preventDefault(); if (document.querySelectorAll('#bx_parts .bx-part').length > 1) { row.remove(); } });
      }
      document.querySelectorAll('#bx_parts .bx-part').forEach(wire);
      var n = 1000;
      document.getElementById('bx_add').addEventListener('click', function (ev) {
        ev.preventDefault();
        var div = document.createElement('div');
        div.innerHTML = document.getElementById('bx_tpl').innerHTML.replace(/__i__/g, String(n++));
        var row = div.firstElementChild;
        document.getElementById('bx_parts').appendChild(row);
        wire(row);
      });
    })();
    </script>
    <?php
}

function bx_ev_read_point($raw) {
    if (!is_array($raw)) { return null; }
    $u = isset($raw['u']) && in_array($raw['u'], array('day', 'month', 'season', 'year'), true) ? $raw['u'] : 'day';
    $p = array('u' => $u, 'y' => isset($raw['y']) ? (int) $raw['y'] : 0);
    if ($u === 'day' || $u === 'month') { $p['m'] = isset($raw['m']) ? (int) $raw['m'] : 0; }
    if ($u === 'day') {
        $p['d'] = isset($raw['d']) ? (int) $raw['d'] : 0;
        if ($p['m'] >= 1 && $p['m'] <= 12 && $p['y'] && $p['d'] > 0) { $p['d'] = min($p['d'], (int) date('t', mktime(0, 0, 0, $p['m'], 1, $p['y']))); }   // February 30th becomes the 28th/29th
    }
    if ($u === 'season') { $p['s'] = isset($raw['s']) ? sanitize_key($raw['s']) : ''; }
    return bx_ev_point_ok($p) ? $p : null;
}

add_action('admin_post_bx_ev_save', function () {
    if (!current_user_can(BX_EV_CAP)) { wp_die('Not allowed.'); }
    check_admin_referer('bx_ev_save');
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $title = isset($_POST['title']) ? trim(wp_kses(wp_unslash($_POST['title']), array('em' => array(), 'strong' => array(), 'br' => array()))) : '';
    $parts = array();
    foreach (isset($_POST['dates']) ? (array) wp_unslash($_POST['dates']) : array() as $raw) {
        $f = bx_ev_read_point(isset($raw['f']) ? $raw['f'] : null);
        if (!$f) { continue; }
        $t = !empty($raw['span']) ? bx_ev_read_point(isset($raw['t']) ? $raw['t'] : null) : null;
        $parts[] = array('f' => $f, 't' => $t);
    }
    if ($title === '' || !$parts) { wp_safe_redirect(bx_ev_url('bx-event-edit', array('id' => $id, 'bx_msg' => 'bad'))); exit; }
    $body = isset($_POST['body']) ? wp_unslash($_POST['body']) : '';
    if (!current_user_can('unfiltered_html')) { $body = wp_kses_post($body); }
    $id = bx_ev_save(array(
        'title' => $title, 'dates' => $parts,
        'time' => isset($_POST['time']) ? bx_ev_inline(wp_unslash($_POST['time'])) : '',
        'location' => isset($_POST['location']) ? bx_ev_inline(wp_unslash($_POST['location'])) : '',
        'body' => $body,
    ), $id);
    wp_safe_redirect(bx_ev_url('bx-event-edit', array('id' => $id, 'bx_msg' => 'saved'))); exit;
});

/* A one-line editor with only Bold, Italic and Link buttons, for "Time or note" and "Location".
   Enter makes a line break, not a new paragraph. */
function bx_ev_small_editor($id, $name, $value) {
    wp_editor($value, $id, array(
        'textarea_name' => $name, 'textarea_rows' => 2, 'media_buttons' => false, 'teeny' => true, 'wpautop' => false, 'quicktags' => false,
        'tinymce' => array('toolbar1' => 'bold,italic,link,unlink', 'toolbar2' => '', 'statusbar' => false, 'resize' => false, 'height' => 56, 'forced_root_block' => false, 'force_br_newlines' => true),
    ));
}

add_action('admin_post_bx_ev_delete', function () {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if (!current_user_can(BX_EV_CAP)) { wp_die('Not allowed.'); }
    check_admin_referer('bx_ev_delete_' . $id);
    bx_ev_delete($id);
    wp_safe_redirect(bx_ev_url('bx-events', array('bx_msg' => 'deleted'))); exit;
});

/* ---------------------------------------------------------------- Sections */
function bx_ev_screen_sections() {
    global $wpdb;
    $all = bx_ev_sections();
    $editing = isset($_GET['edit']) ? sanitize_key($_GET['edit']) : null;
    ?>
    <div class="wrap">
      <h1 class="wp-heading-inline">Sections</h1>
      <a class="page-title-action" href="<?php echo esc_url(bx_ev_url('bx-sections', array('edit' => '_new'))); ?>">Add Section</a>
      <hr class="wp-header-end">
      <?php bx_ev_notice(); ?>
      <p>A section is a heading plus a list of events. Put one on any page by pasting its code into the page. The same code can go on several pages, for example the home page and News and Events.</p>
      <?php if ($editing === null) { ?>
      <table class="widefat striped">
        <thead><tr><th>Heading</th><th>Shows</th><th>Code to paste into a page</th><th></th></tr></thead>
        <tbody>
        <?php if (!$all) { echo '<tr><td colspan="4">No sections yet.</td></tr>'; } ?>
        <?php foreach ($all as $name => $s) {
            $what = $s['type'] === 'upcoming' ? 'Upcoming events' : ($s['type'] === 'range' ? 'Events that ended ' . (int) $s['newer'] . ' to ' . (int) $s['older'] . ' months ago' : count(isset($s['ids']) ? $s['ids'] : array()) . ' hand-picked events');
            if (!empty($s['limit'])) { $what .= ', at most ' . (int) $s['limit']; }
            $del = wp_nonce_url(admin_url('admin-post.php?action=bx_ev_sdelete&name=' . $name), 'bx_ev_sdelete_' . $name);
            echo '<tr><td><strong>' . esc_html($s['title']) . '</strong></td><td>' . esc_html($what) . '</td>';
            echo '<td><code style="user-select: all;">[bx_section name="' . esc_html($name) . '"]</code></td>';
            echo '<td><a href="' . esc_url(bx_ev_url('bx-sections', array('edit' => $name))) . '">Edit</a> | <a href="' . esc_url($del) . '" style="color:#b32d2e;" onclick="return confirm(\'Delete this section? Pages using its code will show nothing there.\');">Delete</a></td></tr>';
        } ?>
        </tbody>
      </table>
      <p class="description">Yearly pages need no section: each "News and Media YYYY" page lists that year's events by itself with <code>[bx_year year="YYYY"]</code>, and <code>[bx_years]</code> lists all the yearly pages.</p>
      <?php } else {
          $s = ($editing !== '_new' && isset($all[$editing])) ? $all[$editing] : array('title' => '', 'type' => 'range', 'newer' => 0, 'older' => 4, 'limit' => '', 'ids' => array(), 'empty' => '');
          $events = $wpdb->get_results('SELECT id, title, dates FROM ' . bx_ev_table() . ' ORDER BY sort_date DESC, id DESC');
          $picked = array_map('intval', isset($s['ids']) ? $s['ids'] : array());
          ?>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="bx_ev_ssave">
        <input type="hidden" name="old" value="<?php echo esc_attr($editing); ?>">
        <?php wp_nonce_field('bx_ev_ssave'); ?>
        <table class="form-table" role="presentation">
          <tr><th><label for="bx_st">Heading</label></th><td><input id="bx_st" name="title" class="regular-text" required value="<?php echo esc_attr($s['title']); ?>" placeholder="Recent Events"></td></tr>
          <tr><th><label for="bx_sn">Code name</label></th><td><input id="bx_sn" name="name" class="regular-text" value="<?php echo esc_attr($editing === '_new' ? '' : $editing); ?>" placeholder="recent">
              <p class="description">Short, lowercase, no spaces. Changing it means pasting the new code onto the pages again.</p></td></tr>
          <tr><th>Shows</th><td>
              <p><label><input type="radio" name="type" value="upcoming" <?php checked($s['type'], 'upcoming'); ?>> Upcoming events (not over yet), soonest first</label></p>
              <p><label><input type="radio" name="type" value="range" <?php checked($s['type'], 'range'); ?>> Events that ended between
                 <input type="number" name="newer" min="0" value="<?php echo (int) $s['newer']; ?>" style="width:60px;"> and
                 <input type="number" name="older" min="1" value="<?php echo (int) $s['older']; ?>" style="width:60px;"> months ago, newest first</label><br>
                 <span class="description">0 and 4 = the last four months. 6 and 12 = six months to a year ago.</span></p>
              <p><label><input type="radio" name="type" value="picked" <?php checked($s['type'], 'picked'); ?>> Only the events ticked below, newest first</label></p>
          </td></tr>
          <tr><th><label for="bx_sl">At most</label></th><td><input id="bx_sl" type="number" name="limit" min="1" value="<?php echo esc_attr($s['limit']); ?>" style="width:70px;"> events <span class="description">(leave empty for no limit)</span></td></tr>
          <tr><th><label for="bx_se">When empty</label></th><td><input id="bx_se" name="empty" class="large-text" value="<?php echo esc_attr($s['empty']); ?>" placeholder="New events will be posted here as they are scheduled.">
              <p class="description">Shown when the section has no events. Leave empty to hide the whole section, heading included.</p></td></tr>
          <tr><th>Hand-picked events</th><td>
              <p><input type="search" id="bx_filter" placeholder="Type to filter the list" class="regular-text"> <span class="description">Only used when "Only the events ticked below" is chosen.</span></p>
              <div id="bx_pick" style="max-height: 360px; overflow: auto; border: 1px solid #c3c4c7; background: #fff; padding: 6px 10px;">
              <?php foreach ($events as $ev) {
                  echo '<label style="display:block; padding: 2px 0;"><input type="checkbox" name="ids[]" value="' . (int) $ev->id . '"' . checked(in_array((int) $ev->id, $picked, true), true, false) . '> ';
                  echo '<span style="color:#646970; display:inline-block; min-width: 210px;">' . esc_html(bx_ev_when($ev)) . '</span> ' . wp_kses_post($ev->title) . '</label>';
              } ?>
              </div>
          </td></tr>
        </table>
        <?php submit_button('Save Section'); ?>
      </form>
      <script>
      document.getElementById('bx_filter').addEventListener('input', function () {
        var q = this.value.toLowerCase();
        document.querySelectorAll('#bx_pick label').forEach(function (l) { l.style.display = l.textContent.toLowerCase().indexOf(q) === -1 ? 'none' : 'block'; });
      });
      </script>
      <?php } ?>
    </div>
    <?php
}

add_action('admin_post_bx_ev_ssave', function () {
    if (!current_user_can(BX_EV_CAP)) { wp_die('Not allowed.'); }
    check_admin_referer('bx_ev_ssave');
    $old = isset($_POST['old']) ? sanitize_key($_POST['old']) : '_new';
    $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
    $name = sanitize_key(isset($_POST['name']) && $_POST['name'] !== '' ? $_POST['name'] : $title);
    if ($name === '' || $name === '_new') { $name = 'section-' . time(); }
    $type = isset($_POST['type']) && in_array($_POST['type'], array('upcoming', 'range', 'picked'), true) ? $_POST['type'] : 'range';
    $s = array(
        'title' => $title, 'type' => $type,
        'newer' => max(0, (int) (isset($_POST['newer']) ? $_POST['newer'] : 0)),
        'older' => max(1, (int) (isset($_POST['older']) ? $_POST['older'] : 4)),
        'limit' => isset($_POST['limit']) && $_POST['limit'] !== '' ? max(1, (int) $_POST['limit']) : '',
        'ids' => array_values(array_map('intval', isset($_POST['ids']) ? (array) $_POST['ids'] : array())),
        'empty' => isset($_POST['empty']) ? sanitize_text_field(wp_unslash($_POST['empty'])) : '',
    );
    if ($s['older'] <= $s['newer']) { $s['older'] = $s['newer'] + 1; }
    $all = bx_ev_sections();
    if ($old !== '_new' && $old !== $name) { unset($all[$old]); }   // renamed
    $all[$name] = $s;
    update_option('bx_ev_sections', $all, false);
    wp_safe_redirect(bx_ev_url('bx-sections', array('bx_msg' => 'ssaved'))); exit;
});

add_action('admin_post_bx_ev_sdelete', function () {
    $name = isset($_GET['name']) ? sanitize_key($_GET['name']) : '';
    if (!current_user_can(BX_EV_CAP)) { wp_die('Not allowed.'); }
    check_admin_referer('bx_ev_sdelete_' . $name);
    $all = bx_ev_sections();
    unset($all[$name]);
    update_option('bx_ev_sections', $all, false);
    wp_safe_redirect(bx_ev_url('bx-sections', array('bx_msg' => 'sdeleted'))); exit;
});

/* ---------------------------------------------------------------- How it works */
function bx_ev_screen_help() {
    ?>
    <div class="wrap" style="max-width: 780px; font-size: 14px; line-height: 1.7;">
      <h1>How it works</h1>
      <h2>Adding an event</h2>
      <ol>
        <li><strong>Events &rarr; Add Event.</strong></li>
        <li>Type the title, for example <em>21st Annual CHINA Town Hall</em>.</li>
        <li>Set the date. Choose <em>Day</em>, <em>Month</em>, <em>Season</em>, or <em>Year</em> for how exactly it is known. For a trip or anything that runs a while, tick <em>to</em> and set the end. For separate days (May 15th and May 17th), use <em>+ Another date</em>.</li>
        <li>Fill in <em>Time or note</em> and <em>Location</em> if they matter; they show under the date. If the exact dates are not known yet, set as much of the date as is known (for example Year 2027) and type <em>Dates to be announced</em> in <em>Time or note</em>.</li>
        <li>Write the description. Notes like "Canceled" go in the title or the description. The <strong>Add Media</strong> button adds photos and PDFs.</li>
        <li><strong>Save Event.</strong> It appears on the pages right away.</li>
      </ol>
      <h2>Where events show up</h2>
      <ul style="list-style: disc; padding-left: 20px;">
        <li><strong>Yearly pages</strong> (News and Events – 2024, 2025, and so on) are the archive: each lists every event of that year, newest first, with no setup. An event that crosses New Year appears in both years. The current year has no page; its events are on the home page and News and Events. On January 1st the year just ended gets its page, and it joins "Archives by Year" by itself.</li>
        <li><strong>Sections</strong> are the lists on the home page and News and Events. Each is a heading plus a rule: upcoming events, events that ended within a span of months (for example the last four months), or events you tick by hand. Make or change them under <strong>Events &rarr; Sections</strong>, then paste a section's code, like <code>[bx_section name="recent"]</code>, into a page where it should appear. Changing a section changes it on every page that uses it.</li>
        <li>Events are ordered by the last day they cover; for filtering they cover everything from their earliest day to their last. A season counts as its whole span (fall: September 23rd to December 20th).</li>
      </ul>
      <h2>Changing or removing an event</h2>
      <p><strong>Events &rarr; All Events</strong>, then Edit or Delete. Deleting cannot be undone, so if in doubt, edit the title to say "Canceled" instead.</p>
      <h2>If something looks wrong</h2>
      <p>The events live in the database table <code><?php echo esc_html(bx_ev_table()); ?></code>, included in the site's regular backups. The code is part of the "BXSCC WordPress Enhancements" plugin (the <code>events</code> folder inside it). Turning that plugin off hides the event lists (the codes then show as plain text) along with the rest of the site's customizations, but deletes nothing.</p>
    </div>
    <?php
}
