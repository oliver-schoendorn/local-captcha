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

namespace OS\LocalCaptcha\View;


class InputField implements Input
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * AbstractInput constructor.
     *
     * @param string $name
     * @param mixed $value
     * @param string $type
     * @param array $attributes
     */
    public function __construct(string $name, $value, string $type, array $attributes = [])
    {
        $this->name = $name;
        $this->value = $value;
        $this->type = $type;
        $this->attributes = $attributes;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function toHTML(): string
    {
        return sprintf(
            '<input name="%s" type="%s" value="%s" %s/>',
            $this->getName(),
            $this->getType(),
            $this->getValue(),
            $this->attributesToHTML()
        );
    }

    private function attributesToHTML(): string
    {
        $attributes = [];
        foreach ($this->getAttributes() as $attributeKey => $attributeValue) {
            if (is_array($attributeValue)) {
                $values = [];
                foreach ($attributeValue as $key => $value) {
                    array_push($values, $key . ': ' . $value);
                }
                $attributeValue = implode(';', $values);
            }
            if (is_string($attributeValue)) {
                $attributeValue = str_replace('"', '\\"', $attributeValue);
            }
            array_push($attributes, $attributeKey . '="' . $attributeValue . '"');
        }
        return implode(' ', $attributes);
    }

    public function jsonSerialize()
    {
        return array_merge_recursive($this->getAttributes(), [
            'name' => $this->getName(),
            'type' => $this->getType(),
            'value' => $this->getValue()
        ]);
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function __toString()
    {
        return $this->getName();
    }
}
