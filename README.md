# CakePHP Security Plugin

@todo write description

## Install

### Composer package

First, add this plugin as a requirement to your `composer.json`:

	{
		"require": {
			"cakephp/security": "*"
		}
	}

And then update:

	php composer.phar update

That's it! You should now be ready to start configuring your channels.

### Submodule

	$ cd /app
	$ git submodule add git://github.com/gourmet/security.git Plugin/Security

### Clone

	$ cd /app/Plugin
	$ git clone git://github.com/gourmet/security.git

## Configuration

You need to enable the plugin your `app/Config/bootstrap.php` file:

	CakePlugin::load('Security');

If you are already using `CakePlugin::loadAll();`, then this is not necessary.

You will also need to define some `Configure` key/value sets in your `bootstrap.php`,
`config.ini` or `config.json`. Create your own [honey pot](https://www.projecthoneypot.org/manage_honey_pots.php)
or get a [QuickLink](https://www.projecthoneypot.org/manage_quicklink.php) to start
taking part in the [community](https://www.projecthoneypot.org).

### bootstrap.php

	<?php
	Configure::write('Security.HttpBL.apiKey', 'your_api_key');
	Configure::write('Security.HttpBL.honeyPot', '/your_honey_pot.php');

### config.ini

	[Security.HttpBL]
	apiKey = your_api_key
	honeyPot = /your_honey_pot.php

### config.json

	{
		"Security": {
			"HttpBL": {
				"apiKey": "your_api_key",
				"honeyPot": "/your_honey_pot.php"
			}
		}
	}

## Usage

Add honey pots URLs to your layout and/or views after obviously adding the helper to your controller(s):

	<?php
	echo $this->HoneyPot->render();

## Patches & Features

* Fork
* Mod, fix
* Test - this is important, so it's not unintentionally broken
* Commit - do not mess with license, todo, version, etc. (if you do change any, bump them into commits of their own that I can ignore when I pull)
* Pull request - bonus point for topic branches

## Bugs & Feedback

http://github.com/gourmet/security/issues

## License

Copyright 2013, [Jad Bitar](http://jadb.io)

Licensed under [The MIT License](http://www.opensource.org/licenses/mit-license.php)<br/>
Redistributions of files must retain the above copyright notice.
