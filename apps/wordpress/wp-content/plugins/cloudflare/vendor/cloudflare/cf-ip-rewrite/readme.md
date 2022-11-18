# Cloudflare PHP IP Rewriting

This module makes it easy for developers to add rewrite Cloudflare IP Addresses for actual end-user IP Addresses at the application layer. It is recommended to either install mod_cloudflare for Apache or use nginx rewrite rules (<https://support.cloudflare.com/hc/en-us/articles/200170706-Does-CloudFlare-have-an-IP-module-for-Nginx->) if possible.

For those cases, where the IP can not be guaranteed to be rewritten by one of these alternate means, this module can be used to rewrite the IP address.

### How it works
```php
    $ipRewrite = new CloudFlare\IpRewrite();
    $is_cf = $ipRewrite->isCloudFlare();
    $rewritten_ip = $ipRewrite->getRewrittenIP();
    $original_ip = $ipRewrite->getOriginalIP();
```
The class exposes three methods for interaction and a constructor. 

Initializing `IpRewrite()` object will try to rewrite the IP. If the IP is rewritten, `$_SERVER["REMOTE_ADDR"]` will be updated to reflect the end-user's IP address.

`isCloudFlare();` returns `true` if the `CF_CONNECTING_IP` header is present in the request and the request originates from a Cloudflare IP.

`getRewrittenIP()` Returns the rewritten ip address if a rewrite occurs, otherwise it will return `null`.

`getOriginalIP()` returns the saved original ip address from `$_SERVER["REMOTE_ADDR"]`.

### Best Pratice

```php
    // Initialize object to rewrite the headers
    try {
        $ipRewrite = new CloudFlare\IpRewrite();
    } catch (RuntimeException $e) {
        // PHP configurations does not support IPv6
    }
    
    // Check if the request is from Cloudflare
    $is_cf = $ipRewrite->isCloudFlare();
    if ($is_cf) {
        // Get original or rewritten ip
        // Order does not matter
        ...
        $rewritten_ip = $ipRewrite->getRewrittenIP();
        ...
        $original_ip = $ipRewrite->getOriginalIP();
        ...
    }
```

#### Caution

Rewrite action is triggered only once in constructor. If `getRewrittenIP()` or `getOriginalIP()` is called multiple times it'll return the first result regardless if a change happened after the first call. Since rewrite action was not triggered.

To get the newest changes a new `IpRewrite` object should be used.

### Testing this module

This module comes with a set of tests that can be run using phpunit. To run the tests, run `composer install` on the package and then one of the following commands:

#### Basic Tests

    composer test

#### With code coverage report in `coverage` folder

    vendor/bin/phpunit -c phpunit.xml.dist --coverage-html coverage
