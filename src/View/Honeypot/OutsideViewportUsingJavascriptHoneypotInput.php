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

namespace OS\LocalCaptcha\View\Honeypot;


class OutsideViewportUsingJavascriptHoneypotInput extends AbstractHoneypotInput
{
    /**
     * HiddenUsingJavascriptHoneypotInput constructor.
     *
     * @param string $name
     * @param array  $attributes
     *
     * @throws \Exception
     */
    public function __construct(
        string $name,
        array $attributes = []
    )
    {
        $javascript = $this->loadJavascript('OutsideViewport.js');
        parent::__construct($name, '', 'text', $attributes, $javascript);
    }
}
