//<?php

class collab_hook_ipsHttpUrl extends _HOOK_CLASS_
{
	/**
	 * Save "unfriendly" url params for insepection later
	 */
	public $_queryString = array();
	
	/**
	 * Constructor
	 *
	 * @param	string	$url		The URL
	 * @param	bool	$internal	Is internal? (NULL to auto-detect)
	 * @return	void
	 * @throws	\InvalidArgumentException
	 */
	public function __construct( $url, $internal=NULL )
	{
		call_user_func_array( 'parent::__construct', func_get_args() );
		$this->_queryString = $this->queryString;
	}

}