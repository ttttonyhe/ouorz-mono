![Build Status](https://github.com/colinmollenhour/credis/actions/workflows/ci.yml/badge.svg)

# Credis

Credis is a lightweight interface to the [Redis](http://redis.io/) key-value store which wraps the [phpredis](https://github.com/nicolasff/phpredis)
library when available for better performance. This project was forked from one of the many redisent forks.

## Getting Started

Credis_Client uses methods named the same as Redis commands, and translates return values to the appropriate
PHP equivalents.

```php
require 'Credis/Client.php';
$redis = new Credis_Client('localhost');
$redis->set('awesome', 'absolutely');
echo sprintf('Is Credis awesome? %s.\n', $redis->get('awesome'));

// When arrays are given as arguments they are flattened automatically
$redis->rpush('particles', array('proton','electron','neutron'));
$particles = $redis->lrange('particles', 0, -1);
```
Redis error responses will be wrapped in a CredisException class and thrown.

Credis_Client also supports transparent command renaming. Write code using the original command names and the
client will send the aliased commands to the server transparently. Specify the renamed commands using a prefix
for md5, a callable function, individual aliases, or an array map of aliases. See "Redis Security":http://redis.io/topics/security for more info.

## Supported connection string formats

```php
$redis = new Credis_Client(/* connection string */);
```

### Unix socket connection string

`unix:///path/to/redis.sock` 

### TCP connection string

`tcp://host[:port][/persistence_identifier]` 

### TLS connection string

`tls://host[:port][/persistence_identifier]` 

or 

`tlsv1.2://host[:port][/persistence_identifier]`

Before php 7.2, `tls://` only supports TLSv1.0, either `ssl://` or `tlsv1.2` can be used to force TLSv1.2 support.

Recent versions of redis do not support the protocols/cyphers that older versions of php default to, which may result in cryptic connection failures.

#### Enable transport level security (TLS)

Use TLS connection string `tls://127.0.0.1:6379` instead of TCP connection `tcp://127.0.0.1:6379` string in order to enable transport level security.

```php
require 'Credis/Client.php';
$redis = new Credis_Client('tls://127.0.0.1:6379');
$redis->set('awesome', 'absolutely');
echo sprintf('Is Credis awesome? %s.\n', $redis->get('awesome'));

// When arrays are given as arguments they are flattened automatically
$redis->rpush('particles', array('proton','electron','neutron'));
$particles = $redis->lrange('particles', 0, -1);
```

## Clustering your servers

Credis also includes a way for developers to fully utilize the scalability of Redis with multiple servers and [consistent hashing](http://en.wikipedia.org/wiki/Consistent_hashing).
Using the [Credis_Cluster](Cluster.php) class, you can use Credis the same way, except that keys will be hashed across multiple servers.
Here is how to set up a cluster:

### Basic clustering example
```php
<?php
require 'Credis/Client.php';
require 'Credis/Cluster.php';

$cluster = new Credis_Cluster(array(
    array('host' => '127.0.0.1', 'port' => 6379, 'alias'=>'alpha'),
    array('host' => '127.0.0.1', 'port' => 6380, 'alias'=>'beta')
));
$cluster->set('key','value');
echo "Alpha: ".$cluster->client('alpha')->get('key').PHP_EOL;
echo "Beta: ".$cluster->client('beta')->get('key').PHP_EOL;
```

### Explicit definition of replicas

The consistent hashing strategy stores keys on a so called "ring". The position of each key is relative to the position of its target node. The target node that has the closest position will be the selected node for that specific key.

To avoid an uneven distribution of keys (especially on small clusters), it is common to duplicate target nodes. Based on the number of replicas, each target node will exist *n times* on the "ring".

The following example explicitly sets the number of replicas to 5. Both Redis instances will have 5 copies. The default value is 128.

```php
<?php
require 'Credis/Client.php';
require 'Credis/Cluster.php';

$cluster = new Credis_Cluster(
    array(
        array('host' => '127.0.0.1', 'port' => 6379, 'alias'=>'alpha'),
        array('host' => '127.0.0.1', 'port' => 6380, 'alias'=>'beta')
    ), 5
);
$cluster->set('key','value');
echo "Alpha: ".$cluster->client('alpha')->get('key').PHP_EOL;
echo "Beta: ".$cluster->client('beta')->get('key').PHP_EOL;
```

## Master/slave replication

The [Credis_Cluster](Cluster.php) class can also be used for [master/slave replication](http://redis.io/topics/replication).
Credis_Cluster will automatically perform *read/write splitting* and send the write requests exclusively to the master server.
Read requests will be handled by all servers unless you set the *write_only* flag to true in the connection string of the master server.

### Redis server settings for master/slave replication

Setting  up master/slave replication is simple and only requires adding the following line to the config of the slave server:

```
slaveof 127.0.0.1 6379
```

### Basic master/slave example
```php
<?php
require 'Credis/Client.php';
require 'Credis/Cluster.php';

$cluster = new Credis_Cluster(array(
    array('host' => '127.0.0.1', 'port' => 6379, 'alias'=>'master', 'master'=>true),
    array('host' => '127.0.0.1', 'port' => 6380, 'alias'=>'slave')
));
$cluster->set('key','value');
echo $cluster->get('key').PHP_EOL;
echo $cluster->client('slave')->get('key').PHP_EOL;

$cluster->client('master')->set('key2','value');
echo $cluster->client('slave')->get('key2').PHP_EOL;
```

### No read on master

The following example illustrates how to disable reading on the master server. This will cause the master server only to be used for writing.
This should only happen when you have enough write calls to create a certain load on the master server. Otherwise this is an inefficient usage of server resources.

```php
<?php
require 'Credis/Client.php';
require 'Credis/Cluster.php';

$cluster = new Credis_Cluster(array(
    array('host' => '127.0.0.1', 'port' => 6379, 'alias'=>'master', 'master'=>true, 'write_only'=>true),
    array('host' => '127.0.0.1', 'port' => 6380, 'alias'=>'slave')
));
$cluster->set('key','value');
echo $cluster->get('key').PHP_EOL;
```
## Automatic failover with Sentinel

[Redis Sentinel](http://redis.io/topics/sentinel) is a system that can monitor Redis instances. You register master servers and Sentinel automatically detects its slaves.

When a master server dies, Sentinel will make sure one of the slaves is promoted to be the new master. This autofailover mechanism will also demote failed masters to avoid data inconsistency.

The [Credis_Sentinel](Sentinel.php) class interacts with the *Redis Sentinel* instance(s) and acts as a proxy. Sentinel will automatically create [Credis_Cluster](Cluster.php) objects and will set the master and slaves accordingly.

Sentinel uses the same protocol as Redis. In the example below we register the Sentinel server running on port *26379* and assign it to the [Credis_Sentinel](Sentinel.php) object.
We then ask Sentinel the hostname and port for the master server known as *mymaster*. By calling the *getCluster* method we immediately get a [Credis_Cluster](Cluster.php) object that allows us to perform basic Redis calls.

```php
<?php
require 'Credis/Client.php';
require 'Credis/Cluster.php';
require 'Credis/Sentinel.php';

$sentinel = new Credis_Sentinel(new Credis_Client('127.0.0.1',26379));
$masterAddress = $sentinel->getMasterAddressByName('mymaster');
$cluster = $sentinel->getCluster('mymaster');

echo 'Writing to master: '.$masterAddress[0].' on port '.$masterAddress[1].PHP_EOL;
$cluster->set('key','value');
echo $cluster->get('key').PHP_EOL;
```
### Additional parameters

Because [Credis_Sentinel](Sentinel.php) will create [Credis_Cluster](Cluster.php) objects using the *"getCluster"* or *"createCluster"* methods, additional parameters can be passed.

First of all there's the *"write_only"* flag. You can also define the selected database and the number of replicas. And finally there's a *"selectRandomSlave"* option.

The *"selectRandomSlave"* flag is used in setups for masters that have multiple slaves. The Credis_Sentinel will either select one random slave to be used when creating the Credis_Cluster object or to pass them all and use the built-in hashing.

The example below shows how to use these 3 options. It selects database 2, sets the number of replicas to 10, it doesn't select a random slave and doesn't allow reading on the master server.

```php
<?php
require 'Credis/Client.php';
require 'Credis/Cluster.php';
require 'Credis/Sentinel.php';

$sentinel = new Credis_Sentinel(new Credis_Client('127.0.0.1',26379));
$cluster = $sentinel->getCluster('mymaster',2,10,false,true);
$cluster->set('key','value');
echo $cluster->get('key').PHP_EOL;
```

## About

&copy; 2011 [Colin Mollenhour](http://colin.mollenhour.com)
&copy; 2009 [Justin Poliey](http://justinpoliey.com)
