<?php

App::uses('CommonTestSuite', 'Common.TestSuite');

class AllSecurityPluginTest extends PHPUnit_Framework_TestSuite {

	public static function suite() {
		return CommonTestSuite::allPluginTests();
	}

}
