<div class="col-lg-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <h4 class="card-title mb-0">Social Media</h4>
                <button type="button" class="btn btn-sm btn-secondary p-1" data-bs-toggle="modal" data-bs-target="#helpModalSosmed" title="Help">
                    <i class="fa fa-question-circle"></i>
                </button>
            </div>

            @if($isEditable)
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createModalSosmed">Create</button>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            @if($isEditable)
                                <th>Actions</th>
                            @endif
                            <th>Platform</th>
                            <th>Username</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($socialMedia as $item)
                            <tr>
                                @if($isEditable)
                                    <td>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModalSosmed{{ $item->id }}">Edit</button>
                                        <button class="btn btn-danger btn-sm" id="deleteFormSosmed" data-id="{{ $item->id }}">Delete</button>
                                    </td>
                                @endif

                                <td>{{ $item->platform }}</td>
                                <td>{{ $item->username }}</td>
                            </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModalSosmed{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form id="editFormSosmed{{ $item->id }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Social Media</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>Platform</label>
                                                <input type="text" name="platform" class="form-control" value="{{ $item->platform }}" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label>Username</label>
                                                <input type="text" name="username" class="form-control" value="{{ $item->username }}" required>
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
<!-- Create Modal Sosmed-->
<div class="modal fade" id="createModalSosmed" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="createFormSosmed">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Social Media</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Platform</label>
                        <select class="form-control" id="platform" name="platform" required>
                            <option value="">- Select -</option>
                            <option value="Linkedin">Linkedin</option>
                            <option value="Instagram">Instagram</option>
                            <option value="Facebook">Facebook</option>
                            <option value="Github">Github</option>
                            <option value="xTwitter">xTwitter</option>
                            <!-- Add more countries as needed -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Help Modal -->
<div class="modal fade" id="helpModalSosmed" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Guide to Filling in Social Media Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Please refer to the following examples when entering your data:</p>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Platform</th>
                            <th>Username</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Instagram</td>
                            <td>@john_doe</td>
                        </tr>
                        <tr>
                            <td>LinkedIn</td>
                            <td>john-doe</td>
                        </tr>
                        <tr>
                            <td>Facebook</td>
                            <td>john.doe.fb</td>
                        </tr>
                    </tbody>
                </table>
                <p><strong>Note:</strong> Make sure the username matches your actual username on the selected platform.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>