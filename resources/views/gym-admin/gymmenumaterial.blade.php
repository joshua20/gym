<div class="page-header-menu">
    <div class="container-fluid">

        <!-- BEGIN MEGA MENU -->
        <!-- DOC: Apply "hor-menu-light" class after the "hor-menu" class below to have a horizontal menu with white background -->
        <!-- DOC: Remove data-hover="dropdown" and data-close-others="true" attributes below to disable the dropdown opening on mouse hover -->
        <div class="hor-menu  ">
            <ul class="nav navbar-nav">
                @if($user->is_admin == 1)
                    <li class="menu-dropdown classic-menu-dropdown {{ $superAdminMenu ?? '' }}">
                        <a href="{{route('gym-admin.superadmin.dashboard')}}"><i class="font-green fa fa-dashboard"></i> Super Admin
                        </a>
                    </li>
                @endif
                @if($user->can("view_dashboard"))
                    <li class="menu-dropdown classic-menu-dropdown {{ $dashboardMenu ?? ''}}">
                        <a href="{{route('gym-admin.dashboard.index')}}"><i class="font-green fa fa-dashboard"></i> Dashboard
                        </a>
                    </li>
                @endif
                @if($user->can("view_customers") || $user->can("add_attendance") || $user->can("my_gym") || $user->can("view_enquiry")
                || $user->can("view_targets") || $user->can("view_subscriptions") || $user->can("view_membership") || $user->can("task"))
                    <li class="menu-dropdown mega-menu-dropdown {{$manageMenu ?? ''}}">
                        <a href="javascript:;"><i class="font-green fa fa-gear"></i> Manage <i class="fa fa-angle-down hidden-xs hidden-sm"></i>
                            <span class="arrow"></span>
                        </a>
                        <ul class="dropdown-menu" style="min-width: 400px">
                            <li>
                                <div class="mega-menu-content">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="mega-menu-submenu ">
                                                @if($user->can("view_customers"))
                                                    <li class="{{ $customerMenu ?? ''}}">
                                                        <a href="{{route('gym-admin.client.index')}}" class="nav-link  ">
                                                            <i class="icon-users"></i>  Customers
                                                        </a>
                                                    </li>
                                                @endif
                                                @if($user->can("add_attendance"))
                                                    <li class="{{$attendanceMenu ?? ''}} ">
                                                        <a href="{{route('gym-admin.attendance.create')}}" class="nav-link  ">
                                                            <i class="icon-plus"></i> Mark Attendance
                                                        </a>
                                                    </li>
                                                @endif
                                                @if($user->can("my_gym"))
                                                    <li class="{{ $gymMenu ?? ''}} ">
                                                        <a href="{{route('gym-admin.my-gym.index')}}" class="nav-link  ">
                                                            <i class="fa fa-heartbeat"></i> My Business
                                                        </a>
                                                    </li>
                                                @endif
                                                @if($user->can("view_enquiry"))
                                                    <li class="{{ $enuriryMenu ?? ''}} ">
                                                        <a href="{{route('gym-admin.enquiry.index')}}" class="nav-link">
                                                            <i class="font-green icon-earphones-alt"></i>  Enquiries
                                                        </a>
                                                    </li>
                                                @endif
                                                @if($user->can("message"))
                                                    <li class="{{ $messageMenu ?? ''}} ">
                                                        <a href="{{route('gym-admin.message.index')}}" class="nav-link">
                                                            <i class="fa fa-envelope"></i>  Message
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="mega-menu-submenu">
                                                @if($user->can("view_targets"))
                                                    <li class="{{$targetMenu ?? ''}}">
                                                        <a href="{{route('gym-admin.target.index')}}" class="nav-link  ">
                                                            <i class="fa fa-bullseye"></i>  Targets
                                                        </a>
                                                    </li>
                                                @endif
                                                @if($user->can("view_subscriptions"))
                                                    <li class="{{$subscriptionMenu ?? ''}}">
                                                        <a href="{{route('gym-admin.client-purchase.index')}}" class="nav-link  ">
                                                                <i class="fa {{ $gymSettings->currency->symbol }}"></i>  Subscriptions
                                                        </a>
                                                    </li>
                                                @endif
                                                @if($user->can("view_membership"))
                                                    <li class="{{$membershipMenu ?? ''}}">
                                                        <a href="{{route('gym-admin.membership.index')}}" class="nav-link nav-toggle">
                                                            <i class="icon-badge"></i> Memberships
                                                        </a>
                                                    </li>
                                                @endif
                                                @if($user->can("task"))
                                                    <li class="{{$taskMenu ??''}}">
                                                        <a href="{{route('gym-admin.task.index')}}" class="nav-link nav-toggle">
                                                            <i class="fa fa-tasks"></i> Tasks
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </li>
                @endif
                @if($user->can("view_payments") || $user->can("view_due_payments") || $user->can("view_due_payments")
                || $user->can("view_invoice") || $user->can("expense"))
                    <li class="menu-dropdown classic-menu-dropdown {{$paymentMenu ?? ''}} ">
                        <a href="javascript:;" ><i class="font-green fa {{ $gymSettings->currency->symbol }}"></i> Accounts  <i class="fa fa-angle-down hidden-xs hidden-sm"></i>
                            <span class="arrow"></span>
                        </a>
                        <ul class="dropdown-menu pull-left">
                            <li class="dropdown-submenu {{$account ?? ''}}">
                                <a href="{{ route('gym-admin.membership-payment.index') }}" class="nav-link  ">
                                    <i class="fa {{ $gymSettings->currency->symbol }}"></i> Payments
                                </a>
                                <ul class="dropdown-menu">
                                    @if($user->can("view_payments"))
                                        <li class="{{$showpaymentMenu ?? ''}}">
                                            <a href="{{ route('gym-admin.membership-payment.index') }}" class="nav-link ">
                                                <i class="fa {{ $gymSettings->currency->symbol }}"></i> Payments
                                            </a>
                                        </li>
                                    @endif
                                    @if($user->can("view_due_payments"))
                                        <li class="{{$paymentreminderMenu ?? ''}}">
                                            <a href="{{route('gym-admin.client-purchase.paymentreminder')}}" class="nav-link ">
                                                <i class="fa fa-bullhorn"></i> Due Payments
                                            </a>
                                        </li>
                                    @endif
                                    @if($user->can("view_due_payments"))
                                        <li class="{{$paymentreminderHistoryMenu ?? ''}}">
                                            <a href="{{route('gym-admin.client-purchase.reminder-history')}}" class="nav-link ">
                                                <i class="fa fa-list"></i> Payment Reminder History
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                            @if($user->can("view_invoice"))
                                <li class="{{$invoiceMenu ?? ''}}">
                                    <a href="{{route('gym-admin.gym-invoice.index')}}" class="nav-link  ">
                                        <i class="fa fa-file"></i> Invoice
                                    </a>
                                </li>
                            @endif
                            @if($user->can("expense"))
                                <li class="{{$expenseMenu ??''}}">
                                    <a href="{{ route('gym-admin.expense.index') }}" class="nav-link">
                                        <i class="fa fa-money"></i> Expenses
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if($user->can("view_target_report") || $user->can("view_client_report") || $user->can("view_booking_report")
                || $user->can("view_finance_report") || $user->can("view_attendance_report") || $user->can("view_enquiry_report")
                || $user->can("balance_report"))
                    <li class="menu-dropdown mega-menu-dropdown {{$reportMenu ?? ''}}  ">
                        <a href="javascript:;"><i class="font-green icon-notebook"></i> Reports <i class="fa fa-angle-down hidden-xs hidden-sm"></i>
                            <span class="arrow"></span>
                        </a>
                        <ul class="dropdown-menu pull-left">
                            @if($user->can("view_target_report"))
                                <li class="{{$targetreportMenu ?? ''}}">
                                    <a href="{{route('gym-admin.target-report.index')}}" class="nav-link  ">
                                        <i class="fa fa-bullseye"></i> Target Report
                                    </a>
                                </li>
                            @endif
                            @if($user->can("view_client_report"))
                                <li class="{{$clientreportMenu ?? ''}}">
                                    <a href="{{route('gym-admin.client-report.index')}}" class="nav-link  ">
                                        <i class="icon-users"></i> Clients Report
                                    </a>
                                </li>
                            @endif
                            @if($user->can("view_booking_report"))
                                <li class="{{$bookingreportMenu ?? ''}}">
                                    <a href="{{route('gym-admin.booking-report.index')}}" class="nav-link  ">
                                        <i class="icon-notebook"></i> Subscription Report
                                    </a>
                                </li>
                            @endif
                            @if($user->can("view_finance_report"))
                                <li class="{{$financialreportMenu ?? ''}}">
                                    <a href="{{route('gym-admin.finance-report.index')}}" class="nav-link  ">
                                        <i class="fa fa-money"></i> Financial Report
                                    </a>
                                </li>
                            @endif
                            @if($user->can("view_attendance_report"))
                                <li class="{{$attendancereportMenu ?? ''}}">
                                    <a href="{{route('gym-admin.attendance-report.index')}}" class="nav-link  ">
                                        <i class="fa fa-tasks"></i> Attendance Report
                                    </a>
                                </li>
                            @endif
                            @if($user->can("view_enquiry_report"))
                                <li class="{{$enquiryreportMenu ?? ''}}">
                                    <a href="{{route('gym-admin.enquiry-report.index')}}" class="nav-link  ">
                                        <i class="fa fa-question-circle"></i> Enquiry Report
                                    </a>
                                </li>
                            @endif
                            @if($user->can("balance_report"))
                                <li class="{{$balancereportMenu ?? ''}}">
                                    <a href="{{ route('gym-admin.balance-report.index') }}" class="nav-link  ">
                                        <i class="fa fa-balance-scale"></i> Balance Report
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if($user->can("view_previous_promotions"))
                    <li class="menu-dropdown classic-menu-dropdown  {{$promotionMenu ?? ''}} ">
                        <a href="javascript:;"><i class="font-green icon-paper-plane"></i> Promotions <i class="fa fa-angle-down hidden-xs hidden-sm"></i>
                            <span class="arrow"></span>
                        </a>
                        <ul class="dropdown-menu pull-left">
                            {{--<li class="{{$smscreditsMenu ?? ''}}">--}}
                                {{--<a href="{{route('gym-admin.credits.index')}}" class="nav-link  ">--}}
                                    {{--<i class="icon-basket"></i> Buy SMS Credits--}}
                                {{--</a>--}}
                            {{--</li>--}}
                            {{--<li class="{{$promotionindexMenu ?? ''}}">--}}
                                {{--<a href="{{route('gym-admin.promotion.index')}}" class="nav-link ">--}}
                                    {{--<i class="icon-envelope"></i> SMS Promotion </a>--}}
                            {{--</li>--}}
                            @if($user->can("view_previous_promotions"))
                                <li class="{{$promotionEmailMenu ?? ''}}">
                                    <a href="{{ route('gym-admin.email-promotion.index') }}" class="nav-link ">
                                        <i class="icon-paper-plane"></i> Email Promotion </a>
                                </li>
                            @endif
                            @if($user->can("view_previous_promotions"))
                                <li class="{{$promotionDbMenu ?? ''}}">
                                    <a href="{{ route('gym-admin.promotion-db.index') }}" class="nav-link ">
                                        <i class="fa fa-database"></i> Promotional Database </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if($user->can("view_software_updates"))
                    <li class="menu-dropdown mega-menu-dropdown {{$updatesMenu ?? ''}}  ">
                        <a href="{{route('gym-admin.upcoming.index')}}">
                            @if(!is_null($gymSwUpdates) &&  (\Carbon\Carbon::now('Asia/Calcutta')->diffInDays($gymSwUpdates->date, false) >= -3))
                                <i class="font-yellow-crusta fa fa-magic faa-tada animated"></i>
                            @else
                                <i class="font-green fa fa-magic"></i>
                            @endif
                            S/W Updates
                        </a>
                    </li>
                @endif
                @if($user->is_admin == 1)
                        <li class="menu-dropdown mega-menu-dropdown {{$indexSuperAdmin ?? ''}}  ">
                            <a href="{{ route('gym-admin.superadmin.index') }}">
                                <i class="font-green fa fa-cogs"></i>
                                Manage Branches
                            </a>
                        </li>
                @endif
            </ul>
        </div>
        <!-- END MEGA MENU -->
    </div>
</div>