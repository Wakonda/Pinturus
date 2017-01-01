<?php
namespace Poeticus\Service;

class GenericFunction
{
	private $app;
	
	public function __construct($app)
	{
		$this->app = $app;
	}
	
	public function getUniqCleanNameForFile($file)
	{
		$file = preg_replace('/[^A-Za-z0-9 _\-.]/', '', $file->getClientOriginalName());
		return uniqid()."_".$file;
	}
}