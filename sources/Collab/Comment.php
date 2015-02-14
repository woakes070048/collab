<?php
/**
 * @brief		File Comment Model
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Social Suite
 * @subpackage	Downloads
 * @since		11 Oct 2013
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\collab\Collab;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * File Comment Model
 */
class _Comment extends \IPS\Content\Comment implements 
	\IPS\Content\EditHistory, 
	\IPS\Content\ReportCenter, 
	\IPS\Content\Hideable, 
	\IPS\Content\Reputation, 
	\IPS\Content\Searchable
{
	/**
	 * @brief	[ActiveRecord] Multiton Store
	 */
	protected static $multitons;
	
	/**
	 * @brief	[Content\Comment]	Item Class
	 */
	public static $itemClass = 'IPS\collab\Collab';
	
	/**
	 * @brief	[ActiveRecord] Database Table
	 */
	public static $databaseTable = 'collab_comments';
	
	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 'comment_';
	
	/**
	 * @brief	Database Column Map
	 */
	public static $databaseColumnMap = array(
		'item'				=> 'collab_id',
		'author'			=> 'mid',
		'author_name'			=> 'author',
		'content'			=> 'text',
		'date'				=> 'date',
		'ip_address'			=> 'ip_address',
		'edit_time'			=> 'edit_time',
		'edit_member_name'		=> 'edit_name',
		'edit_show'			=> 'append_edit',
		'approved'			=> 'open'
	);
	
	/**
	 * @brief	Application
	 */
	public static $application = 'collab';
	
	/**
	 * @brief	Title
	 */
	public static $title = 'collab_comment';
	
	/**
	 * @brief	Icon
	 */
	public static $icon = 'users';
	
	/**
	 * @brief	Reputation Type
	 */
	public static $reputationType = 'comment_id';
	
	/**
	 * @brief 	Hide Log Key
	 */
	public static $hideLogKey = 'collab-comment';
	
	/**
	 * Can view?
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for or NULL for the currently logged in member
	 * @return	bool
	 */
	public function canView( $member=NULL )
	{
		$member 	= $member ?: \IPS\Member::loggedIn();
		$collab 	= $this->item();
		$commentCan 	= FALSE;
		
		if ( $collab->collabCan( 'moderateContent', $member ) )
		{
			$commentCan = TRUE;
		}
		
		return $commentCan or call_user_func_array( 'parent::canView', func_get_args() );
	}
	
	/**
	 * Can edit?
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for or NULL for the currently logged in member
	 * @return	bool
	 */
	public function canEdit( $member=NULL )
	{
		$member 	= $member ?: \IPS\Member::loggedIn();
		$collab 	= $this->item();
		$commentCan 	= FALSE;
		
		if ( $collab->collabCan( 'moderateContent', $member ) )
		{
			$commentCan = TRUE;
		}
		
		return $commentCan or call_user_func_array( 'parent::canEdit', func_get_args() );
	}	
	
	/**
	 * Can hide?
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for or NULL for the currently logged in member
	 * @return	bool
	 */
	public function canHide( $member=NULL )
	{
		$member 	= $member ?: \IPS\Member::loggedIn();
		$collab 	= $this->item();
		$commentCan 	= FALSE;
		
		if ( $collab->collabCan( 'moderateContent', $member ) )
		{
			$commentCan = TRUE;
		}
		
		return $commentCan or call_user_func_array( 'parent::canHide', func_get_args() );
	}

	/**
	 * Can unhide?
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for or NULL for the currently logged in member
	 * @return	bool
	 */
	public function canUnhide( $member=NULL )
	{
		$member 	= $member ?: \IPS\Member::loggedIn();
		$collab 	= $this->item();
		$commentCan 	= FALSE;
		
		if ( $collab->collabCan( 'moderateContent', $member ) )
		{
			$commentCan = TRUE;
		}
		
		return $commentCan or call_user_func_array( 'parent::canUnhide', func_get_args() );
	}

	/**
	 * Can delete?
	 *
	 * @param	\IPS\Member|NULL	$member	The member to check for or NULL for the currently logged in member
	 * @return	bool
	 */
	public function canDelete( $member=NULL )
	{
		$member 	= $member ?: \IPS\Member::loggedIn();
		$collab 	= $this->item();
		$commentCan 	= FALSE;
		
		if ( $collab->collabCan( 'moderateContent', $member ) )
		{
			$commentCan = TRUE;
		}
		
		return $commentCan or call_user_func_array( 'parent::canDelete', func_get_args() );
	}

	/**
	 * Get URL for doing stuff
	 *
	 * @param	string|NULL		$action		Action
	 * @return	\IPS\Http\Url
	 */
	public function url( $action=NULL )
	{
		return parent::url( $action )->setQueryString( 'tab', 'comments' );
	}
}