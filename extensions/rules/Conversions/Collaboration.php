<?php
/**
 * @brief		Rules conversions: Collaboration
 * @package		Rules for IPS Social Suite
 * @since		30 May 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\collab\extensions\rules\Conversions;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	Rules conversions extension: Collaboration
 */
class _Collaboration
{

	/**
	 * Global Arguments
	 *
	 * Let rules know about any global arguments that your app may have. Global arguments
	 * are made available to all rule configurations (conditions/actions), and can also
	 * be used as token replacements on rules forms.
	 *
	 * @return 	array		Array of global arguments
	 */
	public function globalArguments()
	{
		$globals = array
		(
			'active_collab' => array
			(
				'token' => 'collab',			
				'description' => 'the currently active collaboration',
				'argtype' => 'object',
				'nullable' => TRUE,
				'class' => '\IPS\collab\Collab',
				'getArg' => function()
				{
					return \IPS\collab\Application::affectiveCollab();
				},
			),
		);
		
		return $globals;
	}

	/**
	 * Conversion Map
	 *
	 * Let rules know how to convert objects into different types of arguments.
	 * For example, if an event provides an \IPS\Content object, a conversion map
	 * will tell rules how to derive another possible argument from it (such as the 
	 * content title).
	 *
	 * @return 	array		Conversion map
	 */
	public function conversionMap()
	{
		$map = array
		(
			'\IPS\Node\Model' => array
			(
				'Collaboration' => array
				(
					'token' => 'collab',
					'description' => 'The associated collaboration',
					'argtype' => 'object',
					'class' => '\IPS\collab\Collab',
					'nullable' => TRUE,
					'converter' => function( $object )
					{
						return \IPS\collab\Application::getCollab( $object );
					},
				),
			),
			'\IPS\Content' => array
			(
				'Collaboration' => array
				(
					'token' => 'collab',
					'description' => 'The associated collaboration',
					'argtype' => 'object',
					'class' => '\IPS\collab\Collab',
					'nullable' => TRUE,
					'converter' => function( $object )
					{
						if ( $object instanceof \IPS\collab\Collab )
						{
							return $object;
						}
						
						return \IPS\collab\Application::getCollab( $object );
					},
				),
			),
		);
		
		return $map;		
	}
	
}