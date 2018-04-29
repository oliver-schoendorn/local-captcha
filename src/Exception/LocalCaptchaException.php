<?php

namespace OS\LocalCaptcha\Exception;


class LocalCaptchaException extends \Exception
{
    const CODE_MISSING_META_DATA_EXCEPTION = 1;
    const CODE_INVALID_SIGNATURE_EXCEPTION = 2;
    const CODE_DECRYPTION_EXCEPTION        = 4;
}
