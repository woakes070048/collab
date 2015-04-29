<?php


namespace IPS\collab\modules\admin\collab;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Social Groups Importer
 */
class _socialgroups extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'collab_settings_manage' );
		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'Social Groups Importer' );
		
		$db = \IPS\Db::i();
		
		if ( $db->checkForTable( 'social_groups' ) )
		{
			$import_tables = array
			( 
				'social_groups', 
				'social_groups_cat', 
				'social_group_members',
				'social_groups_invites', 
				'social_groups_news',
				'social_groups_pages',
			);
			
			foreach ( $import_tables as $table )
			{
				if ( $db->checkForTable( $table ) )
				{
					if ( ! $db->checkForColumn( $table, 'imported_id' ) )
					{
						$db->addColumn( $table, array(
							'name'			=> 'imported_id',
							'type'			=> 'INT',
							'length'		=> 1,
							'null'			=> FALSE,
							'default'		=> 0,
						) );
					}
				}
			}

			$group_count = $db->select( 'COUNT(*)', 'social_groups', array( 'imported_id=0' ) )->first();
			$group_cat_count = $db->select( 'COUNT(*)', 'social_groups_cat', array( 'imported_id=0' ) )->first();
			
			if ( $group_count or $group_cat_count )
			{
				\IPS\Output::i()->output .= "
					<div class='ipsMessage ipsMessage_info'>
						Groups ready to import: <strong>{$group_count}</strong><br>
						Categories ready to import: <strong>{$group_cat_count}</strong>
					</div>
				";
				
				\IPS\Output::i()->output .= "<a href='" . $this->url->setQueryString( array( 'do' => 'socialGroupsWizard' ) ) . "' class='ipsButton ipsButton_positive ipsButton_large'>Begin Import</a>";
			}
			else
			{
				\IPS\Output::i()->output .= "
					<div class='ipsMessage ipsMessage_success'>
						Congratulations. All social groups have already been imported!
					</div>
				";
			}
		}
		else
		{
			\IPS\Output::i()->output .= "
				<div class='ipsMessage ipsMessage_error'>
					Import not available. Social groups not detected.
				</div>
			";
		}
	}
	
	/**
	 * Social Groups Import Wizard
	 */
	protected function socialGroupsWizard()
	{
		$db = \IPS\Db::i();
		
		if ( ! $db->checkForTable( 'social_groups' ) )
		{
			\IPS\Output::i()->error( 'collab_migrate_missing_data', '2CM10/A', 403 );
		}
		
		$this->socialGroupsImport();
		
	}
	
	protected function socialGroupsImport()
	{
		$import_url = \IPS\Http\Url::internal( "app=collab&module=collab&controller=socialgroups&do=socialGroupsImport" );
		
		$importer = new \IPS\Helpers\MultipleRedirect( 
				
			$import_url,
			
			function( $data )
			{
				/* Initialization */
				if ( empty ( $data ) )
				{
					$data = array
					(
						'step' 		=> 'categories',
						'progress'	=> 0,
						'counts'	=> array
						(
							'social_groups'		=> \IPS\Db::i()->select( 'COUNT(*)', 'social_groups', 		array( 'imported_id=0' ) )->first(),
							'social_groups_cat'	=> \IPS\Db::i()->select( 'COUNT(*)', 'social_groups_cat', 	array( 'imported_id=0 AND cat_parent=0' ) )->first(),
							'social_groups_news'	=> \IPS\Db::i()->select( 'COUNT(*)', 'social_groups_news', 	array( 'imported_id=0' ) )->first(),
							'social_groups_pages'	=> \IPS\Db::i()->select( 'COUNT(*)', 'social_groups_pages', 	array( 'imported_id=0' ) )->first(),
						),
						'collabs'	=> array(),
					);
					
					$data[ 'counts' ][ 'total' ] = 
						$data[ 'counts' ][ 'social_groups' ] +
						$data[ 'counts' ][ 'social_groups_cat' ] +
						//$data[ 'counts' ][ 'social_groups_news' ] +
						0;
						
					if ( $data[ 'counts' ][ 'total' ] == 0 )
					{
						return NULL;
					}
					
					$message = "Migrating Social Groups Categories";
					$progress = 0;
					return array( $data, $message, $progress );
				}
				
				switch ( $data[ 'step' ] )
				{
					case 'categories':
					
						$categories 	= \IPS\Db::i()->select( '*', 'social_groups_cat', array( 'imported_id=0 AND cat_parent=0' ) );
						$nodeClass	= '\IPS\forums\Forum';
						
						/**
						 * Make sure forums are provisioned for collab usage
						 */
						\IPS\collab\Application::provisionNode( $nodeClass );
						
						/* Recommended permissions */
						$perms = array
						(
							'app' 		=> 'collab',
							'perm_type' 	=> 'forums_forum',
						);
						
						foreach ( $nodeClass::$permissionMap as $k => $v )
						{
							switch ( $k )
							{
								case 'view':
								case 'read':
									$perms["perm_{$v}"] = '*';
									break;
									
								case 'add':
								case 'reply':
								case 'review':
								case 'upload':
								case 'download':
								default:
									$perms["perm_{$v}"] = implode( ',', array_keys( \IPS\Member\Group::groups( TRUE, FALSE ) ) );
									break;
							}
						}
			
						foreach ( $categories as $category )
						{
							$this->_createCategory( $category, $perms );
							$data[ 'progress' ]++;
						}
						
						$data[ 'step' ] = 'groups';
						$message 	= "Migrating Social Groups to Collaborations (" . $data[ 'counts' ][ 'social_groups' ] . " total)";
						$progress 	= $data[ 'progress' ] / $data[ 'counts' ][ 'total' ];
						break;
						
					case 'groups':
					
						$groups = \IPS\Db::i()->select( '*', 'social_groups', array( 'imported_id=0' ), NULL, 5 );
						$join_map = array
						(
							0 => 3,
							1 => 2,
							2 => 1,
							3 => 1
						);
						
						foreach ( $groups as $group )
						{
							try
							{
								$category = \IPS\Db::i()->select( '*', 'social_groups_cat', array( 'cat_id=?', $group[ 'g_cat_id' ] ) )->first();
								
								/**
								 * Build Collab
								 */
								$collab 		= new \IPS\collab\Collab;
								$collab->category_id 	= $category[ 'imported_id' ];
								$collab->created_date 	= $group[ 'g_start' ];
								$collab->title 		= $group[ 'g_name' ];
								$collab->state		= $group[ 'g_locked' ] ? 'closed' : 'open';
								$collab->approved	= $group[ 'g_approved' ];
								$collab->owner_id	= $group[ 'g_founder' ];
								$collab->featured	= $group[ 'g_featured' ];
								$collab->description	= $group[ 'g_desc' ];
								$collab->title_seo 	= $group[ 'g_seo_name' ];
								$collab->join_mode 	= $join_map[ $group[ 'g_approval' ] ];
								
								/* Get Owner Name */
								try { $collab->owner_name = \IPS\Member::load( $collab->owner_id )->name; }
								catch ( \UnderflowException $e ) {}
								
								/* Save the collab, bypass creating a membership automatically, and secure an ID */
								$collab->save( TRUE );
								
								/**
								 * Import Collab Roles
								 */
								$ranks 		= \IPS\Db::i()->select( '*', 'social_groups_ranks', array( 'g_id=?', $group[ 'g_id' ] ) );
								$perms 		= array( 'invite' => array(), 'moderate' => array(), 'manage' => array() );
								$role_map	= array();
								
								try
								{
									$_perms = \IPS\Db::i()->select( '*', 'social_groups_perms', array( 'group_id=?', $group[ 'g_id' ] ) )->first();
									$perms = array
									(
										'invite' => explode( ',', $_perms[ 'invite' ] ),
										'moderate' => explode( ',', $_perms[ 'add_news' ] ),
										'manage' => explode( ',', $_perms[ 'manage' ] ),
									);
								}
								catch ( \UnderflowException $e ) {}
								
								$role_weight = 2;
								
								foreach ( $ranks as $rank )
								{
									/**
									 * Create roles for all ranks except for guest
									 */
									if ( $rank[ 'type' ] != 'guest' )
									{
										$role 			= new \IPS\collab\Collab\Role;
										$role->collab_id 	= $collab->collab_id;
										$role->name 		= $rank[ 'rank_name' ];
										$role->member_title	= $rank[ 'rank_name' ];
										$role->weight 		= $role_weight++;
										
										switch ( $rank[ 'type' ] )
										{
											case 'owner':
												
												$role->weight = 1;
												$role->owner_default = 1;
												break;
											
											case 'default':
											
												$role->weight = 99;
												$role->member_default = 1;
												break;
											
											default:
											
												$role->weight = $role_weight++;
										}
										
										$role_perms 	= array();
										$mod_perms 	= array();
										
										/* Invite Permission */
										if ( in_array( $rank[ 'rank_id' ], $perms[ 'invite' ] ) )
										{
											$role_perms[] = 'inviteMember';
										}
										
										/* Moderation Permission */
										if ( in_array( $rank[ 'rank_id' ], $perms[ 'moderate' ] ) )
										{
											$role_perms[] = 'moderateContent';
											$mod_perms = $collab->container()->_mod_perms;
										}
										
										/* Management Permission */
										if ( in_array( $rank[ 'rank_id' ], $perms[ 'manage' ] ) )
										{
											$role_perms[] = 'manageCollab';
											$role_perms[] = 'manageMembers';
											$role_perms[] = 'approveMember';
										}
									
										$role->perms		= implode( ',', $role_perms );
										$role->mod_perms	= \serialize( $mod_perms );
										
										$role->save();
										
										/* Map the new ID */
										$role_map[ $rank[ 'rank_id' ] ] = $role->id;										
									}
								}
								
								/**
								 * Add Memberships
								 */
								$members 	= \IPS\Db::i()->select( '*', 'social_group_members', array( 'g_id=? AND imported_id=0', $group[ 'g_id' ] ) );
								$invites 	= \IPS\Db::i()->select( '*', 'social_groups_invites', array( 'inv_group=? AND inv_status=0 AND imported_id=0', $group[ 'g_id' ] ) );
								
								foreach ( $members as $member )
								{
									$imported_id = -1;
									try
									{
										$_member = \IPS\Member::load( $member[ 'member_id' ] );
										
										$membership 		= $collab->getMembership( $_member ) ?: new \IPS\collab\Collab\Membership;
										$membership->collab_id 	= $collab->collab_id;
										$membership->member_id 	= $member[ 'member_id' ];
										$membership->joined	= $member[ 'join_date' ];
										$membership->roles	= $role_map[ $member[ 'm_rank' ] ];
										
										/**
										 * Work out membership status
										 */
										if ( $member[ 'is_banned' ] ) 		$membership->status = \IPS\collab\COLLAB_MEMBER_BANNED;
										else if ( ! $member[ 'is_approved' ] )	$membership->status = \IPS\collab\COLLAB_MEMBER_PENDING;
										else					$membership->status = \IPS\collab\COLLAB_MEMBER_ACTIVE;
										
										$membership->save();
									}
									catch( \OutOfRangeException $e ) { }
									
									\IPS\Db::i()->update( 'social_group_members', array( 'imported_id' => $imported_id ), array( 'member_id=? AND g_id=?', $member[ 'member_id' ], $group[ 'g_id' ] ) );
								}
								
								/**
								 * Add Invitational Memberships
								 */
								foreach ( $invites as $invite )
								{
									$imported_id = -1;
									try
									{
										$_member = \IPS\Member::load( $invite[ 'inv_reciever' ] );
										
										if ( $membership = $collab->getMembership( $_member ) )
										{
											$membership->sponsor_id = $invite[ 'inv_sender' ];
										}
										else
										{
											$membership 		= new \IPS\collab\Collab\Membership;
											$membership->collab_id 	= $collab->collab_id;
											$membership->member_id 	= $invite[ 'inv_reciever' ];
											$membership->sponsor_id = $invite[ 'inv_sender' ];
											$membership->status 	= \IPS\collab\COLLAB_MEMBER_INVITED;										
										}
									
										$membership->save();
										$imported_id = $membership->id;
									}
									catch( \OutOfRangeException $e ) { }
									
									\IPS\Db::i()->update( 'social_groups_invites', array( 'imported_id' => $imported_id ), array( 'inv_id=?', $invite[ 'inv_id' ] ) );
								}
								
								/**
								 * Assign forums and re-apply permissions based on group privacy level
								 * Stock permissions should match the category, collab permissions should match the privacy level
								 */
								$forums 	= \IPS\Db::i()->select( '*', 'social_groups_forums', array( 'g_id=?', $group[ 'g_id' ] ) );
								
								/* Permissions Matrix */
								$collab_perms = array
								(
									'app' 		=> 'forums',
									'perm_type' 	=> 'collab_forum',
								);
								
								foreach ( \IPS\forums\Forum::$permissionMap as $k => $v )
								{
									switch ( $k )
									{
										case 'view':
										case 'read':
										
											/**
											 * For public social groups, give everybody read permission
											 */
											if ( $group[ 'g_privacy' ] == 0 )
											{
												$collab_perms["perm_{$v}"] = '*';
											}
											/**
											 * For private ones, only give group members read permission
											 */
											else
											{
												$collab_perms["perm_{$v}"] = implode( ',', array_merge( array( 0 ), array_keys( $collab->roles() ) ) );
											}
											break;
											
										case 'add':
										case 'reply':
										case 'review':
										case 'upload':
										case 'download':
										default:
										
											$collab_perms["perm_{$v}"] = implode( ',', array_merge( array( 0 ), array_keys( $collab->roles() ) ) );
											break;
									}
								}
								
								$saved_forums = array();
								
								foreach ( $forums as $_forum )
								{
									try
									{
										$forum = \IPS\forums\Forum::load( $_forum[ 'f_id' ] );
										$forum->collab_id = $collab->collab_id;
										$forum->password = NULL;
										
										$collab_perms[ 'perm_type_id' ] = $forum->_id;
										
										/* Set the collab permissions */
										\IPS\Db::i()->delete( 'core_permission_index', array( 'app=? AND perm_type=? AND perm_type_id=?', $collab_perms[ 'app' ], $collab_perms[ 'perm_type' ], $collab_perms[ 'perm_type_id' ] ) );
										\IPS\Db::i()->insert( 'core_permission_index', $collab_perms );
										
										/**
										 * Clear forum permissions and allow GC to re-create them automatically
										 * based on category defaults when the permissions are re-requested.
										 */
										$forum->clearPermissions();
										$forum->permissions();
										$forum->save();
										
										$saved_forums[] = $forum;
									}
									catch ( \Exception $e ) { }
								}
								
								/**
								 * Ensure root group forums have a root parent id
								 */
								foreach( $saved_forums as $_forum )
								{
									if ( ! $_forum->parent() or $_forum->parent()->collab_id != $collab->collab_id )
									{
										$parentColumn = $_forum::$databaseColumnParent;
										$_forum->$parentColumn = $_forum::$databaseColumnParentRootValue;
										$_forum->save();
									}
								}
								
								/**
								 * Recount content for group members
								 */
								$data[ 'collabs' ][] = $collab->collab_id;
								
								\IPS\Db::i()->update( 'social_groups', array( 'imported_id' => $collab->collab_id ), array( 'g_id=?', $group[ 'g_id' ] ) );
								$data[ 'progress' ]++;
								
							}
							catch ( \UnderflowException $e )
							{
								\IPS\Db::i()->update( 'social_groups', array( 'imported_id' => -1 ), array( 'g_id=?', $group[ 'g_id' ] ) );
								$data[ 'progress' ]++;
							}
						}
						
						if ( ( $groups_left = \IPS\Db::i()->select( 'COUNT(*)', 'social_groups', array( 'imported_id=0' ) )->first() ) == 0 )
						{							
							$data[ 'step' ] = 'news';
							$message	= "Updating News Topics (" . $data[ 'counts' ][ 'social_groups_news' ] . " total)";
						}
						else
						{
							$message = "Migrating Social Groups to Collaborations (" . ( $data[ 'counts' ][ 'social_groups' ] - $groups_left ) . " out of " . $data[ 'counts' ][ 'social_groups' ] . " complete)";
						}
						
						$progress	= $data[ 'progress' ] / $data[ 'counts' ][ 'total' ];
						break;
						
					case 'news':
					
						$news_topics = \IPS\Db::i()->select( '*', 'social_groups_news', array( 'imported_id=0' ), NULL, 100 );
						
						foreach ( $news_topics as $news )
						{
							try
							{
								$topic = \IPS\forums\Topic::load( $news[ 't_id' ] );
								$tags = array_merge( (array) $topic->tags(), array( 'news' ) );
								$topic->setTags( array_unique( $tags ) );
								$topic->pinned = 1;
								$topic->save();
								
								\IPS\Db::i()->update( 'social_groups_news', array( 'imported_id' => $topic->tid ), array( 'news_id=?', $news[ 'news_id' ] ) );
								//$data[ 'progress' ]++;
							}
							catch ( \OutOfRangeException $e )
							{
								\IPS\Db::i()->update( 'social_groups_news', array( 'imported_id' => -1 ), array( 'news_id=?', $news[ 'news_id' ] ) );
								//$data[ 'progress' ]++;
							}
						}
					
						if ( ( $news_left = \IPS\Db::i()->select( 'COUNT(*)', 'social_groups_news', array( 'imported_id=0' ) )->first() ) == 0 )
						{
							$data[ 'step' ] = 'complete';
							$message	= "Migration Complete!";
						}
						else
						{
							$message = "Updating News Topics (" . ( $data[ 'counts' ][ 'social_groups_news' ] - $groups_left ) . " out of " . $data[ 'counts' ][ 'social_groups_news' ] . " complete)";;
						}
						
						$progress	= $data[ 'progress' ] / $data[ 'counts' ][ 'total' ];
						break;
					
					case 'complete':
					default:
					
						/* Steps Complete */
						\IPS\Task::queue( 'collab', 'recountCollabContent', $data[ 'collabs' ] );
						sleep( 2 );
						return NULL;
				}
				
				return array( $data, $message, (int) ( 100 * $progress ) );					
			},
			
			/* Processing Complete */ 
			function() 
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=collab&module=collab&controller=categories" ), 'Social Groups Import Complete.' );
			}
		);
		
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'Social Groups Importer' );
		\IPS\Output::i()->output = $importer;
		
	}
	
	protected function _createCategory( $category, $perms, $parent_id=0 )
	{
		// Create Category
		$nid 	 = md5( 'IPS\forums\Forum' );
		$node_id = 'node_' . $nid;
		
		$options = array
		(
			$node_id => array
			(
				'enabled' 	=> 1,
				'maxnodes' 	=> 0,
				'enable_add'	=> 1,
				'enable_edit'	=> 1,
				'enable_delete'	=> 1,
				'enable_reorder'=> 1,
			),
		);
		
		$collab_category 		= new \IPS\collab\Category;
		$collab_category->weight 	= $category[ 'cat_position' ];
		$collab_category->name_seo	= $category[ 'cat_seo_name' ];
		$collab_category->parent_id 	= $parent_id;
		$collab_category->_options	= $options;
		$collab_category->collabs_enable = ! $category[ 'cat_locked' ];
		
		/**
		 * Build Moderator Permissions
		 */
		$modoptions 	= \IPS\collab\Application::modOptions();
		$modperms	= array();
		
		if ( isset( $modoptions[ 'IPS\forums\Topic' ] ) and $ext = $modoptions[ 'IPS\forums\Topic' ][ 'ext' ] )
		{
			$toggles = array( 'view_future' => array(), 'future_publish' => array(), 'pin' => array(), 'unpin' => array(), 'feature' => array(), 'unfeature' => array(), 'edit' => array(), 'hide' => array(), 'unhide' => array(), 'view_hidden' => array(), 'move' => array(), 'lock' => array(), 'unlock' => array(), 'reply_to_locked' => array(), 'delete' => array(), 'split_merge' => array() );
			
			foreach ( $ext->getPermissions( $toggles ) as $name => $data )
			{
				$type = is_array( $data ) ? $data[0] : $data;
				switch ( $type )
				{
					case 'YesNo':
						$modperms[ $name ] = TRUE;
						break;
						
					case 'Number':
						$modperms[ $name ] = -1;
						break;
				}			
			}
		}
		
		$collab_category->_mod_perms = $modperms;
		
		$collab_category->bitoptions[ 'allow_comments' ] 		= TRUE;
		$collab_category->bitoptions[ 'allow_ratings' ] 		= TRUE;
		$collab_category->bitoptions[ 'allow_reviews' ] 		= TRUE;
		$collab_category->bitoptions[ 'increase_mainposts' ] 		= TRUE;
		$collab_category->bitoptions[ 'enable_model' ]			= FALSE;
		$collab_category->bitoptions[ 'require_model' ] 		= FALSE;
		$collab_category->bitoptions[ 'multiple_model' ] 		= FALSE;
		
		/**
		 * Save the category... get an ID.
		 */
		$collab_category->save();
		
		/* Set default category permissions */
		\IPS\Db::i()->insert( 'core_permission_index', array(
			'app'			=> 'collab',
			'perm_type'		=> 'collab_category',
			'perm_type_id'		=> $collab_category->id,
			'perm_view'		=> '*',
			'perm_2'		=> '*'
		) );
		
		/* Set forums permissions for this category */
		$perms[ 'perm_type_id' ] = $collab_category->id;
		$collab_category->setNodePermissions( $perms, '\IPS\forums\Forum' );
		
		/* Update the social groups category with the imported id */
		\IPS\Db::i()->update( 'social_groups_cat', array( 'imported_id' => $collab_category->id ), array( 'cat_id=?', $category[ 'cat_id' ] ) );
		
		/**
		 * Save Language Strings ( Title, Description, etc )
		 */
		\IPS\Lang::saveCustom( 'collab', "collab_category_{$collab_category->id}", $category[ 'cat_name' ] );
		\IPS\Lang::saveCustom( 'collab', "collab_category_{$collab_category->id}_desc", $category[ 'cat_desc' ] );
		\IPS\Lang::saveCustom( 'collab', "collab_cat_{$collab_category->id}_collab_singular", \IPS\Lang::load( \IPS\Lang::defaultLanguage() )->get( 'collab_cat__collab_singular' ) );
		\IPS\Lang::saveCustom( 'collab', "collab_cat_{$collab_category->id}_collabs_plural", \IPS\Lang::load( \IPS\Lang::defaultLanguage() )->get( 'collab_cat__collab_plural' ) );
		
		/**
		 * Build Sub-categories
		 */
		foreach ( \IPS\Db::i()->select( '*', 'social_groups_cat', array( 'imported_id=0 AND cat_parent=?', $category[ 'cat_id' ] ) ) as $_category )
		{
			$this->_createCategory( $_category, $perms, $collab_category->id );
		}

	}
	
}