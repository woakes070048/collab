<?php


namespace IPS\collab\setup\upg_10306;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * 1.3.6 Upgrade Code
 */
class _Upgrade
{
	/**
	 * ...
	 *
	 * @return	array	If returns TRUE, upgrader will proceed to next step. If it returns any other value, it will set this as the value of the 'extra' GET parameter and rerun this step (useful for loops)
	 */
	public function step1()
	{
		/* Add an index for heavily queried permissions so LIKE can be used more efficiently */
		if ( ! \IPS\Db::i()->checkForIndex( 'core_permission_index', 'collab_perm_view' ) )
		{
			\IPS\Db::i()->addIndex( 'core_permission_index', array
			(
				'type'		=> 'key',
				'name'		=> 'collab_perm_view',		
				'columns'	=> array( 'perm_view' )	
			) );
		}
		
		if ( ! \IPS\Db::i()->checkForIndex( 'core_permission_index', 'collab_perm_2' ) )
		{
			\IPS\Db::i()->addIndex( 'core_permission_index', array
			(
				'type'		=> 'key',
				'name'		=> 'collab_perm_2',		
				'columns'	=> array( 'perm_2' )	
			) );
		}
		
		/* Tidy Up Collab Permissions */
		\IPS\Db::i()->update( 'core_permission_index', array( 'perm_view' => '*' ), array( "FIND_IN_SET( '-1', perm_view ) AND FIND_IN_SET( '0', perm_view ) AND perm_type LIKE 'collab_%'" ) );
		\IPS\Db::i()->update( 'core_permission_index', array( 'perm_view' => '0' ), array( "FIND_IN_SET( '0', perm_view ) AND perm_type LIKE 'collab_%'" ) );
		
		\IPS\Db::i()->update( 'core_permission_index', array( 'perm_2' => '*' ), array( "FIND_IN_SET( '-1', perm_2 ) AND FIND_IN_SET( '0', perm_2 ) AND perm_type LIKE 'collab_%'" ) );
		\IPS\Db::i()->update( 'core_permission_index', array( 'perm_2' => '0' ), array( "FIND_IN_SET( '0', perm_2 ) AND perm_type LIKE 'collab_%'" ) );
		
		return TRUE;
	}
	
}