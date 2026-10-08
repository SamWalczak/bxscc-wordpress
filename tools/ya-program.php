<?php
/*
 * Youth Ambassadors Program page (/youth-ambassadors-program/, page 917): new layout, October 2026.
 * - Program Information: the six questions as one section heading and table, each question a row title with its
 *   answer under it; the donation button stays under the cost answer, centered, with even space above and below it
 *   (22px each). "When" now reads "The last trip was July 14-31, 2026."; "Where" reads "In 2026 we traveled to
 *   Xiamen, Anhui and Beijing." (the Fuzhou stop was dropped on June 20).
 * - Key Staff for the 2026 Program (was 2025): a section heading with a table, one row per person by surname. Hosea
 *   Chew added; entries updated for what each person did in 2026 (Jeannette's and Justin's as they were).
 * - Media Coverage of Past Trips: one table by trip, newest first; each item dated (the date links to it too) and
 *   summarized, the Chinese ones in Chinese. Media Coverage of the 2025 Trip folded in (three highlights; the
 *   embassy's copy and the second Xiamen TV report stay on the 2025 page). Two vague 2018 labels name their sources.
 * - Reflections from Past Program Participants: the 2019 page's format; quotes verbatim (the Word markup removed).
 * - Thanks: a section heading with the text in a table, wording unchanged.
 * - The "- - -" lines removed; the photos and Profiles of Past Programs (the year list) unchanged.
 * Works on the page as it was, on the version approved at 17:23 on Oct 8, or on the 17:41 version (button centered,
 * spacing not yet even).
 * Needs plugin 1.75 or later.
 * From public_html:
 *   wp eval-file ~/ya-program.php dry --use-include --skip-themes --skip-plugins   (checks the page; changes nothing)
 *   wp eval-file ~/ya-program.php run --use-include --skip-themes --skip-plugins   (changes it)
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

$page = get_page_by_path('youth-ambassadors-program');
if (!$page) { WP_CLI::error('No page at /youth-ambassadors-program/.'); }
$site = untrailingslashit(home_url());
$new = str_replace('https://bxscc.org', $site, <<<'HTML'
<img class="alignnone wp-image-858 size-full" src="https://bxscc.org/wp-content/uploads/2024/08/2024-7-25-Xiamen-FAO-DSC_2192-compressed-for-web.jpg" alt="" width="1008" height="475" />
<div class="bx-section">
<h1 style="text-align: center;"><strong>Program Information</strong></h1>
<table>
<tbody>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>What is the Youth Ambassadors Program?</strong></h3>
</td>
</tr>
<tr>
<td colspan="2">
<p>The Youth Ambassadors Program is the BXSCC’s student leadership development program, which may include a trip to China. The chaperoned trip consists of site visits, educational classes, and cultural immersion. Baltimore / DC-area site visits may also be included as part of the program.</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>Where Do We Go?</strong></h3>
</td>
</tr>
<tr>
<td colspan="2">
<p>Every trip, we go to our sister city of Xiamen. In 2026 we traveled to Xiamen, Anhui and Beijing.</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>When is the Youth Ambassadors Program?</strong></h3>
</td>
</tr>
<tr>
<td colspan="2">
<p>The last trip was July 14-31, 2026.</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>Who is Eligible?</strong></h3>
</td>
</tr>
<tr>
<td colspan="2">
<p>All Youth Ambassadors trip student participants should be 14 to 18-year-old Maryland residents (or Maryland-based), with priority given to Baltimore City residents or students. Rising high school freshmen and graduating seniors are eligible.</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>What Does the Program Cost?</strong></h3>
</td>
</tr>
<tr>
<td colspan="2">
<p>The value of the program is over $7,000 per participant, inclusive of all transportation, lodgings, meals, and group activities. Actual program costs will vary from year to year*, pending fundraising levels. As a nonprofit organization, the BXSCC will do our best to make the program affordable through fundraising and grants. Leftover funds after the program has completed are refunded to the Youth Ambassadors. If you are interested in donating to this program, please follow the link below; specify the BXSCC and its Youth Ambassadors Program as the beneficiary.</p>
<form style="padding: 0; margin: 22px 0 14px; text-align: center;" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top"><input name="cmd" type="hidden" value="_s-xclick" /><input name="hosted_button_id" type="hidden" value="4SH5RSPZMFMGY" /><input style="padding: 0; margin: 0 auto; display: block;" alt="PayPal - The safer, easier way to pay online!" name="submit" src="https://baltimoresistercities.org/wp-content/uploads/2018/02/donate-2.png" type="image" /><img class="aligncenter" style="display: none;" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" alt="" width="1" height="1" border="0" /></form>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>When Do I Apply?</strong></h3>
</td>
</tr>
<tr>
<td colspan="2">
<p>If you are interested in getting involved in the Youth Ambassadors Program, please contact the BXSCC.</p>
</td>
</tr>
</tbody>
</table>
</div>
<div class="bx-section">
<h1 style="text-align: center;"><strong>Key Staff for the 2026 Program</strong></h1>
<table>
<tbody>
<tr>
<td colspan="2">
<p><strong>Hosea Chew</strong> is the BXSCC’s Member-at-Large. He was on the 2026 planning team, approved the program’s deposit and reimbursement requests as one of the committee’s officers, and joined the youth ambassadors when they reported on the trip to Maryland Secretary of State Susan Lee in August.</p>
</td>
</tr>
<tr>
<td colspan="2">
<p><strong>Jeannette Felton</strong> is the BXSCC’s long-time Education Subcommittee Chair and brings an extensive knowledge of the Baltimore community to the committee. Her commitment over the years has had a great impact on the efforts of BXSCC to provide Baltimore youth with opportunities to learn about China and share Baltimore culture with their Chinese counterparts.</p>
</td>
</tr>
<tr>
<td colspan="2">
<p><strong>Justin Lipsky</strong> is the Vice Chair of the BXSCC and provides overall support for the program through seeking greater connections with American businesses in Baltimore working with Chinese counterparts. He also helps to liaise with our Chinese counterparts in Xiamen.</p>
</td>
</tr>
<tr>
<td colspan="2">
<p><strong>Rosanna Mantova</strong> is the Assistant Director of International Affairs at Maryland’s Office of the Secretary of State and was the lead liaison between the Office of the Secretary of State, the Anhui Foreign Affairs Office, and the BXSCC for the 2026 Youth Ambassadors Program. She secured the support of Secretary Lee and the Maryland-Anhui Sister State Committee for the program and, with Director of International Affairs Mary (Mendy) Nitsch, set up the youth ambassadors’ report to Secretary Lee in August.</p>
</td>
</tr>
<tr>
<td colspan="2">
<p><strong>Chelsea Nakabayashi</strong> is the BXSCC’s Chair and a state-certified Chinese educator at Baltimore City College, whose Chinese program partnered with the BXSCC on the 2026 program. She was responsible for outreach to and support of the youth ambassadors and their families, met with the Beijing Foreign Affairs Office to set up the Beijing portion of the trip, and served–for a third year in a row–as the lead chaperone of the program. Nakabayashi holds a PhD from Beijing Normal University and seeks to provide youth with opportunities to build friendship and peace between the US and China.</p>
</td>
</tr>
<tr>
<td colspan="2">
<p><strong>Sam Walczak</strong> is the BXSCC’s Secretary, with responsibilities including maintaining the BXSCC website and communications. </p>
</td>
</tr>
<tr>
<td colspan="2">
<p><strong>Xin (Cindy) Wang</strong> is the BXSCC’s Treasurer and its main liaison to the Xiamen Foreign Affairs Office, and was the key staff handling financial matters for the 2026 Youth Ambassadors Program, from participant payments and refunds to booking flights, travel insurance and train tickets in China. She is the previous chair of the BXSCC, a role she held for almost a decade. She is a past recipient of the Gates Millennium Scholarship for high-achieving students with significant financial need, studied abroad during college through the Council on International Educational Exchange (CIEE), and has served as a volunteer English-language lecturer in rural Jiangxi Province, China. She presently manages strategic operations for a Maryland machinery manufacturer.</p>
</td>
</tr>
<tr>
<td colspan="2">
<p><strong>Fang Wu</strong> is a member of the BXSCC and was on the 2026 planning team. For a second year in a row, she was the lead liaison to participant families during the trip itself, keeping them updated over WeChat.</p>
</td>
</tr>
</tbody>
</table>
</div>
<div class="bx-section">
<h1 style="text-align: center;"><strong>Media Coverage of Past Trips</strong></h1>
<table>
<tbody>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>2025 Trip</strong></h3>
</td>
</tr>
<tr>
<td lang="zh-Hans"><em><a href="https://mp.weixin.qq.com/s/AKBDaVdr8Lbtziwj4o_7GA?scene=1">2025年7月24日</a></em></td>
<td lang="zh-Hans">
<p>Article posted on the Department of North American and Oceanian Affairs of the Ministry of Foreign Affairs of China's microblog "Broad Pacific", all in Chinese: <a href="https://mp.weixin.qq.com/s/AKBDaVdr8Lbtziwj4o_7GA?scene=1" target="_blank" rel="noopener noreferrer">携手青春之约 共筑友谊之桥</a></p>
<p>外交部北美大洋洲司微信公众号“宽广太平洋”发布：7月17日至22日，巴尔的摩和加州橙郡的27名青年、教师和友好人士与17名中国青年参加“友行中国・趣淘厦门”鹭岛行。文章介绍筼筜湖、山海健康步道和五缘湾湿地的生态探寻与“友谊树”种植，厦门大学潘维廉教授的分享，厦门外国语学校和厦门市音乐学校的课堂，以及中山路、鼓浪屿和非遗体验；巴尔的摩-厦门姐妹城市委员会副主席Chelsea Nakabayashi谈青年们对“青年大使”使命的认真投入。</p>
</td>
</tr>
<tr>
<td lang="zh-Hans"><em><a href="https://share1.kxm.xmtv.cn/xmtv/2025-07-23/f2e4aa553d88cedb.html">2025年7月23日</a></em></td>
<td lang="zh-Hans">
<p>Video and article from our group's time in Xiamen from <i>Xiamen TV</i>, all in Chinese: <a href="https://share1.kxm.xmtv.cn/xmtv/2025-07-23/f2e4aa553d88cedb.html">2025美国“青年大使”鹭岛行活动举行</a></p>
<p>《午间新闻广场》报道：适逢厦门与巴尔的摩缔结友城40周年，巴尔的摩和加州橙郡的27名青年学员、教师和友好人士与17名中国青年同住、同学、同行。中美青年在厦门外国语学校把《红楼梦》改编成舞台剧、学做漆扇，并到中山路、五缘湾和美图公司参观交流；巴尔的摩-厦门姐妹城市委员会副主席何娇霞谈学习中国艺术与文化的意义。</p>
</td>
</tr>
<tr>
<td><em><a href="https://www.instagram.com/p/DMS8sA7RRwP/?">July 19, 2025</a></em></td>
<td>
<p>Shout-out by Baltimore's Mayor's Office of Immigrant Affairs, <a href="https://www.instagram.com/p/DMS8sA7RRwP/?">via Instagram</a></p>
<p>The Mayor’s Office of Immigrant Affairs (MIMA) on the send-off of Baltimore high schoolers to Xiamen as Youth Ambassadors, joined by representatives of the Enoch Pratt Free Library; thanks to the BXSCC for inviting MIMA to the kick-off event, and a pointer to the photo exhibit on the forty-year relationship on the second floor of the Pratt’s Central branch.</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>2024 Trip</strong></h3>
</td>
</tr>
<tr>
<td><em><a href="https://xhnewsapi.xinhuaxmt.com/share/news_pc?id=983086756200448">August 9, 2024</a></em></td>
<td>
<p>Blurb and video about our group’s time in Anhui from <i>Xinhua News</i>, in English: <a href="https://xhnewsapi.xinhuaxmt.com/share/news_pc?id=983086756200448" target="_blank" rel="noopener noreferrer">American students explore Chinese culture in Anhui Province</a></p>
<p>Xinhua GLOBALink video: the group of Maryland students in Anhui Province on a China-U.S. youth exchange program.</p>
</td>
</tr>
<tr>
<td lang="zh-Hans"><em><a href="https://m.chinanews.com/wap/detail/chs/zw/10261363.shtml">2024年8月1日</a></em></td>
<td lang="zh-Hans">
<p>Article about our group’s time in Anhui from <i>China News</i>, in Chinese: <a href="https://m.chinanews.com/wap/detail/chs/zw/10261363.shtml" target="_blank" rel="noopener noreferrer">感登黄山、访宏村 美国师生领略世界遗产之美</a></p>
<p>中新社图片报道（记者韩苏原）：7月31日，马里兰州高中生研学团登黄山、访宏村，在迎客松观景台前合影；安徽省与马里兰州于1980年结为友好省州。</p>
</td>
</tr>
<tr>
<td lang="zh-Hans"><em><a href="https://www.youtube.com/watch?v=AOf_EV53JN8&amp;t=11s">2024年8月1日</a></em></td>
<td lang="zh-Hans">
<p>Video from our group’s time in Xiamen from <i>Xiamen Star</i>, with English audio and Chinese captions: <a href="https://www.youtube.com/watch?v=AOf_EV53JN8&amp;t=11s" target="_blank" rel="noopener noreferrer">2024美國「青年大使」鷺島行圓滿舉行</a></p>
<p>“感知中国·趣淘厦门”2024美国“青年大使”鹭岛行于7月23日至30日在厦门举行，来自马里兰州巴尔的摩市和加利福尼亚州橙郡的25名学生、教师和政府代表展开为期8天的文化与友谊之旅。</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>2019 Trip</strong></h3>
</td>
</tr>
<tr>
<td><em><a href="https://govt.chinadaily.com.cn/s/201907/19/WS5d31328a498ed6fd11ca1faa/baltimore-students-wowed-by-xiamen.html">July 19, 2019</a></em></td>
<td>
<p>Article in <i>China Daily Global Edition</i>: <a href="https://govt.chinadaily.com.cn/s/201907/19/WS5d31328a498ed6fd11ca1faa/baltimore-students-wowed-by-xiamen.html" target="_blank" rel="noopener noreferrer">“Baltimore students wowed by Xiamen”</a></p>
<p>Sixteen Baltimore high school students and three chaperones in Xiamen and Beijing, June 24 to July 4: Xiamen Shuangshi High School, Xiamen University and Peking University. Madison, Jahnae and Camille on welcoming Chinese peers, a friendship kept up on WeChat, and Xiamen University's marine programs; Danny Evans on the program's goals of mutual understanding and leadership.</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>2018 Trip</strong></h3>
</td>
</tr>
<tr>
<td><em><a href="http://www.xmenglish.cn/Feature/201808/t20180831_5213233.htm">August 31, 2018</a></em></td>
<td>
<p>Article in Xiamen Network (English edition): <a href="http://www.xmenglish.cn/Feature/201808/t20180831_5213233.htm">“Xiamen Shining in the Eyes of Two Baltimore Area Brothers”</a></p>
<p>Aaron and Alexander's own account of their days in Xiamen: the 2017 BRICS Summit venue, a performance at the Youth and Children's Palace, dinner and gifts from the city's Foreign and Overseas Chinese Affairs Office, downtown shops, Gulangyu Island, and a virtual reality tour of the city. In their words, the trip "exceeded all expectations."</p>
</td>
</tr>
<tr>
<td><em><a href="https://www.meipian.cn/1i7uuo9k?share_depth=2&amp;user_id=0&amp;share_user_mpuuid=fa12a2e208a81aa35642840e34304cf7">August 7, 2018</a></em></td>
<td>
<p>Recap by the Jiading International Youth Friendship Camp in Shanghai: <a href="https://www.meipian.cn/1i7uuo9k?share_depth=2&amp;user_id=0&amp;share_user_mpuuid=fa12a2e208a81aa35642840e34304cf7">“Long Live With Our Friendship”</a></p>
<p>The camp's own bilingual recap, posted on Meipian, a Chinese photo-story app: the closing ceremony of the nine-day camp on August 5, 2018, with speeches by Jiading's education and foreign affairs officials; photos of classical gardens, pandas at the zoo, a school day and a family day with Jiading households; awards and national dances by campers; and tearful goodbyes at departure.</p>
</td>
</tr>
<tr>
<td><em><a href="https://www.afro.com/baltimore-teacher-takes-students-to-china-to-educate-and-be-educated/">August 6, 2018</a></em></td>
<td>
<p>Article in <i>The Afro</i>: <a href="https://www.afro.com/baltimore-teacher-takes-students-to-china-to-educate-and-be-educated/">“Baltimore Man Takes Students to China to Educate and Be Educated”</a></p>
<p>Trip leader Danny Evans, who taught in China on a teacher exchange from 2012 to 2014, on preparing the students for two weeks in China, and on the trip as a chance for them to teach others what it means to be Black, American, and from Baltimore.</p>
</td>
</tr>
<tr>
<td><em><a href="http://www.xmenglish.cn/DynamicInformation/201807/t20180731_5204695.htm">July 31, 2018</a></em></td>
<td>
<p>Article in Xiamen Network (English edition): <a href="http://www.xmenglish.cn/DynamicInformation/201807/t20180731_5204695.htm">“Baltimore Teenagers visit Xiamen in Sister-Cities Exchange Trip”</a></p>
<p>Report on the three-day exchange, July 26 to 28: the four ambassadors and five of the site's young reporters toured Xiamen University, Zhongshan Road, Gulangyu Island and the 2017 BRICS Summit venue, and took lessons in Chinese painting, calligraphy, drumming and paper cutting. Danny Evans called the trip "a great way to bring the two cities closer."</p>
</td>
</tr>
<tr>
<td lang="zh-Hans"><em><a href="http://www.xmnn.cn/xmnn/2018/07/26/100400617.shtml">2018年7月26日</a></em></td>
<td lang="zh-Hans">
<p>Article in 厦门网: <a href="http://www.xmnn.cn/xmnn/2018/07/26/100400617.shtml">“巴尔的摩学生代表团访厦 携手英文小记者体验厦门文化”</a></p>
<p>7月26日，巴尔的摩学生代表团参观金砖国家领导人会晤现场，与4名厦门网英文小记者一起开启厦巴友城交流活动；当天下午在厦门市青少年宫第一次拿起毛笔画兰花，并观看传统乐器和芭蕾舞表演。</p>
</td>
</tr>
</tbody>
</table>
</div>
<img loading="lazy" class="alignnone wp-image-858 size-full" src="https://bxscc.org/wp-content/uploads/2024/03/2019-6-27-Image_20190630203727-compressed.jpg" alt="" width="1008" height="672" />
<div class="bx-section">
<h1 style="text-align: center;"><strong>Reflections from Past Program Participants</strong></h1>
<div class="bx-reflections">
<div class="bx-reflection">
<div class="bx-reflection-who"><strong>2025 Youth Ambassador Sydney B.</strong></div>
<blockquote class="bx-quote">
<p>“My second time in Anhui not only as a youth ambassador but as a representative of the State of Maryland has been an amazing experience. I really enjoyed seeing the beautiful city of Huangshan again and not only being able to show my family and friends pictures but to have my father with me this time to experience these things like hiking a mountain and cooking traditional Chinese foods. Being able to come to Anqing for the first time and see the city where Anhui gets its name has been wonderful. Also interacting with Chinese students my age and learning the history of the Flying Tigers has definitely been the highlight of this trip for me. This is an experience that I will carry with me forever. I am honored to be here to celebrate the relationship between Maryland and Anhui and I can wait to see more of Anhui Province.”</p>
</blockquote>
</div>
<div class="bx-reflection">
<div class="bx-reflection-who"><strong>2025 Youth Ambassador Jewel C.</strong></div>
<blockquote class="bx-quote">
<p>“大家好！Dajia hao！The past few days of serving as a youth ambassador in China have been truly unforgettable. From the moment I arrived, I was struck by the warmth of the people and the deep sense of history and culture that surrounds everything, from the streets to the food to the architecture. One of the highlights of my trip was visiting Huangshan, the Yellow Mountain. Standing above the clouds and seeing those iconic peaks felt like stepping into a traditional Chinese painting. This journey has deepened my appreciation for China and inspired me even more to continue learning the Chinese language. I believe that language is a bridge, and I’m excited to keep building connections between cultures. Thank you, Anhui, for your beauty, your hospitality, and the memories I’ll carry with me forever. 谢谢！Xiexie!”</p>
</blockquote>
</div>
<div class="bx-reflection">
<div class="bx-reflection-who"><strong>2025 Youth Ambassador Delores C.</strong></div>
<blockquote class="bx-quote">
<p>“This has been my first time in China and I’m beyond grateful that I have gotten to experience the beautiful province of Anhui. This beautiful city of Anqing alongside its welcoming community has surely left a mark on my heart. My experience here has given me more than just the experience of a trip abroad but has opened me up to the beautiful friendships that come with it. The ability to connect to others despite a language barrier, different upbringings, and cultural differences has meant so much to me. It was so beautiful and touching the way that Anhui has continued to memorialize the Flying Tigers. I hope that we may continue to cherish our relationship not only for another 40 years, but longer into the future. I will hold the people, history learned, memories that my peers and I have created together.”</p>
</blockquote>
</div>
<div class="bx-reflection">
<div class="bx-reflection-who"><strong>2025 Youth Ambassador Nex C.</strong></div>
<blockquote class="bx-quote">
<p>“During my time in Anhui, I have been introduced to many wonderful people. Everyone here has been so kind and welcoming. It’s so amazing that I even considered moving here in the future. My favorite part of this trip to Anhui was climbing the Yellow Mountain, even though the walk was rough. We made it to our destination, and the views were to die for. Sadly, I didn’t get to see any monkeys, but I did see the wonderful smiles of all the locals, and it just brought so much happiness to my heart. When I get back to Maryland, I will make sure to share my findings with my peers and encourage them to seek out the same experiences that have enlightened me. And I want to thank everyone that made it possible for this journey.”"</p>
</blockquote>
</div>
<div class="bx-reflection">
<div class="bx-reflection-who"><strong>2025 Youth Ambassador Nyiohbe R.</strong></div>
<blockquote class="bx-quote">
<p>“Building Bridges, Opening Doors”</p>
<p>Good evening to everyone here with me in China and good morning to everyone who has called in from America. Today, I stand before you to speak not just as someone with a deep desire to serve as a Foreign Service Officer and diplomat in the future, but as someone whose life has been shaped by the powerful ties of trade, family, and cultural exchange. My family has long been involved in commodity exchange; particularly rare earth minerals sourced from the heart of Africa. And through this, I’ve witnessed firsthand how trade is more than economics — it’s a bridge. A bridge between people, between countries, between futures. It’s that very bridge that brings the world to China, and China to the world. And today, more than ever, the world is relying on us — the next generation to keep these bridges strong.</p>
<p>In these challenging times, we need programs that inspire young minds, programs that encourage global understanding, and that give students a chance to see the world. Not just through a screen, but through their own eyes. This is why I wholeheartedly support this educational and diplomatic exchange program. I also believe it would be a powerful investment if we could take a step forward and include programs like this for the State of Maryland to include it in its budget — not as an expense, but as a promise to our future and our forever growing roots with China.</p>
<p>When we walked the streets of Fujian, we saw the legacy of multiple businesses that have thrived for seven generations. We saw the artistry of seven generations of puppet performers at the Fujian puppet museum who were actively preserving Chinese values, heritage, and resilience. It was a stark reminder that culture is continuity as well as strength.</p>
<p>As we explored Anhui, we got the chance to climb and explore the Yellow Mountain and discovered the beauty of the black mountain with its white clouds surrounding it. While on the mountain, I even got to witness first-hand how things such a tools and food were transported up the mountain via cable car and then carried the rest of the way on the shoulders of hardworking people.</p>
<p>What I’ve learned from this is that we must celebrate Chinese culture and the role it plays in shaping global connections. We must create opportunities to link cities across China with Maryland and Baltimore — opening doors, deepening friendships, and inspiring young minds to explore beyond their borders. The bridges we build between China and the United States, particularly at the people-to-people level, are essential to our shared future.</p>
<p>Let me be clear, the world is watching and is relying on us — the next generation of leaders, diplomats, and educators — to keep the doors of understanding open.</p>
<p>Let us open those doors — between Baltimore and Xiamen, between Maryland and Anhui — and let us ensure that our students have the chance to walk through them. Because kids deserve that chance. Doors deserve to be opened. And diplomacy, in its truest form, begins not in boardrooms, but in classrooms — in communities, and in the stories we share with one another. Let us move forward — together — in support of a program that not only builds bridges but becomes one. Thank you.”</p>
</blockquote>
</div>
<div class="bx-reflection">
<div class="bx-reflection-who"><strong>2025 Youth Ambassador Kimora S.</strong></div>
<blockquote class="bx-quote">
<p>“Visiting Anhui and its cities of Huangshan and Anqing has been such an amazing experience. I have really enjoyed learning about the culture, hearing the language, and meeting the people. Everyone has been friendly, and the place feels so different in a good way from Baltimore. In Huangshan, I couldn’t believe the mountains were so high and truly beautiful. Climbing them was hard, but I’m glad I did it. It felt good to reach such a high elevation and take in the amazing views. The trees, the clouds, everything just looked stunning. I also liked walking around, trying new food, and just seeing how people live at Hongcun Village and Anqing. My experience here in Maryland’s sister state, Anhui, has been fun, interesting, and something I’ll always remember. This is truly a special place, and I’m so happy I have had the opportunity to be here with you to celebrate this special anniversary.”</p>
</blockquote>
</div>
</div>
</div>
<img loading="lazy" class="size-full wp-image-748 aligncenter" src="https://bxscc.org/wp-content/uploads/2018/09/Trip-6.png" alt="" width="741" height="346" />
<div class="bx-section">
<h1 style="text-align: center;"><strong>Thanks</strong></h1>
<table>
<tbody>
<tr>
<td colspan="2">
<p>The Youth Ambassadors Program staff, participants, &amp; parents thank the following entities for their generous financial support of the 2025 program: Maryland Humanities' Marilyn Hatza Memorial SHINE Grant and Bard College.</p>
<p>We also thank the Office of Baltimore City Mayor Brandon Scott, the Office of the City Council President of Baltimore, the <a href="https://www.prattlibrary.org/">Enoch Pratt Free Library System,</a> <a href="https://baltimore.bard.edu/">Bard High School Early College - Baltimore</a>, the Office of the Secretary of State of Maryland, the Maryland-Anhui Province Sister State Committee, Maryland Center China, the Xiamen Municipal Government's Foreign Affairs Office, <a href="https://www.xiamenair.com/en-us/">Xiamen Airlines</a>, the Anhui Province Foreign Affairs Office, the Anhui Province Department of Education, the <a href="https://sais.jhu.edu/hopkins-nanjing-center">Hopkins-Nanjing Center</a>, the Foreign Affairs Office of the Shanghai Municipal People's Government, <a href="https://smly.hpe.cn/">Shanghai Business and Tourism School</a>, the Embassy of the People's Republic of China in the United States, and many other individuals and organizations for their support including in-kind donations.</p>
<p>In honor of our late treasurer and Youth Ambassadors program co-founder Arella Yan Liu, the BXSCC is providing special need-based funding for select participants under the “Arella Yan Liu Scholarship Program”.</p>
</td>
</tr>
</tbody>
</table>
</div>
<div class="bx-section">
<h1 style="text-align: center;"><strong>Profiles of Past Programs</strong></h1>
[bx_ya_years]
</div>
HTML
);
$c = $page->post_content;
if ($c === $new) { WP_CLI::success('Nothing to do: the page already has the new layout.'); return; }
$was = md5(str_replace($site, 'https://bxscc.org', $c));
$known = array('0c3c0abc44a9dd5e350c194d89c48daa' => 'the original', 'b35a9fb4860eb5e693f30322ad18ca3d' => 'the version approved at 17:23', '064d8bb5cefe45d481f5ac782871aed4' => 'the 17:41 version (button centered)');
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
