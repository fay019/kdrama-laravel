<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    /**
     * Show contact form
     */
    public function show()
    {
        return view('contact');
    }

    /**
     * Store contact message
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10|max:5000',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,csv,xlsx,xls,jpg,jpeg,png,gif,doc,docx',
        ], [
            'name.required' => __('contact.validation.name_required'),
            'email.required' => __('contact.validation.email_required'),
            'email.email' => __('contact.validation.email_invalid'),
            'subject.required' => __('contact.validation.subject_required'),
            'message.required' => __('contact.validation.message_required'),
            'message.min' => __('contact.validation.message_min'),
            'message.max' => __('contact.validation.message_max'),
            'attachment.max' => __('contact.validation.attachment_max'),
            'attachment.mimes' => __('contact.validation.attachment_mimes'),
        ]);

        $attachmentPath = null;
        $attachmentOriginalName = null;
        $attachmentMime = null;
        $attachmentSize = null;

        // Handle file upload
        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $file = $request->file('attachment');
            $attachmentOriginalName = $file->getClientOriginalName();
            $attachmentMime = $file->getMimeType();
            $attachmentSize = $file->getSize();

            // Store file in storage/app/private/contact-attachments
            $attachmentPath = $file->store('contact-attachments', 'local');
        }

        $emailSent = false;
        $errorMessage = null;

        try {
            // Send email to admin with attachment
            $adminEmail = env('MAIL_ADMIN_EMAIL', config('app.admin_email'));

            $mailable = new ContactMail($validated);

            // Add attachment if uploaded
            if ($attachmentPath) {
                $fullPath = storage_path('app/private/' . $attachmentPath);
                if (file_exists($fullPath)) {
                    $mailable->attach($fullPath, [
                        'as' => $attachmentOriginalName,
                        'mime' => $attachmentMime,
                    ]);
                }
            }

            Mail::to($adminEmail)->send($mailable);
            $emailSent = true;
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            \Log::error('Contact form email error', [
                'error' => $errorMessage,
                'trace' => $e->getTraceAsString()
            ]);
        }

        // Always save to database
        try {
            ContactMessage::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'attachment_path' => $attachmentPath,
                'attachment_original_name' => $attachmentOriginalName,
                'attachment_mime' => $attachmentMime,
                'attachment_size' => $attachmentSize,
                'email_sent' => $emailSent,
                'error_message' => $errorMessage,
            ]);

            return redirect()->route('contact.show')
                ->with('success', __('contact.success'));
        } catch (\Exception $e) {
            \Log::error('Contact form database error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', __('contact.error'));
        }
    }
}
