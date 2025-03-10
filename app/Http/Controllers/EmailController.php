<?php

namespace App\Http\Controllers;
use GuzzleHttp\Client;
use App\Models\Email;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function generateEmail(Request $request)
    {
        $subject = $request->input('subject');
        $keywords = $request->input('keywords', ''); 

        if (!$subject) {
            return response()->json(['error' => 'Email subject is required'], 400);
        }

        $apiKey = env('GEMINI_API_KEY');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
        
        try {
            $client = new Client();
            $response = $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'contents' => [
                        ['parts' => [['text' => "Generate a professional email body for the subject: {$subject}" . ($keywords ? " with the following keywords: {$keywords}" : "")]]]
                    ]
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            $generatedText = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response from AI';
            
            $email = new Email();
            $email->subject = $subject;
            $email->content = $generatedText;
            $email->save();

            return response()->json(['email_content' => $generatedText]);
            
        } catch (\Exception $e) {
            \Log::error('Gemini API error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate email: ' . $e->getMessage()], 500);
        }
    }

    public function getEmails()
    {
        $emails = Email::latest()->get();
        return response()->json($emails);
    }

    public function getEmailById($id)
    {
        $email = Email::find($id);

        if (!$email) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        return response()->json($email);
    }

    public function updateEmail(Request $request, $id)
    {
        $email = Email::find($id);

        if (!$email) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        $email->content = $request->input('content');
        $email->save();

        return response()->json(['message' => 'Email updated successfully']);
    }

    public function deleteEmail($id)
    {
        $email = Email::find($id);

        if (!$email) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        $email->delete();

        return response()->json(['message' => 'Email deleted successfully']);
    }

    public function showEmailForm()
    {
        $emails = Email::all();
        return view('email-form', compact('emails'));
    }
}