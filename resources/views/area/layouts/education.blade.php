<div class="col-lg-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <h4 class="card-title mb-0">Education</h4>
                <button type="button" class="btn btn-sm btn-secondary p-1" data-bs-toggle="modal" data-bs-target="#helpModalEducation" title="Help">
                    <i class="fa fa-question-circle"></i>
                </button>
            </div>

            @if($isEditable)<button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createModalEducation">Create</button>@endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            @if($isEditable)<th>Actions</th>@endif
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Education Establishment</th>
                            <th>Institute / Location</th>
                            <th>Country</th>
                            <th>Certificate</th>
                            <th>Duration Year/Month/Day</th>
                            <th>Branch of Study 1 (Major)</th>
                            <th>Branch of Study (Major/Minor)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($education as $item)
                        <tr>
                            @if($isEditable)<td>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModalEducation{{ $item->id }}">Edit</button>
                                <button class="btn btn-danger btn-sm" id="deleteFormEducation" data-id="{{ $item->id }}">Delete</button>
                            </td>@endif
                            <td>{{ $item->start_date }}</td>
                            <td>{{ $item->end_date }}</td>
                            <td>{{ $item->education_establishment }}</td>
                            <td>{{ $item->institute_location }}</td>
                            <td>{{ $item->country }}</td>
                            <td>{{ $item->certificate }}</td>
                            <td>{{ $item->duration }}</td>
                            <td>{{ $item->branch_of_study_major }}</td>
                            <td>{{ $item->branch_of_study_minor }}</td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModalEducation{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form id="editFormEducation{{ $item->id }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Education</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>Start Date</label>
                                                <input type="date" name="start_date" class="form-control" value="{{ $item->start_date }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>End Date</label>
                                                <input type="date" name="end_date" class="form-control" value="{{ $item->end_date }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Education Establishment</label>
                                                <input type="text" name="education_establishment" class="form-control" value="{{ $item->education_establishment }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Institute / Location</label>
                                                <input type="text" name="institute_location" class="form-control" value="{{ $item->institute_location }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Country</label>
                                                <select class="form-control" id="country" name="country" required>
                                                    <option value="">- Select -</option>
                                                    <option value="Indonesia" {{ $item->country == 'Indonesia' ? 'selected' : '' }}>Indonesia</option>
                                                    <option value="United States" {{ $item->country == 'United States' ? 'selected' : '' }}>United States</option>
                                                    <option value="United Kingdom" {{ $item->country == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                                                    <option value="Canada" {{ $item->country == 'Canada' ? 'selected' : '' }}>Canada</option>
                                                    <option value="Australia" {{ $item->country == 'Australia' ? 'selected' : '' }}>Australia</option>
                                                    <option value="Germany" {{ $item->country == 'Germany' ? 'selected' : '' }}>Germany</option>
                                                    <option value="France" {{ $item->country == 'France' ? 'selected' : '' }}>France</option>
                                                    <option value="Japan" {{ $item->country == 'Japan' ? 'selected' : '' }}>Japan</option>
                                                    <option value="China" {{ $item->country == 'China' ? 'selected' : '' }}>China</option>
                                                    <option value="India" {{ $item->country == 'India' ? 'selected' : '' }}>India</option>
                                                    <option value="Brazil" {{ $item->country == 'Brazil' ? 'selected' : '' }}>Brazil</option>
                                                    <option value="South Africa" {{ $item->country == 'South Africa' ? 'selected' : '' }}>South Africa</option>
                                                    <option value="others" {{ $item->country == 'others' ? 'selected' : '' }}>Others</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Certificate</label>
                                                <select name="certificate" class="form-control" required>
                                                    <option value="">- Select Certificate -</option>
                                                    <option value="Middle School" {{ $item->certificate == 'Middle School' ? 'selected' : '' }}>Middle School (SMP)</option>
                                                    <option value="High School" {{ $item->certificate == 'High School' ? 'selected' : '' }}>High School (SMA)</option>
                                                    <option value="Bachelor" {{ $item->certificate == 'Bachelor' ? 'selected' : '' }}>Bachelor</option>
                                                    <option value="Master" {{ $item->certificate == 'Master' ? 'selected' : '' }}>Master</option>
                                                    <option value="PhD" {{ $item->certificate == 'PhD' ? 'selected' : '' }}>PhD</option>
                                                    <option value="CPA" {{ $item->certificate == 'CPA' ? 'selected' : '' }}>CPA</option>
                                                    <option value="CFA" {{ $item->certificate == 'CFA' ? 'selected' : '' }}>CFA</option>
                                                    <option value="Other" {{ $item->certificate == 'Other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Duration</label>
                                                <input type="number" min="0" name="duration" class="form-control" value="{{ $item->duration }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Branch of Study 1 (Major)</label>
                                                <input type="text" name="branch_of_study_major" class="form-control" value="{{ $item->branch_of_study_major }}">
                                            </div>
                                            <div class="mb-3">
                                                <label>Branch of Study (Major/Minor)</label>
                                                <input type="text" name="branch_of_study_minor" class="form-control" value="{{ $item->branch_of_study_minor }}">
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

<!-- Create Modal Education -->
<div class="modal fade" id="createModalEducation" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="createFormEducation">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Education</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Education Establishment</label>
                        <input type="text" name="education_establishment" class="form-control" placeholder="e.g IHM University" required>
                    </div>
                    <div class="mb-3">
                        <label>Institute / Location</label>
                        <input type="text" name="institute_location" class="form-control" placeholder="Nusantara" required>
                    </div>
                    <div class="mb-3">
                        <label>Country</label>
                        <select class="form-control" id="country" name="country" required>
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
                        <label>Certificate</label>
                        <select name="certificate" class="form-control" required>
                            <option value="">- Select Certificate -</option>
                            <option value="Middle School">Middle School (SMP)</option>
                            <option value="High School">High School (SMA)</option>
                            <option value="Bachelor">Bachelor</option>
                            <option value="Master">Master</option>
                            <option value="PhD">PhD</option>
                            <option value="CPA">CPA</option>
                            <option value="CFA">CFA</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Duration (Years)</label>
                        <input type="number" min="0" name="duration" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Branch of Study 1 (Major)</label>
                        <input type="text" name="branch_of_study_major" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Branch of Study (Major/Minor)</label>
                        <input type="text" name="branch_of_study_minor" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Help Modal: Education -->
<div class="modal fade" id="helpModalEducation" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">How to Fill Education Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Please use this section to input your formal education history. Below is a sample:</p>

                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Start</th>
                            <th>End</th>
                            <th>Education Establishment</th>
                            <th>Institute / Location</th>
                            <th>Country</th>
                            <th>Certificate</th>
                            <th>Duration (Years)</th>
                            <th>Major</th>
                            <th>Major/Minor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2016-08-01</td>
                            <td>2020-06-15</td>
                            <td>University</td>
                            <td>Universitas Indonesia / Depok</td>
                            <td>Indonesia</td>
                            <td>Bachelor</td>
                            <td>3</td>
                            <td>Computer Science</td>
                            <td>CS / Mathematics</td>
                        </tr>
                        <tr>
                            <td>2020-09-01</td>
                            <td>2022-07-30</td>
                            <td>Graduate School</td>
                            <td>MIT / Cambridge</td>
                            <td>United States</td>
                            <td>Master</td>
                            <td>2</td>
                            <td>AI Engineering</td>
                            <td>AI / Robotics</td>
                        </tr>
                    </tbody>
                </table>

                <p><strong>Notes:</strong></p>
                <ul>
                    <li><strong>Education Establishment:</strong> e.g., "University", "High School", "Graduate School".</li>
                    <li><strong>Certificate:</strong> Choose from official degrees (e.g., Bachelor, Master, PhD, CPA, CFA).</li>
                    <li><strong>Duration:</strong> Use consistent format such as number of years, or year-month-day format.</li>
                    <li><strong>Branch of Study:</strong> Mention field of study clearly, such as "Information Systems", "Accounting", or "Physics / Mathematics".</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
