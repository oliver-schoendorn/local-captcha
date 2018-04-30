<?php

namespace OS\LocalCaptcha;


use OS\LocalCaptcha\Exception\LocalCaptchaException;
use OS\LocalCaptcha\Exception\TuringTestException;
use OS\LocalCaptcha\Helper\EncryptionHelper;
use OS\LocalCaptcha\Helper\SigningHelper;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class LocalCaptcha implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    private $formId;

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
     * @param LoggerInterface $logger
     */
    public function __construct(string $formId, string $encryptionKey = 'local-captcha', LoggerInterface $logger = null)
    {
        $this->formId = $formId;
        $this->signingHelper = new SigningHelper($formId . ':' . $encryptionKey);
        $this->encryptionHelper = new EncryptionHelper($formId . ':' . $encryptionKey);
        $this->logger = $logger;
    }

    public function generator()
    {

    }

    /**
     * @param array $submittedData
     *
     * @return FormData
     *
     * @throws \Throwable
     */
    public function validate(array $submittedData): FormData
    {
        $formData = new FormData($this->formId, $submittedData, $this->encryptionHelper, $this->signingHelper);
        try {
            $formData->validate();
        }
        catch (\Throwable $throwable) {
            if ($this->logger) {
                $this->logThrowable($throwable);
            }
            throw $throwable;
        }

        return $formData;
    }

    private function logThrowable(\Throwable $throwable)
    {
        switch (true) {
            case $throwable instanceof TuringTestException:
                $this->logger->debug('LocalCaptcha: Turing test failed', [ 'exception' => $throwable ]);
                break;

            case $throwable instanceof LocalCaptchaException:
                $this->logger->error('LocalCaptcha: Error', [ 'exception' => $throwable ]);
                break;

            default:
                $this->logger->critical('LocalCaptcha: Unexpected exception', [ 'exception' => $throwable ]);
                break;
        }
    }
}
