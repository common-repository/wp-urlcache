=== WP-URLCache ===
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=pL%40fusi0n%2eorg&lc=CA&item_name=Pier%2dLuc%20Petitclerc%20%2d%20Code%20Support&currency_code=CAD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHostedGuest
Tags: url, cache, archive, scrape
Requires at least: 2.7
Tested up to: 2.8.9
Stable tag: 0.2
Author: Pier-Luc Petitclerc
Author URI: http://blog.fusi0n.org

WP-URLCache allows you to use a shortcode to locally cache outgoing links in your posts and pages.

== Description ==

WP-URLCache allows you to use a shortcode to locally cache outgoing links in your posts and pages.

== Installation ==

* Use WordPress’ builtin plugin installation system located in your WordPress admin panel, labeled as the "Add New" options in the "Plugins" menu to upload the zip file you downloaded
* Extract the zip file and upload the resulting "wp-urlcache" folder on your server under `wp-content/plugins/`.

All you need to do after that is navigate to your blog’s administration panel, go in the plugins section and enable WP-URLCache.

In order to personalize the available options, check in WordPress' "Misc" section under "Settings" (options-misc.php).

For more information, see the ["Installing Plugins" article on the WordPress Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

== Frequently Asked Questions ==

= How do I use it? =

Simply use the [urlcache *url* *target* *alt* *cachetext*] shortcode in your posts where:
* URL is the URL you want to cache
* Target is the link target value
* Alt is the link alternate value
* CacheText is the text you want to display in lieu of the cached link

= Any technical requirements? =

* You will need PHP5. PHP4 has been [officially discontinued since August 8 2008](http://www.php.net/archive/2007.php#2007-07-13-1). If this plugin gives you PHP errors (T_STATIC, T_OLD_FUNCTION), you should strongly consider upgrading your PHP installation.
* You will need [cURL](http://docs.php.net/manual/en/book.curl.php)
* You will also need at least WordPress 2.7 since WP-URLCache is using WordPress' Settings API.

== ChangeLog ==

= 0.1 =

* Initial release
