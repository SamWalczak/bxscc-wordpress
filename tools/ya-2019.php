<?php
/*
 * 2019 Youth Ambassadors page (/news-and-events/2019-bxscc-youth-ambassadors-program/): new layout, October 2026,
 * the same treatment as the 2018 page.
 * - Trip Overview: a section heading like the home page's, the dates and text in a table under it (one cell, the
 *   dates in italics as the first line, as before).
 * - In the Media: the China Daily article dated (date linked too) and summarized; link title unchanged.
 * - Reflections from 2019 Youth Ambassadors: no photos; each ambassador's title and years of study above the quote,
 *   a divider between ambassadors.
 * - Key 2019 Program Staff: a section heading with a table, one row per person (tense brought to the past).
 * - Thanks and Appreciation (was Appreciation): a section heading with the text in a table; the donation line removed.
 * Needs plugin 1.73 or later (the styles for reflections without photos, and italics in section-table text).
 * Works on the page as it was, or on the first version of this layout (Oct 8, 14:00, with a date column).
 * From public_html:
 *   wp eval-file ~/ya-2019.php dry --skip-themes --skip-plugins   (checks the page; changes nothing)
 *   wp eval-file ~/ya-2019.php run --skip-themes --skip-plugins   (changes it)
 * WordPress keeps the previous version as a revision (page editor > Revisions).
 */
if (!defined('ABSPATH')) { exit; }
$mode = isset($args[0]) ? $args[0] : 'dry';
kses_remove_filters();   // save the content exactly as written (no user is logged in under WP-CLI)

$plugin = WP_PLUGIN_DIR . '/bxscc-wordpress-enhancements/bxscc-wordpress-enhancements.php';
$ver = is_file($plugin) ? get_file_data($plugin, array('v' => 'Version'))['v'] : '';
if (!$ver || version_compare($ver, '1.73', '<')) { WP_CLI::error("The plugin here is version '$ver'; install 1.73 first, or the new sections would show without their styles."); }

$page = get_page_by_path('news-and-events/2019-bxscc-youth-ambassadors-program');
if (!$page) { WP_CLI::error('No page at /news-and-events/2019-bxscc-youth-ambassadors-program/.'); }
$site = untrailingslashit(home_url());
$new = str_replace('https://bxscc.org', $site, <<<'HTML'
<img class="aligncenter wp-image-859" src="https://bxscc.org/wp-content/uploads/2019/07/Screen-Shot-2019-07-21-at-11.00.07-PM.png" />
<div class="bx-section">
<h1 style="text-align: center;"><strong>Trip Overview</strong></h1>
<table>
<tbody>
<tr>
<td colspan="2">
<p><em>June 24th to July 4th, 2019 and various</em></p>
<p>BXSCC Vice Chair Danny Evans led this visit of 16 Baltimore and Maryland high school-aged students and 2 other chaperones to Xiamen and Beijing. The BXSCC obtained funds via cash grants, donations, and fundraising activities to help offset trip costs. Organizational &amp; business in-kind sponsors included Chick-Fil-A, Starbucks, Shake Shack, Blaze Pizza, Papa John’s, and Xiamen Shuangshi Middle / High School.</p>
<p>Summer trip activities included: In Xiamen, students took classes in airplane modeling, tai chi, calligraphy, folk music &amp; music appreciation, Minnan-style Mid-Autumn Festival culture, literature, martial arts, polymer clay, and more at Xiamen Shuangshi Middle / High School. Participants visited Xiamen Airlines, the Xiamen International Convention and Exhibition Center / BRICS Xiamen Summit venue, Xiamen University, Nanputuo Temple, Gulangyu Island, and engaged with the Xiamen Office of Foreign Affairs. In Beijing, the students visited Peking University, Tian’anmen Square, the Palace Museum, the Beijing American Center (U.S. Embassy in Beijing), the Juyongguan section of the Great Wall, and Beijing Olympic Park.</p>
<p>Associated events included: Two kick-off briefings (on December 7th, 2018 and January 18th, 2019), three participant and parents meetings (on March 29th, May 3rd, &amp; June 14th), a visit to the State Department and USAID in Washington, D.C. (on June 21st), a Small Business Administration presentation (on July 24th), a debriefing with Mayor Bernard C. “Jack” Young (on July 29th), and a U.S. Senate Foreign Relations Panel discussion in Washington, D.C. (on August 29th). At an August 2nd closing reception &amp; awards ceremony, three Youth Ambassadors were presented with certificates and gift cards for their excellent participation in the Youth Ambassadors program.</p>
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
<h3 style="text-align: center;"><strong><em>China Daily Global Edition</em></strong></h3>
</td>
</tr>
<tr>
<td><em><a href="https://govt.chinadaily.com.cn/s/201907/19/WS5d31328a498ed6fd11ca1faa/baltimore-students-wowed-by-xiamen.html" target="_blank" rel="noopener noreferrer">July 19, 2019</a></em></td>
<td>
<p><a href="https://govt.chinadaily.com.cn/s/201907/19/WS5d31328a498ed6fd11ca1faa/baltimore-students-wowed-by-xiamen.html" target="_blank" rel="noopener noreferrer">"Baltimore students wowed by Xiamen"</a></p>
<p>Sixteen Baltimore high school students and three chaperones in Xiamen and Beijing, June 24 to July 4: Xiamen Shuangshi High School, Xiamen University and Peking University. Madison, Jahnae and Camille on welcoming Chinese peers, a friendship kept up on WeChat, and Xiamen University's marine programs; Danny Evans on the program's goals of mutual understanding and leadership.</p>
</td>
</tr>
</tbody>
</table>
</div>
<div class="bx-section">
<h1 style="text-align: center;"><strong>Reflections from 2019 Youth Ambassadors</strong></h1>
<div class="bx-reflections">
<div class="bx-reflection">
<div class="bx-reflection-who"><strong>2019 Youth Ambassador Elijah A.</strong><em>9 Years of Chinese Studies</em></div>
<blockquote class="bx-quote">
<p>"[I]t was my first trip to China, and my first time out of the United States. It was warmer than I expected. The heat was something that I never experienced to that degree. The cities were so pretty. The architecture of the buildings was amazing. I wish I took more pictures. It was also fascinating to meet students of a different culture."</p>
<p>"I’ve been learning Chinese for a total of 9 years. I started when I was five years old in kindergarten. I attended a language emersion school for elementary and middle school. I wanted to continue learning Chinese because it's a cool language to learn, and my mom doesn’t know what I’m saying."</p>
<p>"This trip helped me learn more about Chinese culture. It taught me about the different foods, how to eat them and how to write in Chinese using a “mao bi 毛笔”. There were so many cultural activities every day that I couldn’t wait for the next day to come."</p>
<p>"One interesting story is that the ice cream we had was the best ice cream I ever tasted in my life! Also, eating from a rotating table was so different, I wish I had one at home. Also, I hope the Baltimore and Xiamen friendship does not end. It was such a great opportunity, and I will always remember my time in China."</p>
</blockquote>
</div>
<div class="bx-reflection">
<div class="bx-reflection-who"><strong>2019 Youth Ambassador Jahnae D.</strong><em>Approaching 1 Year of Chinese Culture Studies</em></div>
<blockquote class="bx-quote">
<p>"[This] was my first trip to China and being outside the country. My first impression on Xiamen was that I could tell it was a place for people to live in instead of doing business in. It’s a beautiful place that’d be wonderful to call home. Seeing the buildings and tourist places that it’s filled with showed that Xiamen is filled with a lot of history but also very modern. Working with the Chinese students and being able to meet some of them was my favorite part being in China. Being able to communicate with them and understand that we’re not so different was a huge part of what made the trip so amazing."</p>
<p>"This trip has helped me gain a little more knowledge of China. Before going to China, I’d taken a Chinese culture class, so I’d already known some things about China. I’d known the history of Mao, about concubines, and the cultural differences. I also got to learn more things that I didn’t get to learn in class while visiting China. I learned about Gulangyu 鼓浪屿 island, which has a lot of old architecture and history behind it."</p>
<p>"I have made many Chinese friends! I’ve made a friend that I talk to every day since I’ve left China! I chat with her through WeChat, and we get to exchange what is different here in America and there in China. I hope to see my friends again someday, either in China or here in Baltimore. The opportunity to bring them to Baltimore would be a fun experience because most of the people I’d met had never been to America before. I’d love to get the chance to introduce them to some of the things that we have here that they don’t have."</p>
</blockquote>
</div>
<div class="bx-reflection">
<div class="bx-reflection-who"><strong>2019 Youth Ambassador Madison H.</strong><em>4 Years of Chinese Studies</em></div>
<blockquote class="bx-quote">
<p>"This was my first time in China. I was immediately and pleasantly surprised at how welcoming all the students were. They were a lot of fun to work with, and they had such excellent English skills. Xiamen was so pretty to visit. Every place we visited had trees and flowers, it was all so calming, even though the actual city was quite bustling with all the traffic."</p>
<p>"I’ve learned Chinese for a total of four years. I want to keep learning because I think it’s becoming a more popular foreign language in the USA, especially Maryland. I also love learning foreign languages, and Chinese will be super useful as I enter the workforce, especially in my desired field."</p>
<p>"This trip made me think about all the little cultural differences between Chinese culture. For example, education and transportation, things that I’ve never questioned in my day to day lifestyle but are significantly different for the students and people in China. Before I traveled to China, the only knowledge I had about China was from my history class textbook. However, this trip has allowed me to understand just what it looks like to be in a developing country, such as China."</p>
<p>"Baltimore and Xiamen’s friendship is an excellent example for other cities in the US. Even when national ties are shaky, local connections can be the key to keeping things stable. If more cities in the US had relationships with cities in China, I think it would mean that country relations with China would slowly get better as well."</p>
</blockquote>
</div>
<div class="bx-reflection">
<div class="bx-reflection-who"><strong>2019 Youth Ambassador Camille L.</strong><em>6 Years of Chinese Studies</em></div>
<blockquote class="bx-quote">
<p>"This [was] my second trip to China. I visited Yangz[h]ou and Shanghai a few years ago. I had an enjoyable time when I spent time with the Chinese students. I learned that we have a lot of things in common."</p>
<p>"I have learned Chinese for six years now. I started when I was young because my parents wanted me to learn the language. But now that I am older and because I have been learning for so long, I have come to feel proud that I can speak this complex language. I see myself growing up and working in a job where my language skills will help me."</p>
<p>"I have learned more about Chinese culture thanks to this last trip. The more I learn about China I understand how much history there is and that's very interesting to me. While visiting the Forbidden City, I could honestly imagine what life was like for the people hundreds of years ago."</p>
<p>"For me, I was very interested in visiting Xiamen University because I had researched the prestigious university. I want to be a Marine Biologist when I graduate college, and I read that Xiamen University had a program that might be good for me in that area. I was then very excited when we visited Peking University because I was able to another [school’s] perspective."</p>
</blockquote>
</div>
</div>
</div>
<img loading="lazy" class="alignnone wp-image-858 size-full" src="https://bxscc.org/wp-content/uploads/2024/03/2019-6-27-Image_20190630203727-compressed.jpg" alt="" width="1008" height="672" />
<div class="bx-section">
<h1 style="text-align: center;"><strong>Key 2019 Program Staff</strong></h1>
<table>
<tbody>
<tr>
<td colspan="2"><p>The BXSCC members and associates who planned the Program had extensive experience living and/or working in China. The team included Baltimore City Chinese teachers, who could help ensure that these students were enriched through language immersion learning throughout the course of the study tour. Key program staff included:</p></td>
</tr>
<tr>
<td colspan="2"><p><strong>Danny Evans</strong> was the BXSCC’s Vice Chair and the manager of the Youth Ambassadors Program. Danny was a chaperone for the 2018 and 2019 programs. He had served as a WorldTeach and Chinese Red Cross volunteer. For WorldTeach, he was named Teacher of the Year for Changsha, Hunan Province, China because of the students’ achievements and for serving as an international ambassador for delegations. As a Chinese Red Cross volunteer, he assisted seniors with medical support in underprivileged areas. Since his return from China, he had been committed to providing study-aboard programs for high school students, encouraging them to pursue careers in China affairs.</p></td>
</tr>
<tr>
<td colspan="2"><p><strong>Yu-San (Trista) Lai</strong> was a high school Chinese teacher in Baltimore City. She had six years of teaching experience including summer intensive programs in Indiana University and PiB (Princeton in Beijing). Trista was selected as a cultural exchange student to study in Ohio in 2009 and had volunteered in northern Thailand and rural tribal village in eastern Taiwan and other community events. Trista had extensive experiences in organizing cultural excursions, overseeing the tasks, and planning various events.</p></td>
</tr>
<tr>
<td colspan="2"><p><strong>Arella Liu</strong> was the BXSCC’s Treasurer from 2014 until her passing in 2020. The 2018 and 2019 Youth Ambassadors programs' concept was born of her interactions with visiting delegations from China during her time with the BXSCC and her own experience years earlier as an international student from China. A native of Beijing, Arella was an avid traveler in her time away from a very successful career as a financial analyst with a major insurance / financial services firm.</p></td>
</tr>
<tr>
<td colspan="2"><p><strong>Xin (Cindy) Wang</strong> was the BXSCC’s Chair and responsible for overseeing the organizations’ overall structural support for the Program. Cindy was a past recipient of the Gates Millennium Scholarship for high-achieving ethnic minority students with significant financial need, studied abroad through the Council on International Educational Exchange (CIEE) during college, and had served as a volunteer English-language lecturer in rural Jiangxi Province, China. At the time, she worked for a U.S. manufacturing company with responsibilities related to their export business.</p></td>
</tr>
</tbody>
</table>
</div>
<div class="bx-section">
<h1 style="text-align: center;"><strong>Thanks and Appreciation</strong></h1>
<table>
<tbody>
<tr>
<td colspan="2"><p>The Youth Ambassadors Program staff, participants, &amp; parents thank the following entities and individuals for their generous grants and donations to the 2019 program: The <a href="https://www.bcf.org/" target="_blank" rel="nofollow noopener noreferrer">Baltimore Community Foundation</a>'s Van Buskirk International Exchange Fund, <a href="http://www.baltimorecitycouncil.com/bernard-young" target="_blank" rel="nofollow noopener noreferrer">Baltimore City Council President Bernard C. "Jack" Young’s P.L.A.Y. Campaign</a>, <a href="http://baltimoresistercities.org/" target="_blank" rel="nofollow noopener noreferrer">Baltimore Sister Cities</a>, <a href="https://www.constellation.com/" target="_blank" rel="nofollow noopener noreferrer">Constellation</a>, the Lerner family, and the <a href="https://www.mecu.com/About-MECU" target="_blank" rel="nofollow noopener noreferrer">MECU Foundation</a>. We also thank <a href="https://www.chick-fil-a.com/" target="_blank" rel="nofollow noopener noreferrer">Chick-Fil-A</a>, <a href="https://www.nalleyfresh.com/" target="_blank" rel="nofollow noopener noreferrer">Nalley Fresh</a>, <a href="https://www.shakeshack.com/" target="_blank" rel="nofollow noopener noreferrer">Shake Shack</a>, <a href="https://www.starbucks.com/" target="_blank" rel="nofollow noopener noreferrer">Starbucks</a>, and Mr. Hosea Chew for their in-kind donations."</p></td>
</tr>
</tbody>
</table>
</div>
HTML
);
$c = $page->post_content;
if ($c === $new) { WP_CLI::success('Nothing to do: the page already has the new layout.'); return; }
$was = md5(str_replace($site, 'https://bxscc.org', $c));
$known = array('889866b8aeb9fd44ed93ec878d38d190' => 'the original', '21f7291a121c85e0159da9988e2add89' => 'the first version of the new layout');
if (!isset($known[$was])) {
    WP_CLI::error("The page is not the version this was written against (fingerprint $was), so nothing was done. Paste this output; the page's content is needed to redo the change.");
}
WP_CLI::log("Page {$page->ID}: found {$known[$was]} (" . strlen($c) . ' characters); the new one has ' . strlen($new) . '.');
if ($mode !== 'run') { WP_CLI::success('Dry run: nothing changed. Run again with "run" to make the change.'); return; }

$r = wp_update_post(wp_slash(array('ID' => $page->ID, 'post_content' => $new)), true);
if (is_wp_error($r)) { WP_CLI::error($r->get_error_message()); }
clean_post_cache($page->ID);
if (get_post($page->ID)->post_content !== $new) { WP_CLI::error('Saved, but the stored content differs from what was written; check the page revisions.'); }
WP_CLI::success("Saved (page {$page->ID}). Empty the page cache next.");
