//<?php

class collab_hook_ipsDispatcherFront extends _HOOK_CLASS_
{
  
	/**
	 * @brief	Cached Menu
	 */
	protected $menu = NULL;
	
	/**
	 * @brief	Search Keywords
	 */
	public $searchKeywords = array();
	
	/**
	 * @brief	ACP Restrictions (for search keyword editing)
	 */
	public $moduleRestrictions = array();
	
	/**
	 * @brief	ACP Restriction for the current menu item (for search keyword editing)
	 */
	public $menuRestriction = NULL;

	/**
	 * Check ACP Permission
	 *
	 * @param	string					$key		Permission Key
	 * @param	\IPS\Application|null	$app		Application (NULL will default to current)
	 * @param	\IPS\Module|string|null	$module		Module (NULL will default to current)
	 * @return	void
	 */
	public function checkAcpPermission( $key, $app=NULL, $module=NULL )
	{
		
	}

	/**
	 * Base CSS
	 *
	 * @return	void
	 */
	static public function baseCss()
	{
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'global.css', 'collab', 'front' ) );
		return call_user_func_array( 'parent::baseCss', func_get_args() );
	}
	
	/**
	 * Finish
	 *
	 * @return	void
	 */
	public function finish()
	{		
		/**
		 * Breadcrumb Tweaks
		 * 
		 */
		if ( $collab = \IPS\collab\Application::affectiveCollab() )
		{
			/* Make sure the module breadcrumb points to this app */
			if ( isset( \IPS\Output::i()->breadcrumb[ 'module' ] ) )
			{
				\IPS\Output::i()->breadcrumb[ 'module' ] = array( \IPS\Http\Url::internal( 'app=collab&module=collab&controller=categories', 'front', 'collab_index' ), \IPS\Member::loggedIn()->language()->addToStack( '__app_collab' ) );
			}
			
			/* Make sure the last breadcrumb is a page title */
			$lastCrumb = end( \IPS\Output::i()->breadcrumb );
			if ( $lastCrumb[0] != NULL )
			{
				\IPS\Output::i()->breadcrumb[] = array( NULL, \IPS\Output::i()->title );
			}
		}
		
		 
		parent::finish();
	}	

}