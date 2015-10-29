<?php
/**
 * @brief		Collaboration collab (Software, Content, Social Group, Clan, Etc.)
 * @author		Kevin Carwile (http://www.linkedin.com/in/kevincarwile)
 * @copyright		(c) 2014 - Kevin Carwile
 * @package		Collaboration
 * @since		10 Dec 2014
 */

namespace IPS\collab;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 *  Collab collab
 */
class _Collab extends \IPS\Content\Item implements
	\IPS\Content\Permissions,
	\IPS\Content\Pinnable, \IPS\Content\Lockable, \IPS\Content\Hideable, \IPS\Content\Featurable,
	\IPS\Content\Tags,
	\IPS\Content\Followable,
	\IPS\Content\Shareable,
	\IPS\Content\ReportCenter,
	\IPS\Content\ReadMarkers,
	\IPS\Content\Views,
	\IPS\Content\Ratings,
	\IPS\Content\Searchable,
	\IPS\Content\Embeddable
{

	/**
	 * @brief	Multiton Store
	 */
	protected static $multitons;

	/**
	 * @brief	[ActiveRecord] ID Database Column
	 */
	public static $databaseColumnId = 'collab_id';

	/**
	 * @brief	Application
	 */
	public static $application = 'collab';
	
	/**
	 * @brief	Module
	 */
	public static $module = 'collab';

	/**
	 * @brief	Database Table
	 */
	public static $databaseTable = 'collab_collabs';
	
	/**
	 * @brief	Database Prefix
	 */
	public static $databasePrefix = '';
			
	/**
	 * @brief	Database Column Map
	 */
	public static $databaseColumnMap = array
	(
		'author'			=> 'owner_id',
		'author_name'			=> 'owner_name',
		'container'			=> 'category_id',
		'date'				=> 'created_date',
		'title'				=> 'title',
		'content'			=> 'description',
		'num_comments'			=> 'posts',
		'num_reviews'			=> 'reviews',
		'unapproved_comments'		=> 'queuedposts',
		'first_comment_id'		=> 'firstpost',
		'last_comment'			=> array( 'last_real_post', 'last_post' ),
		'last_comment_by'		=> 'last_poster_id',
		'last_comment_name'		=> 'last_poster_name',
		'views'				=> 'views',
		'rating'			=> 'rating',
		'rating_total'			=> 'rating_total',
		'rating_hits'			=> 'rating_hits',
		'approved'			=> 'approved',
		'pinned'			=> 'pinned',
		'status'			=> 'state',
		'moved_to'			=> 'moved_to',
		'moved_on'			=> 'moved_on',
		'featured'			=> 'featured',
		'state'				=> 'state',
		'cover_photo'			=> 'cover_photo',
		'cover_photo_offset'		=> 'cover_offset',
	);
	
	/**
	 * @brief	Title
	 */
	public static $title = 'collab';
	
	/**
	 * @brief	Node Class
	 */
	public static $containerNodeClass = 'IPS\collab\Category';
	
	/**
	 * @brief	[Content\Item]	Comment Class
	 */
	public static $commentClass = 'IPS\collab\Collab\Comment';

	/**
	 * @brief	[Content\Item]	Review Class
	 */
	public static $reviewClass = 'IPS\collab\Collab\Review';

	/**
	 * @brief	[Content\Item]	First "comment" is part of the item?
	 */
	public static $firstCommentRequired = FALSE;
	
	/**
	 * @brief	[Content\Comment]	Language prefix for forms
	 */
	public static $formLangPrefix = 'collab_collab_';
	
	/**
	 * @brief	Icon
	 */
	public static $icon = 'users';
	
	/**
	 * @brief 	Hide Log Key
	 */
	public static $hideLogKey = 'collab';
	
	/**
	 * @brief	Permission Set
	 */
	public $permissions = array();

	 /**
	 * @brief	Cache for collab user memberships
	 */
	protected $memberships = array();
	
	/**
	 * @brief	Cache for collab roles
	 */
	protected $roles = NULL;

	/**
	 * @brief	Cached URLs
	 */
	protected $_url	= array();
	
	/**
	 * @brief	URL Base
	 */
	public static $urlBase = 'app=collab&module=collab&controller=collabs&id=';
	
	/**
	 * @brief	URL Base
	 */
	public static $urlTemplate = 'collab_collab';
	
	/**
	 * @brief	SEO Title Column
	 */
	public static $seoTitleColumn = 'title_seo';

	/**
	 * @brief	Permissions Loaded Flag
	 */
	protected $permissionsLoaded = FALSE;
	
	/**
	 * @brief	Cover Photo Storage Extension
	 */
	public static $coverPhotoStorageExtension = 'collab_Collab';
	
	/**
	 * Set Default Values (overriding $defaultValues)
	 *
	 * @return	void
	 */
	protected function setDefaultValues()
	{
		parent::setDefaultValues();
	}
	
	/**
	 * Compile Full Collab Permissions ( on demand )
	 *
	 * @return	void
	 */
	public function collabPermissions()
	{
		if ( $this->permissionsLoaded )
		{
			return $this->permissions;
		}
		
		$lang = \IPS\Member::loggedIn()->language();
		$this->permissions = array_merge( $this->permissions, array
		(
			'inviteMember',
			'moderateContent',
			'manageCollab' => array
			(
				'editDescription',
				'editSettings',
				'editMenu',
				'manageMembers' => array
				(
					'editMember' => array
					( 
						'editMemberRoles',
					),
					'banMember',
					'deleteMember',
					'unbanMember',
					'approveMember',
				),
				'manageRoles' => array
				(
					'addRole',
					'editRole',
					'deleteRole',
					'editRolePermissions',
				),
			),
		) );
		
		foreach ( $this->enabledNodes() as $app => $configured )
		{
			$lang->words[ 'collab_perm_appManage-' . $app ] = $lang->addToStack( 'collab_perm_nodeManage', FALSE, array( 'sprintf' => array( $lang->addToStack( '__app_' . $app ) ) ) );			
			foreach ( $configured[ 'nodes' ] as $node )
			{
				$nid = md5( $node['node'] );
				$this->permissions[ 'manageCollab' ][ "appManage-{$app}" ] = array_merge( $this->permissions[ 'manageCollab' ][ "appManage-{$app}" ] ?: array(), array(
					"nodeAdd-{$nid}",
					"nodeEdit-{$nid}",
					"nodeDelete-{$nid}",
				) );
				$lang->words[ 'collab_perm_nodeAdd-' . $nid ] = $lang->addToStack( 'collab_perm_nodeAdd', FALSE, array( 'sprintf' => array( $lang->addToStack( $node['node']::$nodeTitle ) ) ) );
				$lang->words[ 'collab_perm_nodeEdit-' . $nid ] = $lang->addToStack( 'collab_perm_nodeEdit', FALSE, array( 'sprintf' => array( $lang->addToStack( $node['node']::$nodeTitle ) ) ) );
				$lang->words[ 'collab_perm_nodeDelete-' . $nid ] = $lang->addToStack( 'collab_perm_nodeDelete', FALSE, array( 'sprintf' => array( $lang->addToStack( $node['node']::$nodeTitle ) ) ) );
			}
		}
		
		$this->permissionsLoaded = TRUE;
		return $this->permissions;
	}

	/**
	 * Get the langauge string for this collab (singular)
	 *
	 * @return	string		Language MD5 key
	 */
	public function get_collab_singular()
	{
		return $this->container()->collab_singular;
	}
	
	/**
	 * Get the langauge string for this collab (plural)
	 *
	 * @return	string		Language MD5 key
	 */
	public function get_collab_plural()
	{
		return $this->container()->collab_plural;
	}
	
	/**
	 * Get the langauge string for this collab (plural)
	 *
	 * @return	string		Language MD5 key
	 */
	public function guestTitle()
	{
		if ( \trim( $this->guest_title ) != '' )
		{
			return \trim( $this->guest_title );
		}
		
		return sprintf( \IPS\Member::loggedIn()->language()->get( 'collab_default_guest_title' ), $this->collab_singular );
	}
	
	/**
	 * Get SEO name
	 *
	 * @return	string
	 */
	public function get_title_seo()
	{
		if ( ! $this->_data[ 'title_seo' ] )
		{
			$this->title_seo = \IPS\Http\Url::seoTitle( $this->title );
			$this->save();
		}

		return $this->_data[ 'title_seo' ];
	}
	
	/**
	 * Get Arbitrary Collab Data
	 *
	 * @return 	array
	 */
	public function get_collab_data()
	{
		return json_decode( $this->data, TRUE ) ?: array();
	}
	
	/**
	 * Set Arbitrary Collab Data
	 */
	public function set_collab_data( $val )
	{
		$this->data = json_encode( $val );
		return $val;
	}
	
	/**
	 * Get Available Collab Options
	 *
	 * @param	string|NULL	$node_id	md5 checksum of a node object classname to return the settings for ( optional )
	 * @return	array|FALSE			All available collab options or the settings for a single node type ( $node_id )
	 */
	public function enabledNodes( $node_id=NULL )
	{
		return $this->container()->enabledNodes( $node_id );
	}
	
	/**
	 * Get URL
	 *
	 * @param	string|NULL		$action		Action
	 * @return	\IPS\Http\Url
	 */
	public function url( $action=NULL )
	{
		$_key	= md5( $action );

		if( !isset( $this->_url[ $_key ] ) )
		{
			$this->_url[ $_key ] = \IPS\Http\Url::internal( "app=collab&module=collab&controller=collabs&id={$this->collab_id}", 'front', 'collab_collab', array( $this->title_seo ) );
		
			if ( $action )
			{
				$this->_url[ $_key ] = $this->_url[ $_key ]->setQueryString( 'do', $action );
			}
		}
	
		return $this->_url[ $_key ];
	}

	/**
	 * Get elements for add/edit form
	 *
	 * @param	\IPS\Content\Item|NULL	$item		The current item if editing or NULL if creating
	 * @param	\IPS\Node\Model|NULL	$container	Container (e.g. forum), if appropriate
	 * @return	array
	 */
	public static function formElements( $item=NULL, \IPS\Node\Model $container=NULL )
	{
		$form = parent::formElements( $item, $container );
		
		if ( $item === NULL )
		{
			if ( $container->bitoptions[ 'enable_model' ] )
			{
				$_templates = $container->templates();
				if ( count ( $_templates ) )
				{
					if ( ! $container->bitoptions[ 'require_model' ] and ! $container->bitoptions[ 'multiple_model' ] )
					{
						$_options = array( '0' => 'collab_no_template' );
					}
					
					foreach ( $_templates as $template )
					{
						$_options[ $template->collab_id ] = $template->title;
					}
					
					if ( 
						count ( $_options ) > 1 or 
						( 
							$container->bitoptions[ 'multiple_model' ] and 
							! $container->bitoptions[ 'require_model' ] 
						) 
					)
					{
						if ( $container->bitoptions[ 'multiple_model' ] )
						{
							$form[ 'collab_template' ] = new \IPS\Helpers\Form\CheckboxSet( 'collab_template', array(), $container->bitoptions[ 'require_model' ], array( 'options' => $_options ) );
						}
						else
						{
							$form[ 'collab_template' ] = new \IPS\Helpers\Form\Radio( 'collab_template', NULL, TRUE, array( 'options' => $_options ) );
						}
					}
					else
					{
						
					}
				}
			}
		}
		
		$form['collab_short_description'] = new \IPS\Helpers\Form\Textarea( 'collab_short_description', $item ? $item->short_description : NULL, FALSE );

		$form['description'] = new \IPS\Helpers\Form\Editor( 'collab_description', $item ? $item->description : NULL, FALSE, array( 
			'app' => 'collab', 
			'key' => 'Collab', 
			'autoSaveKey' => $item->collab_id ? 'collab-' . $item->collab_id : NULL,
			'attachIds' => ( $item === NULL ? NULL : array( $item->collab_id ) ) 
		) );
		
		unset( $form['auto_follow']);
		
		return $form;
	}
	
	/**
	 * Process created object AFTER the object has been created
	 *
	 * @param	array				$values	Values from form
	 * @return	void
	 */
	protected function processAfterCreate( $comment, $values )
	{
		parent::processAfterCreate( $comment, $values );
		
		/* Have we chosen any templates to create from? */
		if ( isset( $values[ 'collab_template' ] ) and $values[ 'collab_template' ] )
		{
			$_tmpl_ids = (array) $values[ 'collab_template' ];
			foreach( $_tmpl_ids as $id )
			{
				if ( $id )
				{
					try
					{
						$collab = \IPS\collab\Collab::load( $id );
						$this->addModel( $collab );
					}
					catch ( \Exception $e ) {}
				}
			}
		}
		else if 
		(
			$container = $this->container() and
			$container->bitoptions[ 'enable_model' ] and 
			$container->bitoptions[ 'require_model' ] and
			count( $_templates = $container->templates() )
		)
		{
			$this->addModel( array_shift( $_templates ) );
		}
	}
	
	/**
	 * Should new items be moderated?
	 *
	 * @param	\IPS\Member		$member		The member posting
	 * @param	\IPS\Node\Model	$container	The container
	 * @return	bool
	 */
	public static function moderateNewItems( \IPS\Member $member, \IPS\Node\Model $container = NULL )
	{
		if ( $container and $configuration = $container->_configuration and $configuration[ 'require_approval' ] and ! $member->group['g_avoid_q'] )
		{
			return TRUE;
		}

		return parent::moderateNewItems( $member, $container );
	}

	/**
	 * Process create/edit form
	 *
	 * @param	array				$values	Values from form
	 * @return	void
	 */
	public function processForm( $values )
	{
		parent::processForm( $values );
		
		$this->title_seo = \IPS\Http\Url::seoTitle( $this->title );
		
		if ( isset( $values[ 'collab_short_description' ] ) )
		{
			$this->short_description = $values[ 'collab_short_description' ];
		}
		
		if ( isset( $values[ 'collab_short_description' ] ) )
		{
			$this->description = $values[ 'collab_description' ];
		}
		
		if ( isset( $values[ 'collab_short_description' ] ) )
		{
			$this->default_member_title = $values[ 'collab_default_title' ];
		}
		
		/* Moderator actions */
		if ( isset( $values[ 'collab_create_state' ] ) )
		{
			if ( in_array( 'lock', $values[ 'collab_create_state' ] ) )
			{
				$this->state = 'closed';	
			}

			if ( in_array( 'hide', $values[ 'collab_create_state' ] ) )
			{
				$this->approved = -1;
			}

			if ( in_array( 'pin', $values[ 'collab_create_state' ] ) )
			{
				$this->pinned = 1;
			}

			if ( in_array( 'feature', $values[ 'collab_create_state' ] ) )
			{
				$this->featured = 1;
			}
		}
		
		

	}
	
	/**
	 * Process after the object has been edited on the front-end
	 *
	 * @param	array	$values		Values from form
	 * @return	void
	 */
	public function processAfterEdit( $values )
	{
		parent::processAfterEdit( $values );
		
		/* Collab changed? */
		if ( ! $this->hidden() and ( $this->collab_id === $this->container()->last_id ) and ( $this->title_seo !== $this->container()->seo_last_title ) )
		{
			$this->container()->seo_last_title = $this->title_seo;
			$this->container()->last_title     = $this->title;
			$this->container()->save();
			
			foreach( $this->container()->parents() AS $parent )
			{
				if ( ( $this->collab_id === $parent->last_id ) and ( $this->title_seo !== $parent->seo_last_title ) )
				{
					$parent->seo_last_title		= $this->title_seo;
					$parent->last_title			= $this->title;
					$parent->save();
				}
			}
		}
	}
	
	/**
	 * Get collab menu items
	 *
	 * @return array	Array of menu items
	 */
	public function collabMenuItems()
	{
		$menuItems = array
		(
			'home' => array
			(
				'title' => \IPS\Member::loggedIn()->language()->addToStack( 'collab_homepage', FALSE, array( 'sprintf' => array( $this->collab_singular ) ) ),
				'url' => $this->url(),
				'icon' => 'home',
			),
		);
		
		foreach( \IPS\collab\Menu::roots( NULL ) as $item )
		{
			if ( $item->can( 'view' ) )
			{
				$menuItems[ $item->id ] = array
				(
					'title' => $item->title,
					'url' => new \IPS\Http\Url( $item->link ),
					'icon' => $item->icon,
				);
			}
		}
		
		return $menuItems;
	}
	
	/**
	 * Get stats to display in the collab header
	 *
	 * @return  array		An array of stat items
	 */
	public function collabStatItems()
	{
		$stats = array();
		
		/**
		 * Add Rules Data To Stats
		 */
		if ( \IPS\Application::appIsEnabled( 'rules' ) )
		{
			foreach ( \IPS\rules\Data::roots( 'view', NULL, array( array( 'data_class=? AND data_display_mode IN ( \'automatic\' ) AND data_type IN ( \'string\', \'int\', \'float\' )', static::rulesDataClass() ) ) ) as $data_field )
			{
				$statValue = $this->getRulesData( $data_field->column_name );					
				if ( $statValue !== NULL )
				{
					$stats[ 'rules_' . $data_field->column_name ] = \IPS\Theme::i()->getTemplate( 'components', 'collab', 'front' )->collabStatItem( $this, $data_field->name, $statValue );
				}
			}
		}
		
		return $stats;
	}
	
	/**
	 * Does a container contain unread items?
	 *
	 * @param	\IPS\Node\Model		$container	The container
	 * @param	\IPS\Member|NULL	$member	The member (NULL for currently logged in member)
	 * @return	bool|NULL
	 */
	public static function containerUnread( \IPS\Node\Model $container, \IPS\Member $member = NULL )
	{
		$configuration = $container->_configuration;
		
		if ( $configuration[ 'collab_unread_method' ] == 'comprehensive' )
		{
			foreach( $container->getContentItems( NULL, NULL ) as $collab )
			{
				if ( $collab->unread( $member ) )
				{
					return TRUE;
				}
			}
		}
		else
		{
			return parent::containerUnread( $container, $member );
		}
		
		return FALSE;
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
		/* Omit collabs from mixed content list */
		if ( ! ( \IPS\Request::i()->type AND \IPS\Request::i()->type !== 'all' ) )
		{
			return array_merge( parent::vncWhere( $joinContainer, $joins ), array( 'collab_collabs.collab_id=0' ) );
		}

		return parent::vncWhere( $joinContainer, $joins );
	}

	/**
	 * Get latest collab content with permission
	 * 
	 * @param 	int				$limit		The number of items to get
	 * @return	array
	 */
	public function getLatestContent( $limit=1 )
	{
		$contentItems = array();
		
		/* Get types */
		$types = array();
		foreach( $this->enabledNodes() as $app => $config )
		{
			foreach ( $config[ 'nodes' ] as $node )
			{
				if ( $node[ 'content' ] )
				{
					$types[] = $node[ 'content' ];
				}
			}
		}
		
		/* Build the selects */
		$selects = array();
		foreach ( $types as $key => $class )
		{
			$containerClass = $class::$containerNodeClass;
			$dateColumnExpression = $this::latestContentSQL( $class );
			
			/* Limit content to this collab */
			$contentWhere = array( array( $containerClass::$databaseTable . "." . $containerClass::$databasePrefix . 'collab_id=' . $this->collab_id ) );

			$columns = array
			(
				$class::$databaseTable . "." . $class::$databasePrefix.$class::$databaseColumnId				=> 'id',
				$dateColumnExpression											=> 'date',
				$class::$databaseTable . "." . $class::$databasePrefix.$class::$databaseColumnMap['author']	=> 'author'
			);
			
			if ( in_array( 'IPS\Content\Hideable', class_implements( $class ) ) and !\IPS\Member::loggedIn()->modPermission( "can_view_hidden_content" ) )
			{
				if ( $class::$databaseColumnMap['approved'] )
				{
					$columns[ $class::$databaseTable . "." .  $class::$databasePrefix.$class::$databaseColumnMap[ 'approved' ] . '-1' ] = 'hidden';
					$contentWhere[] = array( $class::$databaseTable . "." . $class::$databasePrefix.$class::$databaseColumnMap['approved'] . '-1=0' );
				}
				else
				{
					$columns[ $class::$databaseTable . "." . $class::$databasePrefix.$class::$databaseColumnMap[ 'hidden' ] ] = 'hidden';
					$contentWhere[] = array( $class::$databaseTable . "." . $class::$databasePrefix.$class::$databaseColumnMap[ 'hidden' ] . '=0' );
				}
			}
			
			$select = array( "'".str_replace( '\\', '\\\\' , $class ) ."' AS class" );
			foreach ( $columns as $local => $normalized )
			{
				$select[] = "{$local} AS {$normalized}";
			}
			
			$select = implode( ', ', $select );
			
			/* Permissions */
			if ( in_array( 'IPS\Content\Permissions', class_implements( $class ) ) )
			{
				$categories = array();
				foreach( \IPS\Db::i()->select( 'perm_type_id', 'core_permission_index', array( "core_permission_index.app='" . $containerClass::$permApp . "' AND core_permission_index.perm_type='" . $containerClass::$permType . "' AND (" . \IPS\Db::i()->findInSet( 'perm_' . $containerClass::$permissionMap['read'], \IPS\Member::loggedIn()->groups ) . ' OR ' . 'perm_' . $containerClass::$permissionMap['read'] . "='*' )" ) ) as $result )
				{
					$categories[] = $result;
				}

				if( count( $categories ) )
				{
					$contentWhere[] = array( $class::$databaseTable . '.' . $class::$databasePrefix . $class::$databaseColumnMap['container'] . ' IN(' . implode( ',', $categories ) . ')' );
				}
				else
				{
					$contentWhere[]	= array( $class::$databaseTable . "." . $class::$databasePrefix . $class::$databaseColumnMap['container'] . '=0' );
				}
			}
			
			/* App-specific wheres */
			$joinContainer	= TRUE;
			$joins		= array();
			$vncWhere	= $class::vncWhere( $joinContainer, $joins );

			if( isset( $vncWhere['item'] ) )
			{
				$contentWhere	= array_merge( $contentWhere, $vncWhere['item'] );

				if( isset( $vncWhere['container'] ) )
				{
					$contentWhere	= array_merge( $contentWhere, $vncWhere['container'] );
				}
			}
			else
			{
				$contentWhere	= array_merge( $contentWhere, $vncWhere );
			}
			
			if ( isset( $class::$databaseColumnMap['state'] ) )
			{
				$contentWhere[] = array( $class::$databaseTable . '.' . $class::$databasePrefix . $class::$databaseColumnMap['state'] . "!='link'" );
			}

			/* Add to the list */
			$select = \IPS\Db::i()->select( $select, $class::$databaseTable, $contentWhere );
			if ( $joinContainer )
			{
				$select->join( $containerClass::$databaseTable, $class::$databaseTable . "." . $class::$databasePrefix . $class::$databaseColumnMap['container'] . '=' . $containerClass::$databaseTable . "." . $containerClass::$databasePrefix . $containerClass::$databaseColumnId );
			}
			if ( count( $joins ) )
			{
				foreach ( $joins as $join )
				{
					$select->join( $join['from'], $join['where'] );
				}
			}
			$selects[] = $select;
		}
		
		if ( ! empty( $selects ) )
		{
			/* Query content */
			$results = \IPS\Db::i()->union( $selects, 'date DESC', array( 0, $limit ), NULL, FALSE, \IPS\Db::SELECT_SQL_CALC_FOUND_ROWS, $where );
			foreach( $results as $result )
			{
				try
				{
					$contentItems[] = $result[ 'class' ]::load( $result[ 'id' ] );
				}
				catch( \OutOfRangeException $e ) { }
			}
		}
		
		return $contentItems;
	}

	/**
	 * Get the latest date column expression
	 *
	 * @param	string	$class 		Content class
	 * @return	string
	 */
	public static function latestContentSQL( $class )
	{
		/* What is the best date column? */
		$dateColumns = array();
		foreach ( array( 'updated', 'last_comment', 'last_review' ) as $k )
		{
			if ( isset( $class::$databaseColumnMap[ $k ] ) )
			{
				if ( is_array( $class::$databaseColumnMap[ $k ] ) )
				{
					foreach ( $class::$databaseColumnMap[ $k ] as $v )
					{
						$dateColumns[] = " IFNULL( " . $class::$databaseTable . '.'. $class::$databasePrefix . $v . ", 0 )";
					}
				}
				else
				{
					$dateColumns[] = " IFNULL( " . $class::$databaseTable . '.'. $class::$databasePrefix . $class::$databaseColumnMap[ $k ] . ", 0 )";
				}
			}
		}
		
		return count( $dateColumns ) > 1 ? ( 'GREATEST(' . implode( ',', $dateColumns ) . ')' ) : array_pop( $dateColumns );
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
	 * @param	mixed		$skipPermission		If you are getting records from a specific container, pass the container to reduce the number of permission checks necessary or pass TRUE to skip conatiner-based permission. You must still specify this in the $where clause
	 * @param	bool		$joinTags			If true, will join the tags table
	 * @param	bool		$joinAuthor			If true, will join the members table for the author
	 * @param	bool		$joinLastCommenter	If true, will join the members table for the last commenter
	 * @return	\IPS\Patterns\ActiveRecordIterator|int
	 */
	public static function getItemsWithPermission( $where=array(), $order=NULL, $limit=10, $permissionKey='read', $includeHiddenItems=NULL, $queryFlags=0, \IPS\Member $member=NULL, $joinContainer=FALSE, $joinComments=FALSE, $joinReviews=FALSE, $countOnly=FALSE, $joins=NULL, $skipPermission=FALSE, $joinTags=TRUE, $joinAuthor=TRUE, $joinLastCommenter=TRUE )
	{
		$joinContainer 	= TRUE;
		$joins 		= $joins ?: array();
		$member 	= $member ?: \IPS\Member::loggedIn();
		
		$where[] = array( "( collab_categories.category_privacy_mode!=? OR collab_memberships.status IN ( 'active', 'invited', 'pending' ) )", 'private' );
		$joins[] = array( 'from' => 'collab_memberships', 'where' => array( 'collab_memberships.member_id=? AND collab_memberships.collab_id=collab_collabs.collab_id', $member->member_id ) );
	
		return parent::getItemsWithPermission( $where, $order, $limit, $permissionKey, $includeHiddenItems, $queryFlags, $member, $joinContainer, $joinComments, $joinReviews, $countOnly, $joins, $skipPermission, $joinTags, $joinAuthor, $joinLastCommenter );
	}
	
	/**
	 * Is unread?
	 *
	 * @param	\IPS\Member|NULL	$member	The member (NULL for currently logged in member)
	 * @return	int|NULL	0 = read. -1 = never read. 1 = updated since last read. NULL = unsupported
	 * @note	When a node is marked read, we stop noting which individual content items have been read. Therefore, -1 vs 1 is not always accurate but rather goes on if the item was created
	 */
	public function unread( \IPS\Member $member = NULL )
	{
		if ( isset( $this->unread ) )
		{
			return $this->unread;
		}
		
		$configuration = $this->container()->_configuration;
		
		if ( $configuration[ 'collab_unread_method' ] == 'comprehensive' )
		{
			$member = $member ?: \IPS\Member::loggedIn();
			$savedAffectiveCollab = \IPS\collab\Application::$affectiveCollab;
			\IPS\collab\Application::$affectiveCollab = $this;

			/* See if any container from any app inside the collab has unread content */
			foreach( $this->enabledNodes() as $app => $config )
			{
				foreach ( $config[ 'nodes' ] as $node )
				{				
					foreach ( $node[ 'node' ]::roots( 'view' ) as $root )
					{
						if ( $node[ 'content' ]::containerUnread( $root, $member ) )
						{
							\IPS\collab\Application::$affectiveCollab = $savedAffectiveCollab;
							return $this->unread = 1;
						}
					}
				}
			}

			\IPS\collab\Application::$affectiveCollab = $savedAffectiveCollab;
			return $this->unread = 0;
		}
		else
		{
			return parent::unread();
		}
	}

	/**
	 * Mark entire collab as read
	 *
	 * @param	\IPS\Member|NULL	$member	The member (NULL for currently logged in member)
	 * @param	int|NULL			$time	The timestamp to set (or NULL for current time)
	 * @param	mixed				$extraContainerWhere	Additional where clause(s) (see \IPS\Db::build for details)
	 * @return	void
	 */
	public function markCollabRead( \IPS\Member $member = NULL, $time = NULL, $extraContainerWhere = NULL )
	{
		$this->markRead( $member, $time, $extraContainerWhere );
		
		/* Mark all containers in enabled apps as read */
		foreach( $this->enabledNodes() as $app => $config )
		{
			foreach ( $config[ 'nodes' ] as $node )
			{				
				foreach ( $node[ 'node' ]::roots( 'view' ) as $root )
				{
					$node[ 'content' ]::markContainerRead( $root );
				}
			}
		}		
	}
	
	/**
	 * Count node aggregate totals inside collab
	 *
	 * @param 	string		$k		The property to recount ( '_items', '_comments', '_reviews' )
	 * @param	string		$nodeClass	The node class to count or NULL for all
	 * @return 	mixed				An array of total count data or an integer if counting single node type
	 */
	public function countTotals( $k, $nodeClass=NULL )
	{
		$savedAffectiveCollab				= \IPS\collab\Application::$affectiveCollab;
		\IPS\collab\Application::$affectiveCollab 	= $this;
		
		$totals = array
		(
			'node_totals' => array(),
			'grand_total' => 0,
		);
		
		$countRecursive = function( $node ) use ( $k, &$countRecursive )
		{
			$count = 0;
			
			if ( ! ( $contentItemClass = $node::$contentItemClass ) )
			{
				return 0;
			}
			
			$contentWhere = array( array( $contentItemClass::$databasePrefix . $contentItemClass::$databaseColumnMap[ 'container' ] . '=?', $node->_id ) );
			
			if ( in_array( 'IPS\Content\Hideable', class_implements( $contentItemClass ) ) )
			{
				if ( isset( $contentItemClass::$databaseColumnMap[ 'approved' ] ) )
				{
					$contentWhere[] = array( $contentItemClass::$databasePrefix . $contentItemClass::$databaseColumnMap[ 'approved' ] . '=?', 1 );
				}
				elseif ( isset( $contentItemClass::$databaseColumnMap[ 'hidden' ] ) )
				{
					$contentWhere[] = array( $contentItemClass::$databasePrefix . $contentItemClass::$databaseColumnMap[ 'hidden' ] . '=?', 0 );
				}
			}
			
			switch( $k )
			{
				case '_items' :
				
					$count = (int) \IPS\Db::i()->select( 'COUNT(*)', $contentItemClass::$databaseTable, $contentWhere )->first();
					break;
					
				case '_comments' :
					
					if ( $contentItemClass::$databaseColumnMap[ 'num_comments' ] )
					{
						$count = (int) \IPS\Db::i()->select( 'SUM(' . $contentItemClass::$databasePrefix . $contentItemClass::$databaseColumnMap[ 'num_comments' ] . ')', $contentItemClass::$databaseTable, $contentWhere )->first();
						
						/* Subtract first comments if they are required */
						if ( $contentItemClass::$firstCommentRequired )
						{
							$count -= (int) \IPS\Db::i()->select( 'COUNT(*)', $contentItemClass::$databaseTable, $contentWhere )->first();
						}
					}
					break;
					
				case '_reviews' :
					
					if ( $contentItemClass::$databaseColumnMap[ 'num_reviews' ] )
					{
						$count = (int) \IPS\Db::i()->select( 'SUM(' . $contentItemClass::$databasePrefix . $contentItemClass::$databaseColumnMap[ 'num_reviews' ] . ')', $contentItemClass::$databaseTable, $contentWhere )->first();
					}
					break;
					
				default : 
				
					$count = (int) $node->$k;

			}
			
			foreach( $node->children() as $child )
			{
				$count += $countRecursive( $child );
			}
			
			return $count;
		};
		
		/* Count all enabled node types */
		if ( $nodeClass === NULL )
		{
			/* Tally Counts */
			foreach( $this->enabledNodes() as $app => $config )
			{
				foreach ( $config[ 'nodes' ] as $node )
				{
					$node_type_total = 0;
					foreach ( $node[ 'node' ]::roots( NULL ) as $root )
					{
						$node_type_total += $countRecursive( $root );
					}
					$totals[ 'node_totals' ][ $node[ 'nid' ] ] = $node_type_total;
					$totals[ 'grand_total' ] += $node_type_total;
				}
			}
		}
		
		/* Count individual node type */
		else
		{
			$totals = 0;
			if ( $this->enabledNodes( md5( $nodeClass ) ) )
			{
				foreach( $nodeClass::roots( NULL ) as $root )
				{
					$totals += $countRecursive( $root );
				}
			}
		}
		
		\IPS\collab\Application::$affectiveCollab 	= $savedAffectiveCollab;
		return $totals;
	}
	
	/**
	 * Get node aggregate totals inside collab, saving if they haven't been tallied yet
	 *
	 * @param 	string		$k		The property to recount ( '_items' or '_comments' )
	 * @param	string		$nid		The node type to return totals for or NULL for grand total
	 * @return 	int				The total count
	 */
	public function getTotal( $k, $nid=NULL )
	{
		$data = $this->collab_data;
		
		if ( isset( $data[ $k ] ) )
		{
			return $nid ? $data[ $k ][ 'node_totals' ][ $nid ] : $data[ $k ][ 'grand_total' ];
		}
		
		/**
		 * Save the totals the first time they are counted
		 */
		$data[ $k ] = $this->countTotals( $k );
		$this->collab_data = $data;
		$this->save();
		
		return $this->getTotal( $k, $nid );
	}
	
	/**
	 * Get total collab contributions
	 */
	public function getTotalContributions()
	{
		$configuration = $this->container()->_configuration;
		
		switch( $configuration[ 'contribution_mode' ] )
		{				
			case 'items':
			
				return $this->getTotal( '_items' );
			
			default:
			case 'posts':
			
				return $this->getTotal( '_items' ) + $this->getTotal( '_comments' );
				
		}
	}
	
	/**
	 * Recount and save all totals
	 *
	 * @return void
	 */
	public function recountAll()
	{
		$data = $this->collab_data;
		
		foreach( array( '_items', '_comments' ) as $k )
		{
			$data[ $k ] = $this->countTotals( $k );
		}
		
		$this->collab_data = $data;
		$this->save();
	}

	/**
	 * Cover Photo
	 *
	 * @return	\IPS\Helpers\CoverPhoto
	 */
	public function coverPhoto()
	{		
		$photo = parent::coverPhoto();
		$photo->editable = $this->canEdit() and \IPS\collab\Application::urlMatch( $this );
		$photo->overlay = \IPS\Theme::i()->getTemplate( 'components', 'collab', 'front' )->collabCoverOverlay( $this );
		return $photo;
	}
	
	/**
	 * Set Configured Theme
	 *
	 * @return	void
	 */
	public function setTheme()
	{
		$this->container()->setTheme();
	}
	
	/**
	 * Save
	 *
	 * @param	bool	$bypassMembership	Bypass the automatic creation of collab owner membership
	 * @return	void
	 */
	public function save( $bypassMembership=FALSE )
	{
		$isNew = $this->_new;
		
		/**
		 * @DEMO: Restrict amount of collabs in demo version
		 */
		if ( $isNew and \IPS\collab\DEMO )
		{
			if ( \IPS\Db::i()->select( 'COUNT(*)', 'collab_collabs' )->first() >= 10 )
			{
				\IPS\Output::i()->error( 'Demo version restricted to a maximum of 10 collaborations.', 'GCDEMO', 200, '' );
				exit;
			}
		}	
		
		parent::save();
		
		/**
		 * Automatically give the collab creator a membership
		 */
		if ( $isNew and ! $bypassMembership )
		{
			$membership = new \IPS\collab\Collab\Membership;
			$membership->collab_id = $this->collab_id;
			$membership->member_id = $this->owner_id;
			$membership->status = \IPS\collab\COLLAB_MEMBER_ACTIVE;
			$membership->joined = time();
			$membership->save();
		}
		
	}
	
	/**
	 * Can view?
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for or NULL for the currently logged in member
	 * @return	bool
	 */
	public function canView( $member=NULL )
	{
		$member = $member ?: \IPS\Member::loggedIn();
		
		$canView = parent::canView( $member );
		
		if ( $this->container()->privacy_mode == 'private' and ! $member->modPermission( 'can_bypass_collab_permissions' ) )
		{
			if ( $membership = $this->getMembership( $member ) )
			{
				return $canView and $membership->status !== \IPS\collab\COLLAB_MEMBER_BANNED;
			}
			else
			{
				return $canView and in_array( $this->join_mode, array( \IPS\collab\COLLAB_JOIN_FREE, \IPS\collab\COLLAB_JOIN_APPROVE ) );
			}
		}
		
		return $canView;
	}

	/**
	 * Can edit?
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for (NULL for currently logged in member)
	 * @return	bool
	 */
	public function canEdit( $member=NULL )
	{
		return $this->collabCan( 'editCollab', $member ) or parent::canEdit( $member );
	}
	
	/**
	 * Can Rate?
	 *
	 * @param	\IPS\Member|NULL		$member		The member to check for (NULL for currently logged in member)
	 * @return	bool
	 * @throws	\BadMethodCallException
	 */
	public function canRate( \IPS\Member $member = NULL )
	{
		$member = $member ?: \IPS\Member::loggedIn();		
		return $this->container()->bitoptions[ 'allow_ratings' ] and parent::canRate( $member ) and $this->container()->can( 'rate', $member );
	}
	
	/**
	 * Can join this collab?
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for (NULL for currently logged in member)
	 * @return	bool
	 */
	public function canJoin( $member=NULL )
	{
		$member 	= $member ?: \IPS\Member::loggedIn();
		$collabCan 	= $this->notFull();
		
		/* Joining Disabled */
		if ( $this->join_mode == \IPS\collab\COLLAB_JOIN_DISABLED )
		{
			return FALSE;
		}
		
		/* Already has a membership */
		if ( $membership = $this->getMembership( $member ) )
		{
			return FALSE;
		}
		
		return $collabCan and $this->container()->can( 'join', $member );
	}
	
	/**
	 * Can the logged in member perform moderation actions?
	 *
	 * @param 	\IPS\Member|NULL	$member		The member to check permission for or NULL for currently logged in member
	 * @return	bool
	 */
	public function isMod( $member=NULL )
	{
		return 
		(
			$this->canEdit( $member ) or 
			$this->canPin( $member ) or 
			$this->canUnpin( $member ) or 
			$this->canFeature( $member ) or 
			$this->canUnfeature( $member ) or 
			$this->canHide( $member ) or 
			$this->canUnhide( $member ) or 
			$this->canMove( $member ) or 
			$this->canLock( $member ) or 
			$this->canUnlock( $member ) or 
			$this->canDelete( $member )
		);
	}

	/**
	 * Is this collab full?
	 *
	 * @return	bool
	 */
	public function isFull()
	{
		$isFull 	= FALSE;
		$max_members 	= $this->r_max_members !== NULL ? $this->r_max_members : $this->container()->max_collab_members;
		
		if ( $max_members > 0 )
		{
			$isFull = ( \IPS\Db::i()->select( 'COUNT(*)', 'collab_memberships', array( 'collab_id=? AND status=?', $this->collab_id, \IPS\collab\COLLAB_MEMBER_ACTIVE ) )->first() >= $this->container()->max_collab_members );
		}

		return $isFull;
	}
	
	/**
	 * Is there room for people to join this collab?
	 *
	 * @return	bool
	 */
	public function notFull()
	{
		return ! ( $this->isFull() );
	}
	
	/**
	 * Invite Member to Collab
	 *
	 * @param 	$member 	\IPS\Member		The member to invite
	 * @param	$sponsor	\IPS\Member|NULL	The member responsible for the invite
	 * @param	$notes		string			Collab notes to attach to invitation
	 * @param	$skipNotification	bool		Skip sending a notification to invited member
	 */
	public function inviteMember( \IPS\Member $member, \IPS\Member $sponsor=NULL, $notes=NULL, $skipNotification=FALSE )
	{
		if ( $this->canJoin( $member ) )
		{
			$membership 			= new \IPS\collab\Collab\Membership;
			$membership->member_id 		= $member->member_id;
			$membership->collab_id 		= $this->collab_id;
			$membership->status 		= \IPS\collab\COLLAB_MEMBER_INVITED;
			$membership->collab_notes 	= $notes ?: '';
			$membership->sponsor_id		= $sponsor ? $sponsor->member_id : NULL;
			$membership->save();
			
			/**
			 * Rules Event: Member Invited
			 */
			if ( \IPS\Application::appIsEnabled( 'rules' ) )
			{
				\IPS\rules\Event::load( 'collab', 'Collaboration', 'member_invited' )->trigger( $member, $sponsor, $collab, $membership );
			}
			
			if ( ! $skipNotification )
			{
				// Send "Invited" Notification
				$notification = new \IPS\Notification( \IPS\Application::load( 'collab' ), 'collab_invitation_received', $membership, array( $membership->sponsor(), $collab, $membership ) );
				$notification->recipients->attach( $membership->member() );
				$notification->send();
			}
			
			return $membership;
		}
		
		return NULL;
	}
	
	/**
	 * Approve Member to Collab
	 */
	
	/**
	 * Collab member permission check
	 *
	 * @param	string			$perm		A string representing a permission to check for
	 * @param	\IPS\Member|NULL	$member		The member to check for (NULL for currently logged in member)
	 * @param	array			$params		Optional parameters which can be used to determine authorization
	 * @return	bool
	 */
	public function collabCan( $perm, $member=NULL, $params=array() )
	{
		$member 	= $member ?: \IPS\Member::loggedIn();
		$collabCan 	= FALSE;
		
		if ( !( $member instanceOf \IPS\Member ) )
		{
			throw new \UnexpectedValueException();
		}
		
		/**
		 * Some moderators may be able to bypass collab permissions
		 */
		if ( $member->modPermission( 'can_bypass_collab_permissions' ) )
		{
			$collabCan = TRUE;
		}
		
		/**
		 * Collab owners can bypass permissions if they have not been restricted
		 */
		if ( $member->member_id === $this->owner_id and ! $this->container()->bitoptions[ 'restrict_owner' ] )
		{
			$collabCan = TRUE;
		}
		
		/* Check permissions based on membership roles ( collab owners are also super ) */
		if ( $membership = $this->getMembership( $member ) )
		{	
			/* Basic Membership Permission Test */
			$collabCan = $membershipCan = 
			(
				( $collabCan or $membership->can( $perm ) ) and $membership->status === \IPS\collab\COLLAB_MEMBER_ACTIVE
			);
			
			/* More Advanced Permission Tests */
			switch ( $perm )
			{
				case 'inviteMember':
				
					if ( isset ( $params[ 'invitee' ] ) )
					{
						$collabCan = 
						( 
							$membershipCan and 
							$this->join_mode != \IPS\collab\COLLAB_JOIN_DISABLED and
							$this->canJoin( $params[ 'invitee' ] )
						);
					}
					break;
			}
		}
		
		return $collabCan;
	}
	
	/**
	 * Authorize an object against this collab
	 *
	 * @param	mixed		$obj		Object which should belong to this collab
	 * @return	
	 */
	public function authObj( $obj, $error=TRUE )
	{
		if ( $obj->collab_id !== $this->collab_id )
		{
			if ( ! $error )
			{
				return FALSE;
			}
			
			/** 
			 * This may have been triggered from inside a node controller
			 * on the front end which uses our theme hack...
			 * so we make sure to disable it for the error output.
			 */
			\IPS\Theme::i()->defaultLocation = NULL;
			\IPS\Output::i()->error( 'collab_asset_mismatch', '2CA00/C', 403 );
		}
		
		return $obj;
	}
	
	/**
	 * Can view hidden comments on this item?
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for (NULL for currently logged in member)
	 * @return	bool
	 */
	public function canViewHiddenComments( $member=NULL )
	{		
		return $this->collabCan( 'moderateContent', $member ) or call_user_func_array( 'parent::canViewHiddenComments', func_get_args() );
	}

	/**
	 * Saved Actions
	 *
	 * @return	array|NULL
	 */
	public function availableSavedActions()
	{
		return NULL;
	}
	
	/**
	 * Get A Collab Membership
	 *
	 * @param	\IPS\Member		$member		Member to check collab membership for
	 * @param	bool			$bypassCache	Bypass cache and recheck for existing membership
	 * @return	array|FALSE
	 */
	public function getMembership( \IPS\Member $member=NULL, $bypassCache=FALSE )
	{
		$member = $member ?: \IPS\Member::loggedIn();
		
		if ( isset( $this->memberships[ $member->member_id ] ) and ! $bypassCache )
		{
			return $this->memberships[ $member->member_id ];
		}
	
		$membership = \IPS\collab\Application::collabMembership( $this, $member ) ?: FALSE;
		
		return $this->memberships[ $member->member_id ] = $membership;
	}
	
	/**
	 *  @brief  Storage for guest accounts
	 */	
	protected $guests = array();
	
	/**
	 * Get or Create A Guest Record
	 *
	 * @param	\IPS\Member		$member		Member to check collab membership for
	 * @param	boolean			$create		Create a guest record if it doesn't already exist
	 * @return	array|FALSE
	 */
	public function guest( \IPS\Member $member, $create=TRUE )
	{
		if ( $member->member_id )
		{
			if ( isset ( $this->guests[ $member->member_id ] ) )
			{
				return $this->guests[ $member->member_id ];
			}
			
			try
			{
				$guest = \IPS\collab\Collab\Guest::load( \IPS\Db::i()->select( 'id', 'collab_guests', array( 'member_id=? AND collab_id=?', $member->member_id, $this->collab_id ) )->first() );
			}
			catch ( \UnderflowException $e )
			{
				if ( $create )
				{
					$guest = new \IPS\collab\Collab\Guest;
					$guest->member_id = $member->member_id;
					$guest->collab_id = $this->collab_id;
					$guest->posts = 0;
					$guest->save();
				}
				else
				{
					return FALSE;
				}
			}
			
			return $this->guests[ $member->member_id ] = $guest;
		}
	}

	/**
	 * Get Collab Member Roles
	 *
	 * @return	\IPS\Db\Select|array
	 */
	public function roles( $id=NULL )
	{
		if ( isset( $this->roles ) )
		{
			return $id ? $this->roles[ $id ] : $this->roles;
		}
		
		$roles = array();
		try
		{
			$roles = iterator_to_array
			( 
				new \IPS\Patterns\ActiveRecordIterator
				( 
					\IPS\Db::i()->select( '*', 'collab_roles', array( 'collab_id=?', $this->collab_id ), 'weight ASC' )->setKeyField( 'id' ),
					'IPS\collab\Collab\Role'
				)
			);
		}
		catch ( \UnderflowException $e ) {}
		
		$this->roles = $roles;
		return $id ? $this->roles[ $id ] : $this->roles;
	}
	
	/**
	 * Get Multiple Collab Memberships
	 *
	 * @param 	string	$params		parameters to control the memberships query (statuses, permissions, count only)
	 * @return	mixed
	 */
	public function memberships( $params=array(), $extraWhere=array() )
	{	
		$cache_key = md5( json_encode( $params ) );
		if ( isset ( $this->memberships[ $cache_key ] ) )
		{
			return $this->memberships[ $cache_key ];
		}
		
		$_where 	= array( array( 'collab_memberships.collab_id=?', $this->collab_id ) );
		$_count 	= isset ( $params[ 'count' ] ) and $params[ 'count' ] == TRUE;
		$_limit		= NULL;
		$_order		= NULL;
		$_group		= NULL;
		$_join_type	= 'LEFT';
		
		if ( ! $_count )
		{
			$_order = 'COALESCE( collab_roles.weight, 999 ) ASC, collab_memberships.joined ASC';
		}
		
		if ( isset ( $params[ 'limit' ] ) )
		{
			$_limit = $params[ 'limit' ];
		}
		
		if ( isset ( $params[ 'statuses' ] ) )
		{
			$_where[] = array( \IPS\Db::i()->findInSet( 'collab_memberships.status', (array) $params[ 'statuses' ] ) );
		}
		
		if ( isset ( $params[ 'permissions' ] ) )
		{
			$perm_requisites = array();
			$perm_paths = \IPS\collab\Application::flattenPermissions( $this->collabPermissions(), NULL, TRUE );
			$perm_where = array();
			foreach ( (array) $params[ 'permissions' ] as $perm )
			{
				foreach ( array_keys ( $perm_paths ) as $path )
				{
					$bits = explode( '/', $path );
					if ( end( $bits ) === $perm )
					{
						foreach ( $bits as $bit )
						{
							$perm_requisites[ $bit ] = TRUE;
						}
						break;
					}
				}
			}

			foreach ( array_keys( $perm_requisites ) as $perm )
			{
				$perm_where[] = \IPS\Db::i()->findInSet( 'collab_roles.perms', array( $perm ) );
			}
			
			if ( empty( $perm_where ) )
			{
				$_where[] = array( '1=0' );
			}
			else
			{
				$_where[] = array( '( ' . implode( ' AND ', $perm_where ) . ' ) ' );
			}
			
			$_join_type = 'INNER';
		
		}
		
		if ( ! empty ( $extraWhere ) )
		{
			$_where[] = $extraWhere;
		}
		
		$select = \IPS\Db::i()->select( $_count ? 'COUNT( DISTINCT(collab_memberships.id) )' : 'collab_memberships.*', 'collab_memberships', $_where, $_order, $_limit, $_group );
		$select->join( 'collab_roles', 'FIND_IN_SET( collab_roles.id, collab_memberships.roles )', $_join_type );
		
		if ( $_count )
		{
			return $this->memberships[ $cache_key ] = $select->first();
		}
		
		$select->setKeyField( 'id' );
		
		try
		{
			$this->memberships[ $cache_key ] = iterator_to_array( 
				new \IPS\Patterns\ActiveRecordIterator( $select, 'IPS\collab\Collab\Membership' )
			);
		}
		catch ( \UnderflowException $e ) {}
		
		return $this->memberships[ $cache_key ];
	}

	/*
	 *  Collab comments are used in the context of an activity feed by default,
	 *  so we want to show the newest ones first
	 */		
	public function comments( $limit=NULL, $offset=NULL, $order='date', $orderDirection=NULL, $member=NULL, $includeHiddenComments=NULL, $cutoff=NULL, $extraWhereClause=NULL, $bypassCache=FALSE )
	{
		if ( $order === 'date' and $orderDirection === NULL )
		{
			$orderDirection = 'desc';
		}
		
		$orderDirection = $orderDirection ?: 'asc';
		
		return parent::comments( $limit, $offset, $order, $orderDirection, $member, $includeHiddenComments, $cutoff, $extraWhereClause );
	}

	/*
	 *  Collab reviews are used in the context of an activity feed by default,
	 *  so we want to show the newest ones first
	 */		
	public function reviews( $limit=NULL, $offset=NULL, $order=NULL, $orderDirection=NULL, $member=NULL, $includeHiddenReviews=NULL, $cutoff=NULL, $extraWhereClause=NULL )
	{
		$orderDirection = $orderDirection ?: 'desc';
		
		return parent::reviews( $limit, $offset, $order, $orderDirection, $member, $includeHiddenReviews, $cutoff, $extraWhereClause );
	}
	
	/**
	 * Stats for table view
	 *
	 * @param	bool	$includeFirstCommentInCommentCount	Determines whether the first comment should be inlcluded in the comment count (e.g. For "posts", use TRUE. For "replies", use FALSE)
	 * @return	array
	 */
	public function stats( $includeFirstCommentInCommentCount=TRUE )
	{
		$stats = array
		(
			'items' => $this->getTotal( '_items' ),
			'posts' => $this->getTotal( '_comments' ),
		);

		$stats = array_merge( $stats, parent::stats( $includeFirstCommentInCommentCount ) );

		unset( $stats[ 'comments' ] );
		unset( $stats[ 'num_views' ] );
		
		return $stats;
	}

	/**
	 * Can delete?
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for (NULL for currently logged in member)
	 * @return	bool
	 */
	public function canDelete( $member=NULL )
	{
		return parent::canDelete( $member );
	}
	
	/**
	 * Can Make Model?
	 *
	 * @return	void
	 */
	public function canMakeModel( $member=NULL )
	{
		$member = $member ?: \IPS\Member::loggedIn();
		return ! $this->is_template and $member->isAdmin();
	}
	
	/**
	 * Can UnMake Model?
	 *
	 * @return	void
	 */
	public function canUnmakeModel( $member=NULL )
	{
		$member = $member ?: \IPS\Member::loggedIn();
		return $this->is_template and $member->isAdmin();
	}	
	
	/**
	 * Delete Record
	 *
	 * @return	void
	 */
	public function delete( $options=array() )
	{
		$_nodes_to_delete = array();
		
		/* Switch the affective collab so the roots() method produces the correct results */
		$affectiveCollab = \IPS\collab\Application::switchCollab( $this );
		
		/* Discover all nodes (and content) attached to this collab that needs to be deleted */
		foreach ( \IPS\collab\Application::collabOptions() as $app => $nodes )
		{
			foreach ( $nodes as $node )
			{
				$nid = md5( $node[ 'node' ] );
				
				/* Check if collabs have been provisioned on this node */
				if ( \IPS\Db::i()->checkForColumn( $node['node']::$databaseTable, $node['node']::$databasePrefix . 'collab_id' ) )
				{
					/* Check for special option to leave this node type alone (used by uninstaller) */
					if ( ! isset ( $options[ 'keep_nodes' ] ) or ! in_array( $nid, (array) $options[ 'keep_nodes' ] ) )
					{
						$_nodes_to_delete = array_merge( $_nodes_to_delete, $this->nodeFamily( $node[ 'node' ]::roots( NULL, NULL, array( array( $node['node']::$databasePrefix . 'collab_id=?', $this->collab_id ) ) ) ) );
					}
				}
			}
		}
		
		/* Restore original affective collab */
		\IPS\collab\Application::switchCollab( $affectiveCollab );

		/* Queue discovered nodes (and content) for deletion */
		foreach ( $_nodes_to_delete as $_node )
		{
			/* Put up the yellow tape */
			if ( in_array( 'IPS\Node\Permissions', class_implements( $_node ) ) )
			{
				\IPS\Db::i()->update( 'core_permission_index', array( 'perm_view' => '' ), array( "app=? AND perm_type=? AND perm_type_id=?", $_node::$permApp, $_node::$permType, $_node->_id ) );
			}
			
			/* Zip up the body bag */
			\IPS\Task::queue( 'core', 'DeleteOrMoveContent', 
				array( 
					'class' 		=> get_class( $_node ), 
					'id' 			=> $_node->_id, 
					'deleteWhenDone'	=> TRUE, 
					'additional' 		=> array() 
				) 
			);
		}
		
		/* Delete memberships */
		\IPS\Db::i()->delete( 'collab_memberships', array( 'collab_id=?', $this->collab_id ) );

		/* Delete roles */
		\IPS\Db::i()->delete( 'collab_roles', array( 'collab_id=?', $this->collab_id ) );
		
		/* Delete comments, reviews, tags, follows, and notifications via core */
		parent::delete();
	}
	
	/**
	 * Get all the nodes in the family tree
	 *
	 * @return	void
	 */
	public function nodeFamily( $siblings )
	{
		$_familyline = array();
		foreach ( $siblings as $sibling )
		{
			$_familyline[] = $sibling;
			if ( $sibling->hasChildren( NULL ) )
			{
				$_familyline = array_merge( $_familyline, $this->nodeFamily( $sibling->children( NULL ) ) );
			}
		}
		return $_familyline;
	}
	
	/**
	 * Add A Collab Model
	 *
	 * @param 	\IPS\collab\Collab 	$collab		The collab model to replicate to this collab
	 * @return	void
	 */
	public function addModel( \IPS\collab\Collab $collab )
	{
		$lang 						= \IPS\Member::loggedIn()->language();
		$lang->words[ 'copy' ] 				= "";
		$lang->words[ 'copy_noun' ]			= "";		
		$affectiveCollab				= \IPS\collab\Application::switchCollab( $collab );
		
		/* Copy Roles */
		$_role_map = array();
		foreach ( $collab->roles() as $role )
		{
			$role->collab_id = $this->collab_id;
			$copy = clone $role;
			$role->collab_id = $collab->collab_id;
			$_role_map[ $role->id ] = $copy->id;
		}
		
		/* Copy Nodes */
		foreach( $collab->enabledNodes() as $app => $config )
		{
			foreach ( $config[ 'nodes' ] as $node )
			{				
				foreach ( $node[ 'node' ]::roots( NULL ) as $root )
				{
					$this->addNodeModel( $root, $node[ 'node' ]::$databaseColumnParentRootValue, $_role_map );
				}
			}
		}
		
		/* Copy Join Mode */
		$this->join_mode = $collab->join_mode;
		
		/* Copy Default Member Title */
		$this->default_member_title = $collab->default_member_title;
		
		$this->save();
		
		\IPS\collab\Application::switchCollab( $affectiveCollab );
		
		/* Recount collab totals to reset any miscalculations generated by the cloning process */
		$collab->recountAll();
	}

	/**
	 * Add Model Nodes
	 *
	 * @return	void
	 */
	public function addNodeModel( $node, $parent_id, $role_map=array() )
	{
		$copy 			= clone $node;
		$parentColumn		= $copy::$databaseColumnParent;
		$idColumn		= $copy::$databaseColumnId;
		
		if ( $parentColumn !== NULL )
		{
			$copy->$parentColumn = $parent_id;
		}
		
		$copy->collab_id = $this->collab_id;
		$copy->save();
		
		if ( $node instanceof \IPS\Node\Permissions )
		{
			/* Re-map Permissions */
			$collabPermissions 	= $node->collabPermissions();
			$_new_permissions 	= array();
			
			foreach( array( 'perm_view', 'perm_2', 'perm_3', 'perm_4', 'perm_5', 'perm_6', 'perm_7' ) as $perm )
			{
				$_new_roles = array();
				foreach( explode( ',', $collabPermissions[ $perm ] ) as $_role_id )
				{
					$_new_roles[] = intval( $_role_id ) > 0 ? $role_map[ $_role_id ] : $_role_id;
				}
				$_new_permissions[ $perm ] = implode( ',', array_filter( $_new_roles, 'mb_strlen' ) );
			}
			
			/* Apply Collab Permissions */
			$copy->setCollabPermissions( $_new_permissions );			
		}
		
		if ( $node->hasChildren( NULL ) )
		{			
			foreach ( $node->children( NULL ) as $child )
			{
				$this->addNodeModel( $child, $copy->$idColumn, $role_map );
			}
		}
	}
	
	/**
	 * Merge Collabs
	 *
	 * @param	array	$items	Items to merge in
	 * @return	void
	 */
	public function mergeIn( array $items )
	{		
		$db = \IPS\Db::i();
		foreach ( $items as $item )
		{
			if ( $item instanceof \IPS\collab\Collab )
			{
				foreach ( \IPS\collab\Application::collabOptions() as $app => $nodes )
				{
					foreach ( $nodes as $node )
					{
						$nid = md5( $node[ 'node' ] );
						/* Check if collabs have been provisioned on this node */
						if ( \IPS\Db::i()->checkForColumn( $node['node']::$databaseTable, $node['node']::$databasePrefix . 'collab_id' ) )
						{
							/* Reassign all nodes to this collab */
							$db->update( $node['node']::$databaseTable, array( $node['node']::$databasePrefix . 'collab_id' => $this->collab_id ), array( $node['node']::$databasePrefix . 'collab_id=?', $item->collab_id ) );
						}
					}
				}
				
				/* Merge Memberships */
				foreach ( $item->memberships() as $membership )
				{
					if ( ! $_membership = $this->getMembership( $membership->member() ) )
					{
							$membership->collab_id = $this->collab_id;
							$membership->roles = '';
							$membership->save();
					}
				}
				
			}
		}
		
		/* Merge Comments and Reviews */
		parent::mergeIn( $items );
	}

	/**
	 * Check if a url is a collab category url
	 *
	 * @param	\IPS\Http\Url|string	$url		A url object or string url
	 * @return	bool
	 */
	public static function checkAndLoadUrl( $url )
	{
		if ( ! ( $url instanceof \IPS\Http\Url ) )
		{
			$url = new \IPS\Http\Url( $url );
		}
		
		$qs = $url->queryString;
		try
		{
			$qs = array_merge( $qs, $url->getFriendlyUrlData() );
		}
		catch( \Exception $e ) { }
	
		if 
		(
			$qs[ 'app' ] == 'collab' and 
			$qs[ 'module' ] == 'collab' and 
			$qs[ 'controller' ] == 'collabs' and 
			$qs[ 'id' ] > 0
		)
		{
			try
			{
				$collab = static::loadFromUrl( $url );
				return $collab;
			}
			catch( \Exception $e ) { }
		}
		
		return NULL;
	}

	/**
	 * [ActiveRecord] Duplicate
	 *
	 * @return	void
	 */
	public function __clone()
	{
		if( $this->skipCloneDuplication === TRUE )
		{
			return;
		}

		$oldId = $this->collab_id;
		parent::__clone();
		
		$collab = \IPS\collab\Collab::load( $oldId );
		$this->addModel( $collab );
	}
	
	
}