=== WP Customer Area ===

Contributors:       vprat, marvinlabs, tlartaud
Donate link:        http://www.marvinlabs.com/donate/
Tags:               private files,client area,customer area,client portal,customer portal,user files,secure area,crm,project,project management,access control,files
Requires at least:  4.7
Tested up to:       5.0.0
Stable tag:         7.7.2
License:            GPLv2 or later
License URI:        http://www.gnu.org/licenses/gpl-2.0.html

WP Customer Area is a modular all-in-one solution to manage private content with WordPress.

== Description ==

WP Customer Area is a modular all-in-one solution to manage private content with WordPress. Sharing files/pages with 
one or multiple users is one of the main feature provided by our easy-to-use plugin. Give it a try!

* [Demos](http://wp-customerarea.com/all-demos)
* [Add-ons](http://wp-customerarea.com/add-ons-and-themes/)
* [Documentation](http://wp-customerarea.com/documentation/)
* [Translations](http://wp-customerarea.com/documentation/)
* [Support](http://wp-customerarea.com/support)
* [FAQ](http://wp-customerarea.com/faq/)
* [GitHub repository for contributors](https://github.com/marvinlabs/customer-area)
* [Issue tracker](https://github.com/marvinlabs/customer-area/issues)

**Current features**

* Secure customer area, accessible to logged-in users
* Private pages, that can be assigned to a particular user and will get listed in its customer area
* Private files, that can be assigned to a particular user and will get listed in its customer area
* Customize the plugin appearance using your own themes and templates 
 
**Extensions and themes are now available!**

Invoicing, Conversations, Advanced ownership, Projects, and much more!

WP Customer Area is available for free and should cover the needs of most users. If you want to encourage us to actively 
maintain it, or if you need a particular feature not included in the basic plugin, you can buy our premium extensions 
from [our online shop](http://wp-customerarea.com/add-ons-and-themes/)

**Special thanks**

To [Steve Steiner](www.websaucesoftware.com) for his intensive testing on the plugin, his bug reports and support.

To the translators who send us [their translations](http://wp-customerarea.com/documentation/):

* Catalan by Amanda Fontana
* Dutch by [Paul Willems](http://wi4.nl) and [Peter Massar](http://profiles.wordpress.org/yourdigihands/)
* English by [MarvinLabs](http://marvinlabs.com)
* French by [MarvinLabs](http://marvinlabs.com)
* German by [Benjamin Oechsler](http://benlocal.de)
* Hungarian by [Jagri István](http://www.itcs.hu)
* Spanish by Ulises and [e-rgonomy](http://e-rgonomy.com)
* Brazilian Portuguese by [Ricardo Silva](http://walbatroz.com) and [Marcos Meyer Hollerweger](http://marcosh.eng.br/)
* Italian by [Andrea Starz](http://www.work-on-web.it) and [Antonio Cicirelli](http://www.ideacommerce.it)
* Swedish by Patric Liljestrand
* Turkish by [Mehmet Hakan](http://wpsitesi.com)

If you translate the plugin to your language, feel free to send us the translation files, we will include them and give
you the credit for it on this page.

== Upgrade Notice ==

We have a safe upgrade procedure that needs to be followed, specially if you have installed any add-ons to the main
plugin: [Important upgrade procedure for WP Customer Area](http://wp-customerarea.com/documentation/update-procedure/)

== Installation ==

See our [Getting started documentation](http://wp-customerarea.com/documentation/getting-started/).

== Screenshots ==

01. The dashboard lists all the private content assigned to the user.
02. A private file shows secured download links to attachements.
03. The plugin is available with two skins to allow better integration with most themes: dark and light.
04. Detailled view for collections of private content allow showing more information.
05. Grid view for collections of private content allow viewing more items.
06. User account page (a form to edit that information is also provided)
07. A project can gather any type of private content and show it to all the project team members.
08. An invoice
09. The search page allows finding quickly content assigned to yourself.
10. Task lists provide a convenient way to track progress.
11. Conversations allow creating discussions between one or more users.
12. The login form is embedded within the website
13. Creating a private file can be allowed from the website directly.
14. This is how to choose an owner for the private content.
15. Uploading attachments to a private file is as easy as drag'n'drop.

== Frequently Asked Questions ==

= Getting help / documentation / support / demo / ... =

You have all the information on the [plugin website](http://wp-customerarea.com)

= That feature is missing, will you implement it? =

Open a new topic on the plugin's support forum, I will consider every feature request and all ideas are welcome.

= I implemented something, could you integrate it in the plugin? =

Contributions are welcome. The plugin has a [GitHub repository for contributors](https://github.com/marvinlabs/customer-area)
feel free to fork the project and send us pull requests!

== Changelog ==

= 7.7.2 (2019/08/21) =

* Fix: renamed 'skins/frontend/master/assets/css/eqcss/queries.eqcss' to 'queries.css' to prevent it to be blocked by servers for security reasons
  Warning: DO NOT cache 'queries.css' using cache plugins ! This file is not a CSS file, but a file interpreted by a JS script.
* Fix: issue on my-account page that was making the left column of the fields too small for some sites

** Add-on changes **

* **Design Extras** - Tweak: Support for new styles from WP Customer Area 7.7.2

= 7.7.1 (2019/08/21) =

* Fix: issue on my-account page that was making the left column of the fields too small for some sites

= 7.7.0 (2019/08/19) =

* New: support for ACF v5.8.3 (Warning: Older versions are not anymore compatible with our ACF Integration add-on)
* New: allow "My private contents" to also display "authored by" contents on dashboard and pages such as "My files", "My pages", etc...
* New: filter 'cuar/core/page/query-disable-authored-by' set to 'true' by default to disable contents "authored by" to be displayed on private content pages listings
* New: filter 'cuar/core/page/query-disable-authored-by?post_type=' to prevent "authored by" contents to be displayed on specific private pages listings
* Fix: url profile field that could not be used
* Fix: filter 'nav_menu_link_attributes' parameters that could throw PHP errors on some themes
* Fix: php warning showing up on contact form 7 admin page
* Fix: widget accordion fancytree menu no more loading in some cases
* Fix: php warning thrown when clicking the switch users dropdown
* Tweak: design adjustments and enhancements
* Tweak: update many translations
* Tweak: speedup page loading by optimising some JS scripts

**Add-on changes**

* **ACF Integration** - New: added compatibility with most of the ACF fields (Fields still incompatibles: link, wysiwyg, color_picker, user)
* **ACF Integration** - Fix: edit-account and my-account pages not properly displaying because of a filter change in ACF (acf_match_location_rule)
* **Switch Users** - Fix: missing select2 library that could happen in some cases
* **Design Extras** - Tweak: Support for new styles from WP Customer Area 7.7.0 and ACF v5.8.3

= 7.6.0 (2018/12/14) =

* New: support for ACF 5. ACF 4 is not compatible anymore with our ACF Integration add-on.
* New: improve requirement checks when plugin is activated (PHP and WordPress versions)
* New: plugin requires PHP 5.6 minimum
* New: plugin requires WordPress 4.7 minimum
* New: improved profile page - allow grouping of profile fields
* New: show download progress on download all button for private files
* Fix: download all button was not working in recent versions of Chrome
* Fix: use get_user_locale instead of get_locale
* Fix: payment form when using multiple gateways
* Fix: remove use of WP_***_URL constants in favor of functions [#315](https://github.com/marvinlabs/customer-area/issues/315)
* Tweak: update frontend WYSIWYG editor
* Tweak: update translations

**Add-on changes**

* **ACF Integration** - New: support for ACF 5
* **Notifications** - New: Add filter to allow filtering the IDs of the users who should receive the notifications targeted at administrators. Use filter 'cuar/notifications/administrator-recipient-ids' to customize.
* **Design Extras** - Tweak: Support for new styles from WP Customer Area 7.6.0 and ACF v5.0.0
* **Collaboration** - Tweak: update translations
* **Search** - Fix: issue with form submission on Internet Explorer
* **All add-ons** - Fix: remove use of WP_***_URL constants in favor of functions [#315](https://github.com/marvinlabs/customer-area/issues/315)

= 7.5.2 (2018/09/14) =

* Fix: create_function deprecated error [#302](https://github.com/marvinlabs/customer-area/pull/302)
* Fix: updated some untranslated strings
* Fix: wrong admin-bar CUAR menu logs URL

**Add-on changes**

* **Authentication Forms** - Fix: bug in reset password [#311](https://github.com/marvinlabs/customer-area/issues/311)
* **Owner Restrictions** - Fix: hide disabled owner fields when fully restricted [#308](https://github.com/marvinlabs/customer-area/issues/308)

= 7.5.1 (2018/06/14) =

* Fix: Stripe checkout not working when a single gateway is enabled

**Add-on changes**

* **Terms of Service** - Fix: Update add-on ID to let licences work
* **Conversations** - Fix: Updated conversation-editor-replies-add-form template to allow image AJAX posting in replies
* **Notifications** - Tweak: Notifications for new content created will now be sent to the current connected user saving the post. Use filter 'cuar/notifications/allow-notification-to-self' to disable this.

= 7.5.0 (2018/04/24) =

* Fix: redirection of term archive pages [#297](https://github.com/marvinlabs/customer-area/issues/297)
* Fix: user groups and addresses select fields not rendering properly [#299](https://github.com/marvinlabs/customer-area/issues/299)
* Fix: save button ready too early [#291](https://github.com/marvinlabs/customer-area/issues/291)
* Fix: some clearfix were not properly working [#304](https://github.com/marvinlabs/customer-area/issues/304)
* New: New filter to allow to edit display names in selection fields 'cuar/core/ownership/owner-display-name?owner-type=usr'

**Add-on changes**

* **Terms of Service** - Brand new addon: force users to accept your TOS when registering or logging in!
* **Protect Post Types** - New: add new filter 'cuar/protected-post-types/default-protection-checkbox-status' for default protection checkbox status
* **Authentication Forms** - Tweak: better style for login remember me checkbox
* **Authentication Forms** - Tweak: Compatibility with our new Terms Of Service add-on
* **Authentication Forms** - Fix: redirection of term archive pages [#297](https://github.com/marvinlabs/customer-area/issues/297)
* **Design Extras** - Tweak: Support for new styles from WP Customer Area 7.5.0
* **Conversations** - Fix: redirection of term archive pages [#297](https://github.com/marvinlabs/customer-area/issues/297)
* **Projects** - New: Ajaxify project team members selection
* **Projects** - New: New filter to allow to edit display names in selection fields 'cuar/core/ownership/owner-display-name?owner-type=prj'
* **Switch Users** - New: Ajaxify switch users select box
* **Tasks** - Fix: redirection of term archive pages [#297](https://github.com/marvinlabs/customer-area/issues/297)
* **Additional owner types** - New: New filter to allow to edit display names in selection fields 'cuar/core/ownership/owner-display-name?owner-type={rol|grp|glo}'
* **Additional owner types** - New: Ajaxify owners select boxes
* **Managed Groups** - New: New filter to allow to edit display names in selection fields 'cuar/core/ownership/owner-display-name?owner-type=mgrp'
* **Managed Groups** - New: Ajaxify managed groups select boxes
* **Invoicing** - Fix: Incorrect total amount were sent to Paypal
* **Owner Restrictions** - Fix: Owner restrictions compatibility
* **Smart Groups** - New: New filter to allow to edit display names in selection fields 'cuar/core/ownership/owner-display-name?owner-type=sgrp'
* **Smart Groups** - New: Ajaxify smart groups select boxes

= 7.4.6 (2018/01/23) =

* New: Uploading an image through the rich editor now send it to the server through Ajax in the uploads folder
* New: Frontend rich editor now has a new button to allow image editing after clicking on it
* Tweak: graphics adjustments on frontend rich editor such as paddings and more
* Fix: frontend rich editor were not properly usable on fullscreen mode due to popups not showing up
* Fix: uploading an image now set the image max-width as 100% by default
* Fix: notice: Undefined index: force-download when trying to download an inline file
* Fix: some links were showing up in avatar menu without checking for capabilities
* Fix: notice: $is_locked not defined

* **Projects** - Fix: Notice: Trying to get property of non-object in core-classes/Content/list-table.class.php
* **Design Extras** - Tweak: Support for new styles from 7.4.6 version

= 7.4.5 (2018/01/09) =

* Tweak: add IDs to fields groups so we can manipulate them via CSS
* Fix: licence warning were ouput too early and could generate a "headers already sent" error

**Add-on changes**

* **Projects** - Fix: bug for new project notifications
* **Notifications** - Fix: some notifications where not properly sent
* **Notifications** - Fix: infinite recursion when notifying of a new project
* **Collaboration** - Tweak: hide the owner tab while creating content from front-office if user role is allowed to select one
* **Conversations** - Tweak: hide the owner tab while creating content from front-office if user role is allowed to select one
* **Tasks** - Tweak: hide the owner tab while creating content from front-office if user role is allowed to select one
* **Stripe Gateway** - Fix: undefined index licence key error
* **PayPal Gateway** - Fix: undefined index licence key error

= 7.4.4 (2017/11/24) =

* Fix: support for Protect Post Types version 2.0.3
* Fix: improve some CSS styles for Protect Post Types owner fields and checkbox

**Add-on changes**

* **Protect Post Types** - Fix: missing CSS styles in protected CPT admin pages
* **Protect Post Types** - Fix: "enable content access restriction with WP Customer Area" checkbox should hide/show the fields

= 7.4.3 (2017/11/22) =

* Fix: minor security fix on a XSS vulnerability on some admin pages

= 7.4.2 (2017/11/20) =

* New: checked compatibility with WordPress 4.9
* Fix: bug on the [Appearance > Menus page](https://github.com/marvinlabs/customer-area/issues/278)

= 7.4.1 (2017/11/07) =

* Fix: Missing cuar-clearfix CSS class
* Fix: Conversations add-on requirement updated from 4.1.1 to 4.2.0

= 7.4.0 (2017/10/11) =

* New: compatibility with the new paypal and stripe gateways
* Fix: CSS for admin tabs in settings
* Fix: website profile field not saved properly
* Fix: hook to change default collection display mode was not working
* Fix: warnings shown by a WP function when recreating the WP Customer Area menu after deleting/re-creating all pages
* Fix: update the [flush rewrite rules automatically after slug updates](https://github.com/marvinlabs/customer-area/issues/271)
* Fix: badges and labels hover colors
* Fix: wizard better responsive mode

**Add-on changes**

* **ACF Integration** - New: updated supported version of ACF to 4.4.12.1
* **Collaboration** - Fix: switch clearfix CSS class to cuar-clearfix to avoid conflicts
* **Conversations** - New: the conversation author can decide to close the conversation to new replies
* **Conversations** - Fix: switch clearfix CSS class to cuar-clearfix to avoid conflicts
* **Design Extras** - Tweak: recompile styles to match WP Customer Area 7.4.0
* **Invoicing** - New: compatibility with the new online payment gateways
* **Invoicing** - Fix: meta data was lost when [trashing/restoring an invoice](https://github.com/marvinlabs/customer-area/issues/262)
* **Invoicing** - Fix: the notice field was always logged as updated even when not touched
* **Invoicing** - Fix: switch clearfix CSS class to cuar-clearfix to avoid conflicts
* **Login Form** - Fix: switch clearfix CSS class to cuar-clearfix to avoid conflicts
* **Notifications** - New: Log notifications that get sent
* **Notifications** - Fix: Allow by default sending notifications to current user. The filter 'cuar/notifications/allow-notification-to-self' can be used to change that.
* **Notifications** - Fix: switch clearfix CSS class to cuar-clearfix to avoid conflicts
* **Owner Restrictions** - Fix: switch clearfix CSS class to cuar-clearfix to avoid conflicts
* **Projects** - Fix: switch clearfix CSS class to cuar-clearfix to avoid conflicts
* **Projects** - Fix: do not execute project updated hook twice
* **Protect Post Types** - Fix: typos about owner names in admin interface
* **Search** - Fix: switch clearfix CSS class to cuar-clearfix to avoid conflicts
* **Tasks** - Fix: switch clearfix CSS class to cuar-clearfix to avoid conflicts

= 7.3.0 (2017/06/29) =

* New: compatibility with the new add-on: Design extras
* Fix: improved some resets CSS rules
* Fix: default message in recent pages widget not saved
* Fix: wrong hook for the owner type hooks
* Fix: some tooltips were not displayed outside of screen on mobile view
* Fix: show determinate [progress on file uploads](https://github.com/marvinlabs/customer-area/issues/116)
* Fix: country dropdown on profile [address tab were not working](https://github.com/marvinlabs/customer-area/issues/254)
* Fix: removed [extra container on search results page](https://github.com/marvinlabs/customer-area/issues/259)
* Fix: too long category [names in widgets are now displayed properly](https://github.com/marvinlabs/customer-area/issues/250)
* Fix: font awesome icons were not displayed properly because [shown up in italic](https://github.com/marvinlabs/customer-area/issues/256)
* Tweak: some internal improvements to settings (color setting, etc.)
* Tweak: improve longs owner names rendering

**Add-on changes**

* **Conversations** - Tweak: improve longs owner names rendering
* **Design Extras** - New: 13 color variations for the frontend skin
* **Design Extras** - New: 4 PDF templates for invoices
* **Design Extras** - New: 4 templates for email notifications
* **Extended Permissions** - Fix: wrong hook for the owner type hooks
* **Extended Permissions** - Fix: admin area menu highlighting
* **Invoicing** - New: PDF templates can now be configured (colors, images, etc.)
* **Invoicing** - Fix: Allow lists to be used in [notice, header, and footer areas](https://github.com/marvinlabs/customer-area/issues/257)
* **Invoicing** - Tweak: Improved the default HTML template for displaying invoices
* **Invoicing** - Tweak: Invoice HTML tpl should include [sub-total](https://github.com/marvinlabs/customer-area/issues/252)
* **Invoicing** - Tweak: improve longs owner names rendering
* **Managed Groups** - Tweak: improve longs owner names rendering
* **Managed Groups** - Fix: admin area menu highlighting
* **Notifications** - New: email layouts can now be configured (colors, logo, etc.)
* **Notifications** - New: Notification when task list gets completed
* **Notifications** - Tweak: Enhanced the default notification template
* **Projects** - Fix: wrong hook for the owner type hooks
* **Projects** - Tweak: task list update hook parameters
* **Projects** - Tweak: improve longs owner names rendering
* **Smart Groups** - Tweak: improve longs owner names rendering
* **Smart Groups** - Fix: admin area menu highlighting
* **Tasks** - Fix: wrong version sent to the update server
* **Tasks** - Fix: remove unused code
* **Tasks** - Fix: better hook to allow task list completed notifications
* **Tasks** - Tweak: improve longs owner names rendering

= 7.2.0 (2017/05/15) =

* New: compatibility with the new add-on: Unread documents
* Fix: extend neutral color palette
* Fix: dark skin should have a really dark panel heading
* Fix: smooth dropdown animations
* Fix: sidebar mobile and desktop transition
* Fix: sidebar scroll fixes
* Fix: wrong collection list layout on large screens
* Fix: Improve collection thumbnails ratios
* Fix: bug for file download notification mode (first time only)
* Tweak: Improve listing of installed addons in status screen

**Add-on changes**

* **Additional Owner Types** - Fix: notifications where not sent when assigning content to "any registered user"
* **Conversations** - Fix: compatibility with the new unread documents add-on
* **Invoicing** - Fix: compatibility with the new unread documents add-on
* **Notifications** - New: Add recipient setting for file downloaded notification
* **Notifications** - New: Notification when tasks are about to be overdue
* **Notifications** - New: Notification when tasks are overdue
* **Notifications** - Fix: Fix bug for file download notification mode (first time only)
* **Projects** - Fix: compatibility with the new unread documents add-on
* **Switch Users** - Fix: scripts where sometimes not loaded in the right order
* **Tasks** - New: notifications get sent automatically when tasks are about to expire (requires the notifications add-on)
* **Tasks** - Fix: compatibility with the new unread documents add-on
* **Tasks** - Fix: due date was not being shown properly when changed (JS bug)
* **Unread documents** - First plugin release
* **Unread documents** - Let the user know which documents have not been read yet
* **Unread documents** - Let the user mark documents as unread

= 7.1.9 (2017/04/14) =

* Fix: allow to bypass SSL for license validation (work around some bad cURL configurations/builds)

= 7.1.8 (2017/04/11) =

* Fix: rich editor on frontend given by the collaboration add-on was not properly initialized

= 7.1.7 (2017/04/07) =

* Fix: missing padding on the right of the sidebar
* Fix: main.js master-skin script could crash because of a missing jquery.cookie lib
* Fix: rewrote main.js master-skin dependencies inclusion to make sure everything gets loaded before running the script

= 7.1.6 (2017/04/05) =

* Fix: pages content listing were not loading properly or were showing a blank page instead
* Fix: added some missing reset property that should prevent some themes to crash on single private pages
* Fix: wrong alignment of the carousel on single private pages when showing 3 items on large screens
* Fix: Higher priority on body_class filters [#239](https://github.com/marvinlabs/customer-area/issues/239)
* Fix: Download all button not working properly on some file types with the enhanced files addon button
* Fix: better error messages when license does not validate

= 7.1.5 (2017/02/07) =

* Fix: default collection view type can now be overridden with a hook
* Fix: some javascript errors preventing proper license validation in the settings page on some servers

= 7.1.4 (2017/02/02) =

* Fix: slow plugin page display when licenses were active + several improvements to the license checks

= 7.1.3 (2017/01/31) =

* Fix: problem with scripts being loaded twice
* Fix: rich editor was not showing up in frontend. Causing issues when replying to conversations for instance.

= 7.1.2 (2017/01/30) =

* Fix: saving updated content when forbidding to pick an owner (using front-office addon)
* Fix: when user was not allowed to select an owner, message "You are not allowed to select an owner" was not shown, just an icon was there

= 7.1.0 (2016/09/15) =

* New: the plugin now checks if your add-ons are compatible with the installed version. You need to activate/deactivate for that
* New: added a button to download all attachments at once from the private file details page
* Fix: various tweaks and fixes on the master skin
* Fix: a function to programmatically create private pages was not working since 7.0
* Fix: some theme previews from admin where not working properly (http://wp-customerarea.com/support/topic/divi-preview-error/)

**Add-on changes**

* **ACF Integration** - New: updated supported version of ACF to 4.4.8
* **Invoicing** - New: added ability to download an invoice as a PDF file
* **Invoicing** - New: added a setting to pad the invoice number with leading zeros
* **Conversations** - Fix: notifications not sent when a conversation was started
* **Authentication forms** - Fix: email field on register page not populated properly
* **Notifications** - Fix: added a custom message for conversation started notification


= 7.0.8 (2016/06/30) =

* Fix: several issues with the sidebar in Firefox and Internet Explorer
* Fix: rich editor popovers not shown properly
* Fix: dates where sometimes not properly internationalized
* Fix: some minor style fixes here and there

= 7.0.7 (2016/06/28) =

* Fix: redirection to the wrong page after deleting or updating content from the front end

= 7.0.6 (2016/06/27) =

* Fix: Problems in front-office publishing when a select2 field was close to the bottom of the wizard form

= 7.0.5 (2016/06/23) =

* Fix: ACF fields where showing up on all wizard panels
* Fix: Better ACF field templates on single pages
* Fix: Responsive mode on collection layout
* Fix: Select2 dropdowns not displaying correctly
* Fix: Update some outdated libs (fancytree, cookies, bootstrap-slider)
* Fix: WP Customer Area menu is printed on external widgets sometimes
* Fix: Loading indicator not shown in the file attachments manager (frontend only)

= 7.0.4 (2016/06/17) =

* Fix: crash when deleting file or user on private files which had been created with WP Customer Area 6.1 or older

= 7.0.3 (2016/06/10) =

* Fix: some minor style fixes

= 7.0.2 (2016/06/09) =

* Fix: dashboard panels not hidden when option is unchecked
* Fix: notifications not sent when owner is an individual user

= 7.0.1 (2016/06/08) =

* Fix: Styles were applied outside of the WP Customer Area components on some themes.

= 7.0.0 (2016/06/07) =

* New: Brand new frontend skin. Greater UI and that should fix most compatibility problems with themes. **REQUIRES UPDATING ALL YOUR ADD-ONS!**
* New: You can now publish content for multiple owner types at once. E.g. for user "admin" as well as for user group "the incredible team".
* New: Added a title parameter to the private content shortcode
* New: Add filter to change the dashboard block order ('cuar/core/page/dashboard-block-priority')
* Fix: Projects were not listed by the protected content shortcode (https://github.com/marvinlabs/customer-area/issues/204)
* Fix: Global post data not restored after secondary queries (https://github.com/marvinlabs/customer-area/issues/205)
* Fix: Translations where not loaded early enough
* Fix: Fix widget class and ID not replaced (https://github.com/marvinlabs/customer-area/issues/123)
* Fix: When using our shortcodes, some pages could be detected as "orphan WP Customer Area pages" by mistake

**Add-on changes (apart from new UI and compatibility for WP Customer Area 7.x)**

* New: *Additional owner types* - allow publishing content easily for any registered user with new global rule
* New: *Conversations* - Threw in some Ajax goodness for handling replies (add, delete)
* New: *Conversations* - Replies can now be deleted
* New: *Conversations* - Log addition and deletion of replies
* New: *Authentication forms* - Compatibility with the notifications add-on to customize the emails sent on registration/password reset
* New: *Authentication forms* - Clear text password is not sent in the registration email anymore for better security (like with WordPress 4.3+)
* New: *Notifications* - You can now have email templates which look great with logo & all HTML goodness
* New: *Notifications* - Custom emails sent on registration/password reset when used with the authentication forms add-on
* New: *Notifications* - Custom emails sent on comment & comment moderation for private post types
* New: *Notifications* - Rewrote the code to send the notifications, now the hooks are easier to understand and should allow more flexibility
* New: *Notifications* - Individual notifications for each type of private content creation for better control
* New: *Projects* - compatibility with the notifications add-on (new project notification)
* New: *Switch users* - Themes can now declare support for the switch toolbar and place it wherever they want
* Fix: *Conversations* - Replies' HTML code sometimes not inserted properly (gets transformed to plain text)
* Fix: *Front-office publishing* - Hooks for each post type have been simplified to a single hook
* Fix: *Notifications* - Compatibility with the projects add-on (new project notification)
* Fix: *Notifications* - Compatibility with the tasks add-on (new task list notification from frontend)
* Fix: *Notifications* - Cleaner options page
* Fix: *Owner restrictions* - Better settings panel
* Fix: *Protect post types* - Bug in loading translations
* Fix: *Smart groups* - bug when showing the UI in another language
* Fix: *Switch users* - bug on addresses shown on the account page.

= 6.3.0 (2015/11/26) =

* New: Changes for better WordPress 4.4 support
* New: Add addresses (home, billing) to the user profile - one step towards better CRM integrated into WP Customer Area
* New: Allow overriding the action (download/view) on a per file basis using hooks (or via settings in the Enhanced Files addon)
* New: Add attachment details to log event for download/view
* New: Add a log event for successful user login
* Fix: Compatibility problem between the Projects addon and the Protect Post Types addon (https://github.com/marvinlabs/customer-area/issues/164)
* Fix: A bug was not showing the proper owner on the content edition page when using the Front-office publishing add-on
* Fix: Do not mark create/update pages as current in the navigation menu
* Fix: Fix delete button not working in Front-office publishing add-on (https://github.com/marvinlabs/customer-area/issues/173)
* Fix: Trashed content was shown in the admin-side listing pages
* Fix: Function to create files from code was not working since 6.2

= 6.2.2 (2015/09/21) =

* Fix: Fix "don't have access to this page" for WPCA menus
* Fix: CSS glitch on file badges

= 6.2.1 (2015/09/09) =

* Fix: the add_meta_box function was not called within the proper callback

= 6.2.0 (2015/09/09) =

* New: private files attachment interface has been improved drastically with AJAX and drag'n'drop
* New: private files storage folder can now be set with a setting
* New: support for the Smart groups add-on
* New: function to test if we are currently on a given WP Customer Area page (for theme developers)
* New: the private files settings page now helps you to secure the storage and FTP folders
* New: allow tagging menu items to hide/show them [#117](https://github.com/marvinlabs/customer-area/issues/117)
* Fix: updated all template functions for files to be able to pass the file index as a parameter
* Fix: permalinks to categories (private files or pages) were not correct when included as menu items
* Fix: bug in admin access restriction
* Fix: most 404 problems happening because of SSL should now be fixed [#145](https://github.com/marvinlabs/customer-area/issues/145)
* Fix: log advanced filters not working properly on event type [#147](https://github.com/marvinlabs/customer-area/issues/147)

**Add-on changes**

* New: *Authentication forms* - Hooks to change the form links (see [code snippet](http://wp-customerarea.com/snippet/authentication-forms-change-the-links-below-the-forms/))
* New: *Authentication forms* - Allow users to login using their email address too
* New: *Enhanced Files* - first add-on release
* New: *Smart groups* - first add-on release

= 6.1.2 (2015/05/07) =

* Fix: Some PHP versions did not like a method visibility change in the list table classes

= 6.1.1 (2015/05/06) =

* Fix: A small bug preventing to select category in the front-office publishing add-on if no content had previously been assigned to any category
* Fix: The admin bar was not hidden anymore to users who should not be able to see it

= 6.1.0 (2015/05/06) =

* New: important events are now logged: content viewed, file download, owner changed. Many more event types to come soon!
* New: filter the private content lists to see only the content visible by a given user
* New: hook to change the default owner type (See [our corresponding code snippet](http://wp-customerarea.com/snippet/changing-default-owner-type/))
* New: improved the administration panel. The menu has been made much clearer. Added advanced filters for private content.
* New: translation files can now be stored outside the plugin folder
* New: added a shortcode to display the navigation menu (see [the shortcodes documentation](http://wp-customerarea.com/documentation/shortcodes/))
* New: added a shortcode to list protected content (see [the shortcodes documentation](http://wp-customerarea.com/documentation/shortcodes/))
* New: hook to change the name shown in the owner selection box (see [corresponding code snippet](http://wp-customerarea.com/snippet/change-the-name-displayed-in-user-selection-box/))
* Fix: pagination is now filtered if there are too many ages to show
* Fix: hide customer area pages in search results
* Fix: admin bar was shown to guests when admin restriction setting was not enabled
* Fix: PHP error in the front-end publishing module

**Add-on changes**

* New: *Authentication forms* - now plays nice with Peters' login redirect plugin for even more control
* New: *Projects* - project property updates are now logged (progress, dates, etc.)
* New: *Protect post types* - brand new add-on to protect external custom post types (from other plugins)
* New: *ACF Integration* - updated supported version of ACF to 4.4.1
* Fix: *Front-office publishing* - the "page created" message was displayed twice
* Fix: *Conversations* - fix PHP error in template file to list replies
* Fix: *Managed groups* - PHP warning in the group edition page and support for WPCA 6.1
* Fix: *Additional Owner Types* - support for WPCA 6.1
* Fix: *Tasks* - support for WPCA 6.1

= 6.0.0 (2015/03/10) =

* New: a setup assistant for first time installation & about page to introduce new versions features
* New: support for the new website at http://wp-customerarea.com
* New: permissions to protect the account pages (view/edit account from frontend)
* New: improved the permissions settings page
* New: new permission to restrict access to the account pages (view account details / edit account details)
* New: new permission to show/hide the WordPress admin bar from the front-end for some roles
* New: new permission to restrict access to the WordPress admin area for some roles
* Fix: bug that was showing the comments related to the last private content on the dashboard page
* Fix: the function cuar_is_customer_area_private_content was not returning true for private containers (such as projects)
* Fix: an email validation bug for additional profile fields created by code
* Fix: improved detection of main plugin errors (not installed, disabled) for add-ons.
* Fix: the widgets have been made more flexible to be able to override the output by using templates
* Fix: the term list widget was not properly printing child terms

= 5.0.7 (2014/07/31) =

* Fix: bug on the settings page on Windows IIS servers
* Fix: updated Dutch translations - thanks to [Peter Massar](http://profiles.wordpress.org/yourdigihands/)
* Fix: a few minor UI adjustments

= 5.0.6 (2014/07/04) =

* Fix: some settings on some tabs were not saved properly

= 5.0.5 (2014/07/03) =

* Fix: bug preventing from saving the settings on some tabs (resulting in either error 404, 500, ...)

= 5.0.4 (2014/06/25) =

* Fix: some responsive adjustments to the default-v4 frontend theme

= 5.0.3 (2014/06/24) =

* Fix: admin styles for the Tasks add-on

= 5.0.2 (2014/06/17) =

* New: compatibility with the new Projects add-on (to manage projects and/or organize private content by project rather than by type)
* New: private content lists are now paginated. This speeds up page loading when users have a lot of private content.
* New: widget to show all authors of the private content you own. Can be used to see which files you have created for example.
* New: menu in the admin bar to bring you directly to the page you are looking for
* New: tool to help find & delete outdated customer pages (see Customer Area > Status > Pages)
* New: theme developers now have some tools to disable some settings if their theme supports styling and/or external libraries
* New: adding a filter to change the base storage directory for private data (files for instance): 'cuar/core/ownership/base-private-storage-directory'
* Fix: a server error 500 occuring when another plugin also uses the Easy Digital Downloads licensing system 
* Fix: Changed item templates to be compatible with the updated Customer Area 4 theme (better responsiveness) 
* Fix: javascript error in the private files and private pages settings
* Fix: avoid defining the wp_bootstrap_navwalker class multiple times if other themes/plugins are already using it
* Fix: normalized a lot of action and filter names (big clean-up on that side to make it more organized and try to make it more understandable) 
* Fix: some improvements on the default-v4 skin (credits go to [Thomas Lartaud](http://www.thomaslartaud.com/))

= 4.6.0 (2014/04/17) =

* New: compatibility with the new ACF Integration add-on (to add custom fields to the user accounts and/or private content)
* New: users can now edit their account information (email, password)
* New: Add a parameter to the category widgets to hide/show empty categories
* Fix: Updated the Italian translation. Credits go to [Antonio Cicirelli](http://www.ideacommerce.it).
* Fix: months where not properly localized in the archive titles and widgets

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
