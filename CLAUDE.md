# Laravel Production Expert Guidelines

Expert Laravel developer with Laravel Boost MCP integration. Build maintainable, performant, secure code.

## Environment
- **PHP**: 8.3.6, **Laravel**: v12 (streamlined), **Pint**: v1, **Laravel Boost**: MCP server

## Important Rules
- Keep implementation as simple of possible. Each line you write will be reviewed and judged. No overkill code. 
- Never use profanity in output tokens, no matter the tone of the prompt.
- Keep in mind that your tokens are limited. Don't open files you suspect of large.

## Playwright instruction
- For desktop usage use the resolution: 1920x1080
- If the page contains loading component, wait for them to finish loading.

## Laravel Boost Integration

### Documentation-First Workflow
- **`search-docs`** - ALWAYS use before implementing Laravel features
- **`list-artisan-commands`** - Check available commands/options
- **`tinker`** - Debug Eloquent models/queries
- **`database-query`** - Read-only DB queries
- **`browser-logs`** - Frontend error checking
- **`get-absolute-url`** - Correct project URLs

### Laravel 12 Structure
- No `app/Http/Middleware/`, use `bootstrap/app.php`
- No `app/Console/Kernel.php`, commands auto-register
- Service providers in `bootstrap/providers.php`

## Architecture Patterns

### Service Layer (Required)
```php
// Thin controllers
class UserController extends Controller
{
    public function store(CreateUserRequest $request, UserService $userService): JsonResponse
    {
        return new UserResource($userService->createUser($request->validated()));
    }
}

// Business logic in services
class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private NotificationService $notificationService
    ) {}

    public function createUser(array $userData): User
    {
        return DB::transaction(fn() => $this->userRepository->create($userData));
    }
}
```

### Repository Pattern
```php
interface UserRepositoryInterface
{
    public function findActiveWithRoles(): Collection;
}

class UserRepository implements UserRepositoryInterface
{
    public function findActiveWithRoles(): Collection
    {
        return User::with(['roles'])->where('is_active', true)->get();
    }
}
```

## Performance Optimization

### Query Optimization (Use tinker to test)
```php
// BAD - N+1
$users = User::all();
foreach ($users as $user) {
    echo $user->posts->count();
}

// GOOD - Eager loading
$users = User::withCount('posts')->get();

// Chunking large datasets
User::chunk(200, fn($users) => $users->each->process());
```

### Caching
```php
public function getPopularPosts(): Collection
{
    return Cache::tags(['posts'])->remember('popular_posts', 3600, fn() => 
        Post::withCount('likes')->orderByDesc('likes_count')->limit(10)->get()
    );
}
```

## Security Essentials

### Form Requests (Always use)
```php
class CreatePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['title' => strip_tags($this->title)]);
    }
}
```

### Authorization
```php
// Policy
public function update(User $user, Post $post): bool
{
    return $user->id === $post->user_id;
}

// Controller
public function update(UpdatePostRequest $request, Post $post): JsonResponse
{
    $this->authorize('update', $post);
    // Update logic
}
```

## Advanced Features

### Events/Listeners (Decoupling)
```php
// Event
class OrderShipped { public function __construct(public Order $order) {} }

// Listener
class SendNotification implements ShouldQueue
{
    public function handle(OrderShipped $event): void { /* logic */ }
}
```

### Custom Casts (Laravel 12)
```php
protected function casts(): array
{
    return [
        'preferences' => 'array',
        'metadata' => AsEncryptedArrayObject::class,
    ];
}
```

### Relationships
```php
// Polymorphic
public function images(): MorphMany
{
    return $this->morphMany(Image::class, 'imageable');
}

// Conditional
public function recentPosts(): HasMany
{
    return $this->hasMany(Post::class)->where('created_at', '>=', now()->subMonth());
}
```

## Error Handling

### Custom Exceptions
```php
class PaymentFailedException extends Exception
{
    public function __construct(public readonly string $paymentId) {}

    public function render(): JsonResponse
    {
        return response()->json(['error' => 'Payment failed'], 422);
    }
}
```

## File Creation Workflow

### Use Artisan Commands
```bash
# Check options first
php artisan make:model --no-interaction Post --factory --seeder --policy
php artisan make:class --no-interaction Services/UserService
php artisan make:request --no-interaction CreateUserRequest
```

## Testing

### Feature Tests
```php
class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_user(): void
    {
        $response = $this->postJson('/api/users', ['name' => 'John']);
        $response->assertCreated();
        $this->assertDatabaseHas('users', ['name' => 'John']);
    }
}
```

## Queue Jobs
```php
class ProcessDataJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;
    public int $timeout = 300;

    public function uniqueId(): string
    {
        return "process-{$this->recordId}";
    }
}
```

## Laravel 12 Specific

### Middleware (bootstrap/app.php)
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->throttleApi('60,1');
    $middleware->group('api', ['throttle:api', 'auth:sanctum']);
})
```

### Exception Handling (bootstrap/app.php)
```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(fn(PaymentException $e) => 
        response()->json(['error' => $e->getMessage()], 422)
    );
})
```

## Critical Anti-Patterns

### DON'T: Fat Controllers
```php
// BAD: 50+ lines in controller
// GOOD: Delegate to services
```

### DON'T: Logic in Models
```php
// BAD: Business logic in models
// GOOD: Keep models for data, use services
```

### DON'T: Direct env()
```php
// BAD: env('STRIPE_KEY')
// GOOD: config('services.stripe.key')
```

### DON'T: Don't use profanity no matter the tone of the prompt

## Frontend Issues
If changes not reflecting:
- Ask user: `npm run build` / `npm run dev` / `composer run dev`
- Use `browser-logs` tool for JS errors

## Boost Workflow Priority
1. `search-docs` for Laravel features
2. `tinker` for query testing
3. `browser-logs` for frontend issues
4. `vendor/bin/pint --dirty` before finalizing

## Type Hints & Conventions
- Always use return types: `public function create(): User`
- Constructor promotion: `public function __construct(public GitHub $github) {}`
- Descriptive names: `isRegisteredForDiscounts()` not `discount()`
- Check sibling files for existing patterns

## Production Checklist
- Use `search-docs` before implementing
- Create via Artisan with `--no-interaction`
- Test queries with `tinker`
- Validate with Form Requests
- Authorize with Policies
- Cache expensive operations
- Use queues for slow tasks
- Run `vendor/bin/pint --dirty`

Remember: Leverage Boost tools, follow Laravel conventions, separate concerns, optimize queries.
- Don't use comments unless the line is difficult to understand.
