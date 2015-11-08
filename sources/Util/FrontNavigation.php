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
	 * @brief	Is Subitem of Collab Menu?
	 */
	public $isCollabSubitem = FALSE;
	
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
		/**
		 * Since this utility is being used, we know that we are in a collaboration context,
		 * so the active link should always be the collaboration navigation link.
		 */
		if ( $method == 'active' )
		{
			/* Return TRUE if this is the navigation item for the collab app */
			$isCollabMenu = get_class( $this->obj ) == 'IPS\collab\extensions\core\FrontNavigation\navigation';
			
			/**
			 * IPS 4.1 menu's can have sub items
			 */
			if ( class_exists( 'IPS\core\FrontNavigation' ) )
			{
				return $isCollabMenu or ( $this->isCollabSubitem and $this->obj->active() );
			}
			
			return $isCollabMenu;
		}
		
		return call_user_func_array( array( $this->obj, $method ), $args );
	}
	
	/**
	 * Get Properties
	 */
	public function __get( $prop )
	{
		return $this->obj->$prop;
	}
	
	/**
	 * Set Properties
	 */
	public function __set( $prop, $val )
	{
		return $this->obj->$prop = $val;
	}

}