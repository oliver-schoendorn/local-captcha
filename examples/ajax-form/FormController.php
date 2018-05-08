<?php

use OS\LocalCaptcha\LocalCaptcha;

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

class FormController
{
    private function getLocalCaptcha(): LocalCaptcha
    {
        return new LocalCaptcha('some-unique-form-id', 'some-static-encryption-key');
    }

    public function getForm()
    {
        /*
         * the form generator will return a structure like this:
         *  {
         *      metaData: {
         *          name: 'encoded-field-name',
         *          value: 'encoded-field-value'
         *      },
         *      honeypots: [
         *          {
         *              id: 'input-id', // must be set in order for the javascript to work,
         *              placeholder: 'some-string-telling-the-user-not-to-insert-content-in-the-honeypot',
         *              type: 'input-type',
         *              name: 'encoded-input-name',
         *              style: { position: 'absolute', ... } // optional
         *              javascript: 'executable-javascript' // optional
         *          }, ...
         *      ],
         *      javascriptHelper: 'executable-javascript' // should only be executed once
         *  }
         */
        $formData = $this->getLocalCaptcha()->generator()->toJson([
            'list-of',
            'your-field-names',
            'you want to',
            'add_to_your_form',
            'username',
            'email'
        ]);
        die($formData);
    }

    public function postForm()
    {
        $submittedData = $_POST;

        // throws exceptions if the data is invalid
        $formData = $this->getLocalCaptcha()->validate($submittedData);

        // Laravel form validation
        $this->validate($formData, [
            'username' => 'alpha|min:3',
            'email' => 'email|unique:users'
        ]);

        // Save new user
        $user = new User();
        $user->name = $formData['username'];
        $user->email = $formData['email'];
        $user->save();
    }
}
