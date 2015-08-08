<?php


namespace IPS\collab\modules\front\collab;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * roles
 */
class _roles extends \IPS\Node\Controller
{
	/**
	 * Node Class
	 */
	protected $nodeClass = '\IPS\collab\Collab\Role';
	
	/**
	 * Constructor
	 *
	 * @param	\IPS\Http\Url|NULL	$url		The base URL for this controller or NULL to calculate automatically
	 * @return	void
	 */
	public function __construct( $url=NULL )
	{
		$collab = \IPS\collab\Application::activeCollab();
				
		if ( $url === NULL )
		{
			$class		= get_called_class();
			$exploded	= explode( '\\', $class );
			$this->url = 	\IPS\Http\Url::internal( "app={$exploded[1]}&module={$exploded[4]}&controller={$exploded[5]}&collab={$collab->collab_id}", \IPS\Dispatcher::i()->controllerLocation );
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
		$nodeClass 	= $this->nodeClass;
		$bits 		= explode( '\\', $nodeClass );
		
		if ( ! $collab->collabCan( 'manageRoles' ) )
		{
			\IPS\collab\Application::authError( 'manageRoles' );
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
	 * Mass PM
	 */
	protected function rolePM()
	{
		$collab = \IPS\collab\Application::activeCollab();
		
		try
		{
			$role = \IPS\collab\Collab\Role::load( \IPS\Request::i()->id );
			$collab->authObj( $role );
			
			/**
			 * Set up mass pm recipients
			 */
			if ( \IPS\Request::i()->messenger_to === NULL )
			{
				$extraWhere = $role->member_default ? array( 'collab_memberships.member_id!=?', \IPS\Member::loggedIn()->member_id ) : array( 'collab_roles.id=? and collab_memberships.member_id!=?', $role->id, \IPS\Member::loggedIn()->member_id );
				$memberships = $collab->memberships( array( 'statuses' => array( \IPS\collab\COLLAB_MEMBER_ACTIVE ) ), $extraWhere );
				
				$messenger_to = array();
				foreach( $memberships as $membership )
				{
					$messenger_to[] = $membership->member()->name;
				}
			
				\IPS\Request::i()->messenger_to = implode( ',', $messenger_to );
			}

			$form = \IPS\core\Messenger\Conversation::create();
			$form->action = \IPS\Http\Url::internal( "app=core&module=messaging&controller=messenger&do=compose" );
			$form->class = 'ipsForm_vertical';

			\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack( 'compose_new' );
			\IPS\Output::i()->output	= $form->customTemplate( array( call_user_func_array( array( \IPS\Theme::i(), 'getTemplate' ), array( 'forms', 'core' ) ), 'popupTemplate' ) );
		}
		catch ( \OutOfRangeException $e )
		{
			\IPS\Output::i()->error( 'collab_node_unavailable', '2CR60/A', 403 );
		}
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
		$result = call_user_func( array( 'parent', $callback ) );
		
		/* Reset the location routing theme hack */
		\IPS\Theme::i()->defaultLocation = NULL;
		
		/* Save the page title */
		\IPS\collab\Application::$collabPageTitle = \IPS\Output::i()->title;

		
		return $result;
	}
	
}