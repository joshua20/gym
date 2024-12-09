<div class="md-checkbox">
    <input type="checkbox" 
           id="checkbox{{ $row->id }}" 
           checked 
           name="userIds[]" 
           value="{{ $row->id }}" 
           class="md-check">
    <label for="checkbox{{ $row->id }}">
        <span></span>
        <span class="check"></span>
        <span class="box"></span>
    </label>
</div>