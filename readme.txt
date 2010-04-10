=== Posterize ===
Contributors: Yan Sarazin
Tags: posterous, email, posts
Requires at least: 2.8.6
Tested up to: 2.9.2
Stable tag: 2.1.0

WordPress to Posterous plugin.

== Description ==

Posterize will cross-post your Wordpress blog post to a Posterous site. You can choose whether to link back your Wordpress blog post or cross-post the full content. Also allows you to select which Posterous site to post to.


== Installation ==

1. If you are upgrading, it is recommended that you deactivate the plugin from the Plugins page, and delete the posterize folder from your server.
2. Upload the unzipped contents to your /wp-content/plugins/ directory.
3. Active the plugin from the Plugins page.
4. Go to settings page to configure Posterize

== Configuration == 

1. Enter your Posterous email and password (used to log into http://posterous.com)
2. Fill in site id by clicking on "Get Sites"
3. Chose "Link back to post" or "Post full content"

== Changelog ==

= 2.1.0 =
* Including WordPress post tags if available
* Passing Posterize as the source in the Posterous API call

= 2.0.2 =
* Fixed issues where an error was thrown when “get sites” would return a single Posterous site.

= 2.0.1 =
* Complete rewrite from the ground up
* No longer uses email. Utilizes the Posterous API to interact with Posterous
* Ability to select which Posterous site to post to
* Administrator settings panel for easy configuration

= 1.0.1 =
* Changed Posterous email from post@posterous.com to posterous@posterous.com to avoid potential cross-post loop.

= 1.0.0 =
* Initial Posterize version.

