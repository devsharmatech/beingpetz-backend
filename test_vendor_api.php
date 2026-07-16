<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\User;
use App\Models\Provider;
use App\Models\Service;
use App\Models\ServiceBooking;
use App\Models\ProviderEarning;
use Illuminate\Http\Request;
use Carbon\Carbon;

// Setup test data
User::where('email', 'testvendor@example.com')->delete();

$user = User::create([
    'first_name' => 'Test',
    'last_name' => 'Vendor',
    'email' => 'testvendor@example.com',
    'phone' => '1234567890',
    'password' => bcrypt('password123'),
    'role' => 'vendor',
]);

$service = Service::create([
    'name' => 'Test Service',
    'description' => 'A service for testing',
    'is_active' => true,
]);

$provider = Provider::create([
    'user_id' => $user->id,
    'service_id' => $service->id,
    'name' => 'Test Provider Profile',
    'email' => 'provider@example.com',
    'phone' => '0987654321',
    'address' => '123 Test St',
]);

$booking = ServiceBooking::create([
    'user_id' => $user->id,
    'provider_id' => $provider->id,
    'service_id' => $service->id,
    'scheduled_at' => Carbon::now()->addDays(2),
    'status' => 'accepted',
    'total_amount' => 150.00
]);

ProviderEarning::create([
    'provider_id' => $provider->id,
    'service_booking_id' => $booking->id,
    'amount' => 150.00,
    'status' => 'cleared'
]);

function makeRequest($kernel, $method, $uri, $content = [], $token = null) {
    $server = ['REQUEST_URI' => $uri, 'REQUEST_METHOD' => $method];
    if ($token) {
        $server['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }
    $request = Request::create($uri, $method, $content, [], [], $server);
    $request->headers->set('Accept', 'application/json');
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    return json_decode($response->getContent(), true);
}

// 1. Test Login
$loginRes = makeRequest($kernel, 'POST', '/api/v1/vendor/login', [
    'email' => 'testvendor@example.com',
    'password' => 'password123'
]);

if ($loginRes['status'] !== true) {
    die("Login failed: " . json_encode($loginRes));
}
$token = $loginRes['token'];
echo "Login: OK\n";

// 2. Test Dashboard
$dashRes = makeRequest($kernel, 'GET', '/api/v1/vendor/dashboard', [], $token);
if ($dashRes['status'] !== true) die("Dashboard failed: " . json_encode($dashRes));
echo "Dashboard: OK\n";

// 3. Test Earnings
$earnRes = makeRequest($kernel, 'GET', '/api/v1/vendor/earnings', [], $token);
if ($earnRes['status'] !== true) die("Earnings failed: " . json_encode($earnRes));
echo "Earnings: OK\n";

// 4. Test Chart
$chartRes = makeRequest($kernel, 'GET', '/api/v1/vendor/chart', [], $token);
if ($chartRes['status'] !== true) die("Chart failed: " . json_encode($chartRes));
echo "Chart: OK\n";

// 5. Test Upcoming Bookings
$upRes = makeRequest($kernel, 'GET', '/api/v1/vendor/bookings/upcoming', [], $token);
if ($upRes['status'] !== true) die("Upcoming Bookings failed: " . json_encode($upRes));
echo "Upcoming Bookings: OK\n";

// 6. Test All Bookings
$allRes = makeRequest($kernel, 'GET', '/api/v1/vendor/bookings', [], $token);
if ($allRes['status'] !== true) die("All Bookings failed: " . json_encode($allRes));
echo "All Bookings: OK\n";

// 7. Test Services
$servRes = makeRequest($kernel, 'GET', '/api/v1/vendor/services', [], $token);
if ($servRes['status'] !== true) die("Services failed: " . json_encode($servRes));
echo "Services: OK\n";

// 8. Test Messages
$msgRes = makeRequest($kernel, 'GET', '/api/v1/vendor/messages', [], $token);
if ($msgRes['status'] !== true) die("Messages failed: " . json_encode($msgRes));
echo "Messages: OK\n";

// 9. Test Profile Update
$profRes = makeRequest($kernel, 'POST', '/api/v1/vendor/profile', [
    'name' => 'Updated Profile',
    'phone' => '9999999999'
], $token);
if ($profRes['status'] !== true) die("Profile failed: " . json_encode($profRes));
echo "Profile: OK\n";

echo "All tests passed successfully!\n";
