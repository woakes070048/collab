//<?php

abstract class collab_hook_ipsContentComment extends _HOOK_CLASS_
{

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
	 * @return	\IPS\Patterns\ActiveRecordIterator|int
	 */
	public static function getItemsWithPermission( $where=array(), $order=NULL, $limit=10, $permissionKey='read', $includeHiddenItems=NULL, $queryFlags=0, \IPS\Member $member=NULL, $joinContainer=FALSE, $joinComments=FALSE, $joinReviews=FALSE, $countOnly=FALSE, $joins=NULL )
	{	
		if ( isset( static::$itemClass ) )
		{
			$itemClass = static::$itemClass;
			
			if ( isset( $itemClass::$containerNodeClass ) )
			{
				$nodeClass = $itemClass::$containerNodeClass;

				/**
				 * If the container node is provisioned for collabs, then we either want to limit results to the affective collab,
				 * or limit the results to non-collab content.
				 */
				if ( \IPS\Db::i()->checkForColumn( $nodeClass::$databaseTable, $nodeClass::$databasePrefix . 'collab_id' ) )
				{
					$member = $member ?: \IPS\Member::loggedIn();
					$joinContainer = TRUE;
					
					/**
					 * @BUGFIX:
					 * 
					 * Bug in IPS core causes join clauses using the join container to fail. Need to work around it...
					 * see: https://community.invisionpower.com/4bugtrack/active-reports/db-query-error-getitemswithpermission-r8889/
					 */
					$joinContainer = FALSE;
					$joins = array_merge
					( 
						array
						( 
							array
							(
								'from'	=> $nodeClass::$databaseTable,
								'where'	=> array( array( $itemClass::$databaseTable . '.' . $itemClass::$databasePrefix . $itemClass::$databaseColumnMap[ 'container' ] . '=' . $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . $nodeClass::$databaseColumnId ) )
							), 
						), 
						$joins ?: array() 
					);
					/* END BUGFIX */
					
					$collabClause = $itemClass::collabPermissionWhere( $member, $nodeClass, $permissionKey );

					$where = array_merge( $where ?: array(), $collabClause[ 'where' ] );
					$joins = array_merge( $joins ?: array(), $collabClause[ 'joins' ] );
				}
			}
		}
		
		return parent::getItemsWithPermission( $where, $order, $limit, $permissionKey, $includeHiddenItems, $queryFlags, $member, $joinContainer, $joinComments, $joinReviews, $countOnly, $joins );
	}
	
}