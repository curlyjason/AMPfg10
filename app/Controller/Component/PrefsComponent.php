<?php
App::uses('IdHashTrait', 'Lib/Trait');
/**
 * CakePHP PrefsComponent
 * @author dondrake
 */
class PrefsComponent extends Component
{
	
	use IdHashTrait;

	public $components = array('Flash');
	
	public function initialize(Controller $controller)
	{
		$this->Session = $controller->Session;
		$this->Auth = $controller->Auth;
		$this->Preference = ClassRegistry::init('Preference');
;
	}

	/**
	 * Read the preferences and place them in the session
	 */
    public function retrievePreferences() {
        $prefs = $this->retreiveSavedPreferences();

        if ($prefs) {
            $this->Session->write(
                'Prefs',
                unserialize($prefs['Preference']['prefs']));
            $this->Session->write(
                'Prefs.id',
                $prefs['Preference']['id']);
        }
    }

	/**
	 * 
	 * @param string $id
	 * @return array
	 */
    private function retreiveSavedPreferences($id = null)
    {
        if ($id === null) {
            $id = $this->Auth->user('id');
        }

        $prefs = $this->Preference->find('first',
            ['conditions' =>
                ['Preference.user_id' => $id]
            ]);

        return $prefs;
    }

	/**
	 * 
	 */
	public function savePreferences() {
		if ($this->Session->read('Prefs')) {
			
			$prefs['Preference'] = 
				[
					'prefs' => serialize($this->Session->read('Prefs')),
					'id' => $this->Session->read('Prefs.id'),
					'user_id' => $this->Auth->user('id')
				];
			
			$this->Preference->save($prefs);

			// make sure even a brand new prefs record id gets into the session
			// this could be handled in afterSave of Preference
			$this->Session->write('Prefs.id', $this->Preference->id);
		}
	}

	public function searchFilterPreference($filter) {
//		$this->autoRender = false;
		$this->Session->write('Prefs.Search', $filter);
		$this->savePreferences();
	}

	/**
	 * Persist the submitted form data to the db
	 * 
	 * ```
	 * [
	 *		'company' => '',
	 *		'address1' => '',
	 *		'address2' => '',
	 *		'address3' => '',
	 *		'customer_user_id' => '25/hash'
	 * ]
	 * ```
	 * 
	 * @param array $data
	 */
    public function saveBrandingData($data)
    {
		$IdSecurityChip = 
				$this->validateSelectTakingOverTheWorld(
						$data['customer_user_id']
					);
		
		if($IdSecurityChip->isValid()) {
			debug($IdSecurityChip->id());
			$this->addBrandingToPrefs($IdSecurityChip->id(), $data);
			
		} else {
			
			$this->Flash->error('The branding data was not recorded. '
					. 'Bad id security hash detected.');
			
		}
		
		return TRUE;
	}
	
	private function addBrandingToPrefs($customer_user_id, $data)
	{
		$raw = $this->retreiveSavedPreferences($customer_user_id);
		if (empty($raw)) {
			$raw = [
				'prefs' => '',
				'id' => '',
			];
		}
        $prefs = unserialize($raw['prefs']);
		$prefs['branding'] = $data;

        $record['Preference'] =
            [
                'prefs' => serialize($prefs),
                'id' => $raw['id'],
                'user_id' => $customer_user_id
            ];

        $this->Preference->save($record);
	}

	public function retreiveBrandingData($id)
    {
        $data = $this->retreiveSavedPreferences($id);
        $array = unserialize($data);
        return $array['branding'];
	}

}
