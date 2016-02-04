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
			 * If the container node is provisioned for collabs, then we either want to limit results to the affective collab,
			 * or limit the results to non-collab content.
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
		
		return parent::getItemsWithPermission( $where, $order, $limit, $permissionKey, $includeHiddenItems, $queryFlags, $member, $joinContainer, $joinComments, $joinReviews, $countOnly, $joins, $skipPermission, $joinTags, $joinAuthor, $joinLastCommenter );
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
	
		if ( $collab = \IPS\collab\Application::affectiveCollab() )
		{
			$where[] = array( $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . 'collab_id=?', $collab->collab_id );
			
			/**
			 * If the container node class uses permissions, we also need to filter by internal collab role permissions
			 */
			if 
			(
				! $member->modPermission( 'can_bypass_collab_permissions' ) and 
				in_array( 'IPS\Node\Permissions', class_implements( $nodeClass ) ) and 
				$permissionKey !== NULL and 
				! is_subclass_of( $nodeClass, '\IPS\cms\Categories' ) 
			)
			{
				$collabRoles = array();
				
				if ( $membership = $collab->getMembership( $member ) )
				{
					/* All Members Role */
					$collabRoles[] = '0';
					
					/* Membership Roles */
					foreach( $membership->roles() as $role )
					{
						$collabRoles[] = $role->id;
					}
				}
				else
				{
					/* Guest Role */
					$collabRoles[] = '-1';
				}

				$categories	= array();
				$lookupKey	= md5( $nodeClass::$permApp . 'collab_' . $nodeClass::$permType . $permissionKey . json_encode( $collabRoles ) );

				if( ! isset( static::$permissionSelect[ $lookupKey ] ) )
				{
					static::$permissionSelect[ $lookupKey ] = array();
					foreach( \IPS\Db::i()->select( 'perm_type_id', 'core_permission_index', array( "core_permission_index.app='" . $nodeClass::$permApp . "' AND core_permission_index.perm_type='collab_" . $nodeClass::$permType . "' AND (" . \IPS\Db::i()->findInSet( 'perm_' . $nodeClass::$permissionMap[ $permissionKey ], $collabRoles ) . ' OR ' . 'perm_' . $nodeClass::$permissionMap[ $permissionKey ] . "='*' )" ) ) as $result )
					{
						static::$permissionSelect[ $lookupKey ][] = $result;
					}
				}

				$categories = static::$permissionSelect[ $lookupKey ];

				if( count( $categories ) )
				{
					
					$where[] = array( static::$databaseTable . "." . static::$databasePrefix . static::$databaseColumnMap[ 'container' ] . ' IN(' . implode( ',', $categories ) . ')' );
				}
				else
				{
					$where[] = array( static::$databaseTable . "." . static::$databasePrefix . static::$databaseColumnMap[ 'container' ] . '=0' );
				}
			}
		}
		else
		{					 
			if ( ! $member->modPermission( 'can_bypass_collab_permissions' ) )
			{
				if ( $member->member_id )
				{
					/* Compile a list of all roles for member */
					$all_roles = array( 0 );
					foreach ( new \IPS\Patterns\ActiveRecordIterator( \IPS\Db::i()->select( '*', 'collab_memberships', array( 'status=? AND member_id=?', \IPS\collab\COLLAB_MEMBER_ACTIVE, $member_id ) ), '\IPS\collab\Collab\Membership' ) as $membership )
					{
						$roles = array_map( function( $role ) { return $role->id; }, $membership->roles() );
						$all_roles = array_merge( $all_roles, $roles );
					}
					
					$rolesregexp = implode( '|', $all_roles );
					
					/* Join collab memberships */
					$joins[] = array( 'from' => 'collab_memberships', 'where' => array( 'collab_memberships.member_id=' . $member_id . ' AND collab_memberships.status=\'' . \IPS\collab\COLLAB_MEMBER_ACTIVE . '\' AND collab_memberships.collab_id=' . $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . 'collab_id' ) );
					
					if ( in_array( 'IPS\Node\Permissions', class_implements( $nodeClass ) ) AND $permissionKey !== NULL and ! is_subclass_of( $nodeClass, '\IPS\cms\Categories' ) )
					{
						/* Check membership based permissions + guest permission */
						$joins[] = array( 'from' => array( 'core_permission_index', 'collab_core_permission_index' ), 'where' => array( "collab_core_permission_index.app='" . $nodeClass::$permApp . "' AND collab_core_permission_index.perm_type='collab_" . $nodeClass::$permType . "' AND collab_core_permission_index.perm_type_id=" . $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . $nodeClass::$databaseColumnId ) );				
						$collabWhere = "collab_core_permission_index.perm_" . $nodeClass::$permissionMap[ $permissionKey ] . "='*' OR ( ISNULL( collab_memberships.id ) AND FIND_IN_SET( '-1', collab_core_permission_index.perm_" . $nodeClass::$permissionMap[ $permissionKey ] . " ) ) OR ( collab_memberships.id IS NOT NULL AND " . 'CONCAT(",", collab_core_permission_index.perm_' . $nodeClass::$permissionMap[ $permissionKey ] . ', ",") REGEXP ",(' . $rolesregexp . ')," )';
					}
					else
					{
						/* Just require an active membership to see collab database content (because its a whole lot easier than the alternative) */
						if ( is_subclass_of( $nodeClass, '\IPS\cms\Categories' ) )
						{
							$collabWhere = "collab_memberships.id IS NOT NULL";
						}
					}
				}
				else
				{
					if ( in_array( 'IPS\Node\Permissions', class_implements( $nodeClass ) ) AND $permissionKey !== NULL and ! is_subclass_of( $nodeClass, '\IPS\cms\Categories' ) )
					{
						/* Check for guest permission */
						$joins[] = array( 'from' => array( 'core_permission_index', 'collab_core_permission_index' ), 'where' => array( "collab_core_permission_index.app='" . $nodeClass::$permApp . "' AND collab_core_permission_index.perm_type='collab_" . $nodeClass::$permType . "' AND collab_core_permission_index.perm_type_id=" . $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . $nodeClass::$databaseColumnId ) );				
						$collabWhere = "collab_core_permission_index.perm_" . $nodeClass::$permissionMap[ $permissionKey ] . "='*' OR FIND_IN_SET( '-1', collab_core_permission_index.perm_" . $nodeClass::$permissionMap[ $permissionKey ] . " )";
					}
					else
					{
						/* Prevent guests from seeing collab database records */
						if ( is_subclass_of( $nodeClass, '\IPS\cms\Categories' ) )
						{
							$collabWhere = "1=0";
						}					
					}
				}
				
				if ( isset( $collabWhere ) )
				{
					$where[] = array( '( ( ' . $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . 'collab_id=0 ) OR ( ' . $collabWhere . ' ) )' );
				}
				else
				{
					$where[] = array( '( ' . $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . 'collab_id=0 )' );
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