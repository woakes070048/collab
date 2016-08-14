//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class collab_hook_extCoreNotificationsContent extends _HOOK_CLASS_
{

	/**
	 * Get configuration
	 *
	 * @param	\IPS\Member|null	$member	The member
	 * @return	array
	 */
	public function getConfiguration( $member )
	{
		$result = parent::getConfiguration( $member );
		
		/* Look to see if member is a collab moderator */
		if ( ! isset( $result[ 'unapproved_content' ] ) )
		{
			if( count( $member->collabs( 'active', array( 'moderateContent' ) ) ) > 0 )
			{
				$result[ 'unapproved_content' ] = array( 'default' => array( 'email' ), 'disabled' => array(), 'icon' => 'lock' );
			}
		}
		
		return $result;
	}
	
}
