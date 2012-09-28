<?php
/**
 * Short description for mi_email_controller.php
 *
 * Long description for mi_email_controller.php
 *
 * PHP version 5
 *
 * Copyright (c) 2008, Andy Dawson
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2008, Andy Dawson
 * @link          www.ad7six.com
 * @package       mi_email
 * @subpackage    mi_email.controllers
 * @since         v 1.0
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * EmailsController class
 *
 * @uses          AppController
 * @package       mi_email
 * @subpackage    mi_email.controllers
 */
class MiEmailController extends MiEmailAppController {

/**
 * name property
 *
 * @var string 'Emails'
 * @access public
 */
	public $name = 'MiEmail';

/**
 * postActions property
 *
 * @var array
 * @access public
 */
	public $postActions = array(
		'admin_delete',
		'admin_resend',
		'admin_status',
	);

/**
 * modelClass property
 *
 * @var string 'MiEmail'
 * @access public
 */
	public $modelClass = 'MiEmail';

/**
 * uses property
 *
 * @var array
 * @access public
 */
	public $uses = array('MiEmail.MiEmail');

/**
 * components property
 *
 * @var array
 * @access public
 */
	public $components = array('Email');

/**
 * helpers property
 *
 * @var array
 * @access public
 */
	public $helpers = array('Time');

/**
 * paginate property
 *
 * @var array
 * @access public
 */
	public $paginate = array('order' => 'MiEmail.modified DESC');

/**
 * beforeFilter method
 *
 * Allow "public" access if debug is enabled
 *
 * @access public
 * @return void
 */
	public function beforeFilter() {
		$this->modelClass = 'MiEmail';
		parent::beforeFilter();
		if (!empty($this->params['requested'])) {
			$this->Auth->allow('*');
		} elseif (isset($this->params['admin'])) {
			$this->MiEmail->bindUsers();
			$this->MiEmail->recursive = 0;
		} elseif(isset($this->Auth)) {
			$this->Auth->allow = array('newsletter');
			if (in_array(strtolower($this->action), array('view', 'read'))) {
				$this->Auth->authorize = 'model';
			}
			if ($this->Auth->user('id')) {
				$this->Auth->authError = __d('mi_email', 'Acceso al email desde la web denegado', true);
			} else {
				$this->Auth->authError = __d('mi_email', 'El acceso al email requiere autentificación', true);
			}
			if (isset($this->params['pass'][0])) {
				$this->MiEmail->id = $this->params['pass'][0];
			}
		}
	}

/**
 * admin_edit method
 *
 * @param mixed $id
 * @access public
 * @return void
 */
	public function admin_edit($id) {
		if ($this->data) {
			$file = new File(TMP . rand());
			$file->write('<?php $this->data["MiEmail"]["data"] = ' . $this->data['MiEmail']['data'] . ';');
			include($file->pwd());
			$file->delete();
			if ($this->MiEmail->save($this->data)) {
				return $this->_back();
			}
		}
		parent::admin_edit($id);
		$this->data['MiEmail']['data'] = var_export($this->data['MiEmail']['data'], true);
	}

/**
 * admin_index method
 *
 * @return void
 * @access public
 */
	public function admin_index() {
		if (isset($this->SwissArmy)) {
			$conditions = $this->SwissArmy->parseSearchFilter();
		} else {
			$conditions = array();
		}
		if ($conditions) {
			$this->set('filters', $this->MiEmail->searchFilterFields());
			$this->set('addFilter', true);
		}
		$this->data = $this->paginate($conditions);
		$this->_setSelects();
	}

/**
 * admin_resend method
 *
 * @param mixed $id
 * @access public
 * @return void
 */
	public function admin_resend($id) {
		if ($this->MiEmail->resend($id)) {
			$this->Session->setFlash(sprintf(__d('mi_email', 'El email con identificador %1$s ha sido reenviado', true), $id));
		} else {
			$this->Session->setFlash(__d('mi_email', 'Ha ocurrido un error enviando el email', true));
		}
		return $this->_back();
	}

/**
 * admin_status method
 *
 * @param mixed $id
 * @param mixed $status
 * @return void
 * @access public
 */
	public function admin_status($id, $status) {
		$this->MiEmail->id = $id;
		$data = $this->MiEmail->read(null, $id);
		$this->MiEmail->data['MiEmail']['status'] = $status;
		if ($status == 'pending') {
			$this->MiEmail->data['MiEmail']['data']['spam'] = -1;
		}
		$result = $this->MiEmail->save();
		return $this->_back();
	}

/**
 * admin_text_preview method
 *
 * @param mixed $id
 * @access public
 * @return void
 */
	public function admin_text_preview($id) {
		$this->MiEmail->recursive = -1;
		$data = $this->MiEmail->read(null, $id);
		$this->data = $data['MiEmail']['data'];
		header('Content-type: Text');
		$this->viewPath = 'elements' . DS . 'email' . DS . 'text';
		$this->set('emailData', $data);
		$this->render($data['MiEmail']['template'], 'email' . DS . 'text' . DS . $data['MiEmail']['layout']);
		if ($this->params['isAjax']) {
			$this->output = '<pre>' . $this->output . '</pre>';
		}
		Configure::write('debug', 0);
	}

/**
 * admin_view method
 *
 * @param mixed $id
 * @return void
 * @access public
 */
	public function admin_view($id, $raw = false) {
		$this->data = $this->MiEmail->read(null, $id);
		if(!$this->data) {
			$this->Session->setFlash(__d('mi_email', 'Email inválido', true));
			return $this->_back();
		}
		if ($raw) {
			return $this->render();
		}
		$this->_view($this->data);
	}

/**
 * view method
 *
 * Allow a "view this email on the web" message to be included in emails.
 * Use the id to find the user's email
 * Note that the name of the layout to use is rendered from the NORMAL layout folder in this case
 *
 * @param mixed $id
 * @param mixed $slug
 * @return void
 * @access public
 */
	public function view($id = null, $slug = null) {
		$data = $this->MiEmail->read(null, $id);
		if (!$data) {
			$this->Session->setFlash(__d('mi_email', 'Email no encontrado', true));
			return $this->_back();
		}
		if (!$this->params['isAjax']) {
			$sluggedTitle = $this->MiEmail->slug($data['MiEmail']['subject']);
			if ($slug != $sluggedTitle) {
				return $this->redirect(array($id, $sluggedTitle));
			}
			$this->layout = 'admin_default';
		}
		$this->_view($data);
	}

/**
 * newsletter method
 *
 * @param mixed $id
 * @param mixed $slug
 * @return void
 * @access public
 */
	public function newsletter($id = null, $slug = null) {
		$data = $this->MiEmail->find('first', array('conditions' => array('id' => $id, 'type' => 'newsletter')));
		if (!$data) {
			$this->Session->setFlash(__d('mi_email', 'Newsletter no encontrada', true));
			return $this->_back();
		}
		$sluggedTitle = $this->MiEmail->slug($data['MiEmail']['subject']);
		if ($slug != $sluggedTitle) {
			$this->redirect(array($id, $sluggedTitle));
		}
		$this->set('title_for_layout', $data['MiEmail']['subject']);
		$this->data = $data['MiEmail']['data'];
		$this->viewPath = 'elements' . DS . 'email' . DS . 'html';
		$this->render($data['MiEmail']['template'], $data['MiEmail']['layout']);
	}

/**
 * send method
 *
 * Expected to be called by request action only
 *
 * @return void
 * @access public
 */
	public function send() {
		if (empty($this->params['requested'])) {
			return $this->redirect($this->referer());
		}
		$data = $this->params['data'];
		$keys = array_diff(array_keys($data), array('data'));
		foreach ($keys as $field) {
			$this->Email->$field = $data[$field];
		}
		$this->set('data', $data['data']);
		if (Configure::read() > 2) {
			$this->Email->delivery = 'debug';
		}
		$return = $this->Email->send();
		return $return;
	}

/**
 * Set a view variable with a list of emails' associated users.
 *
 * If $this->data is paginated, set a list of FromUsers and ToUsers.
 *
 * @return void
 * @access protected
 */
	protected function _setSelects() {
		$this->MiEmail->bindUsers();
		$conditions = array();
		$fields = array();
		if ($this->MiEmail->FromUser->hasField('username')) {
			$fields = array('FromUser.username');
		}
		if ($this->data) {
			$from = Set::extract('{n}.MiEmail.from_user_id', $this->data);
			$to = Set::extract('{n}.MiEmail.to_user_id', $this->data);
			$conditions['FromUser.id'] = array_unique(array_merge($from, $to));
		}
		$this->set('users', $this->MiEmail->FromUser->find('list', compact('conditions','fields')));
	}

/**
 * view method
 *
 * @param mixed $data
 * @return void
 * @access protected
 */
	protected function _view($data) {
		$this->set('title_for_layout', $data['MiEmail']['subject']);
		$this->data = $data['MiEmail']['data'];
		$this->viewPath = 'Emails' . DS . 'html';
		$this->set('emailData', $data);
		$this->set('isEmail', 'web');
		if ($this->params['isAjax']) {
			return $this->render($data['MiEmail']['template']);
		}
		if ($this->layout !== 'default') {
			$this->layout =  $data['MiEmail']['layout'];
		}
		$this->render($data['MiEmail']['template']);
	}
}
