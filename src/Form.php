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

abstract class Form
{
    /**
     * @var string
     */
    protected $formId;

    /**
     * @var EncryptionHelper
     */
    protected $encryptionHelper;

    /**
     * @var SigningHelper
     */
    protected $signingHelper;

    /**
     * Form constructor.
     *
     * @param string $formId A unique ID that identifies the current form (e.g. 'contact-form-1').
     * @param EncryptionHelper $encryptionHelper
     * @param SigningHelper $signingHelper
     */
    public function __construct(string $formId, EncryptionHelper $encryptionHelper, SigningHelper $signingHelper)
    {
        $this->formId = $formId;
        $this->encryptionHelper = $encryptionHelper;
        $this->signingHelper = $signingHelper;
    }
}
