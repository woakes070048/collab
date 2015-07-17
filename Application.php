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
class _Application extends \IPS\Application
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
	 * @brief 	Controller map for app compatibility
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
			'IPS\downloads\modules\front\downloads\submit' 	=> array(
				'category' 	=> '\IPS\downloads\Category',
			),
			
			/* IPS Blogs */
			
			// Submit Controller
			'IPS\blog\modules\front\blogs\submit'		=> array(
				'id'		=> '\IPS\blog\Blog',
			),
			
		);
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
	 * Init
	 *
	 * @param	void
	 */
	public function init()
	{
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
						$node_data = array(
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
	 * Operational Collab
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
			static::$activeCollab = \IPS\collab\Collab::loadAndCheckPerms( \IPS\Request::i()->collab );
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
						static::$inferredCollab = $obj;								
						return;				
					}
					
					/* 
					 * Other content... 
					 */
					if ( $collab = static::getCollab( $obj ) )
					{
						/* Infer Collab */
						static::$inferredCollab = $collab;
							
						/* Set Breadcrumbs */
						static::makeBreadcrumbs( $collab );
																			
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
	 * @brief	Cache for current url
	 */
	public static $request = NULL;
	
	/**
	 *  See if our current request url belongs to the object
	 */
	public static function urlMatch( $obj )
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
				catch ( \OutOfRangeException $e ) {}
				
				if ( ! static::$request )
				{
					static::$request = \IPS\Request::i();
				}
			}
			
			/* Filter out any empty parameters */
			$param = $url->_queryString;

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
	
		return FALSE;
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
			static::$collabObjStack[] = array(
				'collab_id' 	=> $collab->collab_id,
				'obj' 		=> $obj,
			);
		}
		
		return $obj;
	}

	/**
	 * @brief  Cache for affective collab
	 */
	public static $affectiveCollab = NULL;
	
	/**
	 * Try to determine what collab this page belongs to
	 *
	 * @param	bool		$require		Require
	 * @param	bool		$throw			Throw exception
	 * @return	\IPS\collab\Collab|FALSE		The operational collab object or FALSE if there isn't one
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
			try
			{
				$collab = \IPS\collab\Collab::load( static::$collabObjStack[ 0 ][ 'collab_id' ] );
				static::makeBreadcrumbs( $collab );
				return static::$affectiveCollab = $collab;
			}
			catch ( \OutOfRangeException $e ) {}
		}
		
		return NULL;
	}
	
	/**
	 *  @brief  Collab Output Title
	 */
	public static $collabPageTitle = "";
	
	/**
	 *  Wrap output with collab theming
	 */
	public static function collabWrapContent( $html )
	{			
		$html = (string) $html;
		if ( $collab = static::affectiveCollab() and ! \IPS\Request::i()->isAjax() )
		{
			if ( ! $collab->hidden() or \IPS\collab\Collab::modPermission( 'view_hidden', NULL, $collab->container() ) or \IPS\Member::loggedIn()->member_id === $collab->owner_id )
			{
				$wrapper = 'collabPublicWrapper';
				if 
				( 
					\IPS\Request::i()->app == 'collab' and 
					\IPS\Request::i()->module == 'collab' and 
					\IPS\Request::i()->controller != 'collabs' 
				)
				{
					$wrapper = 'collabAdminWrapper';
				}
				
				$output = \IPS\Theme::i()->getTemplate( 'layouts', 'collab', 'front' )->$wrapper( $collab, $html, static::$collabPageTitle ?: \IPS\Output::i()->title );
				
				/**
				 * @DEMO: Notice
				 */
				if ( \IPS\collab\DEMO )
				{
					foreach ( range( 0, rand( 0, 2 ) ) as $_ )
					{
						$output = "<div style='" . md5( mt_rand() ) . "'>" . $output . "</div>";
					}
					
					$output = "<div style='font-size:22px; text-align:center; margin-bottom: 10px; background: none repeat scroll 0 0 #ede6e0; color: #564a3f; border-radius: 2px; padding: 15px;'><i class='fa fa-warning'></i> Collab Demo Version</div>" . $output;
					
					foreach ( range( 0, rand( 0, 2 ) ) as $_ )
					{
						$output = "<div style='" . md5( mt_rand() ) . "'>" . $output . "</div>";
					}
				}
				
				return $output;
			}
		}
		
		return $html;
	}

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
				
				if ( $container->collab_id )
				{
					try
					{
						$collab = \IPS\collab\Collab::load( $container->collab_id );
						return $collab;
					}
					catch ( \OutOfRangeException $e ) {}	
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
}

if ( defined( '\IPS\collab\DEMO' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	print "Collab demo error.";
	exit;
}

const DEMO = FALSE;