=== CF7 Pipedrive Integration ===
Contributors: lucasbhealy
Tags: cf7, Pipedrive, Pipedrive Deal, Deal, Contact form 7
Requires at least: 3.6
Tested up to: 4.0
Stable tag: 1.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

CF7 Pipedrive Integration is a minimal plugin that creates a Pipedrive Deal using the form information when a Contact form 7 form is successfully submitted. Enter your API key and the forms you want Deals submitted for and you're done.

== Description ==

CF7 Pipedrive Integration is a minimal plugin that creates a Pipedrive Deal using the form information when a Contact form 7 form is successfully submitted. Enter your Pipedrive API key and the forms you want Deals submitted for and you're done.

This plugin makes use of Pipedrive's API by sending contact form data to their service. [Pipedrive](https://www.pipedrive.com) has their Terms of Service available [here](https://www.pipedrive.com/en/terms-of-service).

Tested with Contact Form 7 4.9. Known issues with Contact Form 7 version 4.3 and lower. Possible issues with version older than 4.9.

== Installation ==

**Installation Instruction & Configuration**

1. Download the zip file and extract the contents. Upload the 'cf7-pipedrive' folder to your plugins directory (wp-content/plugins/).
2.Activate the plugin through the 'Plugins' menu in WordPress. 	
3.Go to Contact -> Pipedrive Integration, set your Pipedrive API, and select the Contact Form 7 Forms you want to submit a Deal.
4. That's it!

== Screenshots ==



== Changelog ==


= 1.0 =
* Initial Release. Excitement insues

= 1.1 =
* Included debug mode
* Added Fields Settings to settings page
* Linked settings values to submission values

= 1.1.1 =
* Validate, Clean, and Sanitized POST data
* Moved from CURL to Wordpress http API
* Updated readme to include Pipedrive info and links
* Changed name in documentation

= 1.2.1 =
* Fixed issue with JS not being loaded in admin and dropdowns not displaying
* Added message to my call for emails to check CF7 version before emailing
* Added warning for possible issues with older CF7 versions