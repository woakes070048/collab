<?php
/**
 * @brief		File Review Model
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Social Suite
 * @subpackage	Downloads
 * @since		4 Nov 2013
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
 * File Review Model
 */
class _Review extends \IPS\Content\Review implements 
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
	public static $databaseTable = 'collab_reviews';
	
	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 'review_';
	
	/**
	 * @brief	Database Column Map
	 */
	public static $databaseColumnMap = array(
		'item'				=> 'collab_id',
		'author'			=> 'mid',
		'author_name'			=> 'author_name',
		'content'			=> 'text',
		'date'				=> 'date',
		'ip_address'			=> 'ip',
		'edit_time'			=> 'edit_time',
		'edit_member_name'		=> 'edit_name',
		'edit_show'			=> 'append_edit',
		'rating'			=> 'rating',
		'votes_total'			=> 'votes',
		'votes_helpful'			=> 'votes_helpful',
		'votes_data'			=> 'votes_data',
		'approved'			=> 'approved',
	);
	
	/**
	 * @brief	Application
	 */
	public static $application = 'collab';
	
	/**
	 * @brief	Title
	 */
	public static $title = 'collab_review';
	
	/**
	 * @brief	Icon
	 */
	public static $icon = 'users';
	
	/**
	 * @brief	Reputation Type
	 */
	public static $reputationType = 'review_id';
	
	/**
	 * @brief 	Hide Log Key
	 */
	public static $hideLogKey = 'collab-review';
	
	/**
	 * Get URL for doing stuff
	 *
	 * @param	string|NULL		$action		Action
	 * @return	\IPS\Http\Url
	 */
	public function url( $action=NULL )
	{
		return parent::url( $action )->setQueryString( 'tab', 'reviews' );
	}
}