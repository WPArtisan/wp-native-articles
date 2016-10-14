=== WP Native Articles ===
Contributors: OzTheGreat
Donate link: https://wp-native-articles.com
Tags: facebook, instant articles, mobile, speed, optimize, performance
Requires at least: 3.0.1
Tested up to: 4.6.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Advanced Facebook Instant Articles integration for WordPress

== Description ==

A better implementation of Facebook Instant Articles (FBIA) for WordPress.

Only available in the Facebook mobile app, Instant Articles are a pre-loaded, super fast, and optimized version of your regular articles.
Boasting some impressive stats (10x faster, 20% more reads, 70% less abandonment) they have quickly become a must for every publisher.

**How it works**

After installation and setup this plugin auto generates an instant article version for each of your WordPress articles.
The article's regular HTML is parsed and converted to correct FBIA markup which is then submitted to Facebook via a RSS feed. When your
article is viewed using the Facebook mobile app it will now load the fast, optimized instant article version directly in the app.
When the article is shared or posted its instant status will also be denoted by the lightening symbol in the corner.

[Read more about Instant Articles from the official documentation](https://developers.facebook.com/docs/instant-articles)

**Content Parsing**

A large part of the complexity of integrating Instant Articles is the rigorous content structure
imposed. Due to the WYSIWYG editor and the plethora of plugins and themes available, WordPress content
tends to be rather unstructured. Most of the current Facebook Instant Article plugins don't really take
account of this and either produce invalid content or strip parts out. This plugin is a concentrated effort
to correctly format any WordPress content it could possibly come across. You can read more about the challenges and
solutions in [this blog post](https://wp-native-articles.com/blog/formatting-wordpress-html-content-instant-articles/).

**PRO Version**

The [Pro version](https://wp-native-articles.com/) of the plugin comes with full Facebook Instant Articles API integration and has
advanced features such as:

* Full API integration.
* Publish, unpublish and manage Instant Articles directly from the WP post page.
* FBIA synced instantly with WP, no waiting for FB to scrape the RSS feed.
* FBIA Errors & import status display live in every article.
* FBIA analytics, individual and aggregated site overview.
* Premium support.

**Future**

A features planned for the near future.

* Image options manager
* Embed manager (ads, related articles, anything)
* WP Comments
* Advanced stats & weekly roundup
* CLI & API integration

== Installation ==

1. Upload `wp-native-articles` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to `Native Articles` in the admin sidebar to manage settings
4. When you're ready submit your feed to Facebook. [Full instructions](https://developers.facebook.com/docs/instant-articles/publishing/setup-rss-feed)

== Upgrade Notice ==

Intentionally left blank

== Frequently Asked Questions ==

= Does it support Branded Content? =

Yes. Individual articles can be setup as branded. The author details are used and displayed on the Instant Article.

= Does it support Ads & Analytics? =

Yes. Both can be added on a global level or overridden for individual articles.

= I use ACF or Meta Fields to display some content. Are these supported? =

They won't automatically be added to the Instant Articles unfortunately. However, they should be easy enough to add as
not only are there hooks galore in the plugin but the Instant Article templates can be overridden in your own theme or plugin.

= Is there a road map? =

Yep. It can be found [here](https://wp-native-articles.com/roadmap/).

= Does it support WordPress Multisite? =

Yes it does. There's a management panel in the Network Admin sidebar menu.
It can be used to copy settings from one blog to another and set a default blog
to inherit settings from when new blogs are created.

= I've found a bug? I have a feature request? =

Excellent. We'd love to hear from you. All support for the free plugin is done
through the WordPress support forum found on the Plugin download page.

== Screenshots ==

1. Default options page.
2. Default options page (cont).
3. Feed specific options.
4. Post options overrides box.
5. Multisite options page.

== Changelog ==

= 1.0.0 =
* Plugin released
