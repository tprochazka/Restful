<?php
namespace Drahak\Restful\Application;

use Nette\Routing\Router;
use Nette\Http;

/**
 * IResourceRouter
 * @package Drahak\Restful\Routes
 * @author Drahomír Hanák
 */
interface IResourceRouter extends Router
{

	/** Resource methods */
	const GET = 4;
	const POST = 8;
	const PUT = 16;
	const DELETE = 32;
	const HEAD = 64;
	const PATCH = 128;
	const OPTIONS = 256;

	/** Combined resource methods */
	const RESTFUL = 508; // GET | POST | PUT | DELETE | HEAD | PATCH | OPTIONS
	const CRUD = 188; // PUT | GET | POST | DELETE | PATCH

	/**
	 * Is this route mapped to given method
	 * @param int $method
	 * @return bool
	 */
	public function isMethod($method);

	/**
	 * Get request method flag
	 * @param Http\IRequest $httpRequest
	 * @return string|null
	 */
	public function getMethod(Http\IRequest $httpRequest);

	/**
	 * Get action dictionary
	 * @return array methodFlag => presenterActionName
	 */
	public function getActionDictionary();


}
