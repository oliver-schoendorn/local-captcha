<?php

namespace OS\LocalCaptcha\Exception;


class LocalCaptchaException extends \Exception
{
    const CODE_MISSING_META_DATA_EXCEPTION     = 1;
    const CODE_INVALID_SIGNATURE_EXCEPTION     = 2;
    const CODE_INVALID_META_DATA_EXCEPTION     = 4;
    const CODE_DECRYPTION_EXCEPTION            = 8;
    const CODE_TIMING_EXCEPTION                = 16;
    const CODE_INVALID_FORM_ID_EXCEPTION       = 32;
    const CODE_HONEYPOT_NOT_EMPTY_EXCEPTION    = 64;
    const CODE_HONEYPOT_NOT_EXISTING_EXCEPTION = 128;
}
