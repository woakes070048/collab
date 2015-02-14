<?php
/**
 * @brief		Permissions
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Social Suite
 * @subpackage	785daf5a5e97facd93f80846fd6066ba
 * @since		29 Jan 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\collab\extensions\core\Permissions;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Permissions
 */
class _Permissions
{
	/**
	 * Get node classes
	 *
	 * @return	array
	 */
	public function getNodeClasses()
	{		
		return array(
			'IPS\collab\Category' => function( $current, $group )
			{
				$rows = array();
				foreach( \IPS\collab\Category::roots( NULL ) AS $root )
				{
					\IPS\collab\Category::populatePermissionMatrix( $rows, $root, $group, $current );
				}
				
				return $rows;
			}
		);	}

}