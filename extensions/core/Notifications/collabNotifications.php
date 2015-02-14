<?php
/**
 * @brief		Notification Options
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Social Suite
 * @subpackage	
 * @since		06 Jan 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\collab\extensions\core\Notifications;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Notification Options
 */
class _collabNotifications
{
	/**
	 * Get configuration
	 *
	 * @param	\IPS\Member	$member	The member
	 * @return	array
	 */
	public function getConfiguration( $member )
	{		
		$default = array( 'default' => array( 'inline' ), 'disabled' => array() );
		
		return array(
			'collab_invitation_received' => $default,
			'collab_join_accepted' => $default,
			'collab_invitation_accepted' => $default,
			'collab_join_requested' => $default,
		);
	}
	
	/**
	 * Parse notification: collab_invitation_received
	 *
	 * @param	\IPS\Notification\Inline	$notification	The notification
	 * @return	array
	 */
	public function parse_collab_invitation_received( $notification )
	{
		$membership = $notification->item;
		
		if ( !$membership )
		{
			throw new \OutOfRangeException;
		}
		
		$collab = \IPS\collab\Collab::load( $membership->collab_id );
		$author = \IPS\Member::load( $membership->sponsor_id );
		
		return array(
				'title'		=> \IPS\Member::loggedIn()->language()->addToStack( 'collab_notification_collab_invitation_received', FALSE, array( 'sprintf' => array( $author->name, $collab->collab_singular, $collab->title ) ) ),
				'url'		=> \IPS\Http\Url::internal( "app=collab&module=collab&controller=collabs&id={$collab->collab_id}&do=joinRequest" ),
				'content'	=> $membership->collab_notes,
				'author'	=> $author,
		);
	}
	
	/**
	 * Parse notification: collab_join_requested
	 *
	 * @param	\IPS\Notification\Inline	$notification	The notification
	 * @return	array
	 */
	public function parse_collab_join_requested( $notification )
	{
		$membership = $notification->item;
		
		if ( !$membership )
		{
			throw new \OutOfRangeException;
		}
		
		$collab = \IPS\collab\Collab::load( $membership->collab_id );
		$author = \IPS\Member::load( $membership->member_id );
		
		return array(
				'title'		=> \IPS\Member::loggedIn()->language()->addToStack( 'collab_notification_collab_join_requested', FALSE, array( 'sprintf' => array( $author->name, $collab->collab_singular, $collab->title ) ) ),
				'url'		=> \IPS\Http\Url::internal( "app=collab&module=collab&controller=admin&collab={$collab->collab_id}&do=manageMembers" ),
				'content'	=> $membership->member_notes,
				'author'	=> $author,
		);
	}
	
	/**
	 * Parse notification: collab_join_accepted
	 *
	 * @param	\IPS\Notification\Inline	$notification	The notification
	 * @return	array
	 */
	public function parse_collab_join_accepted( $notification )
	{
		$membership = $notification->item;
		
		if ( !$membership )
		{
			throw new \OutOfRangeException;
		}
		
		$collab = \IPS\collab\Collab::load( $membership->collab_id );
		$author = \IPS\Member::load( $membership->sponsor_id );
		
		return array(
				'title'		=> \IPS\Member::loggedIn()->language()->addToStack( 'collab_notification_collab_join_accepted', FALSE, array( 'sprintf' => array( $author->name, $collab->collab_singular, $collab->title ) ) ),
				'url'		=> $collab->url(),
				'content'	=> $membership->collab_notes,
				'author'	=> $author,
		);
	}
	
	/**
	 * Parse notification: collab_invitation_accepted
	 *
	 * @param	\IPS\Notification\Inline	$notification	The notification
	 * @return	array
	 */
	public function parse_collab_invitation_accepted( $notification )
	{
		$membership = $notification->item;
		
		if ( !$membership )
		{
			throw new \OutOfRangeException;
		}
		
		$collab = \IPS\collab\Collab::load( $membership->collab_id );
		$author = \IPS\Member::load( $membership->member_id );
		
		return array(
				'title'		=> \IPS\Member::loggedIn()->language()->addToStack( 'collab_notification_collab_invitation_accepted', FALSE, array( 'sprintf' => array( $author->name, $collab->collab_singular, $collab->title ) ) ),
				'url'		=> $collab->url(),
				'content'	=> $membership->member_notes,
				'author'	=> $author,
		);
	}
	
}