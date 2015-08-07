//<?php

abstract class collab_hook_ipsNodeModel extends _HOOK_CLASS_
{

	/**
	 * @brief	Collab permissions cache
	 */
	protected $collabPermissions = NULL;

	/**
	 * Fetch All Root Nodes
	 *
	 * @param	string|NULL			$permissionCheck	The permission key to check for or NULl to not check permissions
	 * @param	\IPS\Member|NULL	$member				The member to check permissions for or NULL for the currently logged in member
	 * @param	mixed				$where				Additional WHERE clause
	 * @return	array
	 */
	static public function roots( $permissionCheck='view', $member=NULL, $where=array() )
	{
		if ( \IPS\Db::i()->checkForColumn( static::$databaseTable, static::$databasePrefix . 'collab_id' ) )
		{
			if ( $collab = \IPS\collab\Application::affectiveCollab() )
			{
				if 
				(
					in_array( get_called_class(), \IPS\collab\Application::$internalNodes ) or
					$collab->enabledNodes( md5( get_called_class() ) ) 
				)
				{
					$where[] = array( static::$databasePrefix . 'collab_id=?', $collab->collab_id );
				}
				else
				{
					return array();
				}
			}
			else
			{
				$where[] = array( static::$databasePrefix . 'collab_id=?', 0 );
			}
		}
		
		return call_user_func( 'parent::roots', $permissionCheck, $member, $where );
	}

	/**
	 * ACP Restrictions Check
	 *
	 * @param	string	$key	Restriction key to check
	 * @return	bool
	 */
	static protected function restrictionCheck( $key )
	{
		$collabCan = FALSE;
		if ( $collab = \IPS\collab\Application::activeCollab( false ) )
		{
			$nid = md5( get_called_class() );
			$node_id = 'node_' . $nid;
			switch ( $key )
			{
				case 'view':
				
					$exploded = explode( '\\', get_called_class() );
					$collabCan = $collab->collabCan( 'appManage-' . $exploded[1] );
					break;
					
				case 'add':
				
					if ( $collab->container()->_options[ 'node_' . $nid ][ 'enable_add' ] and $collab->collabCan( 'nodeAdd-' . $nid ) )
					{
						/* Check if we're at maximum capacity already */
						$options = $collab->container()->_options;
						if ( isset( $options[ $node_id ][ 'maxnodes' ] ) and $options[ $node_id ][ 'maxnodes' ] > 0 )
						{
							if ( $config = $collab->enabledNodes( $nid ) )
							{
								$nodeClass = $config[ 'node' ];
								try
								{
									$existing = \IPS\Db::i()->select( 'COUNT(*)', $nodeClass::$databaseTable, array( $nodeClass::$databasePrefix . 'collab_id=?', $collab->collab_id ) )->first();
								}
								catch ( \UnderflowException $e )
								{
									$existing = 0;
								}
								$collabCan = ( $existing < $options[ $node_id ][ 'maxnodes' ] );
							}
						}
						else
						{
							$collabCan = TRUE;
						}
					}
					break;
					
				case 'edit':
				
					$collabCan = $collab->container()->_options[ 'node_' . $nid ][ 'enable_edit' ] and $collab->collabCan( 'nodeEdit-' . $nid );
					break;
					
				case 'delete':
				
					$collabCan = $collab->container()->_options[ 'node_' . $nid ][ 'enable_delete' ] and $collab->collabCan( 'nodeDelete-' . $nid );
					break;
					
				case 'permissions':
				
					$collabCan = (
						( $collab->container()->_options[ 'node_' . $nid ][ 'enable_add' ] and $collab->collabCan( 'nodeAdd-' . $nid ) ) or 
						( $collab->container()->_options[ 'node_' . $nid ][ 'enable_edit' ] and $collab->collabCan( 'nodeEdit-' . $nid ) )
					);
					break;
			}
		}
		return $collabCan or call_user_func_array( 'parent::restrictionCheck', func_get_args() );
	}

	/**
	 * Check permissions
	 *
	 * @param	mixed								$permission		A key which has a value in static::$permissionMap['view'] matching a column ID in core_permission_index
	 * @param	\IPS\Member|\IPS\Member\Group|NULL	$member			The member or group to check (NULL for currently logged in member)
	 * @return	bool
	 * @throws	\OutOfBoundsException	If $permission does not exist in static::$permissionMap
	 */
	public function can( $permission, $member=NULL )
	{	
		return $this->canCollab( $permission, $member ) and parent::can( $permission, $member );
	}
	
	/**
	 * Check permissions
	 *
	 * @param	mixed								$permission		A key which has a value in static::$permissionMap['view'] matching a column ID in core_permission_index
	 * @param	\IPS\Member|\IPS\Member\Group|NULL	$member			The member or group to check (NULL for currently logged in member)
	 * @return	bool
	 * @throws	\OutOfBoundsException	If $permission does not exist in static::$permissionMap
	 */
	public function canCollab( $permission, $member=NULL )
	{
		$member 	= $member ?: \IPS\Member::loggedIn();
		$collabCan 	= TRUE;
		
		/**
		 * The obvious way to check if this node is owned by a collab
		 */
		if ( $this->collab_id )
		{
			/* Assume we can't do anything until we discover otherwise */
			$collabCan = FALSE;
			
			/**
			 * Entire member groups cannot be given access to collabs
			 */
			if ( $member instanceof \IPS\Member\Group )
			{
				return FALSE;
			}
		
			try
			{
				$collab = \IPS\collab\Collab::load( $this->collab_id );
				if 
				( 
					! $collab->enabledNodes( md5( get_called_class() ) ) and
					! in_array( get_called_class(), \IPS\collab\Application::$internalNodes )
				)
				{
					return FALSE;
				}
			}
			catch ( \OutOfRangeException $e ) 
			{
				return FALSE;
			}
			
			/**
			 * Some moderators may be able to bypass collab permissions
			 */
			if ( $member->modPermission( 'can_bypass_collab_permissions' ) )
			{
				$collabCan = TRUE;
			}
		
			/* If a collab is hidden, then hide all of it's content too */
			if ( $permission == 'view' and $collab->hidden() and ! \IPS\collab\Collab::modPermission( 'view_hidden', $member, $collab->container() ) )
			{
				return FALSE;
			}
			
			/* If a collab is locked, then lock all of it's content too */
			if ( $permission == 'add' and $collab->locked() and ! \IPS\collab\Collab::modPermission( 'reply_to_locked', $member, $collab->container() ) )
			{
				return FALSE;
			}
			
			/**
			 * Check collab permissions if we're not already authorized
			 */
			if ( ! $collabCan )
			{
				/* If this is not permission-dependant, return TRUE */
				if ( 
					! ( $this instanceof \IPS\Node\Permissions ) or
					
					/** 
					 * IPS Databases Compatibility 
					 * Categories implement permissions, but have a soft option to disable them.
					 */
					( 
						$this instanceof \IPS\cms\Categories and
						! $this->has_perms
					)
				)
				{
					$collabCan = TRUE;
				}
				else
				{
					$permissions = $this->collabPermissions();

					if( $membership = $collab->getMembership( $member ) )
					{
						$roles = array_map( function( $role ) { return $role->id; }, $membership->roles() );
					
						if ( $membership->status === \IPS\collab\COLLAB_MEMBER_ACTIVE )
						{
							/* Give permission based on assigned roles */
							$collabCan = ( $permissions[ 'perm_' . static::$permissionMap[ $permission ] ] === '*' or ( $permissions[ 'perm_' . static::$permissionMap[ $permission ] ] != "" and count( array_intersect( array_merge( array( 0 ), $roles ), explode( ',', $permissions[ 'perm_' . static::$permissionMap[ $permission ] ] ) ) ) ) );
						}
						else if ( $membership->status === \IPS\collab\COLLAB_MEMBER_BANNED )
						{
							/* Banned */
							$collabCan = FALSE;
						}
						else
						{
							/* Invited or pending members get the same permission that a guest would get */
							$collabCan = ( $permissions[ 'perm_' . static::$permissionMap[ $permission ] ] === '*' or ( $permissions[ 'perm_' . static::$permissionMap[ $permission ] ] != "" and in_array( '-1', explode( ',', $permissions[ 'perm_' . static::$permissionMap[ $permission ] ] ) ) ) );					
						}
					}
					else
					{
						/* Collab Guest */
						$collabCan = ( $permissions[ 'perm_' . static::$permissionMap[ $permission ] ] === '*' or ( $permissions[ 'perm_' . static::$permissionMap[ $permission ] ] != "" and in_array( '-1', explode( ',', $permissions[ 'perm_' . static::$permissionMap[ $permission ] ] ) ) ) );
					}
				}
			}
		}
		
		return $collabCan;
	}

	/**
	 * Reset Permissions
	 */
	public function clearPermissions()
	{
		$this->_permissions = NULL;
		\IPS\Db::i()->delete( 'core_permission_index', array( 'app=? AND perm_type=? AND perm_type_id=?', static::$permApp, static::$permType, $this->_id ) );
	}
	
	/**
	 * Get corporate permissions
	 *
	 * @return	array
	 */
	public function permissions()
	{
		if ( $this->_permissions === NULL )
		{
			try
			{
				// Try a standard loading of existing permissions on this node
				$this->_permissions = \IPS\Db::i()->select( array( 'perm_id', 'perm_view', 'perm_2', 'perm_3', 'perm_4', 'perm_5', 'perm_6', 'perm_7' ), 'core_permission_index', array( "app=? AND perm_type=? AND perm_type_id=?", static::$permApp, static::$permType, $this->_id ) )->first();
			}
			catch ( \UnderflowException $e )
			{
				// If none exist, and this is a collab node, then we create them automatically based on collab category defaults
				try
				{
					if ( $this->collab_id )
					{
						$collab = \IPS\collab\Collab::load( $this->collab_id );
					
						try
						{
							/**
							 * This grabs the default permission set for this node type for this collab category
							 */
							$permissions = \IPS\Db::i()->select( array( 'perm_view', 'perm_2', 'perm_3', 'perm_4', 'perm_5', 'perm_6', 'perm_7' ), 'core_permission_index', array( "app=? AND perm_type=? AND perm_type_id=?", 'collab', static::$permApp . '_' . static::$permType, $collab->container()->_id ) )->first();
							
							// Now save it to the node
							$permissions[ 'app' ] 		= static::$permApp;
							$permissions[ 'perm_type' ] 	= static::$permType;
							$permissions[ 'perm_type_id' ] 	= $this->_id;
							$this->setPermissions( $permissions, new \IPS\Helpers\Form\Matrix );
						}
						catch ( \UnderflowException $e ) { }
					}
				}
				catch ( \OutOfRangeException $e ) { }
			}
		}
		
		return parent::permissions();
	}

	/**
	 * [Node] Get buttons to display in tree
	 * Example code explains return value
	 *
	 * @param	string	$url		Base URL
	 * @param	bool	$subnode	Is this a subnode?
	 * @return	array
	 */
	public function getButtons( $url, $subnode=FALSE )
	{
		$buttons = parent::getButtons( $url, $subnode );
		
		/**
		 * Check if this node is provisioned for collabs
		 */
		if 
		( 
			\IPS\Db::i()->checkForColumn( static::$databaseTable, static::$databasePrefix . 'collab_id' ) and
			! in_array( get_class( $this ), \IPS\collab\Application::$internalNodes )
		)
		{
			if ( $this->collab_id )
			{
				if ( \IPS\Member::loggedIn()->hasAcpRestriction( 'collab', 'collab' ) )
				{
					$buttons[ 'collab_extract' ] = array
					(
						'icon'	=> 'arrow-left',
						'title'	=> 'collab_extract_from_collab',
						'link'	=> $url->setQueryString( array( 'do' => 'collabExtract', 'id' => $this->_id ) ),
						'data'  => array( 'confirm' => TRUE ),					
					);
				}
			}
			else
			{
				$buttons[ 'collab_copy' ] = array
				(
					'icon'	=> 'files-o',
					'title'	=> 'collab_copy_to_collab',
					'link'	=> $url->setQueryString( array( 'do' => 'collabCopy', 'id' => $this->_id ) ),
					'data'  => array( 'ipsDialog' => TRUE, 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'collab_copy_to_collab' ) ),
				);
				
				$buttons[ 'collab_move' ] = array
				(
					'icon'	=> 'arrow-right',
					'title'	=> 'collab_move_to_collab',
					'link'	=> $url->setQueryString( array( 'do' => 'collabMove', 'id' => $this->_id ) ),
					'data'  => array( 'ipsDialog' => TRUE, 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack( 'collab_move_to_collab' ) ),
				);
			}
		}
		
		return $buttons;
	}
	
	/**
	 * [Node] Does the currently logged in user have permission to edit this node?
	 *
	 * @return	bool
	 */
	public function canEdit()
	{
		$addNotEdit = FALSE;
		if ( $this->_new ) 
		{
			$addNotEdit = static::restrictionCheck( 'add' );
		}
		return $addNotEdit or call_user_func_array( 'parent::canEdit', func_get_args() );
	}
	
	/**
	 * Get collab specific permissions
	 *
	 * @return	array
	 */
	public function collabPermissions( $defaults=array( 'perm_view' => '0', 'perm_2' => '0', 'perm_3' => '0', 'perm_4' => '0', 'perm_5' => '0', 'perm_6' => '0', 'perm_7' => '0' ) )
	{
		if ( isset ( $this->collabPermissions ) )
		{
			return $this->collabPermissions;
		}
		
		if ( $this->_id )
		{
			try
			{
				// Try a standard loading of existing permissions
				$permissions = \IPS\Db::i()->select( array( 'perm_id', 'perm_view', 'perm_2', 'perm_3', 'perm_4', 'perm_5', 'perm_6', 'perm_7' ), 'core_permission_index', array( "app=? AND perm_type=? AND perm_type_id=?", static::$permApp, 'collab_' . static::$permType, $this->_id ) )->first();
			}
			catch ( \UnderflowException $e )
			{
				\IPS\Db::i()->insert( 'core_permission_index', array_merge( $defaults, array(
					'app'			=> static::$permApp,
					'perm_type'		=> 'collab_' . static::$permType,
					'perm_type_id'		=> $this->_id,
					) ) );
					
				return $this->collabPermissions();
			}
			
			return $this->collabPermissions = $permissions;
		}
		
		return $defaults;
	}
	
	/**
	 * Set Collab Related Permissions
	 *
	 * @param	array		$insert		Permission data to insert
	 * @return  	void
	 */
	public function setCollabPermissions( $insert )
	{
		$insert[ 'app' ] 		= static::$permApp;
		$insert[ 'perm_type' ] 		= 'collab_' . static::$permType;
		$insert[ 'perm_type_id' ]	= $this->_id;
		
		/* Delete Existing Permissions */
		\IPS\Db::i()->delete( 'core_permission_index', array( 'app=? AND perm_type=? AND perm_type_id=?', $insert[ 'app' ], $insert[ 'perm_type' ], $insert[ 'perm_type_id' ] ) );
		
		/* Recreate Permissions */
		\IPS\Db::i()->insert( 'core_permission_index', $insert );
		
		/* Reload the collab permissions cache */
		$this->collabPermissions = NULL;
		$this->collabPermissions();
		
		/* Run core node permissions updates */
		$this->setPermissions( array_merge( array( 'app' => static::$permApp, 'perm_type' => static::$permType, 'perm_type_id' => $this->_id ), $this->permissions() ), new \IPS\Helpers\Form\Matrix );
	}

	/**
	 * Retrieve the computed permissions
	 *
	 * @param	\IPS\Node\Model	$node	Node
	 * @return	string
	 */
	protected static function _getPermissions( $node )
	{
		$permissions = explode( ',', parent::_getPermissions( $node ) );
		
		/**
		 * Add In Collab Permissions
		 */
		if( $node instanceof \IPS\Node\Permissions and $node->collab_id )
		{
			$collabPermissions 	= $node->collabPermissions();
			$permissionTypes 	= $node->permissionTypes();
			$perms			= array();
			
			/* Compare both read and view */

			if( ! isset( $permissionTypes[ 'read' ] ) )
			{
				$perms = explode( ',', $collabPermissions[ 'perm_' . $permissionTypes[ 'view' ] ] );
			}
			else
			{
				if( $collabPermissions[ 'perm_' . $permissionTypes[ 'view' ] ] == '*' )
				{
					$perms = explode( ',', $collabPermissions[ 'perm_' . $permissionTypes[ 'read' ] ] );
				}
				else if( $collabPermissions[ 'perm_' . $permissionTypes[ 'read' ] ] == '*' )
				{
					$perms = explode( ',', $collabPermissions[ 'perm_' . $permissionTypes[ 'view' ] ] );
				}
				else
				{
					$perms = array_intersect( explode( ',', $collabPermissions[ 'perm_' . $permissionTypes[ 'view' ] ] ), explode( ',', $collabPermissions[ 'perm_' . $permissionTypes[ 'read' ] ] ) );
				}
			}

			if ( ! /*not*/
			( 
				in_array( '*', $perms ) or 
				( 
					in_array( '0', $perms ) and 
					in_array( '-1', $perms ) 
				) 
			) )
			{
				/* Indicates collab permissions are required */
				$permissions[] = 'c';
				
				foreach( $perms as $role_id )
				{
					switch( $role_id )
					{
						case '-1':
							
							$permissions[] = 'cg' . $node->collab_id;
							break;
							
						case '0':
						
							$permissions[] = 'cm' . $node->collab_id;
							break;
							
						default:
							
							if ( $role_id )
							{
								$permissions[] = 'cr' . $role_id;
							}
					}
				}
			}
		}
		
		return implode( ',', $permissions );
	}	

	/**
	 * Get latest content item (if applicable) for a node.
	 *
	 * @param	int	$limit	The limit
	 * @param	int	$offset	The offset
	 * @param	array $additionalWhere Additional where clauses
	 * @return	\IPS\Patterns\ActiveRecordIterator
	 * @throws	\BadMethodCallException
	 */
	public function getLatestActivityItem()
	{
		if ( !isset( static::$contentItemClass ) )
		{
			return NULL;
		}
		
		$itemClass 	= static::$contentItemClass;
		$prefix 	= $itemClass::$databasePrefix;
		$mapped		= $itemClass::$databaseColumnMap;
		
		$_select 	= array( $itemClass::$databaseTable . '.*' );
		$_where		= array( array( $prefix . $mapped[ 'container' ] . '=?', $this->_id ) );
		$_limit 	= array( 0, 1 );
		
		if ( $latest = \IPS\collab\Collab::latestContentSQL( $itemClass ) )
		{
			$_select[] 	= "{$latest} as latest_activity_date";
			$_order		= 'latest_activity_date DESC';
		}
		else
		{
			$_order 	= $prefix . $mapped[ 'date' ] . ' DESC';
		}		
		
		foreach ( new \IPS\Patterns\ActiveRecordIterator( \IPS\Db::i()->select( implode( ', ', $_select ), $itemClass::$databaseTable, $_where, $_order, $_limit ), $itemClass ) as $content )
		{
			return $content;
		}
		
		return NULL;
	}

	/**
	 * Cache counts for content items and posts inside collabs
	 *
	 * @param	string	$k	Key
	 * @param	mixed	$v	Value
	 * @return	mixed
	 */
	public function __set( $k, $v )
	{
		if( in_array( $k, array( '_items', '_comments' ) ) )
		{
			if ( $this->collab_id and ! ( $this instanceof \IPS\collab\Collab ) )
			{
				try
				{
					$collab	= \IPS\collab\Collab::load( $this->collab_id );
					$nid 	= md5( get_called_class() );
					
					if ( $collab->enabledNodes( $nid ) )
					{
						$data = $collab->collab_data;
						
						/**
						 * If no count has been recorded yet to the collab data, count it all
						 */
						if ( ! isset( $data[ $k ] ) )
						{
							$result = parent::__set( $k, $v );
							$data[ $k ] = $collab->countTotals( $k );
							$collab->collab_data = $data;
							$collab->save();
							return $result;
						}
						
						/**
						 * Otherwise, save some cpu cycles by just adjusting the existing counts
						 */
						else
						{
							$existing = (int) $this->$k;
							
							$data[ $k ][ 'node_totals' ][ $nid ] = $data[ $k ][ 'node_totals' ][ $nid ] - $existing + $v;
							$data[ $k ][ 'grand_total' ] = $data[ $k ][ 'grand_total' ] - $existing + $v;
							
							$collab->collab_data = $data;
							$collab->save();
						}
					}
				}
				catch ( \OutOfRangeException $e ) { }
			}			
		}

		return parent::__set( $k, $v );
	}

	/**
	 * [ActiveRecord] Save Changed Columns
	 *
	 * @return	void
	 */
	public function save()
	{
		$collab = FALSE;
		if ( \IPS\Db::i()->checkForColumn( static::$databaseTable, static::$databasePrefix . 'collab_id' ) )
		{
			if ( $this->_new and ! isset( $this->_data[ 'collab_id' ] ) )
			{
				if ( $collab = \IPS\collab\Application::affectiveCollab() )
				{
					$this->collab_id = $collab->collab_id;
				}
				else
				{
					$this->collab_id = 0;
				}
			}
		}
		parent::save();
		
		if ( $this instanceof \IPS\Node\Permissions )
		{
			if ( $collab and \IPS\collab\Application::collabOptions( md5( get_called_class() ) ) )
			{
				/** 
				 * This will ensure permissions are set for this node, and if it is a collab node,
				 * then permissions will be created based on the defaults set for the category
				 */
				$this->permissions();
			}
		}
	}
	
	/**
	 * Load and check permissions
	 *
	 * @param	mixed	$id		ID
	 * @param	string	$perm	Permission Key
	 * @return	static
	 * @throws	\OutOfRangeException
	 */
	public static function loadAndCheckPerms( $id, $perm='view' )
	{
		/**
		 *  If we're loading and checking permission, then this node is pretty important...
		 *  and if it belongs to a collab, then our object stack is our fallback in case we can't infer
		 */
		return \IPS\collab\Application::collabObjStack( call_user_func_array( 'parent::loadAndCheckPerms', func_get_args() ) );
	}

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
	 * [ActiveRecord] Delete Record
	 *
	 * @return	void
	 */
	public function delete()
	{
		/* Note: Content items are deleted before the node which should update item/comment totals, so they don't need to be recalculated here. */
		 
		/* Delete collab permissions */
		\IPS\Db::i()->delete( 'core_permission_index', array( "app=? AND perm_type=? AND perm_type_id=?", static::$permApp, 'collab_' . static::$permType, $this->_id ) );
		
		return parent::delete();
	}	

}