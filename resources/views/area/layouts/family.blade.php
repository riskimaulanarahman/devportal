<div class="col-lg-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <h4 class="card-title mb-0">Family</h4>
                <button type="button" class="btn btn-sm btn-secondary p-1" data-bs-toggle="modal" data-bs-target="#helpModalFamily" title="Help">
                    <i class="fa fa-question-circle"></i>
                </button>
            </div>

            @if($isEditable)<button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createModalFamily">Create</button>@endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            @if($isEditable)<th>Actions</th>@endif
                            <th>Relation</th>
                            <th>Name</th>
                            <th>Gender (M/F)</th>
                            <th>Birthplace</th>
                            <th>Date of Birth</th>
                            <th>Country of Birth</th>
                            <th>Nationality</th>
                            <th>Job Title</th>
                            <th>Employer</th>
                            <th>Employer Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($family as $item)
                        <tr>
                            @if($isEditable)<td>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModalFamily{{ $item->id }}">Edit</button>
                                <button class="btn btn-danger btn-sm" id="deleteFormFamily" data-id="{{ $item->id }}">Delete</button>
                            </td>@endif
                            <td>{{ $item->members }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->gender }}</td>
                            <td>{{ $item->birth_place }}</td>
                            <td>{{ date_format(date_create($item->date_of_birth),'d-m-Y') }}</td>
                            <td>{{ $item->country_of_birth }}</td>
                            <td>{{ $item->nationality }}</td>
                            <td>{{ $item->job_title }}</td>
                            <td>{{ $item->employer }}</td>
                            <td>{{ $item->employer_type }}</td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModalFamily{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form id="editFormFamily{{ $item->id }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Family</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>Relation</label>
                                                <input type="text" name="members" class="form-control" value="{{ $item->members }}" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label>Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $item->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Gender (M/F)</label>
                                                <select class="form-control" id="gender" name="gender" required>
                                                    <option value="">- Select -</option>
                                                    <option value="M" {{ $item->gender == 'M' ? 'selected' : '' }}>Male</option>
                                                    <option value="F" {{ $item->gender == 'F' ? 'selected' : '' }}>Female</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Birthplace</label>
                                                <input type="text" name="birth_place" class="form-control" value="{{ $item->birth_place }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Date of Birth</label>
                                                <input type="date" name="date_of_birth" class="form-control" value="{{ $item->date_of_birth }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Country of Birth</label>
                                                <select class="form-control" id="country_of_birth" name="country_of_birth" required>
                                                    <option value="">- Select -</option>
                                                    <option value="Indonesia" {{ $personalInfo->country_of_birth == 'Indonesia' ? 'selected' : '' }}>Indonesia</option>
                                                    <option value="United States" {{ $personalInfo->country_of_birth == 'United States' ? 'selected' : '' }}>United States</option>
                                                    <option value="United Kingdom" {{ $personalInfo->country_of_birth == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                                                    <option value="Canada" {{ $personalInfo->country_of_birth == 'Canada' ? 'selected' : '' }}>Canada</option>
                                                    <option value="Australia" {{ $personalInfo->country_of_birth == 'Australia' ? 'selected' : '' }}>Australia</option>
                                                    <option value="Germany" {{ $personalInfo->country_of_birth == 'Germany' ? 'selected' : '' }}>Germany</option>
                                                    <option value="France" {{ $personalInfo->country_of_birth == 'France' ? 'selected' : '' }}>France</option>
                                                    <option value="Japan" {{ $personalInfo->country_of_birth == 'Japan' ? 'selected' : '' }}>Japan</option>
                                                    <option value="China" {{ $personalInfo->country_of_birth == 'China' ? 'selected' : '' }}>China</option>
                                                    <option value="India" {{ $personalInfo->country_of_birth == 'India' ? 'selected' : '' }}>India</option>
                                                    <option value="Brazil" {{ $personalInfo->country_of_birth == 'Brazil' ? 'selected' : '' }}>Brazil</option>
                                                    <option value="South Africa" {{ $personalInfo->country_of_birth == 'South Africa' ? 'selected' : '' }}>South Africa</option>
                                                    <option value="others" {{ $personalInfo->country_of_birth == 'others' ? 'selected' : '' }}>Others</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Nationality</label>
                                                <select class="form-control" id="nationality" name="nationality" required>
                                                    <option value="">- Select -</option>
                                                    <option value="Indonesia" {{ $personalInfo->nationality == 'Indonesia' ? 'selected' : '' }}>Indonesia</option>
                                                    <option value="United States" {{ $personalInfo->nationality == 'United States' ? 'selected' : '' }}>United States</option>
                                                    <option value="United Kingdom" {{ $personalInfo->nationality == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                                                    <option value="Canada" {{ $personalInfo->nationality == 'Canada' ? 'selected' : '' }}>Canada</option>
                                                    <option value="Australia" {{ $personalInfo->nationality == 'Australia' ? 'selected' : '' }}>Australia</option>
                                                    <option value="Germany" {{ $personalInfo->nationality == 'Germany' ? 'selected' : '' }}>Germany</option>
                                                    <option value="France" {{ $personalInfo->nationality == 'France' ? 'selected' : '' }}>France</option>
                                                    <option value="Japan" {{ $personalInfo->nationality == 'Japan' ? 'selected' : '' }}>Japan</option>
                                                    <option value="China" {{ $personalInfo->nationality == 'China' ? 'selected' : '' }}>China</option>
                                                    <option value="India" {{ $personalInfo->nationality == 'India' ? 'selected' : '' }}>India</option>
                                                    <option value="Brazil" {{ $personalInfo->nationality == 'Brazil' ? 'selected' : '' }}>Brazil</option>
                                                    <option value="South Africa" {{ $personalInfo->nationality == 'South Africa' ? 'selected' : '' }}>South Africa</option>
                                                    <option value="others" {{ $personalInfo->nationality == 'others' ? 'selected' : '' }}>Others</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Job Title</label>
                                                <input type="text" name="job_title" class="form-control" value="{{ $item->job_title }}">
                                            </div>
                                            <div class="mb-3">
                                                <label>Employer</label>
                                                <input type="text" name="employer" class="form-control" value="{{ $item->employer }}">
                                            </div>
                                            <div class="mb-3">
                                                <label>Employer Type</label>
                                                <input type="text" name="employer_type" class="form-control" value="{{ $item->employer_type }}" placeholder="Public Sector, Healthcare Providers, Military and Defense">
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
<!-- Create Modal Family -->
<div class="modal fade" id="createModalFamily" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="createFormFamily">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Family Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Relation</label>
                        <select name="members" class="form-control" required>
                            <option value="">- Select -</option>
                            <option value="Father">Father</option>
                            <option value="Mother">Mother</option>
                            <option value="Spouse">Spouse</option>
                            <option value="Child">Child</option>
                            <option value="Sibling">Sibling</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Gender (M/F)</label>
                        <select name="gender" class="form-control" required>
                            <option value="">- Select -</option>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Birthplace</label>
                        <input type="text" name="birth_place" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Country of Birth</label>
                        <select class="form-control" id="country_of_birth" name="country_of_birth" required>
                            <option value="">- Select -</option>
                            <option value="Indonesia" selected>Indonesia</option>
                            <option value="United States">United States</option>
                            <option value="United Kingdom">United Kingdom</option>
                            <option value="Canada">Canada</option>
                            <option value="Australia">Australia</option>
                            <option value="Germany">Germany</option>
                            <option value="France">France</option>
                            <option value="Japan">Japan</option>
                            <option value="China">China</option>
                            <option value="India">India</option>
                            <option value="Brazil">Brazil</option>
                            <option value="South Africa">South Africa</option>
                            <option value="others">Others</option>
                            <!-- Add more countries as needed -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Nationality</label>
                        <select class="form-control" id="nationality" name="nationality" required>
                            <option value="">- Select -</option>
                            <option value="Indonesia" selected>Indonesia</option>
                            <option value="United States">United States</option>
                            <option value="United Kingdom">United Kingdom</option>
                            <option value="Canada">Canada</option>
                            <option value="Australia">Australia</option>
                            <option value="Germany">Germany</option>
                            <option value="France">France</option>
                            <option value="Japan">Japan</option>
                            <option value="China">China</option>
                            <option value="India">India</option>
                            <option value="Brazil">Brazil</option>
                            <option value="South Africa">South Africa</option>
                            <option value="others">Others</option>
                            <!-- Add more countries as needed -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Business Sector</label>
                        <input type="text" name="employer_type" class="form-control" placeholder="Public Sector, Healthcare Providers, Military and Defense">
                    </div>
                    <div class="mb-3">
                        <label>Company</label>
                        <input type="text" name="employer" class="form-control">
                    </div>
                    <div class="mb-3">
                    <label>Job Title</label>
                        <input type="text" name="job_title" class="form-control">
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
<div class="modal fade" id="helpModalFamily" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Guide to Filling in Family Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Please refer to the example below when filling out family data:</p>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Relation</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Birthplace</th>
                            <th>Date of Birth</th>
                            <th>Country of Birth</th>
                            <th>Nationality</th>
                            <th>Job Title</th>
                            <th>Employer</th>
                            <th>Employer Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Spouse</td>
                            <td>Jane Doe</td>
                            <td>F</td>
                            <td>Jakarta</td>
                            <td>12-08-1990</td>
                            <td>Indonesia</td>
                            <td>Indonesia</td>
                            <td>Teacher</td>
                            <td>Green School</td>
                            <td>Education</td>
                        </tr>
                        <tr>
                            <td>Child</td>
                            <td>Michael Doe</td>
                            <td>M</td>
                            <td>Bandung</td>
                            <td>05-05-2015</td>
                            <td>Indonesia</td>
                            <td>Indonesia</td>
                            <td>Student</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
                <p><strong>Tips:</strong></p>
                <ul>
                    <li><strong>Gender:</strong> Use <code>M</code> for Male, <code>F</code> for Female.</li>
                    <li><strong>Employer Type:</strong> Examples include: Public Sector, Private Company, Self-employed, Education, Healthcare, Military, etc.</li>
                    <li>Leave <em>Job Title</em> or <em>Employer</em> blank if the family member is a child or not working.</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
