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
		}
		return $forum;
	}

}