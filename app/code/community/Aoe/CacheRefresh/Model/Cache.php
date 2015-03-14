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
    protected $noCacheHeaderAndIp;

    /**
     * @var bool
     */
    protected $cacheRefreshDebug = false;

    /**
     * @var string
     */
    protected $debugLogfile = '/tmp/cacherefresh.log';

    /**
     * Overwrite original load method in order to bypass it
     *
     * @param string $id
     * @return bool|string
     */
    public function load($id)
    {
        $this->debugLog("Requested id: $id\n");
        $bypass = $this->checkNoCacheHeaderAndIp() && $this->checkWhitelistAndBlacklist($id);
        $this->debugLog("Bypassed: ".($bypass?"yes":"no")."\n\n");
        return $bypass ? false : parent::load($id);
    }

    /**
     * Check if loading from cache should be bypassed for this request
     *
     * @return bool
     * @throws Zend_Controller_Request_Exception
     */
    protected function checkNoCacheHeaderAndIp()
    {
        if (is_null($this->noCacheHeaderAndIp)) {
            $this->noCacheHeaderAndIp = false;
            if (strstr(strtolower(Mage::app()->getRequest()->getHeader('PRAGMA')), 'no-cache') ||
                strstr(strtolower(Mage::app()->getRequest()->getHeader('CACHE_CONTROL')), 'no-cache')
            ) {
                $this->debugLog("Detected no-cache header\n");
                if ($this->checkIp()) {
                    $this->debugLog("IP address ok\n");
                    $this->noCacheHeaderAndIp = true;
                }
            }
        }
        return $this->noCacheHeaderAndIp;
    }

    /**
     * Check whitelist and blacklist if defined
     *
     * @param $id
     * @return bool
     * @throws Zend_Controller_Request_Exception
     */
    protected function checkWhitelistAndBlacklist($id)
    {
        if ($whitelist = Mage::app()->getRequest()->getHeader('X-CACHEREFRESH-WHITELIST')) {
            if (!preg_match($whitelist, $id)) {
                $this->debugLog("Whitelist detected, but id didn't match the whitelist: $whitelist\n");
                return false;
            }
        }
        if ($blacklist = Mage::app()->getRequest()->getHeader('X-CACHEREFRESH-BLACKLIST')) {

            if (preg_match($blacklist, $id)) {
                $this->debugLog("Blacklist detected, but id did match the blacklist: $blacklist\n");
                return false;
            }
        }
        return true;
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

    /**
     * Simple debug log
     *
     * @param $log
     */
    protected function debugLog($log) {
        if ($this->cacheRefreshDebug) {
            // Mage::log() isn't avaiable at this point...
            file_put_contents($this->debugLogfile, $log, FILE_APPEND);
        }
    }

}