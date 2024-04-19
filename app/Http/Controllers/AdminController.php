<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Status;
use App\Models\Ticket;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    //function to get all users in admin panel
    public function getUsers(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user->hasRole('Admin')) {
                $users = User::where('id', '!=', $user->id);
            }
            $response = [];
            if ($request->requireTotalCount) {
                $response['totalCount'] = $users->count();
            }
            if (isset($request->take)) {
                $users->skip($request->skip)->take($request->take);
            }
            if (isset($request->sort)) {
                $sort = json_decode($request->sort, true);
                if (count($sort)) {
                    $users->orderBy($sort[0]['selector'], ($sort[0]['desc'] ? 'DESC' : 'ASC'));
                }
            } else {
                $users->orderBy('created_at', 'DESC');
            }
            if ($request->has('filter')) {
                $filters = json_decode($request->filter, true);
                if (count($filters)) {
                    $filters = is_array($filters[0]) ? $filters[0] : $filters;
                    $search = !blank($filters[2]) ? $filters[2] : false;
                    if ($search) {
                        $users->where('name', 'like', "%$search%");
                    }
                }
            }
            $userList = $users->get();
            foreach ($userList as $user) {
                $roles = $user->getRoleNames();
                $user->roles = $roles;
            }
            $response['data'] = $userList;
            $totalCount = $users->count();
            if ($userList->isNotEmpty()) {
                return response()->json([
                    'status' => true,
                    'data' => $userList,
                    'totalCount' => $totalCount,
                ], 200);
            } else {
                $response = [
                    'status' => false,
                    'message' => 'No Product found',
                ];
                return response()->json($response, 404);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    //function to find user data
    public function findUser(Request $request)
    {
        try {
            $userId = $request->userId;
            $user = User::find($userId);
            $user->getRoleNames();
            return response()->json(['status' => true, 'data' => $user], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    //function to add new user in the admin panel
    public function addUsers(Request $request)
    {
        // dd($request);
        try {
            $this->validate($request, [
                'name' => 'required',
                'email' => 'required|unique:users',
                'password' => 'required',
            ]);
            // dd($request->all());
            $input = $request->all();
            $user = User::create([
                'name' =>  $input['name'],
                'email' =>  $input['email'],
                'password' =>  Hash::make($input['password']),
            ]);
            if ($request->has('roleType')) {
                $user->assignRole($input['roleType']);
            } else {
                $user->assignRole('User');
            }
            return response()->json(['status' => true, 'data' => $user, 'message' => 'User Added Successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    //function to update user
    public function updateUser(Request $request, $id = 0)
    {
        try {
            $user = User::find($id);
            if ($user) {
                if ($request->has('roleType')) {
                    $user->syncRoles([]);
                    $user->assignRole($request->roleType);
                }
                $user->update($request->all());
                return response()->json(['status' => true, 'message' => 'User updated successfully'], 200);
            } else {
                $response = [
                    'status' => false,
                    'message' => 'user not found'
                ];
                return response()->json($response, 404);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    //function to remove user 
    public function removeUsers(Request $request)
    {
        try {
            $userId = $request->userId;
            $user = User::find($userId);
            if ($user) {
                $user->delete();
                $response = [
                    'status' => true,
                    'data' => 'User deleted Successsfully',
                ];
                return response()->json($response, 200);
            } else {
                $response = [
                    'status' => false,
                    'message' => 'no data found',
                ];
                return response()->json($response, 404);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function getAgentUsers()
{
    try {
      
        $agentUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'Agent');
        })
        ->select('id', 'name')
        ->get();

        return response()->json($agentUsers);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to fetch agent users'], 500);
    }
}
public function updateticket(Request $request, $id)
{
    try {
        $ticket = Ticket::find($id);
        $validatedData = $request->validate([
            'assign_agent_id' => 'nullable',
            'status_id' => 'nullable',
        ]);
        if (isset($validatedData['assign_agent_id'])) {
            $user = User::where('name', $validatedData['assign_agent_id'])->first();
            if (!$user) {
                unset($validatedData['assign_agent_id']);
            } else {
                $validatedData['assign_agent_id'] = $user->id;
            }
        }
        if (isset($validatedData['status_id'])) {
            $status = Status::where('name', $validatedData['status_id'])->first();
            if (!$status) {
                unset($validatedData['status_id']);
            } else {
                $validatedData['status_id'] = $status->id;
            }
        }
        if ($ticket) {
            $ticket->assign_agent_id = $validatedData['assign_agent_id'] ?? $ticket->assign_agent_id;
            $ticket->update($validatedData);
        } else {
            $ticket = Ticket::create($validatedData);
        }
        return response()->json(['message' => 'Ticket updated successfully'], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to update ticket', 'error' => $e->getMessage()], 500);
    }
}
public function getAgents(Request $request)
{
    try {
        $user = Auth::user();
        $users = User::query();

        if ($user->hasRole('Admin')) {
            $users->whereHas('roles', function ($query) {
                $query->where('name', 'Agent');
            });
        } else {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $response = [];

        if ($request->requireTotalCount) {
            $response['totalCount'] = $users->count();
        }

        if (isset($request->take)) {
            $users->skip($request->skip)->take($request->take);
        }

        if (isset($request->sort)) {
            $sort = json_decode($request->sort, true);
            if (count($sort)) {
                $users->orderBy($sort[0]['selector'], ($sort[0]['desc'] ? 'DESC' : 'ASC'));
            }
        } else {
            $users->orderBy('created_at', 'DESC');
        }

        if ($request->has('filter')) {
            $filters = json_decode($request->filter, true);
            if (count($filters)) {
                $filters = is_array($filters[0]) ? $filters[0] : $filters;
                $search = !blank($filters[2]) ? $filters[2] : false;
                if ($search) {
                    $users->where('name', 'like', "%$search%");
                }
            }
        }

        $userList = $users->get();

        foreach ($userList as $user) {
            $roles = $user->getRoleNames();
            $user->roles = $roles;
        }

        $response['data'] = $userList;
        $totalCount = $users->count();

        if ($userList->isNotEmpty()) {
            return response()->json([
                'status' => true,
                'data' => $userList,
                'totalCount' => $totalCount,
            ], 200);
        } else {
            $response = [
                'status' => false,
                'message' => 'No agents found',
            ];
            return response()->json($response, 404);
        }
    } catch (\Exception $e) {
        return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
    }
}





}
