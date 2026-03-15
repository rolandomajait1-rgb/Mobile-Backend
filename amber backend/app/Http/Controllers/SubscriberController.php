<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Subscriber::query();

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->has('subscribed')) {
            $query->where('subscribed', $request->boolean('subscribed'));
        }

        $subscribers = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($subscribers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255|unique:subscribers',
            'name' => 'nullable|string|max:255',
        ]);

        $subscriber = Subscriber::create([
            ...$validated,
            'subscribed' => true,
        ]);

        return response()->json($subscriber, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscriber $subscriber)
    {
        return response()->json($subscriber);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subscriber $subscriber)
    {
        $validated = $request->validate([
            'email' => 'sometimes|email|max:255|unique:subscribers,email,'.$subscriber->id,
            'name' => 'nullable|string|max:255',
            'subscribed' => 'sometimes|boolean',
        ]);

        $subscriber->update($validated);

        return response()->json($subscriber);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscriber $subscriber)
    {
        $subscriber->delete();

        return response()->json(['message' => 'Subscriber deleted successfully']);
    }

    /**
     * Subscribe a new subscriber (public endpoint)
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255|unique:subscribers',
            'name' => 'nullable|string|max:255',
        ]);

        $subscriber = Subscriber::create([
            ...$validated,
            'subscribed' => true,
        ]);

        return response()->json([
            'message' => 'Successfully subscribed!',
            'subscriber' => $subscriber,
        ], 201);
    }

    /**
     * Unsubscribe a subscriber (public endpoint)
     * DELETE /subscribers/{email}
     */
    public function unsubscribe(string $email)
    {
        $subscriber = Subscriber::where('email', $email)->first();
        
        if (!$subscriber) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        $subscriber->update(['subscribed' => false]);

        return response()->json([
            'message' => 'Successfully unsubscribed!',
            'email' => $email
        ]);
    }
}
