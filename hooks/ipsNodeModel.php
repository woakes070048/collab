//<?php

abstract class collab_hook_ipsNodeModel extends _HOOK_CLASS_
{

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
					in_array( get_called_class(), array( 'IPS\collab\Collab\Role' ) ) or
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
				if ( ! ( $settings = $collab->enabledNodes( md5( get_called_class() ) ) ) )
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
						if ( $membership->status === \IPS\collab\COLLAB_MEMBER_ACTIVE )
						{
							/* Give permission based on assigned roles */
							$collabCan = ( $permissions[ 'perm_' . static::$permissionMap[ $permission ] ] === '*' or ( $permissions[ 'perm_' . static::$permissionMap[ $permission ] ] != "" and count( array_intersect( array_merge( array( 0 ), explode( ',', $membership->roles ) ), explode( ',', $permissions[ 'perm_' . static::$permissionMap[ $permission ] ] ) ) ) ) );
						}
						else if ( $membership->status === \IPS\collab\COLLAB_MEMBER_BANNED )
						{
							/* Banned */
							$collabCan = FALSE;
						}
						else
						{
							/* Give the same permission that a guest would get */
							$collabCan = ( $permissions[ 'perm_' . static::$permissionMap[ $permission ] ] === '*' or ( $permissions[ 'perm_' . static::$permissionMap[ $permission ] ] != "" and in_array( '-1', explode( ',', $permissions[ 'perm_' . static::$permissionMap[ $permission ] ] ) ) ) );					
						}
					}
					else
					{
						/* Give guest permission */
						$collabCan = ( $permissions[ 'perm_' . static::$permissionMap[ $permission ] ] === '*' or ( $permissions[ 'perm_' . static::$permissionMap[ $permission ] ] != "" and in_array( '-1', explode( ',', $permissions[ 'perm_' . static::$permissionMap[ $permission ] ] ) ) ) );
					}
				}
			}
		}
		
		return $collabCan and parent::can( $permission, $member );
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
					if ( $this->collab_id and $collab = \IPS\collab\Collab::load( $this->collab_id ) )
					{
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
						catch ( \UnderflowException $e ) {}
					}
				}
				catch ( \OutOfRangeException $e ) {}
			}
		}
		
		return parent::permissions();
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
	public function collabPermissions( $defaults=array( 'perm_view' => '' ) )
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
	 * Get latest content item (if applicable) for a node.
	 *
	 * @param	int	$limit	The limit
	 * @param	int	$offset	The offset
	 * @param	array $additionalWhere Additional where clauses
	 * @return	\IPS\Patterns\ActiveRecordIterator
	 * @throws	\BadMethodCallException
	 */
	public function getLatestContentItem()
	{
		if ( !isset( static::$contentItemClass ) )
		{
			return NULL;
		}
		
		$itemClass 	= static::$contentItemClass;
		$prefix 	= $itemClass::$databasePrefix;
		$mapped		= $itemClass::$databaseColumnMap;
		
		$_where		= array( array( $prefix . $mapped['container'] . '=?', $this->_id ) );
		$_order 	= $prefix . $mapped[ 'date' ] . ' DESC';	
		$_limit 	= array( 0, 1 );
		
		foreach ( new \IPS\Patterns\ActiveRecordIterator( \IPS\Db::i()->select( '*', $itemClass::$databaseTable, $_where, $_order, $_limit ), $itemClass ) as $content )
		{
			return $content;
		}
		
		return NULL;
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
			if ( $this->_new )
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
		\IPS\Db::i()->delete( 'core_permission_index', array( "app=? AND perm_type=? AND perm_type_id=?", static::$permApp, 'collab_' . static::$permType, $this->_id ) );	
		return parent::delete();
	}	

}