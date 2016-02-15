//<?php

abstract class collab_hook_ipsContentItem extends _HOOK_CLASS_
{

	/**
	 * Construct ActiveRecord from database row
	 *
	 * @param	array	$data							Row from database table
	 * @param	bool	$updateMultitonStoreIfExists	Replace current object in multiton store if it already exists there?
	 * @return	static
	 */
	static public function constructFromData( $data, $updateMultitonStoreIfExists=true )
	{
		$obj = call_user_func_array( 'parent::constructFromData', func_get_args() );
		\IPS\collab\Application::inferCollab( $obj );
		return $obj;
	}

	/**
	 * Is locked?
	 *
	 * @return	bool
	 * @throws	\BadMethodCallException
	 */
	public function locked()
	{
		$locked = parent::locked();
		
		if ( $locked === TRUE )
		{
			return TRUE;
		}
		
		if ( $collab = \IPS\collab\Application::getCollab( $this ) )
		{
			if ( $collab->locked() )
			{
				return TRUE;
			}
		}
		
		return $locked;
	}
	
	/**
	 * Get items with permisison check
	 *
	 * @param	array		$where				Where clause
	 * @param	string		$order				MySQL ORDER BY clause (NULL to order by date)
	 * @param	int|array	$limit				Limit clause
	 * @param	string|NULL	$permissionKey			A key which has a value in the permission map (either of the container or of this class) matching a column ID in core_permission_index or NULL to ignore permissions
	 * @param	bool|NULL	$includeHiddenItems		Include hidden files? Boolean or NULL to detect if currently logged member has permission
	 * @param	int		$queryFlags			Select bitwise flags
	 * @param	\IPS\Member	$member				The member (NULL to use currently logged in member)
	 * @param	bool		$joinContainer			If true, will join container data (set to TRUE if your $where clause depends on this data)
	 * @param	bool		$joinComments			If true, will join comment data (set to TRUE if your $where clause depends on this data)
	 * @param	bool		$joinReviews			If true, will join review data (set to TRUE if your $where clause depends on this data)
	 * @param	bool		$countOnly			If true will return the count
	 * @param	array|null	$joins				Additional arbitrary joins for the query
	 * @param	mixed		$skipPermission		If you are getting records from a specific container, pass the container to reduce the number of permission checks necessary or pass TRUE to skip conatiner-based permission. You must still specify this in the $where clause
	 * @param	bool		$joinTags			If true, will join the tags table
	 * @param	bool		$joinAuthor			If true, will join the members table for the author
	 * @param	bool		$joinLastCommenter	If true, will join the members table for the last commenter
	 * @param	bool		$showMovedLinks		If true, moved item links are included in the results
	 * @return	\IPS\Patterns\ActiveRecordIterator|int
	 */
	public static function getItemsWithPermission( $where=array(), $order=NULL, $limit=10, $permissionKey='read', $includeHiddenItems=NULL, $queryFlags=0, \IPS\Member $member=NULL, $joinContainer=FALSE, $joinComments=FALSE, $joinReviews=FALSE, $countOnly=FALSE, $joins=NULL, $skipPermission=FALSE, $joinTags=TRUE, $joinAuthor=TRUE, $joinLastCommenter=TRUE, $showMovedLinks=FALSE )
	{		
		if ( isset( static::$containerNodeClass ) )
		{
			$nodeClass = static::$containerNodeClass;

			/**
			 * If the container node is enabled for collabs, then we either want to limit results to the affective collab,
			 * or limit the results to non-collab + permitted collab content.
			 */
			if ( \IPS\Db::i()->checkForColumn( $nodeClass::$databaseTable, $nodeClass::$databasePrefix . 'collab_id' ) )
			{
				$member = $member ?: \IPS\Member::loggedIn();
				$joinContainer = TRUE;
				
				$collabClause = static::collabPermissionWhere( $member, $nodeClass, $permissionKey );
				
				$where = array_merge( $where ?: array(), $collabClause[ 'where' ] );
				$joins = array_merge( $joins ?: array(), $collabClause[ 'joins' ] );
			}
		}
		
		return parent::getItemsWithPermission( $where, $order, $limit, $permissionKey, $includeHiddenItems, $queryFlags, $member, $joinContainer, $joinComments, $joinReviews, $countOnly, $joins, $skipPermission, $joinTags, $joinAuthor, $joinLastCommenter, $showMovedLinks );
	}
	
	/**
	 * Custom Method: Get Collab Items
	 *
	 * @param	\IPS\collab\Collab|int		$collab		A collab object or collab id to get content from
	 * @param	array		$where				Where clause
	 * @param	string		$order				MySQL ORDER BY clause (NULL to order by date)
	 * @param	int|array	$limit				Limit clause
	 * @param	string|NULL	$permissionKey			A key which has a value in the permission map (either of the container or of this class) matching a column ID in core_permission_index or NULL to ignore permissions
	 * @param	bool|NULL	$includeHiddenItems		Include hidden files? Boolean or NULL to detect if currently logged member has permission
	 * @param	int		$queryFlags			Select bitwise flags
	 * @param	\IPS\Member	$member				The member (NULL to use currently logged in member)
	 * @param	bool		$joinContainer			If true, will join container data (set to TRUE if your $where clause depends on this data)
	 * @param	bool		$joinComments			If true, will join comment data (set to TRUE if your $where clause depends on this data)
	 * @param	bool		$joinReviews			If true, will join review data (set to TRUE if your $where clause depends on this data)
	 * @param	bool		$countOnly			If true will return the count
	 * @param	array|null	$joins				Additional arbitrary joins for the query
	 * @return	\IPS\Patterns\ActiveRecordIterator|array|int
	 */
	public static function getCollabItems( $collab, $where=array(), $order=NULL, $limit=10, $permissionKey='read', $includeHiddenItems=NULL, $queryFlags=0, \IPS\Member $member=NULL, $joinContainer=FALSE, $joinComments=FALSE, $joinReviews=FALSE, $countOnly=FALSE, $joins=NULL, $skipPermission=FALSE )
	{
		if ( ! ( $collab instanceof \IPS\collab\Collab ) )
		{
			try
			{
				$collab = \IPS\collab\Collab::load( $collab );
			}
			catch( \OutOfRangeException $e )
			{
				return array();
			}
		}
		
		\IPS\collab\Application::$affectiveCollab = $collab;
		$items = static::getItemsWithPermission( $where, $order, $limit, $permissionKey, $includeHiddenItems, $queryFlags, $member, $joinContainer, $joinComments, $joinReviews, $countOnly, $joins );
		\IPS\collab\Application::$affectiveCollab = NULL;
		
		return $items;
	}
	
	/**
	 * Additional WHERE clauses for New Content view
	 *
	 * @param	bool		$joinContainer		If true, will join container data (set to TRUE if your $where clause depends on this data)
	 * @param	array		$joins				Other joins
	 * @return	array
	 */
	public static function vncWhere( &$joinContainer, &$joins )
	{
		$where = array();
		
		if ( isset( static::$containerNodeClass ) )
		{
			$nodeClass = static::$containerNodeClass;
		
			/**
			 * If the container node is provisioned for collabs, then we either want to limit results to the affective collab,
			 * or limit the results to non-collab content.
			 */
			if ( \IPS\Db::i()->checkForColumn( $nodeClass::$databaseTable, $nodeClass::$databasePrefix . 'collab_id' ) )
			{
				$joinContainer = TRUE;
				$member = \IPS\Member::loggedIn();

				$collabClause = static::collabPermissionWhere( $member, $nodeClass );
				
				$where = array_merge( $where, $collabClause[ 'where' ] );
				$joins = array_merge( $joins ?: array(), $collabClause[ 'joins' ] );
			}
		}
		
		/* Backwards compatibility with 4.0.x */
		$parentWhere = is_callable( 'parent::vncWhere' ) ? parent::vncWhere( $joinContainer, $joins ) : array();
		
		return array_merge( $parentWhere, $where );
	}
	
	/**
	 * Build where clause that accounts for collab permissions
	 *
	 * @param	\IPS\Member	$member			Member to check permissions for
	 * @param	string		$nodeClass		The classname of the item container
	 * @param	string		$permissionKey		The permission to check
	 * @return	array					An array containing new where clauses and required joins
	 */
	public static function collabPermissionWhere( $member, $nodeClass, $permissionKey='read' )
	{
		if ( ! isset( $nodeClass::$permissionMap[ $permissionKey ] ) )
		{
			$permissionKey = 'view';
		}
		
		$where = array();
		$joins = array();
		
		$member_id = $member->member_id ?: 0;
	
		/* Are we in a collab context? */
		if ( $collab = \IPS\collab\Application::affectiveCollab() )
		{
			/* Check if this content type is enabled for this collab */
			if ( $collab->enabledNodes( md5( $nodeClass ) ) )
			{
				/* Filter to only nodes owned by the collab */
				$where[] = array( $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . 'collab_id=?', $collab->collab_id );

				if ( ! $member->modPermission( 'can_bypass_collab_permissions' ) )
				{
					/* If the node class also uses permissions, we need to filter by internal collab role permissions as well */
					if ( in_array( 'IPS\Node\Permissions', class_implements( $nodeClass ) )	)
					{
						
						/* Get a list of all the members collab role id's */
						$collabRoles = array();
						if ( $membership = $collab->getMembership( $member ) )
						{
							/* Membership Roles */
							foreach( $membership->roles() as $role )
							{
								$collabRoles[] = $role->id;
							}
						}

						$categories	= array();
						$lookupKey	= md5( $nodeClass::$permApp . 'collab_' . $nodeClass::$permType . $permissionKey . json_encode( $collabRoles ) );

						/* Lookup collab containers the member has permission for */
						if( ! isset( static::$permissionSelect[ $lookupKey ] ) )
						{
							/* Filter by collab permission */
							$lookupWhere = 
								
								/* Anybody has permission */
								'core_permission_index.perm_' . $nodeClass::$permissionMap[ $permissionKey ] . "='*' OR " . 
							
								( $membership ? 
							
									/* Role based permission */
									'core_permission_index.perm_' . $nodeClass::$permissionMap[ $permissionKey ] . "='0' OR " . 
									\IPS\Db::i()->findInSet( 'core_permission_index.perm_' . $nodeClass::$permissionMap[ $permissionKey ], $collabRoles ) :
									
									/* Guest permission */
									'core_permission_index.perm_' . $nodeClass::$permissionMap[ $permissionKey ] . " LIKE '-1%'"
								);
							
							/* Some CMS Categories are special in that they dont have overridden permissions */
							if ( is_subclass_of( get_called_class(), 'IPS\cms\Categories' ) )
							{
								/* Filter by database */
								$databaseWhere = $nodeClass::$databaseTable . '.category_database_id=' . $nodeClass::$customDatabaseId;
								
								/* Include categories with disabled permission overrides */
								$lookupWhere = $databaseWhere . " AND ( " . $nodeClass::$databaseTable . ".category_has_perms=0 OR ( {$lookupWhere} ) )";				
							}						
							
							/* Prepend collab filter */
							$lookupWhere = $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . 'collab_id=' . $collab->collab_id . ' AND ' . $lookupWhere;
							
							/* Build lookup query */
							$lookup = \IPS\Db::i()
								->select( $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . $nodeClass::$databaseColumnId, $nodeClass::$databaseTable, array( $lookupWhere ) )
								->join( 'core_permission_index', array( 'core_permission_index.perm_type_id=' . $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . $nodeClass::$databaseColumnId . " AND core_permission_index.app='" . $nodeClass::$permApp . "' AND core_permission_index.perm_type='collab_" . $nodeClass::$permType . "'" ) );
							
							/* Cache results */
							static::$permissionSelect[ $lookupKey ] = iterator_to_array( $lookup );
						}

						$categories = static::$permissionSelect[ $lookupKey ];

						if( count( $categories ) )
						{
							/* Filter by permitted containers */
							$where[] = array( static::$databaseTable . "." . static::$databasePrefix . static::$databaseColumnMap[ 'container' ] . ' IN(' . implode( ',', $categories ) . ')' );
						}
						else
						{
							/* No permitted containers available */
							$where[] = array( '1=0' );
						}
					}
				}
			}
			else
			{
				/* This content type is not enabled for this collab */
				$where[] = array( '1=0' );
			}
		}
		
		/* Outside of collab context */
		else
		{					 
			if ( ! $member->modPermission( 'can_bypass_collab_permissions' ) )
			{
				if ( $member->member_id )
				{
					/* Compile a list of all roles for the member */
					$all_roles = array();
					foreach ( new \IPS\Patterns\ActiveRecordIterator( \IPS\Db::i()->select( '*', 'collab_memberships', array( 'status=? AND member_id=?', \IPS\collab\COLLAB_MEMBER_ACTIVE, $member_id ) ), '\IPS\collab\Collab\Membership' ) as $membership )
					{
						$roles = array_map( function( $role ) { return $role->id; }, $membership->roles() );
						$all_roles = array_merge( $all_roles, $roles );
					}
					
					/* Join 'active' collab membership */
					$joins[] = array( 'from' => 'collab_memberships', 'where' => array( 'collab_memberships.member_id=' . $member_id . ' AND collab_memberships.status=\'' . \IPS\collab\COLLAB_MEMBER_ACTIVE . '\' AND collab_memberships.collab_id=' . $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . 'collab_id' ) );
					
					if ( in_array( 'IPS\Node\Permissions', class_implements( $nodeClass ) ) )
					{
						/* Join collab permissions */
						$joins[] = array( 'from' => array( 'core_permission_index', 'collab_core_permission_index' ), 'where' => array( "collab_core_permission_index.app='" . $nodeClass::$permApp . "' AND collab_core_permission_index.perm_type='collab_" . $nodeClass::$permType . "' AND collab_core_permission_index.perm_type_id=" . $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . $nodeClass::$databaseColumnId ) );				
						
						/* Filter by collab permissions */
						$collabWhere = 	
							
							/* Permission for everybody */
							"collab_core_permission_index.perm_" . $nodeClass::$permissionMap[ $permissionKey ] . "='*' OR " .
							
							/* No active membership with guest permission */
							"( ISNULL( collab_memberships.id ) AND collab_core_permission_index.perm_" . $nodeClass::$permissionMap[ $permissionKey ] . " LIKE '-1%' ) OR " . 
							
							/* Active membership with role permission */
							"(" . 
								"collab_memberships.id IS NOT NULL AND " . 
								"(" . 
									"collab_core_permission_index.perm_" . $nodeClass::$permissionMap[ $permissionKey ] . "='0' OR " . 
									\IPS\Db::i()->findInSet( 'collab_core_permission_index.perm_' . $nodeClass::$permissionMap[ $permissionKey ], $all_roles ) .
								")" .
							")";
					}
				}
				else
				{
					if ( in_array( 'IPS\Node\Permissions', class_implements( $nodeClass ) ) )
					{
						/* Join collab permissions */
						$joins[] = array( 'from' => array( 'core_permission_index', 'collab_core_permission_index' ), 'where' => array( "collab_core_permission_index.app='" . $nodeClass::$permApp . "' AND collab_core_permission_index.perm_type='collab_" . $nodeClass::$permType . "' AND collab_core_permission_index.perm_type_id=" . $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . $nodeClass::$databaseColumnId ) );				
						
						/* Check only for ALL or guest permission */
						$collabWhere = 
						
							/* Permission for everybody */
							"collab_core_permission_index.perm_" . $nodeClass::$permissionMap[ $permissionKey ] . "='*' OR " . 
							
							/* Guest permission */
							"collab_core_permission_index.perm_" . $nodeClass::$permissionMap[ $permissionKey ] . " LIKE '-1%'";						
					}
				}
				
				/* Some CMS Categories are special in that they might not even have permissions overridden */
				if ( is_subclass_of( $nodeClass, '\IPS\cms\Categories' ) )
				{
					/* Filter by database */
					$databaseWhere = $nodeClass::$databaseTable . '.category_database_id=' . $nodeClass::$customDatabaseId;
					
					/* Filter by permissions */
					$collabWhere = $databaseWhere . " AND ( " . $nodeClass::$databaseTable . ".category_has_perms=0 OR ( {$collabWhere} ) )";				
				}
				
				if ( isset( $collabWhere ) )
				{
					/* Filter by non collab or collab permitted content */
					$where[] = array( '( ( ' . $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . 'collab_id=0 ) OR ( ' . $collabWhere . ' ) )' );
				}
				else
				{
					// No filter necessary since this content doesn't have collab based restrictions
				}
			}
		}
		
		return array
		(
			'where' => $where,
			'joins' => $joins
		);
	}
	
	/**
	 * Users to receive immediate notifications
	 *
	 * @param	int|array		$limit	LIMIT clause
	 * @param	string|NULL		$extra		Additional data
	 * @param	boolean			$countOnly	Just return the count
	 * @return \IPS\Db\Select
	 */
	public function notificationRecipients( $limit=array( 0, 25 ), $extra=NULL, $countOnly=FALSE )
	{
		if ( $this->containerWrapper( TRUE ) and $this->container()->collab_id )
		{
			try
			{
				$collab = \IPS\collab\Collab::load( $this->container()->collab_id );
				$unions = array
				( 
					$collab->followers( 3, array( 'immediate' ), time(), NULL, NULL, NULL ), 
					$this->author()->followers( 3, array( 'immediate' ), $this->mapped( 'date' ), NULL, NULL, NULL ),
					static::containerFollowers( $this->container(), 3, array( 'immediate' ), $this->mapped( 'date' ), NULL, NULL, 0 )
				);
				
				if ( $countOnly )
				{
					try
					{
						return \IPS\Db::i()->union( $unions, 'follow_added', $limit, NULL, FALSE, 0, NULL, 'COUNT(DISTINCT(follow_member_id))' )->first();
					}
					catch( \UnderflowException $e )
					{
						return 0;
					}
				}
				else
				{
					return \IPS\Db::i()->union( $unions, 'follow_added', $limit, NULL, FALSE, \IPS\Db::SELECT_SQL_CALC_FOUND_ROWS )->setKeyField( 'follow_member_id' );
				}
			}
			catch ( \OutOfRangeException $e ) 
			{
				return parent::notificationRecipients( $limit, $extra );
			}
		}
		else
		{
			return parent::notificationRecipients( $limit, $extra );
		}
	}
	
}