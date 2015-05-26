<?php
/**
 * @brief		Rules extension: Collaboration
 * @package		Rules for IPS Social Suite
 * @since		30 Mar 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\collab\extensions\rules\Definitions;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	Rules definitions extension: Collaboration
 */
class _Collaboration
{

	/**
	 * @brief	The default option group title to list events, conditions, and actions from this class
	 */
	public $defaultGroup = 'Collaboration App';

	/**
	 * Triggerable Events
	 *
	 * Define the events that can be triggered by your application
	 *
	 * @return 	array		Array of event definitions
	 */
	public function events()
	{
		$events = array
		(
			'member_invited' => array
			( 
				'arguments' => array
				( 
					'member' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
					'sponsor' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member', 'nullable' => TRUE ),
					'collab' 	=> array( 'argtype' => 'object', 'class' => '\IPS\collab\Collab' ),
					'membership'	=> array( 'argtype' => 'object', 'class' => '\IPS\collab\Collab\Membership' ),
				),		
			),
			'member_pending' => array
			( 
				'arguments' => array
				( 
					'member' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
					'collab' 	=> array( 'argtype' => 'object', 'class' => '\IPS\collab\Collab' ),
					'membership'	=> array( 'argtype' => 'object', 'class' => '\IPS\collab\Collab\Membership' ),
				),		
			),
			'member_joined' => array
			( 
				'arguments' => array
				( 
					'member' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
					'collab' 	=> array( 'argtype' => 'object', 'class' => '\IPS\collab\Collab' ),
					'membership'	=> array( 'argtype' => 'object', 'class' => '\IPS\collab\Collab\Membership' ),
				),		
			),
			'member_banned' => array
			( 
				'arguments' => array
				( 
					'member' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
					'collab' 	=> array( 'argtype' => 'object', 'class' => '\IPS\collab\Collab' ),
					'membership'	=> array( 'argtype' => 'object', 'class' => '\IPS\collab\Collab\Membership' ),
				),		
			),
			'member_removed' => array
			( 
				'arguments' => array
				( 
					'member' 	=> array( 'argtype' => 'object', 'class' => '\IPS\Member' ),
					'collab' 	=> array( 'argtype' => 'object', 'class' => '\IPS\collab\Collab' ),
					'membership'	=> array( 'argtype' => 'object', 'class' => '\IPS\collab\Collab\Membership' ),
				),		
			),
		);
		
		return $events;
	}
	
	/**
	 * Conditional Operations
	 *
	 * You can define your own conditional operations which can be
	 * added to rules as conditions.
	 *
	 * @return 	array		Array of conditions definitions
	 */
	public function conditions()
	{
		$conditions = array
		(

		);
		
		return $conditions;
	}

	/**
	 * Triggerable Actions
	 *
	 * @return 	array		Array of action definitions
	 */
	public function actions()
	{
		$actions = array
		(

		);
		
		return $actions;
	}
	
}