//<?php

abstract class collab_hook_ipsContentSearchQuery extends _HOOK_CLASS_
{

	/**
	 * @brief	Flag option to filter collab content
	 */
	public $filterCollabs = TRUE;

	/**
	 * Create new query
	 *
	 * @param	\IPS\Member	$member	The member performing the search (NULL for currently logged in member)
	 * @return	\IPS\Content\Search
	 */
	public static function init( \IPS\Member $member = NULL )
	{
		$query = parent::init( $member );
		
		if ( $query->filterCollabs )
		{
			$query->filterCollabContent();
		}
		
		return $query;
	}
	
	/**
	 * Filter Collab Content
	 *
	 * @param	int		$order	Order (see ORDER_ constants)
	 * @return	\IPS\Content\Search\Query	(for daisy chaining)
	 */
	public function filterCollabContent()
	{
		if ( ! $this->member->modPermission( 'can_bypass_collab_permissions' ) )
		{
			$this->where[] = array( "( NOT (" . \IPS\Db::i()->findInSet( 'index_permissions', array( 'c' ) ) . ") OR " . \IPS\Db::i()->findInSet( 'index_permissions', $this->collabPermissionArray() ) . ' )' );
		}
		
		return $this;
	}
	
	/**
	 * Permission Array
	 *
	 * @return	array
	 */
	public function permissionArray()
	{	
		/* Star permission is included to satisfy the core permission SQL clause */
		return array_merge( array( '*' ), parent::permissionArray() );
	}
	
	/**
	 * Collab Permission Array
	 *
	 * @return 	array
	 */
	public function collabPermissionArray()
	{
		$permissionArray = array();
		
		foreach( $this->member->collabMemberships() as $membership )
		{
			if ( $membership->status === \IPS\collab\COLLAB_MEMBER_ACTIVE )
			{
				/* All members permission */
				$permissionArray[] = 'cm' . $membership->collab_id;
				
				/* Individual roles permissions */
				foreach( $membership->roles() as $role )
				{
					$permissionArray[] = 'cr' . $role->id;
				}
			}
		}

		return $permissionArray;
	}

}