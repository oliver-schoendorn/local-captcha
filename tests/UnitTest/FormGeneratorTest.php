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

use OS\LocalCaptcha\Exception\DecryptionException;
use OS\LocalCaptcha\FormGenerator;
use OS\LocalCaptcha\FormMetaData;
use OS\LocalCaptcha\Helper\EncryptionHelper;
use OS\LocalCaptcha\Helper\Inspector;
use OS\LocalCaptcha\Helper\SigningHelper;
use OS\LocalCaptcha\View\Honeypot\AbstractHoneypotInput;
use PHPUnit\Framework\TestCase;

class FormGeneratorTest extends TestCase
{
    /**
     * @var EncryptionHelper
     */
    private $encryptionHelper;

    /**
     * @var SigningHelper
     */
    private $signingHelper;

    /**
     * @var FormGenerator
     */
    private $formGenerator;

    /**
     * @var Inspector
     */
    private $formGeneratorInspector;

    public function setUp()
    {
        $this->signingHelper = new SigningHelper('enc-sig');
        $this->encryptionHelper = new EncryptionHelper('enc-key');

        $this->formGenerator = new FormGenerator(
            'some-form-id',
            new \DateTimeImmutable(),
            $this->encryptionHelper,
            $this->signingHelper
        );
        $this->formGeneratorInspector = new Inspector($this->formGenerator);

        parent::setUp();
    }

    public function testConstructor()
    {
        $honeypots = $this->formGenerator->getHoneypots();
        verify($honeypots)->containsOnlyInstancesOf(AbstractHoneypotInput::class);

        /** @var FormMetaData $metaData */
        $metaData = $this->formGenerator->getMetaData();
        verify($metaData)->isInstanceOf(FormMetaData::class);
        verify($metaData->getHoneypots())->equals(array_keys($honeypots));
        foreach ($metaData->getHoneypots() as $fieldName) {
            verify($fieldName)->equals($honeypots[$fieldName]->getName());
        }
    }

    public function testRenderJavascriptHelper()
    {
        $javascriptHelper = $this->formGenerator->renderJavascriptHelper();
        verify($javascriptHelper)->contains('<script>');
        verify($javascriptHelper)->contains('</script>');
        verify($javascriptHelper)->contains('window.LocalCaptcha');
    }

    public function testRenderMetaDataField()
    {
        $metaData = $this->formGenerator->getMetaData();
        $expectedFieldName = base64_encode($this->encryptionHelper->hash('meta-data'));
        $renderedMetaData = $this->formGenerator->renderMetaDataField();

        verify($renderedMetaData)->contains('<input type="hidden"');
        verify($renderedMetaData)->contains('name="' . $expectedFieldName . '"');

        preg_match('/value\=\"(.*)\"/', $renderedMetaData, $matches);
        $encryptedFieldValue = base64_decode($matches[1]);
        verify($this->signingHelper->verify($encryptedFieldValue))->true();

        $decryptedFieldValue = $this->encryptionHelper->decrypt($this->signingHelper->getPayload($encryptedFieldValue));
        verify($decryptedFieldValue)->equals(json_encode($metaData));
    }

    public function testRenderHoneypotFields()
    {
        $renderedHoneypots = $this->formGenerator->renderHoneypotFields();
        foreach ($this->formGenerator->getHoneypots() as $honeypot) {
            verify($renderedHoneypots)->contains($honeypot->toHTML());
        }
    }

    public function fieldNameProvider()
    {
        $fieldNames = [
            'hello world',
            'aNiceFieldName',
            'some\\spe\'i"al$c4r§'
        ];

        foreach ($fieldNames as $key => $value) {
            unset($fieldNames[$key]);
            $fieldNames[$value] = [ $value ];
        }

        return $fieldNames;
    }

    /**
     * @param string $fieldName
     * @dataProvider fieldNameProvider
     *
     * @throws DecryptionException
     */
    public function testGetFieldName($fieldName)
    {
        $actualFieldName = base64_decode($this->formGenerator->getFieldName($fieldName));
        verify($actualFieldName)->contains('_e:');

        list(, $encryptedFieldName) = explode(':', $actualFieldName, 2);
        verify($this->encryptionHelper->decrypt($encryptedFieldName))->equals($fieldName);
    }

    public function testToJson()
    {
        $formJson = json_decode($this->formGenerator->toJson([ 'foo', 'bar', 'baz' ]));
        verify(base64_decode($formJson->metaData->name))
            ->equals($this->encryptionHelper->hash('meta-data'));

        $decryptedMetaData = $this->encryptionHelper->decrypt(
            $this->signingHelper->getPayload(base64_decode($formJson->metaData->value)));
        verify($decryptedMetaData)->equals(json_encode($this->formGenerator->getMetaData()));

        $expectedHoneypots = $this->formGenerator->getHoneypots();
        foreach ($formJson->honeypots as $name => $honeypot) {
            verify(array_key_exists($name, $expectedHoneypots))->true();
            verify(array_key_exists('id', $honeypot))->true();
            verify(array_key_exists('name', $honeypot))->true();
            verify(array_key_exists('placeholder', $honeypot))->true();
        }
    }
}
