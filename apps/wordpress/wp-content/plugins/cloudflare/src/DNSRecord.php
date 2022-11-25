<?php
/**
 * Created by IntelliJ IDEA.
 * User: johnwineman
 * Date: 4/18/16
 * Time: 2:40 PM.
 */

namespace CF;

class DNSRecord
{
    private $content;
    private $name;
    private $ttl;
    private $type;

    public static $DNS_RECORDS_CF_CANNOT_PROXY = array('LOC', 'MX', 'NS', 'SPF', 'TXT', 'SRV', ':RAW', '$TTL', 'SOA');

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * @param mixed $ttl
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
