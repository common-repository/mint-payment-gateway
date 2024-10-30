=== Mint Payment Plugin ===
Contributors: mnwinfra
Tags: woocommerce,payment gateway,credit card
Requires at least: 5.0
Tested up to: 6.4.3
Requires PHP: 5.6
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Accept Credit Card Payment from your WooCommerce shop using Mint Payment Gateway plugin

== Description ==



== How to Use ==

**Mint HPP Gateway**
This uses  Mint’s HPP form to capture credit card details. This is a safer option for those who want to integrate without worrying about PCI compliance.

*Configuration*
From WooCommerce > Settings > Payments tab , Click the manage button to set the configuration for the Mint HPP gateway.

Fields:
*Test Mode: used to toggle test and prod environments
*Test Token: Test company token
*Prod Token: Prod company token
*Currency: currency of the transaction

*Creating a Purchase*
1. in creating a purchase / card payment, the user will go through the standard checkout process of WooCommerce and use the “Mint HPP” checkout option.
2. The user will be redirected to the HPP page and capture credit card details.
3. Once done, you will be redirected back to the WooCommerce default “Order Received” page.

**Mint API Gateway**
The Mint API Payment Form is an inline form that captures credit card data directly from the shop’s checkout page and send it to Mint’s mPay RESTful API. This will give users fully customizable UI/UX.
Note: The owner of the shop needs to make sure to never store credit card details not CVV to be PCI DSS complaint. For more info visit [PCI-DSS Compliance and WooCommerce - WooCommerce Docs](https://docs.woocommerce.com/document/pci-dss-compliance-and-woocommerce/ \"Your favorite software\")

*Configuration*
From WooCommerce > Settings > Payments tab , Click the manage button to set the configuration for the Mint API Gateway.

Fields:
*Test Mode: used to toggle test and prod environments
*Test API Key: Test API Key to Access the MPay API
*Prod API Key: Test API Key to Access the MPay API
*Test Token: Test company token
*Prod Token: Prod company token



== Installation ==
It is assumed that the site owner is using WooCommerce for its online shop

There are 2 ways to install the plugin

1. Direct Plugin Install From the Marketplace (Preferred Option)
You can search the Mint Payments plugin directly from the wp-admin Plugins page By browsing wp-admin > Plugins > Add New  and search the plugin name “WooCommerce Mint Payment Gateway” and click Install Now

2.Manual Plugin Install From the Marketplace
 The Mint WooCommerce plugin is available at the Wordpress plugins repository, You can find it by searching “WooCommerce Mint Payment Gateway” plugin,  download the .zip file from the plugins repository and upload it directly to your website dashboard.

1. Login to your WordPress wp-admin dashboard
2. Browse Plugins > Add New
3. Click Upload Plugin
4. Choose the plugin zip file
5. Click \'Install Now\'

Once installed, you can verify the installation by browsing WooCommerce > Settings > Payments tab on your admin dashboard. There should be 2 payment gateways that are registered under WooCommerce:

*Mint API
*Mint HPP

== Frequently Asked Questions ==
=1. How can I add WooCommerce Mint Payment Gateway plugin in my store?=
WooCommerce Mint Payment Gateway plugin can be installed from wordpress plugin store. The installation guide can be viewed from "(hyperlink with access to plugin installation guide)"

=2. How to accept payments using WooCommerce Mint Payment Gateway=
hyperlink for the payment setup document link guide you through the payment setup process

=3. Which currencies and countried are accepted?=
Currently we accept Australia, New Zealand and Singapore currencies. We also accept Domestic and International cards with Visa and Master card by default and Amex card transactions can be enabled from the Mint Portals.

=4. What are the terms and conditions for using the Mint Payment System?=
By using Mint payment system Customer is bound to "Terms and Conditions of Mint(This should be a hyperlink)"

=5. What are the pricing involved for using WooCommerce Mint Payment Gateway?=
Installation of WooCommerce Mint Payment Gateway  is free of cost and for every transaction will charge the below

== Screenshots ==

== Changelog ==
= 1.0.1 =
* Bugfix: Update HPP callback function to process transactions where order was no longer in WooCommerce session

= 1.0.0 =
* Initial release.
