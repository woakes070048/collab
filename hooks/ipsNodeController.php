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
		$matrix->columns = array(
			'label'		=> function( $key, $value, $data )
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
		
		$rows = array(
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
			
			/* Set the permissions */
			\IPS\Db::i()->delete( 'core_permission_index', array( 'app=? AND perm_type=? AND perm_type_id=?', $insert[ 'app' ], $insert[ 'perm_type' ], $insert[ 'perm_type_id' ] ) );
			\IPS\Db::i()->insert( 'core_permission_index', $insert );

			unset(\IPS\Data\Store::i()->modules);

			/* Clear out member's cached "Create Menu" contents */
			\IPS\Member::clearCreateMenu();
			
			/* Redirect */
			\IPS\Output::i()->redirect( $this->url->setQueryString( array( 'root' => ( $node->parent() ? $node->parent()->_id : '' ) ) ), 'saved' );
		}
		
		/* Display */
		\IPS\collab\Application::$collabPageTitle = \IPS\Output::i()->title;
		\IPS\Output::i()->output .= $matrix;
	}
	  
}
