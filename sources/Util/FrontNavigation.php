<?php
/**
 * @brief		Collaboration Utilities
 * @author		Kevin Carwile (http://www.linkedin.com/in/kevincarwile)
 * @copyright		(c) 2014 - Kevin Carwile
 * @package		Collaboration
 * @since		10 Dec 2014
 */


namespace IPS\collab\Util;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _FrontNavigation 
{

	/**
	 * @brief	Wrapped Object
	 */
	protected $obj;

	/**
	 * Constructor
	 */
	public function __construct( $obj )
	{
		$this->obj = $obj;
	}
	
	/**
	 * Call Methods
	 */
	public function __call( $method, $args )
	{
		if ( $method == 'active' )
		{
			return get_class( $this->obj ) == 'IPS\collab\extensions\core\FrontNavigation\navigation';
		}
		
		return call_user_func_array( array( $this->obj, $method ), $args );
	}

}