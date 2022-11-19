<?php
/**
 * Credis, a Redis interface for the modest
 *
 * @author Justin Poliey <jdp34@njit.edu>
 * @copyright 2009 Justin Poliey <jdp34@njit.edu>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package Credis
 */

/**
 * A generalized Credis_Client interface for a cluster of Redis servers
 *
 * @deprecated
 */
class Credis_Cluster
{
  /**
   * Collection of Credis_Client objects attached to Redis servers
   * @var Credis_Client[]
   */
  protected $clients;
  /**
   * If a server is set as master, all write commands go to that one
   * @var Credis_Client
   */
  protected $masterClient;
  /**
   * Aliases of Credis_Client objects attached to Redis servers, used to route commands to specific servers
   * @see Credis_Cluster::to
   * @var array
   */
  protected $aliases;

  /**
   * Hash ring of Redis server nodes
   * @var array
   */
  protected $ring;

  /**
   * Individual nodes of pointers to Redis servers on the hash ring
   * @var array
   */
  protected $nodes;

  /**
   * The commands that are not subject to hashing
   * @var array
   * @access protected
   */
  protected $dont_hash;

  /**
   * Currently working cluster-wide database number.
   * @var int
   */
  protected $selectedDb = 0;

  /**
   * Creates an interface to a cluster of Redis servers
   * Each server should be in the format:
   *  array(
   *   'host' => hostname,
   *   'port' => port,
   *   'db' => db,
   *   'password' => password,
   *   'timeout' => timeout,
   *   'alias' => alias,
   *   'persistent' => persistence_identifier,
   *   'master' => master
   *   'write_only'=> true/false
   * )
   *
   * @param array $servers The Redis servers in the cluster.
   * @param int $replicas
   * @param bool $standAlone
   * @throws CredisException
   */
  public function __construct($servers, $replicas = 128, $standAlone = false)
  {
    $this->clients = array();
    $this->masterClient = null;
    $this->aliases = array();
    $this->ring = array();
    $this->replicas = (int)$replicas;
    $client = null;
    foreach ($servers as $server)
    {
      if(is_array($server)){
          $client = new Credis_Client(
            $server['host'],
            $server['port'],
            isset($server['timeout']) ? $server['timeout'] : 2.5,
            isset($server['persistent']) ? $server['persistent'] : '',
            isset($server['db']) ? $server['db'] : 0,
            isset($server['password']) ? $server['password'] : null
          );
          if (isset($server['alias'])) {
            $this->aliases[$server['alias']] = $client;
          }
          if(isset($server['master']) && $server['master'] === true){
            $this->masterClient = $client;
            if(isset($server['write_only']) && $server['write_only'] === true){
                continue;
            }
          }
      } elseif($server instanceof Credis_Client){
        $client = $server;
      } else {
          throw new CredisException('Server should either be an array or an instance of Credis_Client');
      }
      if($standAlone) {
          $client->forceStandalone();
      }
      $this->clients[] = $client;
      for ($replica = 0; $replica <= $this->replicas; $replica++) {
          $md5num = hexdec(substr(md5($client->getHost().':'.$client->getPort().'-'.$replica),0,7));
          $this->ring[$md5num] = count($this->clients)-1;
      }
    }
    ksort($this->ring, SORT_NUMERIC);
    $this->nodes = array_keys($this->ring);
    $this->dont_hash = array_flip(array(
      'RANDOMKEY', 'DBSIZE', 'PIPELINE', 'EXEC',
      'SELECT',    'MOVE',    'FLUSHDB',  'FLUSHALL',
      'SAVE',      'BGSAVE',  'LASTSAVE', 'SHUTDOWN',
      'INFO',      'MONITOR', 'SLAVEOF'
    ));
    if($this->masterClient !== null && count($this->clients()) == 0){
        $this->clients[] = $this->masterClient;
        for ($replica = 0; $replica <= $this->replicas; $replica++) {
            $md5num = hexdec(substr(md5($this->masterClient->getHost().':'.$this->masterClient->getHost().'-'.$replica),0,7));
            $this->ring[$md5num] = count($this->clients)-1;
        }
        $this->nodes = array_keys($this->ring);
    }
  }

  /**
   * @param Credis_Client $masterClient
   * @param bool $writeOnly
   * @return Credis_Cluster
   */
  public function setMasterClient(Credis_Client $masterClient, $writeOnly=false)
  {
    if(!$masterClient instanceof Credis_Client){
        throw new CredisException('Master client should be an instance of Credis_Client');
    }
    $this->masterClient = $masterClient;
    if (!isset($this->aliases['master'])) {
        $this->aliases['master'] = $masterClient;
    }
    if(!$writeOnly){
        $this->clients[] = $this->masterClient;
        for ($replica = 0; $replica <= $this->replicas; $replica++) {
            $md5num = hexdec(substr(md5($this->masterClient->getHost().':'.$this->masterClient->getHost().'-'.$replica),0,7));
            $this->ring[$md5num] = count($this->clients)-1;
        }
        $this->nodes = array_keys($this->ring);
    }
    return $this;
  }
  /**
   * Get a client by index or alias.
   *
   * @param string|int $alias
   * @throws CredisException
   * @return Credis_Client
   */
  public function client($alias)
  {
    if (is_int($alias) && isset($this->clients[$alias])) {
      return $this->clients[$alias];
    }
    else if (isset($this->aliases[$alias])) {
      return $this->aliases[$alias];
    }
    throw new CredisException("Client $alias does not exist.");
  }

  /**
   * Get an array of all clients
   *
   * @return array|Credis_Client[]
   */
  public function clients()
  {
    return $this->clients;
  }

  /**
   * Execute a command on all clients
   *
   * @return array
   */
  public function all()
  {
    $args = func_get_args();
    $name = array_shift($args);
    $results = array();
    foreach($this->clients as $client) {
      $results[] = call_user_func_array([$client, $name], $args);
    }
    return $results;
  }

  /**
   * Get the client that the key would hash to.
   *
   * @param string $key
   * @return \Credis_Client
   */
  public function byHash($key)
  {
    return $this->clients[$this->hash($key)];
  }

  /**
   * @param int $index
   * @return void
   */
  public function select($index)
  {
      $this->selectedDb = (int) $index;
  }

  /**
   * Execute a Redis command on the cluster with automatic consistent hashing and read/write splitting
   *
   * @param string $name
   * @param array $args
   * @return mixed
   */
  public function __call($name, $args)
  {
    if($this->masterClient !== null && !$this->isReadOnlyCommand($name)){
        $client = $this->masterClient;
    }elseif (count($this->clients()) == 1 || isset($this->dont_hash[strtoupper($name)]) || !isset($args[0])) {
      $client = $this->clients[0];
    }
    else {
      $hashKey = $args[0];
      if (is_array($hashKey)) {
        $hashKey = join('|', $hashKey);
      }
      $client = $this->byHash($hashKey);
    }
    // Ensure that current client is working on the same database as expected.
    if ($client->getSelectedDb() != $this->selectedDb) {
      $client->select($this->selectedDb);
    }
    return call_user_func_array([$client, $name], $args);
  }

  /**
   * Get client index for a key by searching ring with binary search
   *
   * @param string $key The key to hash
   * @return int The index of the client object associated with the hash of the key
   */
  public function hash($key)
  {
    $needle = hexdec(substr(md5($key),0,7));
    $server = $min = 0;
    $max = count($this->nodes) - 1;
    while ($max >= $min) {
      $position = (int) (($min + $max) / 2);
      $server = $this->nodes[$position];
      if ($needle < $server) {
        $max = $position - 1;
      }
      else if ($needle > $server) {
        $min = $position + 1;
      }
      else {
        break;
      }
    }
    return $this->ring[$server];
  }

  public function isReadOnlyCommand($command)
  {
      static $readOnlyCommands = array(
          'DBSIZE' => true,
          'INFO' => true,
          'MONITOR' => true,
          'EXISTS' => true,
          'TYPE' => true,
          'KEYS' => true,
          'SCAN' => true,
          'RANDOMKEY' => true,
          'TTL' => true,
          'GET' => true,
          'MGET' => true,
          'SUBSTR' => true,
          'STRLEN' => true,
          'GETRANGE' => true,
          'GETBIT' => true,
          'LLEN' => true,
          'LRANGE' => true,
          'LINDEX' => true,
          'SCARD' => true,
          'SISMEMBER' => true,
          'SINTER' => true,
          'SUNION' => true,
          'SDIFF' => true,
          'SMEMBERS' => true,
          'SSCAN' => true,
          'SRANDMEMBER' => true,
          'ZRANGE' => true,
          'ZREVRANGE' => true,
          'ZRANGEBYSCORE' => true,
          'ZREVRANGEBYSCORE' => true,
          'ZCARD' => true,
          'ZSCORE' => true,
          'ZCOUNT' => true,
          'ZRANK' => true,
          'ZREVRANK' => true,
          'ZSCAN' => true,
          'HGET' => true,
          'HMGET' => true,
          'HEXISTS' => true,
          'HLEN' => true,
          'HKEYS' => true,
          'HVALS' => true,
          'HGETALL' => true,
          'HSCAN' => true,
          'PING' => true,
          'AUTH' => true,
          'SELECT' => true,
          'ECHO' => true,
          'QUIT' => true,
          'OBJECT' => true,
          'BITCOUNT' => true,
          'TIME' => true,
          'SORT' => true,
      );
      return array_key_exists(strtoupper($command), $readOnlyCommands);
  }
}

