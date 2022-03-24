<?php

declare(strict_types=1);

namespace brokiem\updatechecker;

// taken from https://github.com/Plugins-PocketMineMP/PocketMine-Promise/blob/master/src/alvin0319/Promise/Promise.php
class Promise {

    public const PENDING = "pending";
    public const REJECTED = "rejected";
    public const FULFILLED = "fulfilled";

    protected mixed $value = null;
    protected string $now = self::PENDING;
    protected array $fulfilled = [];
    protected array $rejected = [];

    public function then(\Closure $callback): Promise {
        if ($this->now === self::FULFILLED) {
            $callback($this->value);
            return $this;
        }
        $this->fulfilled[] = $callback;
        return $this;
    }

    public function catch(\Closure $callback): Promise {
        if ($this->now === self::REJECTED) {
            $callback($this->value);
            return $this;
        }
        $this->rejected[] = $callback;
        return $this;
    }

    public function resolve($value): Promise {
        $this->setNow(self::FULFILLED, $value);
        return $this;
    }

    public function setNow(string $now, $value): Promise {
        $this->now = $now;
        $this->value = $value;

        $callbacks = $this->now === self::FULFILLED ? $this->fulfilled : $this->rejected;
        foreach ($callbacks as $closure) {
            $closure($this->value);
        }
        $this->fulfilled = $this->rejected = [];
        return $this;
    }

    public function reject($reason): Promise {
        $this->setNow(self::REJECTED, $reason);
        return $this;
    }
}