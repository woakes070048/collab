<?php


namespace IPS\collab\modules\front\collab;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * collabs
 */
class _collabs extends \IPS\Content\Controller
{
	/**
	 * [Content\Controller]	Class
	 */
	protected static $contentModel = 'IPS\collab\Collab';
	
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{
		$collab = parent::manage();
		$this->viewCollab( $collab );
	}
	
	/**
	 * Default Collab View
	 *
	 * @return	void
	 */
	protected function viewCollab( $collab = NULL )
	{
	
		if ($collab === NULL)
		{
			\IPS\Output::i()->error( 'page_doesnt_exist', '2CV01/A', 404 );
		}
		
		\IPS\collab\Application::$inferredCollab = $collab;
		\IPS\collab\Category::loadIntoMemory();
		
		$lang		= \IPS\Member::loggedIn()->language();
		
		// Rename 'collab' to appropriate string
		$lang->words[ 'collab' ] = $collab->collab_singular;
		
		/* @TODO: AJAX hover preview */
		if ( \IPS\Request::i()->isAjax() and \IPS\Request::i()->preview )
		{
			// Example: /applications/forums/modules/front/forums/topic.php
			return;
		}
		
		/* Sort out comments and reviews */
		$tabs = array();
		if ( $collab->container()->bitoptions[ 'allow_comments' ] )
		{
			$tabs[ 'comments' ] = \IPS\Member::loggedIn()->language()->pluralize( \IPS\Member::loggedIn()->language()->get( 'collab_comment_count' ), array( $collab->mapped('num_comments') ) );
		}
		
		if ( $collab->container()->bitoptions[ 'allow_reviews' ] )
		{
			$tabs[ 'reviews' ] = \IPS\Member::loggedIn()->language()->pluralize( \IPS\Member::loggedIn()->language()->get( 'collab_review_count' ), array( $collab->mapped('num_reviews') ) );		
		}
		
		$activity 	= NULL;
		
		if ( count( $tabs ) )
		{
			$_tabs 		= array_keys( $tabs );
			$active_tab	= isset( \IPS\Request::i()->tab ) ? \IPS\Request::i()->tab : array_shift( $_tabs );
			$_tab_contents 	= \IPS\Theme::i()->getTemplate( 'tabs' )->$active_tab( $collab );
			$activity 	= \IPS\Theme::i()->getTemplate( 'global', 'core' )->commentsAndReviewsTabs( \IPS\Theme::i()->getTemplate( 'global', 'core' )->tabs( $tabs, $active_tab, $_tab_contents, $collab->url(), 'tab', FALSE, TRUE ), md5( $collab->url() ) );
		}
		
		if ( \IPS\Request::i()->isAjax() and !\IPS\Request::i()->rating_submitted )
		{
			\IPS\Output::i()->output = $_tab_contents;
			return;
		}
		
		if ( \IPS\Request::i()->isAjax() and \IPS\Request::i()->rating_submitted )
		{
			/* Allow processing of submitted rating */
			$collab->rating();
		}
		
		/* Mark read */
		if( !$collab->isLastPage() )
		{
			$maxTime	= 0;

			foreach( $comments as $comment )
			{
				$maxTime	= ( $comment->mapped('date') > $maxTime ) ? $comment->mapped('date') : $maxTime;
			}

			$collab->markRead( NULL, $maxTime );
		}
		
		/* Online User Location */
		$permissions = $collab->container()->permissions();
		\IPS\Session::i()->setLocation( $collab->url(), explode( ",", $permissions['perm_view'] ), 'loc_collab_viewing_collab', array( $collab->title => FALSE ) );
		
		try {
			// Load css from the forums app which we use
			\IPS\forums\Topic::contentTableTemplate();
		}
		catch ( \Exception $e) {}
				
		\IPS\Output::i()->title = $collab->title;
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'layouts' )->collab( $collab, $activity );		
	
	}
	
	/**
	 * Edit Collab
	 *
	 * @return	void
	 */
	protected function edit()
	{
		try
		{
			$class 	= static::$contentModel;
			$collab	= $class::loadAndCheckPerms( \IPS\Request::i()->id );
			\IPS\collab\Application::$inferredCollab = $collab;
			
			if ( !$collab->canEdit() )
			{
				\IPS\collab\Application::authError( 'editCollab', array( $collab->collab_singular ) );
			}
						
			$form = new \IPS\Helpers\Form( 'form', \IPS\Member::loggedIn()->language()->addToStack( $class::$formLangPrefix . '_save' ) ? $class::$formLangPrefix . '_save' : 'save' );
			$form->class = 'ipsForm_vertical';
						
			$container = NULL;
			try
			{
				$container = $collab->container();
			}
			catch ( \BadMethodCallException $e ) {}
			
			$formElements = $class::formElements( $collab, $container );
			$form->addTab( 'collab_tab_description' );
			
			foreach ( $formElements as $key => $object )
			{
				if ( \IPS\Request::i()->_report AND !in_array( $key, array( 'title', 'content', 'description' ) ) )
				{
					continue;
				}
				
				if ( $key === 'poll' )
				{
					$form->addTab( $class::$formLangPrefix . 'pollTab' );
				}
				elseif ( mb_substr( $key, 0, mb_strlen( 'tab_' ) ) == 'tab_' )
				{
					$form->addTab( 'collab_' . $key );
				}
				
				$form->add( $object );
			}
			
			$collabAdmin = new \IPS\collab\modules\front\collab\admin( NULL, $collab );
			
			$form->addTab( 'collab_tab_settings' );
			$form->add( new \IPS\Helpers\Form\Radio( 'collab_join_mode', $collab->join_mode, TRUE, array( 
				'options' => array( 
					\IPS\collab\COLLAB_JOIN_DISABLED => 'collab_join_disabled', 
					\IPS\collab\COLLAB_JOIN_INVITE => 'collab_join_invite_only', 
					\IPS\collab\COLLAB_JOIN_APPROVE => 'collab_join_approve', 
					\IPS\collab\COLLAB_JOIN_FREE => 'collab_join_free' 
				)
			) ) );
			$form->addSeperator();
			$form->add( new \IPS\Helpers\Form\Text( 'collab_default_title', $collab ? $collab->default_member_title : \IPS\Member::loggedIn()->language()->get( 'collab_default_member_title' ), FALSE, array( 'placeholder' => \IPS\Member::loggedIn()->language()->addToStack( 'collab_default_member_title', FALSE, array( 'sprintf' => array( $collab->collab_singular ) ) ) ) ) );
			$form->add( new \IPS\Helpers\Form\Editor( 'collab_rules', $collab->rules, FALSE, array(
					'app'			=> 'collab',
					'key'			=> 'Generic',
					'autoSaveKey'		=> "collab-rules-{$collab->collab_id}",
				)
			) );
			
			if ( $values = $form->values() )
			{
				$collab->processForm( $values );
				if ( isset( $collab::$databaseColumnMap['updated'] ) )
				{
					$column = $collab::$databaseColumnMap['updated'];
					$collab->$column = time();
				}

				if ( isset( $collab::$databaseColumnMap['date'] ) and isset( $values[ $collab::$formLangPrefix . 'date' ] ) )
				{
					$column = $collab::$databaseColumnMap['date'];

					if ( $values[ $collab::$formLangPrefix . 'date' ] instanceof \IPS\DateTime )
					{
						$collab->$column = $values[ $collab::$formLangPrefix . 'date' ]->getTimestamp();
					}
					else
					{
						$collab->$column = time();
					}
				}
				
				$collab->rules = $values[ 'collab_rules' ];
				$collab->join_mode = $values[ 'collab_join_mode' ];

				$collab->save();
				$collab->processAfterEdit( $values );

				\IPS\Output::i()->redirect( $collab->url() );
			}
			
			$this->_setBreadcrumbAndTitle( $collab );
			\IPS\Output::i()->output = $form;
		}
		catch ( \Exception $e )
		{
			//\IPS\Output::i()->error( 'node_error', '2S136/E', 404, '' );
			throw $e;
		}
	}

	/**
	 * Show Collab Members
	 *
	 * @return	void
	 */
	protected function members()
	{
		$class 		= static::$contentModel;
		$collab		= $class::loadAndCheckPerms( \IPS\Request::i()->id );
		$perPage 	= 20;
		$thisPage 	= isset( \IPS\Request::i()->membersPage ) ? \IPS\Request::i()->membersPage : 1;
		
		$members_count 	= $collab->memberships( array ( 
					'statuses' => array( \IPS\collab\COLLAB_MEMBER_ACTIVE ), 
					'count' => TRUE, 
				) );
		$memberships 	= $collab->memberships( array ( 
					'statuses' => array( \IPS\collab\COLLAB_MEMBER_ACTIVE ), 
					'limit' => array( ( $thisPage - 1 ) * $perPage, $perPage ) 
				) );
	
		/* Display */
		if ( \IPS\Request::i()->isAjax() and isset( \IPS\Request::i()->_infScroll ) )
		{
			\IPS\Output::i()->sendOutput(  \IPS\Theme::i()->getTemplate( 'components' )->membersRows( $memberships ) );
		}
		else
		{
			$url = $collab->url()->setQueryString( array( 'do' => 'members' ) );
			$pagination = \IPS\Theme::i()->getTemplate( 'global', 'core', 'global' )->pagination( $url, ceil( $members_count / $perPage ), $thisPage, $perPage, FALSE, 'membersPage' );
			
			\IPS\collab\Application::makeBreadcrumbs( $collab );
			\IPS\Output::i()->breadcrumb[] = array( NULL, \IPS\Member::loggedIn()->language()->addToStack( 'collab_active_members' ) );
			
			\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'collab_active_members_title' , FALSE, array( 'sprintf' => array( $collab->title ) ) );
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'components' )->membersList( $url, $pagination, $memberships );
		}
	}

	/**
	 * Request to Join Collab
	 *
	 * @return	void
	 */
	protected function joinRequest()
	{
		
		/* Check for permission to join the collab */
		try 
		{
			$collab = \IPS\collab\Collab::loadAndCheckPerms( \IPS\Request::i()->id );
			if ( ! $collab->container()->can( 'join' ) )
			{
				$this->_joinError( 'restricted' );
			}
			
			if ( $collab->isFull() )
			{
				$this->_joinError( 'full' );
			}
			
			if ( $collab->join_mode == \IPS\collab\COLLAB_JOIN_DISABLED )
			{
				$this->_joinError( 'disabled' );
			}
		}
		catch ( \OutOfRangeException $e )
		{
			\IPS\Output::i()->error( 'page_doesnt_exist', '2CJ03/C', 404 );
		}
		
		\IPS\collab\Application::makeBreadcrumbs( $collab );
		
		/* Check if member already has a membership status */
		if ( $membership = $collab->getMembership() )
		{
			// Handle existing membership conditions
			// #1: Member has been invited already, so proceed with activation
			// #2: Member already has a pending join request, so allow them to update it
			// #3: Member is already active, banned, or something else... so bail out
			
			switch ( $membership->status )
			{
				case \IPS\collab\COLLAB_MEMBER_PENDING:
					// do nothing, allow the form to be built (or processed) as usual
					break;
					
				case \IPS\collab\COLLAB_MEMBER_INVITED:
					$this->_confirmInvite( $collab, $membership );
					return;
					
				case \IPS\collab\COLLAB_MEMBER_ACTIVE:
					$this->_joinError( 'active' );
					
				case \IPS\collab\COLLAB_MEMBER_BANNED:
					$this->_joinError( 'banned' );
					
				default:
					$this->_joinError();
			}
		}
		else {
		
			if ( $collab->join_mode == \IPS\collab\COLLAB_JOIN_INVITE )
			{
				$this->_joinError( 'invite_only' );
			}
			
			// No record, allow member to submit a new join request
			$membership = new \IPS\collab\Collab\Membership;
			$membership->member_id = \IPS\Member::loggedIn()->member_id;
			$membership->collab_id = $collab->collab_id;
			
			if ( $collab->join_mode === \IPS\collab\COLLAB_JOIN_FREE )
			{
				$membership->status = \IPS\collab\COLLAB_MEMBER_ACTIVE;
				$membership->joined = time();
			}
			else
			{
				$membership->status = \IPS\collab\COLLAB_MEMBER_PENDING;
			}
		}
		
		$form = new \IPS\Helpers\Form( 'join_collab', 'submit' );
		
		$form->add( new \IPS\Helpers\Form\Editor( 'collab_join_message', $membership->member_notes, FALSE, array(
				'app'			=> 'collab',
				'key'			=> 'Generic',
				'autoSaveKey'		=> "collab-join-{$collab->collab_id}-" . \IPS\Member::loggedIn()->member_id,
			)
		) );
		
		if ( $values = $form->values() )
		{
			// Process Form Submission			
			$membership->member_notes = $values['collab_join_message'];
			$membership->member_notes_updated = time();
			if ( ! $membership->id and $membership->status === \IPS\collab\COLLAB_MEMBER_PENDING )
			{
				$membership->save();
				
				// Create "New Join Request" Notification
				$notification = new \IPS\Notification( \IPS\Application::load( 'collab' ), 'collab_join_requested', $membership, array( $membership->member(), $collab, $membership ) );
				$notification->recipients->attach( $collab->author() );
				foreach ( $collab->memberships( array( 'permissions' => array( 'approveMember' ) ) ) as $approver )
				{
					if ( $approver->member_id != $collab->author()->member_id )
					{
						try
						{
							$recipient = \IPS\Member::load( $approver->member_id );
							$notification->recipients->attach( $recipient );
						}
						catch ( \OutOfRangeException $e ) {}
					}
				}
				$notification->send();
				\IPS\Output::i()->redirect( $collab->url(), 'collab_message_join_request_sent' );
			}
			else
			{
				$membership->save();
				\IPS\Output::i()->redirect( $collab->url(), 'collab_message_join_request_updated' );
			}
		}
		
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'collab_join', FALSE, array( 'sprintf' => array( $collab->collab_singular ) ) );
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'forms' )->collabJoinRequest( $collab, $form, $membership );
	
	}
	
	/**
	 * Bail out of a join request for some reason
	 *
	 * @return	void
	 */
	protected function _joinError( $reason='state' )
	{
		switch ( $reason )
		{
			case 'active':
				$message = \IPS\Member::loggedIn()->language()->addToStack( 'collab_join_error_active', FALSE, array( 'sprintf' => array( $collab->collab_singular ) ) );
				\IPS\Output::i()->error( $message, '2CJ03/D', 403 );			
				break;
				
			case 'banned':
				$message = \IPS\Member::loggedIn()->language()->addToStack( 'collab_join_error_banned', FALSE, array( 'sprintf' => array( $collab->collab_singular ) ) );
				\IPS\Output::i()->error( $message, '2CJ03/E', 403 );
				break;
				
			default:
				\IPS\Output::i()->error( 'collab_join_error_' . $reason , '2CJ03/F', 403 );
				break;
			
		}	
	}
	
	/**
	 * Allow a user to accept/deny a collab invite
	 *
	 * @return	void
	 */
	protected function _confirmInvite( \IPS\collab\Collab $collab, \IPS\collab\Collab\Membership $membership )
	{
		$form = new \IPS\Helpers\Form( 'confirm_invite', 'confirm' );
		$lang = \IPS\Member::loggedIn()->language();
		
		$response_options = array(
			'accept' 	=> 'collab_accept_invitation',
			'deny'	 	=> 'collab_deny_invitation',
		);
		
		$form->add( new \IPS\Helpers\Form\Radio( 'collab_invite_response', NULL, TRUE, array( 'options' => $response_options ) ) );
		
		if ( $values = $form->values() )
		{
			// process invitation response ( accept / deny )
			switch ( $values[ 'collab_invite_response' ] )
			{
				case 'accept':
				
					$membership->status = \IPS\collab\COLLAB_MEMBER_ACTIVE;
					$membership->joined = $membership->joined ?: time();
					$membership->save();
				
					// Send "Invitation Accepted" Notification
					$notification = new \IPS\Notification( \IPS\Application::load( 'collab' ), 'collab_invitation_accepted', $membership, array( $membership->member(), $collab, $membership ) );
					$notification->recipients->attach( $membership->sponsor_id ? $membership->sponsor() : $collab->author() );
					$notification->send();
					
					\IPS\Output::i()->redirect( $collab->url(), 'collab_message_invitation_accepted' );
					break;
					
				case 'deny':
				
					$membership->delete();
					\IPS\Output::i()->redirect( $collab->url(), 'collab_message_invitation_denied' );
					break;
			}
		}

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'collab_join', FALSE, array( 'sprintf' => array( $collab->collab_singular ) ) );
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'forms' )->collabJoinRequest( $collab, $form, $membership );
		
	}

	/**
	 * Mark this collab as a model
	 *
	 * @return	void
	 */
	protected function makeModel()
	{
		\IPS\Session::i()->csrfCheck();
		try 
		{
			$collab = \IPS\collab\Collab::loadAndCheckPerms( (int) \IPS\Request::i()->id );
			if ( ! $collab->canMakeModel() )
			{
				\IPS\Output::i()->error( 'node_error', '2CM03/A', 403 );
			}
			
			$collab->is_template = 1;
			$collab->save();
		}
		catch ( \OutOfRangeException $e )
		{
			\IPS\Output::i()->error( 'page_doesnt_exist', '2CM03/B', 404 );
		}
		
		\IPS\Output::i()->redirect( $collab->url(), 'collab_message_model_marked' );
	}
	
	/**
	 * Unmark this collab as a model
	 *
	 * @return	void
	 */
	protected function unmakeModel()
	{
		\IPS\Session::i()->csrfCheck();
		try 
		{
			$collab = \IPS\collab\Collab::loadAndCheckPerms( (int) \IPS\Request::i()->id );
			if ( ! $collab->canUnmakeModel() )
			{
				\IPS\Output::i()->error( 'node_error', '2CM03/A', 403 );
			}
			
			$collab->is_template = 0;
			$collab->save();
		}
		catch ( \OutOfRangeException $e )
		{
			\IPS\Output::i()->error( 'page_doesnt_exist', '2CM03/B', 404 );
		}
		
		\IPS\Output::i()->redirect( $collab->url(), 'collab_message_model_unmarked' );
	}		
}