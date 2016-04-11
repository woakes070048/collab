<?php
/**
 * @brief		File Storage Extension: Logos
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @subpackage	Collaboration
 * @since		10 Apr 2016
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\collab\extensions\core\FileStorage;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * File Storage Extension: Logos
 */
class _Logos
{
	/**
	 * Count stored files
	 *
	 * @return	int
	 */
	public function count()
	{
		return \IPS\Db::i()->select( 'COUNT(*)', 'collab_collabs', 'logo IS NOT NULL' )->first();
	}
	
	/**
	 * Move stored files
	 *
	 * @param	int			$offset					This will be sent starting with 0, increasing to get all files stored by this extension
	 * @param	int			$storageConfiguration	New storage configuration ID
	 * @param	int|NULL	$oldConfiguration		Old storage configuration ID
	 * @throws	\UnderflowException					When file record doesn't exist. Indicating there are no more files to move
	 * @return	void|int							An offset integer to use on the next cycle, or nothing
	 */
	public function move( $offset, $storageConfiguration, $oldConfiguration=NULL )
	{
		$record	= \IPS\Db::i()->select( '*', 'collab_collabs', 'logo IS NOT NULL', 'collab_id', array( $offset, 1 ) )->first();
		$file	= \IPS\File::get( $oldConfiguration ?: 'collab_Logos', $record[ 'logo' ] )->move( $storageConfiguration );
		
		\IPS\Db::i()->update( 'collab_collabs', array( 'logo' => (string) $file ), array( 'collab_id=?', $record[ 'collab_id' ] ) );
	}

	/**
	 * Check if a file is valid
	 *
	 * @param	\IPS\Http\Url	$file		The file to check
	 * @return	bool
	 */
	public function isValidFile( $file )
	{
		try
		{
			$record	= \IPS\Db::i()->select( '*', 'collab_collabs', array( 'logo=?', (string) $file ) )->first();

			return TRUE;
		}
		catch ( \UnderflowException $e )
		{
			return FALSE;
		}
	}

	/**
	 * Delete all stored files
	 *
	 * @return	void
	 */
	public function delete()
	{
		foreach( \IPS\Db::i()->select( '*', 'collab_collabs', 'logo IS NOT NULL' ) as $collab )
		{
			try
			{
				\IPS\File::get( 'collab_Logos', $collab[ 'logo' ] )->delete();
			}
			catch( \Exception $e ) { }
		}
	}
}