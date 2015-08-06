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
			'collab_owned' => array
			(
				'callback' => array( $this, 'collabOwned' ),
				'arguments' => array
				(
					'entity' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Node or content',
								'class' => array( '\IPS\Node\Model', '\IPS\Content' ),						
							),
						),
						'required' => TRUE,
					),
				),
			),
			'join_mode' => array
			(
				'callback' => array( $this, 'joinMode' ),
				'arguments' => array
				(
					'mode' => array
					(
						'default' => 'manual',
						'argtypes' => array
						( 
							'array' => array( 'description' => 'an array of join modes' ), 
							'string' => array( 'description' => 'string value representing join mode' ), 
							'int' => array( 'description' => 'integer value representing join mode' ), 
						),
						'configuration' => array
						(
							'form' => function( $form, $values, $condition )
							{
								$collab_mode_options = array
								(
									\IPS\collab\COLLAB_JOIN_FREE 		=> 'collab_join_free',
									\IPS\collab\COLLAB_JOIN_APPROVE 	=> 'collab_join_approve',
									\IPS\collab\COLLAB_JOIN_INVITE		=> 'collab_join_invite_only',
									\IPS\collab\COLLAB_JOIN_DISABLED	=> 'collab_join_disabled',
								);
								
								$form->add( new \IPS\Helpers\Form\CheckboxSet( 'collab_rules_modes', $values[ 'collab_rules_modes' ], TRUE, array( 'options' => $collab_mode_options ), NULL, NULL, NULL, 'collab_rules_modes' ) );
								return array( 'collab_rules_modes' );
							},
							'getArg' => function( $values )
							{
								return $values[ 'collab_rules_modes' ];
							},
						),
						'required'	=> TRUE,
					),
					'collab' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Collaboration (\IPS\collab\Collab)',
								'class' => '\IPS\collab\Collab',						
							),						
						),
						'configuration' => \IPS\rules\Application::configPreset( 'item', 'collab_rules_select_collab', TRUE, array( 'class' => '\IPS\collab\Collab' ) ),
						'required'	=> TRUE,
					),
				),
			),
			'membership_status' => array
			(
				'callback' => array( $this, 'membershipStatus' ),
				'arguments' => array
				(
					'member' => array
					(
						'argtypes' => \IPS\rules\Application::argPreset( 'member' ),
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required' => TRUE,
					),
					'status' => array
					(
						'default' => 'manual',
						'argtypes' => array( 'string', 'array' ),
						'configuration' => array
						(
							'form' => function( $form, $values, $condition )
							{
								$collab_status_options = array
								(
									\IPS\collab\COLLAB_MEMBER_ACTIVE 	=> 'collab_status_active',
									\IPS\collab\COLLAB_MEMBER_PENDING 	=> 'collab_status_pending',
									\IPS\collab\COLLAB_MEMBER_INVITED	=> 'collab_status_invited',
									\IPS\collab\COLLAB_MEMBER_BANNED	=> 'collab_status_banned',
								);
								
								$form->add( new \IPS\Helpers\Form\CheckboxSet( 'collab_rules_statuses', $values[ 'collab_rules_statuses' ], TRUE, array( 'options' => $collab_status_options ), NULL, NULL, NULL, 'collab_statuses' ) );
								return array( 'collab_statuses' );
							},
							'getArg' => function( $values )
							{
								return $values[ 'collab_rules_statuses' ];
							},
						),
						'required' => TRUE,
					),
					'collab' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Collaboration (\IPS\collab\Collab)',
								'class' => '\IPS\collab\Collab',						
							),						
						),
						'configuration' => \IPS\rules\Application::configPreset( 'item', 'collab_rules_select_collab', TRUE, array( 'class' => '\IPS\collab\Collab' ) ),
						'required'	=> TRUE,
					),
				),
			),
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
			'set_membership_status' => array
			(
				'callback' => array( $this, 'setMembershipStatus' ),
				'arguments' => array
				(
					'collab' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Collaboration (\IPS\collab\Collab)',
								'class' => '\IPS\collab\Collab',						
							),						
						),
						'configuration' => \IPS\rules\Application::configPreset( 'item', 'collab_rules_select_collab', TRUE, array( 'class' => '\IPS\collab\Collab' ) ),
						'required'	=> TRUE,
					),
					'member' => array
					(
						'argtypes' => \IPS\rules\Application::argPreset( 'member' ),
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required' => TRUE,
					),
					'status' => array
					(
						'default' => 'manual',
						'argtypes' => array( 'string', 'array' ),
						'configuration' => array
						(
							'form' => function( $form, $values, $condition )
							{
								$collab_status_options = array
								(
									\IPS\collab\COLLAB_MEMBER_ACTIVE 	=> 'collab_status_active',
									\IPS\collab\COLLAB_MEMBER_PENDING 	=> 'collab_status_pending',
									\IPS\collab\COLLAB_MEMBER_INVITED	=> 'collab_status_invited',
									\IPS\collab\COLLAB_MEMBER_BANNED	=> 'collab_status_banned',
								);
								
								$form->add( new \IPS\Helpers\Form\Radio( 'collab_rules_set_status', $values[ 'collab_rules_set_status' ], TRUE, array( 'options' => $collab_status_options ), NULL, NULL, NULL, 'collab_statuses' ) );
								return array( 'collab_statuses' );
							},
							'getArg' => function( $values )
							{
								return $values[ 'collab_rules_set_status' ];
							},
						),
						'required' => TRUE,
					),
				),
			),
			'delete_membership' => array
			(
				'callback' => array( $this, 'deleteMembership' ),
				'arguments' => array
				(
					'member' => array
					(
						'argtypes' => \IPS\rules\Application::argPreset( 'member' ),
						'configuration'	=> \IPS\rules\Application::configPreset( 'member', 'rules_choose_member' ),
						'required' => TRUE,
					),
					'collab' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Collaboration (\IPS\collab\Collab)',
								'class' => '\IPS\collab\Collab',						
							),						
						),
						'required'	=> TRUE,
					),
				),			
			),
			'set_join_mode' => array
			(
				'callback' => array( $this, 'setJoinMode' ),
				'arguments' => array
				(
					'mode' => array
					(
						'default' => 'manual',
						'argtypes' => array
						( 
							'int' => array( 'description' => 'integer value representing join mode' ), 
						),
						'configuration' => array
						(
							'form' => function( $form, $values, $condition )
							{
								$collab_mode_options = array
								(
									\IPS\collab\COLLAB_JOIN_FREE 		=> 'collab_join_free',
									\IPS\collab\COLLAB_JOIN_APPROVE 	=> 'collab_join_approve',
									\IPS\collab\COLLAB_JOIN_INVITE		=> 'collab_join_invite_only',
									\IPS\collab\COLLAB_JOIN_DISABLED	=> 'collab_join_disabled',
								);
								
								$form->add( new \IPS\Helpers\Form\Radio( 'collab_rules_set_mode', $values[ 'collab_rules_set_mode' ], TRUE, array( 'options' => $collab_mode_options ), NULL, NULL, NULL, 'collab_rules_modes' ) );
								return array( 'collab_rules_modes' );
							},
							'getArg' => function( $values )
							{
								return $values[ 'collab_rules_set_mode' ];
							},
						),
						'required'	=> TRUE,
					),
					'collab' => array
					(
						'argtypes' => array
						(
							'object' => array
							(
								'description' => 'Collaboration (\IPS\collab\Collab)',
								'class' => '\IPS\collab\Collab',						
							),						
						),
						'required'	=> TRUE,
					),
				),			
			),
		);
		
		return $actions;
	}
	
	
	/** Event, Condition, Action Callbacks **/
	
	/**
	 * Check Membership Status
	 */
	public function membershipStatus( $member, $status, $collab )
	{
		if ( ! is_object( $collab ) )
		{
			throw new \InvalidArgumentException( 'Expecting a collab object. Given: ' . gettype( $collab ) );
		}
	
		if ( ! ( $collab instanceof \IPS\collab\Collab ) )
		{
			throw new \InvalidArgumentException( 'Invalid collaboration object: ' . get_class( $collab ) );
		}
		
		if ( ! ( $member instanceof \IPS\Member ) )
		{
			throw new \InvalidArgumentException( 'Invalid member' );
		}
		
		if ( $membership = $collab->getMembership( $member, TRUE ) )
		{
			return in_array( $membership->status, (array) $status );
		}
		
		return FALSE;
	}
	
	/**
	 * Check Entity For Collab Ownership
	 */
	public function collabOwned( $entity )
	{
		if ( ! ( $entity instanceof \IPS\Node\Model or $entity instanceof \IPS\Content ) )
		{
			throw new \UnexpectedValueException( 'Entity must be a node or content' );
		}
		
		return (bool) ( $entity instanceof \IPS\collab\Collab or \IPS\collab\Application::getCollab( $entity ) );
	}
	
	/**
	 * Check Collab Join Mode
	 */
	public function joinMode( $mode, $collab )
	{
		if ( ! is_object( $collab ) )
		{
			throw new \InvalidArgumentException( 'Expecting a collab object. Given: ' . gettype( $collab ) );
		}
	
		if ( ! ( $collab instanceof \IPS\collab\Collab ) )
		{
			throw new \InvalidArgumentException( 'Invalid collaboration object: ' . get_class( $collab ) );
		}
		
		return in_array( $collab->join_mode, (array) $mode );
	}
	
	/**
	 * Set Membership Status
	 */
	public function setMembershipStatus( $collab, $member, $status )
	{
		if ( ! is_object( $collab ) )
		{
			throw new \InvalidArgumentException( 'Expecting a collab object. Given: ' . gettype( $collab ) );
		}
	
		if ( ! ( $collab instanceof \IPS\collab\Collab ) )
		{
			throw new \InvalidArgumentException( 'Invalid collaboration object: ' . get_class( $collab ) );
		}
		
		if ( ! ( $member instanceof \IPS\Member ) or ! $member->member_id )
		{
			throw new \InvalidArgumentException( 'Invalid member' );
		}
		
		if ( $membership = $collab->getMembership( $member, TRUE ) )
		{
			if ( $membership->status !== $status )
			{
				$membership->status = $status;
				
				if ( $status === \IPS\collab\COLLAB_MEMBER_ACTIVE and ! $membership->joined )
				{
					$membership->joined = time();
				}
				
				$membership->save();
				return "membership status updated: " . $status;
			}
			else
			{
				return "membership already has specified status: " . $status;
			}
		}
		else
		{
			$membership = new \IPS\collab\Collab\Membership;
			$membership->member_id = $member->member_id;
			$membership->collab_id = $collab->collab_id;
			$membership->status = $status;
			
			if ( $status == \IPS\collab\COLLAB_MEMBER_ACTIVE )
			{
				$membership->joined = time();
			}
			
			$membership->save();
			
			return "membership status created: " . $status;
		}
		
	}
	
	/**
	 * Delete Membership
	 */
	public function deleteMembership( $member, $collab )
	{
		if ( ! is_object( $collab ) )
		{
			throw new \InvalidArgumentException( 'Expecting a collab object. Given: ' . gettype( $collab ) );
		}
	
		if ( ! ( $collab instanceof \IPS\collab\Collab ) )
		{
			throw new \InvalidArgumentException( 'Invalid collaboration object: ' . get_class( $collab ) );
		}
		
		if ( ! ( $member instanceof \IPS\Member ) or ! $member->member_id )
		{
			throw new \InvalidArgumentException( 'Invalid member' );
		}
		
		if ( $membership = $collab->getMembership( $member, TRUE ) )
		{
			$membership->delete();
			return "membership deleted";
		}
		
		return "no membership found";	
	}
	
	/**
	 * Set Collaboration Join Mode
	 */
	public function setJoinMode( $mode, $collab )
	{
		if ( ! is_object( $collab ) )
		{
			throw new \InvalidArgumentException( 'Expecting a collab object. Given: ' . gettype( $collab ) );
		}
	
		if ( ! ( $collab instanceof \IPS\collab\Collab ) )
		{
			throw new \InvalidArgumentException( 'Invalid collaboration object: ' . get_class( $collab ) );
		}
		
		$collab->join_mode = $mode;
		$collab->save();
		return "collab join mode updated";
	}
	
}