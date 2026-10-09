<?php
/*
 * Takes the camera data (EXIF, XMP, IPTC: camera make and settings, the maker's private notes, and a location when the
 * camera or phone recorded one) out of the photo files that pages show, for the photos already in the Media Library
 * (October 2026 audit). The plugin does the same for every new upload from version 1.76 on (block 22), with the same
 * code, so the rules are the ones written there:
 * - only the data blocks are removed; the picture data is copied byte for byte, so photos look exactly the same;
 * - the colour profile is kept;
 * - a file is left as it is when its data says to turn the photo or to read its colours differently, when it holds a
 *   second picture (a phone's HDR photo), or when anything about it is unexpected;
 * - the full-size file and every copy WordPress made are done. When WordPress scaled or converted an upload, it kept
 *   the file as uploaded as the "original image": that file is not touched, only reported when it carries a location.
 *   Otherwise the full-size file IS the file as uploaded (the plugin leaves those alone on upload, this does them), so
 *   once the backup below is deleted, the site keeps no copy of that photo's camera data.
 * Before changing anything, every file it changes is copied to ~/bx-photo-metadata-backup/ (same folders as in
 * uploads), so any file can be put back. Each changed photo's recorded file sizes are updated.
 * From public_html (the plugin must load, so only the other plugins are skipped):
 *   wp eval-file ~/photo-metadata.php dry --skip-themes --skip-plugins=wordfence,updraftplus,shortcodes-ultimate
 *   wp eval-file ~/photo-metadata.php run --skip-themes --skip-plugins=wordfence,updraftplus,shortcodes-ultimate
 * Safe to run again: files already done count as "clean".
 */
if (!defined('ABSPATH')) { exit; }
$mode = isset($args[0]) ? $args[0] : 'dry';
if (!function_exists('bx_strip_photo_file') || !function_exists('bx_photo_shown_files') || !function_exists('bx_photo_record_size')) {
    WP_CLI::error('The BXSCC plugin is not loaded or is older than 1.76. Install 1.76 first, and run this without skipping that plugin.');
}
$up = wp_get_upload_dir();
$base = trailingslashit($up['basedir']);
$backup = trailingslashit(dirname(untrailingslashit(ABSPATH))) . 'bx-photo-metadata-backup/';
$ids = get_posts(array('post_type' => 'attachment', 'post_status' => 'any', 'post_mime_type' => array('image/jpeg', 'image/webp'), 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC'));

$seen = array(); $done = 0; $saved = 0; $clean = 0; $kept = array(); $gps = array(); $failed = 0;
foreach ($ids as $id) {
    $meta = wp_get_attachment_metadata($id);
    $files = bx_photo_shown_files($id, $meta, true);
    if (!$files) { continue; }
    if (!empty($meta['original_image'])) {   // the file as uploaded: reported, never changed
        $orig = path_join(dirname(get_attached_file($id)), $meta['original_image']);
        $o = bx_strip_photo_file($orig, false);
        if ($o['gps']) { $gps[] = "  #$id original " . substr($orig, strlen($base)); }
    }
    $byfile = array();   // two copies can share one file: it is done once and its size recorded under each name
    foreach ($files as $name => $file) { $byfile[$file][] = $name; }
    $changed = false;
    foreach ($byfile as $file => $names) {
        if (isset($seen[$file])) { continue; }
        $seen[$file] = true;
        $r = bx_strip_photo_file($file, false);
        if ($r['gps']) { $gps[] = "  #$id " . substr($file, strlen($base)) . ($r['result'] === 'stripped' ? ' (location removed' . ($mode === 'run' ? ')' : ' on run)') : ''); }
        if ($r['result'] === 'clean') { $clean++; continue; }
        if ($r['result'] === 'kept') { if ($r['why'] !== 'not a JPEG or WebP file' && $r['why'] !== 'missing') { $kept[] = "  #$id " . substr($file, strlen($base)) . ': ' . $r['why']; } continue; }
        if ($mode === 'run') {
            if (strpos($file, $base) !== 0) { $failed++; continue; }
            $copy = $backup . substr($file, strlen($base));
            if (!is_dir(dirname($copy)) && !wp_mkdir_p(dirname($copy))) { WP_CLI::error('Could not create the backup folder ' . dirname($copy) . '; nothing more was changed.'); }
            if (!file_exists($copy) && (!copy($file, $copy) || md5_file($copy) !== md5_file($file))) { WP_CLI::error("Could not back up $file; nothing more was changed."); }
            $r = bx_strip_photo_file($file, true);
            if ($r['result'] !== 'stripped') { $failed++; WP_CLI::warning("Left as it was: $file ({$r['why']})"); continue; }
            bx_photo_record_size($meta, $names, $r['after']);
            $changed = true;
        }
        $done++; $saved += $r['before'] - $r['after'];
    }
    if ($changed) { wp_update_attachment_metadata($id, $meta); }
}

WP_CLI::log(sprintf('%d photos, %d files checked: %d %s camera data (%.1f MB %s), %d had none, %d left as they are.',
    count($ids), count($seen), $done, $mode === 'run' ? 'had their' : 'have', $saved / 1048576, $mode === 'run' ? 'saved' : 'to save', $clean, count($kept)));
if ($kept) { WP_CLI::log("Left as they are:\n" . implode("\n", array_slice($kept, 0, 40)) . (count($kept) > 40 ? "\n  ... and " . (count($kept) - 40) . ' more' : '')); }
if ($gps) { WP_CLI::log("Files with a location in their camera data:\n" . implode("\n", array_slice($gps, 0, 60)) . (count($gps) > 60 ? "\n  ... and " . (count($gps) - 60) . ' more' : '')); }
if ($mode !== 'run') { WP_CLI::success('Dry run: nothing changed. Run again with "run" to make the change.'); return; }
if ($failed) { WP_CLI::warning("$failed files could not be changed and were left as they were."); }
WP_CLI::success("Done. The previous files are in $backup (delete it once the site has run a week without trouble). Pages need no cache purge: their addresses are unchanged.");
