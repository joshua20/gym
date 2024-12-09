<?php

namespace App\Http\Controllers\GymAdmin;

use App\Classes\Reply;
use App\Models\MerchantPromotionDatabase;
use App\Models\MerchantPromotionHistory;
use App\Models\MerchantSmsCredit;
use App\Models\MerchantSmsPurchase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Yajra\Datatables\Facades\DataTables;
use Illuminate\Support\Str;

class GymPromotionController extends GymAdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->data['promotionMenu'] = 'active';
        $this->data['promotionmainMenu'] = 'active';
    }

    public function index() {
        if (!$this->data['user']->can("view_previous_promotions")) {
            return App::abort(401);
        }

        $this->data['promotionindexMenu'] = 'active';
        $this->data['title'] = "Send Promotions";
        return View::make('gym-admin.promotion.index', $this->data);
    }

    public function create() {
        if (!$this->data['user']->can("send_promotions")) {
            return App::abort(401);
        }

        $this->data['creditsTransactions'] = MerchantSmsPurchase::where('merchant_id', '=', $this->data['user']->id)
            ->where('status', '!=', 'pending')
            ->count();
        $credits = MerchantSmsCredit::where('merchant_id', '=', $this->data['user']->id)->first();

        if (is_null($credits)) {
            $this->data['credits'] = 0;
        }
        else {
            $this->data['credits'] = $credits->credit_balance;
        }

        $this->data['promotionindexMenu'] = '';
        $this->data['promotionsendMenu'] = 'active';
        $this->data['title'] = "Send Promotions";
        return View::make('gym-admin.promotion.create', $this->data);
    }

    public function ajax_Create($filter)
{
    // Check if the user is authenticated
    if (!auth('web')->check()) {
        return response()->json(['error' => 'User not authenticated.'], 401);
    }

    // Get the authenticated user
    $user = auth()->user();

    // Ensure the user has a detail_id
    if (is_null($user->detail_id)) {
        return response()->json(['error' => 'User does not have a detail ID.'], 400);
    }

    // Build the query
    $query = MerchantPromotionDatabase::query()
        ->select('id', 'name', 'email', 'mobile', 'age', 'gender')
        ->where('detail_id', $user->detail_id);

    // Apply additional filters based on the filter parameter
    match ($filter) {
        'male' => $query->where('gender', 'male'),
        'female' => $query->where('gender', 'female'),
        default => null, // No additional filtering
    };

    // Return the data for DataTables
    return DataTables::of($query)
        ->addColumn('checkbox', function ($row) {
            return view('partials.promotion-checkbox', ['row' => $row]);
        })
        ->editColumn('name', fn($row) => Str::title($row->name))
        ->editColumn('email', fn($row) => "<i class='fa fa-envelope'></i> {$row->email}")
        ->editColumn('mobile', fn($row) => "<i class='fa fa-mobile'></i> {$row->mobile}")
        ->editColumn('gender', function ($row) {
            $icon = $row->gender === 'female' ? 'fa-female' : 'fa-male';
            return "<i class='fa {$icon}'></i> " . Str::title($row->gender);
        })
        ->rawColumns(['checkbox', 'email', 'mobile', 'gender'])
        ->make(true);
}


    public function store() {

        if (!$this->data['user']->can("send_promotions")) {
            return App::abort(401);
        }

        $validator = Validator::make(Input::all(), ['filter' => 'required', 'sms_text' => 'required', 'campaign' => 'required']);

        if ($validator->fails()) {
            return Reply::formErrors($validator);
        }

        $message = Input::get('sms_text');
        $campaign = Input::get('campaign');
        if (Input::has('random')) {
            $random = Input::get('random');
        }
        else {
            $random = 0;
        }
        $mobile = array();

        $filter = Input::get('filter');
        if ($filter == 'random') {
            if ($random == '') {
                return Reply::error('Random records field is required');
            }
        }
        if ($filter == 'manual') {
            if (!count(Input::get('userIds')) > 0) {
                return Reply::error('Please Select at least one client.');
            }
        }
        switch ($filter) {
            Case 'all':
                $user = MerchantPromotionDatabase::select('id', 'name', 'email', 'mobile', 'age', 'gender')
                    ->where('detail_id', '=', $this->data['user']->detail_id)
                    ->get();
                break;
            Case 'manual':
                $user = MerchantPromotionDatabase::select('id', 'name', 'email', 'mobile', 'age', 'gender')
                    ->where('detail_id', '=', $this->data['user']->detail_id)
                    ->whereIn('id', Input::get('userIds'))
                    ->get();
                break;
            Case 'random':
                try {
                    $user = MerchantPromotionDatabase::select('id', 'name', 'email', 'mobile', 'age', 'gender')
                        ->where('detail_id', '=', $this->data['user']->detail_id)
                        ->get()->random($random);
                } catch (\Exception $e) {
                    return Reply::error($e->getMessage());
                }
                break;
            Case 'male':
                $user = MerchantPromotionDatabase::select('id', 'name', 'email', 'mobile', 'age', 'gender')
                    ->where('detail_id', '=', $this->data['user']->detail_id)
                    ->where('gender', '=', 'male')
                    ->get();
                break;
            Case 'female':
                $user = MerchantPromotionDatabase::select('id', 'name', 'email', 'mobile', 'age', 'gender')
                    ->where('detail_id', '=', $this->data['user']->detail_id)
                    ->where('gender', '=', 'female')
                    ->get();
                break;
        }

        $MerchantCredits = MerchantSmsCredit::where('merchant_id', '=', $this->data['user']->id)->first();
        if ($MerchantCredits) {
            $credits = $MerchantCredits->credit_balance;
        }
        else {
            return Reply::error('Low credit balance. You need to buy sms credits to send promotion.');
        }
        $creditUsed = count($user);
        if ($creditUsed < $credits) {
            foreach ($user as $u) {
                array_push($mobile, $u->mobile);
            }
            $this->smsNotification($mobile, $message, 1);

            $MerchantCredits->credit_balance = $credits - $creditUsed;
            $MerchantCredits->save();

            $promotion = new MerchantPromotionHistory();
            $promotion->merchant_id = $this->data['user']->id;
            $promotion->campaign_name = $campaign;
            $promotion->sms_text = $message;
            $promotion->credits_used = $creditUsed;
            $promotion->save();

            return Reply::success('Promotions Sent successfully');
        }
        return Reply::error('Not Enough Credit Balance');

    }

    public function ajax_create_promotions() {
        if (!$this->data['user']->can("view_previous_promotions")) {
            return App::abort(401);
        }

        $promotions = MerchantPromotionHistory::select('campaign_name', 'sms_text', 'credits_used')
            ->where('merchant_id', '=', $this->data['user']->id);

        return Datatables::of($promotions)->make();
    }

}
