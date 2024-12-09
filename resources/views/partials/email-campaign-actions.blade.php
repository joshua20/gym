@if($row->status == 'sent')
    <a class='btn green btn-xs' href="{{ route('gym-admin.email-promotion.edit-campaign', $row->id) }}">
        <i class="fa fa-eye"></i> View Campaign
    </a>
@else
    <a class='btn blue btn-xs' href="{{ route('gym-admin.email-promotion.edit-campaign', $row->id) }}">
        <i class="fa fa-edit"></i> Edit Campaign
    </a>
@endif