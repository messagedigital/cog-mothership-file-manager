<?php

namespace Message\Mothership\FileManager\Bootstrap;

use Message\Cog\Bootstrap\ServicesInterface;
use Message\Mothership\FileManager;

class Services implements ServicesInterface
{
	public function registerServices($services)
	{
		$services->extend('filesystem.stream_wrapper_mapping', function($mapping, $services) {
			$baseDir = $services['app.loader']->getBaseDir();
			// Maps cog://ms/file/* to /files/* (in the installation)
			$mapping["/^\/ms\/file\/(.*)/us"] = $baseDir.'files/$1';

			return $mapping;
		});

		$services['file_manager.file.loader'] = $services->factory(function($c) {
			return new FileManager\File\Loader(
				'Locale class',
				$c['db.query.builder.factory'],
				$c['file_manager.tag.loader']
			);
		});

		$services['file_manager.file.create'] = $services->factory(function($c) {
			return new FileManager\File\Create(
				$c['file_manager.file.loader'],
				$c['db.query'],
				$c['event.dispatcher'],
				$c['user.current']
			);
		});

		$services['file_manager.file.edit'] = $services->factory(function($c) {
			return new FileManager\File\Edit(
				$c['db.query'],
				$c['event.dispatcher'],
				$c['user.current']
			);
		});

		$services['file_manager.file.delete'] = $services->factory(function($c) {
			return new FileManager\File\Delete(
				$c['db.query'],
				$c['event.dispatcher'],
				$c['user.current']
			);
		});

		$services['file_manager.tag.loader'] = function($c) {
			return new FileManager\File\TagLoader($c['db.query.builder.factory']);
		};

		$services->extend('field.collection', function($fields, $c) {
			$fields->add(new FileManager\FieldType\File(
				$c['file_manager.file.loader'],
				$c['translator']
			));

			return $fields;
		});
	}
}