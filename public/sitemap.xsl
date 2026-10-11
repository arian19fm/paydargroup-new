<?xml version="1.0" encoding="UTF-8"?>
<!--
    Human-readable view of the sitemap documents (App\Support\Seo\Sitemap).
    Browsers apply it through the xml-stylesheet instruction; crawlers read
    the XML and ignore it.
-->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:s="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
    xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
    <xsl:output method="html" encoding="UTF-8" indent="yes"/>

    <xsl:template match="/">
        <html lang="fa" dir="rtl">
            <head>
                <meta charset="utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1"/>
                <meta name="robots" content="noindex"/>
                <title>نقشهٔ سایت</title>
                <style>
                    :root { color-scheme: light dark; --bg: #fff; --text: #141a21; --muted: #737a82; --line: #e0e3e3; --head: #f4f6f8; --link: #2a4676; }
                    @media (prefers-color-scheme: dark) { :root { --bg: #0f1319; --text: #f4f6f8; --muted: #8f9499; --line: #2a323d; --head: #1a2029; --link: #1c94e1; } }
                    * { box-sizing: border-box; }
                    body { margin: 0; padding: 32px 16px; background: var(--bg); color: var(--text); font: 14px/1.7 Tahoma, system-ui, sans-serif; }
                    main { max-width: 1100px; margin: 0 auto; }
                    h1 { margin: 0 0 4px; font-size: 22px; }
                    p { margin: 0 0 20px; color: var(--muted); }
                    .wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: 10px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { padding: 10px 14px; border-bottom: 1px solid var(--line); text-align: right; vertical-align: top; }
                    th { background: var(--head); font-weight: 700; white-space: nowrap; }
                    tr:last-child td { border-bottom: 0; }
                    td.url { direction: ltr; text-align: left; word-break: break-all; }
                    td.num, td.date { direction: ltr; white-space: nowrap; color: var(--muted); }
                    .num, .date { text-align: left; }
                    a { color: var(--link); text-decoration: none; }
                    a:hover { text-decoration: underline; }
                </style>
            </head>
            <body>
                <main>
                    <xsl:choose>
                        <xsl:when test="s:sitemapindex">
                            <h1>نقشهٔ سایت</h1>
                            <p><xsl:value-of select="count(s:sitemapindex/s:sitemap)"/> فایل نقشهٔ سایت</p>
                            <div class="wrap"><table>
                                <tr><th>فایل</th><th class="date">آخرین تغییر</th></tr>
                                <xsl:for-each select="s:sitemapindex/s:sitemap">
                                    <tr>
                                        <td class="url"><a href="{s:loc}"><xsl:value-of select="s:loc"/></a></td>
                                        <td class="date"><xsl:value-of select="concat(substring(s:lastmod, 1, 10), ' ', substring(s:lastmod, 12, 5))"/></td>
                                    </tr>
                                </xsl:for-each>
                            </table></div>
                        </xsl:when>
                        <xsl:when test="s:urlset/s:url/news:news">
                            <h1>نقشهٔ اخبار</h1>
                            <p><xsl:value-of select="count(s:urlset/s:url)"/> خبر در ۴۸ ساعت گذشته — <a href="/sitemap.xml">بازگشت به نقشهٔ سایت</a></p>
                            <div class="wrap"><table>
                                <tr><th>عنوان</th><th>نشانی</th><th class="date">تاریخ انتشار</th></tr>
                                <xsl:for-each select="s:urlset/s:url">
                                    <tr>
                                        <td><xsl:value-of select="news:news/news:title"/></td>
                                        <td class="url"><a href="{s:loc}"><xsl:value-of select="s:loc"/></a></td>
                                        <td class="date"><xsl:value-of select="concat(substring(news:news/news:publication_date, 1, 10), ' ', substring(news:news/news:publication_date, 12, 5))"/></td>
                                    </tr>
                                </xsl:for-each>
                            </table></div>
                        </xsl:when>
                        <xsl:otherwise>
                            <h1>نقشهٔ سایت</h1>
                            <p><xsl:value-of select="count(s:urlset/s:url)"/> نشانی — <a href="/sitemap.xml">بازگشت به نقشهٔ سایت</a></p>
                            <div class="wrap"><table>
                                <tr><th>نشانی</th><th class="num">تصویر</th><th class="date">آخرین تغییر</th></tr>
                                <xsl:for-each select="s:urlset/s:url">
                                    <tr>
                                        <td class="url"><a href="{s:loc}"><xsl:value-of select="s:loc"/></a></td>
                                        <td class="num"><xsl:value-of select="count(image:image)"/></td>
                                        <td class="date"><xsl:value-of select="concat(substring(s:lastmod, 1, 10), ' ', substring(s:lastmod, 12, 5))"/></td>
                                    </tr>
                                </xsl:for-each>
                            </table></div>
                        </xsl:otherwise>
                    </xsl:choose>
                </main>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
