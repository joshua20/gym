<?php

namespace App\Http\Controllers\GymAdmin;

use App\Classes\Reply;
use App\Models\MerchantPromotionDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Yajra\Datatables\Facades\DataTables;
use Illuminate\Support\Str;

class GymPromotionalDbController extends GymAdminBaseController
{

    public function index() {

        if (!$this->data['user']->can("view_previous_promotions")) {
            return App::abort(401);
        }

        $this->data['title'] = "Promotional Database";
        return View::make('gym-admin.promotional-db.index', $this->data);
    }

    public function create() {
        $this->data['title'] = "Promotional Database";
        return View::make('gym-admin.promotional-db.create', $this->data);
    }

    public function ajax_Create()
    {
        // Check if the user is authenticated
        if (!auth()->check()) {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }
    
        // Get the authenticated user
        $user = auth()->user();
    
        // Ensure the user has a detail_id
        if (is_null($user->detail_id)) {
            return response()->json(['error' => 'User does not have a detail ID.'], 400);
        }
    
        // Proceed with your query
        $query = MerchantPromotionDatabase::query()
            ->select('name', 'email', 'mobile', 'age', 'gender', 'id')
            ->where('detail_id', $user->detail_id);
    
        return DataTables::of($query)
            ->editColumn('name', fn($row) => Str::title($row->name))
            ->editColumn('email', fn($row) => "<i class='fa fa-envelope'></i> {$row->email}")
            ->editColumn('mobile', fn($row) => "<i class='fa fa-mobile'></i> {$row->mobile}")
            ->rawColumns(['email', 'mobile'])
            ->make(true);
    }
    
    public function store() {
        $validator = Validator::make(Input::all(), MerchantPromotionDatabase::rules('add', null));

        if ($validator->fails()) {
            return Reply::formErrors($validator);
        }

        $data = [
            'name' => Input::get('name'),
            'email' => Input::get('email'),
            'number' => Input::get('mobile'),
            'age' => Input::get('age'),
            'gender' => Input::get('gender')
        ];

        $this->addPromotionDatabase($data);

        return Reply::redirect(route('gym-admin.promotion-db.index'), 'Client added to database');

    }

    public function update($id) {
        $validator = Validator::make(Input::all(), MerchantPromotionDatabase::rules('edit', $id));
//        $validator = Validator::make(Input::all(),MerchantPromotionDatabase::$rules);

        if ($validator->fails()) {
            return Reply::formErrors($validator);
        }

        $user = MerchantPromotionDatabase::where('id', '=', $id)->where('detail_id', '=', $this->data['user']->detail_id)->first();
        $user->name = Input::get('name');
        $user->email = Input::get('email');
        $user->age = Input::get('age');
        $user->mobile = Input::get('mobile');
        $user->gender = Input::get('gender');
        $user->save();

        return Reply::success('Client updated to database');
    }

    public function show($id) {
        $this->data['title'] = "Promotional Database";
        $this->data['client'] = MerchantPromotionDatabase::find($id);
        return View::make('gym-admin.promotional-db.edit', $this->data);
    }

    public function destroy($id) {
        if (request()->ajax()) {
            $promotional = MerchantPromotionDatabase::find($id);
            $promotional->delete();

            return Reply::success("promotional deleted successfully.");
        }
    }

}
