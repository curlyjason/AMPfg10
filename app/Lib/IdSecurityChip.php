<?php

/**
 * IdSecurityChip holds and provides access to validateSelect() results
 * 
 * IdHashTrait::validateSelectTakingOverTheWorld($hash, $delimeter) which was 
 * formerly AppController::validateSelect($hash, $delimeter) returns an array 
 * of 3 values:
 * 
 * ```
 * [
 *		0 => id,
 *		1 => hashed_value,
 *		3 => boolean (valid pair)
 * ]
 * ```
 * 
 * To simplify use of the method and its result, this object will contain 
 * the result array and provide accessor methods.
 *
 * @author dondrake
 */
class IdSecurityChip
{
	
	public function __construct($data)
	{
		$this->data = $data;
	}
	
	public function id()
	{
		if ($this->data) {
			$result = $this->data[0];
		} else {
			$result = NULL;
		}
		return $result;
	}
	
	public function hash()
	{
		if ($this->data) {
			$result = $this->data[1];
		} else {
			$result = NULL;
		}
		return $result;
	}
	
	public function isValid()
	{
		return is_array($this->data);
	}
	
}
