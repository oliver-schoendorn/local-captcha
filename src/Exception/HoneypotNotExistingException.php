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

namespace OS\LocalCaptcha\Exception;


/**
 * Class HoneypotNotExistingException
 * @package OS\LocalCaptcha\Exception
 * @codeCoverageIgnore
 */
class HoneypotNotExistingException extends TuringTestException
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
