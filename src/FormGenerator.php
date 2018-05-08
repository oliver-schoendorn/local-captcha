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


use OS\LocalCaptcha\Helper\EncryptionHelper;
use OS\LocalCaptcha\Helper\SigningHelper;
use OS\LocalCaptcha\View\Honeypot\AbstractHoneypotInput;
use OS\LocalCaptcha\View\Honeypot\HiddenHoneypotInput;
use OS\LocalCaptcha\View\Honeypot\HiddenUsingJavascriptHoneypotInput;
use OS\LocalCaptcha\View\Honeypot\OutsideViewportHoneypotInput;
use OS\LocalCaptcha\View\Honeypot\OutsideViewportUsingJavascriptHoneypotInput;
use OS\LocalCaptcha\View\Honeypot\ZeroSizedHoneypotInput;
use OS\LocalCaptcha\View\Input;

class FormGenerator extends Form
{
    /**
     * @var FormMetaData
     */
    private $metaData;

    /**
     * @var Input[]
     */
    private $honeypots = [];

    /**
     * FormGenerator constructor.
     *
     * @param string $formId
     * @param \DateTimeImmutable $generationDate
     * @param EncryptionHelper $encryptionHelper
     * @param SigningHelper $signingHelper
     */
    public function __construct(
        string $formId,
        \DateTimeImmutable $generationDate,
        EncryptionHelper $encryptionHelper,
        SigningHelper $signingHelper
    ) {
        parent::__construct($formId, $encryptionHelper, $signingHelper);
        $this->honeypots = $this->generateHoneypots();
        $this->metaData = new FormMetaData($this->formId, $generationDate, array_keys($this->honeypots));
    }

    /**
     * @return FormMetaData
     */
    public function getMetaData(): FormMetaData
    {
        return $this->metaData;
    }

    /**
     * @return Input[]
     */
    public function getHoneypots(): array
    {
        return $this->honeypots;
    }

    private function generateHoneypots(): array
    {
        $honeypots = [];
        $availableHoneypots = [
            HiddenHoneypotInput::class,
            HiddenUsingJavascriptHoneypotInput::class,
            OutsideViewportHoneypotInput::class,
            OutsideViewportUsingJavascriptHoneypotInput::class,
            ZeroSizedHoneypotInput::class
        ];

        for ($i = 0; $i < rand(4, 8); ++$i) {
            $randomHoneypot = $availableHoneypots[rand(0, count($availableHoneypots)-1)];
            $randomName = base64_encode($this->encryptionHelper->encrypt(uniqid('honey', true)));
            $honeypots[$randomName] = new $randomHoneypot($randomName);
        }

        return $honeypots;
    }

    public function renderJavascriptHelper(): string
    {
        return '<script>' . $this->getJavascriptHelper() . '</script>';
    }

    private function getJavascriptHelper(): string
    {
        ob_start();
        include(__DIR__ . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'LocalCaptcha.js');
        return ob_get_clean();
    }

    public function renderMetaDataField(): string
    {
        $fieldName = $this->getEncodedMetaDataFieldName();
        $fieldValue = $this->getEncodedMetaDataFieldValue($this->getMetaData());

        return '<input type="hidden" name="' . $fieldName . '" value="' . $fieldValue . '" />';
    }

    private function getEncodedMetaDataFieldName(): string
    {
        return base64_encode($this->encryptionHelper->hash('meta-data'));
    }

    private function getEncodedMetaDataFieldValue(FormMetaData $formMetaData): string
    {
        return base64_encode($this->signingHelper->sign(
            $this->encryptionHelper->encrypt(json_encode($formMetaData))
        ));
    }

    public function renderHoneypotFields(): string
    {
        $output = '';
        foreach ($this->honeypots as $honeypot) {
            $output .= $honeypot->toHTML();
        }

        return $output;
    }

    /**
     * Encrypts and encodes the given field name.
     *
     * @param string $fieldName
     *
     * @return string
     */
    public function getFieldName(string $fieldName): string
    {
        return base64_encode('_e:' . $this->encryptionHelper->encrypt($fieldName));
    }

    /**
     * Returns a JSON representation of the form
     *
     * @param string[] $fieldNames
     *
     * @return string
     */
    public function toJson(array $fieldNames): string
    {
        return json_encode([
            'metaData' => (object) [
                'name' => $this->getEncodedMetaDataFieldName(),
                'value' => $this->getEncodedMetaDataFieldValue($this->getMetaData())
            ],
            'honeypots' => $this->getHoneypots(),
            'javascriptHelper' => $this->getJavascriptHelper(),
            'fields' => array_combine($fieldNames, array_map([ $this, 'getFieldName' ], $fieldNames))
        ]);
    }
}
