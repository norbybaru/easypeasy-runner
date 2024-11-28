[![Run Unit Tests](https://github.com/norbybaru/easypeasy-runner/actions/workflows/test.yml/badge.svg)](https://github.com/norbybaru/easypeasy-runner/actions/workflows/test.yml) [![Check & fix Code styling](https://github.com/norbybaru/easypeasy-runner/actions/workflows/pint.yml/badge.svg)](https://github.com/norbybaru/easypeasy-runner/actions/workflows/pint.yml)


# Background Job Runner
A tailored custom system to execute PHP classes as background jobs, independent of Laravel's built-in queue system.

## Feature
- Priority queue
- Retry attempt
- Delay process
- Retry failed jobs 

## Installation
```php
composer require norbybaru/easypeasy-runner
```

## Publish configuration
```bash
php artisan vendor:publish --tag="easypeasy-runner-config"
```

## Publish migration
```bash
php artisan vendor:publish --tag="easypeasy-runner-migration"
```

Run migration
```bash
php artisan migrate
```

## Usage

### Configure Whitelist class namespaces 
To prevent execution of unauthorized classes, you should update `config/easypeasy-runner.php` file
with allowed class namespace or fully qualified class name.

eg. 
```php
<?php

return [
    ....
    'allowed_namespaces' => [
        'App\\Services\\'
    ],
    ...
]
```

### Start background process
Run the following artisan command to start processing background job
```bash
php artisan background:jobs:process
```

### Basic
```php
<?php

use App\Services\EmailService;
use function NorbyBaru\EasyRunner\runBackgroundJob;

runBackgroundJob(
    className: EmailService::class, 
    methodName: 'sendNotification',
    params: [
        'user@example.com', 
        'Welcome',
        'This is welcome message'
    ]
);
```

PS. Ensure to set params value in correct orders as in function definition
eg.
```php
<?php

class EmailService
{
    public function sendNotification(string $email, string $subject, string $message)
    {
        // Execute
    }
}
```

### Advanced
#### Configure priority job 
Available options: `low`, `medium`, `high`
```php
<?php

use function NorbyBaru\EasyRunner\runBackgroundJob;

runBackgroundJob(
    className: EmailService::class,
    methodName: 'sendUrgentNotification',
    params: [
        'user@example.com',
        'Emergency Alert'
    ],
    options: [
        'priority' => 'high'
    ]
);
```

#### Configure delay (seconds) job
```php
<?php

use function NorbyBaru\EasyRunner\runBackgroundJob;

runBackgroundJob(
    className: EmailService::class,
    methodName: 'sendUrgentNotification',
    params: [
        'user@example.com',
        'Emergency Alert'
    ],
    options: [
        'priority' => 'low',
        'delay' => 120
    ]
);

```

#### Override Retry configuration
```php
<?php
use App\Services\ReportGenerator;
use function NorbyBaru\EasyRunner\runBackgroundJob;

runBackgroundJob(
    className: ReportGenerator::class, 
    methodName: 'generateMonthlyReport', 
    params: [
        '2023-11'
    ], 
    options: [
        'retry_attempts' => 5
    ]
);
```

## Monitoring
### View info logs
New file are generated daily with date format `background_jobs-YYY-MM-DD`. 
eg. `background_jobs-2024-11-23.log`.
```bash
tail -f background_jobs-2024-11-23.log
```

### View Error logs
New file are generated daily with date format `background_jobs-YYY-MM-DD`.
eg. `background_jobs_errors-2024-11-23.log`.
```bash
tail -f background_jobs_errors-2024-11-23.log
```

### Cleanup Jobs table
```bash
php artisan background:jobs:cleanup
```

### Jobs Stats
Display Background Jobs Stats
```bash
php artisan background:jobs:stats
```

Live update of Background Jobs Stats
```bash
php artisan background:jobs:stats --live
```

Display only failed Jobs
```bash
php artisan background:jobs:stats --failed
```

### Retry Failed Jobs
Retry all failed jobs
```bash
php artisan background:jobs:retry-failed
```

Retry a single failed job
```bash
php artisan background:jobs:retry-failed --id={jobID}
```