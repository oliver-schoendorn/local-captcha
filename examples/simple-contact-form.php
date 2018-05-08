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

use OS\LocalCaptcha\LocalCaptcha;

require_once __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/../vendor/autoload.php');


$localCaptcha = new LocalCaptcha('contact-form-2', 'fooBarABc');

if ( ! array_key_exists('submitted', $_POST) || $_POST['submitted'] != '1') {
    $formGenerator = $localCaptcha->generator();
    ?>
    <html>
    <head><title>Form</title></head>
    <body>

    <form method='post' action='<?= $_SERVER['PHP_SELF']; ?>'>
        <?= $formGenerator->renderJavascriptHelper(); ?>
        <?= $formGenerator->renderMetaDataField(); ?>
        <?= $formGenerator->renderHoneypotFields(); ?>

        <input type='hidden' name='submitted' value='1' />
        <input type='text' name='<?= $formGenerator->getFieldName('name'); ?>' />

        <input type='submit' />
    </form>

    </body>
    </html>
    <?php
}
else {
    $formData = $localCaptcha->validate($_POST);
    $parsedData = $formData->getData();
    var_dump($parsedData);
}
