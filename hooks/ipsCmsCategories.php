//<?php

class collab_hook_ipsCmsCategories extends _HOOK_CLASS_
{

	/**
	 * Clone
	 *
	 * @BUGFIX: Cloning a cms category does not generate a new full path for friendly url's
	 */
	public function __clone()
	{
		parent::__clone();
		$this->setFullPath();
	}
	
}