//<?php

class collab_hook_ipsCmsCategories extends _HOOK_CLASS_
{

	/**
	 * Clone
	 */
	public function __clone()
	{
		parent::__clone();
		$this->setFullPath();
	}
	
}