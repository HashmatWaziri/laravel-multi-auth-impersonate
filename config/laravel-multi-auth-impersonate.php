<?php

return [

    /**
     * The session key used to store the original user id.
     */
    'session_key' => 'impersonated_by',

    /**
     * The session guard used to stored the original user guard.
     */
    'session_guard' => 'impersonator_guard',

    /**
     * The impersonated guard used to store the impersonated user guard.
     */
    'impersonated_guard' => 'impersonated_guard',
    /**
     * The session key used to stored what guard is impersonator using.
     */
    'session_guard_using' => 'impersonator_guard_using',

    /**
     * The default impersonator guard used.
     */
    'default_impersonator_guard' => 'web',

    /**
     * The default impersonator guard used.
     */
    'impersonated_key' => 'impersonated_id',



];
