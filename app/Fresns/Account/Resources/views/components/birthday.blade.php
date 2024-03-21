<div class="modal fade" id="editBirthdayModal" tabindex="-1" aria-labelledby="editBirthdayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form class="api-request-form" action="{{ route('account-center.api.update') }}" method="patch" autocomplete="off">
                <input type="hidden" name="formType" value="birthday">

                <div class="modal-body">
                    <div class="input-group">
                        <span class="input-group-text">{{ $fsLang['userBirthday'] }}</span>
                        <input type="date" class="form-control" name="birthday" value="{{ $birthday }}" min="1920-01-01" max="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary me-auto" data-bs-dismiss="modal">{{ $fsLang['close'] }}</button>
                    <button type="submit" class="btn btn-primary">{{ $fsLang['saveChanges'] }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
