<?php

namespace Message\Mothership\FileManager\File;

use Message\Mothership\FileManager\File\File;
use Message\Mothership\FileManager\Event\FileEvent;
use Message\Cog\Event\DispatcherInterface;
use Message\Cog\DB\Query as Query;

	/**
	 * Author Ewan Valentine <ewan@message.co.uk> 
	 * Copyright Message 2013
	 *
	 * Basic file deletion class for database records
	 * Saves who deleted a file and when.
	 */
class Delete
{

	protected $_query;
	protected $_eventDispatcher;

	/**
	 * @access public
	 * @param Query $query
	 */
	public function __construct(Query $query, DispatcherInterface $eventDispatcher)
	{
		$this->_query 			= $query;
		$this->_eventDispatcher = $eventDispatcher;
	}

	/**
	 * Sets file in database marked as deleted.
	 * Keeps a record of who a file was deleted by and when.
	 *
	 * @return $this->_file
	 * @access public
	 * @param $user
	 */
	public function delete(File $file)
	{
	
		$file->authorship->delete(new \Datetime, 1);

		/** Query to set deletion info */
		$result = $this->_query->run('
			UPDATE
				file
			SET
				deleted_at = :dl_at?i,
				deleted_by = :dl_by?i
			WHERE
				file_id = :file_id?i
		', array(
				'dl_at' 	=> $file->authorship->deletedAt()->getTimestamp(),
				'dl_by' 	=> $file->authorship->deletedBy(),
				'file_id' 	=> $file->fileID,
			));

		$this->_eventDispatcher->dispatch(
			FileEvent::DELETE,
			new FileEvent($file)
		);

		return $result->affected() ? $file : false;
	}

	public function restore(File $file)
	{
		$file->authorship->restore();

		$result = $this->_query->run('
			UPDATE 
				file
			SET
				deleted_at = NULL,
				deleted_by = NULL
			WHERE
				file_id = file_id?i
		', array(
				'file_id' 	=> $file->fileID,
			));

		$this->_eventDispatcher->dispatch(
			FileEvent::RESTORE,
			new FileEvent($file)
		);

		return $result->affected() ? $file : false;
	}
}



