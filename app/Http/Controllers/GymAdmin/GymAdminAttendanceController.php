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

    public function markAttendance(Request $request)
    {
        // Authorization check
        if (!auth()->user()->can('add_attendance')) {
            abort(403, 'Unauthorized action.');
        }
    
        // Validate the request
        $validated = $request->validate([
            'date' => 'required|date_format:d/M/Y h:ia',
            'clientId' => 'required|integer|exists:gym_clients,id', // Assuming gym_clients is the related table
        ]);
    
        // Parse and format the date
        $date = Carbon::createFromFormat('d/M/Y h:ia', $validated['date'])->format('Y-m-d H:i:s');
    
        // Mark attendance
        $data = GymClientAttendance::markAttendance($validated['clientId'], $date);
    
        // Return success response
        return response()->json([
            'status' => 'success',
            'message' => 'Attendance marked successfully.',
            'data' => ['id' => $data->id],
        ]);
    }

    public function checkin($Id)
{
    // Set data for the view
    $this->data['id'] = $Id;

    // Return the checkin view
    return view('gym-admin.attendance.checkin', $this->data);
}

    public function destroy($id) {
        GymClientAttendance::destroy($id);
        return Reply::success("Checkin deleted successfully.");

    }

    public function ajax_Create(Request $request)
{
    // Ensure the user is authenticated
    $user = auth()->user();
    if (!$user || !$user->can('add_attendance')) {
        abort(403, 'Unauthorized action.');
    }

    // Validate the request
    $validated = $request->validate([
        'date' => 'required|date_format:d/M/Y',
        'search_data' => 'nullable|string',
    ]);

    // Parse and format the date
    $date = Carbon::createFromFormat('d/M/Y', $validated['date'])->format('Y-m-d');
    $search = $validated['search_data'];

    // Fetch attendance data
    $clientAttendance = GymClientAttendance::clientAttendanceByDate(
        $date, 
        $search, 
        $user->detail_id
    );

    // Return DataTables response
    return DataTables::of($clientAttendance)
        ->addColumn('id', function ($row) {
            return view('gym-admin.attendance.ajaxview', [
                'row' => $row,
                'imageURL' => $this->data['profileHeaderPath'],
                'gymSettings' => $this->data['gymSettings'],
                'deaultImageURL' => $this->data['profilePath']
            ])->render();
        })
        ->removeColumn(['first_name', 'last_name', 'joining_date', 'check_in', 'status', 'image', 'checkin_id', 'total_checkin'])
        ->make(true);
}


}