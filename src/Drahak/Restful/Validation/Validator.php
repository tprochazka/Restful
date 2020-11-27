<?php
namespace Drahak\Restful\Validation;

use Drahak\Restful\InvalidStateException;
use Drahak\Restful\InvalidArgumentException;
use Nette\SmartObject;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

/**
 * Rule validator
 * @package Drahak\Restful\Validation
 * @author Drahomír Hanák
 */
class Validator implements IValidator
{

    use SmartObject;

	/** @var array Command handle callbacks */
	public $handle = array(
		self::EMAIL => array(__CLASS__, 'validateEmail'),
		self::URL => array(__CLASS__, 'validateUrl'),
		self::REGEXP => array(__CLASS__, 'validateRegexp'),
		self::EQUAL => array(__CLASS__, 'validateEquality'),
		self::UUID => array(__CLASS__, 'validateUuid'),
		self::CALLBACK => array(__CLASS__, 'validateCallback'),
		self::REQUIRED => array(__CLASS__, 'validateRequired')
	);

	/**
	 * Validate value for this rule
	 * @param mixed $value
	 * @param Rule $rule
	 * @return bool
	 *
	 * @throws ValidationException
	 * @throws InvalidStateException
	 */
	public function validate($value, Rule $rule)
	{
		if (isset($this->handle[$rule->expression])) {
			$callback = $this->handle[$rule->expression];
			if (!is_callable($callback)) {
				throw new InvalidStateException(
					'Handle for expression ' . $rule->expression . ' not found or is not callable');
			}
			$params = array($value, $rule);
			call_user_func_array($callback, $params);
			return TRUE;
		}

		$expression = $this->parseExpression($rule);
		if (!Validators::is($value, $expression)) {
			throw ValidationException::createFromRule($rule, $value);
		}
		return TRUE;
	}

	/**
	 * Parse nette validator expression
	 * @param Rule $rule
	 * @return string
	 */
	private function parseExpression(Rule $rule)
	{
		$givenArgumentsCount = count((array)$rule->argument);
		$expectedArgumentsCount = substr_count($rule->expression, '%');
		if ($expectedArgumentsCount != $givenArgumentsCount) {
			throw new InvalidArgumentException(
				'Invalid number of arguments for expression "' . $rule->expression . '". Expected ' . $expectedArgumentsCount . ', ' . $givenArgumentsCount . ' given.'
			);
		}
		return vsprintf($rule->expression, $rule->argument);
	}

	/******************** Special validators ********************/

	/**
	 * Validate callback rule
	 * @param  string|numeric|null $value 
	 * @param  Rule   $rule  
	 *
	 * @throws  ValidationException If callback returns false
	 */
	public static function validateCallback($value, Rule $rule)
	{
		$callback = $rule->argument[0];
		$result = $callback($value);
		if ($result === FALSE) {
			throw ValidationException::createFromRule($rule, $value);
		}
	}

	/**
	 * Validate required rule
	 * @param  string|numeric|null $value 
	 * @param  Rule   $rule  
	 *
	 * @throws  ValidationException If field value is missing (is NULL)
	 */
	public static function validateRequired($value, Rule $rule)
	{
		if ($value === NULL) {
			throw ValidationException::createFromRule($rule, $value);
		}
	}

	/**
	 * Validate regexp
	 * @param mixed $value
	 * @param Rule $rule
	 *
	 * @throws InvalidArgumentException
	 * @throws ValidationException
	 */
	public static function validateRegexp($value, Rule $rule)
	{
		if (!isset($rule->argument[0])) {
			throw new InvalidArgumentException('No regular expression found in pattern validation rule');
		}

		if (!Strings::match((string) $value, $rule->argument[0])) {
			throw ValidationException::createFromRule($rule, $value);
		}
	}

	/**
	 * Validate equality
	 * @param string $value
	 * @param Rule $rule
	 * @throws ValidationException
	 */
	public static function validateEquality($value, Rule $rule)
	{
		if (!in_array($value, $rule->argument)) {
			throw ValidationException::createFromRule($rule, $value);
		}
	}

	/**
	 * Validate email
	 * @param string $value
	 * @param Rule $rule
	 * @throws ValidationException
	 */
	public static function validateEmail($value, Rule $rule)
	{
		if (!Validators::isEmail($value)) {
			throw ValidationException::createFromRule($rule, $value);
		}
	}

	/**
	 * Validate URL
	 * @param string $value
	 * @param Rule $rule
	 * @throws ValidationException
	 */
	public static function validateUrl($value, Rule $rule)
	{
		if (!Validators::isUrl($value)) {
			throw ValidationException::createFromRule($rule, $value);
		}
	}

	/**
	 * Validate UUID
	 * @param string $value
	 * @param Rule $rule
	 * @throws ValidationException
	 */
	public static function validateUuid($value, Rule $rule)
	{
		$isUuid = (bool)preg_match("/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i", $value);
		if (!$isUuid) {
			throw ValidationException::createFromRule($rule, $value);
		}
	}

}
