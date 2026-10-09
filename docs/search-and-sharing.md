# Search and sharing

Plugin blocks 23 and 24 (version 1.79, October 2026), from the October 2026 SEO review.

## Search result text

A page's **Excerpt** is its meta description: the summary a search engine may show under the page's title in its results. Edit it in the page editor's sidebar (Page > Excerpt; for pages the box is switched on by the plugin). Keep it to one or two sentences, about 150 characters, saying what the page holds; no two pages should share one. A page with an empty Excerpt gets no meta description, and search engines write their own from the page text.

Search engines use the description when it fits a search better than the page's own text and may still write their own; it affects what people see, not where the page ranks.

`tools/seo-content.php` wrote the first descriptions for all 40 pages and posts. A yearly News and Events post created on January 1 has none until someone writes it.

## Link previews

A link to the site pasted into Facebook, LinkedIn, Discord, Slack, iMessage or X becomes a card built from Open Graph tags in the page's head:

| Tag | From |
|---|---|
| Title | The page title (the home page: the site name) |
| Text | The Excerpt; without one, a yearly post gets "Events, programs and news of the … in YYYY.", and any other page its first paragraph or table cell of at least 80 characters that is not a photo caption, cut at a word near 200 characters |
| Picture | The page's Featured image; else the first photo in the page at least 600 px wide (photos inside links to other sites, which are sponsor and partner logos, are passed over); else `img/share-card.jpg` |

`img/share-card.jpg` is 1200 × 630 pixels: the logo and the site name on navy. To give a page a different picture, set its Featured image.

Services keep a copy of a card for days. After changing a page's picture or text, Facebook's Sharing Debugger and LinkedIn's Post Inspector fetch it fresh. WeChat does not read Open Graph tags; its share card needs a WeChat Official Account.

## Kept out of search

Year and month archives (`/2016/`, `/2016/12/`), the category page and the author page are marked `noindex, follow`: search engines drop them from results but still follow their links. Each only repeats post titles that have pages of their own, and the theme heads every date archive with a single day ("Archive For December 18, 2008" on `/2008/`). Visitors who reach one still see it.

## Sitemap

`https://bxscc.org/wp-sitemap.xml` is WordPress's own sitemap. The plugin leaves out its users list (which showed the admin's username) and its categories list, so it lists the published pages and posts only (40 in October 2026). It updates itself when pages are added or removed; `robots.txt` points to it. Submit this address once in Google Search Console and Bing Webmaster Tools.

## Site name

The home page carries WebSite structured data naming the site "Baltimore-Xiamen Sister City Committee", with "BXSCC" as its short name, for the site name Google shows above each result. Google also reads the home page title and `og:site_name`, which say the same.

## Old addresses

Two addresses of pages removed in October 2026 were still in search results. When either would be "not found", it is sent on permanently (301):

| Old address | Goes to |
|---|---|
| `/updated-recently/` and its `/page/N/` pages | News and Events |
| `/image-gallery/` | History (its photos moved there) |

More can be added in block 24. Search Console's "Not found (404)" report lists old addresses that people or other sites still use.

## Chinese text

Pages with Chinese paragraphs are left marked as English. Google decides a page's language from its visible text and ignores `lang` attributes, so marking would not change Google's results; it would help screen readers and Bing a little.
