<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $validatedData = $request->validate([
                'comment' => 'required|string',
                'ticket_id' => 'required',

            ]);
            $user = auth()->user();


            $comment = new Comment();
            $comment->comment_text = $validatedData['comment'];
            $comment->ticket_id = $validatedData['ticket_id'];
            $comment->user_id = $user->id;
            $comment->author_name = $user->name;
            $comment->author_email = $user->email;
            $comment->save();
            $user->getRoleNames();
           
            return response()->json([
                'status' => true,
                'message' => 'Comment stored successfully',
                'comment' => $comment,

            ], 200);
        } catch (\Exception $e) {

            return response()->json(['status' => false, 'message' => 'Failed to store comment', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
