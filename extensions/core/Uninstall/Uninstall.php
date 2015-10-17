<?php
/**
 * @brief		Uninstall callback
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Social Suite
 * @subpackage	
 * @since		28 Jan 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\collab\extensions\core\Uninstall;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Uninstall callback
 */
class _Uninstall
{
	/**
	 * Code to execute before the application has been uninstalled
	 *
	 * @param	string	$application	Application directory
	 * @return	array
	 */
	public function preUninstall( $application )
	{
		if ( $application === 'collab' )
		{
			$uninstall_url = \IPS\Request::i()->url();
			
			if ( ! isset( \IPS\Request::i()->options ) )
			{
				/**
				 * Form To Get Uninstall Options 
				 */
				$form_id	= 'collab_uninstall';
				$collabOptions	= \IPS\collab\Application::collabOptions();
				$lang		= \IPS\Member::loggedIn()->language();
				$form 		= new \IPS\Helpers\Form( $form_id, 'uninstall' );
				$delete_all	= new \IPS\Helpers\Form\YesNo( 'collab_delete_all', FALSE );
				
				$form->add( $delete_all );
				
				foreach ( $collabOptions as $app => $nodes )
				{
					$nodeCount = count( $nodes );
					foreach ( $nodes as $node )
					{
						$nid 						= md5( $node[ 'node' ] );
						$node_lang 					= ( $nodeCount > 1 ? $lang->addToStack( $node[ 'content' ]::$title ) . ' ' . $lang->addToStack( $node[ 'node' ]::$nodeTitle ) : $lang->addToStack( '__app_' . $app ) );
						$lang->words[ 'keep_' . $nid ] 			= $lang->addToStack( 'collab_keep_node', FALSE, array( 'sprintf' => array( $node_lang ) ) );
						$lang->words[ 'keep_' . $nid . '_desc' ] 	= $lang->addToStack( 'collab_keep_node_desc', FALSE, array( 'sprintf' => array( $node_lang, $lang->addToStack( $node[ 'content' ]::$title ) ) ) );
						$delete_all->options[ 'togglesOff' ][] 		= $form_id . '_keep_' . $nid;
						
						$form->add( new \IPS\Helpers\Form\YesNo( 'keep_' . $nid, TRUE ) );
					}
				}
				
				if ( $values = $form->values() )
				{
					$options = array( 'uninstall' => TRUE );
					
					if ( ! $values[ 'collab_delete_all' ] )
					{
						foreach ( $collabOptions as $app => $nodes )
						{
							foreach ( $nodes as $node )
							{
								$nid = md5( $node[ 'node' ] );
								if ( $values[ 'keep_' . $nid ] )
								{
									$options[ 'keep_nodes' ][] = $nid;
								}
							}
						}
					}
					
					$uninstall_url = $uninstall_url->setQueryString( 'options', urlencode( json_encode( $options ) ) );
				}
				else
				{
					\IPS\Output::i()->output = $form;
					\IPS\Dispatcher::i()->finish();
				}
			}
			else
			{
				$options = ( array ) json_decode( urldecode( \IPS\Request::i()->options ) );
			}
			
			$steps_complete = FALSE;

			$uninstaller = new \IPS\Helpers\MultipleRedirect( 
				
				$uninstall_url,
				
				/* Collaboration Uninstall Processing */
				function( $data ) use ( $options )
				{
					/* Initialization */
					if ( empty ( $data ) )
					{
						$data = array
						(
							'starting_total' => \IPS\Db::i()->select( 'COUNT(*)', 'collab_collabs' )->first()
						);
						
						if ( $data[ 'starting_total' ] == 0 )
						{
							return NULL;
						}
					}
					
					
					/**
					 * Delete all collab content via delete() method in groups of 100
					 * this way, attached collab content can be handled properly
					 */
				
					$collabs 	= \IPS\Db::i()->select( '*', 'collab_collabs', NULL, NULL, 100 );
					$message	= \IPS\Member::loggedIn()->language()->addToStack( 'collab_deleting_collabs' );
					
					if ( count( $collabs ) )
					{
						foreach ( new \IPS\Patterns\ActiveRecordIterator( $collabs, 'IPS\collab\Collab' ) as $collab )
						{
							$collab->delete( $options );
						}
					}
					
					$remaining = \IPS\Db::i()->select( 'COUNT(*)', 'collab_collabs' )->first();
					$progress = intval( ( 100 / $data[ 'starting_total' ] ) * ( $data[ 'starting_total' ] - $remaining ) );
					
					if ( $remaining == 0 )
					{
						/* Steps Complete */
						return NULL;
					}
					
					return array( $data, $message, $progress );					
				},
				
				/* Processing Complete */ 
				function() use ( &$steps_complete )
				{
					$db = \IPS\Db::i();
					
					/* Drop collab_id columns for installed apps */
					foreach ( \IPS\collab\Application::collabOptions() as $app => $nodes )
					{
						foreach ( $nodes as $node )
						{
							$nodeClass = $node[ 'node' ];
							
							/* Drop any provisioned collab_id column ( mysql drops the index automatically ) */
							if ( $db->checkForColumn( $nodeClass::$databaseTable, $nodeClass::$databasePrefix . 'collab_id' ) )
							{
								$db->dropColumn( $nodeClass::$databaseTable, $nodeClass::$databasePrefix . 'collab_id' );
							}
						}
					}
					
					/* Drop imported status columns for social groups */
					if ( $db->checkForTable( 'social_groups' ) )
					{
						$import_tables = array
						( 
							'social_groups', 
							'social_groups_cat', 
							'social_group_members',
							'social_groups_invites', 
							'social_groups_news',
							'social_groups_pages',
						);
						
						foreach ( $import_tables as $table )
						{
							if ( $db->checkForTable( $table ) )
							{
								if ( $db->checkForColumn( $table, 'imported_id' ) )
								{
									$db->dropColumn( $table, 'imported_id' );
								}
							}
						}
					}

					$steps_complete = TRUE;
				}
			);
			
			if ( ! $steps_complete )
			{
				\IPS\Output::i()->output = $uninstaller;
				\IPS\Dispatcher::i()->finish();
			}

		}
	}

	/**
	 * Code to execute after the application has been uninstalled
	 *
	 * @param	string	$application	Application directory
	 * @return	array
	 */
	public function postUninstall( $application )
	{
	}
}