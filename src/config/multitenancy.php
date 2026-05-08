<?php

use Analytica\TenancyCore\Models\Landlord\Tenant;
use Analytica\TenancyCore\Tasks\PrefixCacheTask;
use Analytica\TenancyCore\Tasks\ResetPermissionCacheTask;
use Analytica\TenancyCore\Tasks\SwitchTenantDatabaseTask;
use Analytica\TenancyCore\TenantFinder\SmartCampusTenantFinder;
use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Notifications\SendQueuedNotifications;
use Spatie\Multitenancy\Actions\ForgetCurrentTenantAction;
use Spatie\Multitenancy\Actions\MakeQueueTenantAwareAction;
use Spatie\Multitenancy\Actions\MakeTenantCurrentAction;
use Spatie\Multitenancy\Actions\MigrateTenantAction;

return [
    'tenant_finder' => SmartCampusTenantFinder::class,

    'tenant_artisan_search_fields' => [
        'id',
        'slug',
        'name',
        'database',
    ],

    'switch_tenant_tasks' => [
        SwitchTenantDatabaseTask::class => [
            'connectionName' => config('tenancy-core.tenant_connection', 'tenant'),
        ],
        PrefixCacheTask::class => [
            'enabled' => (bool) config('tenancy-core.cache_prefixing.enabled', true),
        ],
        ResetPermissionCacheTask::class => [
            'enabled' => (bool) config('tenancy-core.permission_cache_reset.enabled', true),
        ],
    ],

    'tenant_model' => Tenant::class,

    'queues_are_tenant_aware_by_default' => true,

    'tenant_database_connection_name' => config('tenancy-core.tenant_connection', 'tenant'),

    'landlord_database_connection_name' => config('tenancy-core.landlord_connection', 'landlord'),

    'current_tenant_container_key' => 'currentTenant',

    'actions' => [
        'make_tenant_current_action' => MakeTenantCurrentAction::class,
        'forget_current_tenant_action' => ForgetCurrentTenantAction::class,
        'make_queue_tenant_aware_action' => MakeQueueTenantAwareAction::class,
        'migrate_tenant' => MigrateTenantAction::class,
    ],

    'queueable_to_job' => [
        SendQueuedMailable::class => 'mailable',
        SendQueuedNotifications::class => 'notification',
        CallQueuedListener::class => 'class',
        BroadcastEvent::class => 'event',
    ],
];
