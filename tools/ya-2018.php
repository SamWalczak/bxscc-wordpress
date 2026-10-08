<?php
/*
 * 2018 Youth Ambassadors page (/news-and-events/2018-bxscc-youth-ambassadors-program/): new layout, October 2026.
 * - Trip Overview: a section heading like the home page's, the text in a table under it ("brings" now "brought").
 * - In the Media: one table by news source, each article dated and summarized (the Chinese article in Chinese);
 *   link titles unchanged.
 * - Reflections from 2018 Youth Ambassadors: each ambassador's photo with name and years of study under it, the quote
 *   beside it, sides alternating, a divider between ambassadors; the three video testimonials moved here.
 * - Thanks: a section heading with the thank-you text and Danny Evans's note in a table under it.
 * Needs plugin 1.70 or later (the styles for these sections).
 * From public_html:
 *   wp eval-file ~/ya-2018.php dry --skip-themes --skip-plugins   (checks the page; changes nothing)
 *   wp eval-file ~/ya-2018.php run --skip-themes --skip-plugins   (changes it)
 * WordPress keeps the previous version as a revision (page editor > Revisions).
 */
if (!defined('ABSPATH')) { exit; }
$mode = isset($args[0]) ? $args[0] : 'dry';
kses_remove_filters();   // save the content exactly as written (no user is logged in under WP-CLI)

$plugin = WP_PLUGIN_DIR . '/bxscc-wordpress-enhancements/bxscc-wordpress-enhancements.php';
$ver = is_file($plugin) ? get_file_data($plugin, array('v' => 'Version'))['v'] : '';
if (!$ver || version_compare($ver, '1.70', '<')) { WP_CLI::error("The plugin here is version '$ver'; install 1.70 first, or the new sections would show without their styles."); }

$page = get_page_by_path('news-and-events/2018-bxscc-youth-ambassadors-program');
if (!$page) { WP_CLI::error('No page at /news-and-events/2018-bxscc-youth-ambassadors-program/.'); }
$site = untrailingslashit(home_url());
$new = str_replace('https://bxscc.org', $site, <<<'HTML'
<img class="wp-image-746 aligncenter" src="https://bxscc.org/wp-content/uploads/2018/09/Trip-1.png" alt="" width="516" height="344" />
<div class="bx-section">
<h1 style="text-align: center;"><strong>Trip Overview</strong></h1>
<table>
<tbody>
<tr>
<td colspan="2">
<p>On July 24th, Baltimore-Xiamen Sister City Vice Chair Danny Evans led a group of local high school students on a thirteen-day trip to Xiamen, Fujian and Jiading, Shanghai. The students, Aaron G., Alexander G., Kolby L., and Mateen K., were invited by the Xiamen and Jiading Offices of Foreign Affairs through BXSCC to participate in cultural and language activities in their cities.</p>
<p>Aaron, Alexander, Kolby, Mateen, and Danny started off their China trip in Xiamen with a variety of activities including, but not limited to, art and culture courses at the Youth and Children’s Palace, a visit to South Putuo Temple, and private tours of the BRICS Summit Facility, Xiamen University, and Gulangyu Island.</p>
<p>After Xiamen, our student ambassadors and Danny jetted off to Jiading District, Shanghai. There they attended the Jiading International Youth Friendship Camp, graciously hosted by the Jiading Office of Foreign Affairs. The Jiading International Youth Friendship Camp brought together exceptional students from across the globe together to learn more about Chinese culture. Aaron, Alexander, Kolby, and Mateen wonderfully represented Baltimore on an international stage by demonstrating aptitude in both intercultural communication and Chinese language skills.</p>
</td>
</tr>
</tbody>
</table>
</div>
<div class="bx-section">
<h1 style="text-align: center;"><strong>In the Media</strong></h1>
<table>
<tbody>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong><em>The Afro</em></strong></h3>
</td>
</tr>
<tr>
<td><em><a href="https://www.afro.com/baltimore-teacher-takes-students-to-china-to-educate-and-be-educated/">August 6, 2018</a></em></td>
<td>
<p><a href="https://www.afro.com/baltimore-teacher-takes-students-to-china-to-educate-and-be-educated/">"Baltimore Man Takes Students to China to Educate and Be Educated"</a></p>
<p>Trip leader Danny Evans, who taught in China on a teacher exchange from 2012 to 2014, on preparing the students for two weeks in China, and on the trip as a chance for them to teach others what it means to be Black, American, and from Baltimore.</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>xmenglish.cn</strong></h3>
</td>
</tr>
<tr>
<td><em><a href="http://www.xmenglish.cn/Feature/201808/t20180831_5213233.htm">August 31, 2018</a></em></td>
<td>
<p><a href="http://www.xmenglish.cn/Feature/201808/t20180831_5213233.htm">"Xiamen Shining in the Eyes of Two Baltimore Area Brothers"</a></p>
<p>Aaron and Alexander's own account of their days in Xiamen: the 2017 BRICS Summit venue, a performance at the Youth and Children's Palace, dinner and gifts from the city's Foreign and Overseas Chinese Affairs Office, downtown shops, Gulangyu Island, and a virtual reality tour of the city. In their words, the trip "exceeded all expectations."</p>
</td>
</tr>
<tr>
<td><em><a href="http://www.xmenglish.cn/DynamicInformation/201807/t20180731_5204695.htm">July 31, 2018</a></em></td>
<td>
<p><a href="http://www.xmenglish.cn/DynamicInformation/201807/t20180731_5204695.htm">"Baltimore Teenagers visit Xiamen in Sister-Cities Exchange Trip"</a></p>
<p>Report on the three-day exchange, July 26 to 28: the four ambassadors and five of the site's young reporters toured Xiamen University, Zhongshan Road, Gulangyu Island and the 2017 BRICS Summit venue, and took lessons in Chinese painting, calligraphy, drumming and paper cutting. Danny Evans called the trip "a great way to bring the two cities closer."</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>Jiading International Youth Friendship Camp</strong></h3>
</td>
</tr>
<tr>
<td><em><a href="https://www.meipian.cn/1i7uuo9k?share_depth=2&amp;user_id=0&amp;share_user_mpuuid=fa12a2e208a81aa35642840e34304cf7">August 7, 2018</a></em></td>
<td>
<p><a href="https://www.meipian.cn/1i7uuo9k?share_depth=2&amp;user_id=0&amp;share_user_mpuuid=fa12a2e208a81aa35642840e34304cf7">"Long Live With Our Friendship"</a></p>
<p>The camp's own bilingual recap, posted on Meipian, a Chinese photo-story app: the closing ceremony of the nine-day camp on August 5, 2018, with speeches by Jiading's education and foreign affairs officials; photos of classical gardens, pandas at the zoo, a school day and a family day with Jiading households; awards and national dances by campers; and tearful goodbyes at departure.</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;" lang="zh-Hans"><strong>厦门网</strong></h3>
</td>
</tr>
<tr>
<td lang="zh-Hans"><em><a href="http://www.xmnn.cn/xmnn/2018/07/26/100400617.shtml">2018年7月26日</a></em></td>
<td lang="zh-Hans">
<p><a href="http://www.xmnn.cn/xmnn/2018/07/26/100400617.shtml">"巴尔的摩学生代表团访厦 携手英文小记者体验厦门文化"</a></p>
<p>7月26日，巴尔的摩学生代表团参观金砖国家领导人会晤现场，与4名厦门网英文小记者一起开启厦巴友城交流活动；当天下午在厦门市青少年宫第一次拿起毛笔画兰花，并观看传统乐器和芭蕾舞表演。</p>
</td>
</tr>
</tbody>
</table>
</div>
<div class="bx-section">
<h1 style="text-align: center;"><strong>Reflections from 2018 Youth Ambassadors</strong></h1>
<div class="bx-reflections">
<div class="bx-reflection">
<figure><img class="wp-image-767" src="https://bxscc.org/wp-content/uploads/2018/09/image1-1-e1536206487504.jpeg" alt="" width="143" height="307" /><figcaption><strong>2018 Youth Ambassador Aaron G.</strong><em>3 years of Chinese studies</em><a href="https://drive.google.com/file/d/1zVGnRSPi-tOv2UzywuEI6fvMgj_QCWAn/view?usp=sharing" target="_blank" rel="noopener">Aaron's video testimonial</a></figcaption></figure>
<blockquote class="bx-quote">
<p>"We really enjoyed our hosts because they made me feel at ease and were really nice. I also enjoyed the food in Xiamen. Authentic Chinese is the best! The highway construction was really interesting and seemed gravity defying. The highway appeared to just hover. Really cool. Because of all these memorable places and people, my first trip to Xiamen was fantastic.</p>
<p>"In conclusion, Xiamen was an experience to remember, with our amazing Tokyo Drift Driver, the beautiful scenery, fantastic food, the kids we met and the activities at the Youth and Children’s Palace."</p>
</blockquote>
</div>
<div class="bx-reflection">
<figure><img class="wp-image-751" src="https://bxscc.org/wp-content/uploads/2018/09/Trip-3.png" alt="" width="396" height="185" /><figcaption><strong>2018 Youth Ambassador Alexander G.</strong><em>4 years of Chinese studies</em><a href="https://drive.google.com/file/d/1_kA-06VVLfxzyWpYurTdC4VeWdiYpE1Z/view?usp=sharing" target="_blank" rel="noopener">Alexander's video testimonial</a></figcaption></figure>
<blockquote class="bx-quote">
<p>"The trip to Shanghai taught me that people are more similar than you think. I have learned and experienced so much this past trip to Shanghai. I met so many kids from different backgrounds and cultures on this trip: kids from Japan, Serbia, France, England and New Zealand all converged in one place. They taught me some of their languages, slang and culture. It gave me insight into how each of them lived and their cultures."</p>
</blockquote>
</div>
<div class="bx-reflection">
<figure><img class="wp-image-808" src="https://bxscc.org/wp-content/uploads/2018/11/Trip-4.png" alt="" width="210" height="314" /><figcaption><strong>2018 Youth Ambassador Kolby L.</strong><em>9 years of Chinese studies</em><a href="https://drive.google.com/file/d/1scl5QvC-_ZgtgGuBAlpqEglEVXM20f79/view?usp=sharing" target="_blank" rel="noopener">Kolby's video testimonial</a></figcaption></figure>
<blockquote class="bx-quote">
<p>"My experience in Xiamen was amazing and I would love to visit again. Everything was so picturesque and there were even palm trees everywhere. The first day in Xiamen we went to see the 2017 BRICS XIAMEN SUMMIT venue, where some of the world’s most powerful leaders met. Later that day, we went to visit the Xiamen Youth &amp; Children’s Palace and had dinner with members of the Xiamen Municipal Foreign and Overseas Chinese Affairs Office."</p>
</blockquote>
</div>
<div class="bx-reflection">
<figure><img class="wp-image-809" src="https://bxscc.org/wp-content/uploads/2018/11/Trip-5.png" alt="" width="164" height="246" /><figcaption><strong>2018 Youth Ambassador Mateen K.</strong><em>9 years of Chinese studies</em></figcaption></figure>
<blockquote class="bx-quote">
<p>"After finishing my four-day adventure with my friends in the city Xiamen, I was exuberant about participating in the camp in Shanghai. As I sat on the plane, I mentally prepared myself to meet new people and experience new things. The camp was well-structured. It had the traditional format of breakfast to sightseeing, lunch to more sightseeing to dinner, which most foreign camps have. But it wasn’t the schedule that made the camp special; it was the campers. In the camp, I met a bunch of kids around my age, from all over the world, from New Zealand to Scotland, who I am now good friends with and that I hope to see again in the future."</p>
</blockquote>
</div>
</div>
</div>
<img class="size-full wp-image-748 aligncenter" src="https://bxscc.org/wp-content/uploads/2018/09/Trip-6.png" alt="" width="741" height="346" />
<div class="bx-section">
<h1 style="text-align: center;"><strong>Thanks</strong></h1>
<table>
<tbody>
<tr>
<td colspan="2">
<p>Thank you to Aaron, Alexander, Kolby, and Mateen for their efforts in representing Baltimore at the international stage, and for their support towards the Baltimore - Xiamen Sister City relationship!</p>
<p>A note from trip coordinator Danny Evans:</p>
<blockquote class="bx-quote">
<p>"I would like to thank our sponsors, the MECU Foundation and the Baltimore Community Foundation, for providing financial support to make this wonderful trip a possibility for these deserving students. Also, a special thank you to the BXSCC team: Chair Xin (Cindy) Wang, Treasurer Arella Liu, Baltimore Sister Cities, Inc. Vice President and BXSCC Member-at-Large Fontaine Bell, and Baltimore City Council's Director of Administration Hosea Chew, for their support of the program. We plan to continue this program in the future and encourage anyone who is interested to contact us via email at <a href="mailto:baltimorexiamen@gmail.com">baltimorexiamen@gmail.com</a>."</p>
</blockquote>
</td>
</tr>
</tbody>
</table>
</div>
HTML
);
$c = $page->post_content;
if ($c === $new) { WP_CLI::success('Nothing to do: the page already has the new layout.'); return; }
$was = md5(str_replace($site, 'https://bxscc.org', $c));
if ($was !== '2e10faa00a1ae2f7a0edf4eca225a8af') {
    WP_CLI::error("The page is not the version this was written against (fingerprint $was), so nothing was done. Paste this output; the page's content is needed to redo the change.");
}
WP_CLI::log("Page {$page->ID}: found the expected version (" . strlen($c) . ' characters); the new one has ' . strlen($new) . '.');
if ($mode !== 'run') { WP_CLI::success('Dry run: nothing changed. Run again with "run" to make the change.'); return; }

$r = wp_update_post(wp_slash(array('ID' => $page->ID, 'post_content' => $new)), true);
if (is_wp_error($r)) { WP_CLI::error($r->get_error_message()); }
clean_post_cache($page->ID);
if (get_post($page->ID)->post_content !== $new) { WP_CLI::error('Saved, but the stored content differs from what was written; check the page revisions.'); }
WP_CLI::success("Saved (page {$page->ID}). Empty the page cache next.");
