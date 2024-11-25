<?php
class RateLimiter {
    private int $limitForPeriod;
    private float $limitRefreshPeriod; // in seconds
    private float $timeoutDuration;   // in seconds
    private array $requests = [];     // Stores timestamps of requests
    private $semId;                   // Semaphore identifier

    /**
     * Constructor for RateLimiter.
     * 
     * @param int $limitForPeriod Maximum number of requests allowed in the period.
     * @param float $limitRefreshPeriod Time period in seconds to refresh the limit.
     * @param float $timeoutDuration Maximum time in seconds to wait for a request to be allowed.
     */
    public function __construct(int $limitForPeriod, float $limitRefreshPeriod, float $timeoutDuration) {
        $this->limitForPeriod = $limitForPeriod;
        $this->limitRefreshPeriod = $limitRefreshPeriod;
        $this->timeoutDuration = $timeoutDuration;

        // Initialize a semaphore
        $this->semId = sem_get(ftok(__FILE__, 'R'));

        if ($this->semId === false) {
            throw new RuntimeException('Unable to create semaphore');
        }
    }

    /**
     * Acquire the semaphore lock.
     */
    private function lock() {
        if (!sem_acquire($this->semId)) {
            throw new RuntimeException('Unable to acquire semaphore lock');
        }
    }

    /**
     * Release the semaphore lock.
     */
    private function unlock() {
        if (!sem_release($this->semId)) {
            throw new RuntimeException('Unable to release semaphore lock');
        }
    }

    /**
     * Checks if a request can proceed within the rate limit.
     *
     * @return bool True if allowed, False otherwise.
     */
    public function allowRequest(): bool {
        $this->lock();
        $now = microtime(true);

        // Remove requests outside the current refresh window
        $this->requests = array_filter($this->requests, function ($timestamp) use ($now) {
            return ($now - $timestamp) <= $this->limitRefreshPeriod;
        });

        // Check if request can proceed
        if (count($this->requests) < $this->limitForPeriod) {
            $this->requests[] = $now; // Add the new request timestamp
            $this->unlock();
            return true;
        }

        $this->unlock();
        return false;
    }

    /**
     * Waits until a request is allowed or the timeout expires.
     *
     * @return bool True if allowed after waiting, False if timeout.
     */
    public function waitForRequest(): bool {
        $start = microtime(true);

        while ((microtime(true) - $start) <= $this->timeoutDuration) {
            if ($this->allowRequest()) {
                return true;
            }
            usleep(10000); // Sleep for 10ms to prevent busy-waiting
        }

        return false;
    }
}
?>
