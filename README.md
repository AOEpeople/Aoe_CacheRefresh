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

### Cache warming

```
curl -H "Accept-Encoding: gzip, deflate" -H "Host: www.example.com" -H "HTTP_PRAGMA: no-cache" -s -X GET -I http://127.0.0.1/examplepage
```

Find more advanced examples and code snippets in this (German) [blog post](http://www.webguys.de/magento/adventskalender/turchen-08-magento-cache-warming-und-weitere-caching-tricks/)