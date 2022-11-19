<?php

namespace CF\Integration;

interface ConfigInterface
{
    /**
     * @param $key
     *
     * @return mixed
     */
    public function getValue($key);
}
