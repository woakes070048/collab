<?php
/**
 * @brief		File Storage Extension: Category
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Social Suite
 * @subpackage	
 * @since		26 Jan 2015
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
 * Editor Extension: Category
 */
class _Category
{
	/**
	 * Count stored files
	 *
	 * @return	int
	 */
	public function count()
	{
		return \IPS\Db::i()->select( 'COUNT(*)', 'collab_categories', 'category_cover_photo IS NOT NULL' )->first();
	}
	
	/**
	 * Move stored files
	 *
	 * @param	int			$offset					This will be sent starting with 0, increasing to get all files stored by this extension
	 * @param	int			$storageConfiguration	New storage configuration ID
	 * @param	int|NULL	$oldConfiguration		Old storage configuration ID
	 * @throws	\Underflowexception				When file record doesn't exist. Indicating there are no more files to move
	 * @return	void								FALSE when there are no more files to move
	 */
	public function move( $offset, $storageConfiguration, $oldConfiguration=NULL )
	{
		$record	= \IPS\Db::i()->select( '*', 'collab_categories', 'category_cover_photo IS NOT NULL', 'category_id', array( $offset, 1 ) )->first();
		$file	= \IPS\File::get( $oldConfiguration ?: 'collab_Category', $record['category_cover_photo'] )->move( $storageConfiguration );
		
		\IPS\Db::i()->update( 'collab_categories', array( 'category_cover_photo' => (string) $file ), array( 'category_id=?', $record['category_id'] ) );
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
			$record	= \IPS\Db::i()->select( '*', 'collab_categories', array( 'category_cover_photo=?', (string) $file ) )->first();

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
		foreach( \IPS\Db::i()->select( '*', 'collab_categories', 'category_cover_photo IS NOT NULL' ) as $category )
		{
			try
			{
				\IPS\File::get( 'collab_Category', $category['category_cover_photo'] )->delete();
			}
			catch( \Exception $e ){}
		}
	}	
}