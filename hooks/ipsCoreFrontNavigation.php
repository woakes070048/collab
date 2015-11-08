//<?php

class collab_hook_ipsCoreFrontNavigation extends _HOOK_CLASS_
{

	/**
	 * Get roots
	 *
	 * @param	bool	$noStore	If true, will skip datastore and get from DB (used for ACP preview)
	 * @return	array
	 */
	public function roots( $noStore=FALSE )
	{
		$navigation = call_user_func_array( 'parent::roots', func_get_args() );
		
		if ( \IPS\collab\Application::affectiveCollab() )
		{
			/**
			 * Wrap with our collab navigation utility
			 */
			foreach( $navigation as &$menu )
			{
				$menu = new \IPS\collab\Util\FrontNavigation( $menu );
			}
		}
		
		return $navigation;
	}
	
	/**
	 * Get sub-bars
	 *
	 * @param	bool	$noStore	If true, will skip datastore and get from DB (used for ACP preview)
	 * @return	array
	 */
	public function subBars( $noStore=FALSE )
	{
		$navigation = call_user_func_array( 'parent::subBars', func_get_args() );
		
		if ( \IPS\collab\Application::affectiveCollab() )
		{
			/**
			 * Wrap with our collab navigation utility
			 */
			foreach( $navigation as &$menu )
			{
				if ( is_array( $menu ) )
				{
					foreach( $menu as &$item )
					{
						$item = new \IPS\collab\Util\FrontNavigation( $item );
					}
				}
			}
		}
		
		return $navigation;
	}	

}