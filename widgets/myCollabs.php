<?php
/**
 * @brief		latestCollabs Widget
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Social Suite
 * @subpackage	collab
 * @since		06 Apr 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\collab\widgets;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * latestCollabs Widget
 */
class _myCollabs extends \IPS\Widget
{
	/**
	 * @brief	Widget Key
	 */
	public $key = 'myCollabs';
	
	/**
	 * @brief	App
	 */
	public $app = 'collab';
		
	/**
	 * @brief	Plugin
	 */
	public $plugin = '';
	
	/**
	 * Initialise this widget
	 *
	 * @return void
	 */ 
	public function init()
	{		
		parent::init();
	}
	
	/**
	 * Specify widget configuration
	 *
	 * @param	null|\IPS\Helpers\Form	$form	Form object
	 * @return	null|\IPS\Helpers\Form
	 */
	public function configuration( &$form=null )
	{
 		if ( $form === null )
		{
			$form = new \IPS\Helpers\Form;
		}

		$form->add( new \IPS\Helpers\Form\Number( 'number_to_show', isset( $this->configuration[ 'number_to_show' ] ) ? $this->configuration[ 'number_to_show' ] : 5, TRUE ) );

		return $form;
 	} 
 	
 	/**
 	 * Ran before saving widget configuration
 	 *
 	 * @param	array	$values	Values from form
 	 * @return	array
 	 */
 	public function preConfig( $values )
 	{
 		return $values;
 	}

	/**
	 * Render a widget
	 *
	 * @return	string
	 */
	public function render()
	{
		$collabs = \IPS\Member::loggedIn()->collabs( \IPS\collab\COLLAB_MEMBER_ACTIVE );
		
		if ( $this->configuration[ 'number_to_show' ] > 0 )
		{
			$collabs = array_slice( $collabs, 0, $this->configuration[ 'number_to_show' ] );
		}
		
		if ( count( $collabs ) )
		{
			return $this->output( $collabs );
		}
		else
		{
			return '';
		}
	}
}