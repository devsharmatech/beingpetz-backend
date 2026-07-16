
use App\Models\User;
use App\Models\Provider;
use App\Models\Service;
use App\Models\ServiceBooking;
use App\Models\ProviderEarning;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

// Setup test data
User::where('email', 'testvendor@example.com')->delete();

$user = User::create([
    'first_name' => 'Test',
    'last_name' => 'Vendor',
    'email' => 'testvendor@example.com',
    'phone' => '12345678911',
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

// Dispatch to API
function makeRequest($method, $uri, $content = [], $token = null) {
    $server = ['REQUEST_URI' => $uri, 'REQUEST_METHOD' => $method, 'REMOTE_ADDR' => '127.0.0.1'];
    if ($token) {
        $server['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }
    $request = Request::create($uri, $method, $content, [], [], $server);
    $request->headers->set('Accept', 'application/json');
    $response = app()->handle($request);
    return json_decode($response->getContent(), true);
}

// 1. Test Login
$loginRes = makeRequest('POST', '/api/v1/vendor/login', [
    'email' => 'testvendor@example.com',
    'password' => 'password123'
]);

if ($loginRes['status'] !== true) {
    echo("Login failed: " . json_encode($loginRes) . "\n");
} else {
    $token = $loginRes['token'];
    echo "Login: OK\n";

    // 2. Test Dashboard
    $dashRes = makeRequest('GET', '/api/v1/vendor/dashboard', [], $token);
    if ($dashRes['status'] !== true) echo("Dashboard failed: " . json_encode($dashRes) . "\n"); else echo "Dashboard: OK\n";

    // 3. Test Earnings
    $earnRes = makeRequest('GET', '/api/v1/vendor/earnings', [], $token);
    if ($earnRes['status'] !== true) echo("Earnings failed: " . json_encode($earnRes) . "\n"); else echo "Earnings: OK\n";

    // 4. Test Chart
    $chartRes = makeRequest('GET', '/api/v1/vendor/chart', [], $token);
    if ($chartRes['status'] !== true) echo("Chart failed: " . json_encode($chartRes) . "\n"); else echo "Chart: OK\n";

    // 5. Test Upcoming Bookings
    $upRes = makeRequest('GET', '/api/v1/vendor/bookings/upcoming', [], $token);
    if ($upRes['status'] !== true) echo("Upcoming Bookings failed: " . json_encode($upRes) . "\n"); else echo "Upcoming Bookings: OK\n";

    // 6. Test All Bookings
    $allRes = makeRequest('GET', '/api/v1/vendor/bookings', [], $token);
    if ($allRes['status'] !== true) echo("All Bookings failed: " . json_encode($allRes) . "\n"); else echo "All Bookings: OK\n";

    // 7. Test Services
    $servRes = makeRequest('GET', '/api/v1/vendor/services', [], $token);
    if ($servRes['status'] !== true) echo("Services failed: " . json_encode($servRes) . "\n"); else echo "Services: OK\n";

    // 8. Test Messages
    $msgRes = makeRequest('GET', '/api/v1/vendor/messages', [], $token);
    if ($msgRes['status'] !== true) echo("Messages failed: " . json_encode($msgRes) . "\n"); else echo "Messages: OK\n";

    // 9. Test Profile Update
    $profRes = makeRequest('POST', '/api/v1/vendor/profile', [
        'name' => 'Updated Profile',
        'phone' => '9999999999'
    ], $token);
    if ($profRes['status'] !== true) echo("Profile failed: " . json_encode($profRes) . "\n"); else echo "Profile: OK\n";
    
    echo "\nAll tests completed!\n";
}
