<div class="btn-group">
    <button class="btn blue btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
        <i class="fa fa-gears"></i> 
        <span class="hidden-xs">Action</span>
        <i class="fa fa-angle-down"></i>
    </button>
    <ul class="dropdown-menu pull-right" role="menu">
        <li>
            <a href="{{ $editRoute }}">
                <i class="fa fa-edit"></i> Edit
            </a>
        </li>
        <li>
            <a href="javascript:;" data-user-id="{{ $merchant->id }}" class="assign-role">
                <i class="fa fa-pencil"></i> Assign Role
            </a>
        </li>
        <li>
            <a href="javascript:;" data-user-id="{{ $merchant->id }}" class="remove-user">
                <i class="fa fa-trash"></i> Delete User
            </a>
        </li>
    </ul>
</div>