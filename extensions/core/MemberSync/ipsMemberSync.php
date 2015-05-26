<?php
/**
 * @brief		Member Sync
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Social Suite
 * @subpackage	
 * @since		14 Jan 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\collab\extensions\core\MemberSync;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Member Sync
 */
class _ipsMemberSync
{
	/**
	 * Member account has been created
	 *
	 * @param	$member	\IPS\Member	New member account
	 * @return	void
	 */
	public function onCreateAccount( $member )
	{
	
	}
	
	/**
	 * Member has validated
	 *
	 * @param	\IPS\Member	$member		Member validated
	 * @return	void
	 */
	public function onValidate( $member )
	{
	
	}
	
	/**
	 * Member has logged on
	 *
	 * @param	\IPS\Member	$member		Member that logged in
	 * @param	\IPS\Http\Url	$redirectUrl	The URL to send the user back to
	 * @return	void
	 */
	public function onLogin( $member, $returnUrl )
	{
	
	}
	
	/**
	 * Member has logged out
	 *
	 * @param	\IPS\Member		$member			Member that logged out
	 * @param	\IPS\Http\Url	$redirectUrl	The URL to send the user back to
	 * @return	void
	 */
	public function onLogout( $member, $returnUrl )
	{
	
	}
	
	/**
	 * Member account has been updated
	 *
	 * @param	$member		\IPS\Member	Member updating profile
	 * @param	$changes	array		The changes
	 * @return	void
	 */
	public function onProfileUpdate( $member, $changes )
	{
	
	}
	
	/**
	 * Member is flagged as spammer
	 *
	 * @param	$member	\IPS\Member	The member
	 * @return	void
	 */
	public function onSetAsSpammer( $member )
	{

	}
	
	/**
	 * Member is unflagged as spammer
	 *
	 * @param	$member	\IPS\Member	The member
	 * @return	void
	 */
	public function onUnSetAsSpammer( $member )
	{
		
	}
	
	/**
	 * Member is merged with another member
	 *
	 * @param	\IPS\Member	$member		Member being kept
	 * @param	\IPS\Member	$goaway		Member being removed
	 * @return	void
	 */
	public function onMerge( $member, $member2 )
	{
		foreach ( $member2->collabMemberships() as $membership )
		{
			if ( $collab = $membership->collab() )
			{
				/** 
				 * Look for an existing membership to the same collab for the member being kept 
				 * and if one exists, merge and delete the membership for the member being removed
				 */
				if ( $existing = $collab->getMembership( $member ) )
				{
					/* Merge */
					$existing->posts += $membership->posts;
					
					/* Transfer ownership if needed */
					if ( $collab->owner_id === $membership->member_id )
					{
						$collab->owner_id 	= $member->member_id;
						$collab->owner_name 	= $member->name;
						$collab->save();
					}
					
					$existing->save();
					$membership->delete();
				}
				else
				{
					/** 
					 * No existing membership for member being kept so transfer membership over 
					 * 
					 * Note: Any existing guest stats for the merged member will be added by the
					 * $membership->save() handler.
					 */
					$membership->member_id = $member->member_id;
					$membership->save();
				}
			}
			else
			{
				$membership->delete();
			}
		}
	}
	
	/**
	 * Member is deleted
	 *
	 * @param	$member	\IPS\Member	The member
	 * @return	void
	 */
	public function onDelete( $member )
	{
		foreach ( $member->collabMemberships() as $membership )
		{
			if ( $collab = $membership->collab() )
			{
				/* If member is the owner of the collab, attempt to transfer to next eligible collab member */
				if ( $collab->owner_id == $member->member_id )
				{
					$_new_owner = NULL;
					foreach ( $collab->memberships( array( 'statuses' => \IPS\collab\COLLAB_MEMBER_ACTIVE ) ) as $_membership )
					{
						/* Check if member can own this collab */
						if ( $_membership->canOwn() )
						{
							if ( $_new_owner = $_membership->member() )
							{
								/* Winner! */
								break;
							}
						}
					}
					
					if ( $_new_owner )
					{
						/* Transfer the ownership */
						$collab->owner_id 	= $_new_owner->member_id;
						$collab->owner_name 	= $_new_owner->name;
						$collab->save();
					}
					else
					{
						/* Nobody qualified to receive collab, so lock it up */
						$collab->state = 'closed';
						$collab->save();
					}
				}
			}
			$membership->delete();
		}
	}
}