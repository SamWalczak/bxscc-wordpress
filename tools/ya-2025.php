<?php
/*
 * 2025 Youth Ambassadors page (/news-and-events/2025-bxscc-youth-ambassadors-program/): new layout, October 2026,
 * the same treatment as the 2018 and 2019 pages.
 * - Trip Overview: a section heading like the home page's and a table: the trip's dates beside the introduction, each
 *   leg's heading (was a bold line) as a row title, its paragraphs beside the dates they cover (checked against the
 *   daily itinerary). No wording changes there; the Word markup (spans, data attributes) removed.
 * - In the Media (was "Media Coverage of the 2025 Program"): one table by source; each item dated (date linked too)
 *   and summarized under its own line, the Chinese ones in Chinese; link titles and lines unchanged.
 * - Key Staff for the 2025 Program: a section heading with a table, one row per person. Tense: roles still held today
 *   (Leadership page, Oct 2026) stay present (Jeannette Felton, Sam Walczak); others past; lasting facts as written.
 * - Thanks and Appreciation (was Appreciation): a section heading with the text in a table.
 * - The "- - -" lines removed.
 * Needs plugin 1.75 or later.
 * From public_html:
 *   wp eval-file ~/ya-2025.php dry --use-include --skip-themes --skip-plugins   (checks the page; changes nothing)
 *   wp eval-file ~/ya-2025.php run --use-include --skip-themes --skip-plugins   (changes it)
 * WordPress keeps the previous version as a revision (page editor > Revisions).
 * --use-include: without it WP-CLI first scans the file with a pattern that runs out of PCRE stack on a page this
 * long (the 2025 page) and then silently runs nothing; include mode skips that scan. No output at all means it did not run.
 */
if (!defined('ABSPATH')) { exit; }
$mode = isset($args[0]) ? $args[0] : 'dry';
kses_remove_filters();   // save the content exactly as written (no user is logged in under WP-CLI)

$plugin = WP_PLUGIN_DIR . '/bxscc-wordpress-enhancements/bxscc-wordpress-enhancements.php';
$ver = is_file($plugin) ? get_file_data($plugin, array('v' => 'Version'))['v'] : '';
if (!$ver || version_compare($ver, '1.75', '<')) { WP_CLI::error("The plugin here is version '$ver'; install 1.75 first, or the new sections would show without their styles."); }

$page = get_page_by_path('news-and-events/2025-bxscc-youth-ambassadors-program');
if (!$page) { WP_CLI::error('No page at /news-and-events/2025-bxscc-youth-ambassadors-program/.'); }
$site = untrailingslashit(home_url());
$new = str_replace('https://bxscc.org', $site, <<<'HTML'
<img class="alignnone wp-image-858 size-full" src="https://bxscc.org/wp-content/uploads/2024/08/2024-7-25-Xiamen-FAO-DSC_2192-compressed-for-web.jpg" alt="" width="1008" height="475" />
<div class="bx-section">
<h1 style="text-align: center;"><strong>Trip Overview</strong></h1>
<table>
<tbody>
<tr>
<td><em>July 15 to August 1, 2025</em></td>
<td>
<p>Seven Baltimore youth participated in the 2025 Youth Ambassadors Program, celebrating the 40th anniversary of the Baltimore-Xiamen Sister City Committee (BXSCC) and the 45th anniversary of the Maryland-Anhui Province Sister State Committee. From July 15th to August 1st, 2025, the delegation, which also included two teachers and two parent chaperones, traveled to seven cities throughout China, including Xiamen in Fujian Province; Huangshan, Anqing, and Hefei in Anhui Province; Nanjing in Jiangsu Province; and Shanghai.</p>
<p>The Youth Ambassadors Program aims to develop student leadership by providing youth with the opportunity to learn what it means to be an international ambassador of peace and friendship at the local level. This year's program played a significant role in commemorating the two sister anniversaries by connecting past relations to the future. The Foreign Affairs Offices of both Xiamen and Anhui hosted the Youth Ambassadors, who presented citations from City of Baltimore and State of Maryland leaders to their Chinese counterparts. This exchange program strengthens the friendship between the two regions and builds connections for the future.</p>
<p>View the <a href="https://bxscc.org/wp-content/uploads/2025/08/2025-Youth-Ambassadors-Daily-Itinerary-7-15-to-8-1-v4-CN.pdf">detailed itinerary</a> from 2025.</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>Exploring Xiamen</strong></h3>
</td>
</tr>
<tr>
<td><em>July 17 to 22</em></td>
<td>
<p>The trip started with Xiamen from July 17th to July 22nd, where they joined Chinese youth to participate in the “Friends Traveling China, Exploring Xiamen—2025 U.S. Youth Ambassadors’ Ludao Tour”. For six days, the American and Chinese youth lived together, engaged in cultural and educational activities, and explored mutual understanding and friendship.</p>
<p>Xiamen, also known as Ludao (Egret Island), is a port city on China’s southeast coast, across from Taiwan. Previously a British treaty port from 1842 to 1912 known as Amoy, it includes the vehicle-free island of Gulangyu, renowned for its beaches, historical villas, and piano and organ museums. As one of China's original four special economic zones opened to foreign investment in the early 1980s, Xiamen today leads in ecological urban development.</p>
<p>During this educational and cultural tour, Baltimore youth learned about Xiamen's environmental protection efforts. They visited Yundang Lake Tidal Plaza, where they saw the transformation of a degraded lake into a revitalized area thanks to environmental measures. They also walked along the Mountain and Sea Health Trail, enjoying beautiful cityscapes and beaches. At Wuyuan Bay Wetland Park, they learned about conservation efforts that have created a sanctuary for over 300 bird species. A highlight of their trip was planting “Friendship Trees” in the wetland’s core protection area, symbolizing their commitment to fostering Sino-U.S. friendship.</p>
<p>While attaching name tags to the trees, Baltimore student Nex C. remarked, “I deeply admire Xiamen’s commitment to ecological restoration and species conservation. Xiamen’s beautiful ecology and extensive greenery astounded me. I felt that all planning and development were tailored to local conditions and centered around environmental protection.”</p>
<p>The trip also featured a visit to the beautiful campus of Xiamen University, where they met Professor William Brown, who shared insights on China's development over the past four decades. Engaging activities included a tea-talk drama class at Xiamen Foreign Language School where students performed scenes from the classic novel <i>Dream of the Red Chamber</i> and visits to cultural sites like the Eaglewood Incense Museum, where they experienced traditional incense seal burning while wearing Hanfu clothing. At Hongyanzhuang Puppet Art Museum, the students saw a puppetry performance and also made their own puppets to bring home. During a scavenger hunt utilizing public transportation, they further explored many other corners of Xiamen. The exchange concluded with a ceremony showcasing performances and certificate presentations, highlighting the bonds formed between American and Chinese youth.</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>Travel to Anhui Province as Youth Ambassadors of Maryland</strong></h3>
</td>
</tr>
<tr>
<td><em>July 23 and 24</em></td>
<td>
<p>Next, the Youth Ambassadors traveled to Anhui Province to celebrate the 45th anniversary of the Maryland-Anhui sister state/province relationship. They began in Huangshan, where they climbed the picturesque Yellow Mountains -- a sister park of Yosemite in the United States -- and took a photo in front of the thousand-years-old Greeting Pine (Yingkesong in Chinese). They marveled at Hongcun, a UNESCO World Heritage Site known for its well-preserved architecture and city planning as a rural village from feudal times. The site has become more well-known among foreign audiences as a site for shooting scenes from the film <i>Crouching Tiger, Hidden Dragon</i>. The students also attended experiential lessons on Five Animal style kungfu at Huangshan University and participated in a cooking class on how to make Stinky Mandarin Fish, an Anhui specialty cuisine taught by a famous local chef.</p>
</td>
</tr>
<tr>
<td><em>July 25</em></td>
<td>
<p>In Anqing, the students not only experienced a performance of Huangmei Opera, a precursor to Peking Opera, by local students, but also visited the Flying Tiger Museum, which honors the American volunteer group that aided China during World War II. Students noted how they hadn’t heard of the Flying Tigers and were happy to learn about a historical moment when the U.S. and China came together to fight against the Japanese invasion of China.</p>
<p>In Anqing, the Youth Ambassadors represented the State of Maryland during an online meeting with Anhui provincial officials and Maryland Secretary of State Susan Lee, as well as Ms. Mendy Nitsch, Director of International Affairs for the state. During the meeting, Secretary Lee read a Governor’s Proclamation in commemoration of the 45th anniversary of the Maryland-Anhui sister state/province relationship, which the delegation delivered to the Anhui officials at the meeting. Following words from the Anhui Provincial Foreign Affairs Office, each of the seven Baltimore youth shared their experiences in Anhui and gratitude for their hosts’ kind hospitality.</p>
<p>Sydney B. stated: “Being able to come to Anqing … and see the city where Anhui gets its name has been wonderful. Also interacting with Chinese students my age and learning the history of the Flying Tigers, has been the highlight of this trip. This is an experience that I will carry with me forever. I am honored to be here to celebrate the 45th anniversary of the sister state relationship between Maryland and Anhui and I can’t wait to see more of Anhui Province.”</p>
</td>
</tr>
<tr>
<td><em>July 26</em></td>
<td>
<p>The students’ time in Anhui wrapped up with a visit to the provincial capital of Hefei, where they visited the Anhui Provincial Museum and Luogang Park (Hefei Garden Expo Park), which redeveloped an old airport into a sprawling urban green space.</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>A Day Trip to Nanjing</strong></h3>
</td>
</tr>
<tr>
<td><em>July 27</em></td>
<td>
<p>On their way from Hefei to Shanghai, the students stopped in Nanjing, Jiangsu Province. They toured the Johns Hopkins University Nanjing Center (HNC), where they learned about training future experts in U.S.-China relations. Through this visit, the Baltimore youth were better able to envision themselves studying abroad in China not only as undergraduates student but also as graduate students in the future. We are grateful to the HNC staff, especially John Urban, deputy American co-director, who went above and beyond to welcome the students to HNC.</p>
<p>The Baltimore delegation also visited the Memorial Hall of the Victims of the Nanjing Massacre. On entry to the exhibition, they were struck by the simplicity and minimalism of the hall that at once solemnly places the visitor’s focus on each victims’ personal life. The dark entryway draws the visitor in to experience the sheer weight of history and the tragic loss of life. Continuing through the hall, the exhibits gradually appear in more open spaces welcoming in more natural light as stories of hope, including ones about American medical professionals who aided Chinese victims. In reflection, the Baltimore youth shared how the memorial hall left a deep impression on them. Learning of such tragic loss, they felt a renewed sense of determination to work for peace. Seeing how Americans and Chinese worked together to aid the victims showed it is possible for our two countries to work together for peace and human happiness.</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>Visiting Shanghai, China's Global Commercial Hub</strong></h3>
</td>
</tr>
<tr>
<td><em>July 27 to 31</em></td>
<td>
<p>A unique aspect of the BXSCC Youth Ambassadors Program is the opportunity to visit a different city that offers a fresh perspective on China. This year, students partnered with the Shanghai School of Business and Tourism for a five-day visit to the global financial center. They participated in various classes, such as porcelain repair, model clay designs, and Song Dynasty tea art, and visited famous sites such as the Oriental Pearl Tower, views of the Bund, Nanjing Road and the Shanghai Museum. Students also had the chance to meet with the staff of both Maryland Center China (the State of Maryland’s representative office in China) and the American Chamber of Commerce (AmCham) in Shanghai.</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>Welcoming Youth Ambassadors Back to Maryland</strong></h3>
</td>
</tr>
<tr>
<td><em>After August 1, 2025</em></td>
<td>
<p>Upon their return, the students and their families, along with chaperones and members of the BXSCC, visited the Maryland State Capitol in Annapolis for a meet-and-greet with Maryland Secretary of State Lee and Deputy Secretary of State Michael Lore.</p>
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
<h3 style="text-align: center;"><strong>Baltimore Mayor’s Office of Immigrant Affairs</strong></h3>
</td>
</tr>
<tr>
<td><em><a href="https://www.instagram.com/p/DMS8sA7RRwP/?">July 19, 2025</a></em></td>
<td>
<p>Shout-out by Baltimore’s Mayor’s Office of Immigrant Affairs, <a href="https://www.instagram.com/p/DMS8sA7RRwP/?">via Instagram</a></p>
<p>The Mayor’s Office of Immigrant Affairs (MIMA) on the send-off of Baltimore high schoolers to Xiamen as Youth Ambassadors, joined by representatives of the Enoch Pratt Free Library; thanks to the BXSCC for inviting MIMA to the kick-off event, and a pointer to the photo exhibit on the forty-year relationship on the second floor of the Pratt’s Central branch.</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>Xiamen TV</strong></h3>
</td>
</tr>
<tr>
<td lang="zh-Hans"><em><a href="https://share1.kxm.xmtv.cn/xmtv/2025-07-23/89f42567ee08de22.html">2025年7月23日</a></em></td>
<td lang="zh-Hans">
<p>Another video and article from our group’s time in Xiamen from <i>Xiamen TV</i>, all in Chinese: <a href="https://share1.kxm.xmtv.cn/xmtv/2025-07-23/89f42567ee08de22.html">友城来客！：当巴尔的摩遇见厦门 友谊地久天长</a></p>
<p>《厦视直播室》报道：中美青年登鼓浪屿参观钢琴博物馆和管风琴艺术中心，逛中山路骑楼、品尝厦门小吃，观看木偶戏，体验剪纸、茶艺等非遗技艺；部分美国青年是第二次来厦门，与去年结对的中国伙伴重逢；结营仪式上，中美学员互赠礼物，在帽檐上按指纹拼成爱心。</p>
</td>
</tr>
<tr>
<td lang="zh-Hans"><em><a href="https://share1.kxm.xmtv.cn/xmtv/2025-07-23/f2e4aa553d88cedb.html">2025年7月23日</a></em></td>
<td lang="zh-Hans">
<p>Video and article from our group’s time in Xiamen from <i>Xiamen TV</i>, all in Chinese: <a href="https://share1.kxm.xmtv.cn/xmtv/2025-07-23/f2e4aa553d88cedb.html">2025美国“青年大使”鹭岛行活动举行</a></p>
<p>《午间新闻广场》报道：适逢厦门与巴尔的摩缔结友城40周年，巴尔的摩和加州橙郡的27名青年学员、教师和友好人士与17名中国青年同住、同学、同行。中美青年在厦门外国语学校把《红楼梦》改编成舞台剧、学做漆扇，并到中山路、五缘湾和美图公司参观交流；巴尔的摩-厦门姐妹城市委员会副主席何娇霞谈学习中国艺术与文化的意义。</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>“Broad Pacific”, Department of North American and Oceanian Affairs, Ministry of Foreign Affairs of China</strong></h3>
</td>
</tr>
<tr>
<td lang="zh-Hans"><em><a href="https://mp.weixin.qq.com/s/AKBDaVdr8Lbtziwj4o_7GA?scene=1">2025年7月24日</a></em></td>
<td lang="zh-Hans">
<p>Article posted on the Department of North American and Oceanian Affairs of the Ministry of Foreign Affairs of China’s microblog “Broad Pacific”, all in Chinese: <a href="https://mp.weixin.qq.com/s/AKBDaVdr8Lbtziwj4o_7GA?scene=1">携手青春之约 共筑友谊之桥</a></p>
<p>外交部北美大洋洲司微信公众号“宽广太平洋”发布：7月17日至22日，巴尔的摩和加州橙郡的27名青年、教师和友好人士与17名中国青年参加“友行中国・趣淘厦门”鹭岛行。文章介绍筼筜湖、山海健康步道和五缘湾湿地的生态探寻与“友谊树”种植，厦门大学潘维廉教授的分享，厦门外国语学校和厦门市音乐学校的课堂，以及中山路、鼓浪屿和非遗体验；巴尔的摩-厦门姐妹城市委员会副主席Chelsea Nakabayashi谈青年们对“青年大使”使命的认真投入。</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>Embassy of the People’s Republic of China in the United States</strong></h3>
</td>
</tr>
<tr>
<td lang="zh-Hans"><em><a href="https://mp.weixin.qq.com/s/JcjTzc4NPyaF0X780hOuDg?scene=1">2025年7月24日</a></em></td>
<td lang="zh-Hans">
<p>The same content and title, posted on the Embassy of the People’s Republic of China in the United States’ microblog, all in Chinese: <a href="https://mp.weixin.qq.com/s/JcjTzc4NPyaF0X780hOuDg?scene=1">携手青春之约 共筑友谊之桥</a></p>
<p>比“宽广太平洋”晚约40分钟发布，内容见上一条。</p>
</td>
</tr>
</tbody>
</table>
</div>
<div class="bx-section">
<h1 style="text-align: center;"><strong>Key Staff for the 2025 Program</strong></h1>
<table>
<tbody>
<tr>
<td colspan="2"><p><strong>Chelsea Nakabayashi</strong> was the BXSCC’s Vice Chair and a faculty member at Bard High School Early College–Baltimore. She was responsible for outreach to and support of the youth ambassadors and their families, and served–for a second year in a row–as the lead chaperone of the program. Nakabayashi holds a BA from the University of Wisconsin–Madison, MAs from the Johns Hopkins University’s School of Advanced International Studies (SAIS) and the University of Masschusetts–Amherst, and a PhD from Beijing Normal University. As a Maryland state-certified educator in Chinese, Nakabayashi enjoys providing Baltimore youth with opportunities to build friendly U.S-China ties.</p></td>
</tr>
<tr>
<td colspan="2"><p><strong>Jeannette Felton</strong> is the BXSCC’s long-time Education Subcommittee Chair.</p></td>
</tr>
<tr>
<td colspan="2"><p><strong>Justin Lipsky</strong> was the Treasurer of the BXSCC and the key staff handling financial matters for the 2025 Youth Ambassadors Program.</p></td>
</tr>
<tr>
<td colspan="2"><p><strong>Rosanna Mantova</strong> was the Assistant Director of International Affairs at Maryland’s Office of the Secretary of State and the lead liaison between the Office of the Secretary of State, the Anhui Foreign Affairs Office, and the BXSCC for the 2025 Youth Ambassadors Program.</p></td>
</tr>
<tr>
<td colspan="2"><p><strong>Xin (Cindy) Wang</strong> was the BXSCC’s Chair and responsible for overseeing the organization’s overall structural support for this program. Cindy is a past recipient of the Gates Millennium Scholarship for high-achieving students with significant financial need, studied abroad during college through the Council on International Educational Exchange (CIEE), and has served as a volunteer English-language lecturer in rural Jiangxi Province, China. At the time, she managed strategic operations for a Maryland machinery manufacturer.</p></td>
</tr>
<tr>
<td colspan="2"><p><strong>Sam Walczak</strong> is the BXSCC’s Secretary, with responsibilities including maintaining the BXSCC website and communications. Sam has previously lived and worked in China as a software engineering project manager and has an academic background focused on U.S.-China academic exchanges.</p></td>
</tr>
<tr>
<td colspan="2"><p><strong>Fang Wu</strong> was a key contributor on planning meeting records-keeping and the lead liaison to participant families during the trip itself.</p></td>
</tr>
</tbody>
</table>
</div>
<div class="bx-section">
<h1 style="text-align: center;"><strong>Thanks and Appreciation</strong></h1>
<table>
<tbody>
<tr>
<td colspan="2">
<p>The Youth Ambassadors Program staff expresses gratitude to all who made this trip possible, including the families of the Baltimore Youth Ambassadors delegation members, <a href="https://baltimoresistercities.org/" target="_blank" rel="noreferrer noopener">Baltimore Sister Cities, Inc.</a>, the Office of Baltimore City Mayor Brandon Scott, the Office of the City Council President of Baltimore, the <a href="https://www.prattlibrary.org/" target="_blank" rel="noreferrer noopener">Enoch Pratt Free Library System</a>, <a href="https://baltimore.bard.edu/" target="_blank" rel="noreferrer noopener">Bard High School Early College (BHSEC)-Baltimore</a>, the Office of the Secretary of State of Maryland, the Maryland-Anhui Province Sister State Committee, Maryland Center China, the Xiamen Municipal Government's Foreign Affairs Office, <a href="https://www.xiamenair.com/en-us/" target="_blank" rel="noreferrer noopener">Xiamen Airlines</a>, the Anhui Province Foreign Affairs Office, the Anhui Province Department of Education, the <a href="https://sais.jhu.edu/hopkins-nanjing-center" target="_blank" rel="noreferrer noopener">Johns Hopkins University–Nanjing University Center for Chinese and American Studies</a>, the Foreign Affairs Office of the Shanghai Municipal People's Government, the <a href="https://smly.hpe.cn/" target="_blank" rel="noreferrer noopener">Shanghai School of Business and Tourism</a>, the <a href="https://www.amcham-shanghai.org/" target="_blank" rel="noreferrer noopener">American Chamber of Commerce in Shanghai</a>, the Embassy of the People's Republic of China in the United States, and many other individuals and organizations that contributed to the program’s success.</p>
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
if ($was !== '83b99f4880923de0c06d19c11fbb4b02') {
    WP_CLI::error("The page is not the version this was written against (fingerprint $was), so nothing was done. Paste this output; the page's content is needed to redo the change.");
}
WP_CLI::log("Page {$page->ID}: found the expected version (" . strlen($c) . ' characters); the new one has ' . strlen($new) . '.');
if ($mode !== 'run') { WP_CLI::success('Dry run: nothing changed. Run again with "run" to make the change.'); return; }

$r = wp_update_post(wp_slash(array('ID' => $page->ID, 'post_content' => $new)), true);
if (is_wp_error($r)) { WP_CLI::error($r->get_error_message()); }
clean_post_cache($page->ID);
if (get_post($page->ID)->post_content !== $new) { WP_CLI::error('Saved, but the stored content differs from what was written; check the page revisions.'); }
WP_CLI::success("Saved (page {$page->ID}). Empty the page cache next.");
