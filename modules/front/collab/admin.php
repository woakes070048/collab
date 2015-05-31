<?php

namespace IPS\collab\modules\front\collab;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * admin
 */
class _admin extends \IPS\Dispatcher\Controller
{

	/**
	 * \IPS\collab\Collab Object
	 */
	protected $collab = NULL;

	/**
	 * Construct
	 *
	 * @return	void
	 */
	public function __construct( $url=NULL, $collab=NULL )
	{
		parent::__construct( $url );
		if ( $collab !== NULL )
		{
			$this->collab = $collab;
		}
	}
	
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		parent::execute();
				
		/* Create breadcrumbs */
		\IPS\collab\Application::makeBreadcrumbs( $this->_getCollab() );	
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{
		$this->manageMembers();
	}
	
	/**
	 * Collab Management Dashboard
	 *
	 * @return	void
	 */
	protected function dashboard()
	{
		$this->_authCheck( 'manageCollab' );
		
		$collab = $this->_getCollab();
	
		\IPS\Output::i()->title = \IPS\collab\Application::$collabPageTitle = \IPS\Member::loggedIn()->language()->addToStack( 'collab_dashboard', FALSE, array( 'sprintf' => array( $collab->collab_singular ) ) );	
		\IPS\Output::i()->output = "";
	}
	
	/**
	 * Manage Collab Members
	 *
	 * @return	void
	 */
	protected function manageMembers()
	{
		$this->_authCheck( 'manageMembers' );
		
		$collab 	= $this->_getCollab();
		$membersTable 	= $this->_membersTable();
		
		\IPS\collab\Application::prepareNodeManager();
		
		if ( \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->output = $membersTable;	
		}
		else
		{
			\IPS\Output::i()->title = \IPS\collab\Application::$collabPageTitle = \IPS\Member::loggedIn()->language()->addToStack( 'collab_title_manage_members', FALSE, array( 'sprintf' => array( $collab->collab_singular ) ) );
			\IPS\Output::i()->output = $membersTable;		
		}
	}

	/**
	 * Edit Collab Member
	 *
	 * @return	void
	 */
	protected function editMember()
	{
		$this->_authCheck( 'editMember' );
		
		$membership 	= $this->_getMembership();
		$collab 	= $this->_getCollab();
		$roles 		= array();
		
		foreach( $collab->roles() as $id => $role )
		{
			$roles[ $id ] = $role->name;
		}
		
		$form = new \IPS\Helpers\Form( 'edit_member' );
		$form->class .= ' ipsPad';
		
		if ( $membership->status === \IPS\collab\COLLAB_MEMBER_PENDING )
		{
			$form->addHeader( 'collab_membership_join_notes' );
			$form->addHtml( $membership->member_notes );
			
			if ( $collab->collabCan( 'approveMember' ) )
			{
				if ( $collab->notFull() )
				{
					$form->add ( new \IPS\Helpers\Form\Checkbox( 'collab_member_approve', FALSE ) );
				}
				else
				{
					$form->addMessage( 'collab_join_full' );
				}
			}
		}
		
		$form->addHeader( 'collab_membership_member_settings' );
		
		\IPS\Member::loggedIn()->language()->words[ 'collab_member_default_title_desc' ] = sprintf( \IPS\Member::loggedIn()->language()->get( 'collab_member_default_title_desc' ), $collab->collab_singular );

		if ( $collab->collabCan( 'editMemberRoles' ) )
		{	
			$form->add( new \IPS\Helpers\Form\YesNo( 'collab_member_default_title', $membership->title == "", TRUE, array( 'togglesOff' => array( 'edit_member_collab_member_custom_title' ) ) ) );
			$form->add( new \IPS\Helpers\Form\Text( 'collab_member_custom_title', $membership->title, FALSE ) );
			if ( ! empty ( $roles ) )
			{
				$form->add( new \IPS\Helpers\Form\CheckboxSet( 'collab_member_roles', array_map( function ( $val ) { return (int) $val; }, explode( ',', $membership->roles ) ), FALSE, array(
					'options' => $roles,
				) ) );
			}
		}
		
		$form->add( new \IPS\Helpers\Form\Number( 'collab_member_posts', $membership->posts, TRUE ) );

		$form->add( new \IPS\Helpers\Form\Editor( 'collab_collab_notes', $membership->collab_notes, FALSE, array(
				'app'			=> 'collab',
				'key'			=> 'Generic',
				'autoSaveKey'		=> "membership-edit-{$membership->id}",
			)
		) );
		
		if ( $values = $form->values() )
		{
			
			if ( $collab->collabCan( 'editMemberRoles' ) )
			{
				$membership->title = $values[ 'collab_member_default_title' ] ? "" : $values[ 'collab_member_custom_title' ];
				
				if ( isset ( $values[ 'collab_member_roles' ] ) )
				{
					$membership->roles = implode( ',', $values[ 'collab_member_roles' ] );
				}				
			}
			
			$membership->posts = $values[ 'collab_member_posts' ];			
			$membership->collab_notes = $values[ 'collab_collab_notes' ];
			$membership->save();
			
			if ( $membership->status === \IPS\collab\COLLAB_MEMBER_PENDING and isset( $values[ 'collab_member_approve' ] ) and $values[ 'collab_member_approve' ] )
			{
				$this->approveMember();
			}
			
			\IPS\Output::i()->redirect( $this->_baseUrl()->setQueryString( array( 'do' => 'manageMembers' ) ), 'collab_member_edited' );
		}
		
		if ( \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->output = $form;	
		}
		else
		{
			\IPS\Output::i()->title = \IPS\collab\Application::$collabPageTitle = \IPS\Member::loggedIn()->language()->addToStack( 'collab_title_edit_membership', FALSE, array( 'sprintf' => array( $membership->member()->name ) ) );
			\IPS\Output::i()->output = $form;
		}
	}
	
	/**
	 * Invite Member to Collab
	 *
	 * @return	void
	 */
	protected function inviteMember()
	{	
		$this->_authCheck( 'inviteMember' );
		
		$collab 	= $this->_getCollab();
		$invitee	= NULL;
		$form 		= new \IPS\Helpers\Form( 'invite_member_' . $collab->collab_id, 'collab_invite_button' );
		$form->class	.= ' ipsPad';
		
		if ( \IPS\Request::i()->invitee )
		{
			try
			{
				$invitee = \IPS\Member::load( \IPS\Request::i()->invitee );
			}
			catch ( \OutOfRangeException $e ) {}
		}
		
		$form->add( new \IPS\Helpers\Form\Member( 'collab_invitees', $invitee, TRUE, array( 'multiple' => 25 ) ) );
		
		$form->add( new \IPS\Helpers\Form\Editor( 'collab_invite_message', NULL, FALSE, array(
				'app'			=> 'collab',
				'key'			=> 'Generic',
				'autoSaveKey'		=> "collab-join-{$collab->collab_id}",
				'attachIds'		=> array( $collab->collab_id ), 
			)
		) );
		
		if ( $values = $form->values() )
		{
			$_invitees = $_rejectees = array();
			
			foreach ( $values[ 'collab_invitees' ] as $invitee )
			{
				if ( $collab->collabCan( 'inviteMember', NULL, array( 'invitee' => $invitee ) ) )
				{
					$_invitees[] = $invitee;
				}
				else
				{
					$_rejectees[] = $invitee;
				}
			}
			
			if ( ! empty ( $_rejectees ) )
			{
				// @TODO: display message showing members who could not be invited 
			}
			
			if ( ! empty ( $_invitees ) )
			{
				// invite members
				foreach ( $_invitees as $invitee )
				{
					$collab->inviteMember( $invitee, \IPS\Member::loggedIn(), $values[ 'collab_invite_message' ] );
				}
			}
			
			\IPS\Output::i()->redirect( $this->_baseUrl()->setQueryString( array( 'do' => 'manageMembers' ) ), 'collab_message_members_invited' );
		
		}
		
		if ( \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->output = $form;	
		}
		else
		{
			\IPS\Output::i()->title = \IPS\collab\Application::$collabPageTitle = \IPS\Member::loggedIn()->language()->addToStack( 'collab_title_invite_member', FALSE, array( 'sprintf' => array( $invitee->name ) ) );
			\IPS\Output::i()->output = $form;	
		}
		
	}
	
	/**
	 * Approve Collab Member
	 *
	 * @return	void
	 */
	protected function approveMember( $resultLang = 'collab_member_approved', $sponsor=NULL )
	{
		\IPS\Session::i()->csrfCheck();
		$this->_authCheck( 'approveMember' );
		
		$membership 	= $this->_getMembership();
		$collab 	= $this->_getCollab();
		$sponsor 	= $sponsor ?: \IPS\Member::loggedIn();
		$_status 	= $membership->status;
		
		if ( $_status === \IPS\collab\COLLAB_MEMBER_ACTIVE )
		{
			/* Member does not need approval */
			\IPS\Output::i()->redirect( $this->_baseUrl()->setQueryString( array( 'do' => 'manageMembers' ) ) );
		}
		
		if ( $collab->isFull() )
		{
			\IPS\Output::i()->error( 'collab_join_full' , '2CA03/B', 403 );
		}
		
		$membership->status = \IPS\collab\COLLAB_MEMBER_ACTIVE;
		$membership->joined = $membership->joined ?: time();
		$membership->sponsor_id = $sponsor->member_id;
		$membership->save();
		
		if ( $_status === \IPS\collab\COLLAB_MEMBER_PENDING )
		{
			// Send "Request Accepted" Notification!
			$notification = new \IPS\Notification( \IPS\Application::load( 'collab' ), 'collab_join_accepted', $membership, array( $membership->sponsor(), $collab, $membership ) );
			$notification->recipients->attach( $membership->member() );
			$notification->send();
		}

		/**
		 * Rules Event: Member Joined
		 */
		if ( \IPS\Application::appIsEnabled( 'rules' ) )
		{
			\IPS\rules\Event::load( 'collab', 'Collaboration', 'member_joined' )->trigger( $membership->member(), $membership->collab(), $membership );
		}
			
		if ( \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->json( 'OK' );
		}
		else
		{
			\IPS\Output::i()->redirect( $this->_baseUrl()->setQueryString( array( 'do' => 'manageMembers' ) ), $resultLang );
		}
	}
	
	/**
	 * Ban Collab Member
	 *
	 * @return	void
	 */
	protected function banMember()
	{
		\IPS\Session::i()->csrfCheck();
		$this->_authCheck( 'banMember' );
		
		$membership = $this->_getMembership();
		
		$membership->status = \IPS\collab\COLLAB_MEMBER_BANNED;
		$membership->save();

		/**
		 * Rules Event: Member Banned
		 */
		if ( \IPS\Application::appIsEnabled( 'rules' ) )
		{
			\IPS\rules\Event::load( 'collab', 'Collaboration', 'member_banned' )->trigger( $membership->member(), $membership->collab(), $membership );
		}
			
		if ( \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->json( 'OK' );
		}
		else
		{
			\IPS\Output::i()->redirect( $this->_baseUrl()->setQueryString( array( 'do' => 'manageMembers' ) ), 'collab_member_banned' );
		}
	}
	
	/**
	 * Un-Ban Collab Member
	 *
	 * @return	void
	 */
	protected function unbanMember()
	{
		\IPS\Session::i()->csrfCheck();
		$this->_authCheck( 'unbanMember' );
		
		$this->approveMember( 'collab_member_unbanned' );
	}

	/**
	 * Remove Collab Member
	 *
	 * @return	void
	 */
	protected function deleteMember()
	{
		\IPS\Session::i()->csrfCheck();
		$this->_authCheck( 'deleteMember' );
		
		$collab 	= $this->_getCollab();
		$membership 	= $this->_getMembership();
		
		if ( $membership->member_id === $collab->owner_id )
		{
			\IPS\Output::i()->error( 'collab_member_invalid', '2CA05/B', 403 );
		}
		
		$membership->delete();

		if ( \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->json( 'OK' );
		}
		else
		{
			\IPS\Output::i()->redirect( $this->_baseUrl()->setQueryString( array( 'do' => 'manageMembers' ) ), 'collab_member_deleted' );
		}
	}
	
	/**
	 * Build a table of the members associated with this collab
	 *
	 * @return	\IPS\Helpers\Table\Table		An instance of the IPS Table object
	 */
	public function _membersTable()
	{
		$collab = $this->_getCollab();

		$table = new \IPS\Helpers\Table\Db( 'core_members', $this->_baseUrl()->setQueryString( array( 'do' => 'manageMembers' ) ), array( array( 'membership.collab_id=?', $collab->collab_id ) ) );
		$table->title = \IPS\Member::loggedIn()->language()->addToStack( 'collab_members', FALSE, array ( 'sprintf' => array( $collab->collab_singular ) ) );
		
		$table->langPrefix = 'members_';
		$table->tableTemplate = array( \IPS\Theme::i()->getTemplate( 'components' ), 'tableWrapper' );
		$table->rowsTemplate = array( \IPS\Theme::i()->getTemplate( 'components' ), 'tableRows' );

		$table->include = array( 'photo', 'name', 'collab_title', 'collab_roles', 'collab_joined', 'collab_status' );
		$table->mainColumn = 'name';
		$table->noSort	= array( 'photo' );
	
		/* Default sort options */
		$table->sortBy = $table->sortBy ?: 'membership.joined';
		$table->sortDirection = $table->sortDirection ?: 'desc';
		
		$roles = array( '' => 'any_group' );
		foreach ( $collab->roles() as $id => $role )
		{
			$roles[ $id ] = $role->name;
		}
		
		$table->quickSearch = 'name';
		$table->advancedSearch = array(
			'name'				=> \IPS\Helpers\Table\SEARCH_CONTAINS_TEXT,
			'collab_roles'			=> array( \IPS\Helpers\Table\SEARCH_SELECT, 
				array( 'options' => $roles ), 
				function( $val ) {
					return array( 'FIND_IN_SET( ?, membership.roles )', $val );
				} 
			),
			'collab_joined'			=> \IPS\Helpers\Table\SEARCH_DATE_RANGE,
			);
		
		/* Joins */
		$table->joins = array(
			array(
				'select' => 'membership.id as msid, membership.joined as collab_joined, membership.status as collab_status, membership.roles as collab_roles, membership.title as collab_title',
				'from' => array( 'collab_memberships', 'membership' ),
				'where' => 'core_members.member_id = membership.member_id' ),
		);
		
		/* Filters */
		$table->filters = array(
			'collab_members_filter_active'		=> 'membership.status=\'' . \IPS\collab\COLLAB_MEMBER_ACTIVE . '\'',
			'collab_members_filter_pending'		=> 'membership.status=\'' . \IPS\collab\COLLAB_MEMBER_PENDING . '\'',
			'collab_members_filter_invited'		=> 'membership.status=\'' . \IPS\collab\COLLAB_MEMBER_INVITED . '\'',
			'collab_members_filter_banned'		=> 'membership.status=\'' . \IPS\collab\COLLAB_MEMBER_BANNED . '\'',
		);
		
		/* Specify the buttons */
		$table->rootButtons = array(
			'invite'	=> array(
				'icon'		=> 'plus',
				'title'		=> 'collab_invite_member',
				'link'		=> $this->_baseUrl()->setQueryString( array( 'do' => 'inviteMember' ) ),
				'data'		=> array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('collab_invite_member') )
			)
		);
		
		$self = $this;
		$table->rowButtons = function( $row ) use ( $collab, $self )
		{
			$member = \IPS\Member::constructFromData( $row );
			
			$buttons = array();
			
			if ( $collab->collabCan( 'editMember' ) )
			{
				$buttons['edit'] = array(
					'icon'		=> 'pencil',
					'title'		=> 'edit',
					'id'		=> "{$member->member_id}-edit",
					'link'		=> $self->_baseUrl()->setQueryString( array( 'do' => 'editMember', 'membership_id' => $row['msid'] ) ),
				);
			}
			
			if ( in_array( $row['collab_status'], array( \IPS\collab\COLLAB_MEMBER_PENDING ) ) and $collab->collabCan( 'approveMember' ) )
			{
				$buttons['approve'] = array(
					'icon'		=> 'thumbs-up',
					'title'		=> 'approve',
					'link'		=> $self->_baseUrl()->setQueryString( array( 'do' => 'approveMember', 'membership_id' => $row['msid'] ) )->csrf(),
					'id'		=> "{$member->member_id}-approve",
					'data'		=> array( 'confirm' => true )
				);
			}

			if ( in_array( $row['collab_status'], array( \IPS\collab\COLLAB_MEMBER_BANNED ) ) and $collab->collabCan( 'unbanMember' ) )
			{
				$buttons['unban'] = array(
					'icon'		=> 'unlock-alt',
					'title'		=> 'unban',
					'link'		=> $self->_baseUrl()->setQueryString( array( 'do' => 'unbanMember', 'membership_id' => $row['msid'] ) )->csrf(),
					'id'		=> "{$member->member_id}-unban",
					'data'		=> array( 'confirm' => true ),
				);
			}
			else
			{
				if ( $member->member_id !== $collab->owner_id and $collab->collabCan( 'banMember' ) )
				{
					$buttons['ban'] = array(
						'icon'		=> 'ban',
						'title'		=> 'ban',
						'link'		=> $self->_baseUrl()->setQueryString( array( 'do' => 'banMember', 'membership_id' => $row['msid'] ) )->csrf(),
						'id'		=> "{$member->member_id}-ban",
						'data'		=> array( 'confirm' => true ),

					);
				}
			}

			if ( $member->member_id !== $collab->owner_id and $collab->collabCan( 'deleteMember' ) )
			{
				$buttons['remove'] = array(
					'icon'		=> 'trash',
					'title'		=> 'kick',
					'id'		=> "{$member->member_id}-kick",
					'link'		=> $self->_baseUrl()->setQueryString( array( 'do' => 'deleteMember', 'membership_id' => $row['msid'] ) )->csrf(),
					'data'		=> array( 'confirm' => true ),
				);
			}
			
			return $buttons;
		};
		
		/* Custom parsers */
		$table->parsers = array(
			'photo'	=> function( $val, $row )
			{
				return \IPS\Theme::i()->getTemplate( 'global', 'core' )->userPhoto( \IPS\Member::constructFromData( $row ), 'mini' );
			},
			'name' => function( $val, $row )
			{
				$member = \IPS\Member::constructFromData( $row );
				return $member->link();
			},
			'collab_title' => function ( $val, $row ) use ( $collab )
			{
				$member = \IPS\Member::constructFromData( $row );
				return $collab->getMembership( $member )->title();
			},
			'collab_joined'	=> function( $val, $row )
			{
				return $val ? \IPS\DateTime::ts( $val )->localeDate() : "---";
			},
			'collab_status'	=> function( $val, $row )
			{
				$statuses = \IPS\collab\Application::memberStatuses();
				return \IPS\Member::loggedIn()->language()->addToStack( $statuses[ $val ] ?: $val );
			},
			'collab_roles'	=> function( $val, $row ) use ( $collab )
			{
				$roles = array();
				foreach ( explode( ',', $val ) as $id )
				{
					if ( $role = $collab->roles( $id ) )
					{
						$roles[] = $role->name;
					}
				}
				return implode(', ', $roles);
			}
		);
		
		return $table;
	}
	
	/**
	 *  Base Url
	 */
	public function _baseUrl()
	{	
		static $url;
		if ( isset ( $url ) )
		{
			return $url;
		}
		
		$collab = $this->_getCollab();	
		$url = \IPS\Http\Url::internal( 'app=collab&module=collab&controller=admin&collab=' . $collab->collab_id );
		return $url;
	}
	
	/**
	 * Collab Action Auth Check
	 *
	 * @param	string|array		$perms		A string or array of strings representing the actions to authorize
	 * @param	\IPS\Member|NULL	$member		The member to check for (NULL for currently logged in member)
	 * @param	array			$params		Optional parameters which can be used to determine authorization
	 * @return	void					This method will exit with an error if any of the permission checks fail
	 */
	protected function _authCheck( $perms=array(), $member=NULL, $params=array() )
	{
		if ( \IPS\Request::i()->collab === NULL )
		{
			$this->_collabSelect();
		}
		
		$collab = $this->_getCollab();
		
		if ( is_string( $perms ) )
		{
			$perms = (array) $perms;
		}
		
		foreach ( $perms as $perm )
		{
			if ( !$collab->collabCan( $perm, $member, $params ) )
			{
				\IPS\collab\Application::authError( 'collab_perm_' . $perm );
			}
		}
	}
	
	/**
	 * Get Operational Collab
	 *
	 * @return	\IPS\collab\Collab			The collab object
	 */
	protected function _getCollab()
	{
		if ( isset ( $this->collab ) )
		{
			return $this->collab;
		}
		
		$this->collab = \IPS\collab\Application::activeCollab();	
		return $this->collab;
	}
	
	/**
	 * Get Operational Membership
	 *
	 * @return	\IPS\collab\Collab\Membership		The membership object
	 */
	protected function _getMembership()
	{
		$collab = $this->_getCollab();
		
		try
		{
			$membership = \IPS\collab\Collab\Membership::load( \IPS\Request::i()->membership_id );
		}
		catch ( \OutOfRangeException $e )
		{
			\IPS\Output::i()->error( 'collab_member_invalid', '2CA00/B', 403 );
		}
		
		return $collab->authObj( $membership );		
	}	
	
	/**
	 * Get Operational Role
	 *
	 * @return	\IPS\collab\Collab\Role			The role object
	 */
	protected function _getRole()
	{
		$collab = $this->_getCollab();
		
		try
		{
			$role = \IPS\collab\Collab\Role::load( \IPS\Request::i()->role_id );
		}
		catch ( \OutOfRangeException $e )
		{
			\IPS\Output::i()->error( 'collab_role_invalid', '2CA00/D', 403 );
		}
		
		return $collab->authObj( $role );
	}
	
	/**
	 * Select A Collab To Manage
	 *
	 * @return	void
	 */
	protected function _collabSelect()
	{
		$member 	= \IPS\Member::loggedIn();
		$_options	= array();
		$_params	= array();
		
		if ( \IPS\Request::i()->invitee )
		{
			try
			{
				$_params[ 'invitee' ] = \IPS\Member::load( \IPS\Request::i()->invitee );
			}
			catch ( \Exception $e ) {}
		}
		
		foreach ( $member->collabs( 'all', 'inviteMember', $_params ) as $collab )
		{
			$_options[ $collab->collab_id ] = $collab->mapped( 'title' );
		}
		
		if ( empty( $_options ) )
		{
			return;
		}
		
		$params		= array( 'do' => \IPS\Request::i()->do );
		$form 		= new \IPS\Helpers\Form( 'collab_select', 'select' );
		$form->class .= ' ipsPad';
		
		$collab_select	= new \IPS\Helpers\Form\Select( 'collab_select_id', NULL, TRUE, array( 'options' => $_options ) );
		$form->add( $collab_select );
		
		if ( \IPS\Request::i()->invitee )
		{
			$form->hiddenValues[ 'collab_invitee' ] = (int) \IPS\Request::i()->invitee;
			$params[ 'invitee' ] = (int) \IPS\Request::i()->invitee;
		}
		
		if ( $values = $form->values() )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=collab&module=collab&controller=admin&collab=' . $values[ 'collab_select_id' ] )->setQueryString( $params ) );
		}
		
		\IPS\Output::i()->output = $form;
		\IPS\Dispatcher::i()->finish();
	}
	
	
}