<?php

/**
 * This file is part of the ReCaptchaControl package
 *
 * @license  MIT
 * @author   Petr Kessler (https://kesspess.cz)
 * @link     https://github.com/uestla/ReCaptchaControl
 */

namespace ReCaptchaControl;

use Nette\Forms;
use Nette\Utils\Html;


class Control extends Forms\Controls\BaseControl
{

	/** @var Validator */
	private $validator;

	/** @var Renderer */
	private $renderer;

	/** @var bool */
	private $initialized = false;


	/**
	 * @param  Validator $validator
	 * @param  Renderer $renderer
	 * @param  string $caption
	 * @param  string $message
	 */
	public function __construct(Validator $validator, Renderer $renderer, $caption = null, $message = null)
	{
		parent::__construct($caption);

		$this->setOmitted();
		$this->renderer = $renderer;
		$this->validator = $validator;
		$this->control = $renderer->getHtml();
		$this->setRequired(true)->addRule(__CLASS__ . '::validateValid', $message);

		$this->initialized = true;
	}


	/** @inheritdoc */
	public function addRule($validator, $message = null, $arg = null)
	{
		if ($this->initialized
				&& ($validator === [__CLASS__, 'validateValid'] || $validator === __CLASS__ . '::validateValid')) {
			trigger_error('ReCaptchaControl is required by default and thus calling addRule() is deprecated. Please remove it to prevent multiple validation.', E_USER_DEPRECATED);
		}

		return parent::addRule($validator, $message, $arg);
	}


	/** @return Html */
	public function getControl()
	{
		$this->setOption('rendered', true);
		$el = clone $this->control;
		return $el->addAttributes([
			'id' => $this->getHtmlId(),
		]);
	}


	/** @return bool */
	public function isFilled()
	{
		return true;
	}


	/**
	 * @param  Control $control
	 * @return bool
	 */
	public static function validateValid(Control $control)
	{
		return $control->validator->validate();
	}


	/**
	 * @param  Validator $validator
	 * @param  Renderer $renderer
	 * @param  string $method
	 * @return void
	 */
	public static function register(Validator $validator, Renderer $renderer, $method = 'addRecaptcha')
	{
		Forms\Container::extensionMethod($method, function ($container, $name, $label = null, $message = null) use ($validator, $renderer) {
			return $container[$name] = new Control($validator, $renderer, $label, $message);
		});
	}

}
