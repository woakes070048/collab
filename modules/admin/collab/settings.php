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
		
		\IPS\Output::i()->sidebar[ 'actions' ][ 'flushCounts' ] = array
		(
			'icon'	=> 'bar-chart',
			'link'	=> \IPS\Http\Url::internal( 'app=collab&module=collab&controller=settings&do=flushCounts' )->csrf(),
			'title'	=> 'collab_flush_counts',
			'data' => array( 'confirm' => '', 'confirmMessage' => \IPS\Member::loggedIn()->language()->addToStack( 'collab_flush_counts_confirm' ) ),
		);
		
		$form = new \IPS\Helpers\Form;

		$form->addHeader( 'settings' );

		$form->add( new \IPS\Helpers\Form\Translatable( 'collab_app_title', NULL, TRUE, array( 'app' => 'collab', 'key' => '__app_collab', 'placeholder' => \IPS\Member::loggedIn()->language()->get( '__app_collab' ) ) ) );
		$form->add( new \IPS\Helpers\Form\Translatable( 'collab_app_collab_singular', NULL, TRUE, array( 'app' => 'collab', 'key' => 'collab_cat__collab_singular', 'placeholder' => \IPS\Member::loggedIn()->language()->get( 'collab_cat__collab_singular' ) ) ) );
		$form->add( new \IPS\Helpers\Form\Translatable( 'collab_app_collabs_plural', NULL, TRUE, array( 'app' => 'collab', 'key' => 'collab_cat__collabs_plural', 'placeholder' => \IPS\Member::loggedIn()->language()->get( 'collab_cat__collabs_plural' ) ) ) );
		
		if ( $values = $form->values() )
		{			
			if ( $values[ 'collab_app_title' ] )
			{
				$this->langSave( array( '__app_collab', 'module__collab_collab', 'menu__collab_collab', 'collab_collab_pl' ), $values[ 'collab_app_title' ] );		
			}
			
			if ( $values[ 'collab_app_collab_singular' ] )
			{
				$this->langSave( array( 'collab_cat__collab_singular' ), $values[ 'collab_app_collab_singular' ] );
			}
			
			if ( $values[ 'collab_app_collabs_plural' ] )
			{
				$this->langSave( array( 'collab_cat__collabs_plural' ), $values[ 'collab_app_collabs_plural' ] );
			}
			
			\IPS\Session::i()->log( 'collab_acplog_settings' );
			\IPS\Output::i()->redirect( $this->url, 'collab_settings_saved' );
		}
		
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'settings' );
		\IPS\Output::i()->output = $form;
	}
	
	/**
	 * Flush cached collab count data
	 */
	protected function flushCounts()
	{
		\IPS\Session::i()->csrfCheck();
		
		\IPS\Db::i()->update( 'collab_collabs', array( 'data' => NULL ) );
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=collab&module=collab&controller=settings' ), 'collab_flush_counts_complete' );
	}
	
}