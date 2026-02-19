<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pet;
use App\Models\User;
use App\Models\Post;
use App\Models\Blog;
use Carbon\Carbon;

class AdminController extends Controller
{
   public function dashboard()
    {
        $totalPets = Pet::count();
        $totalUsers = User::where('role', 'parent')->count();
        $totalPosts = Post::count();
        $totalBlogs = Blog::count();
        $deletedUsers = User::where('role', 'parent')->where('deleted_at' ,0)->count();
        
        // Active Users Count
        $dau = User::where('role', 'parent')
                    ->where('last_login', '>=', now()->subDay())
                    ->count();
                    
        $wau = User::where('role', 'parent')
                    ->where('last_login', '>=', now()->subWeek())
                    ->count();
                    
        $mau = User::where('role', 'parent')
                    ->where('last_login', '>=', now()->subMonth())
                    ->count();
        
        // Chart data
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $petData = [65, 59, 80, 81, 56, 55, 40, 45, 60, 70, 65, 75];
        $petParentData = [28, 48, 40, 19, 86, 27, 90, 35, 50, 60, 45, 55];
        
        $activeUsers = User::where('role', 'parent')->where('isComplete', 1)->count();
        $inactiveUsers = User::where('role', 'parent')->where('isComplete', 0)->count();

        return view('admin.index', compact(
            'totalPets', 'totalUsers', 'totalPosts', 'totalBlogs', 
            'deletedUsers', 'dau', 'wau', 'mau', 'months', 
            'petData', 'petParentData', 'activeUsers', 'inactiveUsers'
        ));
    }

    public function deletedUsers()
    {
        $deletedUsers = User::where('deleted_at', 0)->get();
        
        return view('admin.users.deleted', compact('deletedUsers'));
    }

    // Method to get user details
    public function userDetails($id)
    {
        $user = User::withTrashed()->find($id);
        
        if (!$user) {
            // Demo user data
            $user = (object)[
                'id' => $id,
                'name' => 'Demo User ' . $id,
                'email' => 'demo_user' . $id . '@example.com',
                'role' => 'parent',
                'isComplete' => 1,
                'last_login' => now()->subDays(rand(1, 30)),
                'created_at' => now()->subMonths(rand(1, 6)),
                'deleted_at' => null
            ];
        }
        
        return view('admin.users.details', compact('user'));
    }


// Method to export chart data as CSV
public function exportChartData(Request $request)
{
    $type = $request->get('type', 'monthly');
    
    // Get actual data based on type
    if ($type === 'monthly') {
        $data = $this->getMonthlyChartData();
        $filename = "monthly_chart_data_" . date('Y-m-d') . ".csv";
    } elseif ($type === 'weekly') {
        $data = $this->getWeeklyChartData();
        $filename = "weekly_chart_data_" . date('Y-m-d') . ".csv";
    } else {
        $data = $this->getDailyChartData();
        $filename = "daily_chart_data_" . date('Y-m-d') . ".csv";
    }

    $headers = [
        'Content-Type' => 'text/csv; charset=utf-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ];

    $callback = function() use ($data) {
        $file = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 to handle special characters
        fwrite($file, "\xEF\xBB\xBF");
        
        // Header row
        fputcsv($file, ['Period', 'New Pets', 'New Pet Parents']);
        
        // Data rows
        foreach ($data as $row) {
            fputcsv($file, $row);
        }
        
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function exportChartImage(Request $request)
{
    $type = $request->get('type', 'monthly');
    
    // Get chart data
    $chartData = $this->getChartDataForImage($type);
    
    // Create image with better dimensions
    $width = 1000;
    $height = 700;
    $image = imagecreate($width, $height);
    
    // Colors
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $purple = imagecolorallocate($image, 131, 55, 178);
    $blue = imagecolorallocate($image, 54, 162, 235);
    $green = imagecolorallocate($image, 75, 192, 192);
    $gray = imagecolorallocate($image, 200, 200, 200);
    $lightGray = imagecolorallocate($image, 240, 240, 240);
    
    // Fill background
    imagefill($image, 0, 0, $white);
    
    // Draw border
    imagerectangle($image, 0, 0, $width-1, $height-1, $black);
    
    // Draw title
    $title = "Being Petz - Registrations Overview (" . ucfirst($type) . " View)";
    imagestring($image, 5, 50, 30, $title, $purple);
    
    // Draw subtitle with date
    $subtitle = "Generated on: " . date('F j, Y g:i A');
    imagestring($image, 3, 50, 60, $subtitle, $gray);
    
    // Draw table header with background
    $headerY = 120;
    imagefilledrectangle($image, 50, $headerY, $width-50, $headerY+30, $purple);
    imagestring($image, 4, 60, $headerY+8, "Period", $white);
    imagestring($image, 4, 400, $headerY+8, "New Pets", $white);
    imagestring($image, 4, 600, $headerY+8, "New Pet Parents", $white);
    
    // Draw table data
    $yPosition = $headerY + 40;
    $rowCount = 0;
    
    foreach ($chartData as $row) {
        // Alternate row background for better readability
        if ($rowCount % 2 == 0) {
            imagefilledrectangle($image, 50, $yPosition-5, $width-50, $yPosition+25, $lightGray);
        }
        
        imagestring($image, 3, 60, $yPosition, $row['period'], $black);
        imagestring($image, 3, 400, $yPosition, $row['pets'], $blue);
        imagestring($image, 3, 600, $yPosition, $row['parents'], $green);
        
        $yPosition += 30;
        $rowCount++;
        
        // Prevent going off the page
        if ($yPosition > $height - 50) {
            break;
        }
    }
    
    // Draw footer
    $footer = "Data Source: Being Petz Admin Dashboard";
    imagestring($image, 2, 50, $height-30, $footer, $gray);
    
    // Save image to temporary file
    $tempDir = sys_get_temp_dir();
    $filename = $tempDir . '/chart_data_' . $type . '_' . date('Y-m-d_His') . '.png';
    
    imagepng($image, $filename);
    imagedestroy($image);
    
    // Download and delete temporary file
    return response()->download($filename, 'chart_data_' . $type . '_' . date('Y-m-d') . '.png')
                    ->deleteFileAfterSend(true);
}

// Fixed Data Retrieval Methods
private function getMonthlyChartData()
{
    $currentYear = date('Y');
    $data = [];
    
    // Get pets data
    $petsData = Pet::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
        ->whereYear('created_at', $currentYear)
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('count', 'month')
        ->toArray();

    // Get pet parents data
    $parentsData = User::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
        ->where('role', 'parent')
        ->whereYear('created_at', $currentYear)
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('count', 'month')
        ->toArray();

    $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    for ($month = 1; $month <= 12; $month++) {
        $data[] = [
            $monthNames[$month - 1] . ' ' . $currentYear,
            $petsData[$month] ?? 0,
            $parentsData[$month] ?? 0
        ];
    }
    
    return $data;
}

private function getWeeklyChartData()
{
    $data = [];
    
    for ($i = 11; $i >= 0; $i--) {
        $startDate = now()->subWeeks($i)->startOfWeek();
        $endDate = now()->subWeeks($i)->endOfWeek();
        
        $weekLabel = 'W' . $startDate->format('W') . ' (' . $startDate->format('M d') . ' - ' . $endDate->format('M d') . ')';
        
        // Get pets count for the week
        $petsCount = Pet::whereBetween('created_at', [$startDate, $endDate])->count();
        
        // Get pet parents count for the week
        $parentsCount = User::where('role', 'parent')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        
        $data[] = [$weekLabel, $petsCount, $parentsCount];
    }
    
    return $data;
}

private function getDailyChartData()
{
    $data = [];
    
    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i);
        $dateLabel = $date->format('M d, Y');
        
        // Get pets count for the day
        $petsCount = Pet::whereDate('created_at', $date->format('Y-m-d'))->count();
        
        // Get pet parents count for the day
        $parentsCount = User::where('role', 'parent')
            ->whereDate('created_at', $date->format('Y-m-d'))
            ->count();
        
        $data[] = [$dateLabel, $petsCount, $parentsCount];
    }
    
    return $data;
}

private function getChartDataForImage($type)
{
    if ($type === 'monthly') {
        $chartData = $this->getMonthlyChartData();
    } elseif ($type === 'weekly') {
        $chartData = $this->getWeeklyChartData();
    } else {
        $chartData = $this->getDailyChartData();
    }
    
    $formattedData = [];
    foreach ($chartData as $row) {
        $formattedData[] = [
            'period' => $row[0],
            'pets' => (string)$row[1],
            'parents' => (string)$row[2]
        ];
    }
    
    return $formattedData;
}


// Method for AJAX chart data
public function getChartData(Request $request)
{
    $type = $request->get('type', 'monthly');
    
    if ($type === 'weekly') {
        $labels = [];
        $petData = [];
        $parentData = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $startDate = now()->subWeeks($i)->startOfWeek();
            $endDate = now()->subWeeks($i)->endOfWeek();
            
            $labels[] = 'W' . $startDate->format('W') . ' (' . $startDate->format('M d') . ')';
            $petData[] = Pet::whereBetween('created_at', [$startDate, $endDate])->count();
            $parentData[] = User::where('role', 'parent')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
        }
    } else if ($type === 'daily') {
        $labels = [];
        $petData = [];
        $parentData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('M d');
            $petData[] = Pet::whereDate('created_at', $date)->count();
            $parentData[] = User::where('role', 'parent')
                ->whereDate('created_at', $date)
                ->count();
        }
    } else {
        // Monthly
        $currentYear = date('Y');
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $petData = [];
        $parentData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $petData[] = Pet::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $month)
                ->count();
            $parentData[] = User::where('role', 'parent')
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $month)
                ->count();
        }
    }
    
    return response()->json([
        'labels' => $labels,
        'petData' => $petData,
        'parentData' => $parentData
    ]);
}
}