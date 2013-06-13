<?php

namespace Message\Mothership\FileManager\Controller;
/**
 * Controller listing files from file manager
 *
 * @author Daniel Hannah <danny@message.co.uk>
 */
class Detail extends \Message\Cog\Controller\Controller
{

	/**
	 * Display the details of the given file
	 *
	 * @param  int $fileID fileID to be loaded and displayed
	 */
	public function index($fileID)
	{
		$file = $this->get('filesystem.file.loader')->getByID($fileID);
		$author = $this->get('user.loader')->getByID($file->authorship->createdBy());
		$data = array(
			'file' => $file,
			'author' => $author,
		);
		return $this->render('::detail', $data);
	}

	/**
	 * Edit the given file attributes
	 *
	 * @param  int $fileID fileID to be updated
	 */
	public function edit($fileID)
	{
		// Load the file object
		$file = $this->get('filesystem.file.loader')->getByID($fileID);
		// Load the changed data from the request
		if ($edits = $this->get('request')->get('file')) {
			// Set the alt text
			$file->altText = $edits['alt_text'];
			// Turn the tags into an array and trim the values
			$file->tags = array_filter(array_map('trim', explode(',',$edits['tags'])));
			// Save the file
			if ($file = $this->get('filesystem.file.edit')->save($file)) {
				$this->addFlash('success', $file->file->getBasename().' was updated successfully');
			} else {
				$this->addFlash('error', $file->file->getBasename().' could not be updated.');
			}
		}
		// Redirect the page to where is was
		return $this->redirect($this->generateUrl('ms.cp.file_manager.detail',array('fileID' => $file->id)));
	}

	/**
	 * Delete a fileID
	 *
	 * @param  int 	$fileID id of the file to be marked as deleted
	 */
	public function delete($fileID)
	{
		// Check that the delete request has been sent
		if ($delete = $this->get('request')->get('delete')) {
			// Load the file object
			$file = $this->get('filesystem.file.loader')->getByID($fileID);

			if ($file = $this->get('filesystem.file.delete')->delete($file)) {
				$this->addFlash('success', $file->file->getBasename().' was deleted. <a href="'.$this->generateUrl('ms.cp.file_manager.restore',array('fileID' => $file->id)).'">Undo</a>');
			} else {
				$this->addFlash('error', $file->file->getBasename().' could not be deleted.');
			}

		}

		return $this->redirect($this->generateUrl('ms.cp.file_manager.listing'));
	}

	/**
	 * Restore an image that has been deleted.
	 * 
	 * @param  int $fileID 		fileID of the file to be restored
	 */
	public function restore($fileID)
	{
		// Load the file
		$file = $this->get('filesystem.file.loader')->includeDeleted(true)->getByID($fileID);

		if ($this->get('filesystem.file.delete')->restore($file)) {
			$this->addFlash('success', $file->file->getBasename().' was restored successfully');
		} else {
			$this->addFlash('error', $file->file->getBasename().' could not be restored.');
		}

		return $this->redirect($this->generateUrl('ms.cp.file_manager.listing'));
	}
}
