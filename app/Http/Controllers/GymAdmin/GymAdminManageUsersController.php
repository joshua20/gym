<?php

namespace App\Http\Controllers\GymAdmin;

use App\Classes\Reply;
use App\Models\GymMerchantRole;
use App\Models\GymMerchantRoleUser;
use App\Models\Merchant;
use App\Models\MerchantBusiness;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Facades\DataTables;

class GymAdminManageUsersController extends GymAdminBaseController
{

    public function index() {
        if(!$this->data['user']->can("manage_permissions"))
        {
            return App::abort(401);
        }

        $this->data['title'] = 'Users List';
        $this->data['userCount'] = Merchant::leftJoin('merchant_businesses', 'merchant_businesses.merchant_id', '=', 'merchants.id')
                    ->leftJoin('gym_merchant_role_users', 'gym_merchant_role_users.user_id', '=', 'merchants.id')
                    ->leftJoin('gym_merchant_roles', 'gym_merchant_roles.id', '=', 'gym_merchant_role_users.role_id')
                    ->where('merchant_businesses.detail_id', $this->data['user']->detail_id)
                    ->select('merchants.id', 'merchants.username', 'gym_merchant_roles.name')
                    ->count();

        return view('gym-admin.users.index', $this->data);
    }

    public function create() {
        if(!$this->data['user']->can("manage_permissions"))
        {
            return App::abort(401);
        }

        $this->data['title'] = 'Add User';
        return view('gym-admin.users.create', $this->data);
    }

    public function ajaxCreate()
    {
        // Use Laravel's authorization gates instead of manual checking
        $this->authorize('manage_permissions');

        // Utilize query scopes and relationships
        $query = Merchant::with(['merchantBusinesses', 'merchantRoles'])
            ->whereHas('merchantBusinesses', function ($q) {
                $q->where('detail_id', auth()->user()->detail_id);
            })
            ->when(!auth()->user()->is_admin, function ($q) {
                return $q->where('is_admin', '!=', 1);
            });

        return DataTables::of($query)
            ->addColumn('edit', function (Merchant $merchant) {
                return $this->generateActionButtons($merchant);
            })
            ->rawColumns(['edit'])
            ->make(true);
    }

    protected function generateActionButtons(Merchant $merchant)
    {
        return view('partials.user-actions', [
            'merchant' => $merchant,
            'editRoute' => route('gym-admin.users.edit', $merchant),
        ])->render();
    }

    public function edit($id) {
        if(!$this->data['user']->can("manage_permissions"))
        {
            return App::abort(401);
        }

        $this->data['title'] = 'User Edit';
        $this->data['merchant'] = Merchant::merchantDetail($this->data['user']->detail_id, $id);
        return view('gym-admin.users.edit', $this->data);
    }

    public function store() {

        if(!$this->data['user']->can("manage_permissions"))
        {
            return App::abort(401);
        }

        $validator = Validator::make(Input::all(), Merchant::$addUserRules);

        if($validator->fails())
        {
            return Reply::formErrors($validator);
        }

        $profile = new Merchant();
        $profile->first_name = Input::get('first_name');
        $profile->last_name = Input::get('last_name');
        $profile->mobile = Input::get('mobile');
        $profile->email = Input::get('email');
        $profile->gender = Input::get('gender');
        $profile->username = Input::get('username');
        $profile->gender = Input::get('gender');

        if(Input::get('date_of_birth') != '') {
            $profile->date_of_birth = Input::get('date_of_birth');
        }

        if(Input::has('password')) {
            $profile->password = Hash::make(Input::get('password'));
        }

        $profile->save();

        $insert = [
            "merchant_id" => $profile->id,
            "detail_id" => $this->data['user']->detail_id
        ];

        MerchantBusiness::firstOrCreate($insert);

        return Reply::redirect(route('gym-admin.users.index'), 'New user added.');
    }

    public function update($id) {
        $validator = Validator::make(Input::all(), Merchant::updateRules($id));

        if($validator->fails())
        {
            return Reply::formErrors($validator);
        }

        $id = Input::get('id');
        $profile = Merchant::find($id);
        $profile->first_name = Input::get('first_name');
        $profile->last_name = Input::get('last_name');
        $profile->mobile = Input::get('mobile');
        $profile->email = Input::get('email');
        $profile->gender = Input::get('gender');
        $profile->username = Input::get('username');

        if(Input::get('date_of_birth') != '') {
            $profile->date_of_birth = Input::get('date_of_birth');
        }

        if(Input::has('password')) {
            $profile->password = Hash::make(Input::get('password'));
        }

        $profile->save();
        return Reply::success('User details updated.');
    }

    public function destroy($id, Request $request) {
        if(!$this->data['user']->can("manage_permissions"))
        {
            return App::abort(401);
        }

        if ($request->ajax()) {
            Merchant::find($id)->delete();
            return Reply::redirect(route('gym-admin.users.index'), 'User removed successfully');
        }

        return Reply::error('Request not Valid');
    }

    public function assignRoleModal($id) {
        if($this->data['user']->is_admin == 0) {
            $rolesResults = GymMerchantRole::where('detail_id', $this->data['user']->detail_id)
                ->select('id', 'name')
                ->get();

            $adminResult = GymMerchantRole::select('gym_merchant_roles.id', 'gym_merchant_roles.name')
                ->join('gym_merchant_role_users', 'gym_merchant_role_users.role_id', '=', 'gym_merchant_roles.id')
                ->join('merchants', 'merchants.id', '=', 'gym_merchant_role_users.user_id')
                ->where('gym_merchant_roles.detail_id', '=', $this->data['user']->detail_id)
                ->where('merchants.is_admin', '=', 1)
                ->first();

            $result = [];

            foreach ($rolesResults as $rolesResult) {
                if($adminResult->name != $rolesResult->name){
                    array_push($result, $rolesResult);
                }
            }

            $this->data['roles'] = $result;
        } else {
            $this->data['roles'] = GymMerchantRole::byBusinessId($this->data['user']->detail_id);
        }

        $this->data['user'] = Merchant::find($id);

        return view('gym-admin.users.assign_role_modal', $this->data);
    }

    public function assignRoleStore($id) {

        $this->data['title'] = "Assign Role";
        $this->data['roleSelected'] = "active open";

        $input = Input::all();

        $validator = Validator::make($input, GymMerchantRoleUser::$rules);

        if($validator->fails())
        {
            return Reply::formErrors($validator);
        }

        GymMerchantRoleUser::where('user_id', '=', $id)->delete();

        GymMerchantRoleUser::create(['role_id' => $input['role_id'], 'user_id' => $id]);

        return Reply::redirect(route('gym-admin.users.index'), 'Role assigned.');
    }

}
