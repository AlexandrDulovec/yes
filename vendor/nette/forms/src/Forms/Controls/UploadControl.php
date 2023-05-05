<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;
use Nette\Forms;
use Nette\Forms\Form;
use Nette\Http\FileUpload;
use Nette\Utils\Arrays;


/**
 * Text box and browse button that allow users to select a file to upload to the server.
 */
class UploadControl extends BaseControl
{
	/** validation rule */
	public const Valid = ':uploadControlValid';
	public const VALID = self::Valid;


	/**
	 * @param  string|object  $label
	 */
	public function __construct($label = null, bool $multiple = false)
	{
		parent::__construct($label);
		$this->control->type = 'file';
		$this->control->multiple = $multiple;
		$this->setOption('type', 'file');
		$this->addCondition(true) // not to block the export of rules to JS
			->addRule([$this, 'isOk'], Forms\Validator::$messages[self::Valid]);
		$this->addRule(Form::MaxFileSize, null, Forms\Helpers::iniGetSize('upload_max_filesize'));
		if ($multiple) {
			$this->addRule(Form::MaxLength, 'The maximum allowed number of uploaded files is %i', (int) ini_get('max_file_uploads'));
		}

		$this->monitor(Form::class, function (Form $form): void {
			if (!$form->isMethod('post')) {
				throw new Nette\InvalidStateException('File upload requires method POST.');
			}

			$form->getElementPrototype()->enctype = 'multipart/form-data';
		});
	}


	public function loadHttpData(): void
	{
		$this->value = $this->getHttpData(Form::DataFile);
		if ($this->value === null) {
			$this->value = new FileUpload(null);
		}
	}


	public function getHtmlName(): string
	{
		return parent::getHtmlName() . ($this->control->multiple ? '[]' : '');
	}


	/**
	 * @return static
	 * @internal
	 */
	public function setValue($value)
	{
		return $this;
	}


	/**
	 * Has been any file uploaded?
	 */
	public function isFilled(): bool
	{
		return $this->value instanceof FileUpload
			? $this->value->getError() !== UPLOAD_ERR_NO_FILE // ignore null object
			: (bool) $this->value;
	}


	/**
	 * Have been all files successfully uploaded?
	 */
	public function isOk(): bool
	{
		return $this->value instanceof FileUpload
			? $this->value->isOk()
			: $this->value && Arrays::every($this->value, function (FileUpload $upload): bool {
				return $upload->isOk();
			});
	}


	/** @return static */
	public function addRule($validator, $errorMessage = null, $arg = null)
	{
		if ($validator === Form::Image) {
			$this->control->accept = implode(', ', FileUpload::IMAGE_MIME_TYPES);

		} elseif ($validator === Form::MimeType) {
			$this->control->accept = implode(', ', (array) $arg);

		} elseif ($validator === Form::MaxFileSize) {
			if ($arg > ($ini = Forms\Helpers::iniGetSize('upload_max_filesize'))) {
				trigger_error("Value of MaxFileSize ($arg) is greater than value of directive upload_max_filesize ($ini).", E_USER_WARNING);
			}
			$this->getRules()->removeRule($validator);

		} elseif ($validator === Form::MaxLength) {
			if ($arg > ($ini = ini_get('max_file_uploads'))) {
				trigger_error("Value of MaxLength ($arg) is greater than value of directive max_file_uploads ($ini).", E_USER_WARNING);
			}
			$this->getRules()->removeRule($validator);
		}

		return parent::addRule($validator, $errorMessage, $arg);
	}
}
