# Background Job Runner

## Installation
```php
composer require norbybaru/easypeasy-runner
```

## Publish configuration
```bash
php artisan vendor:publish --tag="easypeasy-runner-config
```

## Usage

### Configure Whitelist class namespaces 
To prevent execution of unauthorized classes, you should update `config/easypeasy-runner.php` file
with allowed class namespace.

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
#### Override Retry configuration
```php
<?php
use App\Services\ReportGenerator;
use function NorbyBaru\EasyRunner\runBackgroundJob;

runBackgroundJob(
    className: ReportGenerator::class, 
    methodName: 'generateMonthlyReport', 
    params: ['2023-11'], 
    retryAttempts: 5
);
```
