<?php


namespace IPS\collab\modules\front\collab;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * categories
 */
class _categories extends \IPS\Helpers\CoverPhoto\Controller
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
		if ( isset ( \IPS\Request::i()->category ) )
		{
			try
			{
				$this->_category( \IPS\collab\Category::loadAndCheckPerms( \IPS\Request::i()->category ) );
			}
			catch ( \OutOfRangeException $e )
			{
				\IPS\Output::i()->error( 'node_error', '2CCV1/A', 404, '' );
			}
		}
		else
		{
			if ( count( \IPS\collab\Category::roots() ) == 1 )
			{
				$categories = \IPS\collab\Category::roots();
				$this->_category( array_shift( $categories ) );
			}
			else
			{
				$this->index();
			}
		}
	}
	
	/**
	 * View Category Index
	 */
	protected function index()
	{
		\IPS\collab\Category::loadIntoMemory();
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('__app_collab');
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'layouts' )->categoryIndex();
	}
	
	/**
	 * View Category
	 *
	 * @return	void
	 */
	protected function _category( \IPS\collab\Category $category )
	{
		$where = NULL;
		
		if ( $category->privacy_mode == 'private' )
		{
			$member = \IPS\Member::loggedIn();
			$_collabs = iterator_to_array( \IPS\Db::i()->select( 'collab_collabs.collab_id', 'collab_collabs', array( 'collab_collabs.category_id=? AND ( ( collab_memberships.member_id=? AND collab_memberships.status IN ( \'active\', \'invited\', \'pending\' ) ) OR collab_collabs.join_mode IN (2,3) )', $category->id, $member->member_id ) )->join( 'collab_memberships', array( 'collab_collabs.collab_id=collab_memberships.collab_id' ) ) );
			if ( count( $_collabs ) )
			{
				$where = array( array( 'collab_collabs.collab_id IN (' . implode( ',', $_collabs ) . ')' ) );
			}
			else
			{
				$where = array( array( '0' ) );
			}
		}
	
		$table 			= new \IPS\Helpers\Table\Content( 'IPS\collab\Collab', $category->url(), $where, $category, NULL, NULL );
		$table->tableTemplate 	= array( \IPS\Theme::i()->getTemplate( 'components', 'collab', 'front' ), 'categoryTable' );
		$table->title 		= \IPS\Member::loggedIn()->language()->addToStack( $category->_title );
		$table->rowsTemplate 	= array( \IPS\Theme::i()->getTemplate( 'components', 'collab', 'front' ), 'collabRow' );
		$table->classes 	= array();
		$table->hover 		= TRUE;

		/* Custom Search */
		$filterOptions = array(
			'all'			=> 'collabs_all',
			'join_closed'		=> 'collabs_join_closed',
			'join_open'		=> 'collabs_join_open',
			'join_invite'		=> 'collabs_join_invite',
			'join_free'		=> 'collabs_join_free',
		);
		
		if ( \IPS\Member::loggedIn()->isAdmin() )
		{
			$filterOptions[ 'is_template' ] = 'collab_is_template';
		}
		
		$timeFrameOptions = array(
			'show_all'		=> 'show_all',
			'today'			=> 'today',
			'last_5_days'		=> 'last_5_days',
			'last_7_days'		=> 'last_7_days',
			'last_10_days'		=> 'last_10_days',
			'last_15_days'		=> 'last_15_days',
			'last_20_days'		=> 'last_20_days',
			'last_25_days'		=> 'last_25_days',
			'last_30_days'		=> 'last_30_days',
			'last_60_days'		=> 'last_60_days',
			'last_90_days'		=> 'last_90_days',
		);
				
		$table->advancedSearch = array(
			'collab_status'	=> array( \IPS\Helpers\Table\SEARCH_SELECT, array( 'options' => $filterOptions ) ),
			
			'sort_by'		=> array( \IPS\Helpers\Table\SEARCH_SELECT, array( 'options' => array(
				'views'			=> 'views',
				'rating'		=> 'rating',
				'reviews'		=> 'reviews',
				'title'			=> 'title',
				'collab_created'	=> 'created_date',
				) )
			),
			
			'sort_direction'=> array( \IPS\Helpers\Table\SEARCH_SELECT, array( 'options' => array(
				'asc'			=> 'asc',
				'desc'			=> 'desc',
				) )
			),
		);
		
		$table->advancedSearchCallback = function( $table, $values )
		{
			/* Type */
			switch ( $values[ 'collab_status' ] )
			{
				case 'join_closed':
					$table->where[] = array( 'join_mode=?', \IPS\collab\COLLAB_JOIN_DISABLED );
					break;
				
				case 'join_open':
					$table->where[] = array( 'join_mode=?', \IPS\collab\COLLAB_JOIN_APPROVE );
					break;
					
				case 'join_invite':
					$table->where[] = array( 'join_mode=?', \IPS\collab\COLLAB_JOIN_INVITE );
					break;
					
				case 'join_free':
					$table->where[] = array( 'join_mode=?', \IPS\collab\COLLAB_JOIN_FREE );
					break;
					
				case 'is_template':
					$table->where[] = array( 'is_template=?', 1 );
			}
			
			/* Sort */
			switch ( $values['sort_by'] )
			{
				case 'collab_created':
					$table->sortBy = 'created_date';
					break;
					
				default:
					$table->sortBy = $values[ 'sort_by' ];
					
			}
			
			$table->sortDirection = $values['sort_direction'];
			

		};
		
		\IPS\Request::i()->sort_direction = \IPS\Request::i()->sort_direction ?: mb_strtolower( $table->sortDirection );

		/* Online User Location */
		$permissions = $category->permissions();
		\IPS\Session::i()->setLocation( $category->url(), explode( ",", $permissions['perm_view'] ), 'loc_collab_viewing_category', array( "collab_category_{$category->id}" => TRUE ) );
		
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( $category->_title );
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'layouts' )->category( $category, $table );		
	}
	
	/**
	 * Add Collab
	 *
	 * @return	void
	 */
	protected function add()
	{
		try
		{
			$category = \IPS\collab\Category::loadAndCheckPerms( \IPS\Request::i()->category, 'add' );
		}
		catch ( \OutOfRangeException $e )
		{
			\IPS\Output::i()->error( 'no_module_permission', '2CCA1/A', 403, 'no_module_permission_guest' );
		}
		
		$form = \IPS\collab\Collab::create( $category );
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'collab_start_new', FALSE, array( 'sprintf' => array( $category->collab_singular ) ) );
		\IPS\Output::i()->output = $form;
	}	

	/**
	 * Get Cover Photo Storage Extension
	 *
	 * @return	string
	 */
	protected function _coverPhotoStorageExtension()
	{
		$class = '\IPS\collab\Category';
		return $class::$coverPhotoStorageExtension;
	}
	
	/**
	 * Set Cover Photo
	 *
	 * @param	\IPS\Helpers\CoverPhoto	$photo	New Photo
	 * @return	void
	 */
	protected function _coverPhotoSet( \IPS\Helpers\CoverPhoto $photo )
	{
		try
		{
			$class = '\IPS\collab\Category';
			$item = $class::loadAndCheckPerms( \IPS\Request::i()->category );
			
			$photoColumn = $class::$databaseColumnMap['cover_photo'];
			$item->$photoColumn = (string) $photo->file;
			
			$offsetColumn = $class::$databaseColumnMap['cover_photo_offset'];
			$item->$offsetColumn = $photo->offset;
			
			$item->save();
		}
		catch ( \OutOfRangeException $e ){}
	}

	/**
	 * Get Cover Photo
	 *
	 * @return	\IPS\Helpers\CoverPhoto
	 */
	protected function _coverPhotoGet()
	{
		try
		{
			$class = '\IPS\collab\Category';
			$item = $class::loadAndCheckPerms( \IPS\Request::i()->category );
			
			return $item->coverPhoto();
		}
		catch ( \OutOfRangeException $e )
		{
			return new \IPS\Helpers\CoverPhoto;
		}
	}
	
}