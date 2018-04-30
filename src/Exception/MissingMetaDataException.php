<?php

namespace OS\LocalCaptcha\Exception;


class MissingMetaDataException extends TuringTestException
{
    public function __construct()
    {
        parent::__construct('The submitted form input is missing the required meta data.', 100);
    }
}
