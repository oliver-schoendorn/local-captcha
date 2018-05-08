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
 * Class HoneypotNotEmptyException
 * @package OS\LocalCaptcha\Exception
 * @codeCoverageIgnore
 */
class HoneypotNotEmptyException extends TuringTestException
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
