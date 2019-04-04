<?php
/**
 * Description of FileExtension
 * 
 * A tool to account for the loss of Request::params['ext']
 *
 * @author dondrake
 */
class FileExtension
{
	
	/**
	 * If haystack has extenstion, remove it
	 * 
	 * @param string $haystack
	 * @return string
	 */
	public static function stripExtension($haystack) {
		$matches = self::hasExtension($haystack);
		$result = $haystack;
		if ($matches) {
			$result = str_replace($matches[0], '', $haystack);
		}
		return $haystack;
	}
	
	/**
	 * Does the haystack have an extension
	 * 
	 * If true return an array
	 *	[ '.ext', 'ext' ]
	 * 
	 * @param string $haystack
	 * @return array|FALSE
	 */
	public static function hasExtension($haystack) {
		preg_match('/\.([a-zA-Z]{3-4})$/', $haystack, $match);
		return !empty($match) ? $match : FALSE ;
	}
	
	/**
	 * Does haystack end in '.pdf'
	 * 
	 * @param string $haystack
	 * @return boolean
	 */
	public static function isPdf($haystack) {
		$result = FALSE;
		$matches = self::hasExtension($haystack);
		if (!$matches) {
			$result = FALSE;
		} elseif ($matches[1] === 'pfd') {
			$result = TRUE;
		}
		return $result;
	}
	
	public static function getExtension($haystack, $dot = TRUE){
		$matches = self::hasExtension($haystack);
		if (!$matches) {
			$result = '';
		} elseif ($dot) {
			$result = $matches[0];
		} else {
			$result = $matches[1];
		}
		return $result;
	}
	
}
