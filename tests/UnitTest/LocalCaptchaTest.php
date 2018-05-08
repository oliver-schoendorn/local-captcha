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

namespace OS\LocalCaptcha\UnitTest;

use OS\LocalCaptcha\Exception\LocalCaptchaException;
use OS\LocalCaptcha\Exception\TuringTestException;
use OS\LocalCaptcha\FormData;
use OS\LocalCaptcha\FormGenerator;
use OS\LocalCaptcha\Helper\Inspector;
use OS\LocalCaptcha\LocalCaptcha;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LocalCaptchaTest extends TestCase
{
    public function testGenerator()
    {
        $localCaptcha = new LocalCaptcha('form-id', 'enc-key');
        $generator = $localCaptcha->generator();

        $localCaptchaInspector = new Inspector($localCaptcha);
        $generatorInspector = new Inspector($generator);

        verify($localCaptchaInspector->get('formId'))->same($generatorInspector->get('formId'));
        verify($localCaptchaInspector->get('encryptionHelper'))->same($generatorInspector->get('encryptionHelper'));
        verify($localCaptchaInspector->get('signingHelper'))->same($generatorInspector->get('signingHelper'));
    }

    public function testValidateWithValidData()
    {
        $localCaptcha = new LocalCaptcha('form-id2', 'enc-key2');
        /** @var FormGenerator $formGenerator */
        list($formGenerator, $metaDataKey, $metaDataValue, $honeypotData) = $this->prepareTestValidate($localCaptcha);

        $submittedData = array_merge_recursive($honeypotData, [
            $metaDataKey => $metaDataValue,
            $formGenerator->getFieldName('foo') => 'bar'
        ]);

        $formData = $localCaptcha->validate($submittedData);
        verify($formData)->isInstanceOf(FormData::class);
        verify($formData['foo'])->equals('bar');
    }

    public function testValidateWithTuringTestException()
    {
        $logger = $this->getLoggerMock();
        $logger->expects($this::once())->method('debug');
        $logger->expects($this::never())->method('error');
        $logger->expects($this::never())->method('critical');

        $localCaptcha = new LocalCaptcha('form-id2', 'enc-key2', $logger);
        /** @var FormGenerator $formGenerator */
        list($formGenerator, $metaDataKey, $metaDataValue, $honeypotData)
            = $this->prepareTestValidate($localCaptcha, '-2 seconds');

        $submittedData = array_merge_recursive($honeypotData, [
            $metaDataKey => $metaDataValue,
            $formGenerator->getFieldName('foo') => 'bar'
        ]);

        $this->expectException(TuringTestException::class);
        $this->expectExceptionCode(LocalCaptchaException::CODE_TIMING_EXCEPTION);
        $localCaptcha->validate($submittedData);
    }

    public function testValidateWithLocalCaptchaException()
    {
        $logger = $this->getLoggerMock();
        $logger->expects($this::never())->method('debug');
        $logger->expects($this::once())->method('error');
        $logger->expects($this::never())->method('critical');

        $localCaptcha = new LocalCaptcha('form-id2', 'enc-key2', $logger);
        /** @var FormGenerator $formGenerator */
        list($formGenerator, $metaDataKey, $metaDataValue, $honeypotData)
            = $this->prepareTestValidate($localCaptcha, '-2 seconds');

        $submittedData = array_merge_recursive($honeypotData, [
            $metaDataKey => $metaDataValue . 'abc',
            $formGenerator->getFieldName('foo') => 'bar'
        ]);

        $this->expectException(LocalCaptchaException::class);
        $this->expectExceptionCode(LocalCaptchaException::CODE_INVALID_SIGNATURE_EXCEPTION);
        $localCaptcha->validate($submittedData);
    }

    private function prepareTestValidate(LocalCaptcha $localCaptcha, string $generationDate = '-2 minutes'): array
    {
        $localCaptchaInspector = new Inspector($localCaptcha);
        $encryptionHelper = $localCaptchaInspector->get('encryptionHelper');
        $signingHelper = $localCaptchaInspector->get('signingHelper');

        $formGenerator = new FormGenerator(
            'form-id2',
            new \DateTimeImmutable($generationDate),
            $encryptionHelper,
            $signingHelper
        );

        $metaDataKey = base64_encode($encryptionHelper->hash('meta-data'));
        $metaDataValue = json_encode($formGenerator->getMetaData());
        $metaDataValue = base64_encode($signingHelper->sign($encryptionHelper->encrypt($metaDataValue)));

        $submittedData = [];
        foreach ($formGenerator->getHoneypots() as $honeypot) {
            $submittedData[$honeypot->getName()] = '';
        }

        return [
            $formGenerator,
            $metaDataKey,
            $metaDataValue,
            $submittedData
        ];
    }

    private function getLoggerMock(): MockObject
    {
        return $this->getMockBuilder(LoggerInterface::class)->setMethods([
            'debug',
            'error',
            'critical'
        ])->getMockForAbstractClass();
    }
}
