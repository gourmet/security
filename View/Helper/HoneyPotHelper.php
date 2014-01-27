<?php

App::uses('String', 'Utility');

class HoneyPotHelper extends AppHelper {

/**
 * HoneyPot (or QuickLink) URL and hidden links templates.
 *
 * @var string
 * @see https://www.projecthoneypot.org/manage_honey_pots.php
 * @see https://www.projecthoneypot.org/manage_quicklink.php
 */
	public $settings = array(
		'honeyPot' => null,
		'links' => array(
			'<a href=":lnk"></a>',
			'<!-- <a href=":lnk">:txt</a> -->',
			'<a href=":lnk"><!-- :txt --></a>',
			'<a href=":lnk" style="display:none">:txt</a>',
			'<a href=":lnk"><span style="display: none;">:txt</span></a>',
			'<a href=":lnk"><div style="height: 0px; width: 0px;"></div></a>',
			'<div style="display:none"><a href=":lnk">:txt</a></div>',
			'<div style="position: absolute; top: -250px; left: -250px;"><a href=":lnk">:txt</a></div>'
		)
	);

	public function __construct(View $View, $settings = array()) {
		if (is_string($settings)) {
			$settings = array('honeyPot' => $settings);
		}
		parent::__construct($View, $settings);
	}

/**
 * Render one (or more) hidden links.
 *
 * @param integer $num Number of random and unique links to create.
 * @return string Hidden links HTML.
 */
	public function render($num = 1) {
		if (!$lnk = Common::read('Security.HttpBL.honeyPot', $this->settings['honeyPot'])) {
			return;
		}

		$all = $this->settings['links'];
		$len = rand(4, 16);
		$min = array(48, 65, 97);
		$max = array(57, 90, 122);
		$txt = '';
		$res = array();

		while (strlen($txt) < $len) {
			$rnd = rand(0, 2);
			$txt .= chr(rand($min[$rnd], $max[$rnd]));
		}

		if ($num > count($all)) {
			$num = count($all);
		}

		if (0 == $num) {
			return;
		}

		$rnd = array_rand($all, $num);
		foreach ((array) $rnd as $key) {
			$res[] = String::insert($all[$key], compact('lnk', 'str'));
		}

		return implode(' ', $res);
	}

}
