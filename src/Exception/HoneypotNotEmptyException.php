<?php

namespace OS\LocalCaptcha\Exception;


class HoneypotNotEmptyException extends LocalCaptchaException
{
    /**
     * @var string
     */
    public $fieldName;

    /**
     * @var mixed
     */
    public $fieldValue;

    /**
     * HoneypotNotEmptyException constructor.
     *
     * @param string $fieldName
     * @param mixed  $fieldValue
     */
    public function __construct(string $fieldName, $fieldValue)
    {
        parent::__construct('Honeypot field not empty.', $this::CODE_HONEYPOT_NOT_EMPTY_EXCEPTION);
        $this->fieldName = $fieldName;
        $this->fieldValue = $fieldValue;
    }
}
