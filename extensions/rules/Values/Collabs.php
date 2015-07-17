<?php
/**
 * @brief		Rules values: Collabs
 * @package		Rules for IPS Social Suite
 * @since		30 May 2015
 * @version		
 */

namespace IPS\collab\extensions\rules\Values;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	Rules values extension: Collabs
 */
class _Collabs
{

	/**
	 * Values
	 *
	 * Define any values used by your app that users can configure custom data fields for.
	 *
	 * @return 	array		Array of value definitions
	 */
	public function values()
	{
		$values = array
		(
			'\IPS\collab\Collab' => array
			(
				'max_collab_members' => array
				(
					'description' => 'Maximum collab members allowed',
					'argtype' => 'int',
				),
			),
		);
		
		return $values;		
	}
	
}