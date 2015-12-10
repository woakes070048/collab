//<?php

class collab_hook_modForumsForum extends _HOOK_CLASS_
{

	/**
	 * If there is only one forum, but it belongs to a collab,
	 * still return NULL so the forums controller doesn't clobber
	 * our breadcrumbs!
	 *
	 * ./applications/forums/modules/front/forums/forums.php ~ line 85
	 * 
	 * @return	\IPS\forums\Forum|NULL
	 */
	static public function theOnlyForum()
	{
		$forum = call_user_func_array( 'parent::theOnlyForum', func_get_args() );
		if ( $forum !== NULL )
		{
			if ( $forum->collab_id )
			{
				return NULL; 
			}
			
			/* Also check if we have any collab categories set up to show on the forum index */
			foreach( \IPS\collab\Category::roots() as $category )
			{
				if ( $category->can( 'view' ) and $configuration = $category->_configuration and $configuration[ 'show_forum_index' ] )
				{
					return NULL;
				}
			}
		}
		return $forum;
	}
	
	/**
	 * Forum Add/Edit Form
	 */
	public function form( &$form )
	{
		parent::form( $form );
		
		/**
		 * Limit theme selection in collabs to only those which the user can actually use
		 */
		if ( \IPS\collab\Application::affectiveCollab() )
		{
			foreach( $form->elements[ 'forum_settings' ] as &$formElement )
			{
				if ( $formElement->name == 'forum_skin_id' )
				{
					$themes = array( 0 => 'forum_skin_id_default' );
					
					/* Add visible themes */
					foreach ( \IPS\Theme::getThemesWithAccessPermission() as $theme )
					{
						$themes[ $theme->id ] = $theme->_title;
					}
					
					/* Add current theme if not visible to current user */
					if ( $this->id and ! isset( $themes[ $this->skin_id ] ) )
					{
						try
						{
							$currentTheme = \IPS\Theme::load( $this->skin_id );
							$themes[ $this->skin_id ] = $currentTheme->_title;
						}
						catch( \OutOfRangeException $e ) { }
					}

					$formElement = new \IPS\Helpers\Form\Select( 'forum_skin_id', $this->id ? $this->skin_id : 0, FALSE, array( 'options' => $themes ), NULL, NULL, NULL, 'forum_skin_id' );
				}
			}
		}
	}

}