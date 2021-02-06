<?php

namespace HashmatWaziri\LaravelMultiAuthImpersonate\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TakeImpersonation
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /** @var Authenticatable */
    public $impersonator;

    /** @var Authenticatable */
    public $impersonated;

    /**
     * Create a new event instance.
     *
     * @return  void
     */
    public function __construct(Authenticatable $impersonator, Authenticatable $impersonated)
    {
        $this->impersonator = $impersonator;
        $this->impersonated = $impersonated;
    }
}
