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

namespace OS\LocalCaptcha\Helper;


class Inspector
{
    /**
     * @var object
     */
    private $subject;

    /**
     * @var \ReflectionClass
     */
    private $reflection;

    /**
     * Inspector constructor.
     *
     * @param object $subject
     *
     * @throws \ReflectionException
     */
    public function __construct($subject)
    {
        $this->subject = $subject;
        $this->reflection = new \ReflectionClass($subject);
    }

    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function invoke(string $method, array $arguments = [])
    {
        $methodReflection = $this->reflection->getMethod($method);
        $methodReflection->setAccessible(true);
        return $methodReflection->invokeArgs($this->subject, $arguments);
    }

    /**
     * @param string $property
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function get(string $property)
    {
        $property = $this->getProperty($property);
        return $property->getValue($this->subject);
    }

    /**
     * @param string $property
     * @param mixed  $value
     *
     * @throws \ReflectionException
     */
    public function set(string $property, $value)
    {
        $property = $this->getProperty($property);
        $property->setValue($this->subject, $value);
    }

    /**
     * @param string $property
     *
     * @return \ReflectionProperty
     *
     * @throws \ReflectionException
     */
    private function getProperty(string $property): \ReflectionProperty
    {
        $property = $this->reflection->getProperty($property);
        $property->setAccessible(true);
        return $property;
    }
}
