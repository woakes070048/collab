<?php
/**
 * @brief		Group Limits
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Social Suite
 * @subpackage	
 * @since		25 Jan 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\collab\extensions\core\GroupLimits;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Group Limits
 *
 * This extension is used to define which limit values "win" when a user has secondary groups defined
 * 
 */
class _groupLimits
{
	/**
	 * Get group limits by priority
	 *
	 * @return	array
	 */
	public function getLimits()
	{
		return array (
						'exclude' 	=> array(),
						'lessIsMore'	=> array(),
						'neg1IsBest'	=> array(),
						'zeroIsBest'	=> array( 'g_collabs_owned_limit', 'g_collabs_joined_limit' ),
				);
	}
}