<div class="col-lg-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <h4 class="card-title mb-0">Language</h4>
                <button type="button" class="btn btn-sm btn-secondary p-1" data-bs-toggle="modal" data-bs-target="#helpModalLanguage" title="Help">
                    <i class="fa fa-question-circle"></i>
                </button>
            </div>

            @if($isEditable)<button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createModalLanguage">Create</button>@endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            @if($isEditable)<th>Actions</th>@endif
                            <th>Language</th>
                            <th>Read</th>
                            <th>Write</th>
                            <th>Speak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($language as $item)
                        <tr>
                            @if($isEditable)<td>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModalLanguage{{ $item->id }}">Edit</button>
                                <button class="btn btn-danger btn-sm" id="deleteFormLanguage" data-id="{{ $item->id }}">Delete</button>
                            </td>@endif
                            <td>{{ $item->language }}</td>
                            <td>{{ $item->read }}</td>
                            <td>{{ $item->write }}</td>
                            <td>{{ $item->speak }}</td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModalLanguage{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form id="editFormLanguage{{ $item->id }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Language</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>Language</label>
                                                <input type="text" name="language" class="form-control" value="{{ $item->language }}" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label>Read</label>
                                                <select name="read" class="form-control" required>
                                                    <option value="Unable" {{ $item->read == 'Unable' ? 'selected' : '' }}>Unable</option>
                                                    <option value="Limited" {{ $item->read == 'Limited' ? 'selected' : '' }}>Limited</option>
                                                    <option value="Good" {{ $item->read == 'Good' ? 'selected' : '' }}>Good</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Write</label>
                                                <select name="write" class="form-control" required>
                                                    <option value="Unable" {{ $item->write == 'Unable' ? 'selected' : '' }}>Unable</option>
                                                    <option value="Limited" {{ $item->write == 'Limited' ? 'selected' : '' }}>Limited</option>
                                                    <option value="Good" {{ $item->write == 'Good' ? 'selected' : '' }}>Good</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Speak</label>
                                                <select name="speak" class="form-control" required>
                                                    <option value="Unable" {{ $item->speak == 'Unable' ? 'selected' : '' }}>Unable</option>
                                                    <option value="Limited" {{ $item->speak == 'Limited' ? 'selected' : '' }}>Limited</option>
                                                    <option value="Good" {{ $item->speak == 'Good' ? 'selected' : '' }}>Good</option>
                                                </select>
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

<!-- Create Modal -->
<div class="modal fade" id="createModalLanguage" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="createFormLanguage">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Language</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Language</label>
                        <select class="form-control" id="language" name="language" required>
                            <option value="">- Select -</option>
                            <option value="Indonesia">Indonesia</option>
                            <option value="English">English</option>
                            <option value="Mandarin">Mandarin</option>
                            <!-- Add more countries as needed -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Read</label>
                        <select name="read" class="form-control" required>
                            <option value="">- Select -</option>
                            <option value="Unable">Unable</option>
                            <option value="Limited">Limited</option>
                            <option value="Good">Good</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Write</label>
                        <select name="write" class="form-control" required>
                            <option value="">- Select -</option>
                            <option value="Unable">Unable</option>
                            <option value="Limited">Limited</option>
                            <option value="Good">Good</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Speak</label>
                        <select name="speak" class="form-control" required>
                            <option value="">- Select -</option>
                            <option value="Unable">Unable</option>
                            <option value="Limited">Limited</option>
                            <option value="Good">Good</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Help Modal: Language -->
<div class="modal fade" id="helpModalLanguage" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">How to Fill Out Language Proficiency</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Use this section to list the languages you know and your proficiency in reading, writing, and speaking them. Example:</p>

                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Language</th>
                            <th>Read</th>
                            <th>Write</th>
                            <th>Speak</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>English</td>
                            <td>Good</td>
                            <td>Good</td>
                            <td>Limited</td>
                        </tr>
                        <tr>
                            <td>Mandarin</td>
                            <td>Limited</td>
                            <td>Unable</td>
                            <td>Good</td>
                        </tr>
                    </tbody>
                </table>

                <p><strong>Proficiency Levels:</strong></p>
                <ul>
                    <li><strong>Unable</strong> – You do not have any skill in this area.</li>
                    <li><strong>Limited</strong> – You have basic knowledge or beginner-level skills.</li>
                    <li><strong>Good</strong> – You are confident and fluent enough to use the language in everyday or work-related settings.</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
