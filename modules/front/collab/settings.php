<?php


namespace IPS\collab\modules\front\collab;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * settings
 */
class _settings extends \IPS\Dispatcher\Controller
{
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
		$table 	= $this->_membershipsTable();
		$lang 	= \IPS\Member::loggedIn()->language();
		
		\IPS\collab\Application::prepareNodeManager();

		\IPS\Output::i()->title = $lang->addToStack( 'collab_manage_memberships', FALSE, array( 'sprintf' => array( $lang->addToStack( '__app_collab' ) ) ) );
		\IPS\Output::i()->output = 
			\IPS\Theme::i()->getTemplate( 'global', 'core', 'front' )->pageHeader( \IPS\Output::i()->title, $lang->addToStack( 'collab_manage_memberships_blurb', FALSE, array( 'sprintf' => array( $lang->addToStack( '__app_collab' ) ) ) ) ) .
			$table;
	}
	
	/**
	 * Edit Membership
	 *
	 * @return	void
	 */
	protected function edit()
	{
		try
		{
			$membership = \IPS\collab\Collab\Membership::load( \IPS\Request::i()->id );
		}
		catch ( \OutOfRangeException $e ) {}
		
		$member = \IPS\Member::loggedIn();
		
		if ( ! isset ( $membership ) or $membership->member_id != $member->member_id )
		{
			\IPS\Output::i()->error( 'collab_invalid_membership', '2CM00/1', 404 );
		}
		
		$form = new \IPS\Helpers\Form( 'membership_edit' );
		
		$form->add( new \IPS\Helpers\Form\Editor( 'collab_member_notes', $membership->member_notes, FALSE, array(
				'app'			=> 'collab',
				'key'			=> 'Generic',
				'autoSaveKey'		=> "membership-edit-{$membership->id}",
			)
		) );
		
		if ( $values = $form->values() )
		{
			$membership->member_notes = $values[ 'collab_member_notes' ];
			$membership->save();
			
			\IPS\Output::i()->redirect( $this->_baseUrl(), 'collab_membership_updated' );
		}
		
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'collab_edit_membership', FALSE, array( 'sprintf' => array( $membership->collab()->title ) ) );
		
		if ( ! \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'global', 'core', 'front' )->pageHeader( \IPS\Output::i()->title, "" );
		}
		
		\IPS\Output::i()->output .= $form;
		

	}

	/**
	 * Leave Collab
	 *
	 * @return	void
	 */
	protected function delete()
	{
		\IPS\Session::i()->csrfCheck();
		try
		{
			$membership = \IPS\collab\Collab\Membership::load( \IPS\Request::i()->id );
		}
		catch ( \OutOfRangeException $e ) {}
		
		$member = \IPS\Member::loggedIn();
		
		if ( ! isset ( $membership ) or $membership->member_id != $member->member_id )
		{
			\IPS\Output::i()->error( 'collab_invalid_membership', '2CM00/2', 404 );
		}
		
		if ( $collab = $membership->collab() and $membership->member_id == $collab->owner_id )
		{
			\IPS\Output::i()->error( 'collab_owner_non_removable', '2CM01/2', 404 );
		}
		
		$membership->delete();
		\IPS\Output::i()->redirect( $this->_baseUrl(), 'collab_message_membership_deleted' );

	}

	/**
	 * Transfer Collab Ownership
	 *
	 * @return	void
	 */
	protected function transfer()
	{
		try
		{
			$membership = \IPS\collab\Collab\Membership::load( \IPS\Request::i()->id );
		}
		catch ( \OutOfRangeException $e ) {}
		
		$member = \IPS\Member::loggedIn();
		
		if ( 
			! isset ( $membership ) or 
			$membership->member_id != $member->member_id or
			! ( $collab = $membership->collab() ) or
			$collab->owner_id != $membership->member_id
		)
		{
			\IPS\Output::i()->error( 'collab_invalid_membership', '2CM00/3', 404 );
		}
		
		$_options = array();
		foreach ( $collab->memberships( array( 'statuses' => array( \IPS\collab\COLLAB_MEMBER_ACTIVE ) ) ) as $_membership )
		{
			if ( $_membership->member_id !== $membership->member_id and $_membership->canOwn() )
			{
				$_options[ $_membership->member_id ] = $_membership->member()->name;
			}
		}
		
		if ( empty ( $_options ) )
		{
			\IPS\Output::i()->error( 'collab_no_transfer_members', '2CM00/4', 404 );
		}
		
		$form = new \IPS\Helpers\Form( 'collab_owner_transfer' );
		$form->add( new \IPS\Helpers\Form\Select( 'collab_new_owner', NULL, TRUE, array( 'options' => $_options ) ) );
		
		if ( $values = $form->values() )
		{
			$new_owner = \IPS\Member::load( $values[ 'collab_new_owner' ] );
			if ( $new_owner->member_id )
			{
				$collab->owner_id = $new_owner->member_id;
				$collab->owner_name = $new_owner->name;
				$collab->save();
				\IPS\Output::i()->redirect( $this->_baseUrl(), 'collab_message_ownership_transferred' );
			}
		}
		
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'collab_transfer_ownership', FALSE, array( 'sprintf' => array( $collab->title ) ) );
		
		if ( ! \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'global', 'core', 'front' )->pageHeader( \IPS\Output::i()->title, "" );
		}
		
		\IPS\Output::i()->output .= $form;
	}
	
	/**
	 * Build a table of the member's collab memberships
	 *
	 * @return	\IPS\Helpers\Table\Table		An instance of the IPS Table object
	 */
	public function _membershipsTable( $member=NULL )
	{		
		$member = $member ?: \IPS\Member::loggedIn();
		
		$table = new \IPS\Helpers\Table\Db( 'collab_memberships', $this->_baseUrl(), array( array( 'member_id=?', $member->member_id ) ) );
		$table->title = \IPS\Member::loggedIn()->language()->addToStack( 'collab_memberships' );
		
		$table->langPrefix = 'collab_membership_';
		$table->tableTemplate = array( \IPS\Theme::i()->getTemplate( 'components' ), 'tableWrapper' );
		$table->rowsTemplate = array( \IPS\Theme::i()->getTemplate( 'components' ), 'tableRows' );

		$table->include = array( 'collab_id', 'title', 'roles', 'status', 'joined', 'members_count' );
		$table->mainColumn = 'collab_id';
	
		/* Default sort options */
		$table->sortBy = $table->sortBy ?: 'joined';
		$table->sortDirection = $table->sortDirection ?: 'desc';
		$table->noSort = array( 'roles' );
				
		$self = $this;
		$table->rowButtons = function( $row ) use ( $self )
		{	
			$buttons = array();
			try
			{
				$collab = \IPS\collab\Collab::load( $row[ 'collab_id' ] );
			}
			catch ( \OutOfRangeException $e ) {}
			
			if ( $row[ 'status' ] !== \IPS\collab\COLLAB_MEMBER_BANNED  )
			{
				if ( in_array( $row[ 'status' ], array( \IPS\collab\COLLAB_MEMBER_INVITED, \IPS\collab\COLLAB_MEMBER_PENDING ) ) )
				{
					$buttons[ 'edit' ] = array(
						'icon'		=> 'pencil',
						'title'		=> $row[ 'status' ] === \IPS\collab\COLLAB_MEMBER_INVITED ? 'collab_respond' : 'collab_update',
						'link'		=> \IPS\Http\Url::internal( "app=collab&module=collab&controller=collabs&id={$collab->collab_id}&do=joinRequest" ),
					);			
				}
				else 
				{
					$buttons[ 'edit' ] = array(
						'icon'		=> 'pencil',
						'title'		=> 'edit',
						'link'		=> $self->_baseUrl()->setQueryString( array( 'do' => 'edit', 'id' => $row['id'] ) ),
					);
				}
				
				if ( $row[ 'member_id' ] === $collab->owner_id )
				{
					$buttons[ 'transfer' ] = array(
						'icon'		=> 'share',
						'title'		=> 'collab_transfer',
						'link'		=> $self->_baseUrl()->setQueryString( array( 'do' => 'transfer', 'id' => $row['id'] ) )->csrf(),
						'data'		=> array( 'confirm' => true ),
					);
				}
				else
				{
					$buttons[ 'remove' ] = array(
						'icon'		=> 'times-circle',
						'title'		=> 'collab_leave',
						'link'		=> $self->_baseUrl()->setQueryString( array( 'do' => 'delete', 'id' => $row['id'] ) )->csrf(),
						'data'		=> array( 'confirm' => true ),
					);
				}
			}
			
			return $buttons;
		};

		/* Custom parsers */
		$table->parsers = array(
			'collab_id'	=> function( $val, $row )
			{
				try
				{
					$collab = \IPS\collab\Collab::load( $row[ 'collab_id' ] );
				}
				catch ( \OutOfRangeException $e )
				{
					return "---";
				}
			
				return "<a href='{$collab->url()}'>" . $collab->mapped( 'title' ) . "</a>";
			
			},
			'roles'		=> function( $val, $row )
			{
				try
				{
					$collab = \IPS\collab\Collab::load( $row[ 'collab_id' ] );
				}
				catch ( \OutOfRangeException $e )
				{
					return "---";
				}
			
				$roles = explode( ',', $val );
				foreach ( $roles as &$role )
				{
					$role = $collab->roles( $role )->name;
				}
				
				return implode( ', ', $roles );
			},
			'title'		=> function( $val, $row )
			{
				$membership = \IPS\collab\Collab\Membership::constructFromData( $row );
				return $membership->title();
			},
			'joined'	=> function( $val, $row )
			{
				return $val ? \IPS\DateTime::ts( $val )->localeDate() : "---";
			},
			'status' 	=> function( $val, $row )
			{
				return ucwords( $val );
			},
			'members_count' => function( $val, $row )
			{
				try
				{
					$collab = \IPS\collab\Collab::load( $row[ 'collab_id' ] );
				}
				catch ( \OutOfRangeException $e )
				{
					return "---";
				}
				
				return $collab->memberships( array( 'statuses' => \IPS\collab\COLLAB_MEMBER_ACTIVE, 'count' => TRUE ) ) . ' / ' . $collab->memberships( array( 'count' => TRUE ) );
			},
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
		
		$url = \IPS\Http\Url::internal( 'app=collab&module=collab&controller=settings' );
		return $url;
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
	

}