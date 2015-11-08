<?php


namespace IPS\collab\modules\admin\collab;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * categories
 */
class _categories extends \IPS\Node\Controller
{
	/**
	 * Node Class
	 */
	protected $nodeClass = '\IPS\collab\Category';
	
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'collab_categories_manage' );
		parent::execute();
	}
	
	/**
	 * Manage
	 *
	 * @return	void
	 */
	protected function manage()
	{
		parent::manage();
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( '__app_collab' ) . ' ' . \IPS\Member::loggedIn()->language()->addToStack( 'menu__collab_collab_categories' );
	}
	
	/**
	 * Manage Collab App
	 */
	protected function manageApp()
	{
		$category = \IPS\collab\Category::load( \IPS\Request::i()->cat );
	
		foreach ( \IPS\collab\Application::collabOptions() as $app => $nodes )
		{
			if ( $app == \IPS\Request::i()->mapp )
			{
				$form = new \IPS\Helpers\Form( 'collab_category_app_config' );
				$form->addHtml( "<h1>" . $category->_title . "</h1><hr class='ipsHr' />" );
				
				/* Node Options */
				foreach ( $nodes as $option )
				{
					$category->addNodeOption( $form, $option );
				}
				
				if ( $values = $form->values() )
				{
					$category->updateNodeSettings( $values );
					$category->save();
					\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=collab&module=collab&controller=categories" ), 'collab_category_updated' );
				}
				
				\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'collab_category_app_config' );
				\IPS\Output::i()->output .= $form;
				return;
			}
		}		
		
		\IPS\Output::i()->error( 'page_doesnt_exist', '2CC01/A', 404 );		
		
	}
	
}