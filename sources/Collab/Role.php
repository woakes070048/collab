<?php
/**
 * @brief		Collaboration collab (Software, Content, Social Group, Clan, Etc.)
 * @author		Kevin Carwile (http://www.linkedin.com/in/kevincarwile)
 * @copyright		(c) 2014 - Kevin Carwile
 * @package		Collaboration
 * @since		10 Dec 2014
 */

namespace IPS\collab\Collab;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 *  Collab role
 */
class _Role extends \IPS\Node\Model
{
	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = '';
	
	/**
	 * @brief	[ActiveRecord] ID Database Column
	 */
	public static $databaseColumnId = 'id';

	/**
	 * @brief	[ActiveRecord] Database table
	 * @note	This MUST be over-ridden
	 */
	public static $databaseTable	= 'collab_roles';
		
	/**
	 * @brief	[ActiveRecord] Database ID Fields
	 */
	protected static $databaseIdFields = array();
	
	/**
	 * @brief	Bitwise keys
	 */
	protected static $bitOptions = array();

	/**
	 * @brief	[Node] Order Database Column
	 */
	public static $databaseColumnOrder = 'weight';
	
	/**
	 * @brief	[Node] Node Title
	 */
	public static $nodeTitle = 'collab_membership_roles';

	/**
	 * @brief	[ActiveRecord] Multiton Store
	 * @note	This needs to be declared in any child classes as well, only declaring here for editor code-complete/error-check functionality
	 */
	protected static $multitons	= array();
	
	/**
	 *  Disable Copy Button
	 */	
	public $noCopyButton = TRUE;
	
	/**
	 * [Node] Does the currently logged in user have permission to add a child node to this node?
	 *
	 * @return	bool
	 */
	public function canAdd()
	{
		return FALSE;	
	}
	
	/**
	 * [Node] Does the currently logged in user have permission to add aa root node?
	 *
	 * @return	bool
	 */
	public static function canAddRoot()
	{
		$collab = \IPS\collab\Application::activeCollab();
		return $collab->collabCan( 'addRole' );	
	}
	
	/**
	 * [Node] Does the currently logged in user have permission to edit this node?
	 *
	 * @return	bool
	 */
	public function canEdit()
	{
		$collab = \IPS\collab\Application::activeCollab();
		return $this->id ? $collab->collabCan( 'editRole' ) : $collab->collabCan( 'addRole' );	
	}
	
	/**
	 * [Node] Does the currently logged in user have permission to copy this node?
	 *
	 * @return	bool
	 */
	public function canCopy()
	{
		return static::canAddRoot();	
	}
	
	/**
	 * [Node] Does the currently logged in user have permission to delete this node?
	 *
	 * @return	bool
	 */
	public function canDelete()
	{
		$collab = \IPS\collab\Application::activeCollab();
		return $collab->collabCan( 'deleteRole' );	
	}

	/**
	 * [Node] Does the currently logged in user have permission to edit permissions for this node?
	 *
	 * @return	bool
	 */
	public function canManagePermissions()
	{
		return FALSE;
	}
	
	/**
	 * [Node] Get Title
	 *
	 * @return	string|null
	 */
	protected function get__title()
	{
		return $this->name;
	}
	
	/**
	 * [Node] Set Title
	 *
	 * @return	string|null
	 */
	protected function set__title( $val )
	{
		$this->name = $val;
	}
	
	/**
	 * Working around bug...
	 * ./system/Node/Model.php : line 1888
	 * $this->title = $title . ' ' . \IPS\Member::loggedIn()->language()->get('copy');
	 */
	protected function set_title( $val )
	{
		$this->_title = $val;
	}

	/**
	 * [Node] Return the custom badge for each row
	 *
	 * @return	NULL|array		Null for no badge, or an array of badge data (0 => CSS class type, 1 => language string, 2 => optional raw HTML to show instead of language string)
	 */
	protected function get__badge()
	{
		$_badge 	= array();
		$lang		= \IPS\Member::loggedIn()->language();
		$_perm_checks	= array( 'inviteMember', 'moderateContent', 'manageMembers', 'manageRoles' );
		
		if ( $this->member_default )
		{
			$_badge[] = "<span style='color:green; font-weight:bold;'>Member Default</span>";
		}
		
		if ( $this->owner_default and \IPS\Member::loggedIn()->modPermission( 'can_bypass_collab_permissions' ) )
		{
			$_badge[] = "<span style='color:blue; font-weight:bold;'>Owner Default</span>";
		}
		
		foreach ( $_perm_checks as $perm )
		{
			if ( $this->roleCan( $perm ) )
			{
				$_badge[] = $lang->addToStack( 'collab_perm_' . $perm );
			}
		}
				
		if ( ! empty ( $_badge ) )
		{
			return array(
				0	=> 'collab',
				2	=> implode( ' / ', $_badge ),
			);
		}
		
		return NULL;
	}

	/**
	 * [Node] Add/Edit Form
	 *
	 * @param	\IPS\Helpers\Form	$form	The form
	 * @return	void
	 */
	public function form( &$form )
	{
		$collab 	= \IPS\collab\Application::activeCollab();
		$role 		= $this->id ? $collab->authObj( $this ) : $this;
		$current_perms	= explode( ',', $role->perms );
		$lang 		= \IPS\Member::loggedIn()->language();
		$form_id 	= $form->id;
		
		$form->addTab( 'collab_tab_settings' );
		$form->add( new \IPS\Helpers\Form\Text( 'collab_role_name', $role->name, TRUE, array(), 
			function( $val ) use ( $collab, $role )
			{
				try
				{
					\IPS\Db::i()->select( 'id', 'collab_roles', array( "collab_id=? AND id!=? AND name=?", $collab->collab_id, $role->id, $val ) )->first();
					throw new \InvalidArgumentException('collab_role_exists');
				}
				catch ( \UnderflowException $e ) {}
			}
		) );
		
		$form->add( new \IPS\Helpers\Form\Text( 'collab_role_custom_title', $role->member_title, FALSE ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'collab_member_default_role', $role->member_default, TRUE ) );
		
		if ( \IPS\Member::loggedIn()->modPermission( 'can_bypass_collab_permissions' ) )
		{
			$form->add( new \IPS\Helpers\Form\YesNo( 'collab_owner_default_role', $role->owner_default, TRUE ) );
		}
		
		if ( $collab->collabCan( 'editRolePermissions' ) )
		{	
			$form->addTab( 'collab_tab_permissions' );
			$form->addHtml( "<div class='role-permission-sets'>" );
			$this->_addPermissionSet( $form, $collab->collabPermissions() );
			$form->add( $moderateContent = new \IPS\Helpers\Form\YesNo( 'perm_moderateContent', in_array( 'moderateContent', $current_perms ) ) );
			$form->addHtml( "</div>" );
			
			$modoptions = \IPS\collab\Application::modOptions();
			$moderateContent->label = \IPS\Member::loggedIn()->language()->addToStack( 'collab_perm_moderateContent' );
			
			foreach ( $collab->enabledNodes() as $app => $config )
			{
				$form->addTab( $config[ 'app' ]->_title );
				$moderateContent->options[ 'togglesOn' ] = array_merge( $moderateContent->options[ 'togglesOn' ], array( $form_id . '_tab_' . $lang->get( "__app_{$app}" ) ) );
				
				foreach ( $config[ 'nodes' ] as $node )
				{
					if ( isset ( $node[ 'content' ] ) and isset ( $modoptions[ $node[ 'content' ] ] ) )
					{
						$mod = $modoptions[ $node[ 'content' ] ];
						$form->addHeader( 'collab_role_mod_permissions' );
						$collab->container()->addModerationSettings( $form, $mod[ 'key' ], $mod[ 'ext' ], $role );
					}
				}
			}
			
		}
		
		parent::form( $form );
	}

	/**
	 * Add Role Permission Fields
	 *
	 * @param	\IPS\Helpers\Form	$form		The form to add permission settings to
	 * @param	array			$permissions	array of permissions to create form elements for
	 * @return	array					Added permission
	 */
	protected function _addPermissionSet( &$form, $permissions )
	{
		$current 	= explode( ',', $this->perms );		
		$collab 	= \IPS\collab\Application::activeCollab();
		
		$added = array();
		foreach ( $permissions as $k => $v )
		{
			if ( is_array( $v ) )
			{
				$form->add( $switch = new \IPS\Helpers\Form\YesNo( 'perm_' . $k, in_array( $k, $current ) ) );
				$switch->label = \IPS\Member::loggedIn()->language()->addToStack( 'collab_perm_' . $k );
				$form->addHtml( "<ul class='role-permissions'>" );
				$switch->options[ 'togglesOn' ] = $this->_addPermissionSet( $form, $v, $role );
				$form->addHtml( "</ul>" );
				$added[] = $form->id . '_perm_' . $k;
			}
			else
			{
				$form->add( $switch = new \IPS\Helpers\Form\YesNo( 'perm_' . $v, in_array( $v, $current ) ) );
				$switch->label = \IPS\Member::loggedIn()->language()->addToStack( 'collab_perm_' . $v );
				$added[] = $form->id . '_perm_' . $v;
			}
		}
		return $added;
	}

	/**
	 * [Node] Save Add/Edit Form
	 *
	 * @param	array	$values	Values from the form
	 * @return	void
	 */
	public function saveForm( $values )
	{
		$collab 	= \IPS\collab\Application::activeCollab();
		$this->name 	= $values[ 'collab_role_name' ];
		$perms 		= array();
		$mod_perms 	= array();
		
		foreach ( $values as $key => $value )
		{
			// Process Collab Permissions
			if ( \substr( $key, 0, \strlen( 'perm_' ) ) === 'perm_' )
			{
				$perm = \substr( $key, \strlen( 'perm_' ) );
				if ( $value )
				{
					$perms[] = $perm;
				}
			}
			
			// Process Moderation Permissions
			if ( \substr( $key, 0, \strlen( 'modperms_' ) ) === 'modperms_' )
			{
				$mod_perm = \substr( $key, \strlen( 'modperms_' ) );
				$mod_perms[ $mod_perm ] = $value;
			}
		}
		
		$this->perms 		= implode( ',', $perms );
		$this->collab_id	= $collab->collab_id;
		$this->mod_perms 	= \serialize( $mod_perms );
		$this->member_title	= $values[ 'collab_role_custom_title' ];
		
		if ( isset( $values[ 'collab_member_default_role' ] ) )
		{
			$this->member_default = $values[ 'collab_member_default_role' ];
		}
		
		if ( isset( $values[ 'collab_owner_default_role' ] ) )
		{
			$this->owner_default = $values[ 'collab_owner_default_role' ];
		}
		
		$this->save();
		$this->postSaveForm( $values );
	}
	
	/**
	 * Search
	 *
	 * @param	string		$column	Column to search
	 * @param	string		$query	Search query
	 * @param	string|null	$order	Column to order by
	 * @param	mixed		$where	Where clause
	 * @return	array
	 */
	public static function search( $column, $query, $order, $where=array() )
	{
		if ( $column === '_title' )
		{
			$column = 'name';
			$order = 'weight';
		}
		
		$collab = \IPS\collab\Application::activeCollab();
		$where[] = array( 'collab_id=?', $collab->collab_id );

		$nodes = array();
		foreach( \IPS\Db::i()->select( '*', static::$databaseTable, array_merge( array( array( "{$column} LIKE CONCAT( '%', ?, '%' )", $query ) ), $where ), $order ) as $k => $data )
		{
			$nodes[ $k ] = static::constructFromData( $data );
		}
		return $nodes;
	}
	
	/**
	 * Get Role Collab
	 *
	 * @return	\IPS\Member
	 */
	public function collab()
	{
		try
		{
			$collab = \IPS\collab\Collab::load( $this->collab_id );
		}
		catch ( \OutOfRangeException $e ) 
		{
			return NULL;
		}
		
		return $collab;
	}

	/**
	 * Permission Check
	 *
	 * @param	string			$perm		A string representing a permission to check for
	 * @return	bool
	 */
	public function roleCan( $perm )
	{
		if ( $collab = $this->collab() )
		{
			if ( array_key_exists( $perm, \IPS\collab\Application::flattenPermissions( $collab->collabPermissions(), explode( ',', $this->perms ) ) ) )
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	/**
	 * [ActiveRecord] Save Changed Columns
	 *
	 * @return	void
	 */
	public function save()
	{
		parent::save();
		
		if ( $collab = $this->collab() )
		{
			/**
			 * Make sure there is only 1 active default role for each type
			 */
			 
			if ( $this->member_default )
			{
				\IPS\Db::i()->update( 'collab_roles', array( 'member_default' => 0 ), array( 'collab_id=? AND id!=?', $collab->collab_id, $this->id ) );
			}
			
			if ( $this->owner_default )
			{
				\IPS\Db::i()->update( 'collab_roles', array( 'owner_default' => 0 ), array( 'collab_id=? AND id!=?', $collab->collab_id, $this->id ) );
			}
		}
	}
	
}