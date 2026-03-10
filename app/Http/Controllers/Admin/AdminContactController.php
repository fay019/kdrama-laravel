<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class AdminContactController extends Controller
{
    /**
     * Display contact messages list with stats
     */
    public function index(Request $request)
    {
        // Stats
        $totalMessages = ContactMessage::count();
        $pendingCount = ContactMessage::where('status', 'pending')->count();
        $readCount = ContactMessage::where('status', 'read')->count();
        $resolvedCount = ContactMessage::where('status', 'resolved')->count();
        $spamCount = ContactMessage::where('status', 'spam')->count();
        $emailFailedCount = ContactMessage::where('email_sent', false)->count();

        // Filter
        $query = ContactMessage::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search)
                  ->orWhere('subject', 'like', $search)
                  ->orWhere('message', 'like', $search);
            });
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.contact.index', [
            'messages' => $messages,
            'totalMessages' => $totalMessages,
            'pendingCount' => $pendingCount,
            'readCount' => $readCount,
            'resolvedCount' => $resolvedCount,
            'spamCount' => $spamCount,
            'emailFailedCount' => $emailFailedCount,
            'currentStatus' => $request->status,
            'searchQuery' => $request->search,
        ]);
    }

    /**
     * Show single message
     */
    public function show($id)
    {
        $message = ContactMessage::findOrFail($id);

        // Mark as read
        if ($message->status === 'pending') {
            $message->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
        }

        return view('admin.contact.show', ['message' => $message]);
    }

    /**
     * Update message status with workflow validation
     */
    public function updateStatus($id, Request $request)
    {
        try {
            $message = ContactMessage::findOrFail($id);

            $validated = $request->validate([
                'status' => 'required|in:pending,read,in_progress,resolved,spam',
            ]);

            $newStatus = $validated['status'];
            $currentStatus = $message->status;

            // Define valid workflow transitions
            $allowedTransitions = [
                'pending' => ['read', 'spam'],
                'read' => ['in_progress', 'resolved', 'spam'],
                'in_progress' => ['resolved', 'spam'],
                'resolved' => [],  // Cannot change from resolved
                'spam' => ['pending'],  // Can unmark as spam
            ];

            // Check if transition is allowed
            if (!isset($allowedTransitions[$currentStatus]) || !in_array($newStatus, $allowedTransitions[$currentStatus])) {
                return back()->with('error', __('admin.contact.status_transition_forbidden', ['from' => $currentStatus, 'to' => $newStatus]));
            }

            $updateData = ['status' => $newStatus];

            // Auto-set timestamps for status changes
            if ($newStatus === 'read' && !$message->read_at) {
                $updateData['read_at'] = now();
            }

            if ($newStatus === 'in_progress' && !$message->read_at) {
                $updateData['read_at'] = now();
            }

            if ($newStatus === 'resolved' && !$message->resolved_at) {
                $updateData['resolved_at'] = now();
            }

            $message->update($updateData);

            $statusLabels = [
                'pending' => __('admin.contact.show.status_pending'),
                'read' => __('admin.contact.show.status_read'),
                'in_progress' => __('admin.contact.show.status_in_progress'),
                'resolved' => __('admin.contact.show.status_resolved'),
                'spam' => __('admin.contact.show.status_spam'),
            ];

            return back()->with('success', __('admin.contact.status_changed', ['status' => $statusLabels[$newStatus]]));
        } catch (\Exception $e) {
            \Log::error('Contact status update error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', __('admin.contact.status_update_error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Delete message
     */
    public function destroy($id)
    {
        $message = ContactMessage::findOrFail($id);

        // Delete attachment if exists
        if ($message->attachment_path) {
            \Storage::disk('local')->delete($message->attachment_path);
        }

        $message->delete();

        return back()->with('success', __('admin.contact.deleted'));
    }

    /**
     * Download attachment
     */
    public function downloadAttachment($id)
    {
        $message = ContactMessage::findOrFail($id);

        if (!$message->attachment_path || !\Storage::disk('local')->exists($message->attachment_path)) {
            return back()->with('error', __('admin.contact.attachment_not_found'));
        }

        return \Storage::disk('local')->download(
            $message->attachment_path,
            $message->attachment_original_name
        );
    }
}
