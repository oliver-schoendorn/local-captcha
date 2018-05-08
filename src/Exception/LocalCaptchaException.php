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


class LocalCaptchaException extends \Exception
{
    const CODE_UNEXPECTED_EXCEPTION            = 1;
    const CODE_MISSING_META_DATA_EXCEPTION     = 2;
    const CODE_INVALID_SIGNATURE_EXCEPTION     = 4;
    const CODE_INVALID_META_DATA_EXCEPTION     = 8;
    const CODE_DECRYPTION_EXCEPTION            = 16;
    const CODE_TIMING_EXCEPTION                = 32;
    const CODE_INVALID_FORM_ID_EXCEPTION       = 64;
    const CODE_HONEYPOT_NOT_EMPTY_EXCEPTION    = 128;
    const CODE_HONEYPOT_NOT_EXISTING_EXCEPTION = 256;
}
