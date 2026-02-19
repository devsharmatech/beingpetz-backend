<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::all();
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'providers_count' => 'nullable|integer|min:0',
            'location' => 'nullable|string|max:255',
            'icon' => 'nullable|mimes:svg,png,jpg,jpeg|max:2048', 
        ]);

        $serviceData = $request->except('icon');

        if ($request->hasFile('icon')) {
            $icon = $request->file('icon');
            $iconName = time() . '_' . uniqid() . '.' . $icon->getClientOriginalExtension();
            $icon->move(public_path('uploads/services/icons'), $iconName);
            $serviceData['icon'] = 'uploads/services/icons/' . $iconName;
            $serviceData['status'] = 'active';
        }

        Service::create($serviceData);

        return redirect()->route('admin.services.index')->with('success', 'Service created successfully.');
    }

    public function show(string $id)
    {
        $service = Service::findOrFail($id);
        return view('admin.services.show', compact('service'));
    }

    public function edit(string $id)
    {
        $service = Service::findOrFail($id);
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, string $id)
    {
        $service = Service::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'providers_count' => 'nullable|integer|min:0',
            'location' => 'nullable|string|max:255',
            'icon' => 'nullable|mimes:svg,png,jpg,jpeg|max:2048', 
        ]);

        $serviceData = $request->except('icon');

        if ($request->hasFile('icon')) {
            // Delete old icon if exists
            if ($service->icon && file_exists(public_path($service->icon))) {
                unlink(public_path($service->icon));
            }

            // Save new icon
            $icon = $request->file('icon');
            $iconName = time() . '_' . uniqid() . '.' . $icon->getClientOriginalExtension();
            $icon->move(public_path('uploads/services/icons'), $iconName);
            $serviceData['icon'] = 'uploads/services/icons/' . $iconName;
            
        }

        $service->update($serviceData);

        return redirect()->route('admin.services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(string $id)
    {
        $service = Service::findOrFail($id);
        
        // Delete icon if exists
        if ($service->icon && file_exists(public_path($service->icon))) {
            unlink(public_path($service->icon));
        }

        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'Service deleted successfully.');
    }

    // Helper method to create directory
    private function ensureUploadDirectoryExists()
    {
        $uploadPath = public_path('uploads/services/icons');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
    }


    public function uploadProviders(Request $request)
    {
        // Validate the request
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'providers_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        try {
            $service = Service::findOrFail($request->service_id);
            
            // Process CSV file
            $file = $request->file('providers_file');
            $csvData = array_map('str_getcsv', file($file));
            
            // Remove header row
            $headers = array_shift($csvData);
            $importedCount = 0;
            
            foreach ($csvData as $row) {
                if (count($row) >= 4 && !empty(trim($row[0]))) {
                    // Create provider - adjust based on your Provider model structure
                    \App\Models\Provider::create([
                        'service_id' => $service->id,
                        'name' => trim($row[0]),
                        'email' => trim($row[1]),
                        'phone' => trim($row[2]),
                        'address' => trim($row[3]),
                        'is_active' => true
                    ]);
                    $importedCount++;
                }
            }
            
            // Update providers count in service table
            $service->update([
                'providers_count' => $service->providers()->count()
            ]);
            
            return redirect()->route('admin.services.index')
                ->with('success', "Successfully imported {$importedCount} providers for {$service->name}!");
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error importing providers: ' . $e->getMessage());
        }
    }

    // ServiceController mein yeh method add karo
    public function downloadTemplate()
    {
        $filename = "service-providers-template.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, ['name', 'email', 'phone', 'address']);
            
            // Add sample data
            $sampleData = [
                ['John Doe', 'john@example.com', '+1234567890', '123 Main Street, New York'],
                ['Jane Smith', 'jane@example.com', '+0987654321', '456 Park Avenue, London'],
                ['Mike Johnson', 'mike@example.com', '+1122334455', '789 Oak Road, Chicago'],
                ['Sarah Wilson', 'sarah@example.com', '+5566778899', '321 Pine Lane, Miami']
            ];
            
            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ServiceController mein yeh method add karo
    public function getProviders(Service $service)
    {
        $providers = $service->providers()->latest()->get();
        
        return response()->json([
            'success' => true,
            'providers' => $providers
        ]);
    }
}