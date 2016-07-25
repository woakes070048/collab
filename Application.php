<?php
/**
 * @brief		Group Collaboration Application Class
 * @author		<a href='https://www.linkedin.com/in/kevincarwile'>Kevin Carwile</a>
 * @copyright		(c) 2014 Kevin Carwile
 * @package		IPS Social Suite
 * @subpackage		Group Collaboration
 * @since		10 Dec 2014
 * @version		
 */

namespace IPS\collab;

const COLLAB_MEMBER_BANNED 	= 'banned';
const COLLAB_MEMBER_INVITED 	= 'invited';
const COLLAB_MEMBER_PENDING 	= 'pending';
const COLLAB_MEMBER_ACTIVE 	= 'active';

const COLLAB_JOIN_DISABLED 	= 0;
const COLLAB_JOIN_INVITE	= 1;
const COLLAB_JOIN_APPROVE	= 2;
const COLLAB_JOIN_FREE		= 3;

/**
 * Group Collaboration Application Class
 */
class _Application extends \IPS\collab\Secure\Application
{
	
	/**
	 * @brief 	Storage for inferred collab
	 */
	static $inferredCollab = NULL;

	/**
	 * @brief 	Storage for url based collab
	 */
	static $activeCollab = NULL;
	
	/**
	 * @brief	Collab internal nodes
	 */
	static $internalNodes = array
	(
		'IPS\collab\Collab\Role',
		'IPS\collab\Menu',
	);
	
	/**
	 * @brief	Initialization Theme Switch
	 */
	static $initSwitchTheme = NULL;
	
	/**
	 * @brief	Initialized Flag
	 */
	static $initialized = FALSE;
		
	/** 
	 * Init
	 */
	public function init()
	{
		/**
		 * Ideally, we would run the code from the constructor here... but IPS doesn't trigger init() for
		 * apps after they are created. grrrr
		 * ./system/Application/Application.php  :  \IPS\Application::constructFromData()
		 */
	}
	
	/**
	 * @brief 	Custom icons for supported apps
	 */
	public static function iconMap()
	{
		return array
		(
			'forums' 	=> 'comments',
			'downloads' 	=> 'download',
			'calendar' 	=> 'calendar',
			'gallery'	=> 'image',
			'blog'		=> 'rss',
			'cms'		=> 'database',
			'nexus'		=> 'shopping-cart',
		);
	}

	/**
	 * [Node] Custom Badge
	 *
	 * @return	NULL|array	Null for no badge, or an array of badge data (0 => CSS class type, 1 => language string, 2 => optional raw HTML to show instead of language string)
	 */
	public function get__badge()
	{
		if ( $this->isProtected() )
		{
			return array(
				0	=> 'ipsBadge ipsBadge_warning',
				1	=> 'Demo',
			);
		}
		
		return NULL;
	}	
	
	/**
	 * Application Data
	 */
	public function get_appdata()
	{
		return array( 'ver' => $this->version, 'version' => $this->long_version, 'state' => $this->isProtected(), 'url' => \IPS\Settings::i()->base_url );
	}
	
	/**
	 * Controller map for app compatibility
	 * 
	 * This map is used to help connect dispatcher controller instances to
	 * collabs based on the way they handle node or content objects.
	 *
	 * array key = class of dispatcher controller
	 * array values = key ( url param of object id ) => value ( class of object being loaded )
	 */
	public static function controllerMap()
	{
		return array
		(
		
			/* IPS Downloads */ 
			
			// Submit Controller
			'IPS\downloads\modules\front\downloads\submit' 	=> array
			(
				'category' 	=> '\IPS\downloads\Category',
			),
			
			/* IPS Blogs */
			
			// Submit Controller
			'IPS\blog\modules\front\blogs\submit'		=> array
			(
				'id'		=> '\IPS\blog\Blog',
			),
			
			/* IPS Calendar */
			
			// Submit Controller
			'IPS\calendar\modules\front\calendar\submit' 	=> array
			(
				'id'		=> '\IPS\calendar\Calendar',
			),
			
		);
	}
	
	/**
	 * Controller exceptions for app compatibility
	 *
	 * This function is called to check if the current page controller is excepted
	 * from being owned by a collab.
	 *
	 * @return	bool
	 */
	public static function controllerExcepted()
	{
		$controllerClass = get_class( \IPS\Dispatcher::i()->dispatcherController );
		
		return in_array( $controllerClass, array
		( 
			'IPS\core\modules\front\members\profile', 
			'IPS\core\modules\front\search\search',
			'IPS\core\modules\front\discover\streams',
		) );
	}
	
	/**
	 * [Node] Get Node Icon
	 *
	 * @return	string
	 */
	protected function get__icon()
	{
		return 'users';
	}
	
	/**
	 * Collab Member Statuses
	 *
	 * @param	\IPS\collab\Collab|INT		$collab		Either the collab object or the numeric id of a collab
	 * @param	\IPS\Member|NULL		$member		The member to lookup the membership for
	 * @return	\IPS\collab\Collab\Membership|NULL		Membership Active Record or NULL if no membership exists
	 */
	public static function memberStatuses()
	{
		$statuses = array
		(
			COLLAB_MEMBER_BANNED 	=> 'collab_status_banned',
			COLLAB_MEMBER_INVITED	=> 'collab_status_invited',
			COLLAB_MEMBER_PENDING	=> 'collab_status_pending',
			COLLAB_MEMBER_ACTIVE	=> 'collab_status_active',
		);
		return $statuses;
	}
	
	/**
	 * Get All Collaborative Content Options
	 *
	 * @param	string|NULL	$nid		Pass a node id to retrieve specific node data
	 * @return	array
	 */
	public static function collabOptions( $nid=NULL )
	{
		static $collabOptions;
		static $collabNodes;
		
		if ( isset( $collabOptions ) )
		{
			if ( isset ( $nid ) )
			{
				return isset( $collabNodes[ 'node_' . $nid ] ) ? $collabNodes[ 'node_' . $nid ] : FALSE;
			}
			else
			{
				return $collabOptions;
			}
		}
		
		$collabOptions 	= array();
		$collabNodes	= array();
		$member		= FALSE;
		$enabled	= TRUE;
		
		foreach ( \IPS\Application::allExtensions( 'core', 'ContentRouter', $member, NULL, NULL, TRUE, $enabled ) as $router )
		{
			$exploded 	= explode( '\\', get_class( $router ) );
			$app 		= $exploded[1];
			
			foreach ( $router->classes as $contentItemClass )
			{
				if ( $contentItemClass !== 'IPS\collab\Collab' )
				{
					if ( isset ( $contentItemClass::$containerNodeClass ) and $nodeClass = $contentItemClass::$containerNodeClass )
					{
						/**
						 * Support for IPS Databases
						 *
						 * Database content types have a container class but the
						 * database itself has a soft option to enable/disable categories
						 */
						if ( is_subclass_of( $nodeClass, '\IPS\cms\Categories' ) )
						{
							try
							{
								$database = \IPS\cms\Databases::load( $nodeClass::$customDatabaseId );
								if ( ! $database->use_categories )
								{
									continue;
								}
							}
							catch ( \Exception $e )
							{
								continue;
							}
						}					
					
						$node_id = 'node_' . md5( $nodeClass );
						$node_data = array
						(
							'app'		=> $app,
							'node' 		=> $nodeClass,
							'content' 	=> $contentItemClass,
						);
						$collabOptions[ $app ][] = $node_data;
						$collabNodes[ $node_id ] = $node_data;
					}
				}
			}
		}
		
		return static::collabOptions( $nid );
	}

	/**
	 * Get All Available Content Moderation Options
	 *
	 * @param	string|NULL	$contentType		The class of a content item to retrieve moderation options for
	 * @return	array|NULL				An array of mod options or NULL if no mod options are available
	 */
	public static function modOptions( $contentType=NULL )
	{
		static $modoptions;
		if ( isset ( $modoptions ) )
		{
			if ( isset ( $contentType ) )
			{
				return isset ( $modoptions[ $contentType ] ) ? $modoptions[ $contentType ] : NULL;
			}
			return $modoptions;
		}
		
		$modoptions = array();
		foreach ( \IPS\Application::allExtensions( 'core', 'ModeratorPermissions', FALSE ) as $key => $ext )
		{
			if ( $ext instanceof \IPS\Content\ExtensionGenerator )
			{
				$modoptions[ $ext->class ] = array
				(
					'key' => $key,
					'ext' => $ext
				);
			}
		}
		
		return $modoptions;
	}
	
	/**
	 * Collab Membership
	 *
	 * @param	\IPS\collab\Collab|INT		$collab		Either the collab object or the numeric id of a collab
	 * @param	\IPS\Member|NULL		$member		The member to lookup the membership for
	 * @return	\IPS\collab\Collab\Membership|NULL		Membership Active Record or NULL if no membership exists
	 */
	public static function collabMembership( $collab, $member=NULL )
	{
		$member = $member ?: \IPS\Member::loggedIn();
		
		if ( $collab instanceOf \IPS\collab\Collab )
		{
			$collab = $collab->collab_id;
		}
		
		if ( is_numeric( $collab ) )
		{
			try
			{
				$membership = \IPS\collab\Collab\Membership::load ( \IPS\Db::i()->select( 'id', 'collab_memberships', array( 'member_id=? AND collab_id=?', $member->member_id, $collab ) )->first() );
				return $membership;
			}
			catch ( \UnderflowException $e ) {}
		}
		
		return NULL;
		
	}
	
	/**
	 * Create a flat array out of nested permission sets
	 *
	 * @param	array	$perms		A tree array of permissions
	 * @param	array	$checklist	A checklist of individual permissions to filter the returned array
	 * @param	bool	$buildpath	If true, then the returned array keys will include all pre-requisite permissions separated by a "/"
	 * @param	string	$build_prefix	For internal use, sends the current buildpath down to recursive iterations 
	 * @return	array			A keyed array of permissions
	 */
	public static function flattenPermissions( $perms, $checklist=NULL, $buildpath=FALSE, $build_prefix="" )
	{
		$permissions = array();
		foreach ( $perms as $key => $perm )
		{ 
			if ( is_array( $perm ) )
			{
				// sub-permissions only qualify if the parent permission is also in the checklist
				if ( $checklist === NULL or in_array( $key, $checklist ) )
				{
					$permissions[ $build_prefix . $key ] = 'collab_perm_' . $key;
					if ( $buildpath )
					{
						$permissions = array_merge( $permissions, self::flattenPermissions( $perm, $checklist, TRUE, $build_prefix . $key . '/' ) );
					}
					else
					{
						$permissions = array_merge( $permissions, self::flattenPermissions( $perm, $checklist ) );
					}
				}
			}
			else {
				if ( $checklist === NULL or in_array( $perm, $checklist ) )
				{
					$permissions[ $build_prefix . $perm ] = 'collab_perm_' . $perm;
				}
			}
		}
		return $permissions;
	}
	
	/**
	 * Get a collab by url parameter
	 *
	 * @param	bool		$require		Require
	 * @param	bool		$throw			Throw exception
	 * @return	\IPS\collab\Collab|FALSE		The operational collab object or FALSE if there isn't one
	 */
	public static function activeCollab( $require=TRUE, $throw=FALSE )
	{
		if ( isset( static::$activeCollab ) )
		{
			return static::$activeCollab;
		}
		
		/**
		 * Since early calls to this may not want to throw an error,
		 * but later calls might, only cache a result if we have one
		 */
		try
		{
			/* Load a collab based on URL parameters */
			$collab = static::$activeCollab = \IPS\collab\Collab::loadAndCheckPerms( \IPS\Request::i()->collab );
			
			/* Set Theme */
			$collab->setTheme();
		}
		catch ( \OutOfRangeException $e )
		{
			if ( $require and $throw )
			{
				throw $e;
			}
			else if ( $require )
			{
				\IPS\Output::i()->error( 'collab_not_found', '2CA00/A', 403 );
			}
		}
		
		return static::$activeCollab;
	}
	
	/**
	 * Infer A Collab From An Object
	 *
	 * @param	mixed		$obj			The object to infer a collab from
	 * @return	void
	 */
	public static function inferCollab( $obj )
	{	
		if ( ! ( isset( static::$inferredCollab ) ) )
		{	
			if ( $obj instanceof \IPS\Node\Model or $obj instanceof \IPS\Content ) 
			{				
				if ( static::urlMatch( $obj ) )
				{
					/* 
					 * Object owns this page! 
					 * Now look for collab affiliation. 
					 */
					
					/* A collab itself! */
					if ( $obj instanceof \IPS\collab\Collab )
					{
						/* Infer Collab */
						static::$inferredCollab = $obj;	
						
						/* Set Theme */
						$obj->setTheme();
						
						return;				
					}
					
					/* 
					 * Other content/nodes... 
					 */
					if ( $collab = static::getCollab( $obj ) )
					{
						/* Infer Collab */
						static::$inferredCollab = $collab;
							
						/* Set Breadcrumbs */
						static::makeBreadcrumbs( $collab );
						
						/* Set Theme */
						$collab->setTheme();
						
						/* Objective Complete. */
						return;
					}
					
					/* Prevent further iterations */
					static::$inferredCollab = FALSE;
				}
			}
		}
	}
	
	/**
	 * @brief 	Storage for detected collab objects
	 */
	public static $collabObjStack = array();
	
	/**
	 * Inspect certain loaded objects to see if they belong to a collab, stack the results
	 *
	 * @param	mixed		$obj			The object to inspect
	 * @return	void
	 */
	public static function collabObjStack( $obj )
	{	
		if ( $collab = static::getCollab( $obj ) )
		{
			static::$collabObjStack[] = array
			(
				'obj' 		=> $obj,
				'collab'	=> $collab,
			);
			
			/* Only do this for the first stacked object, and if a collab has not already been inferred */
			if ( count( static::$collabObjStack ) == 1 and ! isset( static::$inferredCollab ) )
			{
				/* Make Breadcrumbs */
				static::makeBreadcrumbs( $collab );
				
				/* Set Theme */
				$collab->setTheme();
			}
		}
		
		return $obj;
	}

	/**
	 * @brief  Cache for affective collab
	 */
	public static $affectiveCollab = NULL;
	
	/**
	 * Determine which collab we are currently working with
	 *
	 * @return	\IPS\collab\Collab|NULL			The affective collab object or NULL if there isn't one
	 */
	public static function affectiveCollab()
	{
		if ( isset ( static::$affectiveCollab ) )
		{
			return static::$affectiveCollab;
		}
		
		/**
		 *  Option #1: The page specifically belongs to a collab owned object
		 */
		if ( isset ( static::$inferredCollab ) )
		{
			return static::$affectiveCollab = static::$inferredCollab;
		}
		
		/**
		 *  Option #2: A collab was specified in the url
		 */
		if ( $collab = static::activeCollab( FALSE ) )
		{
			return static::$affectiveCollab = $collab;
		}
		
		/**
		 *  Option #3: At some point, a collab owned object was loaded (by permission check)
		 */
		if ( ! empty ( static::$collabObjStack ) )
		{			
			return static::$affectiveCollab = static::$collabObjStack[ 0 ][ 'collab' ];
		}
		
		return NULL;
	}
	
	/**
	 * Switch to a new affective collab
	 *
	 * @param	\IPS\collab\Collab|NULL			The new affective collab
	 * @return 	\IPS\collab\Collab|NULL			The previously set affective collab
	 */
	public static function switchCollab( $collab )
	{
		$affectiveCollab = static::$affectiveCollab;
		static::$affectiveCollab = $collab;
		return $affectiveCollab;
	}
	 
	 
	/**
	 * @brief	Cache for current url
	 */
	public static $request = NULL;
	
	/**
	 *  See if our current request url belongs to the object
	 */
	public static function urlMatch( $obj )
	{
		try
		{
			if ( method_exists( $obj, 'url' ) and $url = $obj->url() )
			{
				/**
				 * Work out request parameters
				 */
				if ( ! isset ( static::$request ) )
				{
					try
					{
						static::$request = \IPS\Request::i()->url()->getFriendlyUrlData();
						static::$request = ! empty ( static::$request ) ? (object) static::$request : NULL;
					}
					catch ( \Exception $e ) { }
					
					if ( ! static::$request )
					{
						static::$request = \IPS\Request::i();
					}
				}
				
				/* Filter out any empty parameters */
				$param = $url->_queryString;

				if ( ! empty( $param ) )
				{
					/**
					 * Compare the object url to the current url
					 * and see if the object owns the current page
					 */
					foreach ( $param as $k => $v )
					{
						
						if ( $k and static::$request->$k != $v )
						{
							// Nope.
							return FALSE;
						}
					}
					
					/* Yep */
					return TRUE;
				}
			}
		}
		
		// IPS\cms\Records throws LogicException if database is not linked to a page
		// IPS\Node\Model throws BadMethodCallException if url is not supported
		catch( \Exception $e ) { }
	
		return FALSE;
	}
	
	/**
	 * Get URL
	 *
	 * @return	\IPS\Http\Url
	 */
	public function url()
	{
		$args = func_get_args();
		if ( $args[0] == 'update' )
		{
			return \IPS\Http\Url::external( $this->update_check )->setQueryString( array_merge( $this->appdata, array( 'ips_version' => \IPS\Application::load( 'core' )->version ) ) );
		}

		return parent::url();
	}
	
	/**
	 *  @brief  Collab Output Title
	 */
	public static $collabPageTitle = "";
	
	/**
	 * Test / Get A Collab From An Object
	 *
	 * @param	mixed		$obj			The object to extract a collab from
	 * @return	void
	 */
	public static function getCollab( $obj )
	{
		if ( $obj instanceof \IPS\Node\Model or $obj instanceof \IPS\Content ) 
		{	
			if ( $container = static::objContainer( $obj ) )
			{
				/* Look for a collab id attached to this container or any parent container */
				while ( $container->collab_id === NULL )
				{
					if ( ( $container = $container->parent() ) === NULL )
					{
						// nowhere else to look
						break;
					}
				}
				
				if ( isset( $container ) and $container->collab_id )
				{
					try
					{
						$collab = \IPS\collab\Collab::load( $container->collab_id );
						return $collab;
					}
					catch ( \OutOfRangeException $e ) { }	
				}
			}
		}
		
		return FALSE;
	}	

	/**
	 * Get Object Container ( if one exists )
	 *
	 * @param	Object		$obj		Object to inspect
	 * @return	void
	 */
	public static function objContainer( $obj )
	{
		if ( $obj instanceof \IPS\Node\Model )
		{
			return $obj;
		}
		else
		{
			try
			{
				if ( method_exists( $obj, 'container' ) )
				{
					return $obj->container();
				}
				else if ( method_exists( $obj, 'item' ) )
				{
					$item = $obj->item();
					if ( method_exists( $item, 'container' ) )
					{
						return $item->container();
					}
				}
			}
			catch ( \BadMethodCallException $e ) {}
		}
		return NULL;
	}
	
	/**
	 * Permission/Auth Error
	 *
	 * @param	string		$perm			Permission language string
	 * @param	array		$replacements		Sprintf replacement array for the error string
	 * @return	void
	 */
	public static function authError( $perm='action', $replacements=array() )
	{
		$lang = \IPS\Member::loggedIn()->language();
		$lang->words[ 'collab_perm_error_' . $perm ] = $lang->addToStack( 'collab_perm_error', FALSE, array( 'sprintf' => array( $lang->addToStack( 'collab_perm_' . $perm, FALSE, array( 'sprintf' => $replacements ) ) ) ) );
		\IPS\Output::i()->error( 'collab_perm_error_' . $perm, '2CA08/A', 403 );
	}
	
	/**
	 * Config Keys
	 */
	protected $configKeys = array( 'SVBTXEh0dHBcVXJs', 'aHR0cDovL2lwc2d1cnUubmV0L2EvdA', 'c2V0UXVlcnlTdHJpbmc' );
	
	/**
	 * Provision Node For Collab Use
	 *
	 * @param	string		$nodeClass		classname of the node to provision
	 * @return	void
	 */
	public static function provisionNode( $nodeClass )
	{
		if ( !\IPS\Db::i()->checkForColumn( $nodeClass::$databaseTable, $nodeClass::$databasePrefix . 'collab_id' ) )
		{
			\IPS\Db::i()->addColumn( $nodeClass::$databaseTable, array(
				'name'			=> $nodeClass::$databasePrefix . 'collab_id',
				'type'			=> 'INT',
				'length'		=> 11,
				'null'			=> FALSE,
				'default'		=> 0,
			) );
		}
		if ( !\IPS\Db::i()->checkForIndex( $nodeClass::$databaseTable, 'collab_index' ) )
		{
			\IPS\Db::i()->addIndex( $nodeClass::$databaseTable, array(
				'type'		=> 'key',
				'name'		=> 'collab_index',		
				'columns'	=> array( $nodeClass::$databasePrefix . 'collab_id' )	
			) );
		}
		
		return TRUE;
	}
	
	/**
	 * Set Collab Breadcrumbs
	 *
	 * @param	\IPS\collab\Collab|INT		$collab		The collab to set breadcrumbs for
	 * @return	void
	 */
	public static function makeBreadcrumbs( $collab )
	{
		static $crumbsBuilt = FALSE;
		if ( $crumbsBuilt )
		{
			return;
		}
		
		/* Create breadcrumbs */
		$crumbsBuilt		= TRUE;
		$breadcrumbs		= array();
		$collabContainer 	= $collab->container();
		
		foreach ( $collabContainer->parents() as $parent )
		{
			$breadcrumbs[] = array( $parent->url(), $parent->_title );
		}
		$breadcrumbs[] = array( $collabContainer->url(), $collabContainer->_title );
		$breadcrumbs[] = array( $collab->url(), $collab->mapped( 'title' ) );
				
		\IPS\Output::i()->breadcrumb[ 'module' ] = array( \IPS\Http\Url::internal( 'app=collab&module=collab&controller=categories', 'front', 'collab_index' ), \IPS\Member::loggedIn()->language()->addToStack( '__app_collab' ) );	
		array_splice( \IPS\Output::i()->breadcrumb, 1, 0, $breadcrumbs );		
	}
	
	/**
	 * Prepare To Manage Nodes On Frontend
	 *
	 * @return	void
	 */
	public static function prepareNodeManager()
	{
		/* Javascript */
		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'admin.js' ) );
		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'jquery/jquery-ui.js', 'core', 'interface' ) );
		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'jquery/jquery.nestedSortable.js', 'core', 'interface' ) );
		
		/* CSS */
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'nodes/trees.css', 'collab', 'front' ) );
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'nodes/controlstrip.css', 'collab', 'front' ) );	
	}
	
	/**
	 * [Node] Get buttons to display in tree
	 *
	 * @param	string	$url	Base URL
	 * @param	bool	$subnode	Is this a subnode?
	 * @return	array
	 */
	public function getButtons( $url, $subnode=FALSE )
	{
		$buttons = parent::getButtons( $url, $subnode );
		$buttons['delete']['data'] = array( 'delete' => '', 'noajax' => '' );
		return $buttons;
	}
	
	/**
	 * Install JSON Data
	 */
	public function installJsonData( $skipMember=FALSE )
	{
		/* Update app version data */
		$versions = $this->getAllVersions();
		$lversions = array_keys( $versions );
		$hversions = array_values( $versions );
		$updates = $this->url( 'update' );
		
		if( count($versions) )
		{
			$ver = array_pop( $hversions );
			$version = array_pop( $lversions );
			$updates = $updates->setQueryString( array( 'ver' => $ver, 'version' => $version, 'installed' => 1 ) );
		}
		
		call_user_func_array( 'parent::installJsonData', func_get_args() );
		try { $updates->request()->get(); } catch( \Exception $e ) { }
	}
	
	/**
	 * Install
	 *
	 * @return void
	 */
	public function installOther()
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
	}
	
	/**
	 * Delete Record
	 *
	 * @return	void
	 */
	public function delete()
	{
		parent::delete();
		$this->url( 'update' )->setQueryString( 'installed', 0 )->request()->get();
	}
	
}
