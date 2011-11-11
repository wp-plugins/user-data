=== Plugin Name ===
Contributors: circlecube
Donate link: http://circlecube.com/contact/
Tags: user, author, profile, bio
Requires at least: 3.0
Tested up to: 3.2
Stable tag: 1.1.1

Add certain fields and images to your user profiles. Also gives access via shortcode to display a list of users/authors anywhere on your site. 

== Description ==

Add certain company centric fields and images to your user profiles, the following fields are added to the edit user page. 

*	job title
*	sort (a hidden field for contorlling display order)
*	html bigraphy (bio is already present, this adds an additional one which supports html)
*	skills
*	phone number
*	start date
*	picture
*	thumbnail
*	twitter
*	facebook
*	google+
*	linkedin
*	youtube

Also gives access via [cc-user-data-list] shortcode to display a list of users/authors anywhere on your site.

Attributes supported by the shortcode:

*   show_thumbs="false"		- hides the thumnail from the list
*   show_picture="false"	- hides the picture from the list
*   show_bio="false" 		- hides the bio from the list
*   show_title="false" 		- hides the job title from the list
*   show_email="false" 		- hides the email from the list
*   show_name="false" 		- hides the name from the list
*   show_posts="false" 		- hides the authors recent posts (if any) from the list
*	show_social="false" 	- hides the authors social links (if any) in the list


== Installation ==

Install the plugin and get it working.

1. Upload `cc-user-data` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Edit users and fill in exra details as desired
4. Place `[cc-user-data-list]` in your post/page to display the author list
5. (optional) Add a User Data widget to your site to display a random user spotlight.

== Frequently Asked Questions ==

None yet
Initial install

== Screenshots ==

1. A view of the shortcode in a post.
2. Check out the extra meta data on the user edit page.
3. Here is a preview of the widget you get.
4. Here is the front end with a couple authors from the shortcode in the first screenshot.

== Changelog ==


= 1.1.1 =
*	typo fix- update

= 1.1.0 =
*	adding a widget to display a random user

= 1.0.0 =
*	initial release

== Upgrade Notice ==

= 1.0 =
Initial Version

== Roadmap ==

Things in consideration for future development

* Create your own fields interface
* Drag/drop update sort order
* Sort by sort order on user index page
* Calendar for date
* Add fields...
* Add shortcode options for sorting behavior
* Add shortcode option for getting specific users, or roles only
