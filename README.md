Codeigniter Authorize.NET AIM Integration
=========================================

A quick way to add Authorize.NET AIM integration with just 3 lines of code.

Requires [Philip Sturgeon's](http://philsturgeon.co.uk/) [cURL lib](http://getsparks.org/packages/curl/show) (included).

Installation
------------

1. Copy the /application/config/authorize_net.php file into your application's config/ folder and make sure to change the values!
2. Copy /application/libraries/Authorize_net.php and /application/libraries/Curl.php into your application's libraries/ folder.

Usage
-----

You can do an authorize and capture on a card in just three lines from your controller:

	$this->load->library('authorize_net');
	$this->authorize_net->setData($credit_card_data);
	$this->authorize_net->authorizeAndCapture();

For a full example with more values and error handling, check out /application/controllers/example.php

Enjoy!
[SammyK](http://sammyk.me/)
