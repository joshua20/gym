<?php

namespace App\Http\Controllers\GymAdmin;

use App\Classes\Reply;
use App\Models\GymClientAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class GymAdminAttendanceController extends GymAdminBaseController
{

    public function __construct() {
        parent::__construct();
        $this->data['manageMenu'] = 'active';
        $this->data['attendanceMenu'] = 'active';
    }

    public function index() {

        $this->data['title'] = "Client Attendance";

        return view('gym-admin.attendance.index', $this->data);
    }

    public function create() {
        if (!$this->data['user']->can("add_attendance")) {
            return App::abort(401);
        }

        $this->data['title'] = "Client Attendance";

        return view('gym-admin.attendance.create', $this->data);
    }

    public function markAttendance(Request $request) {
        $this->authorize('add_attendance'); // Laravel's authorization method
    
        $validated = $request->validate([
            'clientId' => 'required|exists:gym_clients,id',
            'date' => 'required|date_format:d/M/Y H:ia'
        ]);
    
        $date = Carbon::createFromFormat('d/M/Y H:ia', $validated['date'])
            ->format('Y-m-d H:i:s');
    
        $attendance = GymClientAttendance::markAttendance($validated['clientId'], $date);
    
        return response()->json([
            'message' => 'Attendance marked successfully',
            'data' => ['id' => $attendance->id]
        ]);
    }

    public function checkin($Id) {
        $this->data['id'] = $Id;
        return View::make('gym-admin.attendance.checkin', $this->data);
    }

    public function destroy($id) {
        GymClientAttendance::destroy($id);
        return Reply::success("Checkin deleted successfully.");

    }

    public function ajax_create(Request $request)
{
    // Ensure the user has permission to add attendance
    abort_unless($this->data['user']->can('add_attendance'), 401);

    // Validate and parse the date
    $date = $request->has('date') 
        ? Carbon::createFromFormat('d/M/Y', $request->date)->format('Y-m-d') 
        : Carbon::today()->format('Y-m-d');

    $search = $request->input('search', '');
    $draw = intval($request->input('draw', 0));
    $start = intval($request->input('start', 0));
    $length = intval($request->input('length', 10));

    // Base query for client attendance
    $query = GymClientAttendance::clientAttendanceByDate($date, $search, $this->data['user']->detail_id);

    // Total records before filtering
    $totalRecords = $query->count();

    // Apply global search if needed
    if (!empty($search)) {
        $query->where(function($q) use ($search) {
            $q->where('first_name', 'LIKE', "%{$search}%")
              ->orWhere('last_name', 'LIKE', "%{$search}%");
        });
    }

    // Count filtered records
    $filteredRecords = $query->count();

    // Apply pagination
    $clientAttendance = $query->skip($start)->take($length)->get();

    // Generate the DataTable response
    return response()->json([
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $clientAttendance->map(function ($row) {
            return [
                'id' => $row->id,
                'first_name' => $row->first_name,
                'last_name' => $row->last_name,
                'full_name' => $row->first_name . ' ' . $row->last_name,
                'joining_date' => $row->joining_date,
                'check_in' => $row->check_in,
                'status' => $row->status,
                'image' => $row->image ?: '',
                'checkin_id' => $row->checkin_id,
                'total_checkin' => $row->total_checkin ?: 0,
                'actions' => view('gym-admin.attendance.ajaxview', [
                    'row' => $row,
                    'imageURL' => $this->data['profileHeaderPath'],
                    'gymSettings' => $this->data['gymSettings'],
                    'defaultImageURL' => $this->data['profilePath'],
                ])->render(),
            ];
        })->toArray(),
    ]);
}
}