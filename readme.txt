=== Simple Page Sidebars ===
Contributors: blazersix
Donate link: http://bit.ly/s2zcgD
Tags: sidebars, custom sidebars, dynamic sidebar, simple, widget, widgets
Requires at least: 3.2.1
Tested up to: 3.2.1
Stable tag: 0.1

Assign custom, widget-enabled sidebars to any page with ease.

== Description ==

Designed for simplicity and flexibility, Simple Page Sidebars gives WordPress users and theme authors the ability to assign custom sidebars to individual pages. Existing sidebars can be assigned in quick edit and bulk edit modes, helping save you time.

Also included is a widget to allow a sidebar to include all the widgets from any other widget area. Don't worry if that didn't make sense, because without a sanity check, a Widget Area widget including it's parent widget area would create an infinite loop.

== Installation ==

Installing Simple Page Sidebars is just like installing most other plugins. [Check out the codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins) if you have any questions.

#### Setup
You'll need to include the following function call within your sidebar template in order for the plugin to know which sidebar to replace.

`if ( function_exists( 'simple_sidebar' )
	simple_sidebar( 'Default Sidebar Name' );`

== Screenshots ==

1. Simply create a new sidebar when editing a page.
2. The new sidebar shows up on the widget panel. Notice the new "Widget Area" widget for including other widget areas.
3. Bulk edit in action. Easily assign a sidebar to multiple pages. (Quick edit works, too!)

== Changelog ==

= 0.1 =
Initial release.