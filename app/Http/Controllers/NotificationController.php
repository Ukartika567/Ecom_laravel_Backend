<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    function getNotificationData($user_id){
        // Use Eloquent to query the notifications for the specified user_id
        $notifications = Notification::where('user_id', $user_id)->get();

        // Return the filtered notifications
        return $notifications;
    }

    function insertNotificationData(Request $request){
        // Validate the incoming request data
        $request->validate([
            'user_id' => 'required',
            'type' => 'required',
            'title' => 'required',
            'description' => 'required',
        ]);
        
        // Create a new notification record
        $notification = Notification::create([
            'user_id' => $request->input('user_id'),
            'type' => $request->input('type'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
        ]);

        // Return a response indicating success or failure
        if ($notification) {
            return response()->json(['message' => 'Notification created successfully'], 201);
        } else {
            return response()->json(['message' => 'Notification creation failed'], 500);
        }
    }
}
