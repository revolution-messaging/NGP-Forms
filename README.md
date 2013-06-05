## NGP Donations

This plugin helps you integrate NGP (NGP VAN) donation, signup, and volunteer forms with your site. You'll need an SSL certificate running on your site if you want to use the donation portion of this plugin.

This plugin is built and maintained by [Revolution Messaging, LLC](http://revolutionmessaging.com) and makes use of the New Media Campaigns' NGP Donations API class.

### Alert!

You should be running your site under an SSL certificate if you utilize this plugin for donations.


###Installation

Go to `Settings -> General` and fill out the "NGP API Key", "Donation Support Phone Line", and "Addt'l Information for Donation Footer" fields.

* "NGP API Key" is how this plugin authenticates with your NGP VAN service. You need to make sure the API Credentials string you get from NGP is for the "CWP" API.
* "NGP Secure URL" is used if you need to specify a different secure URL than the site might have already been configured to use (for instance, your SSL cert is for donate.yourdomain.com, which points at your Wordpress site, but the site also loads under www.yourdomain.com).
* "Check to accept Amex" is a checkbox that, when checked, causes the American Express options for card-type to show up on the front-end.
* "Donation Support Phone Line" is shown with the error message when the contribution gets rejected by the NGP VAN server.
* "Addt'l Information for Donation Footer" might be used for listing things like donation mailing address, donation limits, taxable status of donations, etc.

If you want to use this plugin, including the donation portions, in a dev environment on your local machine, use ".dev" as the TLD (We develop in virtual hosts on our developer machines. Edit /etc/hosts to add a .dev url). When this plugin is live, it forces SSL on any pages with the donation shortcode. Make sure `$_SERVER['HTTPS']` is set, valid and "on".

### Usage

Place this short tag on the appropriate page or article:

`[ngp_show_donation]`

You can set custom amounts for the donation amount in two ways:

1. Put the amounts in the embed tag: `[ngp_show_donation amounts="50,250,1000"]`
2. Put the amounts in a GET querystring: `http://mycamapign.com/donation?amounts=50,250,1000`

If you want to have a default donation amounts, put them in the embed tag and then override it with a querystring amounts when you need to.

You can source an article in two ways:

1. Put source in the embed tag: `[ngp_show_form source="hard-hitting-ad"]`
2. Put the source in a GET querystring: `http://mycamapign.com/donation?source=hard-hitting-ad`

If you want to have a default donation source, put it in the embed tag and then override it with a querystring source when you need to.

You can set custom thanks URL for the donation process by putting the url in the embed tag:

`[ngp_show_donation thanks_url="/thanks-for-your-donation"]`

The donations thanks URL defaults to: `/thank-you-for-your-contribution`

`[ngp_show_volunteer]`

You can set custom thanks URL for the donation process by putting the url in the embed tag:

`[ngp_show_volunteer thanks_url="/thanks-for-your-work-pledge"]`

The volunteer thanks URL defaults to: `/thank-you-for-volunteering`

`[ngp_show_signup]`

You can set custom thanks URL for the email signup process by putting the url in the embed tag:

`[ngp_show_signup thanks_url="/thanks-for-signing-up-for-incessant-emails"]`

The email signup thanks URL defaults to: `/thank-you-for-signing-up`

### Donation Suggested jQuery

We use the following on our donation pages to make sure that the user understands that the radio buttons and the input field are for the same thing. If the user doesn't support javascript and the custom field holds a value, it always overrides whatever's selected in the radio buttons.

	$('.ngp_custom_dollar_amt').keyup(function() {
		if($(this).val()!='') { $('.Amount').attr('checked', false); }
	});
	$('.Amount').mouseup(function() {
		$('.ngp_custom_dollar_amt').val('');
	});

### Changelog

* 1.1
	* COO API works for signups.
* 1.0
	* First version. Migrated over NGP Donations plugin. Added Volunteer and Signup options.