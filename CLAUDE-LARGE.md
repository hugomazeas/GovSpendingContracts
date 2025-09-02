# Laravel Production Expert Guidelines

You are an expert Laravel developer building production-ready applications with Laravel Boost MCP server integration. Follow these guidelines to create maintainable, performant, and secure code.

## Environment Context
- **PHP**: 8.3.6
- **Laravel**: v12 (streamlined structure, no app/Http/Middleware/, no app/Console/Kernel.php)
- **Laravel Prompts**: v0
- **Laravel Pint**: v1
- **Laravel Boost**: MCP server with powerful debugging and development tools

## Laravel Boost Integration

### Documentation-First Approach
**ALWAYS use `search-docs` before implementing anything Laravel-related:**
```
Use search-docs tool with multiple broad queries: ['authentication', 'middleware', 'routing']
Pass package arrays to filter: ['laravel/sanctum', 'laravel/fortify'] 
Never add package names to queries - they're auto-detected
```

### Boost Tools Priority
1. **`search-docs`** - Version-specific Laravel ecosystem documentation
2. **`list-artisan-commands`** - Check available Artisan commands and options
3. **`tinker`** - Debug PHP code and query Eloquent models
4. **`database-query`** - Read-only database queries for debugging
5. **`browser-logs`** - Read recent browser errors and exceptions
6. **`get-absolute-url`** - Generate correct project URLs

### Debugging Workflow
```php
// Use tinker tool for Eloquent debugging instead of dd() or var_dump()
User::with(['posts', 'roles'])->find(1);
// Check relationships, test queries, validate data

// Use database-query for complex read operations
SELECT users.*, COUNT(posts.id) as posts_count 
FROM users LEFT JOIN posts ON users.id = posts.user_id 
GROUP BY users.id;

// Use browser-logs to check frontend errors before assuming backend issues
```

### Laravel 12 Streamlined Structure
- **Middleware registration**: Use `bootstrap/app.php`, not middleware files
- **Console commands**: Auto-register from `app/Console/Commands/`
- **Service providers**: Register in `bootstrap/providers.php`
- **Exception handling**: Configure in `bootstrap/app.php`

## Architecture & Design Patterns

### Service Layer Architecture
**Before implementing**: Use `search-docs` with queries like ['service layer', 'dependency injection', 'controllers']

Always separate business logic from controllers using services:

```php
// Controller - thin, handles HTTP concerns only
class UserController extends Controller
{
    public function store(CreateUserRequest $request, UserService $userService): JsonResponse
    {
        $user = $userService->createUser($request->validated());
        return response()->json(new UserResource($user), 201);
    }
}

// Service - contains business logic
class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private NotificationService $notificationService
    ) {}

    public function createUser(array $userData): User
    {
        DB::transaction(function () use ($userData) {
            $user = $this->userRepository->create($userData);
            $this->notificationService->sendWelcomeEmail($user);
            event(new UserCreated($user));
            return $user;
        });
    }
}
```

### Repository Pattern
**Search first**: `search-docs` with ['repository pattern', 'eloquent repositories']

Use repositories for complex data access:

```php
interface UserRepositoryInterface
{
    public function findActiveUsersWithRoles(): Collection;
    public function createWithProfile(array $userData): User;
}

class UserRepository implements UserRepositoryInterface
{
    public function findActiveUsersWithRoles(): Collection
    {
        return User::query()
            ->with(['roles', 'profile'])
            ->where('is_active', true)
            ->get();
    }
}
```

## File Creation Workflow

### Always Use Artisan Commands
**Check available commands first**: Use `list-artisan-commands` tool

```bash
# Create models with related files
php artisan make:model --no-interaction Post --factory --seeder --policy --resource

# Create services and other classes  
php artisan make:class --no-interaction Services/UserService
php artisan make:class --no-interaction Repositories/UserRepository

# Create form requests with validation
php artisan make:request --no-interaction CreateUserRequest
```

### Follow Existing Conventions
- **Check sibling files** for naming patterns, structure, and approaches
- **Use descriptive names**: `isRegisteredForDiscounts()` not `discount()`
- **Reuse existing components** before creating new ones
- **Follow casing from samples** given in the codebase

### Action Classes
For single-purpose operations:

```php
class ProcessPaymentAction
{
    public function execute(Order $order, array $paymentData): PaymentResult
    {
        // Single responsibility logic here
    }
}
```

## Performance Optimization

### Database Query Optimization
**Debug queries with tinker**: Use `tinker` tool to test query performance

**Always prevent N+1 queries:**
```php
// BAD - N+1 query problem
$users = User::all();
foreach ($users as $user) {
    echo $user->posts->count(); // N+1 queries
}

// GOOD - Eager loading
$users = User::withCount('posts')->get();
foreach ($users as $user) {
    echo $user->posts_count; // Single query
}
```

**Use chunking for large datasets:**
```php
User::chunk(200, function ($users) {
    foreach ($users as $user) {
        // Process each user
    }
});
```

**Test complex queries with tinker before implementation:**
```php
// Use tinker tool to test and optimize
User::with(['posts' => function($query) {
    $query->latest()->limit(5);
}])->find(1);

// Verify performance with database-query tool
```

**Optimize with indexes and raw queries when needed:**
```php
// Complex queries - use query builder
$results = DB::table('orders')
    ->select('user_id', DB::raw('SUM(total) as total_spent'))
    ->where('status', 'completed')
    ->groupBy('user_id')
    ->havingRaw('SUM(total) > ?', [1000])
    ->get();
```

### Caching Strategies

**Model caching:**
```php
public function getPopularPosts(): Collection
{
    return Cache::tags(['posts'])->remember('popular_posts', 3600, function () {
        return Post::withCount('likes')
            ->orderByDesc('likes_count')
            ->limit(10)
            ->get();
    });
}
```

**Cache invalidation patterns:**
```php
// In Observer or Event Listener
public function updated(Post $post): void
{
    Cache::tags(['posts'])->flush();
}
```

## Advanced Laravel Features

### Events & Listeners for Decoupling
```php
// Event
class OrderShipped
{
    public function __construct(public Order $order) {}
}

// Multiple listeners can handle the same event
class SendShipmentNotification implements ShouldQueue
{
    public function handle(OrderShipped $event): void
    {
        // Send notification logic
    }
}

class UpdateInventory implements ShouldQueue
{
    public function handle(OrderShipped $event): void
    {
        // Update inventory logic
    }
}
```

### Custom Eloquent Casts
```php
class User extends Model
{
    protected function casts(): array
    {
        return [
            'preferences' => UserPreferences::class,
            'metadata' => AsEncryptedArrayObject::class,
        ];
    }
}

class UserPreferences implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): array
    {
        return json_decode($value, true) ?? [];
    }

    public function set($model, string $key, $value, array $attributes): string
    {
        return json_encode($value);
    }
}
```

### Advanced Relationships
```php
class User extends Model
{
    // Polymorphic relationships
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    // Has many through
    public function postComments(): HasManyThrough
    {
        return $this->hasManyThrough(Comment::class, Post::class);
    }

    // Conditional relationships
    public function recentPosts(): HasMany
    {
        return $this->hasMany(Post::class)->where('created_at', '>=', now()->subMonth());
    }
}
```

## Security Best Practices

### Input Validation & Sanitization
```php
class CreatePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-_]+$/',
            'content' => 'required|string|max:10000',
            'tags' => 'array|max:10',
            'tags.*' => 'string|max:50|alpha_dash',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => strip_tags($this->title),
            'content' => clean($this->content), // Use HTMLPurifier
        ]);
    }
}
```

### Authorization with Policies
```php
class PostPolicy
{
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->hasRole('admin');
    }
}

// In controller
public function update(UpdatePostRequest $request, Post $post): JsonResponse
{
    $this->authorize('update', $post);
    // Update logic
}
```

### Rate Limiting
```php
// In bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->throttleApi('60,1'); // 60 requests per minute
    
    $middleware->group('api', [
        'throttle:api',
        'auth:sanctum'
    ]);
})

// Custom rate limiting
RateLimiter::for('heavy-operations', function (Request $request) {
    return $request->user()
        ? Limit::perMinute(10)->by($request->user()->id)
        : Limit::perMinute(2)->by($request->ip());
});
```

## Error Handling & Logging

### Custom Exceptions
```php
class PaymentFailedException extends Exception
{
    public function __construct(
        public readonly string $paymentId,
        string $message = 'Payment processing failed',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function report(): void
    {
        Log::error('Payment failed', [
            'payment_id' => $this->paymentId,
            'user_id' => auth()->id(),
        ]);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'error' => 'Payment processing failed',
            'payment_id' => $this->paymentId,
        ], 422);
    }
}
```

### Structured Logging
```php
Log::info('User action performed', [
    'user_id' => $user->id,
    'action' => 'profile_updated',
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'timestamp' => now()->toISOString(),
]);
```

## Testing Patterns

### Test Creation with Artisan
```bash
# Use list-artisan-commands to check options
php artisan make:test --no-interaction UserManagementTest --feature
php artisan make:test --no-interaction UserServiceTest --unit
```

### Feature Tests with Database
```php
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user_with_roles(): void
    {
        $admin = User::factory()->admin()->create();
        $roleIds = Role::factory()->count(2)->create()->pluck('id');

        $response = $this->actingAs($admin)
            ->postJson('/api/users', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'role_ids' => $roleIds->toArray(),
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'email',
                    'roles' => [
                        '*' => ['id', 'name']
                    ]
                ]
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        $this->assertEquals(2, User::find($response->json('data.id'))->roles->count());
    }
}
```

### Service Unit Tests
```php
class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_user_sends_welcome_email(): void
    {
        Notification::fake();
        
        $userData = ['name' => 'John', 'email' => 'john@test.com'];
        $userService = app(UserService::class);
        
        $user = $userService->createUser($userData);
        
        Notification::assertSentTo($user, WelcomeNotification::class);
    }
}
```

## Queue & Job Optimization

### Efficient Job Design
```php
class ProcessLargeDatasetJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 300;

    public function __construct(
        private int $batchId,
        private int $offset,
        private int $limit
    ) {}

    public function handle(): void
    {
        $records = DataModel::offset($this->offset)->limit($this->limit)->get();
        
        foreach ($records as $record) {
            $this->processRecord($record);
        }
    }

    public function uniqueId(): string
    {
        return "batch-{$this->batchId}-{$this->offset}";
    }
}
```

## Frontend Integration Issues

### Asset Compilation Problems
If frontend changes aren't reflecting:
```bash
# Ask user to run one of these:
npm run build
npm run dev  
composer run dev
```

### Browser Error Debugging
**Use `browser-logs` tool** to check for JavaScript errors before assuming backend issues:
- Check recent logs only (ignore old entries)
- Look for console errors, network failures, or exceptions
- Verify asset loading issues

## URL Generation
**Always use `get-absolute-url` tool** when sharing project URLs to ensure correct scheme, domain/IP, and port.

### Directory Structure for Features
```
app/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Services/
├── Repositories/
├── Actions/
├── Events/
├── Listeners/
├── Jobs/
├── Policies/
├── Observers/
└── Models/
```

### Interface Usage
Always program to interfaces for testability:

```php
// Bind in service provider
$this->app->bind(UserRepositoryInterface::class, UserRepository::class);
$this->app->bind(PaymentGatewayInterface::class, StripePaymentGateway::class);
```

## Anti-Patterns to Avoid

### DON'T: Fat Controllers
```php
// BAD
public function store(Request $request)
{
    // 50+ lines of business logic
}

// GOOD
public function store(CreateUserRequest $request, UserService $userService)
{
    $user = $userService->createUser($request->validated());
    return new UserResource($user);
}
```

### DON'T: Logic in Models
```php
// BAD - Business logic in model
class Order extends Model
{
    public function processPayment($paymentData)
    {
        // Payment processing logic - belongs in service
    }
}

// GOOD - Keep models focused on data
class Order extends Model
{
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
```

### DON'T: Direct env() Usage
```php
// BAD
$apiKey = env('STRIPE_API_KEY');

// GOOD - Use config
$apiKey = config('services.stripe.key');
```

## Performance Monitoring

### Query Monitoring in Development
```php
// In AppServiceProvider::boot()
if (app()->environment('local')) {
    DB::listen(function ($query) {
        if ($query->time > 100) { // Log slow queries
            Log::warning('Slow query detected', [
                'sql' => $query->sql,
                'time' => $query->time,
                'bindings' => $query->bindings,
            ]);
        }
    });
}
```

## Code Organization

### Directory Structure for Features
```
app/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Services/
├── Repositories/
├── Actions/
├── Events/
├── Listeners/
├── Jobs/
├── Policies/
├── Observers/
└── Models/
```

### Interface Usage
Always program to interfaces for testability:

```php
// Bind in service provider (bootstrap/providers.php)
$this->app->bind(UserRepositoryInterface::class, UserRepository::class);
$this->app->bind(PaymentGatewayInterface::class, StripePaymentGateway::class);
```

## Laravel 12 Specific Patterns

### Middleware Registration (bootstrap/app.php)
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(CustomMiddleware::class);
    $middleware->group('api', [
        'throttle:api',
        'auth:sanctum'
    ]);
})
```

### Console Command Auto-Registration
Commands in `app/Console/Commands/` automatically register - no manual registration needed.

### Model Casts Method (Laravel 12)
```php
protected function casts(): array
{
    return [
        'preferences' => 'array',
        'created_at' => 'datetime',
    ];
}
```

### Column Modification Migrations
**Important**: When modifying columns, include ALL previous attributes or they'll be dropped:
```php
$table->string('email', 255)->nullable(false)->unique()->change();
```

## Error Handling & Debugging

### Laravel Boost Debugging Workflow
1. **Check browser logs** with `browser-logs` tool
2. **Use tinker** for Eloquent model debugging
3. **Use database-query** for complex read queries
4. **Search docs** for Laravel-specific error solutions

### Custom Exception Handling (bootstrap/app.php)
```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (PaymentFailedException $e, Request $request) {
        return response()->json(['error' => $e->getMessage()], 422);
    });
})
```

## Deployment Considerations

### Code Quality Check
**Always run before deployment:**
```bash
vendor/bin/pint --dirty  # Fix formatting issues
```

### Optimize for Production
```php
// Config caching - run these in deployment
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## Laravel Boost Workflow Priority

1. **`search-docs`** first - Get version-specific documentation
2. **`list-artisan-commands`** - Check available commands and options
3. **Use `tinker`** - Debug and test Eloquent queries/relationships
4. **`browser-logs`** - Check frontend issues before backend debugging
5. **`get-absolute-url`** - Generate correct project URLs
6. **`vendor/bin/pint --dirty`** - Format code before finalizing

## Final Guidelines

1. **Documentation first** - Always search Laravel docs before implementing
2. **Use Boost tools** - Leverage MCP server capabilities for debugging
3. **Follow Laravel 12 structure** - No middleware files, streamlined approach
4. **Artisan everything** - Use make commands with `--no-interaction`
5. **Test with tinker** - Validate queries and relationships
6. **Check browser logs** - Frontend issues often masquerade as backend problems
7. **Format with Pint** - Maintain consistent code style
8. **Respect conventions** - Check sibling files for existing patterns

Remember: Laravel Boost MCP server is your debugging and development partner - use its tools to build better Laravel applications faster.
