//<?php

class collab_hook_ipsNodeController extends _HOOK_CLASS_
{

	/**
	 * Public NodeClass Getter
	 *
	 * @return	void
	 */
	public function _getNodeClass()
	{
		return $this->nodeClass;
	}
  
	/**
	 * Permissions
	 *
	 * @return	void
	 */
	protected function permissions()
	{

		$nodeClass = $this->nodeClass;
		if ( \IPS\Request::i()->subnode )
		{
			$nodeClass = $nodeClass::$subnodeClass;
		}
		$node = NULL;
		
		if ( \IPS\Request::i()->id )
		{
			try
			{
				$node = call_user_func( "{$nodeClass}::load", \IPS\Request::i()->id );
				\IPS\Output::i()->title = $node->_title;
			}
			catch ( \OutOfRangeException $e )
			{
				\IPS\Output::i()->error( 'node_error', '2S101/M', 404, '' );
			}
		}

		if ( ! $node->collab_id )
		{
			// Send to stock permissions for non-collab nodes
			return parent::permissions();
		}

		/* Build a collab/role based permission matrix */
		try
		{
			$collab = \IPS\collab\Collab::loadAndCheckPerms( $node->collab_id );
			if ( ! ( $settings = $collab->enabledNodes( md5( $nodeClass ) ) ) )
			{
				\IPS\IPS\Output::i()->error( 'collab_node_unavailable', '2CP01/B', 404, '' );
			}
		}
		catch ( \OutOfRangeException $e ) 
		{
			\IPS\IPS\Output::i()->error( 'collab_not_found', '2CP01/B', 404, '' );
		}
		
		/* Check permission */
		if( ! $node->canManagePermissions() )
		{
			\IPS\Output::i()->error( 'node_noperm_edit', '2S101/O', 403, '' );
		}
		
		/* Get current permissions */
		try
		{
			$current = \IPS\Db::i()->select( '*', 'core_permission_index', array( 'app=? AND perm_type=? AND perm_type_id=?', $nodeClass::$permApp, 'collab_' . $nodeClass::$permType, $node->_id ) )->first();
		}
		catch( \UnderflowException $e )
		{
			/* Recommended permissions */
			$current = array();
			foreach ( $nodeClass::$permissionMap as $k => $v )
			{
				switch ( $k )
				{
					case 'view':
					case 'read':
						$current["perm_{$v}"] = '*';
						break;
						
					case 'add':
					case 'reply':
					case 'review':
					case 'upload':
					case 'download':
					default:
						$current["perm_{$v}"] = implode( ',', array_merge( array( 0 ), array_keys( $collab->roles() ) ) );
						break;
				}
			}
		}
		
		/* Build Matrix */
		$matrix = new \IPS\Helpers\Form\Matrix;
		$matrix->manageable = FALSE;
		$matrix->langPrefix = $nodeClass::$permissionLangPrefix . 'perm__';
		$matrix->columns = array
		(
			'label'	=> function( $key, $value, $data )
			{
				return $value;
			},
		);
		
		foreach ( $node->permissionTypes() as $k => $v )
		{
			$matrix->columns[ $k ] = function( $key, $value, $data ) use ( $current, $k, $v )
			{
				$groupId = mb_substr( $key, 0, -( 2 + mb_strlen( $k ) ) );
				return new \IPS\Helpers\Form\Checkbox( $key, isset( $current[ "perm_{$v}" ] ) and ( $current[ "perm_{$v}" ] === '*' or in_array( $groupId, explode( ',', $current[ "perm_{$v}" ] ) ) ) );
			};
			$matrix->checkAlls[ $k ] = ( $current[ "perm_{$v}" ] === '*' );
		}
		
		$matrix->checkAllRows = TRUE;
		
		$rows = array
		(
			'-1' => array( 'label' => 'collab_role_guests', 'view' => TRUE ),
			'0' => array( 'label' => 'collab_role_members', 'view' => TRUE ),
		);
		
		foreach ( $collab->roles() as $role )
		{
			$rows[ $role->id ] = array(
				'label'	=> $role->name,
				'view'	=> TRUE,
			);
		}
		
		$matrix->rows = $rows;
		
		/* Handle submissions */
		if ( $values = $matrix->values() )
		{
			$_perms = array();
			
			/* Check for "all" checkboxes */
			foreach ( $nodeClass::$permissionMap as $k => $v )
			{
				if ( isset( \IPS\Request::i()->__all[ $k ] ) )
				{
					$_perms[ $v ] = '*';
				}
				else
				{
					$_perms[ $v ] = array();
				}
			}
			
			/* Prepare insert */
			$insert = array( 'app' => $nodeClass::$permApp, 'perm_type' => 'collab_' . $nodeClass::$permType, 'perm_type_id' => $node->_id );
			if ( isset( $current['perm_id'] ) )
			{
				$insert['perm_id'] = $current['perm_id'];
			}
			
			/* Loop groups */
			foreach ( $values as $group => $perms )
			{
				foreach ( $nodeClass::$permissionMap as $k => $v )
				{
					if ( isset( $perms[ $k ] ) and $perms[ $k ] and is_array( $_perms[ $v ] ) )
					{
						$_perms[ $v ][] = $group;
					}
				}
			}
			
			/* Finalize */
			foreach ( $_perms as $k => $v )
			{
				$insert[ "perm_{$k}" ] = is_array( $v ) ? implode( $v, ',' ) : $v;
			}
			
			/* Set the collab permissions */
			$node->setCollabPermissions( $insert );

			unset( \IPS\Data\Store::i()->modules );

			/* Clear out member's cached "Create Menu" contents */
			\IPS\Member::clearCreateMenu();
			
			/* Redirect */
			\IPS\Output::i()->redirect( $this->url->setQueryString( array( 'root' => ( $node->parent() ? $node->parent()->_id : '' ) ) ), 'saved' );
		}
		
		/* Display */
		\IPS\collab\Application::$collabPageTitle = \IPS\Output::i()->title;
		\IPS\Output::i()->output .= $matrix;
	}

	/**
	 * Copy node into a collab
	 */
	protected function collabCopy()
	{
		$nodeClass = $this->nodeClass;
		if ( \IPS\Request::i()->subnode )
		{
			$nodeClass = $nodeClass::$subnodeClass;
		}
		$node = NULL;
		
		if ( ! \IPS\Db::i()->checkForColumn( $nodeClass::$databaseTable, $nodeClass::$databasePrefix . 'collab_id' ) )
		{
			\IPS\Output::i()->error( 'node_error', '2S100/Z', 404, '' );
		}
		
		if ( \IPS\Request::i()->id )
		{
			try
			{
				$node = call_user_func( "{$nodeClass}::load", \IPS\Request::i()->id );
				\IPS\Output::i()->title = $node->_title;
			}
			catch ( \OutOfRangeException $e )
			{
				\IPS\Output::i()->error( 'node_error', '2S101/Z', 404, '' );
			}
		}
		
		$form = new \IPS\Helpers\Form( 'copy_to_collab' );
		
		$form->add( new \IPS\Helpers\Form\Number( 'collab_id_select', 0, TRUE, array(), function( $val )
		{
			try
			{
				$collab = \IPS\collab\Collab::load( $val );
			}
			catch ( \OutOfRangeException $e )
			{
				throw new \InvalidArgumentException( 'Invalid Collab ID' );
			}
		} ) );
		
		if ( $values = $form->values() )
		{
			\IPS\Member::loggedIn()->language()->words[ 'copy' ] = "";
			$collab = \IPS\collab\Collab::load( $values[ 'collab_id_select' ] );
			$collab->addNodeModel( $node, $node::$databaseColumnParentRootValue );
			\IPS\Output::i()->redirect( $this->url, 'collab_node_copied_to_collab' );
		}
		
		\IPS\Output::i()->output .= $form;
	}
	
	/**
	 * Move a node and children into a collab
	 */
	protected function collabMove()
	{
		$nodeClass = $this->nodeClass;
		if ( \IPS\Request::i()->subnode )
		{
			$nodeClass = $nodeClass::$subnodeClass;
		}
		$node = NULL;
		
		if ( ! \IPS\Db::i()->checkForColumn( $nodeClass::$databaseTable, $nodeClass::$databasePrefix . 'collab_id' ) )
		{
			\IPS\Output::i()->error( 'node_error', '2S100/Y', 404, '' );
		}
		
		if ( \IPS\Request::i()->id )
		{
			try
			{
				$node = call_user_func( "{$nodeClass}::load", \IPS\Request::i()->id );
				\IPS\Output::i()->title = $node->_title;
			}
			catch ( \OutOfRangeException $e )
			{
				\IPS\Output::i()->error( 'node_error', '2S101/Y', 404, '' );
			}
		}
		
		$form = new \IPS\Helpers\Form( 'copy_to_collab' );
		
		$form->add( new \IPS\Helpers\Form\Number( 'collab_id_select', 0, TRUE, array(), function( $val )
		{
			try
			{
				$collab = \IPS\collab\Collab::load( $val );
			}
			catch ( \OutOfRangeException $e )
			{
				throw new \InvalidArgumentException( 'Invalid Collab ID' );
			}
		} ) );
		
		if ( $values = $form->values() )
		{
			$collab = \IPS\collab\Collab::load( $values[ 'collab_id_select' ] );
			
			$moveRecursive = NULL;
			$moveRecursive = function( $node ) use ( $collab, &$moveRecursive )
			{
				$node->collab_id = $collab->collab_id;
				$node->save();
				
				/* Resave permissions */
				$node->setPermissions( array_merge( array( 'app' => $node::$permApp, 'perm_type' => $node::$permType, 'perm_type_id' => $node->_id ), $node->permissions() ), new \IPS\Helpers\Form\Matrix );
				
				foreach ( $node->children() as $child )
				{
					$moveRecursive( $child );
				}
			};
			
			$parentColumn		= $node::$databaseColumnParent;
			
			if ( $parentColumn )
			{
				$node->$parentColumn 	= $node::$databaseColumnParentRootValue;
			}
			
			/* Make sure collab permissions have been initialized */
			$node->collabPermissions();
			
			$moveRecursive( $node );
			
			\IPS\Output::i()->redirect( $this->url, 'collab_node_moved_to_collab' );
		}
		
		\IPS\Output::i()->output .= $form;	
	}
	
	/**
	 * Extract a node and children from collab to main site
	 */
	protected function collabExtract()
	{
		$nodeClass = $this->nodeClass;
		if ( \IPS\Request::i()->subnode )
		{
			$nodeClass = $nodeClass::$subnodeClass;
		}
		$node = NULL;
		
		if ( ! \IPS\Db::i()->checkForColumn( $nodeClass::$databaseTable, $nodeClass::$databasePrefix . 'collab_id' ) )
		{
			\IPS\Output::i()->error( 'node_error', '2S100/X', 404, '' );
		}
		
		if ( \IPS\Request::i()->id )
		{
			try
			{
				$node = call_user_func( "{$nodeClass}::load", \IPS\Request::i()->id );
				\IPS\Output::i()->title = $node->_title;
			}
			catch ( \OutOfRangeException $e )
			{
				\IPS\Output::i()->error( 'node_error', '2S101/X', 404, '' );
			}
		}
		
		$extractRecursive = NULL;
		$extractRecursive = function( $node ) use ( &$extractRecursive )
		{
			$node->collab_id = 0;
			$node->save();
			
			/* Resave permissions */
			$node->setPermissions( array_merge( array( 'app' => $node::$permApp, 'perm_type' => $node::$permType, 'perm_type_id' => $node->_id ), $node->permissions() ), new \IPS\Helpers\Form\Matrix );
			
			foreach ( $node->children() as $child )
			{
				$extractRecursive( $child );
			}
		};
		
		$parentColumn		= $node::$databaseColumnParent;
		
		if ( $parentColumn )
		{
			$node->$parentColumn 	= $node::$databaseColumnParentRootValue;
		}
			
		$extractRecursive( $node );
		
		\IPS\Output::i()->redirect( $this->url, 'collab_node_extracted_from_collab' );		
	}
	
}
