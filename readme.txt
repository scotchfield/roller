=== Roller ===
Contributors: sgrant
Donate link: http://scotchfield.com
Tags: dice, rolling, rpg, role playing, dungeons and dragons, dice rolling
Requires at least: 4.0
Tested up to: 4.3
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress plugin for dice rolling, random lists, and conditional variables.

== Description ==

Want to set up custom character sheets on your WordPress installation? Need a dice rolling app with standard (or non-standard) dice? This collection of shortcodes makes it easy to add random dice rolls to your page.

In addition to the dice, you can define lists in the administration panel--things like names, professions, and locations--and can choose elements at random using a single shortcode.

And you want to build conditional logic based on those results? Want to set skill variables based on the random profession chosen? Store results in variables and use conditional logic to modify state as you need it.

Shortcodes included:

Roll some dice: [roller 3d6]

Save dice rolls as variables: [roller 3d6 var=str]

Display a variable's value: [roller_var str]

Equations: [roller 3d6 var=pow] [roller_exp pow*5 var=san]

Random list elements: [roller_choose var=gender list=gender]

Conditionals: [roller_if gender=Female][roller_choose var=first_name list=first_name_female][/roller_if]

== Installation ==

Place all the files in a directory inside of wp-content/plugins (for example, roller). Activate the plugin, and look for the Roller menu in the sidebar.

== Frequently Asked Questions ==

= How do I show the result of rolling a single six-sided die? =

Use the shortcode [roller 1d6]. The shortcode will be replaced on the page with the result. Refreshing the page will cause another roll to occur

= How do I save the roll results in variables? =

Use the var attribute. For example, [roller 1d6 var=myroll]. You can recall the result by using [roller_var myroll].

== Screenshots ==

1. A collection of sample lists defined in the administration panel.

== Changelog ==

= 1.0 =
* First release!

== Upgrade Notice ==

= 1.0 =
First public release.
