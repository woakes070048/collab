//<?php

class collab_hook_ipsMember extends _HOOK_CLASS_
{

	/*
	 * @brief	Cache for memberships
	 */
	protected $memberships = NULL;
	
	/*
	 * @brief	Cache for collabs
	 */
	protected $collabs = array();
	
	/**
	 * Get all collab memberships for this member
	 *
	 * @return	array	Array of Membership Active Records [\IPS\collab\Collab\Membership]
	 */
	public function collab_memberships()
	{

		if ( isset( $this->memberships ) )
		{
			return $this->memberships;
		}
		
		$memberships = array();
		try
		{
			$rows = \IPS\Db::i()->select( '*', 'collab_memberships', array( 'member_id=?', $this->member_id ) );
			foreach ( $rows as $row )
			{
				$memberships[ $row[ 'collab_id' ] ] = \IPS\collab\Collab\Membership::constructFromData( $row );
			}
		}
		catch ( \UnderflowException $e ) {}
			
		return $this->memberships = $memberships;
	}
	
	/**
	 * Get collabs of a particular membership status or with specific permissions
	 *
	 * @param	mixed		$status		"all" for all statuses, or a membership status id
	 * @param	string|array	$perms		permissions to check for
	 * @param	array		$params		array of parameters to supply with permission check
	 * @return	array				Array of Collab Active Records [\IPS\collab\Collab]
	 */
	public function collabs( $status='all', $perms=NULL, $params=array() )
	{
		$cache_key = md5( $status . json_encode( $perms ) . json_encode( $params ) );
		
		if ( isset( $this->collabs[ $cache_key ] ) )
		{
			return $this->collabs[ $cache_key ]; 
		}
	      
		$this->collabs[ $cache_key ] = array();
		foreach ( $this->collab_memberships() as $membership )
		{
			if ( $status == 'all' or $membership->status == $status )
			{
				try
				{
					$collab = \IPS\collab\Collab::load( $membership->collab_id );
					if ( $perms !== NULL )
					{
						$_pass = FALSE;
						foreach ( (array) $perms as $perm )
						{
							$_pass = $collab->collabCan( $perm, $this, $params );
							if ( ! $_pass ) break;
						}
						if ( ! $_pass ) continue;
					}
					$this->collabs[ $cache_key ][] = $collab;		
				}
				catch( \OutOfRangeException $e ) {}
			}
		}
		return $this->collabs[ $cache_key ];
	}
	
	/**
	 * Get moderator permission
	 *
	 * @param	string|NULL	$key	Permission Key to check, or NULL to just test if they have any moderator permissions.
	 * @return	mixed
	 */
	public function modPermission( $key=NULL )
	{		
		/**
		 * Special case forums mod permissions
		 */
		if ( in_array( $key, array( 'forums', 'can_read_all_topics' ) ) )
		{
			if ( $collab = \IPS\collab\Application::affectiveCollab() )
			{			
				if ( $membership = $collab->getMembership( $this ) )
				{
					if ( $membership->can( 'moderateContent' ) )
					{
						return TRUE;
					}
				}
			}
		}
		
		return call_user_func_array( 'parent::modPermission', func_get_args() );
	}
	
	/**
	 * Recounts content for this member (excluding collab content)
	 *
	 * @return void
	 */
	public function recountContent()
	{
		/* Write our count to bypass the magic __set method */
		$this->_data[ 'member_posts' ] = 0;

		foreach ( \IPS\Content::routedClasses( $this, TRUE, TRUE, FALSE ) as $class )
		{
			if ( $class::memberPostCount( $this ) !== 0 )
			{
				$itemClass = NULL;
				
				if ( isset ( $class::$itemClass ) )
				{
					$itemClass = $class::$itemClass;
					if ( isset ( $itemClass::$containerNodeClass ) )
					{
						$nodeClass = $itemClass::$containerNodeClass;
					}
				}
				else
				{		
					if ( isset ( $class::$containerNodeClass ) )
					{
						$nodeClass = $class::$containerNodeClass;
					}
				}
				
				if 
				( 	/** 
					 * Check if container node is provisioned for collab usage...
					 * and if so, build a query that only includes posts from
					 * non-collab categories and also collab categories that are
					 * configured to include posts in the main site post count.
					 */
					isset ( $nodeClass ) and 
					\IPS\Db::i()->checkForColumn( $nodeClass::$databaseTable, $nodeClass::$databasePrefix . 'collab_id' ) 
				)
				{
					$_is_author		= $class::$databaseTable . '.' . $class::$databasePrefix . $class::$databaseColumnMap[ 'author' ] . '=?';
					$_not_collab		= $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . 'collab_id=0';
					$_increase_mainposts 	= "( collab_categories.category_bitoptions & 64 ) != 0";
					
					$where 	= array( array( "{$_is_author} AND ( {$_not_collab} OR {$_increase_mainposts} )", $this->member_id ) );
					
					/**
					 * Special Cases
					 */
					switch ( $class )
					{
						case 'IPS\forums\Topic\Post':
						
							$where[] = array( 'forums_forums.inc_postcount=1' );
							break;
					
					}
					
					$select = \IPS\Db::i()->select( 'COUNT(*)', $class::$databaseTable, $where );
					
					if ( isset ( $itemClass ) )
					{
						$select->join( $itemClass::$databaseTable, array( $itemClass::$databaseTable . '.' . $itemClass::$databasePrefix . $itemClass::$databaseColumnId . '=' . $class::$databaseTable . '.' . $class::$databasePrefix . $class::$databaseColumnMap['item'] ) );
						$select->join( $nodeClass::$databaseTable, array( $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . $nodeClass::$databaseColumnId . '=' . $itemClass::$databaseTable . '.' . $itemClass::$databasePrefix . $itemClass::$databaseColumnMap['container'] ) );
					}
					else
					{
						$select->join( $nodeClass::$databaseTable, array( $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . $nodeClass::$databaseColumnId . '=' . $class::$databaseTable . '.' . $class::$databasePrefix . $class::$databaseColumnMap['container'] ) );
					}
					
					$select->join( 'collab_collabs', array( 'collab_collabs.collab_id='. $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . 'collab_id' ) );
					$select->join( 'collab_categories', array( 'collab_collabs.category_id=collab_categories.category_id' ) );
					
					$this->_data[ 'member_posts' ] += $select->first();
				}
				else
				{
					/* It's not collab content, so count it all */
					$this->_data[ 'member_posts' ] += $class::memberPostCount( $this );
				}
			}
		}
		
		$this->changed[ 'member_posts' ] = $this->_data[ 'member_posts' ];
		$this->save();
		
		/* Rules Fix */
		\IPS\Application::appIsEnabled( 'rules', TRUE ) and \IPS\rules\Event::load( 'rules', 'Members', 'content_recounted' )->trigger( $this, $this->_data[ 'member_posts' ] );

	}
	
	/**
	 * Recounts content for this member for a specific collab
	 *
	 * @return void
	 */
	public function recountCollabContent( \IPS\collab\Collab $collab )
	{
		/* recount content collab content */
		if ( $memberOrGuest = $collab->getMembership( $this ) ?: $collab->guest( $this, FALSE ) )
		{
			$memberOrGuest->posts = 0;
			foreach ( \IPS\Content::routedClasses( $this, TRUE, TRUE, FALSE ) as $class )
			{
				$itemClass = NULL;
				
				if ( isset ( $class::$itemClass ) )
				{
					$itemClass = $class::$itemClass;
					if ( isset ( $itemClass::$containerNodeClass ) )
					{
						$nodeClass = $itemClass::$containerNodeClass;
					}
				}
				else
				{		
					if ( isset ( $class::$containerNodeClass ) )
					{
						$nodeClass = $class::$containerNodeClass;
					}
				}
				
				if 
				( 	/* Check if container node is provisioned for collab usage */
					isset ( $nodeClass ) and 
					\IPS\Db::i()->checkForColumn( $nodeClass::$databaseTable, $nodeClass::$databasePrefix . 'collab_id' ) 
				)
				{
					$_is_author		= $class::$databaseTable . '.' . $class::$databasePrefix . $class::$databaseColumnMap[ 'author' ] . '=?';
					$_is_collab		= $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . 'collab_id=' . $collab->collab_id;
					
					$where 	= array( array( "{$_is_author} AND {$_is_collab}", $this->member_id ) );
					
					/**
					 * Special Cases
					 */
					switch ( $class )
					{
						case 'IPS\forums\Topic\Post':
						
							$where[] = array( 'forums_forums.inc_postcount=1' );
							break;
					
					}
					
					$select = \IPS\Db::i()->select( 'COUNT(*)', $class::$databaseTable, $where );
					
					if ( isset ( $itemClass ) )
					{
						$select->join( $itemClass::$databaseTable, array( $itemClass::$databaseTable . '.' . $itemClass::$databasePrefix . $itemClass::$databaseColumnId . '=' . $class::$databaseTable . '.' . $class::$databasePrefix . $class::$databaseColumnMap[ 'item' ] ) );
						$select->join( $nodeClass::$databaseTable, array( $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . $nodeClass::$databaseColumnId . '=' . $itemClass::$databaseTable . '.' . $itemClass::$databasePrefix . $itemClass::$databaseColumnMap[ 'container' ] ) );
					}
					else
					{
						$select->join( $nodeClass::$databaseTable, array( $nodeClass::$databaseTable . '.' . $nodeClass::$databasePrefix . $nodeClass::$databaseColumnId . '=' . $class::$databaseTable . '.' . $class::$databasePrefix . $class::$databaseColumnMap[ 'container' ] ) );
					}
									
					$memberOrGuest->posts += $select->first();
				}
			}
			
			$memberOrGuest->save();
			
		}
		
	}

	/*
	 *  Custom Getters
	 */
	public function __get( $key )
	{
		if ( $this->member_id )
		{
			switch ( $key )
			{
				/* Get collab member title if applicable */
				case 'member_title':
				
					if ( $collab = \IPS\collab\Application::affectiveCollab() )
					{
						if ( $membership = $collab->getMembership( $this ) )
						{
							return $membership->title();
						}
						else
						{
							return $collab->guestTitle();
						}
					}
					break;
				
				/* Get collab related posts if applicable */ 			
				case 'member_posts':
				
					if ( $collab = \IPS\collab\Application::affectiveCollab() )
					{
						if ( $membership = $collab->getMembership( $this ) )
						{
							return $membership->posts;
						}
						else
						{
							return $collab->guest( $this )->posts;
						}
					}
					break;
			}
		}
		
		return parent::__get( $key );
	}
	
	/*
	 *  Custom Setters
	 */
	public function __set( $key, $value )
	{
		if ( $this->member_id )
		{
			if ( in_array( $key, array( 'member_posts', 'member_last_post' ) ) )
			{
				if ( $collab = \IPS\collab\Application::affectiveCollab() )
				{
					$account = $collab->getMembership( $this ) ?: $collab->guest( $this );
				}

				if ( $account )
				{
					switch ( $key )
					{	
						case 'member_posts':
						
							$_diff = $account->posts - $value;
							$account->posts = $value;
							$account->save();
							if ( ! $collab->container()->bitoptions[ 'increase_mainposts' ] )
							{
								return $value;
							}
							$value = $this->_data[ 'member_posts' ] - $_diff;
							break;
							
						case 'member_last_post':
						
							$account->lastpost = $value;
							$account->save();
							if ( ! $collab->container()->bitoptions[ 'increase_mainposts' ] )
							{
								return $value;
							}
							break;
					}
				}
			}
		}
		
		return parent::__set( $key, $value );
	}
}