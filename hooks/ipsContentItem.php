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
	 * @param	string|NULL	$permissionKey		A key which has a value in the permission map (either of the container or of this class) matching a column ID in core_permission_index or NULL to ignore permissions
	 * @param	bool|NULL	$includeHiddenItems	Include hidden files? Boolean or NULL to detect if currently logged member has permission
	 * @param	int			$queryFlags			Select bitwise flags
	 * @param	\IPS\Member	$member				The member (NULL to use currently logged in member)
	 * @param	bool		$joinContainer		If true, will join container data (set to TRUE if your $where clause depends on this data)
	 * @param	bool		$joinComments		If true, will join comment data (set to TRUE if your $where clause depends on this data)
	 * @param	bool		$joinReviews		If true, will join review data (set to TRUE if your $where clause depends on this data)
	 * @param	bool		$countOnly			If true will return the count
	 * @param	array|null	$joins				Additional arbitrary joins for the query
	 * @return	\IPS\Patterns\ActiveRecordIterator|int
	 */
	public static function getItemsWithPermission( $where=array(), $order=NULL, $limit=10, $permissionKey='read', $includeHiddenItems=NULL, $queryFlags=0, \IPS\Member $member=NULL, $joinContainer=FALSE, $joinComments=FALSE, $joinReviews=FALSE, $countOnly=FALSE, $joins=NULL, $skipPermission=FALSE )
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
		
		return parent::getItemsWithPermission( $where, $order, $limit, $permissionKey, $includeHiddenItems, $queryFlags, $member, $joinContainer, $joinComments, $joinReviews, $countOnly, $joins );
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
		
		return array_merge( parent::vncWhere( $joinContainer, $joins ), $where );
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
					$where[]	= array( static::$databaseTable . "." . static::$databasePrefix . static::$databaseColumnMap[ 'container' ] . ' IN(' . implode( ',', $categories ) . ')' );
				}
				else
				{
					$where[]	= array( static::$databaseTable . "." . static::$databasePrefix . static::$databaseColumnMap[ 'container' ] . '=0' );
				}
			}
		}
		else
		{					 
			if ( ! $member->modPermission( 'can_bypass_collab_permissions' ) )
			{
				/**
				 * Compile a list of all roles for member
				 */
				$all_roles = array( 0 );
				foreach ( new \IPS\Patterns\ActiveRecordIterator( \IPS\Db::i()->select( '*', 'collab_memberships', array( 'status=? AND member_id=?', \IPS\collab\COLLAB_MEMBER_ACTIVE, $member_id ) ), '\IPS\collab\Collab\Membership' ) as $membership )
				{
					$roles = array_map( function( $role ) { return $role->id; }, $membership->roles() );
					$all_roles = array_merge( $all_roles, $roles );
				}
				
				$rolesregexp = implode( '|', $all_roles );
				
				$joins[] = array( 'from' => 'collab_memberships', 'where' => array( 'collab_memberships.member_id=' . $member_id . ' AND collab_memberships.status=\'' . \IPS\collab\COLLAB_MEMBER_ACTIVE . '\' AND collab_memberships.collab_id=' . $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . 'collab_id' ) );
				
				if ( in_array( 'IPS\Node\Permissions', class_implements( $nodeClass ) ) AND $permissionKey !== NULL and ! is_subclass_of( $nodeClass, '\IPS\cms\Categories' ) )
				{
					$joins[] = array( 'from' => 'core_permission_index', 'where' => array( "core_permission_index.app='" . $nodeClass::$permApp . "' AND core_permission_index.perm_type='collab_" . $nodeClass::$permType . "' AND core_permission_index.perm_type_id=" . $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . $nodeClass::$databaseColumnId ) );
					$collabWhere = "core_permission_index.perm_" . $nodeClass::$permissionMap[ $permissionKey ] . "='*' OR ( ISNULL( collab_memberships.id ) AND FIND_IN_SET( '-1', core_permission_index.perm_" . $nodeClass::$permissionMap[ $permissionKey ] . " ) ) OR ( collab_memberships.id IS NOT NULL AND " . 'CONCAT(",", core_permission_index.perm_' . $nodeClass::$permissionMap[ $permissionKey ] . ', ",") REGEXP ",(' . $rolesregexp . ')," )';
				}
				else
				{
					$collabWhere = "collab_memberships.id IS NOT NULL";
				}
				
				$where[] = array( '( ( ' . $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . 'collab_id=0 ) OR ( ' . $collabWhere . ' ) )' );
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
	 * @return \IPS\Db\Select
	 */
	public function notificationRecipients( $limit=array( 0, 25 ), $extra=NULL )
	{
		if ( $this->containerWrapper( TRUE ) and $this->container()->collab_id )
		{
			try
			{
				$collab = \IPS\collab\Collab::load( $this->container()->collab_id );
				return \IPS\Db::i()->union( 
					array
					( 
						$collab->followers( 3, array( 'immediate' ), time(), NULL, NULL, NULL ), 
						$this->author()->followers( 3, array( 'immediate' ), $this->mapped('date'), NULL, NULL, NULL ),
						static::containerFollowers( $this->container(), 3, array( 'immediate' ), $this->mapped('date'), NULL, NULL, 0 )
					), 
				'follow_added', $limit, 'follow_member_id', FALSE, \IPS\Db::SELECT_SQL_CALC_FOUND_ROWS );
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