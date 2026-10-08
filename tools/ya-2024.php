<?php
/*
 * 2024 Youth Ambassadors page (/news-and-events/2024-bxscc-youth-ambassadors-program/): new layout, October 2026,
 * the same treatment as the 2018 and 2019 pages.
 * - Trip Overview: a section heading like the home page's, the dates (first line) and text in one table cell.
 * - In the Media (was "Media Coverage of the 2024 Program"): one table by source, newest first within a source; each
 *   item dated (date linked too) and summarized under its own line, the Chinese ones in Chinese; link titles and
 *   lines unchanged. The Xinhua Chinese article (404, no archived copy) removed.
 * - Key Staff for the 2024 Program: a section heading with a table, one row per person. Tense: roles still held today
 *   (Leadership page, Oct 2026) stay present (Jeannette Felton; John Facelo per Sam); others past.
 * - Thanks and Appreciation (was Appreciation): a section heading with the text in a table.
 * - The "- - -" lines removed.
 * Needs plugin 1.75 or later.
 * From public_html:
 *   wp eval-file ~/ya-2024.php dry --use-include --skip-themes --skip-plugins   (checks the page; changes nothing)
 *   wp eval-file ~/ya-2024.php run --use-include --skip-themes --skip-plugins   (changes it)
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

$page = get_page_by_path('news-and-events/2024-bxscc-youth-ambassadors-program');
if (!$page) { WP_CLI::error('No page at /news-and-events/2024-bxscc-youth-ambassadors-program/.'); }
$site = untrailingslashit(home_url());
$new = str_replace('https://bxscc.org', $site, <<<'HTML'
<img class="alignnone wp-image-858 size-full" src="https://bxscc.org/wp-content/uploads/2024/08/2024-7-25-Xiamen-FAO-DSC_2192-compressed-for-web.jpg" alt="" width="1008" height="475" />
<div class="bx-section">
<h1 style="text-align: center;"><strong>Trip Overview</strong></h1>
<table>
<tbody>
<tr>
<td colspan="2">
<p>July 21st to August 5th, 2024.</p>
<p>Seven students and four adults from the Baltimore region traveled to our sister city of Xiamen, Maryland's sister province of Anhui, and China's capital Beijing for over two weeks.</p>
<p>View the <a href="https://bxscc.org/wp-content/uploads/2024/08/BXSCC-Youth-Ambassadors-2024-Itinerary-Abridged.pdf">detailed itinerary</a> from 2024.</p>
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
<h3 style="text-align: center;"><strong>Xiamen People's Association for Friendship with Foreign Countries</strong></h3>
</td>
</tr>
<tr>
<td><em><a href="https://youtu.be/G4zH8gDE4pM" target="_blank" rel="nofollow noopener" shape="rect">August 6, 2024</a></em></td>
<td>
<p>Video from our group's time in Xiamen courtesy of the Xiamen People's Association for Friendship with Foreign Countries, with English audio and bilingual captions: <a href="https://youtu.be/G4zH8gDE4pM" target="_blank" rel="nofollow noopener" shape="rect">Youth Ambassadors in Xiamen 2024 "Moments"</a></p>
<p>A look back at the group's days in Xiamen, posted on the Baltimore Sister Cities YouTube channel.</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>Xinhua News</strong></h3>
</td>
</tr>
<tr>
<td><em><a href="https://xhnewsapi.xinhuaxmt.com/share/news_pc?id=983086756200448" target="_blank" rel="nofollow noopener" shape="rect">August 9, 2024</a></em></td>
<td>
<p>Blurb and video about our group's time in Anhui from <em>Xinhua News</em>, in English: <a href="https://xhnewsapi.xinhuaxmt.com/share/news_pc?id=983086756200448" target="_blank" rel="nofollow noopener" shape="rect">American students explore Chinese culture in Anhui Province</a></p>
<p>Xinhua GLOBALink video: the group of Maryland students in Anhui Province on a China-U.S. youth exchange program.</p>
</td>
</tr>
<tr>
<td><em><a href="https://xhnewsapi.xinhuaxmt.com/share/news_pc?id=978842647867392" target="_blank" rel="noopener noreferrer">July 28, 2024</a></em></td>
<td>
<p>Blurb and video about our group's time in Xiamen from <em>Xinhua News</em>, in English: <a href="https://xhnewsapi.xinhuaxmt.com/share/news_pc?id=978842647867392" target="_blank" rel="noopener noreferrer">“American students charmed by Chinese culture in Xiamen, China”</a></p>
<p>Xinhua GLOBALink video: Chinese tea culture, puppet shows and paper-cutting for the American students and teachers in a Sino-American youth cultural exchange program in Xiamen.</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>Xiamen Star</strong></h3>
</td>
</tr>
<tr>
<td lang="zh-Hans"><em><a href="https://www.youtube.com/watch?v=AOf_EV53JN8&amp;t=11s" target="_blank" rel="nofollow noopener" shape="rect">2024年8月1日</a></em></td>
<td lang="zh-Hans">
<p>Video from our group's time in Xiamen from <em>Xiamen Star</em>, with English audio and Chinese captions: <a href="https://www.youtube.com/watch?v=AOf_EV53JN8&amp;t=11s" target="_blank" rel="nofollow noopener" shape="rect">2024美國「青年大使」鷺島行圓滿舉行</a></p>
<p>“感知中国·趣淘厦门”2024美国“青年大使”鹭岛行于7月23日至30日在厦门举行，来自马里兰州巴尔的摩市和加利福尼亚州橙郡的25名学生、教师和政府代表展开为期8天的文化与友谊之旅。</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>People.cn</strong></h3>
</td>
</tr>
<tr>
<td lang="zh-Hans"><em><a href="http://usa.people.com.cn/n1/2024/0731/c241376-40289542.html" target="_blank" rel="noopener noreferrer">2024年7月31日</a></em></td>
<td lang="zh-Hans">
<p>Article about our group's time in Xiamen from <em>People.cn</em>, in Chinese: <a href="http://usa.people.com.cn/n1/2024/0731/c241376-40289542.html" target="_blank" rel="noopener noreferrer">2024美国“青年大使”鹭岛行：Citywalk鹭岛走透透</a></p>
<p>人民网美国频道转载中新网记者杨伏山的报道：厦门市人民对外友好协会主办的“感知中国·趣淘厦门”2024美国“青年大使”鹭岛行于7月23日至30日举行，巴尔的摩市和加州橙郡的25名青年学员、教师和政府代表与中国青年同住、同学、同行，走访下潭尾红树林湿地公园、沙坡尾、中山路老字号、集美新城和红点博物馆，体验剪纸、香道、福船制作和茶艺，在厦门外国语学校演绎李白茶话戏剧，并听厦门大学潘维廉教授分享。巴尔的摩姐妹城市协会主席约翰·法赛洛（John Facelo）和巴尔的摩市议会行政主任楚候塞（Hosea Chew）谈此行的意义。</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>Fujian Daily</strong></h3>
</td>
</tr>
<tr>
<td lang="zh-Hans"><em><a href="https://share.fjdaily.com/displayTemplate/news/newsDetail/1124/2696854.html" target="_blank" rel="nofollow noopener" shape="rect">2024年7月31日</a></em></td>
<td lang="zh-Hans">
<p>Article about our group's time in Xiamen from <em> Fujian Daily</em>, in Chinese: <a href="https://share.fjdaily.com/displayTemplate/news/newsDetail/1124/2696854.html" target="_blank" rel="nofollow noopener" shape="rect">美国青年来厦City Walk！2024美国“青年大使”鹭岛行在厦举办</a></p>
<p>《海峡导报》记者陈婧琳报道（新福建客户端）：巴尔的摩和橙郡25名青年学员、教师和政府代表的8天鹭岛行，走访下潭尾湿地、沙坡尾和中山路老字号，体验剪纸、香道、福船制作和布袋木偶，在鼓浪屿博饼；在厦门外国语学校演绎李白茶话戏剧、练习书法和掐丝珐琅，在厦门市音乐学校合奏《茉莉花》，漫步厦门大学。厦门市外办副主任曾金吉寄语中美青年成为两国友谊的使者。</p>
</td>
</tr>
<tr>
<td lang="zh-Hans"><em><a href="https://share.fjdaily.com/displayTemplate/news/newsDetail/1157/2695503.html" target="_blank" rel="nofollow noopener" shape="rect">2024年7月30日</a></em></td>
<td lang="zh-Hans">
<p>Article about our group's time in Xiamen from <em> Fujian Daily</em>, in Chinese: <a href="https://share.fjdaily.com/displayTemplate/news/newsDetail/1157/2695503.html" target="_blank" rel="nofollow noopener" shape="rect">2024美国“青年大使”鹭岛行活动举行</a></p>
<p>福建日报记者林梓健、林泽贵报道（附视频）：福建省人民对外友好协会指导、厦门市人民对外友好协会主办的鹭岛行于7月23日至30日举行。中美青年在厦门外国语学校用英语演绎李白茶话戏剧，在厦门市音乐学校合奏《茉莉花》，体验剪纸、香道、福船制作、茶艺、汉服和博饼。巴尔的摩姐妹城市协会主席约翰·法赛洛（John Facelo）谈文化体验如何开阔美国青年的视野。</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>Xiamen TV</strong></h3>
</td>
</tr>
<tr>
<td lang="zh-Hans"><em><a href="https://share1.kxm.xmtv.cn/xmtv/2024-07-31/35d30ab6280d2fd8.html" target="_blank" rel="nofollow noopener" shape="rect">2024年7月31日</a></em></td>
<td lang="zh-Hans">
<p>Article and video about our group's time in Xiamen from <em>Xiamen TV</em>, in Chinese: <a href="https://share1.kxm.xmtv.cn/xmtv/2024-07-31/35d30ab6280d2fd8.html" target="_blank" rel="nofollow noopener" shape="rect">感知中国·趣淘厦门：2024美国“青年大使”鹭岛行在厦举行</a></p>
<p>《厦视新闻》报道：巴尔的摩和加州橙郡的25名青年学员、教师和政府代表与中国青年同住、同学、同行。中美青年在厦门外国语学校用英语情景剧演绎诗人李白的故事，合读《赠汪伦》；8天里还走访鼓浪屿、下潭尾湿地公园、中山路和厦门大学，体验剪纸、香道、福船制作和茶艺，品尝烧肉粽、薄饼、炸枣等闽南美食。巴尔的摩姐妹城市协会主席约翰·法赛洛（John Facelo）和厦门市外办副主任曾金吉谈此行的意义。</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>Xiamen News</strong></h3>
</td>
</tr>
<tr>
<td lang="zh-Hans"><em><a href="https://www.douyin.com/user/MS4wLjABAAAASU0-3dFJzZTLkE3Bn_r3tdKzIsqHBtJUUN3x7c92Ktg?modal_id=7397192943125187879 " target="_blank" rel="nofollow noopener" shape="rect">2024年7月30日</a></em></td>
<td lang="zh-Hans">
<p>Video from our group's time in Xiamen from <em>Xiamen News</em>, in Chinese: <a href="https://www.douyin.com/user/MS4wLjABAAAASU0-3dFJzZTLkE3Bn_r3tdKzIsqHBtJUUN3x7c92Ktg?modal_id=7397192943125187879 " target="_blank" rel="nofollow noopener" shape="rect">“歪果仁” 学习李白的诗</a></p>
<p>抖音短视频：美国青年学习李白的诗。</p>
</td>
</tr>
<tr>
<td colspan="2">
<h3 style="text-align: center;"><strong>China News</strong></h3>
</td>
</tr>
<tr>
<td lang="zh-Hans"><em><a href="https://m.chinanews.com/wap/detail/chs/zw/10261722.shtml" target="_blank" rel="nofollow noopener" shape="rect">2024年8月1日</a></em></td>
<td lang="zh-Hans">
<p>Article about our group's time in Anhui from <em>China News</em>, in Chinese: <a href="https://m.chinanews.com/wap/detail/chs/zw/10261722.shtml" target="_blank" rel="nofollow noopener" shape="rect">美国青年“徽游记”：迎客松前续友谊</a></p>
<p>中新社合肥8月1日电（储玮玮、韩苏原）：7月30日至8月1日，7名来自马里兰州的美国高中生在黄山研学，在迎客松前合影（黄山风景区与约塞米蒂国家公园2006年结为中美首对友好公园），用筷子品尝徽菜，在黄山学院体验书法、武术、板凳龙和徽菜制作。带队教师何娇霞（Chelsea）谈美食与文化，巴尔的摩市议会行政主任楚候塞（Hosea Chew）期待次年马里兰州与安徽省结好45周年。</p>
</td>
</tr>
<tr>
<td lang="zh-Hans"><em><a href="//m.chinanews.com/wap/detail/chs/zw/10261363.shtml" target="_blank" rel="nofollow noopener" shape="rect">2024年8月1日</a></em></td>
<td lang="zh-Hans">
<p>Article about our group's time in Anhui from <em>China News</em>, in Chinese: <a href="//m.chinanews.com/wap/detail/chs/zw/10261363.shtml" target="_blank" rel="nofollow noopener" shape="rect">感登黄山、访宏村 美国师生领略世界遗产之美</a></p>
<p>中新社图片报道（记者韩苏原）：7月31日，马里兰州高中生研学团登黄山、访宏村，在迎客松观景台前合影；安徽省与马里兰州于1980年结为友好省州。</p>
</td>
</tr>
</tbody>
</table>
</div>
<div class="bx-section">
<h1 style="text-align: center;"><strong>Key Staff for the 2024 Program</strong></h1>
<table>
<tbody>
<tr>
<td colspan="2"><p><strong>Chelsea Nakabayashi</strong> was a faculty member at Bard High School Early College - Baltimore. She holds a BA from the University of Wisconsin - Madison, MAs from the Johns Hopkins University's School of Advanced International Studies (SAIS) and the University of Massachusetts - Amherst, and a PhD from Beijing Normal University.</p></td>
</tr>
<tr>
<td colspan="2"><p><strong>Jeannette Felton</strong> is the BXSCC's long-time Education Subcommittee Chair.</p></td>
</tr>
<tr>
<td colspan="2"><p><strong>Xin (Cindy) Wang</strong> was the BXSCC’s Chair and responsible for overseeing the organization's overall structural support for this program. Cindy is a past recipient of the Gates Millennium Scholarship for high-achieving students with significant financial need, studied abroad during college through the Council on International Educational Exchange (CIEE), and has served as a volunteer English-language lecturer in rural Jiangxi Province, China. At the time, she handled international sales operations for a Maryland machinery manufacturer.</p></td>
</tr>
<tr>
<td colspan="2"><p><strong>John Facelo</strong> is the President of Baltimore Sister Cities, Inc. and a long-time officer of Constellation Energy's Asian American Resource Group.</p></td>
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
<p>The Youth Ambassadors Program staff, participants, &amp; parents thank the following entities for their generous financial support of the 2024 program: The Baltimore Community Foundation‘s Van Buskirk International Exchange Fund and Bard College. Financial support was also provided by Jeannette Felton and Xin (Cindy) Wang.</p>
<p>We also thank the Office of Baltimore City Mayor Brandon Scott, the Office of the City Council President of Baltimore, the Maryland-Anhui Province Sister State Committee, the Office of the Secretary of State of Maryland, the Embassy of the United States in Beijing, Xiamen Airlines, Xiamen University, the Xiamen Municipal Government’s Foreign Affairs Office, Huangshan University, the Anhui Province Foreign Affairs Office, the Anhui Province Department of Education, the Embassy of the People's Republic of China in the United States, the Nakabayashi family, and many other individuals and organizations for their support including in-kind donations.</p>
<p>In honor of our late treasurer and Youth Ambassadors program co-founder Arella Yan Liu, the BXSCC provided special need-based funding for select participants under the "Arella Yan Liu Scholarship Program".</p>
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
if ($was !== 'ef64f8eb59796c45da0d5df012ec1525') {
    WP_CLI::error("The page is not the version this was written against (fingerprint $was), so nothing was done. Paste this output; the page's content is needed to redo the change.");
}
WP_CLI::log("Page {$page->ID}: found the expected version (" . strlen($c) . ' characters); the new one has ' . strlen($new) . '.');
if ($mode !== 'run') { WP_CLI::success('Dry run: nothing changed. Run again with "run" to make the change.'); return; }

$r = wp_update_post(wp_slash(array('ID' => $page->ID, 'post_content' => $new)), true);
if (is_wp_error($r)) { WP_CLI::error($r->get_error_message()); }
clean_post_cache($page->ID);
if (get_post($page->ID)->post_content !== $new) { WP_CLI::error('Saved, but the stored content differs from what was written; check the page revisions.'); }
WP_CLI::success("Saved (page {$page->ID}). Empty the page cache next.");
