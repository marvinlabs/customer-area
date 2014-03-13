=== Customer Area ===
Contributors: vprat, marvinlabs
Donate link: http://www.marvinlabs.com/donate/
Tags: private files,client area,customer area,user files,secure area,crm
Requires at least: 3.6
Tested up to: 3.9.0 beta
Stable tag: 4.5.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Give your customers a page on your site where they can access private content (files, notices, ...). 

== Description ==

Give your customers a page on your site where they can access private content in a secure and easy way. 
As of today, private content means files: you upload files for your customer, and only him will be able to view them
or download them on his page. 

* [Documentation](http://customer-area.marvinlabs.com/documentation)
* [FAQ](http://customer-area.marvinlabs.com/category/faq/)
* [Support](http://customer-area.marvinlabs.com/support)
* [Demo](http://customer-area.marvinlabs.com/demo)
* [Add-ons](http://www.marvinlabs.com/downloads/category/customer-area)
* [Translations](http://customer-area.marvinlabs.com/documentation/translations/)
* [GitHub repository for contributors](https://github.com/marvinlabs/customer-area)

**Current features**

* Secure customer area, accessible to logged-in users
* Private pages, that can be assigned to a particular user and will get listed in its customer area
* Private files, that can be assigned to a particular user and will get listed in its customer area
* Show private files grouped by category / by year / ungrouped in the customer area
* Comments on private files and pages: the customer/user can send some feedback/observations about it
* Customize the plugin appearance using your own themes and templates 
 
**Extensions are now available!**

Customer Area is available for free and should cover the needs of most users. If you want to encourage us to actively 
maintain it, or if you need a particular feature not included in the basic plugin, you can buy our premium extensions 
from [our online shop](http://www.marvinlabs.com/downloads/category/customer-area): 

* [Collaboration](http://www.marvinlabs.com/downloads/customer-area-collaboration/)
* [Conversations](http://www.marvinlabs.com/downloads/customer-area-conversations/)
* [Extended Permissions](http://www.marvinlabs.com/downloads/customer-area-extended-permissions/)
* [Login and registration forms](http://www.marvinlabs.com/downloads/customer-area-login-register-forms/)
* [Managed Groups](http://www.marvinlabs.com/downloads/customer-area-managed-groups/)
* [Notifications](http://www.marvinlabs.com/downloads/customer-area-notifications/)
* [Owner Restriction](http://www.marvinlabs.com/downloads/customer-area-owner-restriction/)
* [Search](http://www.marvinlabs.com/downloads/customer-area-search/)
* [Switch Users](http://www.marvinlabs.com/downloads/customer-area-switch-users/)

**Included translations**

* Dutch by [Paul Willems](http://wi4.nl) and [Peter Massar](http://profiles.wordpress.org/yourdigihands/)
* English by [MarvinLabs](http://www.marvinlabs.com)
* French by [MarvinLabs](http://www.marvinlabs.com)
* German by [Benjamin Oechsler](http://benlocal.de)
* Spanish by [e-rgonomy](http://e-rgonomy.com)
* Brazilian Portuguese by [Ricardo Silva](http://walbatroz.com) and [Marcos Meyer Hollerweger](http://marcosh.eng.br/)
* Italian by [Andrea Starz](http://www.work-on-web.it)
* Swedish by Patric Liljestrand

If you translate the plugin to your language, feel free to send us the translation files, we will include them and give
you the credit for it on this page.

**About us**

If you like our plugins, you might want to [check our website](http://www.marvinlabs.com) for more.

If you want to get updates about our plugins, you can:

* [Follow us on Twitter](http://twitter.com/marvinlabs)
* [Follow us on Google+](https://plus.google.com/u/0/117677945360605555441)
* [Follow us on Facebook](http://www.facebook.com/studio.marvinlabs)


All the screenshots where done on a WordPress 3.6 development site, using the base Twenty Thirteen theme. No other
customisation has been done to the plugin or to the add-ons.
	
	
== Upgrade Notice ==

We have a safe upgrade procedure that needs to be followed, specially if you have installed any add-ons to the main
plugin: [Important upgrade procedure for Customer Area](http://customer-area.marvinlabs.com/documentation/general-topics/updating-main-plugin-andor-add-ons/)

= Upgrading from 3.x to 4.x =

Version 4 introduces a brand new menu system and breaks many templates that have been there in version 3.x. Coming 
from that version, you will need to create the pages that are necessary to display the private content types. Before
upgrading the plugin, backup your files and database. It is advised that you attempt the upgrade on a development 
site first.

= Upgrading from 2.x to 3.x =

Because version 3 introduces a new layout for the customer area, template files for displaying files and pages 
have been renamed and reorganised. If you have customized them, be warned that you will need to change your 
customized files to reflect the changes! In that case, first do the upgrade on a development site, not on your live 
website. And as always:

1. Backup your database
2. /!\ IMPORTANT - Update all the Customer Area extensions FIRST
3. Update the Customer Area plugin

= Upgrading from 2.x to 2.3.0 =

Make sure to backup your database. Some important changes have been made to the ownership system and even though we have
tested our upgrade script as much as we could, you might find yourself with owners of posts reset to nobody.

1. Backup your database
2. /!\ IMPORTANT - Update all the Customer Area extensions FIRST
3. Update the Customer Area plugin

= Upgrading from 1.x to 2.x =

You must also upgrade all the add-ons you are using.

== Installation ==

1. Nothing special, just upload the files, activate and you can then visit the settings page if you want. Like any 
other plugin
1. You need then to create a page and insert the [customer-area] shortcode. Your customers will be able to access their
private content on that page.
1. Finally, you will need to create content for the users (!): you can start with a private file for instance. Just 
check out the WordPress menu in your administration panel, you can add new customer files just like any post. Simply 
upload a file (below the content box), set the owner of that file, publish, and your customer should be able to see it.  

== Screenshots ==

01. The customer area for logged-in customer "Gail". Files are grouped by categories. A click on the category title 
expands the panel. Currently shown is the "Invoices" category.
02. The customer area for logged-in customer "Gail". Files are grouped by categories. A click on the category title 
expands the panel. Currently shown is the "Product Samples" category.
03. The detail page for a private file. You can see the title, description and download links. 
04. The detail page for a private file. Same page as in the previous screenshot, except that we have scrolled down
to show that you can also comment on a private file, you can then have a discussion with your customer about that
particular file
05. The private file edition page. You can have a title, description, attach a file and set the owner easily, as in any
other WordPress admin screen
06. The main settings screen.
07. The private files settings screen.
08. The login screen when the login form add-on is enabled.
09. The lost password screen when the login form add-on is enabled. Also shows the integration with the Really Simple
CAPTCHA plugin.
10. The registration screen when the login form add-on is enabled. Also shows the integration with the Really Simple
CAPTCHA plugin.
11. Send a notification to your customer when you create a new private file (see top-right box). You need to enable
the notifications add-on.
12. The settings screen when the notifications add-on is enabled.
13. The rest of the settings screen when the notifications add-on is enabled.

== Frequently Asked Questions ==

= Getting help / documentation / support / demo / ... =

You have all the information on the [plugin website](http://customer-area.marvinlabs.com):

* [Documentation](http://customer-area.marvinlabs.com/documentation)
* [FAQ](http://customer-area.marvinlabs.com/category/faq/)
* [Support](http://customer-area.marvinlabs.com/support)
* [Demo](http://customer-area.marvinlabs.com/demo)

= That feature is missing, will you implement it? =

Open a new topic on the plugin's support forum, I will consider every feature request and all ideas are welcome.

= I implemented something, could you integrate it in the plugin? =

Contributions are welcome. Additionally, if you wish to participate to development, you can send us an email 
([check-out our website](http://www.marvinlabs.com)) and tell us a little bit about you (specially, send us a link to 
your wordpress.org profile with your other developed plugins.

== Changelog ==

= 4.5.0 (2014/03/13) =

* New: compatibility with the new Search add-on
* New: allow fetching templates from multiple root directories (dev. feature)
* New: added a category filter in the private page and private file administration pages.
* New: function to print the private file size (cuar_the_file_size)

= 4.4.0 (2014/03/07) =

* New: templates now have a version number. This will help you to detect your outdated overriden templates.
* New: slighlty improved template debug info
* New: added feature to import/export settings (see Customer Area > Status > Settings)

= 4.3.0 (2014/03/03) =

* New: Added the addon ID in the add-on status page (useful for developers)
* New: Added more page information in the pages status page (useful for developers)
* New: Added a hook to change the redirect URL for root pages
* Fix: Translation problem in the customer account page

= 4.2.3 (2014/02/21) =

* Fix: renamed the dashboard query filter
* Fix: bug in some file templates functions 

= 4.2.1 (2014/02/20) =

* New: new template functions. See the directory "customer-area/includes/functions" for details
* New: some templates have been updated to use the new template functions
* New: add a warning when we detect that permalinks are disabled
* Fix: do not force to select a category when creating content and no category has yet been created
* Fix: the rich editor setting was not working properly for some add-ons
* Fix: refactoring typo that was affecting the "advanced owner restrictions" add-on

= 4.1.2 (2014/02/19) =

* Fix: another "headers already sent" bug. This one was tricky. Hopefully that's the last one...

= 4.1.1 (2014/02/19) =

* New: Allow filtering queries for date archive and recent content widgets
* Fix: Filter name for content queries was wrong

= 4.1.0 (2014/02/18) =

* New: Added a list of all hooks (actions and filters) that are available to developers in the status page.
* Fix: a strange bug that most likely happens when you hav orphan menu items in your database.

= 4.0.3 (2014/02/17) =

* New: setting to output debug information for developers about the templates used in a page
* New: Added some developer info in the status page for customer pages (to help findind which template to override)
* Fix: logout page bug when getting the warning "headers already sent"

= 4.0.2 (2014/02/17) =

* New: Add a class to the body element on customer area pages (helps to tweak CSS)
* Fix: redirect issue occuring sometimes (headers already sent)
* Fix: small issue when re-creating the customer area menu in some cases
* Fix: Some small fixes on the default-v4 theme

= 4.0.1 (2014/02/16) =

* New: Added a reminder to configure the plugin permissions
* Fix: normal WordPress pages could not be added to any navigation menu

= 4.0.0 (2014/02/15) =

**THIS IS A MAJOR UPDATE - PLEASE MAKE SOME TESTS ON YOUR DEVELOPMENT AND TEST SITES BEFORE UPGRADING YOUR LIVE WEBSITE**

* Completely changed the way information is displayed in the frontend. Now the plugin uses a system of pages similar to the one used by WooCommerce for example. 
This has allowed greater possibilities for customization (Widget areas, more templates to override, ...)
* The customer area main navigation menu now is a WordPress menu that can be customized just like any other WordPress menu (from the admin page: "Appearance" > "Menus")
* Restructured the options page. It now has less tabs and should group options in a more logical way. If you have commercial add-ons, you may need to enter their
license key again. These are available by logging-in with your credentials at http://www.marvinlabs.com/shop/your-account/
* Added category column in the admin page for private files and private pages (A pending feature request from several users)
* Refactored a lot of code to make the plugin ligher and more efficient. Should also reduce the amount of duplicated code and thus the potential hidden bugs. 
* Add possibility to include a function file in your Customer Area theme (See included frontend theme: default-v4). Works like WordPress' functions.php file for themes
except that this file should be located in your Customer Area theme folder (not the WordPress theme's folder) and it should be named 'cuar-functions.php'

= 3.9.1 (2014/02/07) =

* Added lot of mime types, this should fix th "0 bytes downloads" bug on old browsers.

= 3.9.0 (2014/01/23) =

* Improved the interface to select private content owners. Threw in some jQuery goodness.
* Load frontend scripts only when showing the Customer Area page (not on the other pages of the website)

= 3.8.3 (2014/01/07) =

* Better error handling when uploading forbidden file types and files which exceed the size limit

= 3.8.2 (2013/12/06) =

* Fix a PHP warning message on some servers for new install (Warning: session_start() [function.session-start]: Cannot send 
session cookie - headers already sent by ...)
* Updated Brazilian Portuguese translation

= 3.8.1 (2013/12/05) =

* Added a button to reset options to default values (settings > Support)

= 3.8.0 (2013/12/03) =

* Add page categories
* Adjust styles for new WP 3.8 admin 
* Added some value to the main Customer Area admin dashboard (news / FAQ / resources / ...)

= 3.7.5 (2013/11/29) =

* Updated Dutch translation

= 3.7.4 (2013/11/28) =

* Updated Dutch translation
* Added Swedish translation

= 3.7.3 (2013/11/27) =

* Added some support functions to new Collaboration add-on features.

= 3.7.2 (2013/11/26) =

* Added a template function to show the private content owner name

= 3.7.1 (2013/11/22) =

* Added the setup wizard (was not showing up anymore)
* Remove a PHP warning in the new private file screen when the FTP upload folder does not exist  
* Fixed a bug when setting a default owner with Collaboration add-on active and without Extended Permissions add-on active
* Optimised a lot of pages that were slow, in particular on sites with lots of users

= 3.7.0 (2013/11/06) =

* Allow to copy files from an FTP folder rather than directly uploading them from the browser. Handy for big files! Thanks to Pat O'Brien for his work on this feature.

= 3.6.2 (2013/11/05) =

* Added POT file to easy translation work
* Added some handy actions and filters
* Added compatibility with the new add-on to view another Customer Area easily

= 3.5.0 (2013/11/01) =

* Added a setting to adjust the number of items shown in the dashboard for each private content type
* Allow listing private files grouped by months
* Fix main menu warnings when using the collaboration add-on

= 3.4.1 (2013/10/30) =

* Fixed a fatal error when showing the customer area for a guest user 

= 3.4.0 (2013/10/30) =

* Allowing to output the main menu in the single post pages (Some templates have changed in the customer page core add-on, careful if you have customized any of those)
* Fixed a typo causing a fatal error below "select the type of owner"

= 3.3.0 (2013/10/27) =

* Added actions and filters to allow more customisation without having to change the templates 
* Added a new capability for private content to allow some role to be able to view any private content (useful for admins/moderators)
* Refined capabilities for the back-office

= 3.2.3 (2013/10/24) =

* Updated italian translation

= 3.2.2 (2013/10/22) =

* Added a way to hide the owner when only a single owner is selectable when creating private content (requested by 2 users). This
is disabled by default and enabled by a checkbox in the general plugin settings. 
* Fixed bug when accessing a private file/page/conversation directly:  if the user follows a link directly to a private
page, this presents the standard wordpress login and not the plugin login page.
* Added Italian translation by [Andrea Starz](http://www.work-on-web.it)
* Adjusted the styling for the add-ons page

= 3.1.2 (2013/10/21) =

* Made the warning message about "no customer area page detected" clearer
* A small update needed by the new Notifications add-on version

= 3.1.0 (2013/10/21) =

* Added a 20-second setup wizard for new installs
* Removed view link for files (not very useful and causes problems with some file types on some browsers, e.g. pdf on Chrome)
* Show more information about private files and private pages in single post views
* Fixed a problem for titles in the customer page when using the Collaborations add-on

= 3.0.1 (2013/10/18) =

* Updated the way the action menu is displayed (no more button, just a simple list of actions)
* Better layout for the customer area: private content is now displayed in its own content type page and the customer area
only shows the most recent content (like a dashboard)
* As a consequence of the above, and for naming consistency, the templates have been renamed for private files and pages 
* Added Brazilian Portuguese translation by Ricardo Silva (http://walbatroz.com)
* Add link to main Customer Area page (Dashboard) in the menu
* Added more details to the list of files and pages 

= 2.4.0 (2013/10/15) =

* Added a way to restrict listing of private content in the administration area (if user does not have the capability
to list all the content, he will only see the content he authored) 
* Support for 3 new add-ons: Managed Groups, Owner Restrictions and Messenger 
* Allowing to scroll the capabilities table (see http://wordpress.org/support/topic/cant-access-capabilities)
* Added German translation by Benjamin Oechsler (http://benlocal.de/)
* Added a warning if permalinks are disabled
* Fixed a bug causing warnings to show up on the Collaborations add-on page
* Fixed a bug showing the file categories to user who did not necessarily have access to it 
* Fixed a few untranslatable messages
* Fixed an error on the embedded add-ons page on some servers

= 2.3.6 (2013/09/17) =

* Added Dutch translation by Paul Willems

= 2.3.5 (2013/09/03) =

* Fixed categories cannot be assigned to private files by users without admin/editor role
* Fixed file was not getting moved properly when changing an owner from an existing file 

= 2.3.3 (2013/08/29) =

* Fixed a regression that removed the links to logout or to come back to the customer area main page

= 2.3.2 (2013/08/21) =

* Fixed a bug causing the collaboration add-on not to upload files properly

= 2.3.1 (2013/08/20) =

* Adding translation to Estonian language (Courtesy of R. Huusmann)

= 2.3.0 (2013/08/01) =

* Update required for the new Extended Permissions features (multiple user selection)
* Changed the permalink structure for private posts and pages to make it prettier and to avoid exposing who the owner is

= 2.2.2 (2013/07/22) =

* Fix corrupted file download on some servers (see http://wordpress.org/support/topic/problems-with-image-files)

= 2.2.1 (2013/07/08) =

* Added spanish translation (Courtesy of http://e-rgonomy.com)

= 2.2.0 (2013/07/02) =

* Added some filters to change the titles of the files and pages sections (see FAQ)
* Added a FAQ entry

= 2.1.0 (2013/06/26) =

* New functions for the collaboration addon
* Refactored a few functions from "Post Owner" and "Private Files" core add-ons to allow re-use of code
* Added an optional action bar on top of the customer area (if any add-on wants to show actions there)
* Refined capabilities for private files and private pages (separated "delete" and "edit" capabilities)
* Fixed a few other minor bugs

= 2.0.2 (2013/06/06) =

* Fixed a bug in the private files settings page

= 2.0.1 (2013/05/31) =

* Added troubleshooting information to the settings page to help support
* Refactored all the code controlling the ownership of a post to have less crashes and more possibilities for
  extensions.
* This update will require you to also update all the add-ons if you bought any.

= 1.6.4 (2013/05/27) =

* Fixed bug: http://wordpress.org/support/topic/how-to-keep-the-submenu-opened-when-editing-a-page-or-file

= 1.6.3 (2013/05/20) =

* Fixed bug: http://wordpress.org/support/topic/how-to-keep-the-submenu-opened-when-editing-a-page-or-file
* Fixed bug: http://wordpress.org/support/topic/wp_debug-notice-for-fileperms

= 1.6.2 (2013/05/17) =

* Some internal code to allow add-ons to warn if you are running an incompatible version of Customer Area

= 1.6.0 (2013/05/16) =

* Added a new private content type: private pages 
* Possibility to disable private files from the settings if that add-on is not required

= 1.5.0 (2013/05/16) =

* Re-organised the administration menu under a single menu item
* Added a quick link to the settings in the plugin list  
* Added some version upgrade mechanism for the future
* Added an indication in the settings to give the path of private files and to say if permissions are correctly set
* Added a kind of dashboard page as the plugin entry point, that should be filled later on (feels empty right now, 
we are waiting for your ideas)

= 1.4.0 (2013/05/15) =

* Fixed a bug with permalinks (again)
* Added contextual help in the settings page
* Created an add-on tab in the settings, you can now easily know which add-ons could help you enhance your customer area

= 1.3.1 (2013/05/12) =

* Fixed a bug with permalinks

= 1.3.0 (2013/05/08) =

* Added a capability manager for the plugin

= 1.2.0 (2013/05/07) =

* Added setting to show/hide empty file categories
* Added setting to select the theme

= 1.1.2 (2013/05/06) =

* Added some hooks to customize the login page and the customer area
* Added a link to logout of the customer area 

= 1.1.0 (2013/05/03) =

* Added download count 
* Added support for comments on private files
* Added compatibility with PHP 5.2
* Added possibility to show files grouped by category / by year / ungrouped in the customer area
* Updated french translation
* Don't show register link if registration is disabled anyway
* Redirect to login page if accessing directly a private file and not logged-in
* Fixed a bug in the permalinks preventing the file downloads

= 1.0.1 (2013/05/02) =

* Improved settings page
* Fixed a bug in the frontend 

= 1.0.0 (2013/04/25) =

* First plugin release
* Upload private files for your customer