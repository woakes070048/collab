//<?php

class collab_hook_ipsSessionFront extends _HOOK_CLASS_
{

	/**
	 * Admin Log
	 *
	 * @code
	 	\IPS\Session::i()->log( 'acplog__enhancements_enable', array( 'enhancements__foo' => TRUE ) );
	 * @encode
	 * @param	string	$langKey	Language key for log
	 * @param	array	$params		Key/Values - keys are variables to use in sprintf on $langKey, values are booleans indicating if they are language keys themselves (TRUE) or raw data (FALSE)
	 * @param	bool	$noDupes	If TRUE, will check the last log and not log again if it's the same and less than an hour ago
	 * @return	void
	 */
	public function log( $langKey, $params=array(), $noDupes=FALSE )
	{
	
	}
  
}