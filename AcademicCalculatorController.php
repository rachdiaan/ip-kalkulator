<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AcademicCalculatorController extends Controller
{
    /**
     * Display the academic calculator view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('calculator');
    }

    /**
     * Get feedback and suggestions from the Gemini AI.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAIFeedback(Request $request)
    {
        try {
            $validated = $request->validate([
                'gpa' => 'required|numeric',
                'studentName' => 'nullable|string|max:255',
                'studentNIM' => 'nullable|string|max:255',
                'courses' => 'required|array',
                'courses.*' => 'string', // Ensure each course is a string
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        }

        $studentName = $validated['studentName'] ?: "Mahasiswa";
        $studentNIM = $validated['studentNIM'] ?: "Tidak ada";
        $gpa = $validated['gpa'];
        $coursesText = implode("\n", $validated['courses']);

        $prompt = "Anda adalah seorang konselor akademik yang positif dan memotivasi di Telkom University. Seorang mahasiswa meminta umpan balik tentang performa akademiknya.\n\nData Mahasiswa:\nNama: {$studentName}\nNIM: {$studentNIM}\nIPK saat ini: {$gpa}\nMata Kuliah yang telah dinilai:\n{$coursesText}\n\nTugas Anda:\n1. Sapa mahasiswa dengan namanya jika tersedia. Berikan paragraf pembuka yang singkat, positif, dan memotivasi berdasarkan IPK mereka.\n2. Berikan 3-4 poin saran belajar yang spesifik, praktis, dan dapat ditindaklanjuti dalam format daftar (list).\n3. Berikan paragraf penutup yang memberi semangat.\n\nJawab dalam format HTML sederhana (gunakan <p>, <ul>, dan <li>) dan dalam Bahasa Indonesia.";

        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json(['error' => 'Kunci API Gemini tidak dikonfigurasi.'], 500);
        }

        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";
        
        $payload = [
            'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
        ];

        try {
            $response = Http::timeout(30)->post($apiUrl, $payload);

            if ($response->failed()) {
                 return response()->json(['error' => 'Gagal menghubungi layanan AI.'], $response->status());
            }

            $responseData = $response->json();
            
            if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                return response()->json(['feedback' => $responseData['candidates'][0]['content']['parts'][0]['text']]);
            } else {
                 return response()->json(['error' => 'Respon dari AI tidak valid.'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan internal: ' . $e->getMessage()], 500);
        }
    }
}
