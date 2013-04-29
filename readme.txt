=== Hosting Monitor ===
Contributors: mbijon, Alive Media
Tags: disk space, shared hosting, web space, server monitor, visual tracking, alerts, overage, resources, storage
Requires at least: 3.0
Tested up to: 3.5.2
Stable tag: 0.7.3

Track how much disk and database space WordPress is using.


== Description ==

Hosting Monitor is an easy, visual way to track how much disk and database storage your WordPress site uses. Let users upload-away and keep an eye on storage without logging in to your hosting control panel or SSH.

= Compatibility Notes =
* Works on both shared hosting plans and dedicated servers
* Supports all Linux web servers and Apache on Windows. Windows IIS not currently supported

= Coming Soon =
* Email alerts so you don't need to check constantly
* Windows IIS support
* Bandwidth tracking


== Installation ==

1. Upload the entire folder `hosting-monitor` to the WordPress Plugins directory and activate it
2. Go to Tools > Hosting Monitor in WordPress Admin. Set the maximum disk space allowed by your hosting company & press "Save Changes"

**NOTE** The Plugin can't tell how much space you're *allowed* to use by your host. This means the amount-remaining and the diagrams are only accurate if you configure the plugin first.


== Frequently Asked Questions ==

= Plugin Requirements =

This Plugin only works completely on Linux/Apache servers. Windows IIS servers don't work right.
To see the pie charts on the Settings page, your server needs to have GD 2.0.1 or later installed and have JPG Support enabled. Without this the plugin will not fail, but you won't see a pie chart.

= Does this Plugin run on Windows web servers? =

Not entirely. It works on Windows Apache, but has errors on Windows IIS.

= I've noticed my Dashboard is slow. What gives? = 

The used disk space is calculated when the Dashboard is loaded. It can be slow because the server counts every file, every time. On slow servers this can take some time. We agree that it's annoying and plan to fix it.

To prevent this, close the dahsboard window using the little arrow in the top-right corner. Alternatively, click on Screen Options and disable the widget.

= Are you going to fix {bug X}? =

Yes, as quickly as we can. The problems in version 0.5 and some we inherited from a previous plugin should be fixable. We can probably make this work correctly on Windows servers. And, we should be able to cache the disk space stats so the dashboard is not so slow.

There will be additional features added in future versions too: The 1st one planned is better alerts for when you're using too much disk-space (including an optional email alert). Second is adding bandwidth monitoring. Then we'll move on to adding tools for tracking & alerting if your server is down.

= Where did this come from, and will you keep updating it? =

Hosting Monitor is produced by: www.AliveMediaDev.com, and developed by: www.EtchSoftware.com. It is built with code from Disk Space Pie Chart by Jay Versluis and the Pie Chart Script by Rasmus Peters.

This plugin is installed on many of our customer sites. We plan to keep it updated _and_ to add new features as often as time allows. It is more than just a hobby, since it must be updated for new versions of WordPress.


== Screenshots ==

1. Dashboard widget: See WordPress disk use & database size
2. Admin Tools page: View disk use & database size. Also configure storage settings here


== Upgrade Notice ==

= 0.7 =
Smooth upgrade from 0.5.x or 0.6.x versions. Just backup & use WordPress update


== Changelog ==

= 0.7 =
* Minor efficiency updates
* Prevent division-by-zero error
* Tested and updated to work up to WordPress 3.5.1

= 0.6.2 =
* Update Upgrade Notice with correct rev

= 0.6 =
* Add detailed Help menu (including 3.3 Help Tabs, & fallback for older Contextual Help)
* Typo update in URL

= 0.5.5 =
* Add editable DB size & units
* Block non-admins from seeing extra settings on Dashboard

= 0.5.1 =
* Formatting changes: Update confirm, field labels, & unit rounding
* Add nonces & sanitize input for admin options
* Updated readme descriptions & formatting
* Change DB size warning to 10MB, from 4MB

= 0.5 =
* First version of this plugin, built on code from plugin 'Disk Space Pie Chart' plugin by Jay Versluis
* Fixed division-by-zero warning & unquoted array value error
* Updated coding style (not complete yet), closer to WP standards
* Alert user on Dashboard if plugin has not been configured with space setting
* Moved admin-settings page from under Dashboard to under Tools
* Updated readme.txt, tried to shorten it
