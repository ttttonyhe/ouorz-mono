<?php

namespace CF\Integration;

interface DataStoreInterface
{
    /**
     * @param $client_api_key
     * @param $email
     * @param $unique_id
     * @param $user_key
     *
     * @return mixed
     */
    public function createUserDataStore($client_api_key, $email, $unique_id, $user_key);

    /**
     * @return mixed
     */
    public function getHostAPIUserUniqueId();

    /**
     * @return mixed
     */
    public function getClientV4APIKey();

    /**
     * @return mixed
     */
    public function getHostAPIUserKey();

    /**
     * @return mixed
     */
    public function getCloudFlareEmail();

    /**
     * @param $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function set($key, $value);
}
