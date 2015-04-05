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
	public static function getItemsWithPermission( $where=array(), $order=NULL, $limit=10, $permissionKey='read', $includeHiddenItems=NULL, $queryFlags=0, \IPS\Member $member=NULL, $joinContainer=FALSE, $joinComments=FALSE, $joinReviews=FALSE, $countOnly=FALSE, $joins=NULL )
	{	
		if ( $nodeClass = static::$containerNodeClass )
		{
			/**
			 * If the container node is provisioned for collabs, then we either want to limit results to the affective collab,
			 * or limit the results to non-collab content.
			 */
			if ( \IPS\Db::i()->checkForColumn( $nodeClass::$databaseTable, $nodeClass::$databasePrefix . 'collab_id' ) )
			{
				$joinContainer = TRUE;
				if ( $collab = \IPS\collab\Application::affectiveCollab() )
				{
					$where[] = array( $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . 'collab_id=?', $collab->collab_id );
					
					/**
					 * If the container node class uses permissions, we also need to filter by internal collab role permissions
					 */
					if ( in_array( 'IPS\Node\Permissions', class_implements( $nodeClass ) ) AND $permissionKey !== NULL )
					{
						$containerClass = static::$containerNodeClass;

						$member = $member ?: \IPS\Member::loggedIn();
						$collabRoles = array();
						
						if ( $membership = $collab->getMembership( $member ) )
						{
							/* All Members Role */
							$collabRoles[] = 0;
							
							/* Membership Roles */
							foreach( $membership->roles() as $role )
							{
								$collabRoles[] = $role->id;
							}
						}
						else
						{
							/* Guest Role */
							$collabRoles[] = -1;
						}

						$categories	= array();
						$lookupKey	= md5( $containerClass::$permApp . 'collab_' . $containerClass::$permType . $permissionKey . json_encode( $collabRoles ) );

						if( ! isset( static::$permissionSelect[ $lookupKey ] ) )
						{
							static::$permissionSelect[ $lookupKey ] = array();
							foreach( \IPS\Db::i()->select( 'perm_type_id', 'core_permission_index', array( "core_permission_index.app='" . $containerClass::$permApp . "' AND core_permission_index.perm_type='collab_" . $containerClass::$permType . "' AND (" . \IPS\Db::i()->findInSet( 'perm_' . $containerClass::$permissionMap[ $permissionKey ], $collabRoles ) . ' OR ' . 'perm_' . $containerClass::$permissionMap[ $permissionKey ] . "='*' )" ) ) as $result )
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
					$where[] = array( $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . 'collab_id=0' );
				}
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
		
		if ( $nodeClass = static::$containerNodeClass )
		{
			/**
			 * If the container node is provisioned for collabs, then we either want to limit results to the affective collab,
			 * or limit the results to non-collab content.
			 */
			if ( \IPS\Db::i()->checkForColumn( $nodeClass::$databaseTable, $nodeClass::$databasePrefix . 'collab_id' ) )
			{
				//$joinContainer = TRUE;
				/**
				 * If the container node class uses permissions, we also need to filter by internal collab role permissions
				 */
				if ( in_array( 'IPS\Node\Permissions', class_implements( $nodeClass ) ) AND $permissionKey !== NULL )
				{
					$containerClass = static::$containerNodeClass;

					$member = \IPS\Member::loggedIn();

					/**
					 * @TODO: write one hell of a sql query that checks collab membership permissions on the container nodes
					 */
					$categories = array( 0 );
					//$where[] = array( $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . 'collab_id=0' . ' OR ' . static::$databaseTable . "." . static::$databasePrefix . static::$databaseColumnMap[ 'container' ] . ' IN(' . implode( ',', $categories ) . ')' );
				}
			}
		}
		
		return array_merge( parent::vncWhere( $joinContainer, $joins ), $where );
	}
	
}