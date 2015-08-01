//<?php

class collab_hook_ipsWidgetPermissionCache extends _HOOK_CLASS_
{
	/**
	 * Constructor
	 *
	 * @param	String				$uniqueKey				Unique key for this specific instance
	 * @param	array				$configuration			Widget custom configuration
	 * @param	null|string|array	$access					Array/JSON string of executable apps (core=sidebar only, content=IP.Content only, etc)
	 * @param	null|string			$orientation			Orientation (top, bottom, right, left)
	 * @param	boolean				$allowReuse				If true, when the block is used, it will remain in the sidebar so it can be used again.
	 * @param	string				$menuStyle				Menu is a drop down menu, modal is a bigger modal panel.
	 * @return	void
	 */
	public function __construct( $uniqueKey, array $configuration, $access=null, $orientation=null )
	{
		parent::__construct( $uniqueKey, $configuration, $access, $orientation );

		$roles = array();
		$membership_roles = iterator_to_array( \IPS\Db::i()->select( 'roles', 'collab_memberships', array( 'member_id=? AND status=? AND roles!=?', \IPS\Member::loggedIn()->member_id, \IPS\collab\COLLAB_MEMBER_ACTIVE, '' ) ) );
		
		foreach( $membership_roles as $rolegroup )
		{
			foreach( explode( ',', $rolegroup ) as $roleid )
			{
				if ( $roleid )
				{
					$roles[] = $roleid;
				}
			}
		}
		
		if ( $roles )
		{
			/* For permissions based cache we need to make cacheKey unique per collab roleset */
			sort( $roles );
			$this->cacheKey .= '_' . md5( implode( ',', $roles ) );
		}
	}

}