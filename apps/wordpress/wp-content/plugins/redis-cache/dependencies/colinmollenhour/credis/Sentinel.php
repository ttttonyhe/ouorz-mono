<?php
/**
 * Credis_Sentinel
 *
 * Implements the Sentinel API as mentioned on http://redis.io/topics/sentinel.
 * Sentinel is aware of master and slave nodes in a cluster and returns instances of Credis_Client accordingly.
 *
 * The complexity of read/write splitting can also be abstract by calling the createCluster() method which returns a
 * Credis_Cluster object that contains both the master server and a random slave. Credis_Cluster takes care of the
 * read/write splitting
 *
 * @author Thijs Feryn <thijs@feryn.eu>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package Credis_Sentinel
 */
class Credis_Sentinel
{
    /**
     * Contains a client that connects to a Sentinel node.
     * Sentinel uses the same protocol as Redis which makes using Credis_Client convenient.
     * @var Credis_Client
     */
    protected $_client;

    /**
     * Contains an active instance of Credis_Cluster per master pool
     * @var array
     */
    protected $_cluster = array();

    /**
     * Contains an active instance of Credis_Client representing a master
     * @var array
     */
    protected $_master = array();

    /**
     * Contains an array Credis_Client objects representing all slaves per master pool
     * @var array
     */
    protected $_slaves = array();

    /**
     * Use the phpredis extension or the standalone implementation
     * @var bool
     * @deprecated
     */
    protected $_standAlone = false;

    /**
     * Store the AUTH password used by Credis_Client instances
     * @var string
     */
    protected $_password = '';
    /**
     * Store the AUTH username used by Credis_Client instances (Redis v6+)
     * @var string
     */
    protected $_username = '';
    /**
     * @var null|float
     */
    protected $_timeout;
    /**
     * @var string
     */
    protected $_persistent;
    /**
     * @var int
     */
    protected $_db;
    /**
     * @var string|null
     */
    protected $_replicaCmd = null;
    /**
     * @var string|null
     */
    protected $_redisVersion = null;

  /**
     * Connect with a Sentinel node. Sentinel will do the master and slave discovery
     *
     * @param Credis_Client $client
     * @param string $password (deprecated - use setClientPassword)
     * @throws CredisException
     */
    public function __construct(Credis_Client $client, $password = NULL, $username = NULL)
    {
        $client->forceStandalone(); // SENTINEL command not currently supported by phpredis
        $this->_client     = $client;
        $this->_password   = $password;
        $this->_username   = $username;
        $this->_timeout    = NULL;
        $this->_persistent = '';
        $this->_db         = 0;
    }

    /**
     * Clean up client on destruct
     */
    public function __destruct()
    {
        $this->_client->close();
    }

    /**
     * @param float $timeout
     * @return $this
     */
    public function setClientTimeout($timeout)
    {
        $this->_timeout = $timeout;
        return $this;
    }

    /**
     * @param string $persistent
     * @return $this
     */
    public function setClientPersistent($persistent)
    {
        $this->_persistent = $persistent;
        return $this;
    }

    /**
     * @param int $db
     * @return $this
     */
    public function setClientDatabase($db)
    {
        $this->_db = $db;
        return $this;
    }

    /**
     * @param null|string $password
     * @return $this
     */
    public function setClientPassword($password)
    {
        $this->_password = $password;
        return $this;
    }

    /**
     * @param null|string $username
     * @return $this
     */
    public function setClientUsername($username)
    {
      $this->_username = $username;
      return $this;
    }

    /**
     * @param null|string $replicaCmd
     * @return $this
     */
    public function setReplicaCommand($replicaCmd)
    {
      $this->_replicaCmd = $replicaCmd;
      return $this;
    }

    public function detectRedisVersion()
    {
      if ($this->_redisVersion !== null && $this->_replicaCmd !== null) {
        return;
      }
      $serverInfo = $this->info('server');
      $this->_redisVersion =  $serverInfo['redis_version'];
      // Redis v7+ renames the replica command to 'replicas' instead of 'slaves'
      $this->_replicaCmd = version_compare($this->_redisVersion, '7.0.0', '>=') ? 'replicas' : 'slaves';
    }

    /**
     * @return Credis_Sentinel
     * @deprecated
     */
    public function forceStandalone()
    {
        $this->_standAlone = true;
        return $this;
    }

    /**
     * Discover the master node automatically and return an instance of Credis_Client that connects to the master
     *
     * @param string $name
     * @return Credis_Client
     * @throws CredisException
     */
    public function createMasterClient($name)
    {
        $master = $this->getMasterAddressByName($name);
        if(!isset($master[0]) || !isset($master[1])){
            throw new CredisException('Master not found');
        }
        return new Credis_Client($master[0], $master[1], $this->_timeout, $this->_persistent, $this->_db, $this->_password, $this->_username);
    }

    /**
     * If a Credis_Client object exists for a master, return it. Otherwise create one and return it
     * @param string $name
     * @return Credis_Client
     */
    public function getMasterClient($name)
    {
        if(!isset($this->_master[$name])){
            $this->_master[$name] = $this->createMasterClient($name);
        }
        return $this->_master[$name];
    }

    /**
     * Discover the slave nodes automatically and return an array of Credis_Client objects
     *
     * @param string $name
     * @return Credis_Client[]
     * @throws CredisException
     */
    public function createSlaveClients($name)
    {
        $slaves = $this->slaves($name);
        $workingSlaves = array();
        foreach($slaves as $slave) {
            if(!isset($slave[9])){
                throw new CredisException('Can\' retrieve slave status');
            }
            if(!strstr($slave[9],'s_down') && !strstr($slave[9],'disconnected')) {
                $workingSlaves[] = new Credis_Client($slave[3], $slave[5], $this->_timeout, $this->_persistent, $this->_db, $this->_password, $this->_username);
            }
        }
        return $workingSlaves;
    }

    /**
     * If an array of Credis_Client objects exist for a set of slaves, return them. Otherwise create and return them
     * @param string $name
     * @return Credis_Client[]
     */
    public function getSlaveClients($name)
    {
        if(!isset($this->_slaves[$name])){
            $this->_slaves[$name] = $this->createSlaveClients($name);
        }
        return $this->_slaves[$name];
    }

    /**
     * Returns a Redis cluster object containing a random slave and the master
     * When $selectRandomSlave is true, only one random slave is passed.
     * When $selectRandomSlave is false, all clients are passed and hashing is applied in Credis_Cluster
     * When $writeOnly is false, the master server will also be used for read commands.
     * When $masterOnly is true, only the master server will also be used for both read and write commands. $writeOnly will be ignored and forced to set to false.
     * @param string $name
     * @param int $db
     * @param int $replicas
     * @param bool $selectRandomSlave
     * @param bool $writeOnly
     * @param bool $masterOnly
     * @return Credis_Cluster
     * @throws CredisException
     * @deprecated
     */
    public function createCluster($name, $db=0, $replicas=128, $selectRandomSlave=true, $writeOnly=false, $masterOnly=false)
    {
        $clients = array();
        $workingClients = array();
        $master = $this->master($name);
        if(strstr($master[9],'s_down') || strstr($master[9],'disconnected')) {
            throw new CredisException('The master is down');
        }
        if (!$masterOnly) {
            $slaves = $this->slaves($name);
            foreach($slaves as $slave){
                if(!strstr($slave[9],'s_down') && !strstr($slave[9],'disconnected')) {
                    $workingClients[] =  array('host'=>$slave[3],'port'=>$slave[5],'master'=>false,'db'=>$db,'password'=>$this->_password);
                }
            }
            if(count($workingClients)>0){
                if($selectRandomSlave){
                    if(!$writeOnly){
                        $workingClients[] = array('host'=>$master[3],'port'=>$master[5],'master'=>false,'db'=>$db,'password'=>$this->_password);
                    }
                    $clients[] = $workingClients[rand(0,count($workingClients)-1)];
                } else {
                    $clients = $workingClients;
                }
            }
        } else {
            $writeOnly = false;
        }
        $clients[] = array('host'=>$master[3],'port'=>$master[5], 'db'=>$db ,'master'=>true,'write_only'=>$writeOnly,'password'=>$this->_password);
        return new Credis_Cluster($clients,$replicas,$this->_standAlone);
    }

    /**
     * If a Credis_Cluster object exists, return it. Otherwise create one and return it.
     * @param string $name
     * @param int $db
     * @param int $replicas
     * @param bool $selectRandomSlave
     * @param bool $writeOnly
     * @param bool $masterOnly
     * @return Credis_Cluster
     * @throws CredisException
     * @deprecated
     */
    public function getCluster($name, $db=0, $replicas=128, $selectRandomSlave=true, $writeOnly=false, $masterOnly=false)
    {
        if(!isset($this->_cluster[$name])){
            $this->_cluster[$name] = $this->createCluster($name, $db, $replicas, $selectRandomSlave, $writeOnly, $masterOnly);
        }
        return $this->_cluster[$name];
    }

    /**
     * Catch-all method
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        array_unshift($args,$name);
        return call_user_func(array($this->_client,'sentinel'),$args);
    }

    /**
     * get information block for the sentinel instance
     *
     * @param string|NUll $section
     *
     * @return array
     */
    public function info($section = null)
    {
        if ($section)
        {
            return $this->_client->info($section);
        }
        return $this->_client->info();
    }

    /**
     * Return information about all registered master servers
     * @return mixed
     */
    public function masters()
    {
        return $this->_client->sentinel('masters');
    }

    /**
     * Return all information for slaves that are associated with a single master
     * @param string $name
     * @return mixed
     */
    public function slaves($name)
    {
        if ($this->_replicaCmd === null) {
          $this->detectRedisVersion();
        }
        return $this->_client->sentinel($this->_replicaCmd,$name);
    }

    /**
     * Get the information for a specific master
     * @param string $name
     * @return mixed
     */
    public function master($name)
    {
        return $this->_client->sentinel('master',$name);
    }

    /**
     * Get the hostname and port for a specific master
     * @param string $name
     * @return mixed
     */
    public function getMasterAddressByName($name)
    {
        return $this->_client->sentinel('get-master-addr-by-name',$name);
    }

    /**
     * Check if the Sentinel is still responding
     * @return string|Credis_Client
     */
    public function ping()
    {
        return $this->_client->ping();
    }

    /**
     * Perform an auto-failover which will re-elect another master and make the current master a slave
     * @param string $name
     * @return mixed
     */
    public function failover($name)
    {
        return $this->_client->sentinel('failover',$name);
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->_client->getHost();
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->_client->getPort();
    }
}
