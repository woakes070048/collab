//<?php

class collab_hook_ipsThemeDevTheme extends _HOOK_CLASS_
{

	/**
	 * Get a template
	 *
	 * @param	string	$group				Template Group
	 * @param	string	$app				Application key (NULL for current application)
	 * @param	string	$location		    Template Location (NULL for current template location)
	 * @return	\IPS\Theme\Template
	 */
	public function getTemplate( $group, $app=NULL, $location=NULL )
	{
		return call_user_func( 'parent::getTemplate', $group, $app, $this->getLocation( $location ) );
	}
	
	/**
	 * Get CSS
	 *
	 * @param	string		$file		Filename
	 * @param	string|null	$app		Application
	 * @param	string|null	$location	Location (e.g. 'admin', 'front')
	 * @return	array		URLs to CSS files
	 */
	public function css( $file, $app=NULL, $location=NULL )
	{
		return call_user_func( 'parent::css', $file, $app, $this->getLocation( $location ) );
	}

	/**
	 * Get JS
	 *
	 * @param	string		$file		Filename
	 * @param	string|null	$app		Application
	 * @param	string|null	$location	Location (e.g. 'admin', 'front')
	 * @return	array		URL to JS files
	 */
	public function js( $file, $app=NULL, $location=NULL )
	{
		return call_user_func( 'parent::js', $file, $app, $this->getLocation( $location ) );
	}

}