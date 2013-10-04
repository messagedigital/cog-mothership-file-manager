<?php

namespace Message\Mothership\FileManager\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router['ms.cp.file_manager']->setPrefix('/file')->setParent('ms.cp');

		$router['ms.cp.file_manager']->add('ms.cp.file_manager.listing', '/', '::Controller:Listing#index')
			->setMethod('GET');

		$router['ms.cp.file_manager']->add('ms.cp.file_manager.search.forward', '/search', '::Controller:Listing#searchRedirect')
			->setMethod('POST');

		$router['ms.cp.file_manager']->add('ms.cp.file_manager.search', '/search/{term}', '::Controller:Listing#search');

		$router['ms.cp.file_manager']->add('ms.cp.file_manager.upload', '/upload', '::Controller:Upload#index')
			->setMethod('POST');

		$router['ms.cp.file_manager']->add('ms.cp.file_manager.detail', '/{fileID}', '::Controller:Detail#index')
			->setRequirement('fileID', '\d+');

		$router['ms.cp.file_manager']->add('ms.cp.file_manager.edit', '/{fileID}/edit', '::Controller:Detail#edit')
			->setRequirement('fileID', '\d+')
			->setMethod('POST');

		$router['ms.cp.file_manager']->add('ms.cp.file_manager.delete', '/{fileID}/delete', '::Controller:Detail#delete')
			->setRequirement('fileID', '\d+')
			->setMethod('DELETE');

		$router['ms.cp.file_manager']->add('ms.cp.file_manager.restore', '/{fileID}/restore/{hash}', '::Controller:Detail#restore')
			->setRequirement('fileID', '\d+')
			->setMethod('GET')
			->enableCsrf('hash');

		# TODO: put modal routes in nested group when 3 levels of collection nesting works properly
		#$router['ms.cp.file_manager.modal']->setPrefix('/modal')->setParent('ms.cp.file_manager');

		$router['ms.cp.file_manager']->add('ms.cp.file_manager.modal.index', '/modal', '::Controller:Listing#index')
			->setMethod('GET');

		$router['ms.cp.file_manager']->add('ms.cp.file_manager.print', '/print', '::Controller:Printer#printPath')
			->setRequirement('path', '.+');
		$router['ms.cp.file_manager']->add('ms.cp.file_manager.print.file', '/{fileID}/print', '::Controller:Printer#printFile')
			->setRequirement('fileID', '\d+');
	}
}