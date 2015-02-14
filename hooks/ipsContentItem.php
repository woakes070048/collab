//<?php

abstract class collab_hook_ipsContentItem extends _HOOK_CLASS_
{

	/**
	 * Construct ActiveRecord from database row
	 *
	 * @param	array	$data							Row from database table
	 * @param	bool	$updateMultitonStoreIfExists	Replace current object in multiton store if it already exists there?
	 * @return	static
	 */
	static public function constructFromData( $data, $updateMultitonStoreIfExists=true )
	{
		$obj = call_user_func_array( 'parent::constructFromData', func_get_args() );
		\IPS\collab\Application::inferCollab( $obj );
		return $obj;
	}

	/**
	 * Is locked?
	 *
	 * @return	bool
	 * @throws	\BadMethodCallException
	 */
	public function locked()
	{
		$locked = parent::locked();
		
		if ( $locked === TRUE )
		{
			return TRUE;
		}
		
		if ( $collab = \IPS\collab\Application::getCollab( $this ) )
		{
			if ( $collab->locked() )
			{
				return TRUE;
			}
		}
		
		return $locked;
	}	
	
}