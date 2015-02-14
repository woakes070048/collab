<?php
/**
 * @brief		Admin CP Group Form
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Social Suite
 * @subpackage	
 * @since		24 Jan 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\collab\extensions\core\GroupForm;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Admin CP Group Form
 */
class _groupForm
{
	/**
	 * Process Form
	 *
	 * @param	\IPS\Form\Tabbed		$form	The form
	 * @param	\IPS\Member\Group		$group	Existing Group
	 * @return	void
	 */
	public function process( &$form, $group )
	{		
		$form->add( new \IPS\Helpers\Form\Number( 'g_collabs_owned_limit', $group->g_collabs_owned_limit, FALSE, array( 'unlimited' => 0, 'min' => 1 ), NULL, NULL, NULL, 'g_collabs_owned_limit' ) );
		$form->add( new \IPS\Helpers\Form\Number( 'g_collabs_joined_limit', $group->g_collabs_joined_limit, FALSE, array( 'unlimited' => 0, 'min' => 1 ), NULL, NULL, NULL, 'g_collabs_joined_limit' ) );
	}
	
	/**
	 * Save
	 *
	 * @param	array				$values	Values from form
	 * @param	\IPS\Member\Group	$group	The group
	 * @return	void
	 */
	public function save( $values, &$group )
	{
		$group->g_collabs_owned_limit = $values[ 'g_collabs_owned_limit' ];	
		$group->g_collabs_joined_limit = $values[ 'g_collabs_joined_limit' ];	
	}
}