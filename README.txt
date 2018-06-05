=== WP Native Articles - Facebook Instant Articles Plugin for WordPress ===
Contributors: ozthegreat
Donate link: https://wp-native-articles.com
Tags: facebook, instant articles, facebook instant articles, mobile, speed, optimize, performance
Requires at least: 4.0
Tested up to: 4.9.6
Stable tag: 1.5.3
Requires PHP: 5.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-native-articles
Domain Path: /languages

Easy Facebook Instant Articles Plugin for your WordPress site with better content parsing and advanced features.

== Description ==

**Major Updates**
_- 1.5.0 - Content Transformers. Easily create rules to convert content that is displaying incorrectly._
_- 1.3.5 - Automatic integration with analytics plugins (Jetpack, Google Analytics, Chartbeat etc)._
_- 1.3.2 - Content Parser V2 Released._
_- 1.3.0 - Placement Manager, Crawler Ingestion & WP Recipe Maker Support._
_- 1.2.5 - Specify different content for your Instant Article._
_- 1.2.2 - WP Bakery Visual Composer Support._

A better implementation of Facebook Instant Articles for WordPress.

Only available in the Facebook mobile app, Instant Articles are a pre-loaded, super fast, and optimized version of your regular articles. Boasting some impressive stats (10x faster, 20% more reads, 70% less abandonment) they have quickly become a must for every publisher.

**How it works**

After installation and setup this plugin auto generates an instant article version for each of your WordPress articles. The article's regular HTML is parsed and converted to correct Facebook Instant Article markup which is then submitted to Facebook via an RSS feed. Anytime your article is then viewed using the Facebook mobile app it will load the pre-cached, super fast, optimized version. When the article is shared or posted its instant status will also be denoted by the lightning symbol in the corner.

[Read more about Instant Articles from the official documentation](https://developers.facebook.com/docs/instant-articles)

**Content Parsing**

[Version 2 Released](https://wp-native-articles.com/blog/introducing-v2-content-parser/) - Converts posts up to 8X faster, using fewer resources, and much more accurately.

A large part of the complexity of integrating Facebook Instant Articles is the rigorous content structure imposed. Due to the WYSIWYG editor and the plethora of plugins and themes available, WordPress content tends to be rather unstructured. Most of the current Facebook Instant Article plugins don't really take account of this and either produce invalid content or strip parts out. This plugin is a concentrated effort to correctly format any WordPress content it could possibly come across. You can read more about the challenges and solutions faced when parsing unstructured content on our [Instant Articles blog](https://wp-native-articles.com/blog/formatting-wordpress-html-content-instant-articles/?utm_source=fplugin&utm_medium=readme).

> **PRO Version**
>
> [WP Native Articles Pro](https://wp-native-articles.com/?utm_source=fplugin&utm_medium=readme-a) comes with full [Facebook Instant Articles API](https://wp-native-articles.com/?utm_source=fplugin&utm_medium=readme-b#features) integration and has more advanced features such as:
>
> * **Full API integration**
> * **[Mass Post Syncer](https://wp-native-articles.com/facebook-instant-articles/mass-post-syncer/?utm_source=fplugin&utm_medium=readme-syncer)** Mass convert ALL your WordPress posts to Instant Articles.
> * **[Placement Manager](https://wp-native-articles.com/facebook-instant-articles/placement-manager/?utm_source=fplugin&utm_medium=readme-placements)** Add custom code anywhere within your Instant Articles.
> * **[Crawler Ingestion](https://wp-native-articles.com/facebook-instant-articles/crawler-ingestion/?utm_source=fplugin&utm_medium=readme-crawler)** Auto set all your articles to become Instant Articles the moment they're shared on Facebook.
> * **Manage Articles** Publish, unpublish and manage Instant Articles directly from the WordPress post page.
> * **Live Sync** Instant Articles synced instantly from WordPress, no waiting for FaceBook to scrape the RSS feed.
> * **Individual Sync** Only make posts of your choosing Instant Articles
> * **Article Status** Errors & import status for your Instant Articles displayed live in every article.
> * **Instant Articles Analytics** Individual and aggregated site overview analytics, broken down by device and date.
> * **Premium support**
>
> Check out the Pro version at [https://wp-native-articles.com/](https://wp-native-articles.com/?utm_source=fplugin&utm_medium=readme-c).

**Future**

Features planned for the near future.

* Placement manager (ads, related articles, anything) - **Completed**
* Sync all your old articles - **Completed**
* Embed WordPress Comments
* Advanced stats & weekly roundup email
* CLI & API integration

== Installation ==

1. Upload `wp-native-articles` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to `Native Articles` in the admin sidebar to manage settings
4. When you're ready submit your feed to Facebook. [Full instructions](https://developers.facebook.com/docs/instant-articles/publishing/setup-rss-feed)

== Upgrade Notice ==

Intentionally left blank

== Frequently Asked Questions ==

= Where can I find the support documentation? =

All documentation is available at [docs.wp-native-articles.com](http://docs.wp-native-articles.com). It is a work in progress so please bear with us.

= Does it support Branded Content? =

Yes. Individual articles can be setup as branded. The author details are used and displayed on the Instant Article.

= Does it support Ads & Analytics? =

Yes. Both can be added on a global level or overridden for individual articles. It has built in support for Google Analytics, Google Tag Manager, Chartbeat, Pars.ly, SimpleReach & Jetpack.
It also integrates with the biggest WP Google Analytics Plugins and will automatically use them to add analytics to your Instant Articles.

= I use ACF or Meta Fields to display some content. Are these supported? =

They won't automatically be added to the Instant Articles unfortunately. However, they should be easy enough to add as
not only are there hooks galore in the plugin but the Instant Article templates can be overridden in your own theme or plugin.

= Does it play well with other plugins? =

Yes, extremely well. We've tested loads of popular plugins that embed content and built in support for Yoast, Co-Authors-Plus, Visual Bakery and Playbuzz to name a few.
If you find any that don't work or that we've missed please let us know and we'll be sure to fix them.

= Is there a road map? =

Yep. It can be found on our [instant articles roadmap page](https://wp-native-articles.com/roadmap/?utm_source=fplugin&utm_medium=readme-d).

= Does it support WordPress Multisite? =

Yes it does. There's a management panel in the Network Admin sidebar menu.
It can be used to copy settings from one blog to another and set a default blog
to inherit settings from when new blogs are created.

= Does it support custom post types? =

Yes. The RSS feeds defaults to posts but custom post types can be specified by pass in query parameters. More information can be [found here](http://docs.wp-native-articles.com/article/44-custom-post-types)

= Is it multilingual compatible? =

Yes. All text strings use the WordPress i18n translation functions. If you'd like to help translate the plugin we'd love to hear from you!

= Does it work for multi-language sites? =

Yes. If you're using the WPML plugin it will generate a combined feed of all languages by default or you can pass in query parameters to specify which languages you'd like.

= What's the minimum PHP version required? =

It has the same requirements as WordPress, so PHP >= 5.2.4.

= I've found a bug? I have a feature request? =

Excellent. We'd love to hear from you. All support for the free plugin is done
through the WordPress support forum, found on the Plugin download page.

== Screenshots ==

1. General options.
2. Analytics Integrations.
3. Ad settings.
4. General Styling Options.
5. Image Layout Options.
6. RSS Feed options.
7. Post override general options.
8. Post override styling options.
9. Post content options.
10. Content Transformers overview.
11. Add new Content Transformer.
12. Multisite options page.

== Changelog ==

= 1.5.3 =
* Feature. Superior handling of images in lists
* Fix. PHP 7.2 compatibility

= 1.5.2 =
* Fix. PHP 5.2 compatibility.
* Fix. WordPress <= 4.2 compatibility.
* Fix. Content not saving in the Placement Manager.

= 1.5.1 =
* Fix. Issue with some post drafts getting converted.

= 1.5.0 =
* Feature. Transformers. Create special rules to correctly transformer difficult content.
* Feature. Added [wpna_ad placement_id=""] and [wpna_related_articles title="" ids=""] shortcodes.
* Feature. Compatibility with the Jannah theme.
* Feature. Compatibility with the Zombify page builder.
* Feature. Compatibility with the Aesop page builder.
* Feature. Added wpna_should_convert_post_ia(). All methods now use this.
* Fix. PHP 7.2 error.
* Fix. WPBakery Visual Composer transformer error.
* Fix. Error transforming dd list types and links around images.

= 1.4.1 =
* Fix. Override options notice compatible with closures.
* Fix. Strip links from around images.

= 1.4.0 =
* Feature. Show warning for invalid video headers.
* Feature. Show warning for invalid ad codes.
* Feature. Compatibility with ThemeShop themes with LazyLoad.
* Feature. Compatibility with WP Rocket.
* Feature. Compatibility with NextGen Gallery.
* Feature. Better WP Recipe Maker nutrition label compatibility.
* Feature. Check data-src & data-lazy-src on images as well incase of lazyload.
* Feature. Manually set post sponsor if different to the author.
* Feature. Better admin notices.
* Fix. Strip CDATA tags from post content.
* Fix. Stop self closing iFrames.
* Fix. Bug with PlayBuzz check firing too early.

= 1.3.5 =
* Feature. Complete Analytics re-write.
* Feature. Compatibility with the GA Google Analytics plugin.
* Feature. Compatibility with the Google Analytics Dashboard for WP plugin.
* Feature. Compatibility with the Google Analytics plugin.
* Feature. Compatibility with the Google Analytics by MonsterInsights plugin.
* Feature. Compatibility with the Chartbeat plugin.
* Feature. Compatibility with the Jetpack plugin.
* Feature. Compatibility with the Parse.ly plugin.
* Feature. Compatibility with the SimpleReach plugin.
* Feature. Compatibility with the Easy Video Player plugin.
* Feature. Remove global wpna_options variable.
* Feature. Make wp_parse_url compatible with lower WP versions.
* Feature. Switch to global function to kick everything off.
* Feature. Better saving for post meta fields.
* Fix. Correctly parse images wrapped in headings.
* Fix. Correctly parse elements inside italics.
* Fix. Stop passworded posts appearing via the RSS feed.

= 1.3.4 =
* Feature. Set Content Parser V2 as default upon activation.
* Feature. Auto detected if excerpt is used and enable subtitle automatically.
* Feature. Stricter regex for checking for the featured image.
* Feature. Stricter featured image URL validation for Yoast.
* Feature. Compatibility with the Spider Facebook plugin.
* Feature. Custom params added to analytics.
* Fix. Headings containing ampasands now covert properly.
* Fix. Amin tabs no longer broken when the plugin is disabled.

= 1.3.3 =
* Feature. Default to Version 2 parser.
* Feature. V2 Parser. Remove empty elements.
* Feature. WordPress Galleries now support image captions.
* Feature. Override Ad Type for individual posts.
* Fix. Use GMT date for posts instead of local one.
* Fix. Broken link to the support forums.
* Fix. Stop MediaAce LazyLoad in IA.
* Fix. Stop NewRelic Browser Injection Script in Crawler Ingestion layout.
* Fix. Stop the TVE editor duplicating content.

= 1.3.2 =
* Feature. Content Parser V2 (beta).
* Fix. Bugs with WP Recipe Maker compatibility.

= 1.3.1 =
* Fix. Bugs with WP Recipe Maker compatibility.
* Fix. Stop & (ampersand) getting encoded in ads.

= 1.3.0 =
* Feature. Add rtl support.
* Feature. Add video header support.
* Feature. wpna_allowed_post_types() now controls what post types get converted.
* Feature. Toggle ads field + simpler audience network field.
* Feature. Toggle basic auth field.
* Feature. Support for EasyAzon plugin.
* Feature. Support for AdAce plugin.
* Feature. Support for WP Recipe Maker.
* Fix. Can now use UTF-8 urls in related articles.
* Fix. Add fallback for mb_convert_encoding().
* Fix. Related articles automatically mark as sponsored if they are.
* Fix. Stop & (ampersand) getting encoded in analytics.


= 1.2.5 =
* Feature. Post content override. You can now specify different content for IA.
* Feature. Add [wpna hide=''][/wpna] shortcode for when you want to hide content from IA.
* Feature. You can now manually specify related posts.
* Feature. Removes paragraphs that only contain &nbsp;.
* Feature. Prepare plugin for translation + fix spelling errors.
* Feature. Better parsing for theme.co Pro & X.
* Feature. Faster method for checking if images exist.
* Fix. Better compatibility with older versions of Newmag theme.

= 1.2.4 =
* Feature. Add initial support for WP Quads.
* Feature. Add initial support for theme.co Pro & X.
* Feature. Better rules when dealing with shortcodes in posts.
* Fix. 32bit support. Don't use absint for super large numbers.
* Fix. Allow some html tags in the excerpt.
* Fix. Occasional encoding error. Punctuation no longer appears as ã.
* Fix. Missing op-interactive tag in the Newsmag compatibility.
* Fix. Ignore <!--nextpage--> tags.

= 1.2.3 =
* Feature. Support for GitHub gist embeds added.
* Feature. Support for &lt;pre&gt; & &lt;code&gt; tags added.
* Feature. YouTube & Vimeo videos added through FV Player Plugin now supported.
* Feature. Support for Newsmag theme Video posts added.
* Fix. In-page links now removed.
* Fix. Remove images less than 1024 bytes.
* Fix. Bug affecting images wrapped in links.
* Fix. Bug where some elements wouldn't get removed.

= 1.2.2 =
* Feature. Support for Visual Bakery (beta).
* Feature. Optionally show subtitle, kicker, authors & media in the article header.
* Feature. Add Facebook ad density option.
* Feature. Add Facebook recirculation ad option.
* Fix. Embedding other WordPress posts from your site now works.
* Fix. Using embed code from Twitter & Instagram directly now works.

= 1.2.1 =
* Fix. Bug with [gallery] images.

= 1.2.0 =
* Feature. Now supports custom post types in the RSS feed.
* Feature. Article kicker doesn't display if the category is 'uncategorized'.
* Fix. Article kicker now displays the category name instead of the category slug.
* Fix. Bug with screen metaboxes.
* Fix. Ensures whole post is displayed when <!--MORE--> tag is present.
* Fix. [gallery] shortcodes should now show correctly.

= 1.1.6 =
* Feature. Can now use dynamic date variables in Copyright & Credit fields.
* Fix. Date validation for PHP <= 5.3 now works.

= 1.1.5 =
* Feature. Notify if a template is being overridden.
* Fix. Ads script was sometimes being escaped.

= 1.1.4 =
* Fix. Post settings sometimes override global defaults when they shouldn't.

= 1.1.3 =
* Feature. Show warning if options have been overridden using hooks.
* Fix. Error blocking the Screen Options tab on the post page.
* Fix. Error sanitizing post options.

= 1.1.2 =
* Feature. Image title & caption styling options. Global + post override.
* Feature. Now 100% WordPress & WordPress VIP standards compatible.
* Feature. Overridden templates can now be in a folder rather than top-level.
* Fix. Post tabs not aligning.
* WordPress 4.7.3 compatibility.

= 1.0.8 =
* Fix Infogram embeds.

= 1.0.7 =
* WordPress 4.7 compatibility.

= 1.0.6 =
* PHP 5.2 compatibility.

= 1.0.5 =
* Fix PHP <= 5.5 error.

= 1.0.4 =
* Fix error in image captions with non alphanumeric entities.

= 1.0.3 =
* WordPress 4.7 compatibility.
* Speed improvements for locating attachment IDs.

= 1.0.2 =
* Fix for images using HTML5 markup.
* Take account of captions when removing images.
* 'Premium' page.
* Readme corrections.

= 1.0.1 =
* Readme corrections.

= 1.0.0 =
* Plugin released.
