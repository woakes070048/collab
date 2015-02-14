//<?php

abstract class collab_hook_ipsContent extends _HOOK_CLASS_
{

	/**
	 * Check Moderator Permission
	 *
	 * @param	string						$type		'edit', 'hide', 'unhide', 'delete', etc.
	 * @param	\IPS\Member|NULL			$member		The member to check for or NULL for the currently logged in member
	 * @param	\IPS\Node\Model|NULL		$container	The container
	 * @return	bool
	 */
	static public function modPermission( $type, \IPS\Member $member=NULL, \IPS\Node\Model $container=NULL )
	{
		$member = $member ?: \IPS\Member::loggedIn();
		$modCan = FALSE;
		if ( isset ( $container ) and $container->collab_id )
		{
			try
			{
				$collab 	= \IPS\collab\Collab::load( $container->collab_id );
				$collabModPerms = $collab->container()->_mod_perms;
				$title 		= static::$title;
				
				/* Check master mod permissions on the category */
				if ( $collabModPerms and $collabModPerms[ "can_{$type}_{$title}" ] )
				{
					/* Collab owners are super! */
					if ( $member->member_id === $collab->owner_id )
					{
						$modCan = TRUE;
					}
					
					/* Check everybody else's membership abilities */
					else if ( $membership = \IPS\collab\Application::collabMembership( $collab, $member ) )
					{
						foreach ( explode( ',', $membership->roles ) as $id )
						{
							try
							{
								$role = \IPS\collab\Collab\Role::load( $id );
								if ( $role->can( 'moderateContent' ) and $rolePerms = \unserialize( $role->mod_perms ) )
								{
									if ( $rolePerms[ "can_{$type}_{$title}" ] )
									{
										$modCan = TRUE;
										break;
									}
								}
							}
							catch ( \OutOfRangeException $e ) {}
						}
					}
				}
			}
			catch ( \OutOfRangeException $e ) {}
		}
		
		return $modCan or call_user_func_array( 'parent::modPermission', func_get_args() );
	}
	
}