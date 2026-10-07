<?php
/* Appearance > Site CSS: edit the two stylesheets in css/ from the dashboard, with a history of every saved version.
   Loaded by bxscc-wordpress-enhancements.php in the dashboard only. */
if (!defined('ABSPATH')) { exit; }

define('BX_CSS_CAP', 'edit_theme_options');   // Administrators. To let Editors in too, change this to 'edit_pages'.
define('BX_CSS_KEEP', 100);                   // versions kept per file; the oldest are removed beyond this

function bx_css_files() {
    return array(
        'components'  => array('label' => 'Components', 'help' => 'The site\'s own pieces: event and page tables, photo grids, video boxes, the year bar, sub-page buttons, the Leadership and speaker tables, the dark-mode button, and the colors used everywhere (top of the file). Works with any theme.'),
        'theme-fixes' => array('label' => 'Theme fixes', 'help' => 'Adjustments to the News Magazine theme: header, menu, sidebar, banner, footer, phone layout. If the site ever moves to a different theme, empty this file.'),
    );
}
function bx_css_path($name) { return dirname(__FILE__) . '/css/' . $name . '.css'; }
function bx_css_hist_dir() { return dirname(__FILE__) . '/css/history'; }
/* The history folder holds who saved what: it is for the dashboard only. index.php stops folder listings;
   .htaccess refuses the files to web visitors on Apache (the cPanel web server). */
function bx_css_protect_dir($dir) {
    if (!is_dir($dir)) { return; }
    if (!file_exists($dir . '/index.php')) { @file_put_contents($dir . '/index.php', "<?php // Silence is golden.\n"); }
    if (!file_exists($dir . '/.htaccess')) { @file_put_contents($dir . '/.htaccess', "# Site CSS history: read only by the dashboard\n<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n  Deny from all\n</IfModule>\n"); }
}

/* history files: css/history/<name>--<YYYYmmdd-His>--<user>.css, with a one-line note on the first line as a comment */
function bx_css_history($name) {
    $out = array();
    foreach ((array) glob(bx_css_hist_dir() . '/' . $name . '--*.css') as $f) {
        if (!preg_match('/--(\d{8}-\d{6})--([a-z0-9_.@-]*)\.css$/i', $f, $m)) { continue; }
        $fh = fopen($f, 'r'); $first = $fh ? (string) fgets($fh) : ''; if ($fh) { fclose($fh); }
        $note = preg_match('#^/\* note: (.*) \*/#', $first, $n) ? $n[1] : '';
        $out[] = array('file' => basename($f), 'when' => DateTime::createFromFormat('Ymd-His', $m[1], wp_timezone()), 'user' => $m[2], 'note' => $note, 'size' => filesize($f));
    }
    usort($out, function ($a, $b) { return strcmp($b['file'], $a['file']); });
    return $out;
}

/* a history file name, or '' (only letters, digits, dot, dash, underscore, @; never a path) */
function bx_css_clean_version($v) { $v = basename((string) $v); return preg_match('/^[a-z0-9_.@-]+\.css$/i', $v) ? $v : ''; }

function bx_css_read_version($name, $file) {
    $path = bx_css_hist_dir() . '/' . basename($file);
    if (strpos(basename($file), $name . '--') !== 0 || !is_readable($path)) { return null; }
    $css = file_get_contents($path);
    return preg_replace('#^/\* note: .* \*/\n#', '', $css, 1);   // drop the note line
}

/* write a new current version; the version being replaced goes into the history first */
function bx_css_write($name, $css, $note) {
    $dir = bx_css_hist_dir();
    if (!is_dir($dir) && !wp_mkdir_p($dir)) { return 'The history folder css/history could not be created.'; }
    bx_css_protect_dir($dir);
    $path = bx_css_path($name);
    $old = is_readable($path) ? file_get_contents($path) : '';
    if ($old === $css) { return 'No changes to save.'; }
    $hist = bx_css_history($name);
    if ($old !== '' && (!$hist || bx_css_read_version($name, $hist[0]['file']) !== $old)) {
        // the file was changed outside Site CSS (for example in cPanel): keep that version in the history too
        @file_put_contents($dir . '/' . $name . '--' . date('Ymd-His', current_time('timestamp') - 1) . '--outside.css', "/* note: Version changed outside Site CSS (cPanel or upload) */\n" . $old);
    }
    $user = wp_get_current_user();
    $stamp = current_time('Ymd-His');
    while (file_exists($dir . '/' . $name . '--' . $stamp . '--' . sanitize_file_name($user->user_login ? $user->user_login : 'unknown') . '.css')) { $stamp = date('Ymd-His', strtotime(substr($stamp, 0, 8) . ' ' . substr($stamp, 9, 2) . ':' . substr($stamp, 11, 2) . ':' . substr($stamp, 13, 2)) + 1); }
    $who = sanitize_file_name($user->user_login ? $user->user_login : 'unknown');
    $note = trim(preg_replace('/[\r\n*\/]+/', ' ', $note));
    // the history entry records the NEW version with its note, so the list reads "who changed what, when"
    if (file_put_contents($dir . '/' . $name . '--' . $stamp . '--' . $who . '.css', '/* note: ' . $note . " */\n" . $css) === false) { return 'Could not write the history copy.'; }
    if (file_put_contents($path, $css) === false) { return 'Could not write ' . $name . '.css. Check the file permissions in cPanel.'; }
    $all = bx_css_history($name);
    foreach (array_slice($all, BX_CSS_KEEP) as $h) { @unlink($dir . '/' . $h['file']); }
    bx_purge_all_pages_soon();   // empty every page's cached copy, page by page (block 18 of the main file)
    return '';
}

/* first visit: record the files as they are, so there is always a version to go back to */
function bx_css_seed() {
    foreach (array_keys(bx_css_files()) as $name) {
        if (!bx_css_history($name) && is_readable(bx_css_path($name))) {
            $dir = bx_css_hist_dir();
            if (wp_mkdir_p($dir)) {
                bx_css_protect_dir($dir);
                @file_put_contents($dir . '/' . $name . '--' . current_time('Ymd-His') . '--start.css', "/* note: Starting version (recorded when Site CSS was first opened) */\n" . file_get_contents(bx_css_path($name)));
            }
        }
    }
}

/* refuse CSS that would obviously break: unbalanced braces or comments, or text that could end the style block */
function bx_css_check($css) {
    $plain = preg_replace('#/\*.*?\*/#s', '', $css);
    if (strpos($plain, '/*') !== false) { return 'A comment is opened with /* but never closed with */.'; }
    $plain = preg_replace('/"(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\'/s', '""', $plain);   // ignore braces inside quotes
    $depth = 0; $line = 1;
    for ($i = 0, $n = strlen($plain); $i < $n; $i++) {
        $c = $plain[$i];
        if ($c === "\n") { $line++; }
        if ($c === '{') { $depth++; }
        if ($c === '}') { $depth--; if ($depth < 0) { return 'There is an extra } around line ' . $line . '.'; } }
    }
    if ($depth > 0) { return 'A { is never closed with } (' . $depth . ' missing).'; }
    $out = preg_replace('#/\*.*?\*/#s', '', $css);   // as printed for visitors: comments removed, which could join "</sty" and "le"
    foreach (array($css, $out) as $t) {
        if (stripos($t, '</style') !== false || stripos($t, '<script') !== false) { return 'CSS cannot contain "</style" or "<script".'; }
    }
    return '';
}

add_action('admin_menu', function () {
    add_theme_page('Site CSS', 'Site CSS', BX_CSS_CAP, 'bx-site-css', 'bx_css_screen');
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'appearance_page_bx-site-css') { return; }
    $settings = wp_enqueue_code_editor(array('type' => 'text/css', 'codemirror' => array('lineWrapping' => true)));
    if ($settings) { wp_add_inline_script('code-editor', 'window.bxCssEditor = ' . wp_json_encode($settings) . ';'); }
});

function bx_css_screen() {
    if (!current_user_can(BX_CSS_CAP)) { wp_die('Not allowed.'); }
    bx_css_seed();
    bx_css_protect_dir(bx_css_hist_dir());
    $files = bx_css_files();
    $name = isset($_GET['file']) && is_string($_GET['file']) && isset($files[$_GET['file']]) ? $_GET['file'] : 'components';
    $base = admin_url('themes.php?page=bx-site-css&file=' . $name);
    $view = isset($_GET['view']) && is_string($_GET['view']) ? bx_css_clean_version(wp_unslash($_GET['view'])) : '';
    $msgs = array('saved' => array('success', 'Saved. Every page\'s cached copy is being emptied; reload the site in a few seconds to see the change.'), 'restored' => array('success', 'That version is current again. The version it replaced is in the history.'));
    ?>
    <div class="wrap">
      <h1>Site CSS</h1>
      <?php
      if (!empty($_GET['bx_msg']) && is_string($_GET['bx_msg']) && isset($msgs[$_GET['bx_msg']])) { echo '<div class="notice notice-' . $msgs[$_GET['bx_msg']][0] . ' is-dismissible"><p>' . esc_html($msgs[$_GET['bx_msg']][1]) . '</p></div>'; }
      // the reason a save failed is kept for this user for a minute, not carried in the address (where a crafted
      // link could show any text in the notice)
      $err = get_transient('bx_css_err_' . get_current_user_id());
      if (!empty($_GET['bx_err']) && $err) { delete_transient('bx_css_err_' . get_current_user_id()); echo '<div class="notice notice-error"><p>Not saved: ' . esc_html($err) . '</p></div>'; }
      ?>
      <nav class="nav-tab-wrapper">
        <?php foreach ($files as $k => $f) { echo '<a class="nav-tab' . ($k === $name ? ' nav-tab-active' : '') . '" href="' . esc_url(admin_url('themes.php?page=bx-site-css&file=' . $k)) . '">' . esc_html($f['label']) . '</a>'; } ?>
      </nav>
      <?php $h0 = bx_css_history($name); if ($h0 && bx_css_read_version($name, $h0[0]['file']) !== file_get_contents(bx_css_path($name))) { echo '<div class="notice notice-warning"><p>This file was changed outside Site CSS since the last save here (for example in cPanel). Saving keeps that version in the history as well.</p></div>'; } ?>
      <p style="max-width: 900px;"><?php echo esc_html($files[$name]['help']); ?> Every save is kept in the history below, so any earlier version can be compared and restored.</p>

      <?php if ($view !== '') {
          $old = bx_css_read_version($name, $view);
          if ($old === null) { echo '<p>That version was not found.</p>'; }
          else {
              $cur = file_get_contents(bx_css_path($name));
              echo '<h2>Compare: ' . esc_html($view) . ' (left) with the current file (right)</h2>';
              $diff = wp_text_diff($old, $cur, array('title_left' => 'That version', 'title_right' => 'Current', 'show_split_view' => true));
              echo $diff ? $diff : '<p>They are identical.</p>';
              echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin: 14px 0;">';
              wp_nonce_field('bx_css_restore');
              echo '<input type="hidden" name="action" value="bx_css_restore"><input type="hidden" name="file" value="' . esc_attr($name) . '"><input type="hidden" name="version" value="' . esc_attr($view) . '">';
              submit_button('Restore this version', 'primary', 'submit', false);
              echo ' <a class="button" href="' . esc_url($base) . '">Back to the editor</a></form>';
          }
      } else { ?>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="bx_css_form">
        <input type="hidden" name="action" value="bx_css_save">
        <input type="hidden" name="file" value="<?php echo esc_attr($name); ?>">
        <input type="hidden" name="css64" id="bx_css64" value="">
        <?php wp_nonce_field('bx_css_save'); ?>
        <textarea id="bx_css" rows="30" style="width: 100%; font-family: monospace;"><?php echo esc_textarea(file_get_contents(bx_css_path($name))); ?></textarea>
        <p><label for="bx_note"><strong>What did you change?</strong> (shown in the history)</label><br>
           <input type="text" id="bx_note" name="note" class="large-text" maxlength="200" required placeholder="e.g. Darker link color in the sidebar"></p>
        <?php submit_button('Save CSS', 'primary', 'submit', false); ?>
      </form>
      <script>
      (function () {
        var ta = document.getElementById("bx_css"), cm = null;
        if (window.wp && wp.codeEditor && window.bxCssEditor) { cm = wp.codeEditor.initialize(ta, window.bxCssEditor).codemirror; cm.setSize(null, 560); }
        document.getElementById("bx_css_form").addEventListener("submit", function () {
          var css = cm ? cm.getValue() : ta.value;
          // sent encoded so the host's firewall does not mistake CSS text for an attack and block the save
          document.getElementById("bx_css64").value = btoa(unescape(encodeURIComponent(css)));
        });
      })();
      </script>
      <?php } ?>

      <h2 style="margin-top: 28px;">History</h2>
      <table class="widefat striped" style="max-width: 1100px;">
        <thead><tr><th>Saved</th><th>By</th><th>What changed</th><th></th></tr></thead>
        <tbody>
        <?php $hist = bx_css_history($name);
        if (!$hist) { echo '<tr><td colspan="4">No saved versions yet.</td></tr>'; }
        foreach ($hist as $i => $h) {
            echo '<tr><td>' . esc_html($h['when'] ? $h['when']->format('M j, Y g:i a') : $h['file']) . ($i === 0 ? ' <em>(current)</em>' : '') . '</td>';
            echo '<td>' . esc_html($h['user']) . '</td><td>' . esc_html($h['note']) . '</td>';
            echo '<td>' . ($i === 0 ? '' : '<a href="' . esc_url(add_query_arg('view', $h['file'], $base)) . '">Compare / restore</a>') . '</td></tr>';
        } ?>
        </tbody>
      </table>
      <p class="description">The <?php echo (int) BX_CSS_KEEP; ?> most recent versions of each file are kept, in the plugin's css/history folder.</p>
    </div>
    <?php
}

function bx_css_fail($err, $back) {
    set_transient('bx_css_err_' . get_current_user_id(), $err, MINUTE_IN_SECONDS);
    wp_safe_redirect(add_query_arg('bx_err', 1, $back)); exit;
}

add_action('admin_post_bx_css_save', function () {
    if (!current_user_can(BX_CSS_CAP)) { wp_die('Not allowed.'); }
    check_admin_referer('bx_css_save');
    $files = bx_css_files();
    $name = isset($_POST['file']) && is_string($_POST['file']) && isset($files[$_POST['file']]) ? $_POST['file'] : '';
    $back = admin_url('themes.php?page=bx-site-css&file=' . $name);
    $css = isset($_POST['css64']) && is_string($_POST['css64']) ? base64_decode(wp_unslash($_POST['css64']), true) : false;
    if ($name === '' || $css === false) { bx_css_fail('the editor did not send the CSS (is JavaScript on?).', $back); }
    $css = str_replace("\r\n", "\n", $css);
    $err = bx_css_check($css);
    if ($err === '') { $err = bx_css_write($name, $css, isset($_POST['note']) ? sanitize_text_field(wp_unslash($_POST['note'])) : ''); }
    if ($err !== '') { bx_css_fail($err, $back); }
    wp_safe_redirect(add_query_arg('bx_msg', 'saved', $back)); exit;
});

add_action('admin_post_bx_css_restore', function () {
    if (!current_user_can(BX_CSS_CAP)) { wp_die('Not allowed.'); }
    check_admin_referer('bx_css_restore');
    $files = bx_css_files();
    $name = isset($_POST['file']) && is_string($_POST['file']) && isset($files[$_POST['file']]) ? $_POST['file'] : '';
    $back = admin_url('themes.php?page=bx-site-css&file=' . $name);
    $ver = isset($_POST['version']) && is_string($_POST['version']) ? bx_css_clean_version(wp_unslash($_POST['version'])) : '';
    $css = $name ? bx_css_read_version($name, $ver) : null;
    if ($css === null) { bx_css_fail('that version was not found.', $back); }
    $dt = preg_match('/--(\d{8}-\d{6})--/', $ver, $m) ? DateTime::createFromFormat('Ymd-His', $m[1], wp_timezone()) : false;
    $err = bx_css_write($name, $css, 'Restored the version from ' . ($dt ? $dt->format('M j, Y g:i a') : $ver));
    if ($err !== '') { bx_css_fail($err, $back); }
    wp_safe_redirect(add_query_arg('bx_msg', 'restored', $back)); exit;
});
