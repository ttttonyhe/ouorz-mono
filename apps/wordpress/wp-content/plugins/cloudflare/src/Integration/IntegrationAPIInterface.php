<?php

namespace CF\Integration;

use CF\DNSRecord;

interface IntegrationAPIInterface
{
    /**
     * @param $domain_name
     *
     * @return mixed
     */
    public function getDNSRecords($domain_name);

    /**
     * @param $domain_name
     * @param DNSRecord $DNSRecord
     *
     * @return mixed
     */
    public function addDNSRecord($domain_name, DNSRecord $DNSRecord);

    /**
     * @param $domain_name
     * @param DNSRecord $DNSRecord
     *
     * @return mixed
     */
    public function editDNSRecord($domain_name, DNSRecord $DNSRecord);

    /**
     * @param $domain_name
     * @param DNSRecord $DNSRecord
     *
     * @return mixed
     */
    public function removeDNSRecord($domain_name, DNSRecord $DNSRecord);

    /**
     * @return mixed
     */
    public function getHostAPIKey();

    /**
     * @param null $userId
     *
     * @return mixed
     */
    public function getDomainList($userId = null);

    /**
     * @return mixed
     */
    public function getUserId();
}
