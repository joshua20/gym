<?php

namespace App\Http\Controllers\GymAdmin;

use App\Classes\Reply;
use App\Models\GymClient;
use App\Models\GymMembership;
use App\Models\GymMembershipPayment;
use App\Models\GymPackage;
use App\Models\GymPurchase;
use App\Models\MerchantCustomPaymentType;
use App\Notifications\AddPaymentNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Yajra\Datatables\DataTables;

class GymMembershipPaymentsController extends GymAdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->data['paymentMenu'] = 'active';
        $this->data['showpaymentMenu'] = 'active';
        $this->data['account'] = 'active';
    }

    /**
     * Display the payments index page.
     */
    public function index() {
        if (!$this->data['user']->can("view_payments")) {
            return abort(401); // Use Laravel's abort helper
        }

        $this->data['title'] = 'Payments';
        return view('gym-admin.payments.index', $this->data);
    }

    /**
     * Load data for payments in DataTables.
     */
    public function ajax_create(Request $request)
{
    // Permission check
    if (!$this->data['user']->can('view_payments')) {
        return abort(401);
    }

    // Build payment query
    $paymentQuery = GymMembershipPayment::query()
        ->select([
            'gym_membership_payments.id as pid',
            DB::raw('CONCAT(gym_clients.first_name, " ", gym_clients.last_name) AS full_name'), // Combine first and last name
            'gym_clients.first_name',
            'gym_clients.last_name',
            'payment_amount',
            'gym_memberships.title as membership',
            'payment_source',
            'payment_date',
            'payment_id',
            'merchant_custom_payment_type.name as payment_type',
            'purchase_id'
        ])
        ->leftJoin('gym_client_purchases', 'gym_client_purchases.id', '=', 'gym_membership_payments.purchase_id')
        ->leftJoin('merchant_custom_payment_type', 'gym_membership_payments.payment_type', '=', 'merchant_custom_payment_type.id')
        ->leftJoin('gym_clients', 'gym_clients.id', '=', 'gym_membership_payments.user_id')
        ->leftJoin('gym_memberships', 'gym_memberships.id', '=', 'gym_client_purchases.membership_id')
        ->where('gym_membership_payments.detail_id', '=', $this->data['user']->detail_id);

    // Apply filters based on request (optional)
    if ($request->has('search')) {
        $paymentQuery->where(function ($query) use ($request) {
            $query->where('gym_clients.first_name', 'like', "%{$request->search}%")
                ->orWhere('gym_clients.last_name', 'like', "%{$request->search}%")
                ->orWhere('gym_memberships.title', 'like', "%{$request->search}%")
                ->orWhere('payment_id', 'like', "%{$request->search}%");
        });
    }

    // Configure DataTables
    return DataTables::of($paymentQuery)
        ->editColumn('full_name', fn($row) => ucwords($row->full_name))  // Use pre-built full_name column
        ->editColumn('payment_source', fn($row) => $this->formatPaymentSource($row->payment_source)) // Use separate formatting function
        ->editColumn('payment_date', fn($row) => Carbon::parse($row->payment_date)->toFormattedDateString())
        ->editColumn('payment_amount', fn($row) => $this->formatCurrency($row->payment_amount)) // Use separate formatting function
        ->editColumn('payment_id', fn($row) => "<b>{$row->payment_id}</b>")
        ->editColumn('payment_type', fn($row) => ucfirst($row->payment_type) ?? 'Membership')
        ->addColumn('action', function ($row) {
            return view('gym-admin.payments.partials.actions', ['row' => $row]);
        })
        ->rawColumns(['payment_source', 'payment_amount', 'payment_id', 'action'])
        ->make(true); // Enable server-side processing (optional)
}

// Helper functions (optional)
private function formatPaymentSource($source)
{
    switch ($source) {
        case 'cash':
            return "<div class='font-dark'>Cash <i class='fa fa-money'></i></div>";
        case 'credit_card':
            return "<div class='font-dark'>Credit Card <i class='fa fa-credit-card'></i></div>";
        case 'debit_card':
            return "<div class='font-dark'>Debit Card <i class='fa fa-cc-visa'></i></div>";
        default:
            return "<div class='font-dark'>Net Banking <i class='fa fa-internet-explorer'></i></div>";
    }
}

private function formatCurrency($amount)
{
    return "<i class='fa {$this->data['gymSettings']->currency->symbol}'></i> {$amount}";
}

    /**
     * Add a payment to the database.
     */
    public function store(Request $request) {
        if (!$this->data['user']->can("add_payment")) {
            return abort(401);
        }

        $validator = Validator::make($request->all(), GymMembershipPayment::rules('membership'));

        if ($validator->fails()) {
            return Reply::formErrors($validator);
        }

        $payment = new GymMembershipPayment();
        $payment->fill([
            'user_id' => $request->input('client'),
            'payment_amount' => $request->input('payment_amount'),
            'purchase_id' => $request->input('purchase_id'),
            'payment_source' => $request->input('payment_source'),
            'payment_date' => Carbon::parse($request->input('payment_date'))->format('Y-m-d'),
            'remarks' => $request->input('remark'),
            'payment_type' => $request->input('payment_type'),
            'detail_id' => $this->data['user']->detail_id
        ]);
        $payment->save();

        $payment->update(['payment_id' => 'HPR' . $payment->id]);

        // Update purchase record
        $purchase = GymPurchase::findOrFail($request->input('purchase_id'));
        $purchase->paid_amount += $request->input('payment_amount');
        $purchase->payment_required = $request->input('payment_required');
        $purchase->next_payment_date = $request->input('payment_required') === 'no'
            ? null
            : Carbon::parse($request->input('next_payment_date'))->format('Y-m-d');
        $purchase->save();

        Notification::send(GymClient::find($request->input('client')), new AddPaymentNotification($payment));

        return Reply::redirect(route('gym-admin.membership-payment.index'), 'Payment added successfully.');
    }

    // Additional methods can follow the same logic for clarity and readability.
}
