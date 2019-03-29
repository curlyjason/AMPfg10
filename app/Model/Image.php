<?php

App::uses('AppModel', 'Model');

/**
 * Image Model
 *
 */
class Image extends AppModel {
	
	public $ext = array(
		'image/bmp' => 'bmp',
		'image/gif' => 'gif',
		'image/jpeg' => 'jpg',
		'image/pjpeg' => 'jpg',
		'image/png' => 'png',
		'application/pdf' => 'pdf'
	);


    public $actsAs = array(
		'Upload.Upload' => array(
			'img_file' => array(
			'fields' => array(
				'dir' => 'dir'
			),
			'thumbnailSizes' => array(
				'x800y600' => '800w',
				'x500y375' => '500w',
				'x160y120' => '160w',
				'x100y75' => '100w'
			),
			'path' => '{ROOT}webroot{DS}img{DS}{model}{DS}{field}{DS}',
			'thumbnailMethod' => 'php'
			)
		)
    );

	public function deleteExistingImage($itemId) {
		$images = $this->find('list', array(
			'fields' => array('id', 'id'),
			'conditions' => array('item_id' => $itemId)
		));
		if (!empty($images)) {
			foreach ($images as $id) {
				$this->delete($id);
			}
		}
	}
}