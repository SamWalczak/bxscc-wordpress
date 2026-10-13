<?php
/*
 * Content corrections from the October 2026 site review, as approved on October 11, 2026.
 *   About Us (page 37): the two FAQ answers that sent people to the Chair (attending a meeting, joining) now send them
 *     to the Committee Secretary, who sends meeting links and processes new members.
 *   About Baltimore (page 42): population about 570,000 (Census estimate for July 1, 2025; it said 630,000); part of the
 *     Washington-Baltimore Combined Statistical Area (CSA), 3rd largest in the USA with more than 10 million people
 *     (it said Metropolitan Statistical Area, 4th, 9 million); "National Institutes of Health"; and in the Chinese text
 *     the same figures, the standard term for a CSA (联合统计区), and the one misspelling of Baltimore (巴尔地摩 to
 *     巴尔的摩; every other page already uses 巴尔的摩).
 * Every passage is checked first: each must appear exactly once in its page, or nothing at all is changed. WordPress
 * keeps each page's previous version as a revision. A second run says there is nothing to do.
 * From public_html:
 *   wp eval-file ~/content-fixes.php dry --skip-themes --skip-plugins   (lists every change)
 *   wp eval-file ~/content-fixes.php run --skip-themes --skip-plugins   (makes them)
 * Empty the page cache after.
 */
if (!defined('ABSPATH')) { exit; }
$mode = isset($args[0]) ? $args[0] : 'dry';
kses_remove_filters();   // save the content exactly as written (no user is logged in under WP-CLI)

$pages = array(
    37 => array('about-us', array(
        'meetings: ask the Secretary' => array(
            'Please contact the <a href="mailto:chair-xiamen@baltimoresistercities.org">Committee Chair</a> in advance to receive instructions on how to attend.',
            'Please contact the <a href="mailto:secretary-xiamen@baltimoresistercities.org">Committee Secretary</a> in advance to receive instructions on how to attend.'),
        'joining: ask the Secretary' => array(
            'Please contact the <a href="mailto:chair-xiamen@baltimoresistercities.org">Committee Chair</a> to obtain a membership form.',
            'Please contact the <a href="mailto:secretary-xiamen@baltimoresistercities.org">Committee Secretary</a> to obtain a membership form.'),
    )),
    42 => array('about-baltimore', array(
        'population and CSA (English)' => array(
            'with a population of about 630,000. It is also part of the Baltimore-Washington Metropolitan Statistical Area (MSA), which is the 4th largest in the USA and has a population exceeding 9 million.',
            'with a population of about 570,000 (2025 estimate). It is also part of the Washington-Baltimore Combined Statistical Area (CSA), which is the 3rd largest in the USA and has a population exceeding 10 million.'),
        'National Institutes of Health' => array(
            'the National Institute of Health (NIH)',
            'the National Institutes of Health (NIH)'),
        'spelling of Baltimore (Chinese)' => array(
            '<p>巴尔地摩市坐落于',
            '<p>巴尔的摩市坐落于'),
        'population and CSA (Chinese)' => array(
            '人口约六十三万, 是巴尔的摩-华盛顿都市统筹区的一部分。该区域为全美第四大，总人口超过九百万。',
            '人口约五十七万（2025年估计），是华盛顿-巴尔的摩联合统计区（CSA）的一部分。该区域为全美第三大，总人口超过一千万。'),
    )),
);

// check everything before changing anything
$todo = array(); $done = 0;
foreach ($pages as $id => $pg) {
    list($name, $edits) = $pg;
    $p = get_post($id);
    if (!$p || $p->post_name !== $name || $p->post_status !== 'publish') {
        WP_CLI::error("Page $id is not the published \"$name\" this script expects. Nothing was changed.");
    }
    foreach ($edits as $what => $e) {
        list($old, $new) = $e;
        $no = substr_count($p->post_content, $old); $nn = substr_count($p->post_content, $new);
        if ($no === 0 && $nn === 1) { $done++; continue; }   // already corrected
        if ($no !== 1 || $nn !== 0) {
            WP_CLI::error("$name (page $id), $what: the old text appears $no times and the new text $nn times (expected once and none). Someone may have edited the page; nothing was changed.");
        }
        $todo[$id][$what] = $e;
    }
}
if (!$todo) { WP_CLI::success("Nothing to do: all $done corrections are already in place."); return; }
foreach ($todo as $id => $edits) {
    foreach ($edits as $what => $e) { WP_CLI::log(sprintf('%-16s %s', $pages[$id][0], $what)); }
}
if ($mode !== 'run') { WP_CLI::success('Dry run: ' . array_sum(array_map('count', $todo)) . ' corrections would be made. Run again with "run".'); return; }

foreach ($todo as $id => $edits) {
    $c = get_post($id)->post_content;
    foreach ($edits as $e) { $c = str_replace($e[0], $e[1], $c); }
    $r = wp_update_post(wp_slash(array('ID' => $id, 'post_content' => $c)), true);
    if (is_wp_error($r)) { WP_CLI::error("Page $id: " . $r->get_error_message()); }
    clean_post_cache($id);
    $after = get_post($id)->post_content;
    if ($after !== $c) { WP_CLI::error("Page $id: the saved content differs from what was written. Check its revisions."); }
    WP_CLI::log("Saved {$pages[$id][0]} (page $id).");
}
WP_CLI::success('Saved ' . array_sum(array_map('count', $todo)) . ' corrections. Empty the page cache next.');
