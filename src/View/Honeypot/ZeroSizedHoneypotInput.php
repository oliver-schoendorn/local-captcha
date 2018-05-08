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


class ZeroSizedHoneypotInput extends AbstractHoneypotInput
{
    public function getAttributes(): array
    {
        $attributes = parent::getAttributes();
        $this->addToStyles($attributes, 'height', '0');
        $this->addToStyles($attributes, 'width', '0');
        $this->addToStyles($attributes, 'padding', '0');
        $this->addToStyles($attributes, 'margin', '0');
        $this->addToStyles($attributes, 'position', 'absolute');
        $this->addToStyles($attributes, 'overflow', 'hidden');
        $this->addToStyles($attributes, 'visibility', 'hidden');
        return $attributes;
    }
}
