<?php

namespace CF\API\Exception;

class ZoneSettingFailException extends CloudFlareException
{
    protected $message = 'Oops, something went wrong, please try again in a few minutes';
}
