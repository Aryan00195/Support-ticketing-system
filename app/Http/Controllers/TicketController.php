<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    //function to get all the tickets
    public function getTickets(Request $request)
    {
        try {
            $tickets = Ticket::all();
            $response = [];
            if ($request->requireTotalCount) {
                $response['totalCount'] =  $tickets->count();
            }
            if (isset($request->take)) {
                $tickets->skip($request->skip)->take($request->take);
            }
            if (isset($request->sort)) {
                $sort = json_decode($request->sort, true);
                if (count($sort)) {
                    $tickets->orderBy($sort[0]['selector'], ($sort[0]['desc'] ? 'DESC' : 'ASC'));
                }
            } else {
                $tickets->orderBy('created_at', 'DESC');
            }
            if ($request->has('filter')) {
                $filters = json_decode($request->filter, true);
                if (count($filters)) {
                    $filters = is_array($filters[0]) ? $filters[0] : $filters;
                    $search = !blank($filters[2]) ? $filters[2] : false;
                    if ($search) {
                        $tickets->where('name', 'like', "%$search%");
                    }
                }
            }
            $ticketList =  $tickets->get();
            $response['data'] = $ticketList;
            $totalCount =  $tickets->count();
            if ($ticketList->isNotEmpty()) {
                return response()->json([
                    'status' => true,
                    'data' => $ticketList,
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
}
