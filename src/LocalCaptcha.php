<?php

namespace OS\LocalCaptcha;


use OS\LocalCaptcha\Helper\EncryptionHelper;
use OS\LocalCaptcha\Helper\SigningHelper;

class LocalCaptcha
{
    /**
     * @var SigningHelper
     */
    private $signingHelper;

    /**
     * @var EncryptionHelper
     */
    private $encryptionHelper;

    /**
     * LocalCaptcha constructor.
     *
     * @param string $formId
     * @param string $encryptionKey
     */
    public function __construct(string $formId, string $encryptionKey = 'local-captcha')
    {
        $this->signingHelper = new SigningHelper($formId . ':' . $encryptionKey);
        $this->encryptionHelper = new EncryptionHelper($formId . ':' . $encryptionKey);
    }

    public function generator()
    {

    }

    public function validate(array $submittedData)
    {

    }
}
