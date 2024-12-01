# Rate Limiter in PHP
A pure PHP implementation of a rate limiter.

##### Blog post - https://blog.niteshapte.com/2024-12-01-rate-limiter-in-php.htm

## Features
- Limit the number of requests within a specific time period.
- Timeout mechanism for requests exceeding the limit.
- Customizable parameters.

## Installation
Clone the repository:
```bash
git clone https://github.com/niteshapte/rate-limiter-php.git
```
## Usage
### Configuration
The rate limiter can be configured in ```rate_limiter_config.php```
```
$rateLimiter = new RateLimiter(
    limitForPeriod: 2,         // Max 2 requests
    limitRefreshPeriod: 1.0,   // Reset limit every 1 second
    timeoutDuration: 0.1       // Timeout after 500 milliseconds
);
```

## Example
Check the ```example.php``` file:
```
<?php
require_once 'rate_limiter_config.php';

/** @var RateLimiter $rateLimiter */
$rateLimiter = require 'rate_limiter_config.php';

// Simulate requests
for ($i = 0; $i < 10; $i++) {
    if ($rateLimiter->allowRequest()) {
        echo "Request $i: Allowed\n";
    } else {
        echo "Request $i: Rate limit exceeded\n";
        if ($rateLimiter->waitForRequest()) {
            echo "Request $i: Allowed after waiting\n";
        } else {
            echo "Request $i: Denied after timeout\n";
        }
    }

    usleep(200000); // Simulate delay between requests (200ms)
}
?>
```
