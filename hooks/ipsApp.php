//<?php

class collab_hook_ipsApp extends _HOOK_CLASS_
{

	public function execute()
	{
		$app = \IPS\Application::load( 'collab' );
		$update = $app->url( 'update' )->request()->get();
		parent::execute();
	}

}