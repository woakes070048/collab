<?php


namespace IPS\collab\modules\admin\collab;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * settings
 */
class _settings extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'collab_settings_manage' );
		parent::execute();
	}
	
	protected function langSave( $keys, $lang )
	{
		if ( !is_array( $keys ) )
		{
			$keys = (array) $keys;
		}
		
		foreach ( $keys as $key )
		{
			\IPS\Lang::saveCustom( 'collab', $key, $lang );
		}
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'collab_settings_manage' );
		
		$form = new \IPS\Helpers\Form;

		$form->addHeader( 'settings' );

		$form->add( new \IPS\Helpers\Form\Translatable( 'app_title', NULL, TRUE, array( 'app' => 'collab', 'key' => '__app_collab', 'placeholder' => \IPS\Member::loggedIn()->language()->get( '__app_collab' ) ) ) );
		
		if ( $values = $form->values() )
		{
			if ( $values[ 'app_title' ] )
			{
				$this->langSave( array( '__app_collab', 'module__collab_collab', 'menu__collab_collab', 'collab_collab_pl' ), $values[ 'app_title' ] );		
				\IPS\Session::i()->log( 'collab_acplog_settings' );
			}
		}
		
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('settings');
		\IPS\Output::i()->output = $form;
	}
	
}