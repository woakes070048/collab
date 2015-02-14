<?php


namespace IPS\collab\modules\front\collab;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Collab Generic Nodes Controller
 */
class _nodes extends \IPS\Node\Controller
{
	/**
	 * Node Class
	 */
	protected $nodeClass = '';
	
	/**
	 * Constructor
	 *
	 * @param	\IPS\Http\Url|NULL	$url		The base URL for this controller or NULL to calculate automatically
	 * @return	void
	 */
	public function __construct( $url=NULL )
	{
		$collab 		= \IPS\collab\Application::activeCollab();
		$this->nodeClass 	= $nodeClass = $this->_nodeClass();
		$nid 			= md5( $nodeClass );
		
		/* Make nodes unsortable if category settings disallow reordering */
		if ( ! \IPS\Member::loggedIn()->isAdmin() and ! $collab->container()->_options[ 'node_' . $nid ][ 'enable_reorder' ] )
		{
			$nodeClass::$nodeSortable = FALSE;
		}
				
		if ( $url === NULL )
		{
			$this->url = \IPS\Http\Url::internal( "app=collab&module=collab&controller=nodes&collab={$collab->collab_id}&nid=" . md5( $nodeClass ), \IPS\Dispatcher::i()->controllerLocation );
			
			/* IPS Databases Compatibility */
			if ( isset ( $nodeClass::$customDatabaseId ) )
			{
				$this->url = $this->url->setQueryString( 'database_id', $nodeClass::$customDatabaseId );
			}
		}
		else
		{
			$this->url = $url;
		}
		
		parent::__construct( $this->url );
	}

	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		$collab 	= \IPS\collab\Application::activeCollab();
		$nid 		= md5( $this->nodeClass );
		$nodeClass 	= $this->nodeClass;
		$bits 		= explode( '\\', $nodeClass );
		
		if ( ! $collab->collabCan( 'appManage-' . $bits[1] ) )
		{
			$nodeClass = $this->nodeClass;
			\IPS\collab\Application::authError( 'nodeManage', array( \IPS\Member::loggedIn()->language()->addToStack( $nodeClass::$nodeTitle ) ) );
		}
	
		\IPS\collab\Application::prepareNodeManager();
		\IPS\collab\Application::makeBreadcrumbs( $collab );
		
		parent::execute();	
	}
	
	/**
	 * Manage
	 *
	 * @return	void
	 */
	protected function manage()
	{
		$this->_execute( 'manage' );
	}
	
	/**
	 * Add / Edit Form
	 *
	 * @return	void
	 */
	protected function form()
	{
		$this->_execute( 'form' );
	}
	
	/**
	 * Mass Change
	 *
	 * @return	void
	 */
	protected function massChange()
	{
		$this->_execute( 'massChange' );
	}
	
	/**
	 * Search
	 *
	 * @return	void
	 */
	protected function search()
	{
		$this->_execute( 'search' );
	}
	
	/**
	 * Delete
	 *
	 * @return	void
	 */
	protected function delete()
	{
		$this->_execute( 'delete' );
	}
	
	/**
	 * Execute Controller Methods ( with a "default location" theme hack )
	 *
	 * @return	void
	 */
	protected function _execute( $callback, $location = 'admin' )
	{
		/* Force templates without a specified location to be routed */
		\IPS\Theme::i()->defaultLocation = $location;
		
		/* Execute the handler */
		call_user_func( array( 'parent', $callback ) );
		
		/* Reset the location routing theme hack */
		\IPS\Theme::i()->defaultLocation = NULL;
		
		
		$nodeClass 	= $this->nodeClass;
		$contentItem	= $nodeClass::$contentItemClass;
		$lang 		= \IPS\Member::loggedIn()->language();
		
		/* Save a meaningful page title */
		\IPS\Output::i()->title = \IPS\collab\Application::$collabPageTitle = ucwords( $lang->get( $contentItem::$title ) ) . ' ' . ucwords( $lang->get( $nodeClass::$nodeTitle ) );
	}
	
	/**
	 * Get Operational Node Class
	 *
	 * @return	void
	 */
	protected function _nodeClass()
	{
		static $nodeClass;
		if ( isset ( $nodeClass ) ) 
		{
			return $nodeClass;
		}
		
		$collab = \IPS\collab\Application::activeCollab();
		if ( ! ( $settings = $collab->enabledNodes( \IPS\Request::i()->nid ?: '' ) ) )
		{
			\IPS\Output::i()->error( 'collab_node_unavailable', '2CAN0/A', 403 );
		}
		
		return $nodeClass = $settings['node'];
	}
	
}