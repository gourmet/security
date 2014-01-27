<?php

App::uses('SecurityAppModel', 'Security.Model');

class AccessLimit extends SecurityAppModel {

/**
 * {@inheritdoc}
 */
	public $displayField = 'key';

	public function count($aro, $aco, $aroid = null, $acoid = null) {
		$conditions = array_merge(
			$this->__filter(compact('aro', 'aco', 'aroid', 'acoid')),
			array('expires >' => date('Y-m-d H:i:s'))
		);
		return $this->find('count', compact('conditions'));
	}

	public function limit($aro, $aco, $aroid = null, $acoid = null, $limit = 10) {
		return $this->count($aro, $aco, $aroid, $acoid) < $limit;
	}

	public function fail($aro, $aco, $aroid = null, $acoid = null, $duration = '+1 day') {
		$expires = date('Y-m-d H:i:s', strtotime($duration));
		$this->create($this->__filter(compact('expires', 'aro', 'aco', 'aroid', 'acoid')));
		return $this->save();
	}

	public function reset($aro, $aco, $aroid = null, $acoid = null) {
		return $this->deleteAll($this->__filter(compact('aro', 'aco', 'aroid', 'acoid')), false);
	}

	public function cleanup() {
		return $this->deleteAll(array('expires <' => date('Y-m-d H:i:s')), false);
	}

	public function __filter($data) {
		return array_filter($data, function($val) { return !empty($val); });
	}

}
