=== CF7 Pipedrive Integration ===
Contributors: lucasbhealy
Tags: cf7, Pipedrive, Pipedrive Deal, Deal, Contact form 7
Requires at least: 3.6
Tested up to: 4.9.1
Stable tag: 1.3.1
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
2. Activate the plugin through the 'Plugins' menu in WordPress. 	
3. Go to Contact -> Pipedrive Integration, set your Pipedrive API, and select the Contact Form 7 Forms you want to submit a Deal.
4. Using the tabs at the top of the Contact -> Pipedrive Integration page, select which input fields you want to be associated with each Pipedrive field.
5. That's it.

== Frequently Asked Questions ==

= How do I add Pipedrive fields? Including custom fields? =

There are 3 filters included in the plugin that allow you to alter the Pipedrive fields available. Those filters are:

1. cf7_pipedrive_organization_fields
2. cf7_pipedrive_person_fields
3. cf7_pipedrive_deal_fields

For more information on these filters you can [read more here](http://www.everythinghealy.com/contact-form-7-pipedrive-integraion-wordpress-plugin/).

= What if I want a Pipedrive field to have a static value, not one from an input field? =

While I plan to allow you to submit values that are not Contact Form 7 input values in the future, in the meantime you can use hidden fields. These are essentially fields that you can set the value for, but will not be visible to the user. You can read more about hidden fields for Contact Form 7 on the [official website's tutorial](https://contactform7.com/hidden-field/).

= It's not working. What should I do? =

1. Is your Contact Form 7 plugin up to date?
2. Is our Pipedrive API correct?
3. Are the values being submitted to Pipedrive valid? For example, we can't use characters for a phone number field in Pipedrive.
4. Submit an issue to the [plugin support form](https://wordpress.org/support/plugin/cf7-pipedrive-integration) and I'll get in touch with a solution. 

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

= 1.3 =
* Huge refactor
* Added Organization Support
* Updated Admin Views
* Updated Admin Settings
* Temporarily removed debugging until I clean it up

= 1.3.1 =
* Fixed bug with stage id, and owner ids not being read by pipedrive on form submission