<?php

/**
 * CakePHP PrefsComponent
 * @author dondrake
 */
class PrefsComponent extends Component
{

	public $components = array();
	
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

    public function saveBrandingData($data)
    {
        $data = $this->retreiveSavedPreferences($data['customer_user_id']);
        $prefs = unserialize($data);

        $prefs['Preference'] =
            [
                'branding' => $data['branding'],
                'id' => $data['pref_id'],
                'user_id' => $data['customer_user_id']
            ];

        $data = serialize($array);
        $this->Preference->save($data);
	}

    public function retreiveBrandingData($id)
    {
        $data = $this->retreiveSavedPreferences($id);
        $array = unserialize($data);
        return $array['branding'];
	}

}
