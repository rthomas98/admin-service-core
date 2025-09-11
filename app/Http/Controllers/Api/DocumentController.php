<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DocumentController extends Controller
{
    /**
     * Get driver documents
     */
    public function index(Request $request): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        // Mock documents for now (in a real app, these would be in a documents table)
        $documents = [
            [
                'id' => 1,
                'type' => 'drivers_license',
                'name' => 'Driver\'s License',
                'status' => 'valid',
                'expiry_date' => $driver->license_expiry,
                'uploaded_date' => Carbon::now()->subMonths(6)->toDateString(),
                'file_url' => null,
                'category' => 'license',
            ],
            [
                'id' => 2,
                'type' => 'medical_certificate',
                'name' => 'DOT Medical Certificate',
                'status' => 'valid',
                'expiry_date' => Carbon::now()->addMonths(18)->toDateString(),
                'uploaded_date' => Carbon::now()->subMonths(6)->toDateString(),
                'file_url' => null,
                'category' => 'medical',
            ],
            [
                'id' => 3,
                'type' => 'hazmat_certification',
                'name' => 'HAZMAT Certification',
                'status' => 'expiring_soon',
                'expiry_date' => Carbon::now()->addDays(15)->toDateString(),
                'uploaded_date' => Carbon::now()->subYear()->toDateString(),
                'file_url' => null,
                'category' => 'certification',
            ],
            [
                'id' => 4,
                'type' => 'insurance_card',
                'name' => 'Insurance Card',
                'status' => 'valid',
                'expiry_date' => Carbon::now()->addMonths(6)->toDateString(),
                'uploaded_date' => Carbon::now()->subMonths(3)->toDateString(),
                'file_url' => null,
                'category' => 'insurance',
            ],
            [
                'id' => 5,
                'type' => 'vehicle_registration',
                'name' => 'Vehicle Registration',
                'status' => 'valid',
                'expiry_date' => Carbon::now()->addMonths(9)->toDateString(),
                'uploaded_date' => Carbon::now()->subMonths(3)->toDateString(),
                'file_url' => null,
                'category' => 'vehicle',
            ],
        ];

        // Calculate document statistics
        $stats = [
            'total_documents' => count($documents),
            'valid' => count(array_filter($documents, fn($d) => $d['status'] === 'valid')),
            'expiring_soon' => count(array_filter($documents, fn($d) => $d['status'] === 'expiring_soon')),
            'expired' => count(array_filter($documents, fn($d) => $d['status'] === 'expired')),
        ];

        return response()->json([
            'documents' => $documents,
            'stats' => $stats,
        ]);
    }

    /**
     * Upload a document
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string',
            'name' => 'required|string',
            'document' => 'required|string', // Base64 document
            'expiry_date' => 'nullable|date',
        ]);

        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        // Process base64 document
        $documentData = base64_decode(preg_replace('#^data:[\w/]+;base64,#i', '', $request->document));
        $documentPath = 'documents/' . $driver->id . '/' . $request->type . '_' . time() . '.pdf';
        
        \Storage::disk('public')->put($documentPath, $documentData);

        return response()->json([
            'message' => 'Document uploaded successfully',
            'document' => [
                'id' => rand(100, 999),
                'type' => $request->type,
                'name' => $request->name,
                'status' => 'pending_review',
                'expiry_date' => $request->expiry_date,
                'uploaded_date' => Carbon::now()->toDateString(),
                'file_url' => \Storage::url($documentPath),
            ]
        ]);
    }

    /**
     * Get training records
     */
    public function training(Request $request): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        // Mock training records
        $trainings = [
            [
                'id' => 1,
                'course_name' => 'Defensive Driving',
                'category' => 'safety',
                'status' => 'completed',
                'completion_date' => Carbon::now()->subMonths(2)->toDateString(),
                'expiry_date' => Carbon::now()->addMonths(10)->toDateString(),
                'certificate_number' => 'DD-2024-' . $driver->id,
                'duration_hours' => 8,
                'score' => 92,
            ],
            [
                'id' => 2,
                'course_name' => 'HAZMAT Handling',
                'category' => 'certification',
                'status' => 'completed',
                'completion_date' => Carbon::now()->subMonths(6)->toDateString(),
                'expiry_date' => Carbon::now()->addMonths(6)->toDateString(),
                'certificate_number' => 'HM-2024-' . $driver->id,
                'duration_hours' => 16,
                'score' => 88,
            ],
            [
                'id' => 3,
                'course_name' => 'Hours of Service Compliance',
                'category' => 'compliance',
                'status' => 'in_progress',
                'completion_date' => null,
                'expiry_date' => null,
                'certificate_number' => null,
                'duration_hours' => 4,
                'progress' => 75,
            ],
            [
                'id' => 4,
                'course_name' => 'Winter Driving Safety',
                'category' => 'safety',
                'status' => 'not_started',
                'completion_date' => null,
                'expiry_date' => null,
                'certificate_number' => null,
                'duration_hours' => 6,
                'due_date' => Carbon::now()->addDays(30)->toDateString(),
            ],
        ];

        $stats = [
            'total_courses' => count($trainings),
            'completed' => count(array_filter($trainings, fn($t) => $t['status'] === 'completed')),
            'in_progress' => count(array_filter($trainings, fn($t) => $t['status'] === 'in_progress')),
            'not_started' => count(array_filter($trainings, fn($t) => $t['status'] === 'not_started')),
            'total_hours' => array_sum(array_map(fn($t) => $t['status'] === 'completed' ? $t['duration_hours'] : 0, $trainings)),
        ];

        return response()->json([
            'trainings' => $trainings,
            'stats' => $stats,
        ]);
    }
}