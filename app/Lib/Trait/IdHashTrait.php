<?php

/**
 * IdHashTrait
 * 
 * Bring all that secure hash functionality out of its scattered locations
 *
 * @author dondrake
 */
trait IdHashTrait
{

// <editor-fold defaultstate="collapsed" desc="FROM UsersController">
	/**
	 * Check a set of secure ids and strip hashes from the source data
	 *
	 * When permissions are included in User form data, they don't validate
	 * normally. This is our manual way of taking care of them
	 * array [
	 * 	    0 => 93/432abf349abf98934cc98c9c89a89a
	 * 	    1 => 89/08708b8089e098f098e890f083423f
	 * ]
	 * Operates on a reference of the original data, so no return necessary
	 *
	 * @todo Move this to a generalized security class
	 * @param array $ids an array of xx/xyzhash pairs to validate
	     */
	private function validateIds(&$ids)
	{
		if (!empty($ids)) {
			foreach ($ids as $index => $id) {
				$check = explode('/', $id);
				if ($this->secureId($check[0], $check[1])) {
					$ids[$index] = $check[0];
				} else {
					$ids[$index] = FALSE;
				}
			}
		}
	}

	/**
	 * Not used at this point
	 * @todo Move this to a generalized security class
	 * @param type $id
	     */
	private function validateId(&$id)
	{
		$check = explode('/', $id);
		if (count($check) == 2 && $this->secureId($check[0], $check[1])) {
			$ids[$index] = $check[0];
		} else {
			$ids[$index] = $id;
		}
	}


	/**
	 * Was an id and hash provided and if so, was it valid
	 * 
	 * @param string $id
	 * @param string $hash
	 * @return mixed Null = not supplied, True = valid, False = invalid
	 */
	private function suppliedAndValid($id, $hash)
	{
		if ($id != null && $hash != null) {
			$result = $this->secureId($id, $hash);
		} else {
			$result = NULL;
		}
		return $result;
	}

// </editor-fold>
	
// <editor-fold defaultstate="collapsed" desc="FROM AppController">
	
	/**
	 * Verify that POSTed ID was not altered/spoofed
	 *
	 * @param type $data
	 * @param type $model
	 * @return type
	 * @throws BadMethodCallException
	 */
	public function secureData($data = null, $model = null) {
		if (
				!is_array($data) || 
				!key_exists('id', $data[$model]) || 
				!key_exists('secure', $data[$model])) 
		{
			throw new BadMethodCallException(
					'Missing security-check parameter(s) or '
					. 'expected array elements');
		}
		return $this->secureId($data[$model]['id'], $data[$model]['secure']);
	}

	/**
	 * Verify that ID was not altered/spoofed
	 *
	 * @param type $id
	 * @param type $hash
	 * @return boolean
	 * @throws BadMethodCallException
	 */
	public function secureId($id = null, $hash = null) {
		if ($id === null || $hash === null) {
			throw new BadMethodCallException('Missing security-check parameter(s).');
		}
		if ($this->secureHash($id) == $hash) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Write the hash value to prevent id tampering
	 *
	 * Also defined in AppHelper & AppModel
	 * Create a hash value from a record id and user/session specific
	 * values. The id and hash can be sent to the client, then on
	 * return to the server we can verify the id has not been altered
	 *
	 * @param string $id The record id to secure
	 * @return string The secure has to use as a verification value
	 */
	public function secureHash($id) {
		return sha1(
				$id . 
				AuthComponent::user('id') . 
				AuthComponent::$sessionKey . 
				Configure::read('Security.salt'));
	}

	/**
	 * Provide a complete secured select list item
	 *
	 * Concatenate the actual id and chosen delimeter with the secureHash
	 *
	 * @param string $id The record id to secure
	 * @param string $delimeter The delimeter to concat the string on, default to '/'
	 * @return string The concatenation
	 */
	public function secureSelect($id, $delimeter = '/') {
		return $id . $delimeter . $this->secureHash($id);
	}

	/**
	 * validate a delimited secure pair
	 *
	 * explode and check a secured string for client-side tampering
	 * return all the values and the boolean result in an array
	 *
	 * @param string $id The value to secure
	 * @param string $delimeter The delimeter to concat the string on, default to '/'
	 * @return array array (id, hash, true/false)
	 */
	public function validateSelect($securePair, $delimeter = '/') {
		if (strstr($securePair, $delimeter) > '') {
			$check = explode($delimeter, $securePair);
			if (count($check == 2)) {
				$check[2] = $this->secureId($check[0], $check[1]);
				return $check;
			}
		}
		$this->Flash->error('An improperly formatted security string was found.');
		return false;
	}
	
// </editor-fold>
	
}
