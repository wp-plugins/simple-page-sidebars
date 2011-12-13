=== Simple Page Sidebars ===
Contributors: blazersix
Donate link: http://bit.ly/s2zcgD
Tags: sidebars, custom sidebars, dynamic sidebar, simple, widget, widgets
Requires at least: 3.2.1
Tested up to: 3.3
Stable tag: trunk

Assign custom, widget-enabled sidebars to any page with ease.

== Description ==

Designed for simplicity and flexibility, Simple Page Sidebars gives WordPress users and theme authors the ability to assign custom sidebars to individual pages--without making any template changes. Existing sidebars can be assigned in quick edit and bulk edit modes, helping save you time.

Also included is a widget to allow a sidebar to include all the widgets from any other widget area.

== Installation ==

Installing Simple Page Sidebars is just like installing most other plugins. [Check out the codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins) if you have any questions.

#### Setup
After installation, go to the Reading options panel (the Reading link under Settings) and choose the default sidebar.

*Note: The initial version of Simple Page Sidebars required a template change that is no longer needed in the latest release. It's recommended that any code changes be reverted.*

== Screenshots ==

1. Simply create a new sidebar when editing a page.
2. The new sidebar shows up on the widget panel. Notice the new "Widget Area" widget for including other widget areas.
3. Bulk edit in action. Easily assign a sidebar to multiple pages. (Quick edit works, too!)

== Changelog ==

= 0.2 =
* Added an option to define the default sidebar on the Reading options panel.
* Removed the template change requirement. It's no longer recommended.
* Refactored code, including function/hook names.
* Deprecated `simple_sidebar` function. Replaced by `simple_page_sidebar`.
* Deprecated `simpsid_widget_areas` filter. Replaced by `simple_page_sidebars_widget_areas`.
* Deprecated `simpsid_widget_area_defaults` filter. Replaced by `simple_page_sidebars_widget_area_defaults`.
* Deprecated `simpsid_sidebar_name` filter. Replaced with `simple_page_sidebars_last_call`.

= 0.1 =
* Initial release.