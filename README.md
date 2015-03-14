# AOE Cache Refresh

Author: [Fabrizio Branca](http://fbrnc.net)

This module allows you the refresh caches while they not expired.

When "force refreshing" a page (e.g. via CTRL+F5) the browser sends additional headers to the servers:
- HTTP_PRAGMA: no_cache
- HTTP_CACHE_CONTROL: no_cache

This module allows you to configure a list of IP addresses that can bypass loading anything from the cache when these headers are being detected.

There are many use cases where this behavior comes in handy: 
- During development you can easily selectively bypass all caches for a single request without going flushing the complete cache.
- You can setup a cache warmer that allows to refresh caches even if they're not expired yet. That means you don't have to flush the cache first.
- "Have you flushed the caches?" is no excuse anymore since you can allow the merchant to do this selectively while he's looking at a page without affecting the overall performance much.


## Configuration

Add this to `app/etc/local.xml`

```
<config>
    <global>
        <aoe_cacherefresh>
            <allowed_ips>INSERT COMMA-SEPARATED LIST OF IP ADDRESSES HERE</allowed_ips>
        </aoe_cacherefresh>
    </global>
</config>
```

## Change log

* 0.0.1: First release

## Usage

1. Install module ([Instructions](http://fbrnc.net/blog/2014/11/how-to-install-a-magento-module))
2. Configure allowed IP addresses in `app/etc/local.xml`
3. Hit CTRL+F5 to refresh a page while bypassing any caches.

## Advanced usage

### Blacklist and Whitelist

By default all cache loading will be bypassed if the `PRAGMA`or the `CACHE_CONTROL` headers are set to `no_cache`. In addition to that you can
control which caches are being bypassed more finegrained by defining a blacklist or a whitelist regex pattern:

#### Example: Bypass all caches **except** all cache id that contain 'config' or 'layout' (so those will be loaded from cache as usual)

```
curl -H "PRAGMA: no-cache" -H "X-CACHEREFRESH-BLACKLIST: #.*(config|layout).*#i" -s -X GET -I http://www.example.com/examplepage
```

### Example: Only bypass a specific cache keys containing 'helloworld'

```
curl -H "PRAGMA: no-cache" -H "X-CACHEREFRESH-WHITELIST: #.*helloworld.*#i" -s -X GET -I http://www.example.com/examplepage
```

#### Limitation

The whitelist and the blacklist pattern only work on cache keys, not cache tags. Since cache tags are not being used while loading content from the 
cache this is not possible.

If a whitelist and a blacklist are specified the the cache key has to match the whitelist pattern first and then also not match the blacklist pattern after that.

### Cache warming

```
curl -H "PRAGMA: no-cache" -s -X GET -I http://www.example.com/examplepage
```

or indirectly (e.g. if the website is not accessible from outside at this point): 

```
curl -H "Host: www.example.com" -H "PRAGMA: no-cache" -s -X GET -I http://127.0.0.1/examplepage
```

Find more advanced examples and code snippets in this (German) [blog post](http://www.webguys.de/magento/adventskalender/turchen-08-magento-cache-warming-und-weitere-caching-tricks/)

Example:

```
for i in `n98-magerun.phar sys:url:list --add-all 1`; do curl -H "PRAGMA: no-cache" -s -X GET -I $i; done
```

### Debugging

Since `Mage_Core_Model_Cache->load()` is being called very early in Magento's request lifecycle `Mage::log()` can't be used to debug any ids, ips, whitelists or blacklists.
If you want to use the buildt-in (disabled by default) simple logging to a file temporarily set `Aoe_CacheRefresh_Model_Cache->cacheRefreshDebug = true`.
