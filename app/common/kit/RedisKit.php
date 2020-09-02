<?php
namespace app\common\kit;

class RedisKit
{

    private static $redis = null;
    private static $instance = null;
    private static $config = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '',
        'timeout' => 0,
        'expire' => 0,
        'pconnect' => false,
        'prefix' => 'fenxiaotpl',
        'select' => 0,
    ];

    private function __construct($config = [])
    {
        //判断是否有加载redis扩展
        if (!extension_loaded("redis")) {
            throw new \BadFunctionCallException('not support: redis');
        }

        // 整合配置
        self::$config = array_merge(self::$config, $config);

        $connect = self::$config['pconnect'] ? 'pconnect' : 'connect';
        self::$redis = new \Redis;
        $ret = self::$redis->$connect(self::$config['host'], self::$config['port'], self::$config['timeout']);

        if (!$ret) {
            throw new \BadFunctionCallException('connect redis fail');
        }

        if ('' != self::$config['password']) {
            self::$redis->auth(self::$config['password']);
        }

        if (0 != self::$config['select']) {
            self::$redis->select(self::$config['select']);
        }

    }

    public static function getInstance($config = [])
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public static function get($key, $default = '')
    {
        $key = self::getKey($key);

        $value = self::$redis->get($key);
        if ($value === false && !empty($default)) {
            self::$redis->set($key, $default);
            $value = $default;
        }
        return $value;
    }

    public static function getKey($key)
    {
        $key = self::$config['select'] . ":" . self::$config['prefix'] . ":" . $key;
        return $key;
    }

    public static function set($key, $value, $expire = 0)
    {
        $key = self::getKey($key);
        if ($expire === 0) {
            $expire = self::$config['expire'];
        }
        return self::$redis->set($key, $value, $expire);
    }



    // 防止克隆对象
    private function __clone(){

    }


    public static function psetex($key, $ttl, $value)
    {
        $key = self::getKey($key);
        return self::$redis->psetex($key, $ttl, $value);
    }


    public static function sScan($key, $iterator, $pattern = '', $count = 0)
    {
        $key = self::getKey($key);
        return self::$redis->sScan($key, $iterator, $pattern, $count);
    }

    public static function scan(&$iterator, $pattern = null, $count = 0)
    {
        return self::$redis->scan($iterator, $pattern, $count);
    }

    public static function zScan($key, $iterator, $pattern = '', $count = 0)
    {
        $key = self::getKey($key);
        return self::$redis->zScan($key, $iterator, $pattern, $count);
    }

    public static function hScan($key, $iterator, $pattern = '', $count = 0)
    {
        $key = self::getKey($key);
        return self::$redis->hScan($key, $iterator, $pattern, $count);
    }

    public static function client($command, $arg = '')
    {
        return self::$redis->client($command, $arg);
    }

    public static function slowlog($command)
    {
        return self::$redis->slowlog($command);
    }

    public static function open($host, $port = 6379, $timeout = 0.0, $retry_interval = 0)
    {
        return self::$redis->open($host, $port, $timeout, $retry_interval);
    }


    public static function close()
    {
        self::$redis->close();
    }

    public static function setOption($name, $value)
    {
        return self::$redis->setOption($name, $value);
    }

    public static function getOption($name)
    {
        return self::$redis->getOption($name);
    }

    public static function ping()
    {
        return self::$redis->ping();
    }


    public static function setex($key, $ttl, $value)
    {
        $key = self::getKey($key);
        return self::$redis->setex($key, $ttl, $value);
    }

    public static function setnx($key, $value)
    {
        $key = self::getKey($key);
        return self::$redis->setnx($key, $value);
    }

    public static function del($key)
    {
        $key = self::getKey($key);
        return self::$redis->del($key);
    }

    public static function delete($key)
    {
        $key = self::getKey($key);
        return self::$redis->delete($key);
    }

    public static function multi($mode = \Redis::MULTI)
    {
        return self::$redis->multi($mode);
    }

    public static function exec()
    {
        return self::$redis->exec();
    }

    public static function discard()
    {
        self::$redis->discard();
    }

    public static function watch($key)
    {
        $key = self::getKey($key);
        self::$redis->watch($key);
    }

    public static function unwatch()
    {
        self::$redis->unwatch();
    }

    public static function subscribe($channels, $callback)
    {
        self::$redis->subscribe($channels, $callback);
    }

    public static function psubscribe($patterns, $callback)
    {
        self::$redis->psubscribe($patterns, $callback);
    }

    public static function publish($channel, $message)
    {
        return self::$redis->publish($channel, $message);
    }

    public static function pubsub($keyword, $argument)
    {
        return self::$redis->pubsub($keyword, $argument);
    }

    public static function exists($key)
    {
        $key = self::getKey($key);
        return self::$redis->exists($key);
    }

    public static function incr($key)
    {
        $key = self::getKey($key);
        return self::$redis->incr($key);
    }

    public static function incrByFloat($key, $increment)
    {
        $key = self::getKey($key);
        return self::$redis->incrByFloat($key, $increment);
    }

    public static function incrBy($key, $value)
    {
        $key = self::getKey($key);
        return self::$redis->incrBy($key, $value);
    }

    public static function decr($key)
    {
        $key = self::getKey($key);
        return self::$redis->decr($key);
    }

    public static function decrBy($key, $value)
    {
        $key = self::getKey($key);
        return self::$redis->decrBy($key, $value);
    }

    public static function getMultiple(array $keys)
    {
        return self::$redis->getMultiple($keys);
    }

    public static function lPush($key, $value1)
    {
        $key = self::getKey($key);
        return self::$redis->lPush($key, $value1);
    }

    public static function rPush($key, $value1)
    {
        $key = self::getKey($key);
        return self::$redis->rPush($key, $value1);
    }

    public static function lPushx($key, $value)
    {
        $key = self::getKey($key);
        return self::$redis->lPushx($key, $value);
    }

    public static function rPushx($key, $value)
    {
        $key = self::getKey($key);
        return self::$redis->rPushx($key, $value);
    }

    public static function lPop($key)
    {
        $key = self::getKey($key);
        return self::$redis->lPop($key);
    }

    public static function rPop($key)
    {
        $key = self::getKey($key);
        return self::$redis->rPop($key);
    }

    public static function blPop(array $keys, $timeout)
    {
        return self::$redis->blPop($keys, $timeout);
    }

    public static function brPop(array $keys, $timeout)
    {
        return self::$redis->brPop($keys, $timeout);
    }

    public static function lLen($key)
    {
        $key = self::getKey($key);
        return self::$redis->lLen($key);
    }

    public static function lSize($key)
    {
        $key = self::getKey($key);
        self::$redis->lSize($key);
    }

    public static function lIndex($key, $index)
    {
        $key = self::getKey($key);
        return self::$redis->lIndex($key, $index);
    }

    public static function lGet($key, $index)
    {
        $key = self::getKey($key);
        self::$redis->lGet($key, $index);
    }

    public static function lSet($key, $index, $value)
    {
        $key = self::getKey($key);
        return self::$redis->lSet($key, $index, $value);
    }

    public static function lRange($key, $start, $end)
    {
        $key = self::getKey($key);
        return self::$redis->lRange($key, $start, $end);
    }

    public static function lGetRange($key, $start, $end)
    {
        $key = self::getKey($key);
        self::$redis->lGetRange($key, $start, $end);
    }

    public static function lTrim($key, $start, $stop)
    {
        $key = self::getKey($key);
        return self::$redis->lTrim($key, $start, $stop);
    }

    public static function listTrim($key, $start, $stop)
    {
        $key = self::getKey($key);
        self::$redis->listTrim($key, $start, $stop);
    }

    public static function lRem($key, $value, $count)
    {
        $key = self::getKey($key);
        return self::$redis->lRem($key, $value, $count);
    }

    public static function lRemove($key, $value, $count)
    {
        $key = self::getKey($key);
        self::$redis->lRemove($key, $value, $count);
    }

    public static function lInsert($key, $position, $pivot, $value)
    {
        $key = self::getKey($key);
        return self::$redis->lInsert($key, $position, $pivot, $value);
    }

    public static function sAdd($key, $value1)
    {
        $key = self::getKey($key);
        return self::$redis->sAdd($key, $value1);
    }

    public static function sAddArray($key, array $values)
    {
        $key = self::getKey($key);
        return self::$redis->sAddArray($key, $values);
    }

    public static function sRem($key, $member1)
    {
        $key = self::getKey($key);
        return self::$redis->sRem($key, $member1);
    }

    public static function sRemove($key, $member1)
    {
        $key = self::getKey($key);
        self::$redis->sRemove($key, $member1);
    }

    public static function sMove($srcKey, $dstKey, $member)
    {
        return self::$redis->sMove($srcKey, $dstKey, $member);
    }

    public static function sIsMember($key, $value)
    {
        $key = self::getKey($key);
        return self::$redis->sIsMember($key, $value);
    }

    public static function sContains($key, $value)
    {
        $key = self::getKey($key);
        self::$redis->sContains($key, $value);
    }

    public static function sCard($key)
    {
        $key = self::getKey($key);
        return self::$redis->sCard($key);
    }

    public static function sPop($key)
    {
        $key = self::getKey($key);
        return self::$redis->sPop($key);
    }

    public static function sRandMember($key, $count = null)
    {
        $key = self::getKey($key);
        return self::$redis->sRandMember($key, $count);
    }


    public static function sUnion($key1, $key2)
    {
        $key1 = self::getKey($key1);
        $key2 = self::getKey($key2);
        return self::$redis->sUnion($key1, $key2);
    }




    public static function sMembers($key)
    {
        $key = self::getKey($key);
        return self::$redis->sMembers($key);
    }

    public static function sGetMembers($key)
    {
        $key = self::getKey($key);
        self::$redis->sGetMembers($key);
    }

    public static function getSet($key, $value)
    {
        $key = self::getKey($key);
        return self::$redis->getSet($key, $value);
    }

    public static function randomKey()
    {
        return self::$redis->randomKey();
    }

    public static function select($dbindex)
    {
        return self::$redis->select($dbindex);
    }

    public static function move($key, $dbindex)
    {
        $key = self::getKey($key);
        return self::$redis->move($key, $dbindex);
    }

    public static function rename($srcKey, $dstKey)
    {
        return self::$redis->rename($srcKey, $dstKey);
    }

    public static function renameKey($srcKey, $dstKey)
    {
        self::$redis->renameKey($srcKey, $dstKey);
    }

    public static function renameNx($srcKey, $dstKey)
    {
        return self::$redis->renameNx($srcKey, $dstKey);
    }

    public static function expire($key, $ttl)
    {
        $key = self::getKey($key);
        return self::$redis->expire($key, $ttl);
    }

    public static function pExpire($key, $ttl)
    {
        $key = self::getKey($key);
        return self::$redis->pExpire($key, $ttl);
    }

    public static function setTimeout($key, $ttl)
    {
        $key = self::getKey($key);
        self::$redis->setTimeout($key, $ttl);
    }

    public static function expireAt($key, $timestamp)
    {
        $key = self::getKey($key);
        return self::$redis->expireAt($key, $timestamp);
    }

    public static function pExpireAt($key, $timestamp)
    {
        $key = self::getKey($key);
        return self::$redis->pExpireAt($key, $timestamp);
    }

    public static function keys($pattern)
    {
        return self::$redis->keys($pattern);
    }

    public static function getKeys($pattern)
    {
        self::$redis->getKeys($pattern);
    }

    public static function dbSize()
    {
        return self::$redis->dbSize();
    }

    public static function auth($password)
    {
        return self::$redis->auth($password);
    }

    public static function bgrewriteaof()
    {
        return self::$redis->bgrewriteaof();
    }

    public static function slaveof($host = '127.0.0.1', $port = 6379)
    {
        return self::$redis->slaveof($host, $port);
    }

    public static function object($string = '', $key = '')
    {
        $key = self::getKey($key);
        return self::$redis->object($string, $key);
    }

    public static function save()
    {
        return self::$redis->save();
    }

    public static function bgsave()
    {
        return self::$redis->bgsave();
    }

    public static function lastSave()
    {
        return self::$redis->lastSave();
    }

    public static function wait($numSlaves, $timeout)
    {
        return self::$redis->wait($numSlaves, $timeout);
    }

    public static function type($key)
    {
        $key = self::getKey($key);
        return self::$redis->type($key);
    }

    public static function append($key, $value)
    {
        $key = self::getKey($key);
        return self::$redis->append($key, $value);
    }

    public static function getRange($key, $start, $end)
    {
        $key = self::getKey($key);
        return self::$redis->getRange($key, $start, $end);
    }

    public static function substr($key, $start, $end)
    {
        $key = self::getKey($key);
        self::$redis->substr($key, $start, $end);
    }

    public static function setRange($key, $offset, $value)
    {
        $key = self::getKey($key);
        return self::$redis->setRange($key, $offset, $value);
    }

    public static function strlen($key)
    {
        $key = self::getKey($key);
        return self::$redis->strlen($key);
    }

    public static function bitpos($key, $bit, $start = 0, $end = null)
    {
        $key = self::getKey($key);
        return self::$redis->bitpos($key, $bit, $start, $end);
    }

    public static function getBit($key, $offset)
    {
        $key = self::getKey($key);
        return self::$redis->getBit($key, $offset);
    }

    public static function setBit($key, $offset, $value)
    {
        $key = self::getKey($key);
        return self::$redis->setBit($key, $offset, $value);
    }

    public static function bitCount($key)
    {
        $key = self::getKey($key);
        return self::$redis->bitCount($key);
    }


    public static function flushDB()
    {
        return self::$redis->flushDB();
    }

    public static function flushAll()
    {
        return self::$redis->flushAll();
    }

    public static function sort($key, $option = null)
    {
        $key = self::getKey($key);
        return self::$redis->sort($key, $option);
    }

    public static function info($option = null)
    {
        return self::$redis->info($option);
    }

    public static function resetStat()
    {
        return self::$redis->resetStat();
    }

    public static function ttl($key)
    {
        $key = self::getKey($key);
        return self::$redis->ttl($key);
    }

    public static function pttl($key)
    {
        $key = self::getKey($key);
        return self::$redis->pttl($key);
    }

    public static function persist($key)
    {
        $key = self::getKey($key);
        return self::$redis->persist($key);
    }

    public static function mset(array $array)
    {
        return self::$redis->mset($array);
    }

    public static function mget(array $array)
    {
        return self::$redis->mget($array);
    }

    public static function msetnx(array $array)
    {
        return self::$redis->msetnx($array);
    }

    public static function rpoplpush($srcKey, $dstKey)
    {
        return self::$redis->rpoplpush($srcKey, $dstKey);
    }

    public static function brpoplpush($srcKey, $dstKey, $timeout)
    {
        return self::$redis->brpoplpush($srcKey, $dstKey, $timeout);
    }

    public static function zAdd($key, $score1, $value1, $score2 = null, $value2 = null, $scoreN = null, $valueN = null)
    {
        $key = self::getKey($key);
        return self::$redis->zAdd($key, $score1, $value1, $score2, $value2, $scoreN, $valueN);
    }

    public static function zRange($key, $start, $end, $withscores = null)
    {
        $key = self::getKey($key);
        return self::$redis->zRange($key, $start, $end, $withscores);
    }

    public static function zRem($key, $member1, $member2 = null, $memberN = null)
    {
        $key = self::getKey($key);
        return self::$redis->zRem($key, $member1, $member2, $memberN);
    }

    public static function zDelete($key, $member1, $member2 = null, $memberN = null)
    {
        $key = self::getKey($key);
        return self::$redis->zDelete($key, $member1, $member2, $memberN);
    }

    public static function zRevRange($key, $start, $end, $withscore = null)
    {
        $key = self::getKey($key);
        return self::$redis->zRevRange($key, $start, $end, $withscore);
    }

    public static function zRangeByScore($key, $start, $end, array $options = array())
    {
        $key = self::getKey($key);
        return self::$redis->zRangeByScore($key, $start, $end, $options);
    }

    public static function zRevRangeByScore($key, $start, $end, array $options = array())
    {
        $key = self::getKey($key);
        return self::$redis->zRevRangeByScore($key, $start, $end, $options);
    }

    public static function zRangeByLex($key, $min, $max, $offset = null, $limit = null)
    {
        $key = self::getKey($key);
        return self::$redis->zRangeByLex($key, $min, $max, $offset, $limit);
    }

    public static function zRevRangeByLex($key, $min, $max, $offset = null, $limit = null)
    {
        $key = self::getKey($key);
        return self::$redis->zRevRangeByLex($key, $min, $max, $offset, $limit);
    }

    public static function zCount($key, $start, $end)
    {
        $key = self::getKey($key);
        return self::$redis->zCount($key, $start, $end);
    }

    public static function zRemRangeByScore($key, $start, $end)
    {
        $key = self::getKey($key);
        return self::$redis->zRemRangeByScore($key, $start, $end);
    }

    public static function zDeleteRangeByScore($key, $start, $end)
    {
        $key = self::getKey($key);
        self::$redis->zDeleteRangeByScore($key, $start, $end);
    }

    public static function zRemRangeByRank($key, $start, $end)
    {
        $key = self::getKey($key);
        return self::$redis->zRemRangeByRank($key, $start, $end);
    }


    public static function zCard($key)
    {
        $key = self::getKey($key);
        return self::$redis->zCard($key);
    }


    public static function zScore($key, $member)
    {
        $key = self::getKey($key);
        return self::$redis->zScore($key, $member);
    }

    public static function zRank($key, $member)
    {
        $key = self::getKey($key);
        return self::$redis->zRank($key, $member);
    }

    public static function zRevRank($key, $member)
    {
        $key = self::getKey($key);
        return self::$redis->zRevRank($key, $member);
    }

    public static function zIncrBy($key, $value, $member)
    {
        $key = self::getKey($key);
        return self::$redis->zIncrBy($key, $value, $member);
    }

    public static function zUnion($Output, $ZSetKeys, array $Weights = null, $aggregateFunction = 'SUM')
    {
        return self::$redis->zUnion($Output, $ZSetKeys, $Weights, $aggregateFunction);
    }

    public static function zInter($Output, $ZSetKeys, array $Weights = null, $aggregateFunction = 'SUM')
    {
        return self::$redis->zInter($Output, $ZSetKeys, $Weights, $aggregateFunction);
    }

    public static function hSet($key, $hashKey, $value)
    {
        $key = self::getKey($key);
        return self::$redis->hSet($key, $hashKey, $value);
    }

    public static function hSetNx($key, $hashKey, $value)
    {
        $key = self::getKey($key);
        return self::$redis->hSetNx($key, $hashKey, $value);
    }

    public static function hGet($key, $hashKey)
    {
        $key = self::getKey($key);
        return self::$redis->hGet($key, $hashKey);
    }

    public static function hLen($key)
    {
        $key = self::getKey($key);
        return self::$redis->hLen($key);
    }

    public static function hDel($key, $hashKey1, $hashKey2 = null, $hashKeyN = null)
    {
        $key = self::getKey($key);
        return self::$redis->hDel($key, $hashKey1, $hashKey2, $hashKeyN);
    }

    public static function hKeys($key)
    {
        $key = self::getKey($key);
        return self::$redis->hKeys($key);
    }

    public static function hVals($key)
    {
        $key = self::getKey($key);
        return self::$redis->hVals($key);
    }

    public static function hGetAll($key)
    {
        $key = self::getKey($key);
        return self::$redis->hGetAll($key);
    }

    public static function hExists($key, $hashKey)
    {
        $key = self::getKey($key);
        return self::$redis->hExists($key, $hashKey);
    }

    public static function hIncrBy($key, $hashKey, $value)
    {
        $key = self::getKey($key);
        return self::$redis->hIncrBy($key, $hashKey, $value);
    }

    public static function hIncrByFloat($key, $field, $increment)
    {
        $key = self::getKey($key);
        return self::$redis->hIncrByFloat($key, $field, $increment);
    }

    public static function hMSet($key, $hashKeys)
    {
        $key = self::getKey($key);
        return self::$redis->hMSet($key, $hashKeys);
    }

    public static function hMGet($key, $hashKeys)
    {
        $key = self::getKey($key);
        return self::$redis->hMGet($key, $hashKeys);
    }

    public static function config($operation, $key, $value)
    {
        $key = self::getKey($key);
        return self::$redis->config($operation, $key, $value);
    }

    public static function evaluate($script, $args = array(), $numKeys = 0)
    {
        return self::$redis->evaluate($script, $args, $numKeys);
    }

    public static function evalSha($scriptSha, $args = array(), $numKeys = 0)
    {
        return self::$redis->evalSha($scriptSha, $args, $numKeys);
    }

    public static function evaluateSha($scriptSha, $args = array(), $numKeys = 0)
    {
        self::$redis->evaluateSha($scriptSha, $args, $numKeys);
    }

    public static function script($command, $script)
    {
        return self::$redis->script($command, $script);
    }

    public static function getLastError()
    {
        return self::$redis->getLastError();
    }

    public static function clearLastError()
    {
        return self::$redis->clearLastError();
    }

    public static function _prefix($value)
    {
        return self::$redis->_prefix($value);
    }

    public static function _unserialize($value)
    {
        return self::$redis->_unserialize($value);
    }

    public static function _serialize($value)
    {
        return self::$redis->_serialize($value);
    }

    public static function dump($key)
    {
        $key = self::getKey($key);
        return self::$redis->dump($key);
    }

    public static function restore($key, $ttl, $value)
    {
        $key = self::getKey($key);
        return self::$redis->restore($key, $ttl, $value);
    }

    public static function migrate($host, $port, $key, $db, $timeout, $copy = false, $replace = false)
    {
        $key = self::getKey($key);
        return self::$redis->migrate($host, $port, $key, $db, $timeout, $copy, $replace);
    }

    public static function time()
    {
        return self::$redis->time();
    }

    public static function pfAdd($key, array $elements)
    {
        $key = self::getKey($key);
        return self::$redis->pfAdd($key, $elements);
    }

    public static function pfCount($key)
    {
        $key = self::getKey($key);
        return self::$redis->pfCount($key);
    }

    public static function pfMerge($destkey, array $sourcekeys)
    {
        return self::$redis->pfMerge($destkey, $sourcekeys);
    }

    public static function rawCommand($command, $arguments)
    {
        return self::$redis->rawCommand($command, $arguments);
    }

    public static function getMode()
    {
        return self::$redis->getMode();
    }


}