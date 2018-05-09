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


use OS\LocalCaptcha\View\InputField;

abstract class AbstractHoneypotInput extends InputField
{
    /**
     * @var int
     */
    private static $honeypotFieldCounter = 1;

    /**
     * @var string
     */
    private $uniqueId;

    /**
     * @var string
     */
    private $javascript;

    /**
     * AbstractHoneypotInput constructor.
     *
     * @param string $name
     * @param mixed  $value
     * @param string $type
     * @param array  $attributes
     * @param string $javascript
     */
    public function __construct(
        string $name,
        $value = '',
        string $type = 'text',
        array $attributes = [],
        string $javascript = null
    ) {
        parent::__construct($name, $value, $type, $attributes);
        $this->javascript = $javascript;
        $this->uniqueId = base64_encode(uniqid(++AbstractHoneypotInput::$honeypotFieldCounter));
    }

    public function getAttributes(): array
    {
        $attributes = parent::getAttributes();
        $this->addToAttributes($attributes, 'id', $this->uniqueId);
        $this->addToAttributes($attributes, 'placeholder',
            'Do not enter anything here. This field is used to tell humans from computers apart to reduce spam.');

        return $attributes;
    }

    private function addToAttributes(array &$attributes, string $key, $value, string $separator = ' ')
    {
        if (array_key_exists($key, $attributes)) {
            $attributes[$key] .= $separator . $value;
        }
        else {
            $attributes[$key] = $value;
        }
    }

    protected function addToStyles(array &$attributes, string $key, $value)
    {
        if ( ! array_key_exists('style', $attributes)) {
            $attributes['style'] = [ $key => $value ];
        }
        else if(is_string($attributes['style'])) {
            $attributes['style'] .= '; ' . $key . ': ' . $value;
        }
        else if (is_array($attributes['style'])) {
            $attributes['style'][$key] = $value;
        }
    }

    public function toHTML(): string
    {
        return parent::toHTML() . $this->javascriptToHTML();
    }

    private function javascriptToHTML(): string
    {
        $javascript = $this->getJavascript();
        return empty($javascript)
            ? ''
            : PHP_EOL . sprintf('<script>%s</script>', $javascript);
    }

    public function getJavascript()
    {
        if ( ! $this->javascript) {
            return '';
        }

        return sprintf(
            'window.LocalCaptcha.onLoad(function() { var uniqueId = %s; %s });',
            json_encode($this->uniqueId),
            $this->javascript
        );
    }

    /**
     * @param string $relativePath
     *
     * @return string
     * @throws \Exception
     */
    protected function loadJavascript(string $relativePath): string
    {
        $path = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'Javascript' . DIRECTORY_SEPARATOR . $relativePath);
        if ( ! $path) { throw new \Exception('LocalCaptcha: Javascript not found.'); } // @codeCoverageIgnore

        ob_start();
        include $path;
        return ob_get_clean();
    }

    public function jsonSerialize()
    {
        return array_merge_recursive(parent::jsonSerialize(), [
            'javascript' => $this->getJavascript()
        ]);
    }
}
