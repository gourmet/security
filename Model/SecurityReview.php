<?php
App::uses('AppModel', 'Model');

class SecurityReview extends AppModel {

	public $actsAs = array(
		'Common.Authorizable',
		'Common.Stateable'
	);

	public function add($foreignModel, $foreignKey, $userKey, $options = array()) {
		$Event = new CakeEvent('Model.SecurityReview.beforeAdd', $this);
		list($Event->break, $Event->breakOn) = array(true, false);
		$this->triggerEvent($Event, $this);
		if (false === $Event->result || !$this->validates()) {
			return false;
		}

		$this->create(array('foreign_model' => $foreignModel, 'foreign_key' => $foreignKey, 'user_id' => $userKey));

		$this->triggerEvent('Model.SecurityReview.afterAdd', $this, array('review' => $this->save()));

		return $this->id;
	}

}
