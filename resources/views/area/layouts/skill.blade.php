<div class="col-lg-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <h4 class="card-title mb-0">Skill</h4>
                <button type="button" class="btn btn-sm btn-secondary p-1" data-bs-toggle="modal" data-bs-target="#helpModalSkill" title="Help">
                    <i class="fa fa-question-circle"></i>
                </button>
            </div>

            @if($isEditable)<button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createModalSkill">Create</button>@endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            @if($isEditable)<th>Actions</th>@endif
                            <th>Skill</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($skill as $item)
                        <tr>
                            @if($isEditable)<td>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModalSkill{{ $item->id }}">Edit</button>
                                <button class="btn btn-danger btn-sm" id="deleteFormSkill" data-id="{{ $item->id }}">Delete</button>
                            </td>@endif
                            <td>{{ $item->skill }}</td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModalSkill{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form id="editFormSkill{{ $item->id }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Skill</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>Skill</label>
                                                <input type="text" name="skill" class="form-control" value="{{ $item->skill }}" required>
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

<!-- Create Modal Skill-->
<div class="modal fade" id="createModalSkill" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="createFormSkill">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Skill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Skill</label>
                        <input type="text" name="skill" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Help Modal: Skill -->
<div class="modal fade" id="helpModalSkill" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">How to Fill Out Skills</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>This section is for listing your job-related or technical skills. Examples include:</p>
                <ul>
                    <li>Microsoft Excel</li>
                    <li>Adobe Photoshop</li>
                    <li>Laravel / PHP Development</li>
                    <li>Project Management</li>
                    <li>Customer Service</li>
                    <li>AutoCAD Design</li>
                </ul>
                <p><strong>Tips:</strong></p>
                <ul>
                    <li>Only include skills relevant to the position or industry.</li>
                    <li>Be specific — for example, use “Adobe Illustrator” instead of just “Design”.</li>
                    <li>Don't duplicate — one entry per unique skill.</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

