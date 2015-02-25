<?php
/**
 * @brief		Collaboration Categories
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
 * Category Node
 */
class _Category extends \IPS\Node\Model implements \IPS\Node\Permissions, \IPS\Content\Embeddable
{
	/**
	 * @brief	[ActiveRecord] Multiton Store
	 */
	protected static $multitons;
	
	/**
	 * @brief	[ActiveRecord] Database Table
	 */
	public static $databaseTable = 'collab_categories';
	
	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 'category_';
		
	/**
	 * @brief	[Node] Order Database Column
	 */
	public static $databaseColumnOrder = 'weight';
	
	/**
	 * @brief	[Node] Parent ID Database Column
	 */
	public static $databaseColumnParent = 'parent_id';
	
	/**
	 * @brief	[Node] Node Title
	 */
	public static $nodeTitle = 'categories';

	/**
	 * @brief	[Node] ACP Restrictions
	 */
	protected static $restrictions = array(
		'app'		=> 'collab',
		'module'	=> 'collab',
		'prefix'	=> 'categories_'
	);
	
	/**
	 * @brief	[Node] App for permission index
	 */
	public static $permApp = 'collab';
	
	/**
	 * @brief	[Node] Type for permission index
	 */
	public static $permType = 'collab_category';
	
	/**
	 * @brief	The map of permission columns
	 */
	public static $permissionMap = array(
		'view'			=> 'view',
		'read'			=> 2,
		'add'			=> 3,
		'reply'			=> 4,
		'rate'			=> 5,
		'join'			=> 6,
		'review'		=> 7,
	);
	
	/**
	 * @brief	Database Column Map
	 */
	public static $databaseColumnMap = array(
		'cover_photo'			=> 'cover_photo',
		'cover_photo_offset'		=> 'cover_offset',
	);

	/**
	 * @brief	Bitwise values for category_bitoptions field
	 */
	public static $bitOptions = array(
		'bitoptions' => array(
			'bitoptions' => array(
				'allow_comments'		=> 1,
				'allow_reviews'			=> 2,
				'allow_ratings'			=> 4,
				'show_rules'			=> 8,
				'approve_comments'		=> 16,
				'approve_reviews'		=> 32,
				'increase_mainposts'		=> 64,
				'enable_model'			=> 128,
				'require_model'			=> 256,
				'multiple_model'		=> 512,
			)
		)
	);

	/**
	 * @brief	[Node] Prefix string that is automatically prepended to permission matrix language strings
	 */
	public static $permissionLangPrefix = 'collab_';

	/**
	 * @brief	[Node] Title prefix.  If specified, will look for a language key with "{$key}_title" as the key
	 */
	public static $titleLangPrefix = 'collab_category_';
	
	/**
	 * @brief	[Node] Moderator Permission
	 */
	public static $modPerm = 'collab_categories';

	/**
	 * @brief	Content Item Class
	 */
	public static $contentItemClass = 'IPS\collab\Collab';
	
	/**
	 * @brief	Cover Photo Storage Extension
	 */
	public static $coverPhotoStorageExtension = 'collab_Category';
	
	/**
	 *  Disable Copy Button
	 */	
	public $noCopyButton = TRUE;
	
	/**
	 * Init
	 *
	 * @return	void
	 */
	public function init()
	{
		if ( !is_array( $this->_options ) )
		{
			$this->options = \serialize( array() );
		}
		if ( function_exists( 'parent::init' ) )
		{
			return call_user_func_array( 'parent::init', func_get_args() );
		}
	}
	
	/**
	 * Get description
	 *
	 * @return	string
	 */
	protected function get_description()
	{
		return \IPS\Member::loggedIn()->language()->checkKeyExists( "collab_category_{$this->id}_desc" ) ? \IPS\Member::loggedIn()->language()->get( "collab_category_{$this->id}_desc" ) : '';
	}

	/**
	 * Get SEO name
	 *
	 * @return	string
	 */
	public function get_name_seo()
	{
		if( ! $this->_data[ 'name_seo' ] )
		{
			/**
			 * Using a strategy that doesn't die with an EX0 if for some reason the title language
			 * string doesn't exist in the database.
			 */			
			$lang 	= \IPS\Http\Url::seoTitle( \IPS\Lang::load( \IPS\Lang::defaultLanguage() );
			$title 	= 'Category ' . $this->id;
			
			if ( $lang->checkKeyExists( 'collab_category_' . $this->id ) )
			{
				$title = $lang->get( 'collab_category_' . $this->id );
			}
			
			$this->name_seo	= \IPS\Http\Url::seoTitle( $title );
			$this->save();
		}

		return $this->_data[ 'name_seo' ];
	}

	/**
	 * [Node] Get whether or not this node is enabled
	 *
	 * @note	Return value NULL indicates the node cannot be enabled/disabled
	 * @return	bool|null
	 */
	protected function get__enabled()
	{
		return NULL;
	}

	/**
	 * Get sort order
	 *
	 * @return	string
	 */
	public function get__sortBy()
	{
		return $this->sort_options;
	}

	/**
	 * [Node] Get number of content items
	 *
	 * @return	int
	 * @note	We return null if there are non-public albums so that we can count what you can see properly
	 */
	protected function get__items()
	{
		return $this->private_collabs ? NULL : $this->collabs_count;
	}

	/**
	 * [Node] Get number of content comments
	 *
	 * @return	int
	 */
	protected function get__comments()
	{
		return $this->comments_count;
	}

	/**
	 * [Node] Get category options
	 *
	 * @return	array
	 */
	protected function get__options()
	{
		return \unserialize( $this->options );
	}
	
	/**
	 * [Node] Get category moderator permissions
	 *
	 * @return	array
	 */
	protected function get__mod_perms()
	{
		return $this->mod_perms === NULL ? NULL : \unserialize( $this->mod_perms );
	}
	
	/**
	 * [Node] Get number of unapproved content items
	 *
	 * @return	int
	 */
	protected function get__unnapprovedItems()
	{
		return $this->unapproved_collabs_count;
	}
	
	/**
	 * [Node] Get number of unapproved content comments
	 *
	 * @return	int
	 */
	protected function get__unapprovedComments()
	{
		return $this->unapproved_comments_count;
	}
	
	/**
	 * [Node] Get language string for collab singular representation
	 *
	 * @return	string
	 */
	protected function get_collab_singular()
	{
		return \IPS\Member::loggedIn()->language()->checkKeyExists( "collab_cat_{$this->id}_collab_singular" ) ? 
			\IPS\Member::loggedIn()->language()->get( "collab_cat_{$this->id}_collab_singular" ):
			\IPS\Member::loggedIn()->language()->get( "collab_cat__collab_singular" );
	}

	/**
	 * [Node] Get language string for collab plural representation
	 *
	 * @return	string
	 */
	protected function get_collab_plural()
	{
		return \IPS\Member::loggedIn()->language()->checkKeyExists( "collab_cat_{$this->id}_collabs_plural" ) ? 
			\IPS\Member::loggedIn()->language()->get( "collab_cat_{$this->id}_collabs_plural" ):
			\IPS\Member::loggedIn()->language()->get( "collab_cat__collabs_plural" );
	}
	
	/**
	 * Set number of items
	 *
	 * @param	int	$val	Items
	 * @return	void
	 */
	protected function set__items( $val )
	{
		$this->collabs_count = (int) $val;
	}

	/**
	 * Set number of comments
	 *
	 * @param	int	$val	Comments
	 * @return	void
	 */
	protected function set__comments( $val )
	{
		$this->comments_count = (int) $val;
	}

	/**
	 * [Node] Set category options
	 *
	 * @return	array
	 */
	protected function set__options( $val )
	{
		$this->options = \serialize( $val );
	}

	/**
	 * [Node] Set category moderator permissions
	 *
	 * @return	array
	 */
	protected function set__mod_perms( $val )
	{
		$this->mod_perms = \serialize( $val );
	}

	/**
	 * [Node] Set number of unapproved content items
	 *
	 * @param	int	$val	Unapproved Items
	 * @return	void
	 */
	protected function set__unapprovedItems( $val )
	{
		$this->unapproved_collabs_count = $val;
	}
	
	/**
	 * [Node] Set number of unapproved content comments
	 *
	 * @param	int	$val	Unapproved Comments
	 * @return	void
	 */
	protected function set__unapprovedComments( $val )
	{
		$this->unapproved_comments_count = $val;
	}
	
	/**
	 * [Node] Ignore setting of collab_singular
	 *
	 * @param	int	$val	
	 * @return	void
	 */
	protected function set_collab_singular( $val )
	{
		return;
	}

	/**
	 * [Node] Ignore setting of collab_plural
	 *
	 * @param	int	$val	
	 * @return	void
	 */
	protected function set_collab_plural( $val )
	{
		return;
	}
	
	/**
	 * @brief	App/Node Config Cache
	 */
	protected $config = NULL;

	/**
	 * @brief	Cache for nodes
	 */
	protected $nodeConfig = NULL;

	
	/**
	 * @brief	Cache for templates
	 */
	protected $templates = NULL;
	
	/**
	 * [Node] Get collab templates
	 *
	 * @return	array
	 */
	public function templates()
	{
		if ( isset ( $this->templates ) )
		{
			return $this->templates;
		}
		
		$this->templates 	= array();
		
		try
		{
			$this->templates = iterator_to_array( 
				new \IPS\Patterns\ActiveRecordIterator( 
					\IPS\Db::i()->select( '*', 'collab_collabs', array( 'category_id=? AND is_template=1', $this->_id ) )->setKeyField( 'collab_id' ),
					'IPS\collab\Collab'
				)
			);
		}
		catch( \Exception $e ) {}
		
		return $this->templates;
	}
	
	/**
	 * Get Allowed Node Options
	 *
	 * @param	string|NULL	$node_id	md5 checksum of a node object classname to return the settings for ( optional )
	 * @return	array|FALSE			All available collab node management options or the settings for a single node type ( $node_id )
	 * Example:
	 * array(
	 *   'forums' => array(
	 *     'app' => \IPS\forums\Application [Object],
	 *     'icon' => 'comments',
	 *     'nodes' => array(
	 *       array(
	 *         'node' 	=> '\IPS\forums\Forum',
	 *         'content' 	=> '\IPS\forums\Topic',
	 *       )
	 *     ),
	 *   )
	 * )
	 */
	public function enabledNodes( $nid=NULL )
	{
		if ( isset ( $this->config ) )
		{
			if ( isset( $nid ) )
			{
				return isset ( $this->nodeConfig[ 'node_' . $nid ] ) ? $this->nodeConfig[ 'node_' . $nid ] : FALSE;
			}
			else
			{
				return $this->config;
			}
		}
		
		$lang			= \IPS\Member::loggedIn()->language();
		$this->config 		= array();
		$this->nodeConfig 	= array();
		$options 		= $this->_options;
		$iconMap		= \IPS\collab\Application::iconMap();
		
		foreach ( \IPS\collab\Application::collabOptions() as $app => $nodes )
		{
			$application = \IPS\Application::load( $app );
			foreach ( $nodes as $option )
			{
				$node_id = 'node_' . md5( $option[ 'node' ] );
				if ( isset ( $options[ $node_id ][ 'enabled' ] ) and $options[ $node_id ][ 'enabled' ] )
				{
					if ( !isset ( $this->config[ $app ][ 'app' ] ) )
					{
						$this->config[ $app ][ 'app' ] 	= $application;
						$this->config[ $app ][ 'icon' ]	= $iconMap[ $application->directory ] ?: $application->_icon;
					}
					$option[ 'nid' ] 				= md5( $option[ 'node' ] );
					$option[ 'content_name' ]			= ucwords( $lang->get( $option[ 'content' ]::$title ) );
					$option[ 'container_name' ]			= ucwords( $lang->get( $option[ 'node' ]::$nodeTitle ) );
					$option[ 'content_container_name' ]		= $option[ 'content_name' ] . ' ' . $option[ 'container_name' ];
					$this->config[ $app ][ 'nodes' ][ $node_id ] 	= $option;
					$this->nodeConfig[ $node_id ] 			= $option;
				}
			}
		}

		return $this->enabledNodes( $nid );
	}

	/**
	 * [Node] Add/Edit Form
	 *
	 * @param	\IPS\Helpers\Form	$form	The form
	 * @return	void
	 */
	public function form( &$form )
	{
		$lang 			= \IPS\Member::loggedIn()->language();
		$collab_singular_lang 	= "collab_cat_{$this->id}_collab_singular";
		$collab_plural_lang 	= "collab_cat_{$this->id}_collabs_plural";
		$member_title_lang 	= "collab_cat_{$this->id}_member_title";
		$form_id 		= $this->id ? "form_{$this->id}_" : "form_new_";
		$_singular 		= ucwords( $lang->checkKeyExists( $collab_singular_lang ) ? $lang->get( $collab_singular_lang ) : $lang->get( 'collab_cat__collab_singular' ) );
		$_plural		= ucwords( $lang->checkKeyExists( $collab_plural_lang ) ? $lang->get( $collab_plural_lang ) : $lang->get( 'collab_cat__collab_plural' ) );
		$_lc_singular		= mb_strtolower( $_singular );
		$_lc_plural		= mb_strtolower( $_plural );
		$iconMap		= \IPS\collab\Application::iconMap();
		
		$modoptions = \IPS\collab\Application::modOptions();
		
		/**
		 * @DEMO: Restrict amount of categories available in demo version
		 */
		if ( \IPS\collab\DEMO and ! $this->id )
		{
			if ( \IPS\Db::i()->select( 'COUNT(*)', 'collab_categories' )->first() >= 5 )
			{
				\IPS\Output::i()->error( 'Demo version restricted to a maximum of 5 categories.', 'GCDEMO', 200, '' );
				exit;
			}
		}
		
		/**
		 * Category Settings
		 */
		$form->addTab( 'tab_collab_category_settings', 'cogs' );
		$form->add( $collabs_enable = new \IPS\Helpers\Form\YesNo( 'category_collabs_enable', $this->id ? $this->collabs_enable : 0, FALSE, 
			array( 'togglesOn' => array( 
				$form_id . 'header_tab_collab_collabs_settings',
				$form_id . 'collabs_alias_singular', 
				$form_id . 'collabs_alias_plural', 
				$form_id . 'category_max_collabs_owned',
				$form_id . 'category_max_collabs_joined',
				$form_id . 'category_max_collab_members',
				$form_id . 'collab_allow_comments',
				$form_id . 'collab_allow_ratings',
				$form_id . 'collab_allow_reviews',
				$form_id . 'collab_increase_mainposts',
			) ) 
		) );
		$form->add( new \IPS\Helpers\Form\Translatable( 'category_name', NULL, TRUE, array( 'app' => 'collab', 'key' => ( $this->id ? "collab_category_{$this->id}" : NULL ) ) ) );
		$form->add( new \IPS\Helpers\Form\Translatable( 'category_description', NULL, FALSE, array(
			'app'		=> 'collab',
			'key'		=> ( $this->id ? "collab_category_{$this->id}_desc" : NULL ),
			'editor'	=> array(
				'app'			=> 'collab',
				'key'			=> 'Categories',
				'autoSaveKey'		=> ( $this->id ? "collab-cat-{$this->id}" : "collab-new-cat" ),
				'attachIds'		=> $this->id ? array( $this->id, NULL, 'category_description' ) : NULL, 
				'minimize'		=> 'cdesc_placeholder'
			)
		) ) );
		$form->add( new \IPS\Helpers\Form\Node( 'category_parent_id', $this->id ? $this->parent_id : ( \IPS\Request::i()->parent ?: 0 ), FALSE, array(
			'class'		      => '\IPS\collab\Category',
			'disabled'	      => false,
			'zeroVal'         => 'node_no_parent',
			'permissionCheck' => function( $node )
			{
				if ( ! isset( \IPS\Request::i()->id ) )
				{
					return true;
				}
				
				return $node->id != \IPS\Request::i()->id;
			}
		) ) );
		
		/**
		 * Collab Settings
		 */
		$form->addHeader( 'tab_collab_collabs_settings' );
		$form->add( new \IPS\Helpers\Form\Translatable( 'collabs_alias_singular', $this->collab_singular, TRUE, array( 'app' => 'collab', 'key' => ( $this->id ? $collab_singular_lang : NULL ) ) ) );
		$form->add( new \IPS\Helpers\Form\Translatable( 'collabs_alias_plural', $this->collab_plural, TRUE, array( 'app' => 'collab', 'key' => ( $this->id ? $collab_plural_lang : NULL ) ) ) );
		
		$form->addSeperator();
		
		$form->add( new \IPS\Helpers\Form\Number( 'category_max_collabs_owned', $this->max_collabs_owned ?: 0, FALSE, array( 'unlimited' => 0, 'min' => 1 ) ) );
		$form->add( new \IPS\Helpers\Form\Number( 'category_max_collabs_joined', $this->max_collabs_joined ?: 0, FALSE, array( 'unlimited' => 0, 'min' => 1 ) ) );
		$form->add( new \IPS\Helpers\Form\Number( 'category_max_collab_members', $this->max_collab_members ?: 0, FALSE, array( 'unlimited' => 0, 'min' => 1 ) ) );
				
		$form->addSeperator();
		
		$form->add( new \IPS\Helpers\Form\YesNo( 'collab_allow_comments', $this->id ? $this->bitoptions[ 'allow_comments' ] : TRUE ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'collab_allow_ratings', $this->id ? $this->bitoptions[ 'allow_ratings' ] : TRUE ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'collab_allow_reviews', $this->id ? $this->bitoptions[ 'allow_reviews' ] : TRUE ) );
		
		$form->addSeperator();
		
		$form->add( new \IPS\Helpers\Form\YesNo( 'collab_increase_mainposts', $this->id ? $this->bitoptions[ 'increase_mainposts' ] : FALSE ) );
		
		/**
		 * Collab Model Settings
		 */
		$form->addHeader( 'collab_model_settings' );

		$lang->words[ 'collab_enable_model_choice' ] = $lang->addToStack( 'collab_enable_model', FALSE, array( 'sprintf' => array( $_singular ) ) );
		$lang->words[ 'collab_enable_model_choice_desc' ] = $lang->addToStack( 'collab_enable_model_desc', FALSE, array( 'sprintf' => array( $_lc_plural, $_lc_plural, $_lc_plural ) ) );
		$form->add( $enable_model = new \IPS\Helpers\Form\YesNo( 'collab_enable_model_choice', $this->id ? $this->bitoptions[ 'enable_model' ] : FALSE, TRUE, array( 'togglesOn' => array( $form_id . 'collab_force_model_choice', $form_id . 'collab_multiple_models_choice' ) ) ) );
		$collabs_enable->options[ 'togglesOn' ] = array_merge( $collabs_enable->options[ 'togglesOn' ], array( $form_id . 'header_collab_model_settings', $form_id . 'collab_enable_model_choice' ) );
		
		$lang->words[ 'collab_force_model_choice' ] = $lang->addToStack( 'collab_force_model', FALSE, array( 'sprintf' => array( $_singular ) ) );
		$lang->words[ 'collab_force_model_choice_desc' ] = $lang->addToStack( 'collab_force_model_desc', FALSE, array( 'sprintf' => array( $_lc_singular, $_lc_singular ) ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'collab_force_model_choice', $this->id ? $this->bitoptions[ 'require_model' ] : FALSE, TRUE ) );
		
		$lang->words[ 'collab_multiple_models_choice' ] = $lang->addToStack( 'collab_multiple_models' );
		$lang->words[ 'collab_multiple_models_choice_desc' ] = $lang->addToStack( 'collab_multiple_models_desc', FALSE, array( 'sprintf' => array( $_lc_plural ) ) );
		$form->add( new \IPS\Helpers\Form\Radio( 'collab_multiple_models_choice', $this->id ? ( $this->bitoptions[ 'multiple_model' ] ? 1 : 0 ) : 0, TRUE, array( 'options' => array( 0 => 'single', 1 => 'multiple' ) ) ) );

		/* Application Tabs */
		foreach ( \IPS\collab\Application::collabOptions() as $app => $nodes )
		{
			$application = \IPS\Application::load( $app );
			$form->addTab( "__app_{$application->directory}", $iconMap[ $application->directory ] ?: $application->_icon );
			$collabs_enable->options[ 'togglesOn' ] = array_merge( $collabs_enable->options[ 'togglesOn' ], array( $form_id . 'tab__app_' . $application->directory ) );

			/* Node Options */
			foreach ( $nodes as $option )
			{
				$nodeClass 	= $option[ 'node' ];
				$nid 		= md5( $nodeClass );
				$nodeTitle 	= ucwords( $lang->get( $nodeClass::$nodeTitle ) );
				$contentTitle 	= $option[ 'content' ] ? ucwords( $lang->get( $option[ 'content' ]::$title ) ) : NULL;
				
				$form->addHeader( $contentTitle . ' ' . $nodeTitle );
				
				/**
				 * Enable Node Type 
				 */
				$lang->words[ "options-node_{$nid}-enabled" ] 				= $lang->addToStack( 'collab_allow_node', FALSE, array( 'sprintf' => array( $contentTitle, $nodeTitle ) ) );
				$lang->words[ "options-node_{$nid}-enabled_desc" ] 			= $lang->addToStack( $option['content'] ? 'collab_allow_node_content_desc' : 'content_allow_node_desc', FALSE, array( 'sprintf' => array( $lang->addToStack( $collab_plural_lang ), $nodeTitle, $contentTitle ) ) );
				$form->add( $enable_switch = new \IPS\Helpers\Form\YesNo( "options-node_{$nid}-enabled", isset( $this->_options[ 'node_' . $nid ][ 'enabled' ] ) ? $this->_options[ 'node_' . $nid ][ 'enabled' ] : 0, TRUE, array( 'togglesOn' => array ( 'perms_' . $nid ) ) ) );
				
				/**
				 * Allow Setting Maximum Amount Of Nodes 
				 */
				$lang->words[ "options-node_{$nid}-maxnodes" ] 				= $lang->addToStack( 'collab_node_maxnodes', FALSE, array( 'sprintf' => array( $nodeTitle, $lang->addToStack( $collab_singular_lang ) ) ) );
				$lang->words[ "options-node_{$nid}-maxnodes_desc" ] 			= $lang->addToStack( 'collab_node_maxnodes_desc', FALSE, array( 'sprintf' => array( $nodeTitle, $lang->addToStack( $collab_singular_lang ) ) ) );				
				$enable_switch->options[ 'togglesOn' ] 					= array_merge( $enable_switch->options[ 'togglesOn' ], array( $form_id . "options-node_{$nid}-maxnodes" ) );
				$form->add( new \IPS\Helpers\Form\Number( "options-node_{$nid}-maxnodes", isset( $this->_options[ 'node_' . $nid ][ 'maxnodes' ] ) ? $this->_options[ 'node_' . $nid ][ 'maxnodes' ] : 0, FALSE, array( 'unlimited' => 0, 'min' => 1 ) ) );
					
				/**
				 * Allow Adding, Editing, Deleting Nodes? 
				 */
				foreach ( array( 'enable_add', 'enable_edit', 'enable_delete', 'enable_reorder' ) as $enable_action )
				{
					$lang->words[ "options-node_{$nid}-{$enable_action}" ] 		= $lang->addToStack( 'collab_node_' . $enable_action, FALSE, array( 'sprintf' => array( $nodeTitle ) ) );
					$lang->words[ "options-node_{$nid}-{$enable_action}_desc" ] 	= $lang->addToStack( 'collab_node_' . $enable_action . '_desc', FALSE, array( 'sprintf' => array( $lang->addToStack( $collab_singular_lang ), $nodeTitle, $contentTitle ) ) );
					$form->add( new \IPS\Helpers\Form\YesNo( "options-node_{$nid}-{$enable_action}", isset( $this->_options[ 'node_' . $nid ][ $enable_action ] ) ? $this->_options[ 'node_' . $nid ][ $enable_action ] : 1 ) );
					$enable_switch->options[ 'togglesOn' ] = array_merge( $enable_switch->options[ 'togglesOn' ], array( $form_id . "options-node_{$nid}-{$enable_action}" ) );
				}
				
				if ( in_array( 'IPS\Node\Permissions', class_implements( $nodeClass ) ) )
				{
					/* Permissions Matrix */
					$lang->words[ 'collab_permissions_' . $nid ] = $lang->addToStack( 'collab_permissions', FALSE, array( 'sprintf' => array( $contentTitle, $nodeTitle ) ) );
					$form->addHeader( 'collab_permissions_' . $nid );
					$matrix = $this->nodePermMatrix( $nodeClass );
					$form->addMatrix( 'perms_' . $nid, $matrix );
					$enable_switch->options[ 'togglesOn' ] = array_merge( $enable_switch->options[ 'togglesOn' ], array( $form_id . 'header_collab_permissions_' . $nid ) );
				}
				
				if ( isset ( $modoptions[ $option[ 'content' ] ] ) )
				{
					/* Moderation Options */
					$s = $modoptions[ $option[ 'content' ] ];
					$lang->words[ 'collab_modperms__' . $s[ 'key' ] ] = $lang->addToStack( 'collab_moderation_settings', FALSE, array( 'sprintf' => array( $lang->addToStack( 'modperms__' . $s[ 'key' ] ), $lang->addToStack( $collab_singular_lang ) ) ) );
					$form->addHeader( 'collab_modperms__' . $s[ 'key' ] );
					$toggles = $this->addModerationSettings( $form, $s[ 'key' ], $s[ 'ext' ] );
					$enable_switch->options[ 'togglesOn' ] = array_merge( $enable_switch->options[ 'togglesOn' ], array( $form_id . 'header_collab_modperms__' . $s[ 'key' ] ), $toggles );
				}

			}
		}
		
	}
	
	/**
	 * Add Moderation Permission Settings
	 *
	 * @param	\IPS\Helpers\Form	$form		The form to add permission settings to
	 * @param	string			$k		System extension key
	 * @param	object			$ext		System extension object
	 * @param	\IPS\collab\Role	$role		If provided, permission fields will be generated for display on the role's edit form
	 * @return	void
	 */
	public function addModerationSettings( &$form, $k, $ext, $role=NULL )
	{
		$currentPermissions = isset( $role ) ? ( $role->mod_perms === NULL ? NULL : \unserialize( $role->mod_perms ) ) : $this->_mod_perms;
		$globalPermissions = $this->_mod_perms;
		
		if ( isset ( $role ) and $globalPermissions === NULL )
		{
			return array();
		}

		$lang 		= \IPS\Member::loggedIn()->language();	
		$_added 	= array();
		$toggles 	= array( 'view_future' => array(), 'future_publish' => array(), 'pin' => array(), 'unpin' => array(), 'feature' => array(), 'unfeature' => array(), 'edit' => array(), 'hide' => array(), 'unhide' => array(), 'view_hidden' => array(), 'move' => array(), 'lock' => array(), 'unlock' => array(), 'reply_to_locked' => array(), 'delete' => array(), 'split_merge' => array() );
		
		foreach ( $ext->getPermissions( $toggles ) as $name => $data )
		{
			/* Class */
			$type = is_array( $data ) ? $data[0] : $data;
			$class = '\IPS\Helpers\Form\\' . ( $type );
			
			$globalValue = ( isset( $globalPermissions[ $name ] ) ? $globalPermissions[ $name ] : NULL );
			
			/* Current Value */		
			if ( $currentPermissions === NULL )
			{
				switch ( $type )
				{
					case 'YesNo':
						$currentValue = TRUE;
						break;
						
					case 'Number':
						$currentValue = -1;
						break;
				}
			}
			else
			{
				$currentValue = ( isset( $currentPermissions[ $name ] ) ? $currentPermissions[ $name ] : NULL );
			}
			
			/* Options */
			$options = is_array( $data ) ? $data[1] : array();
			if ( $type === 'YesNo' )
			{
				if ( isset ( $role ) and !( $globalValue) )
				{
					$options[ 'disabled' ] = TRUE;
					$currentValue = $globalValue;
				}
			}
			if ( $type === 'Number' )
			{
				$options['unlimited'] = -1;
				if ( isset ( $role ) )
				{
					$options[ 'max' ] = $globalValue;
				}
			}
			
			/* Prefix/Suffix */
			$prefix = NULL;
			$suffix = NULL;
			if ( is_array( $data ) )
			{
				if ( isset( $data[2] ) )
				{
					$prefix = $data[2];
				}
				if ( isset( $data[3] ) )
				{
					$suffix = $data[3];
				}
			}
			
			/* Add */
			if ( $type !== 'Node' )
			{
				$lang->words[ 'modperms_' . $name ] = $lang->addToStack( $name );
				$form->add( new $class( 'modperms_' . $name, $currentValue, FALSE, $options, NULL, $prefix, $suffix, $name ) );
				$_added[] = $name;
			}
		}
		
		return $_added;
	}

	/**
	 * [Node] Save Add/Edit Form
	 *
	 * @param	array	$values	Values from the form
	 * @return	void
	 */
	public function saveForm( $values )
	{		
		$save_keys = array(
			'category_parent_id',
			'category_options',
			'category_collabs_enable',
			'category_max_collabs_owned',
			'category_max_collabs_joined',
			'category_max_collab_members',
		);
		
		/* Claim attachments */
		if ( ! $this->id )
		{
			$this->_options = array();
			$this->save();
			\IPS\File::claimAttachments( 'collab-new-cat', $this->id, NULL, 'description', TRUE );
		}
		else
		{
			\IPS\File::claimAttachments( 'collab-cat-' . $this->id, $this->id, NULL, 'description', TRUE );
		}
		
		$options = $this->_options;
		$modperms = $this->_mod_perms;
		$t_groups = count( \IPS\Member\Group::groups() );
				
		foreach ( $values as $key => $val )
		{
			// Store Node Options
			if ( \substr( $key, 0, \strlen( 'options-' ) ) === 'options-' )
			{
				$bits = explode( '-', $key );
				$options[ $bits[1] ][ $bits[2] ] = $val;
				if 
				( 
					\strpos( $bits[1], 'node_' ) === 0 and 
					$bits[2] == 'enabled' and 
					$val
				)
				{
					if ( $data = \IPS\collab\Application::collabOptions( \substr( $bits[1], \strlen( 'node_' ) ) ) )
					{
						\IPS\collab\Application::provisionNode( $data[ 'node' ] );
					}
				}
				unset( $values[ $key ] );
			}
			
			// Save Node Permissions
			if ( \substr( $key, 0, \strlen( 'perms_' ) ) === 'perms_' )
			{
				$nid = \substr( $key, \strlen( 'perms_' ) );
				if ( $nodeSettings = \IPS\collab\Application::collabOptions( $nid ) )
				{
					$nodeClass = $nodeSettings[ 'node' ];
					/* Handle submissions */
					if ( $matrix_values = $values[ 'perms_' . $nid ] )
					{
						$_perms = array();
								
						foreach ( $nodeClass::$permissionMap as $k => $v )
						{
							if ( isset( \IPS\Request::i()->__all[ "{$nid}_{$k}" ] ) )
							{
								$_perms[ $v ] = '*';
							}
							else
							{
								$_perms[ $v ] = array();
							}
						}
						
						/* Prepare insert */
						$insert = array( 'app' => 'collab', 'perm_type' => $nodeClass::$permApp . '_' . $nodeClass::$permType, 'perm_type_id' => $this->id );
						
						try
						{
							$current = \IPS\Db::i()->select( '*', 'core_permission_index', array( 'app=? AND perm_type=? AND perm_type_id=?', $insert[ 'app' ], $insert[ 'perm_type' ], $insert[ 'perm_type_id' ] ) )->first();
							$insert['perm_id'] = $current['perm_id'];
						}
						catch( \UnderflowException $e ) {}
						
						/* Loop groups */
						foreach ( $matrix_values as $group => $perms )
						{
							foreach ( $nodeClass::$permissionMap as $k => $v )
							{
								if ( isset( $perms[ "{$nid}_{$k}" ] ) and $perms[ "{$nid}_{$k}" ] and is_array( $_perms[ $v ] ) )
								{
									$_perms[ $v ][] = $group;
								}
							}
						}
						
						/* Finalize */
						foreach ( $_perms as $k => $v )
						{
							$insert[ "perm_{$k}" ] = is_array( $v ) ? ( count( $v ) == $t_groups ? '*' : implode( $v, ',' ) ) : $v;
						}
						
						/* Set the permissions */
						$this->setNodePermissions( $insert, $nodeClass );
					}
				}
			}
			
			// Process Moderator Permissions
			if ( \substr( $key, 0, \strlen( 'modperms_' ) ) === 'modperms_' )
			{
				$modkey = \substr( $key, \strlen( 'modperms_' ) );
				$modperms[ $modkey ] = $val;
			}
			
		}
		$this->_options = $options;
		$this->_mod_perms = $modperms;
		
		/* Parent ID */
		if ( isset( $values[ 'category_parent_id' ] ) )
		{
			$values[ 'category_parent_id' ] = $values[ 'category_parent_id' ] ? intval( $values[ 'category_parent_id' ]->id ) : 0;
		}
		
		$this->bitoptions[ 'allow_comments' ] 		= $values[ 'collab_allow_comments' ];
		$this->bitoptions[ 'allow_ratings' ] 		= $values[ 'collab_allow_ratings' ];
		$this->bitoptions[ 'allow_reviews' ] 		= $values[ 'collab_allow_reviews' ];
		$this->bitoptions[ 'increase_mainposts' ] 	= $values[ 'collab_increase_mainposts' ];
		$this->bitoptions[ 'enable_model' ]		= $values[ 'collab_enable_model_choice' ];
		$this->bitoptions[ 'require_model' ] 		= $values[ 'collab_force_model_choice' ];
		$this->bitoptions[ 'multiple_model' ] 		= $values[ 'collab_multiple_models_choice' ];
		
		$this->name_seo	= \IPS\Http\Url::seoTitle( $values[ 'category_name' ][ \IPS\Lang::defaultLanguage() ] );

		/* Custom language fields */
		\IPS\Lang::saveCustom( 'collab', "collab_category_{$this->id}", $values[ 'category_name' ] );
		\IPS\Lang::saveCustom( 'collab', "collab_category_{$this->id}_desc", $values[ 'category_description' ] );
		\IPS\Lang::saveCustom( 'collab', "collab_cat_{$this->id}_collab_singular", $values[ 'collabs_alias_singular' ] );
		\IPS\Lang::saveCustom( 'collab', "collab_cat_{$this->id}_collabs_plural", $values[ 'collabs_alias_plural' ] );

		$save_values = array();
		foreach ( $values as $key => $value )
		{
			if ( in_array( $key, $save_keys ) )
			{
				$save_values[ $key ] = $value;
			}
		}
	
		/* Send to parent */
		parent::saveForm( $save_values );
	}

	/**
	 * Category Node Permissions Matrix
	 *
	 * @param	string	$nodeClass	The classname of the node object to generate a permission matrix for
	 * @return	\IPS\Helpers\Form\Matrix
	 */
	public function nodePermMatrix( $nodeClass )
	{
		$nid 	= md5( $nodeClass );
		$lang 	= \IPS\Member::loggedIn()->language();
		
		/* Get current permissions */
		try
		{
			$current = \IPS\Db::i()->select( '*', 'core_permission_index', array( 'app=? AND perm_type=? AND perm_type_id=?', 'collab', $nodeClass::$permApp . '_' . $nodeClass::$permType, $this->id ?: 0 ) )->first();
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
						$current["perm_{$v}"] = implode( ',', array_keys( \IPS\Member\Group::groups( TRUE, FALSE ) ) );
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
		foreach ( $nodeClass::$permissionMap as $k => $v )
		{
			$unique_key = "{$nid}_{$k}";
			$lang->words[ $matrix->langPrefix . $unique_key ] = $lang->addToStack( $matrix->langPrefix . $k );
			$matrix->columns[ $unique_key ] = function( $key, $value, $data ) use ( $current, $k, $v, $nid, $unique_key )
			{
				$groupId = mb_substr( $key, 0, -( 2 + mb_strlen( $unique_key ) ) );
				$checkbox = new \IPS\Helpers\Form\Checkbox( $key, isset( $current[ "perm_{$v}" ] ) and ( $current[ "perm_{$v}" ] === '*' or in_array( $groupId, explode( ',', $current[ "perm_{$v}" ] ) ) ) );
				return $checkbox;
			};
			$matrix->checkAlls[ $unique_key ] = ( $current[ "perm_{$v}" ] === '*' );
		}
		$matrix->checkAllRows = TRUE;
		
		$rows = array();
		foreach ( \IPS\Member\Group::groups() as $group )
		{
			$rows[ $group->g_id ] = array(
				'label'	=> $group->name,
				'view'	=> TRUE,
			);
		}
		$matrix->rows = $rows;	
		
		return $matrix;
	}
	
	/**
	 * Set global collab node permissions
	 *
	 * @param	array	$permissions				Permission data to insert
	 * @return  void
	 */
	public function setNodePermissions( $permissions, $nodeClass )
	{
		/* Delete current rows */
		\IPS\Db::i()->delete( 'core_permission_index', array( 'app=? AND perm_type=? AND perm_type_id=?', $permissions[ 'app' ], $permissions[ 'perm_type' ], $permissions[ 'perm_type_id' ] ) );
		
		/* Insert */
		\IPS\Db::i()->insert( 'core_permission_index', $permissions );
		
		unset( $permissions[ 'perm_id' ], $permissions[ 'app' ], $permissions[ 'perm_type' ], $permissions[ 'perm_type_id' ] );
		
		if ( \IPS\Db::i()->checkForColumn( $nodeClass::$databaseTable, $nodeClass::$databasePrefix . 'collab_id' ) )
		{
			\IPS\Db::i()->update( 'core_permission_index', $permissions, 
				array( 'app=? AND perm_type=? AND collab_categories.category_id=?', $nodeClass::$permApp, $nodeClass::$permType, $this->_id ),
				array( 
					array( 'from' => $nodeClass::$databaseTable, 	'where' => array( $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . $nodeClass::$databaseColumnId . '=core_permission_index.perm_type_id' ) ),
					array( 'from' => 'collab_collabs', 		'where' => array( 'collab_collabs.collab_id=' . $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . 'collab_id' ) ),
					array( 'from' => 'collab_categories', 		'where' => array( 'collab_categories.category_id=collab_collabs.category_id' ) )
				)
			);
		}
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
		
		return $this->bitoptions[ 'allow_ratings' ] and parent::canRate( $member ) and $this->can( 'rate', $member );
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
		$categoryCan 	= TRUE;
		
		switch ( $permission )
		{
			case 'add':
			
				/* Are collabs even enabled for this category? */
				if ( ! $this->collabs_enable )
				{
					return FALSE;
				}
				
				/* Make sure we can even join the collab we want to add */
				$categoryCan = $this->can( 'join', $member );
				
				if ( $categoryCan )
				{
					/* Check global group limit */
					if ( $member->group['g_collabs_owned_limit'] > 0 )
					{
						$categoryCan = (
							$categoryCan and
							( \IPS\Db::i()->select( 'COUNT(*)', 'collab_collabs', array( 'owner_id=?', $member->member_id ) )->first() < $member->group['g_collabs_owned_limit'] )
						);
					}
					
					/* Check category limit */
					if ( $this->max_collabs_owned > 0 )
					{
						$categoryCan = (
							$categoryCan and
							( \IPS\Db::i()->select( 'COUNT(*)', 'collab_collabs', array( 'owner_id=? AND category_id=?', $member->member_id, $this->id ) )->first() < $this->max_collabs_owned )
						);
					}
				}
				break;
		
			case 'join':
			
				if ( $member->member_id )
				{
					/* Check global group limit */
					if ( $member->group['g_collabs_joined_limit'] > 0 )
					{
						$categoryCan = (
							$categoryCan and
							( \IPS\Db::i()->select( 'COUNT(*)', 'collab_memberships', array( 'member_id=? AND status=?', $member->member_id, \IPS\collab\COLLAB_MEMBER_ACTIVE ) )->first() < $member->group['g_collabs_joined_limit'] )
						);
					}				
				
					/* Check category limit */
					if ( $this->max_collabs_joined > 0 )
					{
						$categoryCan = (
							$categoryCan and
							( \IPS\Db::i()->select( 'COUNT(*)', 'collab_memberships', array( 'collab_memberships.member_id=? AND collab_memberships.status=? AND collab_collabs.category_id=?', $member->member_id, \IPS\collab\COLLAB_MEMBER_ACTIVE, $this->id ) )->join( 'collab_collabs', 'collab_collabs.collab_id=collab_memberships.collab_id' )->first() < $this->max_collabs_joined )
						);
					}
				}
				else
				{
					/* Anonymous members can't join collabs, it just won't work */
					$categoryCan = FALSE;
				}
				break;
		}
		
		return $categoryCan and parent::can( $permission, $member );
	}
	
	/**
	 * @brief	Cached URL
	 */
	protected $_url	= NULL;

	/**
	 * Get URL
	 *
	 * @return	\IPS\Http\Url
	 */
	public function url()
	{
		if( $this->_url === NULL )
		{
			$this->_url = \IPS\Http\Url::internal( "app=collab&module=collab&controller=categories&category={$this->_id}", 'front', 'collab_category', $this->name_seo );
		}

		return $this->_url;
	}

	/**
	 * Get "No Permission" error message
	 *
	 * @return	string
	 */
	public function errorMessage()
	{
		if ( \IPS\Member::loggedIn()->language()->checkKeyExists( "collab_category_{$this->id}_permerror" ) )
		{
			$message = \IPS\Member::loggedIn()->language()->addToStack( "collab_category_{$this->id}_permerror" );
			if ( $message and $message != '<p></p>' )
			{
				return $message;
			}
		}
		
		return 'node_error_no_perm';
	}

	/**
	 * Cover Photo
	 *
	 * @return	\IPS\Helpers\CoverPhoto
	 */
	public function coverPhoto( $layout='standard' )
	{	
		$photo = parent::coverPhoto();
		$photo->overlay = \IPS\Theme::i()->getTemplate( 'components', 'collab', 'front' )->categoryCoverOverlay( $this, $layout );
		
		if ( $layout != 'standard' or ! $this->canEdit() )
		{
			$photo->editable = FALSE;
		}
		
		return $photo;
	}
	
	/**
	 * Load record based on a URL
	 *
	 * @param	\IPS\Http\Url	$url	URL to load from
	 * @return	static
	 * @throws	\InvalidArgumentException
	 * @throws	\OutOfRangeException
	 */
	public static function loadFromUrl( \IPS\Http\Url $url )
	{
		$qs = array_merge( $url->queryString, $url->getFriendlyUrlData() );
		
		if ( isset( $qs['category'] ) )
		{
			if ( method_exists( get_called_class(), 'loadAndCheckPerms' ) )
			{
				return static::loadAndCheckPerms( $qs['category'] );
			}
			else
			{
				return static::load( $qs['category'] );
			}
		}
		
		throw new \InvalidArgumentException;
	}

	/**
	 * Get template for node tables
	 *
	 * @return	callable
	 */
	public static function nodeTableTemplate()
	{
		return array( \IPS\Theme::i()->getTemplate( 'browse', 'collab' ), 'categoryRow' );
	}
	
	protected function langKeys()
	{
		return array( 
			'description' => "collab_category_{$this->id}_desc", 
			'rules_title' => "collab_category_{$this->id}_rulestitle", 
			'rules_text' => "collab_category_{$this->id}_rules", 
			'permission_custom_error' => "collab_category_{$this->id}_permerror",
			'collab_singular' => "collab_cat_{$this->id}_collab_singular",
			'collab_plural' => "collab_cat_{$this->id}_collabs_plural"
			
		);
	}
	
	/**
	 * [ActiveRecord] Duplicate
	 *
	 * @return	void
	 */
	public function __clone()
	{
		if ( $this->skipCloneDuplication === TRUE )
		{
			return;
		}
		
		$oldId 			= $this->id;
		$oldTitle 		= \IPS\Member::loggedIn()->language()->get( static::$titleLangPrefix . $this->_id );
		$this->show_rules 	= 0;

		parent::__clone();

		foreach ( $this->langKeys() as $fieldKey => $langKey )
		{
			$oldLangKey = str_replace( $this->id, $oldId, $langKey );
			\IPS\Lang::saveCustom( 'collab', $langKey, iterator_to_array( \IPS\Db::i()->select( 'word_custom, lang_id', 'core_sys_lang_words', array( 'word_key=?', $oldLangKey ) )->setKeyField( 'lang_id' )->setValueField('word_custom') ) );
		}

		\IPS\Lang::saveCustom( 'collab', "collab_category_{$this->id}", $oldTitle . ' ' . \IPS\Member::loggedIn()->language()->get( 'copy' ) );

	}
			
	/**
	 * Delete Record
	 *
	 * @return	void
	 */
	public function delete()
	{
		\IPS\File::unclaimAttachments( 'collab_Categories', $this->id );
		parent::delete();
		
		foreach ( $this->langKeys() as $fieldKey => $langKey )
		{
			\IPS\Lang::deleteCustom( 'collab', $langKey );
		}
	}
		
}