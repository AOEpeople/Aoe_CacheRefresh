<?php
/**
 * Class Aoe_CacheRefresh_Model_Cache
 *
 * @author Fabrizio Branca
 * @since 2015-03-02
 */
class Aoe_CacheRefresh_Model_Cache extends Mage_Core_Model_Cache {

    /**
     * @var bool
     */
    protected $bypassCacheLoad;

    /**
     * Overwrite original load method in order to bypass it
     *
     * @param string $id
     * @return bool|string
     */
    public function load($id)
    {
        return $this->bypassCacheLoad() ? false : parent::load($id);
    }

    /**
     * Check if loading from cache should be bypassed for this request
     *
     * @return bool
     * @throws Zend_Controller_Request_Exception
     */
    protected function bypassCacheLoad()
    {
        if (is_null($this->bypassCacheLoad)) {
            $this->bypassCacheLoad = false;
            if (strstr(strtolower(Mage::app()->getRequest()->getHeader('PRAGMA')), 'no-cache') ||
                strstr(strtolower(Mage::app()->getRequest()->getHeader('CACHE_CONTROL')), 'no-cache')
            ) {
                if ($this->checkIp()) {
                    $this->bypassCacheLoad = true;
                }
            }
        }
        return $this->bypassCacheLoad;
    }

    /**
     * Check if current IP address is allowed to bypass loading from cache
     *
     * @return bool
     */
    public function checkIp()
    {
        $allow = true;
        $allowedIps = Mage::getConfig()->getNode('global/aoe_cacherefresh/allowed_ips');
        $remoteAddr = Mage::helper('core/http')->getRemoteAddr();
        if (!empty($allowedIps) && !empty($remoteAddr)) {
            $allowedIps = preg_split('#\s*,\s*#', $allowedIps, null, PREG_SPLIT_NO_EMPTY);
            if (array_search($remoteAddr, $allowedIps) === false
                && array_search(Mage::helper('core/http')->getHttpHost(), $allowedIps) === false) {
                $allow = false;
            }
        }
        return $allow;
    }

}