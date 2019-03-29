<?php

App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

/**
 * NotificationFileHandler
 * 
 * Events that require User notification defer the actual notification to avoid slowing 
 * the user's experience down unnecessarily. As an alternative, these events send packets of 
 * context data to 'Pending' files. At appropriate times, the Pending files are read and 
 * accumulated into a 'Processed' file. The Processed file is used by the Event that actually 
 * sends Notifications. Some notifications must be sent right away, and some may be further 
 * defered, so the Processed file is also stored, retrieved and re-written by this Class.
 *
 * @package Notification.Notify
 * @author jasont
 */

class NotificationFileHandler {
// <editor-fold defaultstate="collapsed" desc="Properties">


	/**
	 * @var array One File object for each pending file
	 */
	public $pendingFiles;


	/**
	 * The pending notification data arrays indexed by the file path where the data is stored
	 * 
	 * Notification events defer actual notifications in favor of writing the context data 
	 * to a Pending file (as xml). When these files are read for processing, their contents 
	 * converted back to an array, indexed by the path to the file, and stored in this property. 
	 * Then the file is deleted so there is no overlapping processing work done on it.
	 * 
	 * Once the data is processed into messages and sent, the elements are unset for the stored 
	 * arrays. If any data are at the end of the notification process, the set is written back to 
	 * the file again for later processing.
	 *
	 * @var array The pending-file data
	 */
	public $pendingData = array ();


	/**
	 * @var string The path to the folder containing the pending files
	 */

	public $pendingPath = '/app/View/Emails/notifications/';
// </editor-fold>


	public function __construct() {
		$this->pendingPath = ROOT . $this->pendingPath;
		}
	/**
	 * Write file of single notification event
	 * 
	 * pre: array containing delivery string (eg email or XML), subject string,
	 *		content array
	 * 
	 * post: written file and throw exception on fail
	 * 
	 * @param array $notification The context data for the notification
	 * @param string $path data may be new or resaved. NULL for new, $path for resaved
	 */
	public function writePending(Array $notification, $path = NULL) {
		//setup xml object & property
		$xml = Xml::fromArray(array('root' => $notification));
		$xmlToWrite = $xml->asXML();

		//create & open file
		if ($path === NULL) {
			$file = new File($this->pendingPath . 'notification' . microtime(TRUE) . '.xml');
		} else {
			$file = new File($path);
		}
		if(!$file->create() && !$file->open('w', FALSE)){
			CakeLog::write('notificationLog', "Open File Failed/n/r" . $xmlToWrite);
			throw new FailedSaveException('Notification xml file failed to create');
		}
		
		//write file
		if(!$file->write($xmlToWrite)){
			CakeLog::write('notificationLog', "Write to File Failed/n/r" . $xmlToWrite);
			throw new FailedSaveException('Notification xml file failed to write');
		}
		
		//close file
		if($file->close() !== TRUE){
			$this->ddd($file->handle, 'handle');
			CakeLog::write('notificationLog', "Close File Failed/n/r" . $xmlToWrite);
			throw new FailedSaveException('Notification xml file failed to close');
		}
	}
	

	/**
	 * Get a list of the files in the notification folder
	 * and read those full-path filenames into the pendingFiles property
	 * 
	 * @return array $this->pendingFiles
	 */
	public function getPendingFiles() {
		$folder = new Folder($this->pendingPath);
		
		// last parm gets full path
		$files = $folder->read(TRUE, TRUE, TRUE);
		
		// [0] is directories, [1] is files (I think)
		foreach ($files[1] as $file) {
			$f = new File($file);
			if($f->open() && $f->size() > 0){
				$this->pendingFiles[] = $f;
			} else {
				$f->delete();
			}
		}
		return $this->pendingFiles;
	}
	
	public function readPendingFiles() {
		$this->getPendingFiles();
		if (!empty($this->pendingFiles)) {
			foreach ($this->pendingFiles as $index => $file) {
				$this->readPending($file);
				$file->delete();
			}
		}
		return $this->pendingData;
	}
	
	public function savePendingData() {
		foreach ($this->pendingData as $path => $data) {
			$this->writePending($data, $path);
		}
	}
	/**
	 * Read a single 'pending' file and convert it to an array stripped of its 'root' node
	 * 
	 * Pending files contain xml data recorded during some order/inventory event
	 * that will be used to provide user notification about site activity. 
	 * The data is artificially wrapped in a 'root' node to insure proper xml structure.
	 * 
	 * @param obj $file
	 * @return array the data that was stored in the file
	 */
	public function readPending(File $file) {
		$f = $file->read();
		$data = Xml::toArray(Xml::build($f));
		if(empty($data)){
            /**
             * @todo add standard logging
             */
		}
		$this->pendingData[$file->path] = $data['root'];
		if (isset($this->pendingData[$file->path]['Watcher']) && is_array($this->pendingData[$file->path]['Watcher'])) {
			$k = array_keys($this->pendingData[$file->path]['Watcher']);
			if ($k[0] != '0') {
				$this->pendingData[$file->path]['Watcher'] = array('0' => $this->pendingData[$file->path]['Watcher']);
			}
		}
	}
	
}
