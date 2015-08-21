//<?php

class collab_hook_modCalendarEvent extends _HOOK_CLASS_
{

	/**
	 * Save Record
	 *
	 * @return	void
	 */
	public function save()
	{
		$_new = $this->_new;
		
		$result = parent::save();

		if ( $_new and $calendar = $this->containerWrapper() and $calendar->collab_id and $calendar->_items === NULL )
		{
			/* Cause our collab totals to be incremented by 1 */
			$calendar->_items = 1;
		}
		
		return $result;
	}
	
	/**
	 * Delete Record
	 *
	 * @return	void
	 */
	public function delete()
	{
		$calendar 	= $this->containerWrapper();
		$result 	= parent::delete();
		
		/* Update Collab Totals */
		if ( $calendar and $calendar->collab_id and $calendar->_items === NULL )
		{
			/* Cause our collab totals to be decreased by 1 */
			$calendar->_items = -1;
		}		
		
		return $result;
	}
	
}