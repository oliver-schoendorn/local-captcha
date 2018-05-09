<?php
/**
 * Copyright (c) 2018 Oliver Schöndorn
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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

    /**
     * @return FormGenerator
     * @throws \Exception
     */
    public function generator(): FormGenerator
    {
        return new FormGenerator($this->formId, new \DateTimeImmutable('now'), $this->encryptionHelper, $this->signingHelper);
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
                $this->logThrowable($throwable, $submittedData);
            }
            throw $throwable;
        }

        return $formData;
    }

    private function logThrowable(\Throwable $throwable, array $submittedData)
    {
        switch (true) {
            case $throwable instanceof TuringTestException:
                $this->logger->debug('LocalCaptcha: Turing test failed', [
                    'formId' => $this->formId,
                    'exception' => $throwable,
                    'submittedData' => $submittedData
                ]);
                break;

            case $throwable instanceof LocalCaptchaException:
                $this->logger->error('LocalCaptcha: Error', [
                    'formId' => $this->formId,
                    'exception' => $throwable
                ]);
                break;

            // @codeCoverageIgnoreStart
            default:
                $this->logger->critical('LocalCaptcha: Unexpected exception', [
                    'formId' => $this->formId,
                    'exception' => $throwable
                ]);
                break;
            // @codeCoverageIgnoreEnd
        }
    }
}
