<div class="btn-group">
    <button class="btn blue btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
        <i class="fa fa-gears"></i> 
        <span class="hidden-xs">Actions</span>
        <i class="fa fa-angle-down"></i>
    </button>
    <ul class="dropdown-menu pull-right" role="menu">
        <li>
            <a href="{{ route('gym-admin.superadmin.edit', $row->id) }}"> 
                <i class="fa fa-edit"></i>Edit Branch
            </a>
        </li>
        <li>
            <a href="javascript:;" onclick="deleteModal({{ $row->id }})"> 
                <i class="fa fa-trash"></i>Delete Branch
            </a>
        </li>
    </ul>
</div>