<?php
/*
 * Search result text for every page and post, and a clearer title for the 2015 delegation list (October 2026).
 * Needs BXSCC WordPress Enhancements 1.79 (it prints each Excerpt as the page's meta description and in its link
 * preview). Sets the Excerpt of the 40 published pages and posts to the approved descriptions below, and renames post
 * 353 "Name List of Xiamen Delegation*" to "2015 Xiamen Delegation Name List" (the asterisk stays on the heading
 * inside the post, next to its footnote; the address does not change).
 * Each page is checked by id and address first; a page whose Excerpt someone has already written is left alone and
 * listed. WordPress keeps the previous version as a revision. A second run says there is nothing to do.
 * From public_html:
 *   wp eval-file ~/seo-content.php dry --skip-themes --skip-plugins   (lists every change)
 *   wp eval-file ~/seo-content.php run --skip-themes --skip-plugins   (makes them)
 */
if (!defined('ABSPATH')) { exit; }
$mode = isset($args[0]) ? $args[0] : 'dry';

$texts = array(
    // id => array(address name, description)
    61   => array('home', "Baltimore's sister city partnership with Xiamen, China, since 1985: upcoming events, monthly meetings, the Youth Ambassadors Program and CHINA Town Hall."),
    37   => array('about-us', "The Baltimore-Xiamen Sister City Committee's mission, how it works as part of Baltimore Sister Cities, Inc., and answers to common questions about taking part."),
    2    => array('history', "How Baltimore and Xiamen became sister cities in 1985, in English and Chinese, with a photo history of the relationship from 1994 to 2012."),
    39   => array('leadership', "Who runs the Baltimore-Xiamen Sister City Committee: the officers and subcommittee chairs, what each role does, and how to reach them."),
    42   => array('about-baltimore', "An introduction to Baltimore, Maryland, Xiamen's sister city: its history, culture and sports, in English and Chinese."),
    44   => array('about-xiamen', "An introduction to Xiamen, Baltimore's sister city on China's southeast coast: an island city of 5.37 million with one of the world's top 20 container ports."),
    46   => array('news-and-events', "Upcoming and recent events of the Baltimore-Xiamen Sister City Committee, its monthly meetings, past programs, and news by year since 2008."),
    50   => array('programs', "The committee's programs in business, education, culture and health: the Youth Ambassadors Program, CHINA Town Hall, AAPI Heritage Month and more."),
    52   => array('contact-us', "How to reach the Baltimore-Xiamen Sister City Committee by email, at its monthly online meetings, on social media or by mail, in English and Chinese."),
    54   => array('partners', "The Baltimore-Xiamen Sister City Committee's corporate members and partner organizations, and how a company or group can partner with the committee."),
    958  => array('ncuscrs-china-town-hall', "Baltimore's host of the National Committee on U.S.-China Relations' annual CHINA Town Hall for twenty years, with past speakers and how to get involved."),
    917  => array('youth-ambassadors-program', "The committee's leadership program for Maryland students aged 14 to 18, with a chaperoned summer trip to Xiamen: who can apply, what it costs, and past years."),
    1851 => array('2025-bxscc-youth-ambassadors-program', "Seven Baltimore students traveled to Xiamen, Anhui, Nanjing and Shanghai from July 15 to August 1, 2025, for the 40th anniversary of the sister city relationship."),
    1781 => array('2024-bxscc-youth-ambassadors-program', "Seven students and four adults from the Baltimore region traveled to Xiamen, Anhui and Beijing from July 21 to August 5, 2024, with news coverage from China."),
    828  => array('2019-bxscc-youth-ambassadors-program', "Sixteen Baltimore and Maryland high school students visited Xiamen and Beijing from June 24 to July 4, 2019: classes, site visits, news coverage and reflections."),
    745  => array('2018-bxscc-youth-ambassadors-program', "Four Baltimore high school students spent thirteen days in Xiamen and Jiading, Shanghai, from July 24, 2018: the trip, news coverage, and their reflections and videos."),
    369  => array('mission-overview', "The 2013 Baltimore trade mission to China, September 3 to 12, 2013, led by Baltimore City Deputy Chief for Economic Development Kaliope Parthemos and organized by the BXSCC."),
    397  => array('trip-information', "Day-by-day schedule of the 2013 Baltimore trade mission to Shanghai and Xiamen, September 3 to 12, 2013, with its information package."),
    403  => array('registration-event-2013', "How companies registered for the 2013 Baltimore trade mission to China: the registration form, fees and deadlines."),
    411  => array('sponsorship-event-2013', "The sponsorship levels offered for the 2013 Baltimore trade mission to Shanghai and Xiamen, including $5,000 Platinum sponsorship, and what each level included."),
    416  => array('about-cifit', "About the China International Fair for Investment and Trade (CIFIT), held every September in Xiamen, as described for the 2013 Baltimore trade mission."),
    353  => array('name-list-of-xiamen-delegation', "Members of the Xiamen delegation led by Vice Mayor Li Dongliang that visited Baltimore in 2015 for the sister cities' 30th anniversary: officials, education leaders and companies."),
    203  => array('news-and-media-2008', "Baltimore-Xiamen Sister City Committee events in 2008, including CHINA Town Hall at Baltimore City Hall, CIFIT in Xiamen, a water dialogue with Asia and the first Calvin Chin dim sum brunch."),
    201  => array('news-and-media-2009', "Baltimore-Xiamen Sister City Committee events in 2009, including a Xiamen business delegation in Baltimore, a Baltimore medical delegation to Xiamen, Chinese New Year at Port Discovery and CIFIT."),
    199  => array('news-and-media-2010', "Baltimore-Xiamen Sister City Committee events in 2010, including the China Business & Trade Mission, a technical school agreement with Xiamen, and the committee's 25th anniversary party."),
    197  => array('news-and-media-2011', "Baltimore-Xiamen Sister City Committee events in 2011, including honorary Xiamen citizenship for Chairman W. Fontaine Bell, Baltimore students studying in Xiamen, and the 5th CHINA Town Hall."),
    195  => array('news-and-media-2012', "Baltimore-Xiamen Sister City Committee events in 2012, including the 6th CHINA Town Hall with Ambassador Gary Locke, Xiamen educator exchanges and Chinese language awards for Baltimore students."),
    193  => array('news-and-media-2013', "Baltimore-Xiamen Sister City Committee events in 2013, including the Baltimore-China Business Mission, the 7th CHINA Town Hall with Madeleine Albright, and Baltimore students in Xiamen."),
    191  => array('news-and-media-2014', "Baltimore-Xiamen Sister City Committee events in 2014, including a Most Innovative award at the U.S.-China Sister Cities Awards, a youth orchestra concert, and the 8th CHINA Town Hall with Jimmy Carter."),
    189  => array('news-and-media-2015', "Baltimore-Xiamen Sister City Committee events in 2015, including the 30th anniversary of the sister city relationship, a Xiamen trade delegation to Baltimore, and the 9th CHINA Town Hall."),
    187  => array('news-and-media-2016', "Baltimore-Xiamen Sister City Committee events in 2016, including the 10th CHINA Town Hall, a Baltimore Sister Cities Innovation Award, Chinese language awards and Xiamen students at City Hall."),
    663  => array('news-and-media-2017', "Baltimore-Xiamen Sister City Committee events in 2017, including Amoy Art Deco Weekend, Xiamen delegations, the 11th CHINA Town Hall and a Light City proposal with Xiamen University of Technology."),
    814  => array('news-and-media-2018', "Baltimore-Xiamen Sister City Committee events in 2018, including the Youth Ambassadors' trip to Shanghai and Xiamen, delegations from Chengdu and Guangxi, and the 12th CHINA Town Hall."),
    1000 => array('news-and-media-2019', "Baltimore-Xiamen Sister City Committee events in 2019, including the Youth Ambassadors summer camp in Xiamen and Beijing, a Xiamen International Bank delegation, and the 13th CHINA Town Hall."),
    1192 => array('news-and-media-2020', "Baltimore-Xiamen Sister City Committee events in 2020, including 50,000 surgical masks donated by Xiamen to Baltimore, the 14th CHINA Town Hall, and the Arella Liu scholarship."),
    1272 => array('news-and-media-2021', "Baltimore-Xiamen Sister City Committee events in 2021, including a Xiamen sister city photo exhibition, a forum supporting AAPI communities, CIFIT and the 15th CHINA Town Hall."),
    1390 => array('news-and-media-2022', "Baltimore-Xiamen Sister City Committee events in 2022, including a WYPR feature on Baltimore's sister cities, the return to CIFIT, the 16th CHINA Town Hall and a Bard High School partnership."),
    1490 => array('news-and-media-2023', "Baltimore-Xiamen Sister City Committee events in 2023, including a visit to Fujian Province, guests from Xiamen in Baltimore, AAPI Heritage Month and CHINA Town Hall's return to City Hall."),
    1703 => array('news-and-media-2024', "Baltimore-Xiamen Sister City Committee events in 2024, including a visit by Xiamen's Deputy Mayor, the return of the Youth Ambassadors trips, a SHINE grant and the 18th CHINA Town Hall."),
    1934 => array('news-and-media-2025', "Baltimore-Xiamen Sister City Committee events in 2025, including the 40th anniversary of the Baltimore-Xiamen relationship, a visit by Xiamen's Vice Mayor, and the Youth Ambassadors trip."),
);
$title_id = 353; $title_old = 'Name List of Xiamen Delegation*'; $title_new = '2015 Xiamen Delegation Name List';

$changes = 0; $kept = array();
foreach ($texts as $id => $t) {
    list($name, $text) = $t;
    $p = get_post($id);
    if (!$p || $p->post_name !== $name || $p->post_status !== 'publish') {
        WP_CLI::error("Post $id is not the published \"$name\" this script expects. Nothing was changed.");
    }
}
foreach ($texts as $id => $t) {
    list($name, $text) = $t;
    $p = get_post($id);
    $update = array('ID' => $id);
    $cur = trim((string) $p->post_excerpt);
    if ($cur === '') { $update['post_excerpt'] = $text; }
    elseif ($cur !== $text) { $kept[] = "$name (page $id) already has its own Excerpt: \"$cur\""; }
    if ($id === $title_id && $p->post_title === $title_old) { $update['post_title'] = $title_new; }
    elseif ($id === $title_id && $p->post_title !== $title_new) { $kept[] = "Post $id is titled \"{$p->post_title}\"; title left alone"; }
    if (count($update) === 1) { continue; }
    $changes++;
    WP_CLI::log(sprintf('%-42s %s%s', $name, isset($update['post_excerpt']) ? 'Excerpt (' . mb_strlen($text) . ' characters)' : '', isset($update['post_title']) ? '; title "' . $title_old . '" -> "' . $title_new . '"' : ''));
    if ($mode !== 'run') { continue; }
    $r = wp_update_post(wp_slash($update), true);
    if (is_wp_error($r)) { WP_CLI::error("Page $id: " . $r->get_error_message()); }
    clean_post_cache($id);
    $after = get_post($id);
    if (isset($update['post_excerpt']) && $after->post_excerpt !== $text) { WP_CLI::error("Page $id: the saved Excerpt differs from the text."); }
    if (isset($update['post_title']) && $after->post_title !== $title_new) { WP_CLI::error("Post $id: the saved title differs."); }
}
foreach ($kept as $k) { WP_CLI::warning($k); }
if (!$changes) { WP_CLI::success('Nothing to do: every page already has its description and the title is changed.'); return; }
if ($mode !== 'run') { WP_CLI::success("Dry run: $changes pages and posts would change. Run again with \"run\"."); return; }
WP_CLI::success("Saved $changes pages and posts. Empty the page cache next.");
