<div class="col-lg-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <h4 class="card-title mb-0">Documents</h4>
                <button type="button" class="btn btn-sm btn-secondary p-1" data-bs-toggle="modal" data-bs-target="#helpModalDocument" title="Help">
                    <i class="fa fa-question-circle"></i>
                </button>
            </div>

            @if($isEditable)<button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createModalDocument">Create</button>@endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Type Document</th>
                            <th>File</th>
                            @if($isEditable)<th>Actions</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($document as $doc)
                        <tr>
                            <td>{{ $doc->typeDocument->name }}</td>
                            <td>
                                @if($doc->path)
                                {{-- <a href="{{ asset('upload/'.$doc->path) }}" class="" target="_blank">
                                    <img src="{{ url('public/assets/images/showfile.png') }}" alt="doc" height="40" width="50">
                                </a> --}}
                                <a href="{{ route('document.download', $doc->id) }}" class="" target="_blank">
                                    {{-- <img src="{{ asset('assets/images/showfile.png') }}" alt="doc" height="40" width="50"> --}}
                                    <button class="btn btn-primary"><i class="fa fa-download"></i></button>
                                </a>
                                @else
                                <span class="text-danger">No file uploaded</span>
                                @endif
                            </td>
                            @if($isEditable)<td>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModalDocument{{ $doc->id }}">Edit</button>
                                <button class="btn btn-danger btn-sm" id="deleteFormDocument" data-id="{{ $doc->id }}">Delete</button>
                            </td>@endif
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModalDocument{{ $doc->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form id="editFormDocument{{ $doc->id }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Document</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>Type Document</label>
                                                <select name="type_document_id" class="form-control" required>
                                                    @foreach($refTypeDocuments as $type)
                                                    <option value="{{ $type->id }}" @if($type->id == $doc->type_document_id) selected @endif>
                                                        {{ $type->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Replace File</label>
                                                <input type="file" name="document" class="form-control">
                                                @if($doc->path)
                                                <small class="text-muted">Current file: <a href="{{ asset('upload/'.$doc->path) }}" target="_blank">{{ $doc->path }}</a></small>
                                                @endif
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

<!-- Create Modal Document -->
<div class="modal fade" id="createModalDocument" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="createFormDocument">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Type Document</label>
                        <select name="type_document_id" class="form-control" required>
                            <option value="">- Select -</option>
                            @foreach($refTypeDocuments as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Upload File</label>
                        <input type="file" name="document" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Help Modal: Document Upload -->
<div class="modal fade" id="helpModalDocument" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">How to Upload and Manage Documents</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Please upload documents relevant to your profile. Each document type should have only one file. If you upload a new file, it will replace the existing one.</p>

                <p><strong>Example:</strong></p>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Type Document</th>
                            <th>File</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>KTP</td>
                            <td><i>Uploaded</i></td>
                        </tr>
                        <tr>
                            <td>CV</td>
                            <td><i>Uploaded</i></td>
                        </tr>
                    </tbody>
                </table>

                <p><strong>Guidelines:</strong></p>
                <ul>
                    <li><strong>Type Document:</strong> Select the document type from the dropdown menu.</li>
                    <li><strong>File Format:</strong> Only PDF, JPG, JPEG, and PNG files are allowed.</li>
                    <li><strong>File Size:</strong> Maximum file size allowed is 2MB.</li>
                    <li><strong>Download:</strong> Click the download icon to view or save the file.</li>
                    <li><strong>Replace:</strong> To update a document, choose a new file and save changes.</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

