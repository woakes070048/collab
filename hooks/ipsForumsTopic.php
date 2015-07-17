//<?php

class collab_hook_ipsForumsTopic extends _HOOK_CLASS_
{

	/**
	 * Does a container contain unread items?
	 *
	 * @param	\IPS\Node\Model		$container	The container
	 * @param	\IPS\Member|NULL	$member	The member (NULL for currently logged in member)
	 * @return	bool|NULL
	 */
	public static function containerUnread( \IPS\Node\Model $container, \IPS\Member $member = NULL )
	{
		if ( $container->redirect_on )
		{
			if ( $category = \IPS\collab\Category::checkAndLoadUrl( $container->redirect_url ) )
			{
				return \IPS\collab\Collab::containerUnread( $category, $member );
			}
			
			if ( $collab = \IPS\collab\Collab::checkAndLoadUrl( $container->redirect_url ) )
			{
				return $collab->unread();
			}
		}
		
		return call_user_func_array( 'parent::containerUnread', func_get_args() );
	}
	
}