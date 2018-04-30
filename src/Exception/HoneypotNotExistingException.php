<?php

namespace OS\LocalCaptcha\Exception;


class HoneypotNotExistingException extends LocalCaptchaException
{
    /**
     * @var string
     */
    public $fieldName;

    /**
     * HoneypotNotExistingException constructor.
     *
     * @param string $fieldName
     */
    public function __construct(string $fieldName)
    {
        parent::__construct('Required field is missing.', $this::CODE_HONEYPOT_NOT_EXISTING_EXCEPTION);
        $this->fieldName = $fieldName;
    }
}
