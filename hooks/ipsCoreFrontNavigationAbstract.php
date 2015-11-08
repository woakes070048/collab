//<?php

abstract class collab_hook_ipsCoreFrontNavigationAbstract extends _HOOK_CLASS_
{

	/**
	 * Get sub items of an item
	 *
	 * @return	array
	 */
	public function subItems()
	{
		$navigation = call_user_func_array( 'parent::subItems', func_get_args() );
		
		if ( \IPS\collab\Application::affectiveCollab() )
		{
			/**
			 * Wrap with our collab navigation utility
			 */
			foreach( $navigation as &$menu )
			{
				$menu = new \IPS\collab\Util\FrontNavigation( $menu );
				$menu->isCollabSubitem = ( get_class( $this ) === 'IPS\collab\extensions\core\FrontNavigation\navigation' );
			}
		}
		
		return $navigation;
	}
	
}