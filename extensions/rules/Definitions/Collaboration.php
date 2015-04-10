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

		);
		
		return array(); // $events;
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
		
		return array(); // $conditions;
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
		
		return array(); // $actions;
	}
	
	/**
	 * Example Operation Callback
	 *
	 * Your operation callback will recieve all of the arguments defined in your
	 * action/condition definition. If an argument is not required, and not provided 
	 * by the user, then it will be NULL.
	 *
	 * Your operation callback will also recieve three additional arguments at the end of
	 * your regularly defined arguments.
	 *
	 * @extraArg1	array				$values		An array of the existing saved values from the configuration form
	 * @extraArg2	array				$arg_map	A keyed array of the arguments from the event
	 * @extraArg3	object	\IPS\rules\Action	$operation	The operation object (Action or Condition) which is invoking the callback
	 *			\IPS\rules\Condition
	 *
	 * @return	mixed			If this is a condition callback, you should return either TRUE or FALSE depending on if the condition has passed.
	 * 					If it is an action callback, you should return a short message to describe the result of the action for debug purposes.
	 *
	 * Note: Any value that you return from an operation callback is logged to the debug console when the rule is in debug mode. This way
	 * it is possible to see what is happening with each operation as it is being evaluated.
	 */
	public function operationCallback( $arg1, $values, $arg_map, $operation )
	{
		return 'action taken';
	}
	
}