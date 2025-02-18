**Laravel 11**:

---

## **1. Install Laravel Passport**
Ensure you have Laravel installed:

```bash
composer require laravel/passport
```

Then, install and configure Passport:

```bash
php artisan migrate
php artisan passport:install
```

Passport will generate encryption keys needed for API authentication and add database tables.

---

## **2. Update `AuthServiceProvider`**
Modify the `AuthServiceProvider` class to load Passport routes:

**File:** `app/Providers/AuthServiceProvider.php`

```php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // Register any model-policy mappings here
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies(); // Correct location

        Passport::routes();
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
    }
}
```

---

## **3. Create Migration for `role` column**
Add a `role` column to the `users` table to differentiate user types.

```bash
php artisan make:migration add_role_to_users_table
```

Modify the migration file:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('role')->default('employee'); // Default role
});
```

Run the migration:

```bash
php artisan migrate
```

---

## **4. Register Roles during User Creation**
When registering users, set the `role` to `admin`, `manager`, or `employee`.

For example, update the registration and login controller to accept a role:

```php
public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string',
        'email' => 'required|string|email|unique:users',
        'password' => 'required|string|min:8',
        'role' => 'required|in:admin,manager,employee',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'role' => $request->role,
    ]);

    return response()->json(['message' => 'User created successfully']);
}
public function login(Request $request)
{
    // Validate request
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    // Check if user exists
    $user = User::where('email', $request->email)->first();

    // Verify password
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Invalid email or password'
        ], 401);
    }

    // Revoke previous tokens (optional)
    $user->tokens()->delete();

    // Generate access token
    $token = $user->createToken('auth_token')->accessToken;

    // Determine the message based on the user's role
    $roleMessage = '';
    switch ($user->role) {
        case 'admin':
            $roleMessage = 'Admin login successful';
            break;
        case 'manager':
            $roleMessage = 'Manager login successful';
            break;
        case 'user':
            $roleMessage = 'User login successful';
            break;
        default:
            $roleMessage = 'Login successful';
    }

    // Return success response with role message
    return response()->json([
        'message' => $roleMessage,
        'token' => $token,
        'user' => $user
    ]);
}

```

---

## **5. Add Middleware for Role Authentication**
Create middleware for each role:

```bash
php artisan make:middleware AdminMiddleware
php artisan make:middleware ManagerMiddleware
php artisan make:middleware EmployeeMiddleware
```

Update each middleware to check the user’s role:

**Admin Middleware:**

```php
public function handle($request, Closure $next)
{
    if (auth()->user() && auth()->user()->role === 'admin') {
        return $next($request);
    }

    return response()->json(['error' => 'Unauthorized'], 403);
}
```

**Manager Middleware:**

```php
public function handle($request, Closure $next)
{
    if (auth()->user() && auth()->user()->role === 'manager') {
        return $next($request);
    }

    return response()->json(['error' => 'Unauthorized'], 403);
}
```

**Employee Middleware:**

```php
public function handle($request, Closure $next)
{
    if (auth()->user() && auth()->user()->role === 'employee') {
        return $next($request);
    }

    return response()->json(['error' => 'Unauthorized'], 403);
}
```

---

## **6. Register Middleware in `Kernel.php`**
Open `app/Http/Kernel.php` and add the middleware:

```php
protected $routeMiddleware = [
    'admin' => \App\Http\Middleware\AdminMiddleware::class,
    'manager' => \App\Http\Middleware\ManagerMiddleware::class,
    'employee' => \App\Http\Middleware\EmployeeMiddleware::class,
];
```

---

## **7. Create API Routes**
Define API routes in `routes/api.php`:

```php
use App\Http\Controllers\API\UserController;

Route::middleware('auth:api')->group(function () {
    Route::get('/admin/dashboard', [UserController::class, 'adminDashboard'])->middleware('admin');
    Route::get('/manager/dashboard', [UserController::class, 'managerDashboard'])->middleware('manager');
    Route::get('/employee/dashboard', [UserController::class, 'employeeDashboard'])->middleware('employee');
});
```

---

## **8. Create Methods in `UserController`**
Create a controller to handle requests for each role:

```bash
php artisan make:controller API/UserController
```

Add the following methods:

```php
public function adminDashboard()
{
    return response()->json(['message' => 'Welcome to the Admin Dashboard']);
}

public function managerDashboard()
{
    return response()->json(['message' => 'Welcome to the Manager Dashboard']);
}

public function employeeDashboard()
{
    return response()->json(['message' => 'Welcome to the Employee Dashboard']);
}
```

---

## **9. Create Policies for Authorization**
Create a policy for additional authorization logic:

```bash
php artisan make:policy RolePolicy
```

In `RolePolicy`, add methods like:

```php
public function manageAdmin(User $user)
{
    return $user->role === 'admin';
}

public function manageManager(User $user)
{
    return $user->role === 'manager';
}

public function manageEmployee(User $user)
{
    return $user->role === 'employee';
}
```

---

## **10. Use Gates for Fine-Grained Authorization**
Define gates in `AuthServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

public function boot()
{
    $this->registerPolicies();

    Gate::define('view-admin-dashboard', function (User $user) {
        return $user->role === 'admin';
    });

    Gate::define('view-manager-dashboard', function (User $user) {
        return $user->role === 'manager';
    });

    Gate::define('view-employee-dashboard', function (User $user) {
        return $user->role === 'employee';
    });
}
```

Use the gates in controllers:

```php
if (Gate::allows('view-admin-dashboard')) {
    // Authorized
} else {
    return response()->json(['error' => 'Unauthorized'], 403);
}
```

---

## **11. Test the API Endpoints**
1. Use a tool like **Postman** to test the login and register endpoints.
2. Authenticate users and test role-based access to each endpoint (`/admin/dashboard`, `/manager/dashboard`, `/employee/dashboard`).

