<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Jobs\SendTicketCreatedEmail;
use App\Notifications\DataChangeEmailNotification;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        return view('user.userpanel');
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
                'title' => 'required',
                'content' => 'required',

            ]);

            $imageName = null;
            if ($request->hasFile('Attachment')) {
                $image = $request->file('Attachment');
                $imageName = time() . '.' . $image->getClientOriginalExtension();

                $image->storeAs('public/assest', $imageName);
            }
            $pendingStatus = Status::firstOrCreate(['name' => 'Open']);

            $ticket = new Ticket([
                'title' => $validatedData['title'],
                'content' => $validatedData['content'],
                'category_id' => $request->category,
                'user_id' => auth()->user()->id,
                'Attachment' => $imageName,
                'status_id' => $pendingStatus->id,
            ]);


            $ticket->save();
            $ticketUrl = url('/user/view-ticket/' . $ticket->id);
            SendTicketCreatedEmail::dispatch($ticket, $ticketUrl);

            return response()->json([
                'status' => true,
                'message' => 'Ticket created successfully.',
                'data' => $ticket,
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Ticket creation failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($ticketId)
    {


        return view('user.viewticket')->with('ticketId', $ticketId);
    }
    public function fetchTicketDetails($ticketId)
    {
        try {
            $ticket = Ticket::with('user', 'comments.user.roles')->findOrFail($ticketId);

            $comments = $ticket->comments->map(function ($comment) {
                $comment->role = $comment->user->roles->first()->name;
                return $comment;
            });
            $statusName = $ticket->status->name;
            $ticket->status_name = $statusName;
            $categoryName = $ticket->category->name;
            $ticket->category_name =  $categoryName;

            return response()->json(['status' => true, 'data' => $ticket, 'comments' => $comments], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {

            $ticket = Ticket::findOrFail($id);


            if ($ticket->user_id !== Auth::id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access.',
                ], 403);
            }

            return response()->json([
                'status' => true,
                'data' => $ticket,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Ticket not found.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
    public function update(Request $request, $id)
    {

        try {
            $validatedData = $request->validate([
                'title' => 'required',
                'content' => 'required',


            ]);

            $ticket = Ticket::findOrFail($id);


            if ($request->hasFile('Attachment')) {

                $image = $request->file('Attachment');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/assest', $imageName);
                $ticket->Attachment = $imageName;
            }
            $ticket->update([
                'title' => $validatedData['title'],
                'content' => $validatedData['content'],

            ]);
            if ($request->has('status_id')) {
                $ticket->status_id = $request->input('status_id');
            }
            if ($request->has('category_id')) {
                $ticket->category_id = $request->input('category_id');
            }

            $ticket->save();
            return response()->json(['status' => true, 'message' => 'Ticket updated successfully', 'data' => $ticket], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Failed to update ticket', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {


            $ticket = Ticket::findOrFail($id);

            if ($ticket->user_id !== Auth::id()) {

                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access.',
                ], 403);
            }
            $ticket->comments()->delete();
            $ticket->delete();

            return response()->json([
                'status' => true,
                'message' => 'Ticket deleted successfully.',
            ], 200);
        } catch (\Exception $e) {


            return response()->json([
                'status' => false,
                'message' => 'Ticket deletion failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchTickets()
    {
        $user = Auth::user();

        $tickets = Ticket::where('user_id', $user->id)->get();
        //    dd($tickets);

        return response()->json([
            'status' => true,
            'data' => $tickets,
        ]);
    }

    public function agentindex()
    {
        //

        return view('agent.agentpanel');
    }
    public function agentTickets()
    {
        $user = Auth::user();

        $tickets = Ticket::where('assign_agent_id', $user->id)->get();

        return response()->json([
            'status' => true,
            'data' => $tickets,
        ]);
    }
    public function getCurrentUser()
    {
        $user = Auth::user();
        if ($user) {
            $roles = $user->getRoleNames();


            $role = $roles->first();

            return response()->json(['user' => $user, 'role' => $role]);
        } else {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
    }
    public function fetchstatus()
    {
        try {
            $Status = Status::all();


            if ($Status->isEmpty()) {
                return response()->json(['message' => 'No job types found'], 404);
            }

            return response()->json($Status);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving job types', 'error' => $e->getMessage()], 500);
        }
    }
    public function fetchcategory()
    {
        try {
            $Status = Category::all();


            if ($Status->isEmpty()) {
                return response()->json(['message' => 'No job types found'], 404);
            }

            return response()->json($Status);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving job types', 'error' => $e->getMessage()], 500);
        }
    }
}
