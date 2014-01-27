<?php

App::uses('CommonTestCase', 'Common.TestSuite');
App::uses('Controller', 'Controller');
App::uses('HoneyPotHelper', 'Security.View/Helper');

class HoneyPotHelperTestController extends Controller {

	public $helpers = array('Security.HoneyPot' => '/sample.php');

}

class HoneyPotHelperTest extends CommonTestCase {

	public function setUp() {
		$Controller = new HoneyPotHelperTestController(new CakeRequest, new CakeResponse);
		$View = $this->getMock('View', null, array($Controller));
		$this->HoneyPot = new HoneyPotHelper($View, $Controller->helpers['Security.HoneyPot']);
	}

	public function tearDown() {
		unset($this->HoneyPot);
	}

	public function testRender() {
		$cnt = count($this->HoneyPot->settings['links']);
		for ($i = 0; $i <= $cnt + 1; $i++) {
			$html = $this->HoneyPot->render($i);

			$result = substr_count($html, '/sample.php');
			$this->assertEqual($result, $cnt >= $i ? $i : ($i - 1));
		}

		$this->HoneyPot->settings['honeyPot'] = null;
		$this->assertEmpty($this->HoneyPot->render());
	}

}
