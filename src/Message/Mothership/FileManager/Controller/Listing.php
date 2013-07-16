<?php

namespace Message\Mothership\FileManager\Controller;

use Message\Mothership\FileManager\File\Create;
use Message\Mothership\FileManager\File\Loader;

use Message\Cog\Filesystem\File as FilesystemFile;

/**
 * Controller listing files from file manager
 *
 * @author Danny Hannah <danny@message.co.uk>
 */
class Listing extends \Message\Cog\Controller\Controller
{
	public function index()
	{
		return $this->_renderSearchForm(
			$this->get('file_manager.file.loader')->getAll()
		);
	}

	public function searchRedirect()
	{
		$valid = ($this->_getUploadForm()->isValid() && $this->_getSearchForm()->isValid());
		if ($valid && ($search = $this->get('request')->request->get('file_search'))) {

			return $this->search($search['term']);
		}

		return $this->index();
	}

	public function search($term)
	{
		return $this->_renderSearchForm(
			$this->get('file_manager.file.loader')->getBySearchTerm($term),
			$term
		);
	}

	protected function _renderSearchForm($files, $searchTerm = null, $uploadForm = null, $searchForm = null)
	{
		$uploadForm = $uploadForm ?: $this->_getUploadForm();
		$searchForm = $searchForm ?: $this->_getSearchForm();

		return $this->render('::listing', array(
			'files'         => $files,
			'searchTerm'    => $searchTerm,
			'form'          => $uploadForm,
			'search_form'   => $searchForm
		));
	}

	protected function _getUploadForm()
	{
		$form = $this->get('form')
			->setName('upload')
			->setMethod('POST')
			->setAction($this->generateUrl('ms.cp.file_manager.upload'));

		$form->add('new_upload', 'file', 'upload an image', array('attr' => array('multiple' => 'multiple')));

		return $form;
	}

	protected function _getSearchForm()
	{
		$form = $this->get('form')
			->setName('file_search')
			->setMethod('POST')
			->setAction($this->generateUrl('ms.cp.file_manager.search.forward'));

		$form->add('term', 'search', 'Enter search term...');

		return $form;
	}
}
