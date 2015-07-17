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
		$member 	= $member ?: \IPS\Member::loggedIn();
		$modCan 	= FALSE;
		$collab_id 	= NULL;
		
		/**
		 * Extrapolate if this modPermission is for collab content
		 */
		if ( isset( $container ) )
		{
			$collab_id = $container->collab_id;
		}
		else if ( $affectiveCollab = \IPS\collab\Application::affectiveCollab() )
		{
			$collab_id = $affectiveCollab->collab_id;
		}
		
		/**
		 * If this modPermission applies to collab content, check collab moderation permissions also
		 */
		if ( $collab_id )
		{
			try
			{
				$collab 	= \IPS\collab\Collab::load( $collab_id );
				$collabModPerms = $collab->container()->_mod_perms;
				$title 		= static::$title;
				
				/* Check master mod permissions on the category */
				if ( $collabModPerms and $collabModPerms[ "can_{$type}_{$title}" ] )
				{
					/* Collab owners are super! */
					if ( $member->member_id === $collab->owner_id and ! $collab->container()->bitoptions[ 'restrict_owner' ] )
					{
						$modCan = TRUE;
					}
					
					/* Check everybody else's membership abilities */
					else if ( $membership = $collab->getMembership( $member ) )
					{
						foreach ( $membership->roles() as $role )
						{
							if ( $role->roleCan( 'moderateContent' ) and $rolePerms = \unserialize( $role->mod_perms ) )
							{
								if ( $rolePerms[ "can_{$type}_{$title}" ] )
								{
									$modCan = TRUE;
									break;
								}
							}
						}
					}
				}
			}
			catch ( \OutOfRangeException $e ) { }
		}

		return $modCan or call_user_func_array( 'parent::modPermission', func_get_args() );
	}
	
	/**
	 * [ActiveRecord] Save
	 *
	 * Update the last post info on collab when new content is created inside a collab
	 */
	public function save()
	{
		$is_new = $this->_new;
		
		$result = parent::save();
		
		if ( $is_new and ( $this instanceof \IPS\Content\Item or $this instanceof \IPS\Content\Comment ) and $collab = \IPS\collab\Application::getCollab( $this ) )
		{
			$collab->last_post = time();
			$collab->last_poster_id = $this->author()->member_id;
			$collab->last_poster_name = $this->author()->name;
			$collab->save();
		}
	}
	
}