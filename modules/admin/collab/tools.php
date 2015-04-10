<?php


namespace IPS\collab\modules\admin\collab;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * tools
 */
class _tools extends \IPS\Dispatcher\Controller
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

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'collab_manage_tools' );
		
		\IPS\Output::i()->output .= "<h2>Social Groups Importer</h2>" . ( 
			\IPS\Db::i()->checkForTable( 'social_groups' ) ? 
			"<p><a href='" . \IPS\Http\Url::internal( "app=collab&module=collab&controller=socialgroups" ) . "' class='ipsButton ipsButton_positive ipsButton_large'><i class='fa fa-rocket'></i> Launch Importer</a></p>" :
			"<p>No social groups detected</p>" 
			);
		
	}

	
}