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
		$lang = \IPS\Member::loggedIn()->language();
	
		foreach ( \IPS\collab\Application::collabOptions() as $app => $nodes )
		{
			if ( $app == \IPS\Request::i()->mapp )
			{				
				if ( \IPS\Request::i()->nid )
				{					
					/* Configure a specific content type */
					foreach( $nodes as $option )
					{
						if ( \IPS\Request::i()->nid == md5( $option[ 'node' ] ) )
						{
							$steps = $category->contentConfigSteps( NULL, $option );
							\IPS\Output::i()->output = new \IPS\Helpers\Wizard( $steps, \IPS\Http\Url::internal( "app=collab&module=collab&controller=categories&do=manageApp" )->setQueryString( array( 'cat' => $category->_id, 'mapp' => $app, 'nid' => \IPS\Request::i()->nid ) ), TRUE, array() );
							return;
						}
					}
				}
				else
				{					
					if ( \IPS\Settings::i()->collab_category_longconfig )
					{
						$form = new \IPS\Helpers\Form( 'collab_category_app_config' );					
						$form->addHtml( "<h1>" . $category->_title . "</h1><hr class='ipsHr' />" );					
					}
					else
					{
						$form = new \IPS\Helpers\Form( 'collab_category_app_config', 'done' );					
						$form->addHtml( "<h1>" . $category->_title . "</h1><hr class='ipsHr' />" );
						$form->hiddenValues[ 'complete' ] = 1;					
					}
					
					foreach ( $nodes as $option )
					{
						if ( \IPS\Settings::i()->collab_category_longconfig )
						{
							/* Show all-in-one config */
							$steps = $category->contentConfigSteps( $form, $option );
							
							foreach( $steps as $step )
							{
								call_user_func( $step );
							}
						}
						else
						{
							/* Link to wizard config */
							$nid			= md5( $option[ 'node' ] );
							$nodeClass 		= $option[ 'node' ];
							$nodeTitle 		= ucwords( $lang->get( $nodeClass::$nodeTitle ) );
							$contentClass		= $option[ 'content' ];
							$contentTitle 		= $contentClass ? ucwords( $lang->get( $contentClass::$title ) ) : NULL;

							$wizardurl = \IPS\Http\Url::internal( "app=collab&module=collab&controller=categories&do=manageApp" )->setQueryString( array( 'cat' => $category->_id, 'mapp' => $app, 'nid' => $nid, '_new' => 1 ) );
							$form->addHtml( "<h2 class='ipsFieldRow_section'>{$contentTitle} {$nodeTitle} &nbsp;&nbsp;<i class='fa fa-caret-right'></i> <a href='{$wizardurl}' class='ipsButton_verySmall'><i class='fa fa-magic'></i> " . $lang->addToStack( 'collab_configureWizard' ) . "</a></h2>" );
						}
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
		}		
		
		\IPS\Output::i()->error( 'page_doesnt_exist', '2CC01/A', 404 );		
		
	}
	
}