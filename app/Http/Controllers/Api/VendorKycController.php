<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Provider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class VendorKycController extends Controller
{
    public function status(Request $request)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        // Map internal kyc_status to display statuses
        $statusMap = [
            'pending' => 'Pending Verification',
            'partially_verified' => 'Partially Verified',
            'verified' => 'Verified',
            'rejected' => 'Rejected'
        ];
        
        $displayStatus = $statusMap[$provider->kyc_status ?? 'pending'] ?? 'Pending Verification';

        // Build list of uploaded documents
        $uploadedDocuments = [];
        
        if ($provider->primary_gov_doc) {
            $uploadedDocuments[] = [
                'name' => 'Government ID (Aadhaar/PAN)',
                'status' => in_array($provider->kyc_status, ['verified', 'partially_verified']) ? 'Verified' : 'Under Review',
                'url' => asset('storage/' . $provider->primary_gov_doc)
            ];
        }
        
        if ($provider->alternate_id_doc) {
             $uploadedDocuments[] = [
                'name' => 'Alternate ID Document',
                'status' => $provider->kyc_status === 'verified' ? 'Verified' : 'Under Review',
                'url' => asset('storage/' . $provider->alternate_id_doc)
            ];
        }

        $kycDocs = json_decode($provider->kyc_documents, true) ?? [];
        foreach ($kycDocs as $doc) {
             $uploadedDocuments[] = [
                'name' => $doc['name'] ?? 'Additional Certification',
                'status' => $doc['status'] ?? 'Under Review',
                'url' => asset('storage/' . $doc['path'])
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'KYC status fetched successfully.',
            'data' => [
                'status' => $provider->kyc_status ?? 'pending',
                'display_status' => $displayStatus,
                'message' => $provider->kyc_message ?? 'Please upload your certification documents to obtain the verification badge.',
                'uploaded_documents' => $uploadedDocuments
            ]
        ], 200);
    }

    public function upload(Request $request)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $validator = Validator::make($request->all(), [
            'document_name' => 'required|string',
            'document_file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }

        if ($request->hasFile('document_file')) {
            $path = $request->file('document_file')->store('providers/kyc', 'public');
            
            $kycDocs = json_decode($provider->kyc_documents, true) ?? [];
            $kycDocs[] = [
                'name' => $request->document_name,
                'path' => $path,
                'status' => 'Under Review'
            ];
            
            $provider->kyc_documents = json_encode($kycDocs);
            
            // If they were rejected or pending, moving to partially verified upon upload is a common flow
            if (in_array($provider->kyc_status, ['rejected', 'pending'])) {
                $provider->kyc_status = 'partially_verified';
                $provider->kyc_message = 'Document uploaded successfully. Under review.';
            }
            
            $provider->save();

            return response()->json([
                'status' => true,
                'message' => 'Document uploaded successfully.',
                'data' => [
                    'name' => $request->document_name,
                    'status' => 'Under Review',
                    'url' => asset('storage/' . $path)
                ]
            ], 200);
        }

        return response()->json(['status' => false, 'message' => 'Failed to upload document.'], 200);
    }
}
