<div class="col-lg-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <h4 class="card-title mb-0">Communication</h4>
                <button type="button" class="btn btn-sm btn-secondary p-1" data-bs-toggle="modal" data-bs-target="#helpModalCommunication" title="Help">
                    <i class="fa fa-question-circle"></i>
                </button>
            </div>

            @if($isEditable)<button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createModalCommunication">Create</button>@endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            @if($isEditable)<th>Actions</th>@endif
                            <th>Type</th>
                            <th>Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($communication as $item)
                        <tr>
                            @if($isEditable)<td>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModalCommunication{{ $item->id }}">Edit</button>
                                <button class="btn btn-danger btn-sm" id="deleteFormCommunication" data-id="{{ $item->id }}">Delete</button>
                            </td>@endif
                            <td>{{ $item->type }}</td>
                            <td>{{ $item->number }}</td>
                        </tr>

                        <!-- Edit Modal Communication -->
                        <div class="modal fade" id="editModalCommunication{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form id="editFormCommunication{{ $item->id }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Communication</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>Type</label>
                                                <input type="text" name="type" class="form-control" value="{{ $item->type }}" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label>Number</label>
                                                <input type="text" name="number" class="form-control" value="{{ $item->number }}">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Save changes</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Create Modal Communication -->
<div class="modal fade" id="createModalCommunication" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="createFormCommunication">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Communication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Type</label>
                        <select class="form-control" id="type" name="type" required>
                            <option value="">- Select -</option>
                            <option value="Handphone">Handphone</option>
                            <option value="Fax">Fax</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Number</label>
                        <input type="text" name="number" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Help Modal: Communication -->
<div class="modal fade" id="helpModalCommunication" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Guide to Filling in Communication Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Please refer to the examples below when entering communication data:</p>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Mobile</td>
                            <td>+62 812-3456-7890</td>
                        </tr>
                        <tr>
                            <td>Work</td>
                            <td>+62 21-555-7890</td>
                        </tr>
                        <tr>
                            <td>Home</td>
                            <td>+62 24-123-4567</td>
                        </tr>
                        <tr>
                            <td>Fax</td>
                            <td>+62 21-999-8888</td>
                        </tr>
                    </tbody>
                </table>

                <p><strong>Notes:</strong></p>
                <ul>
                    <li><strong>Type:</strong> Should be one of: <code>Mobile</code>, <code>Work</code>, <code>Home</code>, or <code>Fax</code>.</li>
                    <li><strong>Number:</strong> Use international format with country code, e.g. <code>+62</code> for Indonesia.</li>
                    <li>Avoid symbols like <code>( )</code> or dashes in excess – keep it clean and standardized.</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
