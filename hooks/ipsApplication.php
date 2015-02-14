//<?php

class collab_hook_ipsApplication extends _HOOK_CLASS_
{

	/**
	 * Get all extensions
	 *
	 * @param	\IPS\Application|string		$app				The app key of the application which owns the extension
	 * @param	string						$extension			Extension Type
	 * @param	\IPS\Member|bool			$checkAccess		Check access permission for application against supplied member (or logged in member, if TRUE) before including extension
	 * @param	string|NULL					$firstApp			If specified, the application with this key will be returned first
	 * @param	string|NULL					$firstExtensionKey	If specified, the extension with this key will be returned first
	 * @param	bool						$construct			Should an object be returned? (If false, just the classname will be returned)
	 * @param	bool						$checkEnabled		Should we verify the application is also enabled?
	 * @return	array
	 */
	static public function allExtensions( $app, $extension, $checkAccess=true, $firstApp=NULL, $firstExtensionKey=NULL, $construct=true, $checkEnabled=true )
	{
		$_extensions = call_user_func_array( 'parent::allExtensions', func_get_args() );
		
		if ( \IPS\collab\Application::affectiveCollab() and $app == 'core' and $extension == 'FrontNavigation' )
		{
			foreach ( $_extensions as $id => &$nav )
			{
				$nav = new \IPS\collab\Util\FrontNavigation( $nav );
			}
		}
		
		return $_extensions;
	}

}