<div class="col-lg-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <h4 class="card-title mb-0">Reference</h4>
                <button type="button" class="btn btn-sm btn-secondary p-1" data-bs-toggle="modal" data-bs-target="#helpModalReference" title="Help">
                    <i class="fa fa-question-circle"></i>
                </button>
            </div>

            @if($isEditable)<button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createModalReference">Create</button>@endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>Relation</th>
                            <th>Name</th>
                            <th>Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reference as $item)
                        <tr>
                            @if($isEditable)<td>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModalReference{{ $item->id }}">Edit</button>
                                <button class="btn btn-danger btn-sm" id="deleteFormReference" data-id="{{ $item->id }}">Delete</button>
                            </td>@endif
                            <td>{{ $item->relation }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->number }}</td>
                        </tr>

                        <!-- Edit Modal Reference -->
                        <div class="modal fade" id="editModalReference{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form id="editFormReference{{ $item->id }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Reference</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>Relation</label>
                                                <input type="text" name="relation" class="form-control" value="{{ $item->relation }}">
                                            </div>
                                            <div class="mb-3">
                                                <label>Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $item->name }}">
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
<!-- Create Modal Reference -->
<div class="modal fade" id="createModalReference" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="createFormReference">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Reference</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Relation</label>
                        <input type="text" name="relation" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
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

<!-- Help Modal: Reference -->
<div class="modal fade" id="helpModalReference" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Guide to Filling in Reference Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Use this section to list people who can provide a reference about you. Below are examples of how to fill in each field:</p>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Relation</th>
                            <th>Name</th>
                            <th>Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Former Manager</td>
                            <td>Michael Tan</td>
                            <td>+62 812-1234-5678</td>
                        </tr>
                        <tr>
                            <td>Colleague</td>
                            <td>Ayu Kartika</td>
                            <td>+62 821-9876-5432</td>
                        </tr>
                        <tr>
                            <td>Mentor</td>
                            <td>John Doe</td>
                            <td>+1 415-555-9876</td>
                        </tr>
                    </tbody>
                </table>

                <p><strong>Notes:</strong></p>
                <ul>
                    <li><strong>Relation:</strong> Describe how you know this person (e.g., Former Manager, Lecturer, Colleague).</li>
                    <li><strong>Name:</strong> Full name of the reference person.</li>
                    <li><strong>Number:</strong> Phone number with country code. Example: <code>+62 812-xxxx-xxxx</code>.</li>
                    <li>Make sure the contact is aware and willing to give a reference if contacted.</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
