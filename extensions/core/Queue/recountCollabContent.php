<?php
/**
 * @brief		Background Task
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Social Suite
 * @subpackage	Collaboration
 * @since		28 Apr 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\collab\extensions\core\Queue;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Background Task
 */
class _recountCollabContent
{
	/**
	 * Run Background Task
	 *
	 * @param	mixed					$data	Data as it was passed to \IPS\Task::queue()
	 * @param	int						$offset	Offset
	 * @return	int|null				New offset or NULL if complete
	 * @throws	\OutOfRangeException	Indicates offset doesn't exist and thus task is complete
	 */
	public function run( $data, $offset )
	{
		$_recounted = 0;
	
		while( $collab_id = $data[ $offset ] )
		{
			try
			{
				$collab = \IPS\collab\Collab::load( $collab_id );
				$_recounted += count( $collab->memberships() );
				
				foreach( $collab->memberships() as $membership )
				{
					$membership->member()->recountCollabContent( $collab );
				}
			}
			catch ( \OutOfRangeException $e ) { }
						
			$offset++;
			
			/**
			 * Stop processing limit per run  
			 */
			if ( $offset < count( $data ) and $_recounted >= 100 )
			{
				return $offset;
			}
		}
		
		return NULL;
	}
	
	/**
	 * Get Progress
	 *
	 * @param	mixed					$data	Data as it was passed to \IPS\Task::queue()
	 * @param	int						$offset	Offset
	 * @return	array( 'text' => 'Doing something...', 'complete' => 50 )	Text explaning task and percentage complete
	 * @throws	\OutOfRangeException	Indicates offset doesn't exist and thus task is complete
	 */
	public function getProgress( $data, $offset )
	{
		$total = count( $data );
		$complete = (int) ( 100 * ( ($offset + 1) / $total ) );
		
		return array
		( 
			'text' => 'Recounting content for ' . $total . ' collaborations', 
			'complete' => $complete 
		);
	}	
}