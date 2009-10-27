=== Expire Comment Links ===
Contributors: aaroncampbell
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=paypal%40xavisys%2ecom&item_name=Expire%20Comment%20Links&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: seo, comments, search engines, google
Requires at least: 2.8
Tested up to: 2.9
Stable tag: 0.1.1

Allows you to stop displaying links for old comments. PHP 5+ required.

== Description ==
Expire Comment Links was inspired by <a href="http://yoast.com">Joost de Valk</a>
in an article he wrote called
<a href="http://www.searchcowboys.com/columns/764">Comment links: an experiment</a>.
It all started when Matt Cutts talked about how Google changed how it handles no
follow links in his article on
<a href="http://www.mattcutts.com/blog/pagerank-sculpting/">PageRank Sculpting</a>.

No one knows for sure yet if this will be beneficial or not, but for those that
want to be on the cutting edge and try this experiment for themselves, this
plugin automates everything.

Requires PHP5.

You may also be interested in <a href="http://wpinformer.com">WordPress tips and tricks at WordPress Informer</a> or general <a href="http://webdevnews.net">Web Developer News</a>

== Installation ==

1. Verify that you have PHP5, which is required for this plugin.
1. Upload the whole `expire-comment-links` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Will this help me rank better in search engines like Google? =

Maybe.  The truth is, we don't know yet.  Joost is an exceptionally tallented
Search Engine Optimation expert, but this method is currently an unproven
experiment.

== Changelog ==

= 0.1.1 =
* Removed the optional anonymous statistics collection.  Nothing is ever collected anymore.

= 0.1.0 =
* First version released to wordpress.org
* Added mechanism to give additional info on future updates via the plugin page

= 0.0.3 =
* Don't run make_clickable on old comments just to undo it later

= 0.0.2 =
* Add the setting link to the plugin row
* Optimize the regular expression

= 0.0.1 =
* Original Version
