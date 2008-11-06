<?php
class MoviesController extends AppController {

	var $name = 'Movies';
	var $helpers = array('Html', 'Form', 'Media.Medium', 'Number');
	
	function index() {
		$this->Movie->recursive = 0;
		$this->set('movies', $this->paginate());
	}

	function view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid Movie.', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->set('movie', $this->Movie->read(null, $id));
	}

	function add() {
		if (!empty($this->data)) {
			$this->Movie->create();
			if ($this->Movie->saveAll($this->data, array('validate' => 'first'))) {
				$this->Session->setFlash(__('The Movie has been saved', true));
				$this->redirect(array('action'=>'index'));
			} else {
				$this->Session->setFlash(__('The Movie could not be saved. Please, try again.', true));
			}
		}
	}

	function edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid Movie', true));
			$this->redirect(array('action'=>'index'));
		}
		if (!empty($this->data)) {
			if ($this->Movie->saveAll($this->data, array('validate' => 'first'))) {
				$this->Session->setFlash(__('The Movie has been saved', true));
				$this->redirect(array('action'=>'index'));
			} else {
				$this->Session->setFlash(__('The Movie could not be saved. Please, try again.', true));
			}
		} else {
			$this->data = $this->Movie->read(null, $id);
		}
	}

	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for Movie', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Movie->del($id)) {
			$this->Session->setFlash(__('Movie deleted', true));
			$this->redirect(array('action'=>'index'));
		}
	}

}
?>