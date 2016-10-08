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
				if ( $category->can( 'view' ) and $configuration = $category->_configuration and isset( $configuration[ 'show_forum_index' ] ) and $configuration[ 'show_forum_index' ] )
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
		 * Special Cases
		 */
		if ( \IPS\collab\Application::affectiveCollab() )
		{
			/* Look for theme select element and modify it to use only themes that the user has access to */
			foreach( $form->elements[ 'forum_settings' ] as &$formElement )
			{
				if ( $formElement instanceof \IPS\Helpers\Form\FormAbstract and $formElement->name == 'forum_skin_id' )
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
			
			/* Allow forums to be saved without a category as a parent */
			if ( isset( $form->elements[ 'forum_settings' ][ 'forum_type' ] ) and isset( $form->elements[ 'forum_settings' ][ 'forum_parent_id' ] ) )
			{
				$forum_type = $form->elements[ 'forum_settings' ][ 'forum_type' ];
				$forum_parent = $form->elements[ 'forum_settings' ][ 'forum_parent_id' ];
				
				/* Clear any potential "category required" error */
				if ( $forum_type->value == 'normal' and $forum_parent->value == 0 )
				{
					$forum_parent->error = NULL;
				}
			}
		}
	}
	
	/**
	 * [Node] Format form values from add/edit form for save
	 *
	 * @param	array	$values	Values from the form
	 * @return	array
	 */
	public function formatFormValues( $values )
	{
		if ( \IPS\collab\Application::affectiveCollab() )
		{
			/* Core code in IPS 4.1.15+ will change forums without a parent to a category if we don't do this */
			if ( isset( $values[ 'forum_parent_id' ] ) and $values[ 'forum_parent_id' ] === 0 and $values[ 'forum_type' ] == 'normal' )
			{
				$values[ 'forum_parent_id' ] = -1;
			}
		}
		
		return parent::formatFormValues( $values );
	}

}