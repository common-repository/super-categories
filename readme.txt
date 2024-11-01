=== Super Categories ===
Contributors: Aaron Harun
Donate link: http://anthologyoi.com/about/donate/
Tags: multi-blog, multiple blogs, super categories
Requires at least: 2.1
Tested up to: 2.1.2
Stable tag: 0.8

This plugin allows you to have multiple blogs running off one Wordpress install which can share content, have different titles, descriptions and themes.

== Description ==

The Super Category plugin allows you to have multiple websites running off of one install and one database (one set of tables). The websites can share content if you wish it, and can have different titles, descriptions and themes. This plugin is a true plugin: there are NO HACKS and NO THEME EDITS of any kind. The plugin works with any other plugins and themes that use Wordpress’ built in APIs, and features modular functions that can even make other plugins that use custom queries work also. However, this plugin is not for the novice user.

_This plugin is Officially unsupported. It is only a proof-of-concept._

_Use at your own risk._

== Installation ==

* Read section entitled “Parking Domains” — if you know what a parked domain is AND can park them, you can skip this part.
* Download Plugin
* Read all instructions under "Other Info"
* Upload super_cat.php to wp-content/plugins/
* Activate Plugin
* Follow instructions that you previously read.


== Setuping Up ==

**I Assume That…**

* You have access to virtual sub-domains or parked domains, or know how to forward all requests to a single file.
* You have at least a mid-level working knowledge of how wordpress displays information.
* You are using a clean install, or that any pre-existing categories and links you want part of one site. If this is not true it is not difficult to change it later.
* You want all the same options and activated plugins on all sites. Currently you can have separate blog titles, descriptions, themes, posts, categories, pages, links, and feeds. Everything but basically.
* You want any pre-existing pages on your blog to be available to all blogs. Again if this is not true it is easy to change.
* You use themes that use fairly normal methods to display categories, links, posts, etc. Themes that don’t use the api’s provided and actually touch the database won’t allow the filters to filter. These themes may need to be edited.
* The same goes for plugins.
* You will read all of the instructions–yah right.

**How To Use It.**

* The Super Category that you will be using is your site URL. This includes the subdomain and the top-level domain. So for example, http://www.mail.google.com would have a Super Category of ‘mail.google.com’, http://anthologyoi.com would have a Super Category of anthologyoi.com
* The basic idea of this plugin is that anything that can be designated for a single site is added to a Super Category. Super Categories are designated for categories and pages. You may then add other pages and categories to those Super Categories by making them children.
* Parent categories and pages will function as normal except for when they are Super Categories.
* Links and Posts can be added to the categories as normal.
* To have pages visible on all sites do not add them to a Super Category. To have links and posts on multiple sites just select categories with different Super Categories.
* You can have as many sites and super categories, and no matter how you add posts or pages your readers will never know there are multiple sites.
* Posts and links SHOULD NEVER be added directly to Super Categories
* The Super Category management page you can change the blog title, description or theme for each Super Category. You can also add them.

== Parking Domains ==

This is not a How-To guide for parking domains; instead it is meant to be just a brief introduction and explanation of what you will need to be able to do to use this plugin. Without parked domains this plugin is almost worthless (although there are some useful traits) so make sure you can use them before you install the plugin.

First of all to park a domain you need a minimum of two domains. A parked domain is a domain that points to the EXACT same place as another domain without using frames or redirections. It is not an add-on domain which points to a sub-folder. You may not be able to park domains depending on your hosting company; if you can’t you must either switch companies or convince your companies to allow you to park domains. Occasionally, a hosting company may refer to parked domains as aliased domains or one of several other names. If the domain name can point to the same folder as another domain name it will work no matter what the name is.

Secondly, you must make sure that the parked domains are registered with valid name servers and have propagated through the DNS before adding them to this plugin. While there will be no obvious errors if you try to do it before hand, none of the options will work until the domain is fully propagated.

== Frequently Asked Questions ==

= XYZ feature doesn't work and ABC plugin breaks. =
Unfortunately, this plugin is very experimental. The version number shows how far it has come since the beginning, not how well it works. It taps into every part of Wordpress and modifies it. There are guaranteed to be bugs, which is why I suggest it for developers only.

For the most part I don't support conflicts with other plugins. However, you may report which plugins work and which ones don't. 
