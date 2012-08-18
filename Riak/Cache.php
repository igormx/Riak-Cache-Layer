<?php
namespace Riak;
include 'Riak/Cache/Exception.php';

use Riak\Cache\Exception;

/**
 *
 * @author usuario
 *        
 */
class Cache
{

    /**
     * Default Server Values
     */
    const DEFAULT_HOST = '127.0.0.1';

    const DEFAULT_PORT = 8098;

    /**
     *
     * @var \RiakClient
     */
    protected $_riakClient;

    /**
     *
     * @var \RiakBucket
     */
    protected $_riakBucket;

    /**
     *
     * @var array
     */
    protected $_options = array(
            'host' => self::DEFAULT_HOST,
            'port' => self::DEFAULT_PORT
    );

    /**
     *
     * @var string
     */
    protected $_bucketName = "cache";

    /**
     *
     * @var int
     */
    protected $_lifeTime = 600;

    /**
     *
     * @param array $options            
     * @param int $lifetime            
     * @param string $bucketName            
     */
    public function __construct (array $options = null, $lifetime = 0, 
            $bucketName = null)
    {
        if (count($options) > 0) {
            $this->setOptions($options);
        }
        
        if ($lifetime > 0) {
            $this->setLifeTime($lifetime);
        }
        
        if (! is_null($bucketName)) {
            $this->setBucketName($bucketName);
        }
    }

    /**
     *
     *
     *
     * This methond saves the data and sets the expiration time
     *
     * @param
     *            the data $data
     * @param unique $id            
     * @throws Exception
     */
    public function save ($data, $id)
    {
        if ($this->getLifeTime() <= 0) {
            throw new Exception("Lifetime has to be grater than 0");
        }
        
        $realData = array(
                'realData' => $data,
                'expirationTime' => time() + $this->getLifeTime()
        );
        
        $result = $this->getRiakBucket()
            ->newObject($id, $realData)
            ->store();
        
        return $result;
    }

    /**
     * Returns the data cached
     *
     * @param string $id            
     * @return boolean
     */
    public function load ($id)
    {
        $cacheObject = $this->getRiakBucket()->get($id);
        
        // if there is no cache with that id stored
        if (! $cacheObject->exists()) {
            return false;
        } else {
            $datos = $cacheObject->getData();
            if (time() > $datos['expirationTime']) {
                return false;
            }
            return $datos['realData'];
        }
    }

    /**
     * Removes data from the cache store
     *
     * @param string $id            
     */
    public function clear ($id)
    {
        $cacheObject = $this->getRiakBucket()->get($id);
        $cacheObject->delete();
    }

    /**
     *
     * @return the $_lifeTime
     */
    public function getLifeTime ()
    {
        return $this->_lifeTime;
    }

    /**
     *
     * @param number $_lifeTime            
     */
    public function setLifeTime ($_lifeTime)
    {
        $this->_lifeTime = $_lifeTime;
    }

    /**
     *
     * @return \RiakClient
     */
    public function getRiakClient ()
    {
        if ($this->_riakClient === null) {
            $this->_riakClient = new \RiakClient($this->_options['host'], 
                    $this->_options['port']);
        }
        return $this->_riakClient;
    }

    /**
     *
     * @return \RiakBucket
     */
    public function getRiakBucket ()
    {
        if ($this->_riakBucket === null) {
            $this->_riakBucket = $this->getRiakClient()->bucket(
                    $this->getBucketName());
        }
        return $this->_riakBucket;
    }

    /**
     *
     * @return the $_options
     */
    public function getOptions ()
    {
        return $this->_options;
    }

    /**
     *
     * @return the $_bucketName
     */
    public function getBucketName ()
    {
        return $this->_bucketName;
    }

    /**
     *
     * @param string $_bucketName            
     */
    public function setBucketName ($_bucketName)
    {
        $this->_bucketName = $_bucketName;
        return $this;
    }

    /**
     *
     * @param RiakClient $_riakClient            
     */
    public function setRiakClient ($_riakClient)
    {
        $this->_riakClient = $_riakClient;
        return $this;
    }

    /**
     *
     * @param RiakBucket $_riakBucket            
     */
    public function setRiakBucket ($_riakBucket)
    {
        $this->_riakBucket = $_riakBucket;
        return $this;
    }

    /**
     *
     * @param multitype: $_options            
     */
    public function setOptions ($_options)
    {
        $this->_options = $_options;
        return $this;
    }
}