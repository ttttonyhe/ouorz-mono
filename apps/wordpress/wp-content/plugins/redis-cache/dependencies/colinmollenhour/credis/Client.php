<?php
/**
 * Credis_Client (a fork of Redisent)
 *
 * Most commands are compatible with phpredis library:
 *   - use "pipeline()" to start a pipeline of commands instead of multi(Redis::PIPELINE)
 *   - any arrays passed as arguments will be flattened automatically
 *   - setOption and getOption are not supported in standalone mode
 *   - order of arguments follows redis-cli instead of phpredis where they differ (lrem)
 *
 * - Uses phpredis library if extension is installed for better performance.
 * - Establishes connection lazily.
 * - Supports tcp and unix sockets.
 * - Reconnects automatically unless a watch or transaction is in progress.
 * - Can set automatic retry connection attempts for iffy Redis connections.
 *
 * @author Colin Mollenhour <colin@mollenhour.com>
 * @copyright 2011 Colin Mollenhour <colin@mollenhour.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package Credis_Client
 */

if( ! defined('CRLF')) define('CRLF', sprintf('%s%s', chr(13), chr(10)));

/**
 * Credis-specific errors, wraps native Redis errors
 */
class CredisException extends Exception
{

    const CODE_TIMED_OUT = 1;
    const CODE_DISCONNECTED = 2;

    public function __construct($message, $code = 0, $exception = NULL)
    {
        if ($exception && get_class($exception) == 'RedisException' && strpos($message,'read error on connection') === 0) {
            $code = CredisException::CODE_DISCONNECTED;
        }
        parent::__construct($message, $code, $exception);
    }

}

/**
 * Credis_Client, a lightweight Redis PHP standalone client and phpredis wrapper
 *
 * Server/Connection:
 * @method Credis_Client               pipeline()
 * @method Credis_Client               multi()
 * @method Credis_Client               watch(string ...$keys)
 * @method Credis_Client               unwatch()
 * @method array                       exec()
 * @method string|Credis_Client        flushAll()
 * @method string|Credis_Client        flushDb()
 * @method array|Credis_Client         info(string $section = null)
 * @method bool|array|Credis_Client    config(string $setGet, string $key, string $value = null)
 * @method array|Credis_Client         role()
 * @method array|Credis_Client         time()
 * @method int|Credis_Client           dbsize()
 *
 * Keys:
 * @method int|Credis_Client           del(string|array $key)
 * @method int|Credis_Client           exists(string $key)
 * @method int|Credis_Client           expire(string $key, int $seconds)
 * @method int|Credis_Client           expireAt(string $key, int $timestamp)
 * @method array|Credis_Client         keys(string $key)
 * @method int|Credis_Client           persist(string $key)
 * @method bool|Credis_Client          rename(string $key, string $newKey)
 * @method bool|Credis_Client          renameNx(string $key, string $newKey)
 * @method array|Credis_Client         sort(string $key, string $arg1, string $valueN = null)
 * @method int|Credis_Client           ttl(string $key)
 * @method string|Credis_Client        type(string $key)
 *
 * Scalars:
 * @method int|Credis_Client           append(string $key, string $value)
 * @method int|Credis_Client           decr(string $key)
 * @method int|Credis_Client           decrBy(string $key, int $decrement)
 * @method false|string|Credis_Client  get(string $key)
 * @method int|Credis_Client           getBit(string $key, int $offset)
 * @method string|Credis_Client        getRange(string $key, int $start, int $end)
 * @method string|Credis_Client        getSet(string $key, string $value)
 * @method int|Credis_Client           incr(string $key)
 * @method int|Credis_Client           incrBy(string $key, int $decrement)
 * @method false|array|Credis_Client   mGet(array $keys)
 * @method bool|Credis_Client          mSet(array $keysValues)
 * @method int|Credis_Client           mSetNx(array $keysValues)
 * @method bool|Credis_Client          set(string $key, string $value, int | array $options = null)
 * @method int|Credis_Client           setBit(string $key, int $offset, int $value)
 * @method bool|Credis_Client          setEx(string $key, int $seconds, string $value)
 * @method int|Credis_Client           setNx(string $key, string $value)
 * @method int |Credis_Client          setRange(string $key, int $offset, int $value)
 * @method int|Credis_Client           strLen(string $key)
 *
 * Sets:
 * @method int|Credis_Client           sAdd(string $key, mixed $value, string $valueN = null)
 * @method int|Credis_Client           sRem(string $key, mixed $value, string $valueN = null)
 * @method array|Credis_Client         sMembers(string $key)
 * @method array|Credis_Client         sUnion(mixed $keyOrArray, string $valueN = null)
 * @method array|Credis_Client         sInter(mixed $keyOrArray, string $valueN = null)
 * @method array |Credis_Client        sDiff(mixed $keyOrArray, string $valueN = null)
 * @method string|Credis_Client        sPop(string $key)
 * @method int|Credis_Client           sCard(string $key)
 * @method int|Credis_Client           sIsMember(string $key, string $member)
 * @method int|Credis_Client           sMove(string $source, string $dest, string $member)
 * @method string|array|Credis_Client  sRandMember(string $key, int $count = null)
 * @method int|Credis_Client           sUnionStore(string $dest, string $key1, string $key2 = null)
 * @method int|Credis_Client           sInterStore(string $dest, string $key1, string $key2 = null)
 * @method int|Credis_Client           sDiffStore(string $dest, string $key1, string $key2 = null)
 *
 * Hashes:
 * @method bool|int|Credis_Client      hSet(string $key, string $field, string $value)
 * @method bool|Credis_Client          hSetNx(string $key, string $field, string $value)
 * @method bool|string|Credis_Client   hGet(string $key, string $field)
 * @method bool|int|Credis_Client      hLen(string $key)
 * @method bool|Credis_Client          hDel(string $key, string $field)
 * @method array|Credis_Client         hKeys(string $key, string $field)
 * @method array|Credis_Client         hVals(string $key)
 * @method array|Credis_Client         hGetAll(string $key)
 * @method bool|Credis_Client          hExists(string $key, string $field)
 * @method int|Credis_Client           hIncrBy(string $key, string $field, int $value)
 * @method float|Credis_Client         hIncrByFloat(string $key, string $member, float $value)
 * @method bool|Credis_Client          hMSet(string $key, array $keysValues)
 * @method array|Credis_Client         hMGet(string $key, array $fields)
 *
 * Lists:
 * @method array|null|Credis_Client    blPop(string $keyN, int $timeout)
 * @method array|null|Credis_Client    brPop(string $keyN, int $timeout)
 * @method array|null |Credis_Client   brPoplPush(string $source, string $destination, int $timeout)
 * @method string|null|Credis_Client   lIndex(string $key, int $index)
 * @method int|Credis_Client           lInsert(string $key, string $beforeAfter, string $pivot, string $value)
 * @method int|Credis_Client           lLen(string $key)
 * @method string|null|Credis_Client   lPop(string $key)
 * @method int|Credis_Client           lPush(string $key, mixed $value, mixed $valueN = null)
 * @method int|Credis_Client           lPushX(string $key, mixed $value)
 * @method array|Credis_Client         lRange(string $key, int $start, int $stop)
 * @method int|Credis_Client           lRem(string $key, int $count, mixed $value)
 * @method bool|Credis_Client          lSet(string $key, int $index, mixed $value)
 * @method bool|Credis_Client          lTrim(string $key, int $start, int $stop)
 * @method string|null|Credis_Client   rPop(string $key)
 * @method string|null|Credis_Client   rPoplPush(string $source, string $destination)
 * @method int|Credis_Client           rPush(string $key, mixed $value, mixed $valueN = null)
 * @method int |Credis_Client          rPushX(string $key, mixed $value)
 *
 * Sorted Sets:
 * @method int|Credis_Client           zAdd(string $key, double $score, string $value)
 * @method int|Credis_Client           zCard(string $key)
 * @method int|Credis_Client           zSize(string $key)
 * @method int|Credis_Client           zCount(string $key, mixed $start, mixed $stop)
 * @method int|Credis_Client           zIncrBy(string $key, double $value, string $member)
 * @method array|Credis_Client         zRangeByScore(string $key, mixed $start, mixed $stop, array $args = null)
 * @method array|Credis_Client         zRevRangeByScore(string $key, mixed $start, mixed $stop, array $args = null)
 * @method int|Credis_Client           zRemRangeByScore(string $key, mixed $start, mixed $stop)
 * @method array|Credis_Client         zRange(string $key, mixed $start, mixed $stop, array $args = null)
 * @method array|Credis_Client         zRevRange(string $key, mixed $start, mixed $stop, array $args = null)
 * @method int|Credis_Client           zRank(string $key, string $member)
 * @method int|Credis_Client           zRevRank(string $key, string $member)
 * @method int|Credis_Client           zRem(string $key, string $member)
 * @method int|Credis_Client           zDelete(string $key, string $member)
 * TODO
 *
 * Pub/Sub
 * @method int |Credis_Client          publish(string $channel, string $message)
 * @method int|array|Credis_Client     pubsub(string $subCommand, $arg = null)
 *
 * Scripting:
 * @method string|int|Credis_Client    script(string $command, string $arg1 = null)
 * @method string|int|array|bool|Credis_Client eval(string $script, array $keys = null, array $args = null)
 * @method string|int|array|bool|Credis_Client evalSha(string $script, array $keys = null, array $args = null)
 */
class Credis_Client {

    const VERSION          = '1.11.4';

    const TYPE_STRING      = 'string';
    const TYPE_LIST        = 'list';
    const TYPE_SET         = 'set';
    const TYPE_ZSET        = 'zset';
    const TYPE_HASH        = 'hash';
    const TYPE_NONE        = 'none';

    const FREAD_BLOCK_SIZE = 8192;

    /**
     * Socket connection to the Redis server or Redis library instance
     * @var resource|Redis
     */
    protected $redis;
    protected $redisMulti;

    /**
     * Host of the Redis server
     * @var string
     */
    protected $host;

    /**
     * Scheme of the Redis server (tcp, tls, tlsv1.2, unix)
     * @var string|null
     */
    protected $scheme;

    /**
     * SSL Meta information
     * @var string
     */
    protected $sslMeta;

    /**
     * Port on which the Redis server is running
     * @var int|null
     */
    protected $port;

    /**
     * Timeout for connecting to Redis server
     * @var float|null
     */
    protected $timeout;

    /**
     * Timeout for reading response from Redis server
     * @var float|null
     */
    protected $readTimeout;

    /**
     * Unique identifier for persistent connections
     * @var string
     */
    protected $persistent;

    /**
     * @var bool
     */
    protected $closeOnDestruct = TRUE;

    /**
     * @var bool
     */
    protected $connected = FALSE;

    /**
     * @var bool
     */
    protected $standalone;

    /**
     * @var int
     */
    protected $maxConnectRetries = 0;

    /**
     * @var int
     */
    protected $connectFailures = 0;

    /**
     * @var bool
     */
    protected $usePipeline = FALSE;

    /**
     * @var array
     */
    protected $commandNames;

    /**
     * @var string
     */
    protected $commands;

    /**
     * @var bool
     */
    protected $isMulti = FALSE;

    /**
     * @var bool
     */
    protected $isWatching = FALSE;

    /**
     * @var string|null
     */
    protected $authUsername;

    /**
     * @var string|null
     */
    protected $authPassword;

    /**
     * @var int
     */
    protected $selectedDb = 0;

    /**
     * Aliases for backwards compatibility with phpredis
     * @var array
     */
    protected $wrapperMethods = array('delete' => 'del', 'getkeys' => 'keys', 'sremove' => 'srem');

    /**
     * @var array<string,string>|callable|null
     */
    protected $renamedCommands;

    /**
     * @var int
     */
    protected $requests = 0;

    /**
     * @var bool
     */
    protected $subscribed = false;

    /** @var bool  */
    protected $oldPhpRedis = false;

    /** @var array */
    protected $tlsOptions = [];


    /**
     * @var bool
     */
    protected $isTls = false;

    /**
     * Gets Useful Meta debug information about the SSL
     *
     * @return string
     */
    public function getSslMeta()
    {
        return $this->sslMeta;
    }

    /**
     * Creates a Redisent connection to the Redis server on host {@link $host} and port {@link $port}.
     * $host may also be a path to a unix socket or a string in the form of tcp://[hostname]:[port] or unix://[path]
     *
     * @param string $host The hostname of the Redis server
     * @param int|null $port The port number of the Redis server
     * @param float|null $timeout  Timeout period in seconds
     * @param string $persistent  Flag to establish persistent connection
     * @param int $db The selected database of the Redis server
     * @param string|null $password The authentication password of the Redis server
     * @param string|null $username The authentication username of the Redis server
     * @param array|null $tlsOptions The TLS/SSL context options. See https://www.php.net/manual/en/context.ssl.php for details
     */
    public function __construct($host = '127.0.0.1', $port = 6379, $timeout = null, $persistent = '', $db = 0, $password = null, $username = null, array $tlsOptions = null)
    {
        $this->host = (string) $host;
        if ($port !== null) {
          $this->port = (int) $port;
        }
        $this->scheme = null;
        $this->timeout = $timeout;
        $this->persistent = (string) $persistent;
        $this->standalone = ! extension_loaded('redis');
        $this->authPassword = $password;
        $this->authUsername = $username;
        $this->selectedDb = (int)$db;
        $this->convertHost();
        if ($tlsOptions) {
          $this->setTlsOptions($tlsOptions);
        }
        // PHP Redis extension support TLS/ACL AUTH since 5.3.0
        $this->oldPhpRedis = (bool)version_compare(phpversion('redis'),'5.3.0','<');
        if ((
              $this->isTls
              || $this->authUsername !== null
            )
            && !$this->standalone && $this->oldPhpRedis){
            $this->standalone = true;
        }
    }

    public function __destruct()
    {
        if ($this->closeOnDestruct) {
            $this->close();
        }
    }

    /**
     * @return bool
     */
    public function isSubscribed()
    {
    	return $this->subscribed;
    }

    /**
     * Return the host of the Redis instance
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }
    /**
     * Return the port of the Redis instance
     * @return int|null
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return bool
     */
    public function isTls()
    {
      return $this->isTls;
    }

    /**
     * Return the selected database
     * @return int
     */
    public function getSelectedDb()
    {
        return $this->selectedDb;
    }
    /**
     * @return string
     */
    public function getPersistence()
    {
        return $this->persistent;
    }
    /**
     * @throws CredisException
     * @return Credis_Client
     */
    public function forceStandalone()
    {
        if ($this->standalone) {
            return $this;
        }
        if($this->connected) {
            throw new CredisException('Cannot force Credis_Client to use standalone PHP driver after a connection has already been established.');
        }
        $this->standalone = TRUE;
        return $this;
    }

    /**
     * @param int $retries
     * @return Credis_Client
     */
    public function setMaxConnectRetries($retries)
    {
        $this->maxConnectRetries = $retries;
        return $this;
    }

    /**
     * @param bool $flag
     * @return Credis_Client
     */
    public function setCloseOnDestruct($flag)
    {
        $this->closeOnDestruct = $flag;
        return $this;
    }

    public function setTlsOptions(array $tlsOptions)
    {
      if($this->connected) {
        throw new CredisException('Cannot change TLS options after a connection has already been established.');
      }
      $this->tlsOptions = $tlsOptions;
    }

    protected function convertHost()
    {
        if (preg_match('#^(tcp|tls|ssl|tlsv\d(?:\.\d)?|unix)://(.+)$#', $this->host, $matches)) {
            $this->isTls = strpos($matches[1], 'tls') === 0 || strpos($matches[1], 'ssl') === 0;
            if($this->isTls || $matches[1] === 'tcp') {
                $this->scheme = $matches[1];
                if ( ! preg_match('#^([^:]+)(:([0-9]+))?(/(.+))?$#', $matches[2], $matches)) {
                    throw new CredisException('Invalid host format; expected '.$this->scheme.'://host[:port][/persistence_identifier]');
                }
                $this->host = $matches[1];
                $this->port = (int) (isset($matches[3]) ? $matches[3] : $this->port);
                $this->persistent = isset($matches[5]) ? $matches[5] : $this->persistent;
            } else {
                $this->host = $matches[2];
                $this->port = NULL;
                $this->scheme = 'unix';
                if (substr($this->host,0,1) != '/') {
                    throw new CredisException('Invalid unix socket format; expected unix:///path/to/redis.sock');
                }
            }
        }
        if ($this->port !== NULL && substr($this->host,0,1) == '/') {
            $this->port = NULL;
            $this->scheme = 'unix';
        }
        if (!$this->scheme) {
            $this->scheme = 'tcp';
        }
    }

    /**
     * @throws CredisException
     * @return Credis_Client
     */
    public function connect()
    {
        if ($this->connected) {
            return $this;
        }
        $this->close(true);
        $tlsOptions = $this->isTls ? $this->tlsOptions : [];
        if ($this->standalone) {
            $flags = STREAM_CLIENT_CONNECT;
            $remote_socket = $this->port === NULL
                ? $this->scheme.'://'.$this->host
                : $this->scheme.'://'.$this->host.':'.$this->port;
            if ($this->persistent && $this->port !== NULL) {
                // Persistent connections to UNIX sockets are not supported
                $remote_socket .= '/'.$this->persistent;
                $flags = $flags | STREAM_CLIENT_PERSISTENT;
            }
            if ($this->isTls) {
                $tlsOptions = array_merge($tlsOptions, [
                    'capture_peer_cert' => true,
                    'capture_peer_cert_chain' => true,
                    'capture_session_meta' => true,
                ]);
            }

            // passing $context as null errors before php 8.0
            $context = stream_context_create(['ssl' => $tlsOptions]);

            $result = $this->redis = @stream_socket_client($remote_socket, $errno, $errstr, $this->timeout !== null ? $this->timeout : 2.5, $flags, $context);

            if ($result && $this->isTls) {
                $this->sslMeta = stream_context_get_options($context);
            }
        }
        else {
            if ( ! $this->redis) {
                $this->redis = new Redis;
            }
            $socketTimeout = $this->timeout ?: 0.0;
            try
            {
              if ($this->oldPhpRedis)
              {
                $result = $this->persistent
                  ? $this->redis->pconnect($this->host, (int)$this->port, $socketTimeout, $this->persistent)
                  : $this->redis->connect($this->host, (int)$this->port, $socketTimeout);
              }
              else
              {
                // 7th argument is non-documented TLS options. But it only exists on the newer versions of phpredis
                if ($tlsOptions) {
                  $context = ['stream' => $tlsOptions];
                } else {
                  $context = [];
                }
                /** @noinspection PhpMethodParametersCountMismatchInspection */
                $result = $this->persistent
                  ? $this->redis->pconnect($this->scheme.'://'.$this->host, (int)$this->port, $socketTimeout, $this->persistent, 0, 0.0, $context)
                  : $this->redis->connect($this->scheme.'://'.$this->host, (int)$this->port, $socketTimeout, null, 0, 0.0, $context);
              }
            }
            catch(Exception $e)
            {
                // Some applications will capture the php error that phpredis can sometimes generate and throw it as an Exception
                $result = false;
                $errno = 1;
                $errstr = $e->getMessage();
            }
        }

        // Use recursion for connection retries
        if ( ! $result) {
            $this->connectFailures++;
            if ($this->connectFailures <= $this->maxConnectRetries) {
                return $this->connect();
            }
            $failures = $this->connectFailures;
            $this->connectFailures = 0;
            throw new CredisException(sprintf("Connection to Redis%s %s://%s failed after %s failures.%s",
                $this->standalone ? ' standalone' : '',
                $this->scheme,
                $this->host.($this->port ? ':'.$this->port : ''),
                $failures,
                (isset($errno) && isset($errstr) ? "Last Error : ({$errno}) {$errstr}" : "")
            ));
        }

        $this->connectFailures = 0;
        $this->connected = TRUE;

        // Set read timeout
        if ($this->readTimeout) {
            $this->setReadTimeout($this->readTimeout);
        }
        if($this->authPassword) {
            $this->auth($this->authPassword, $this->authUsername);
        }
        if($this->selectedDb !== 0) {
            $this->select($this->selectedDb);
        }
        return $this;
    }
    /**
     * @return bool
     */
    public function isConnected()
    {
        return $this->connected;
    }
    /**
     * Set the read timeout for the connection. Use 0 to disable timeouts entirely (or use a very long timeout
     * if not supported).
     *
     * @param float $timeout 0 (or -1) for no timeout, otherwise number of seconds
     * @throws CredisException
     * @return Credis_Client
     */
    public function setReadTimeout($timeout)
    {
        if ($timeout < -1) {
            throw new CredisException('Timeout values less than -1 are not accepted.');
        }
        $this->readTimeout = $timeout;
        if ($this->isConnected()) {
            if ($this->standalone) {
                $timeout = $timeout <= 0 ? 315360000 : $timeout; // Ten-year timeout
                stream_set_blocking($this->redis, TRUE);
                stream_set_timeout($this->redis, (int) floor($timeout), ($timeout - floor($timeout)) * 1000000);
            } else if (defined('Redis::OPT_READ_TIMEOUT')) {
                // supported in phpredis 2.2.3
                // a timeout value of -1 means reads will not timeout
                $timeout = $timeout == 0 ? -1 : $timeout;
                $this->redis->setOption(Redis::OPT_READ_TIMEOUT, $timeout);
            }
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function close($force = FALSE)
    {
        $result = TRUE;
        if ($this->redis && ($force || $this->connected && ! $this->persistent)) {
            try {
                if (is_callable(array($this->redis, 'close'))) {
                    $this->redis->close();
                } else {
                    @fclose($this->redis);
                    $this->redis = null;
                }
            } catch (Exception $e) {
                ; // Ignore exceptions on close
            }
            $this->connected = $this->usePipeline = $this->isMulti = $this->isWatching = FALSE;
        }
        return $result;
    }

    /**
     * Enabled command renaming and provide mapping method. Supported methods are:
     *
     * 1. renameCommand('foo') // Salted md5 hash for all commands -> md5('foo'.$command)
     * 2. renameCommand(function($command){ return 'my'.$command; }); // Callable
     * 3. renameCommand('get', 'foo') // Single command -> alias
     * 4. renameCommand(['get' => 'foo', 'set' => 'bar']) // Full map of [command -> alias]
     *
     * @param string|callable|array $command
     * @param string|null $alias
     * @return $this
     */
    public function renameCommand($command, $alias = NULL)
    {
        if ( ! $this->standalone) {
            $this->forceStandalone();
        }
        if ($alias === NULL) {
            $this->renamedCommands = $command;
        } else {
            if ( ! $this->renamedCommands) {
                $this->renamedCommands = array();
            }
            $this->renamedCommands[$command] = $alias;
        }
        return $this;
    }

    /**
     * @param $command
     * @return string
     */
    public function getRenamedCommand($command)
    {
        static $map;

        // Command renaming not enabled
        if ($this->renamedCommands === NULL) {
            return $command;
        }

        // Initialize command map
        if ($map === NULL) {
            if (is_array($this->renamedCommands)) {
                $map = $this->renamedCommands;
            } else {
                $map = array();
            }
        }

        // Generate and return cached result
        if ( ! isset($map[$command])) {
            // String means all commands are hashed with salted md5
            if (is_string($this->renamedCommands)) {
                $map[$command] = md5($this->renamedCommands.$command);
            }
            // Would already be set in $map if it was intended to be renamed
            else if (is_array($this->renamedCommands)) {
                return $command;
            }
            // User-supplied function
            else if (is_callable($this->renamedCommands)) {
                $map[$command] = call_user_func($this->renamedCommands, $command);
            }
        }
        return $map[$command];
    }

    /**
     * @param string $password
     * @param string|null $username
     * @return bool
     */
    public function auth($password, $username = null)
    {
        if ($username !== null) {
            $response = $this->__call('auth', array($username, $password));
            $this->authUsername= $username;
        } else {
            $response = $this->__call('auth', array($password));
        }
        $this->authPassword = $password;
        return $response;
    }

    /**
     * @param int $index
     * @return bool
     */
    public function select($index)
    {
        $response = $this->__call('select', array($index));
        $this->selectedDb = (int) $index;
        return $response;
    }

    /**
     * @param string|array $pattern
     * @return array
     */
    public function pUnsubscribe()
    {
    	list($command, $channel, $subscribedChannels) = $this->__call('punsubscribe', func_get_args());
    	$this->subscribed = $subscribedChannels > 0;
    	return array($command, $channel, $subscribedChannels);
    }

    /**
     * @param int $Iterator
     * @param string $pattern
     * @param int $count
     * @return bool|array
     */
    public function scan(&$Iterator, $pattern = null, $count = null)
    {
        return $this->__call('scan', array(&$Iterator, $pattern, $count));
    }

    /**
	 * @param int $Iterator
	 * @param string $field
	 * @param string $pattern
	 * @param int $count
	 * @return bool|array
	 */
	public function hscan(&$Iterator, $field, $pattern = null, $count = null)
	{
		return $this->__call('hscan', array($field, &$Iterator, $pattern, $count));
	}

    /**
     * @param int $Iterator
     * @param string $field
     * @param string $pattern
     * @param int $Iterator
     * @return bool|array
     */
    public function sscan(&$Iterator, $field, $pattern = null, $count = null)
    {
        return $this->__call('sscan', array($field, &$Iterator, $pattern, $count));
    }

    /**
     * @param int $Iterator
     * @param string $field
     * @param string $pattern
     * @param int $Iterator
     * @return bool|array
     */
    public function zscan(&$Iterator, $field, $pattern = null, $count = null)
    {
        return $this->__call('zscan', array($field, &$Iterator, $pattern, $count));
    }

    /**
     * @param string|array $patterns
     * @param $callback
     * @return $this|array|bool|Credis_Client|mixed|null|string
     * @throws CredisException
     */
    public function pSubscribe($patterns, $callback)
    {
        if ( ! $this->standalone) {
            return $this->__call('pSubscribe', array((array)$patterns, $callback));
        }

        // Standalone mode: use infinite loop to subscribe until timeout
        $patternCount = is_array($patterns) ? count($patterns) : 1;
        while ($patternCount--) {
            if (isset($status)) {
                list($command, $pattern, $status) = $this->read_reply();
            } else {
                list($command, $pattern, $status) = $this->__call('psubscribe', array($patterns));
            }
            $this->subscribed = $status > 0;
            if ( ! $status) {
                throw new CredisException('Invalid pSubscribe response.');
            }
        }
        while ($this->subscribed) {
            list($type, $pattern, $channel, $message) = $this->read_reply();
            if ($type != 'pmessage') {
                throw new CredisException('Received non-pmessage reply.');
            }
            $callback($this, $pattern, $channel, $message);
        }
        return null;
    }

    /**
     * @param string|array $pattern
     * @return array
     */
    public function unsubscribe()
    {
    	list($command, $channel, $subscribedChannels) = $this->__call('unsubscribe', func_get_args());
    	$this->subscribed = $subscribedChannels > 0;
    	return array($command, $channel, $subscribedChannels);
    }

    /**
     * @param string|array $channels
     * @param $callback
     * @throws CredisException
     * @return $this|array|bool|Credis_Client|mixed|null|string
     */
    public function subscribe($channels, $callback)
    {
        if ( ! $this->standalone) {
            return $this->__call('subscribe', array((array)$channels, $callback));
        }

        // Standalone mode: use infinite loop to subscribe until timeout
        $channelCount = is_array($channels) ? count($channels) : 1;
        while ($channelCount--) {
            if (isset($status)) {
                list($command, $channel, $status) = $this->read_reply();
            } else {
                list($command, $channel, $status) = $this->__call('subscribe', array($channels));
            }
            $this->subscribed = $status > 0;
            if ( ! $status) {
                throw new CredisException('Invalid subscribe response.');
            }
        }
        while ($this->subscribed) {
            list($type, $channel, $message) = $this->read_reply();
            if ($type != 'message') {
                throw new CredisException('Received non-message reply.');
            }
            $callback($this, $channel, $message);
        }
        return null;
    }

    /**
     * @param string|null $name
     * @return string|Credis_Client
     */
    public function ping($name = null)
    {
      return $this->__call('ping', $name ? array($name) : array());
    }

  /**
   * @param string $command
   * @param array $args
   *
   * @return array|Credis_Client
   */
   public function rawCommand($command, array $args)
   {
     if($this->standalone)
     {
       return $this->__call($command, $args);
     }
     else
     {
       \array_unshift($args, $command);
       return $this->__call('rawCommand', $args);
     }
   }

    public function __call($name, $args)
    {
        // Lazy connection
        $this->connect();

        $name = strtolower($name);

        // Send request via native PHP
        if($this->standalone)
        {
            $trackedArgs = array();
            switch ($name) {
                case 'eval':
                case 'evalsha':
                    $script = array_shift($args);
                    $keys = (array) array_shift($args);
                    $eArgs = (array) array_shift($args);
                    $args = array($script, count($keys), $keys, $eArgs);
                    break;
                case 'zinterstore':
                case 'zunionstore':
                    $dest = array_shift($args);
                    $keys = (array) array_shift($args);
                    $weights = array_shift($args);
                    $aggregate = array_shift($args);
                    $args = array($dest, count($keys), $keys);
                    if ($weights) {
                        $args[] = (array) $weights;
                    }
                    if ($aggregate) {
                        $args[] = $aggregate;
                    }
                    break;
                case 'set':
                    // The php redis module has different behaviour with ttl
                    // https://github.com/phpredis/phpredis#set
                    if (count($args) === 3 && is_int($args[2])) {
                        $args = array($args[0], $args[1], array('EX', $args[2]));
                    } elseif (count($args) === 3 && is_array($args[2])) {
                        $tmp_args = $args;
                        $args = array($tmp_args[0], $tmp_args[1]);
                        foreach ($tmp_args[2] as $k=>$v) {
                            if (is_string($k)) {
                                $args[] = array($k,$v);
                            } elseif (is_int($k)) {
                                $args[] = $v;
                            }
                        }
                        unset($tmp_args);
                    }
                    break;
                case 'scan':
                    $trackedArgs = array(&$args[0]);
                    if (empty($trackedArgs[0]))
                    {
                        $trackedArgs[0] = 0;
                    }
                    $eArgs = array($trackedArgs[0]);
                    if (!empty($args[1]))
                    {
                        $eArgs[] = 'MATCH';
                        $eArgs[] = $args[1];
                    }
                    if (!empty($args[2]))
                    {
                        $eArgs[] = 'COUNT';
                        $eArgs[] = $args[2];
                    }
                    $args = $eArgs;
                    break;
                case 'sscan':
                case 'zscan':
                case 'hscan':
					$trackedArgs = array(&$args[1]);
					if (empty($trackedArgs[0]))
					{
 						$trackedArgs[0] = 0;
					}
					$eArgs = array($args[0],$trackedArgs[0]);
					if (!empty($args[2]))
					{
						$eArgs[] = 'MATCH';
						$eArgs[] = $args[2];
					}
					if (!empty($args[3]))
					{
						$eArgs[] = 'COUNT';
						$eArgs[] = $args[3];
					}
					$args = $eArgs;
					break;
                case 'zrangebyscore':
                case 'zrevrangebyscore':
                case 'zrange':
                case 'zrevrange':
                    if (isset($args[3]) && is_array($args[3])) {
                        // map options
                        $cArgs = array();
                        if (!empty($args[3]['withscores'])) {
                            $cArgs[] = 'withscores';
                        }
                        if (($name == 'zrangebyscore' || $name == 'zrevrangebyscore') && array_key_exists('limit', $args[3])) {
                            $cArgs[] = array('limit' => $args[3]['limit']);
                        }
                        $args[3] = $cArgs;
                        $trackedArgs = $cArgs;
                    }
                    break;
                case 'mget':
                    if (isset($args[0]) && is_array($args[0]))
                    {
                        $args = array_values($args[0]);
                    }
                    break;
                case 'hmset':
                    if (isset($args[1]) && is_array($args[1]))
                    {
                        $cArgs = array();
                        foreach($args[1] as $id => $value)
                        {
                            $cArgs[] = $id;
                            $cArgs[] = $value;
                        }
                        $args[1] = $cArgs;
                    }
                    break;
                case 'zsize':
                    $name = 'zcard';
                    break;
                case 'zdelete':
                    $name = 'zrem';
                    break;
                case 'hmget':
                    // hmget needs to track the keys for rehydrating the results
                    if (isset($args[1]))
                    {
                        $trackedArgs = $args[1];
                    }
                    break;
            }
            // Flatten arguments
            $args = self::_flattenArguments($args);

            // In pipeline mode
            if($this->usePipeline)
            {
                if($name === 'pipeline') {
                    throw new CredisException('A pipeline is already in use and only one pipeline is supported.');
                }
                else if($name === 'exec') {
                    if($this->isMulti) {
                        $this->commandNames[] = array($name, $trackedArgs);
                        $this->commands .= self::_prepare_command(array($this->getRenamedCommand($name)));
                    }

                    // Write request
                    if($this->commands) {
                        $this->write_command($this->commands);
                    }
                    $this->commands = NULL;

                    // Read response
                    $queuedResponses = array();
                    $response = array();
                    foreach($this->commandNames as $command) {
                        list($name, $arguments) = $command;
                        $result = $this->read_reply($name, true);
                        if ($result !== null)
                        {
                            $result = $this->decode_reply($name, $result, $arguments);
                        }
                        else
                        {
                            $queuedResponses[] = $command;
                        }
                        $response[] = $result;
                    }

                    if($this->isMulti) {
                        $response = array_pop($response);
                        foreach($queuedResponses as $key => $command)
                        {
                            list($name, $arguments) = $command;
                            $response[$key] = $this->decode_reply($name, $response[$key], $arguments);
                        }
                    }

                    $this->commandNames = NULL;
                    $this->usePipeline = $this->isMulti = FALSE;
                    return $response;
                }
                else if ($name === 'discard')
                {
                    $this->commands = NULL;
                    $this->commandNames = NULL;
                    $this->usePipeline = $this->isMulti = FALSE;
                }
                else {
                    if($name === 'multi') {
                        $this->isMulti = TRUE;
                    }
                    array_unshift($args, $this->getRenamedCommand($name));
                    $this->commandNames[] = array($name, $trackedArgs);
                    $this->commands .= self::_prepare_command($args);
                    return $this;
                }
            }

            // Start pipeline mode
            if($name === 'pipeline')
            {
                $this->usePipeline = TRUE;
                $this->commandNames = array();
                $this->commands = '';
                return $this;
            }

            // If unwatching, allow reconnect with no error thrown
            if($name === 'unwatch') {
                $this->isWatching = FALSE;
            }

            // Non-pipeline mode
            array_unshift($args, $this->getRenamedCommand($name));
            $command = self::_prepare_command($args);
            $this->write_command($command);
            $response = $this->read_reply($name);
            $response = $this->decode_reply($name, $response, $trackedArgs);

            // Watch mode disables reconnect so error is thrown
            if($name == 'watch') {
                $this->isWatching = TRUE;
            }
            // Transaction mode
            else if($this->isMulti && ($name == 'exec' || $name == 'discard')) {
                $this->isMulti = FALSE;
            }
            // Started transaction
            else if($this->isMulti || $name == 'multi') {
                $this->isMulti = TRUE;
                $response = $this;
            }
        }

        // Send request via phpredis client
        else
        {
            // Tweak arguments
            switch($name) {
                case 'get':   // optimize common cases
                case 'set':
                case 'hget':
                case 'hset':
                case 'setex':
                case 'mset':
                case 'msetnx':
                case 'hmset':
                case 'hmget':
                case 'del':
                case 'zrangebyscore':
                case 'zrevrangebyscore':
                   break;
                case 'zrange':
                case 'zrevrange':
                    if (isset($args[3]) && is_array($args[3]))
                    {
                        $cArgs = $args[3];
                        $args[3] = !empty($cArgs['withscores']);
                    }
                    $args = self::_flattenArguments($args);
                    break;
                case 'zinterstore':
                case 'zunionstore':
                    $cArgs = array();
                    $cArgs[] = array_shift($args); // destination
                    $cArgs[] = array_shift($args); // keys
                    if(isset($args[0]) and isset($args[0]['weights'])) {
                        $cArgs[] = (array) $args[0]['weights'];
                    } else {
                        $cArgs[] = null;
                    }
                    if(isset($args[0]) and isset($args[0]['aggregate'])) {
                        $cArgs[] = strtoupper($args[0]['aggregate']);
                    }
                    $args = $cArgs;
                    break;
                case 'mget':
                    if(isset($args[0]) && ! is_array($args[0])) {
                        $args = array($args);
                    }
                    break;
                case 'lrem':
                    $args = array($args[0], $args[2], $args[1]);
                    break;
                case 'eval':
                case 'evalsha':
                    if (isset($args[1]) && is_array($args[1])) {
                        $cKeys = $args[1];
                    } elseif (isset($args[1]) && is_string($args[1])) {
                        $cKeys = array($args[1]);
                    } else {
                        $cKeys = array();
                    }
                    if (isset($args[2]) && is_array($args[2])) {
                        $cArgs = $args[2];
                    } elseif (isset($args[2]) && is_string($args[2])) {
                        $cArgs = array($args[2]);
                    } else {
                        $cArgs = array();
                    }
                    $args = array($args[0], array_merge($cKeys, $cArgs), count($cKeys));
                    break;
                case 'subscribe':
                case 'psubscribe':
                    break;
                case 'scan':
                case 'sscan':
                case 'hscan':
                case 'zscan':
                    // allow phpredis to see the caller's reference
                    //$param_ref =& $args[0];
                    break;
                case 'auth':
                    // For phpredis pre-v5.3, the type signature is string, not array|string
                    $args = $this->oldPhpRedis ? $args : array($args);
                    break;
                default:
                    // Flatten arguments
                    $args = self::_flattenArguments($args);
            }

            try {
                // Proxy pipeline mode to the phpredis library
                if($name == 'pipeline' || $name == 'multi') {
                    if($this->isMulti) {
                        return $this;
                    } else {
                        $this->isMulti = TRUE;
                        $this->redisMulti = call_user_func_array(array($this->redis, $name), $args);
                        return $this;
                    }
                }
                else if($name == 'exec' || $name == 'discard') {
                    $this->isMulti = FALSE;
                    $response = $this->redisMulti->$name();
                    $this->redisMulti = NULL;
                    #echo "> $name : ".substr(print_r($response, TRUE),0,100)."\n";
                    return $response;
                }

                // Use aliases to be compatible with phpredis wrapper
                if(isset($this->wrapperMethods[$name])) {
                    $name = $this->wrapperMethods[$name];
                }

                // Multi and pipeline return self for chaining
                if($this->isMulti) {
                    call_user_func_array(array($this->redisMulti, $name), $args);
                    return $this;
                }


                // Send request, retry one time when using persistent connections on the first request only
                $this->requests++;
                try {
                    $response = call_user_func_array(array($this->redis, $name), $args);
                } catch (RedisException $e) {
                    if ($this->persistent && $this->requests == 1 && $e->getMessage() == 'read error on connection') {
                        $this->close(true);
                        $this->connect();
                        $response = call_user_func_array(array($this->redis, $name), $args);
                    } else {
                        throw $e;
                    }
                }
            }
            // Wrap exceptions
            catch(RedisException $e) {
                $code = 0;
                if ( ! ($result = $this->redis->IsConnected())) {
                    $this->close(true);
                    $code = CredisException::CODE_DISCONNECTED;
                }
                throw new CredisException($e->getMessage(), $code, $e);
            }

            #echo "> $name : ".substr(print_r($response, TRUE),0,100)."\n";

            // change return values where it is too difficult to minim in standalone mode
            switch($name)
            {
                case 'type':
                    $typeMap = array(
                      self::TYPE_NONE,
                      self::TYPE_STRING,
                      self::TYPE_SET,
                      self::TYPE_LIST,
                      self::TYPE_ZSET,
                      self::TYPE_HASH,
                    );
                    $response = $typeMap[$response];
                    break;

                // Handle scripting errors
                case 'eval':
                case 'evalsha':
                case 'script':
                    $error = $this->redis->getLastError();
                    $this->redis->clearLastError();
                    if ($error && substr($error,0,8) == 'NOSCRIPT') {
                        $response = NULL;
                    } else if ($error) {
                        throw new CredisException($error);
                    }
                    break;
                case 'exists':
                    // smooth over phpredis-v4 vs earlier difference to match documented credis return results
                    $response = (int) $response;
                    break;
                case 'ping':
                    if ($response) {
                      if ($response === true) {
                        $response = isset($args[0]) ? $args[0] : "PONG";
                      } else if ($response[0] === '+') {
                        $response = substr($response, 1);
                      }
                    }
                    break;
                case 'auth':
                    if (is_bool($response) && $response === true){
                        $this->redis->clearLastError();
                    }
                default:
                    $error = $this->redis->getLastError();
                    $this->redis->clearLastError();
                    if ($error) {
                        throw new CredisException(rtrim($error));
                    }
                    break;
            }
        }

        return $response;
    }

    protected function write_command($command)
    {
        // Reconnect on lost connection (Redis server "timeout" exceeded since last command)
        if(feof($this->redis)) {
            // If a watch or transaction was in progress and connection was lost, throw error rather than reconnect
            // since transaction/watch state will be lost.
            if(($this->isMulti && ! $this->usePipeline) || $this->isWatching) {
                $this->close(true);
                throw new CredisException('Lost connection to Redis server during watch or transaction.');
            }
            $this->close(true);
            $this->connect();
            if($this->authPassword) {
                $this->auth($this->authPassword);
            }
            if($this->selectedDb != 0) {
                $this->select($this->selectedDb);
            }
        }

        $commandLen = strlen($command);
        $lastFailed = FALSE;
        for ($written = 0; $written < $commandLen; $written += $fwrite) {
            $fwrite = fwrite($this->redis, substr($command, $written));
            if ($fwrite === FALSE || ($fwrite == 0 && $lastFailed)) {
                $this->close(true);
                throw new CredisException('Failed to write entire command to stream');
            }
            $lastFailed = $fwrite == 0;
        }
    }

    protected function read_reply($name = '', $returnQueued = false)
    {
        $reply = fgets($this->redis);
        if($reply === FALSE) {
            $info = stream_get_meta_data($this->redis);
            $this->close(true);
            if ($info['timed_out']) {
                throw new CredisException('Read operation timed out.', CredisException::CODE_TIMED_OUT);
            } else {
                throw new CredisException('Lost connection to Redis server.', CredisException::CODE_DISCONNECTED);
            }
        }
        $reply = rtrim($reply, CRLF);
        #echo "> $name: $reply\n";
        $replyType = substr($reply, 0, 1);
        switch ($replyType) {
            /* Error reply */
            case '-':
                if($this->isMulti || $this->usePipeline) {
                    $response = FALSE;
                } else if ($name == 'evalsha' && substr($reply,0,9) == '-NOSCRIPT') {
                    $response = NULL;
                } else {
                    throw new CredisException(substr($reply,0,4) == '-ERR' ? 'ERR '.substr($reply, 5) : substr($reply,1));
                }
                break;
            /* Inline reply */
            case '+':
                $response = substr($reply, 1);
                if($response == 'OK') {
                  return TRUE;
                }
                if($response == 'QUEUED') {
                    return $returnQueued ? null : true;
                }
                break;
            /* Bulk reply */
            case '$':
                if ($reply == '$-1') return FALSE;
                $size = (int) substr($reply, 1);
                $response = stream_get_contents($this->redis, $size + 2);
                if( ! $response) {
                    $this->close(true);
                    throw new CredisException('Error reading reply.');
                }
                $response = substr($response, 0, $size);
                break;
            /* Multi-bulk reply */
            case '*':
                $count = substr($reply, 1);
                if ($count == '-1') return FALSE;

                $response = array();
                for ($i = 0; $i < $count; $i++) {
                        $response[] = $this->read_reply();
                }
                break;
            /* Integer reply */
            case ':':
                $response = intval(substr($reply, 1));
                break;
            default:
                throw new CredisException('Invalid response: '.print_r($reply, TRUE));
                break;
        }

        return $response;
    }

    protected function decode_reply($name, $response, array &$arguments = array() )
    {
        // Smooth over differences between phpredis and standalone response
        switch ($name)
        {
            case '': // Minor optimization for multi-bulk replies
                break;
            case 'config':
            case 'hgetall':
                $keys = $values = array();
                while ($response)
                {
                    $keys[] = array_shift($response);
                    $values[] = array_shift($response);
                }
                $response = count($keys) ? array_combine($keys, $values) : array();
                break;
            case 'info':
                $lines = explode(CRLF, trim($response, CRLF));
                $response = array();
                foreach ($lines as $line)
                {
                    if (!$line || substr($line, 0, 1) == '#')
                    {
                        continue;
                    }
                    list($key, $value) = explode(':', $line, 2);
                    $response[$key] = $value;
                }
                break;
            case 'ttl':
                if ($response === -1)
                {
                    $response = false;
                }
                break;
            case 'hmget':
                if (count($arguments) != count($response))
                {
                    throw new CredisException(
                        'hmget arguments and response do not match: ' . print_r($arguments, true) . ' ' . print_r(
                            $response, true
                        )
                    );
                }
                // rehydrate results into key => value form
                $response = array_combine($arguments, $response);
                break;

            case 'scan':
            case 'sscan':
                $arguments[0] = intval(array_shift($response));
                $response = empty($response[0]) ? array() : $response[0];
                break;
            case 'hscan':
            case 'zscan':
                $arguments[0] = intval(array_shift($response));
                $response = empty($response[0]) ? array() : $response[0];
                if (!empty($response) && is_array($response))
                {
                    $count = count($response);
                    $out = array();
                    for ($i = 0; $i < $count; $i += 2)
                    {
                        $out[$response[$i]] = $response[$i + 1];
                    }
                    $response = $out;
                }
                break;
            case 'zrangebyscore':
            case 'zrevrangebyscore':
            case 'zrange':
            case 'zrevrange':
                if (in_array('withscores', $arguments, true))
                {
                    // Map array of values into key=>score list like phpRedis does
                    $item = null;
                    $out = array();
                    foreach ($response as $value)
                    {
                        if ($item == null)
                        {
                            $item = $value;
                        }
                        else
                        {
                            // 2nd value is the score
                            $out[$item] = (float)$value;
                            $item = null;
                        }
                    }
                    $response = $out;
                }
                break;
        }

        return $response;
    }

    /**
     * Build the Redis unified protocol command
     *
     * @param array $args
     * @return string
     */
    private static function _prepare_command($args)
    {
        return sprintf('*%d%s%s%s', count($args), CRLF, implode(CRLF, array_map(array('self', '_map'), $args)), CRLF);
    }

    private static function _map($arg)
    {
        return sprintf('$%d%s%s', strlen($arg), CRLF, $arg);
    }

    /**
     * Flatten arguments
     *
     * If an argument is an array, the key is inserted as argument followed by the array values
     *  array('zrangebyscore', '-inf', 123, array('limit' => array('0', '1')))
     * becomes
     *  array('zrangebyscore', '-inf', 123, 'limit', '0', '1')
     *
     * @param array $in
     * @return array
     */
    private static function _flattenArguments(array $arguments, &$out = array())
    {
        foreach ($arguments as $key => $arg) {
            if (!is_int($key)) {
                $out[] = $key;
            }

            if (is_array($arg)) {
                self::_flattenArguments($arg, $out);
            } else {
                $out[] = $arg;
            }
        }

        return $out;
    }
}
