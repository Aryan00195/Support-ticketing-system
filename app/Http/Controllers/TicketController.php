<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\Status;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendTicketCreatedEmail;
use App\Notifications\DataChangeEmailNotification;
use Illuminate\Support\Facades\Validator;

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
                'subcategory_id' => $request->subcategory_id
            ]);


            $ticket->save();
            $ticketUrl = url('/user/view-ticket/' . $ticket->id);
            // SendTicketCreatedEmail::dispatch($ticket, $ticketUrl);

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
            if ($request->has('subcategory_id')) {
                $ticket->subcategory_id = $request->input('subcategory_id');
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

        $tickets = Ticket::with('comments', 'status', 'category', 'subcategory')->where('user_id', $user->id)->get();
        //dd($tickets);
        $tickets->each(function ($ticket) {
            $ticket->status_name = $ticket->status->name;
            $ticket->category_name = $ticket->category->name;
//             if($ticket->subcategory_name!=null)
//             {
// $ticket->subcategory_name=$ticket->subcategory->name;
//             }// $ticket->subcategory_name = $ticket->subcategory->name;

        });
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
            $categories = Category::all();
            if ($categories->isEmpty()) {
                return ['message' => 'No job types found', 'data' => []];
            }

            return ['data' => $categories];
        } catch (\Exception $e) {
            return ['message' => 'Error retrieving job types', 'error' => $e->getMessage()];
        }
    }
    public function getTickets(Request $request)
    {
        try {
            $query = Ticket::with('category:id,name', 'status:id,name', 'user:id,name,email', 'agent:id,name');
            if ($request->has('requireTotalCount')) {
                $totalCount = $query->count();
            }
            if ($request->has('take')) {
                $query->skip($request->skip)->take($request->take);
            }

            if ($request->has('sort')) {
                $sort = json_decode($request->sort, true);
                if (count($sort)) {
                    $query->orderBy($sort[0]['selector'], $sort[0]['desc'] ? 'desc' : 'asc');
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            if ($request->has('filter')) {
                $filters = json_decode($request->filter, true);
                if (count($filters)) {
                    $search = $filters[0]['value'] ?? null;
                    if ($search) {
                        $query->where('name', 'like', "%$search%");
                    }
                }
            }
            $tickets = $query->get();
            if ($tickets->isNotEmpty()) {
                return response()->json([
                    'status' => true,
                    'data' => $tickets,
                    'totalCount' => $totalCount ?? null,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No tickets found',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function countTickets()
    {
        try {
            $totalCount = Ticket::count();

            return response()->json([
                'status' => true,
                'totalCount' => $totalCount,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function countUsers()
    {
        $totalUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'User');
        })->count();

        return response()->json([
            'total_users' => $totalUsers
        ]);
    }
    public function countAgents()
    {
        $totalUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'Agent');
        })->count();

        return response()->json([
            'total_agents' => $totalUsers
        ]);
    }
    public function getRecentTickets()
    {
        try {
            $recentTickets = Ticket::with(['user', 'category', 'status'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            return response()->json([
                'recentTickets' => $recentTickets
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch recent tickets',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    //function to edit ticket
    public function updateTicket(Request $request, $Id = 0)
    {
        try {
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    //function to remove ticket 
    public function removeTicket(Request $request, $Id = 0)
    {
        try {
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'meassge' => $e->getMessage()], 500);
        }
    }
    public function getGraphData()
    {
        try {

            $tickets = Ticket::all();


            $ticketCountsByMonth = [];

            foreach ($tickets as $ticket) {
                $createdAt = $ticket->created_at;
                $month = $createdAt->format('F');

                if (!isset($ticketCountsByMonth[$month])) {
                    $ticketCountsByMonth[$month] = 0;
                }

                $ticketCountsByMonth[$month]++;
            }
            Log::info('Ticket counts by month: ' . json_encode($ticketCountsByMonth));
            $formattedData = [];

            foreach ($ticketCountsByMonth as $month => $ticketCount) {
                $formattedData[] = [
                    'Month' => $month,
                    'ticketCount' => $ticketCount,
                ];
            }
            return response()->json(['status' => true, 'data' => $formattedData], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function show2($ticketId)
    {


        return view('agent.agentviewticket')->with('ticketId', $ticketId);
    }
    public function addCategory(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
            ]);
            if ($validator->fails()) {
                return ['message' => 'Validation error', 'errors' => $validator->errors()];
            }
            $category = new Category();
            $category->name = $request->input('name');

            $category->save();


            return ['message' => 'Category added successfully', 'data' => $category];
        } catch (\Exception $e) {

            return ['message' => 'Error adding category', 'error' => $e->getMessage()];
        }
    }
    public function deleteCategory($categoryId)
    {
        try {
            $category = Category::find($categoryId);
            if (!$category) {
                return ['message' => 'Category not found'];
            }
            $category->delete();
            return ['message' => 'Category deleted successfully'];
        } catch (\Exception $e) {
            return ['message' => 'Error deleting category', 'error' => $e->getMessage()];
        }
    }

    public function UpdateCategory(Request $request, $categoryId)
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',

            ]);


            if ($validator->fails()) {
                return ['message' => 'Validation error', 'errors' => $validator->errors()];
            }


            $category = Category::find($categoryId);


            if (!$category) {
                return ['message' => 'Category not found'];
            }


            $category->name = $request->input('name');

            $category->save();


            return ['message' => 'Category updated successfully', 'data' => $category];
        } catch (\Exception $e) {

            return ['message' => 'Error updating category', 'error' => $e->getMessage()];
        }
    }
    // public function getSubcategories(Category $category)
    // {
    //     $subcategories = Subcategory::where('category_id', $category->id)->get(); 
    //     return response()->json(['data' => $subcategories]);
    // }
    public function getSubcategories(Request $request)
    {
        try {
            if (empty($request->category_id)) {
                $subcategories = Subcategory::all();
            }
            $subcategories = Subcategory::where("category_id", $request->category_id)->get();
            if ($subcategories->isEmpty()) {
                return ['message' => 'No subcategory found', 'data' => []];
            }
            return ['data' => $subcategories];
        } catch (\Exception $e) {
            return ['message' => 'Error retrieving subcategory', 'error' => $e->getMessage()];
        }
    }
    // public function getSubcategories($categoryId)
    // {
    //     $subcategories = Subcategory::where('category_id', $categoryId)->get();
    //     return response()->json(['data' => $subcategories]);
    // }
    public function fetchsubcategory()
    {
        try {
            $subcategories = Subcategory::with('category')->get();
            if ($subcategories->isEmpty()) {
                return ['message' => 'No subcategory found', 'data' => []];
            }
            $data = $subcategories->map(function ($subcategory) {
                return [
                    'id' => $subcategory->id,
                    'subcategory_name' => $subcategory->name,
                    'category_name' => $subcategory->category->name
                ];
            });

            return ['data' => $data];
        } catch (\Exception $e) {
            return ['message' => 'Error retrieving subcategory', 'error' => $e->getMessage()];
        }
    }
    public function deletesubCategory($subcategoryId)
    {
        try {
            $category = Subcategory::find($subcategoryId);
            if (!$category) {
                return ['message' => 'SubCategory not found'];
            }
            $category->delete();
            return ['message' => 'SubCategory deleted successfully'];
        } catch (\Exception $e) {
            return ['message' => 'Error deleting subcategory', 'error' => $e->getMessage()];
        }
    }
    public function addsubCategory(Request $request)
    {
        $categoryName = $request->query('category_id');
        $category = Category::where('name', $categoryName)->first();
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }
        $subcategory = new Subcategory();
        $subcategory->name = $request->input('subcategory_name');
        $subcategory->category_id = $category->id;
        $subcategory->save();
        return response()->json(['message' => 'Subcategory created successfully'], 200);
    }
    public function UpdateSubCategory(Request $request, $categoryId)
    {

        try {
            $subcategory = Subcategory::find($categoryId);
            if (!$subcategory) {
                return ['message' => 'Category not found'];
            }
            $categoryName = $request->query('category_id');

            $category = Category::where('name', $categoryName)->first();
            $subcategory->name = $request->input('subcategory_name');
            $subcategory->category_id = $category->id;

            $subcategory->update();

            return ['message' => 'Category updated successfully', 'data' => $category];
        } catch (\Exception $e) {
            return ['message' => 'Error updating category', 'error' => $e->getMessage()];
        }
    }
}
