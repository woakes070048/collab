<?php
/**
 * @brief		Collaboration collab (Software, Content, Social Group, Clan, Etc.)
 * @author		Kevin Carwile (http://www.linkedin.com/in/kevincarwile)
 * @copyright		(c) 2014 - Kevin Carwile
 * @package		Collaboration
 * @since		10 Dec 2014
 */

namespace IPS\collab\Collab;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 *  Collab collab
 */
class _Membership extends \IPS\Patterns\ActiveRecord
{
	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = '';
	
	/**
	 * @brief	[ActiveRecord] ID Database Column
	 */
	public static $databaseColumnId = 'id';

	/**
	 * @brief	[ActiveRecord] Database table
	 * @note	This MUST be over-ridden
	 */
	public static $databaseTable	= 'collab_memberships';
		
	/**
	 * @brief	[ActiveRecord] Database ID Fields
	 */
	protected static $databaseIdFields = array();
	
	/**
	 * @brief	Bitwise keys
	 */
	protected static $bitOptions = array();

	/**
	 * @brief	[ActiveRecord] Multiton Store
	 * @note	This needs to be declared in any child classes as well, only declaring here for editor code-complete/error-check functionality
	 */
	protected static $multitons	= array();
	
	/**
	 * Get Membership Member
	 *
	 * @return	\IPS\Member
	 */
	public function member()
	{
		return \IPS\Member::load( $this->member_id );
	}

	/**
	 * Get Membership Member Title
	 *
	 * @return	\IPS\Member
	 */
	public function title()
	{
		if ( ! in_array( $this->status, array( \IPS\collab\COLLAB_MEMBER_PENDING, \IPS\collab\COLLAB_MEMBER_INVITED, \IPS\collab\COLLAB_MEMBER_BANNED ) ) )
		{
			/* First, check for member specific title */
			if ( \trim( $this->title ) != '' )
			{
				return \trim( $this->title );
			}
			
			/* Check for first role based title */
			foreach ( $this->roles() as $role )
			{
				if ( \trim( $role->member_title ) != '' )
				{
					return \trim( $role->member_title );
				}
			}
			
			/* Check for collab specific default */
			if ( \trim( $this->collab()->default_member_title ) != '' )
			{
				return \trim( $this->collab()->default_member_title );
			}
			
			/* Fallback to sitewide default */
			return sprintf( \IPS\Member::loggedIn()->language()->get( 'collab_default_member_title' ), $this->collab()->collab_singular );
		}
		
		return "";
	}
	
	/**
	 * Get Membership Collab
	 *
	 * @return	\IPS\Member
	 */
	public function collab()
	{
		try
		{
			$collab = \IPS\collab\Collab::load( $this->collab_id );
		}
		catch ( \OutOfRangeException $e ) 
		{
			return NULL;
		}
		return $collab;
	}
	
	/**
	 * Get Membership Sponsor
	 *
	 * @return	\IPS\Member
	 */
	public function sponsor()
	{
		return \IPS\Member::load( $this->sponsor_id );
	}
	
	/**
	 * Permission Check
	 *
	 * @param	string			$perm		A string representing a permission to check for
	 * @return	bool
	 */
	public function can( $perm )
	{
		if ( $this->status !== \IPS\collab\COLLAB_MEMBER_ACTIVE )
		{
			return FALSE;
		}
		
		foreach ( $this->roles() as $role )
		{
			if ( $role->roleCan( $perm ) )
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}

	/**
	 * Check if member can become the new owner of the collab
	 *
	 * @param	\IPS\collab\Collab	$collab		The collab to check if membership can own, NULL to use 
	 * @return	bool
	 */
	public function canOwn()
	{
		if ( $collab = $this->collab() and $collab->owner_id !== $this->member_id )
		{
			if ( $this->collab_id !== $collab->collab_id or $this->status !== \IPS\collab\COLLAB_MEMBER_ACTIVE )
			{
				return FALSE;
			}
			
			/* Check global group limit */
			if ( $this->member()->group['g_collabs_owned_limit'] > 0 )
			{
				if ( \IPS\Db::i()->select( 'COUNT(*)', 'collab_collabs', array( 'owner_id=?', $this->member()->member_id ) )->first() >= $this->member()->group['g_collabs_owned_limit'] )
				{
					return FALSE;
				}
			}
			
			/* Check category limit */
			if ( $collab = $this->collab() and $category = $collab->container() )
			{
				if ( $category->max_collabs_owned > 0 )
				{
					if ( \IPS\Db::i()->select( 'COUNT(*)', 'collab_collabs', array( 'owner_id=? AND category_id=?', $this->member()->member_id, $category->id ) )->first() >= $category->max_collabs_owned )
					{
						return FALSE;
					}
				}
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
		
		return TRUE;
	}

	/**
	 *  @brief 	Roles Cache
	 */
	protected $roleStack = NULL;
	
	/**
	 * Get Membership Roles. In order of most importance
	 *
	 * @return	\IPS\Member
	 */
	public function roles()
	{
		if ( isset( $this->roleStack ) )
		{
			return $this->roleStack;
		}
		
		$default_roles 		= 'FALSE';
		$this->roleStack 	= array();
		$roles 			= explode( ',', $this->roles ) ?: array();
		
		if ( $this->status === \IPS\collab\COLLAB_MEMBER_ACTIVE )
		{
			$default_roles = 'member_default=1';
			if ( $this->member()->member_id == $this->collab()->owner_id )
			{
				$default_roles .= ' OR owner_default=1';
			}
		}
		
		foreach ( \IPS\Db::i()->select( 'id', 'collab_roles', array( \IPS\Db::i()->findInSet( 'id', $roles ) . " OR ( collab_id=? AND ( {$default_roles} ) )", $this->collab()->collab_id ), 'weight ASC' ) as $role_id )
		{
			$this->roleStack[] = \IPS\collab\Collab\Role::load( $role_id );
		}
		
		return $this->roleStack;
	}
	
	/**
	 * Save Membership
	 *
	 * @return	void
	 */
	public function save()
	{
		/* Transfer Guest Records */
		if ( $guest = $this->collab()->guest( $this->member(), FALSE ) )
		{
			$this->posts += $guest->posts;
			if ( $this->lastpost < $guest->lastpost )
			{
				$this->lastpost = $guest->lastpost;
			}
			$guest->delete();
		}
		
		parent::save();
	}
	
}