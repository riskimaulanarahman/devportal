<div class="col-lg-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <h4 class="card-title mb-0">Tax Information</h4>
                <button type="button" class="btn btn-sm btn-secondary p-1" data-bs-toggle="modal" data-bs-target="#helpModalTax" title="Help">
                    <i class="fa fa-question-circle"></i>
                </button>
            </div>

            @if($isEditable)<button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createModalTax">Create</button>@endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            @if($isEditable)<th>Actions</th>@endif
                            <th>NPWP ID</th>
                            <th>Registered Date</th>
                            <th>NPWP Address</th>
                            <th>Married for Tax Purpose</th>
                            <th>Spouse Benefit</th>
                            <th>Number of Dependents</th>
                            <th>Jamsostek ID</th>
                            <th>BPJS ID</th>
                            <th>Benefit Class</th>
                            <th>Number of Dependents</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tax as $item)
                        <tr>
                            @if($isEditable)<td>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModalTax{{ $item->id }}">Edit</button>
                            </td>@endif
                            <td>{{ $item->npwp }}</td>
                            <td>{{ $item->registered_date }}</td>
                            <td>{{ $item->npwp_address }}</td>
                            <td>{{ $item->married_for_tax_purpose }}</td>
                            <td>{{ $item->spouse_benefit }}</td>
                            <td>{{ $item->number_of_dependents }}</td>
                            <td>{{ $item->jamsostek_id }}</td>
                            <td>{{ $item->bpjs_id }}</td>
                            <td>{{ $item->benefit_class }}</td>
                            <td>{{ $item->dependents_count }}</td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModalTax{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form id="editFormTax{{ $item->id }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Tax Information</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <h4>Tax Data</h4>
                                            <div class="mb-3">
                                                <label>NPWP ID</label>
                                                <input type="text" name="npwp" class="form-control" value="{{ $item->npwp }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Registered Date</label>
                                                <input type="date" name="registered_date" class="form-control" value="{{ $item->registered_date }}">
                                            </div>
                                            <div class="mb-3">
                                                <label>NPWP Address</label>
                                                <textarea name="npwp_address" class="form-control" required>{{ $item->npwp_address }}</textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label>Married for Tax Purpose</label>
                                                <select name="married_for_tax_purpose" class="form-control" required>
                                                    <option value="Yes" {{ $item->married_for_tax_purpose == 'Yes' ? 'selected' : '' }}>Yes</option>
                                                    <option value="No" {{ $item->married_for_tax_purpose == 'No' ? 'selected' : '' }}>No</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Spouse Benefit</label>
                                                <select name="spouse_benefit" class="form-control" required>
                                                    <option value="Yes" {{ $item->spouse_benefit == 'Yes' ? 'selected' : '' }}>Yes</option>
                                                    <option value="No" {{ $item->spouse_benefit == 'No' ? 'selected' : '' }}>No</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Number of Dependents</label>
                                                <input type="number" name="number_of_dependents" class="form-control" value="{{ $item->number_of_dependents }}" required>
                                            </div>
                                            <h4>Jamsostek</h4>
                                            <div class="mb-3">
                                                <label>Jamsostek ID</label>
                                                <input type="text" name="jamsostek_id" class="form-control" value="{{ $item->jamsostek_id }}">
                                            </div>
                                            <h4>BPJS</h4>
                                            <div class="mb-3">
                                                <label>BPJS ID</label>
                                                <input type="text" name="bpjs_id" class="form-control" value="{{ $item->bpjs_id }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Benefit Class</label>
                                                <input type="number" name="benefit_class" class="form-control" value="{{ $item->benefit_class }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Dependents Count</label>
                                                <input type="text" name="dependents_count" class="form-control" value="{{ $item->dependents_count }}" readonly>
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

<!-- Create Modal Tax -->
<div class="modal fade" id="createModalTax" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="createFormTax">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Tax Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h4>Tax Data</h4>
                    <div class="mb-3">
                        <label>NPWP ID</label>
                        <input type="text" name="npwp" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Registered Date</label>
                        <input type="date" name="registered_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>NPWP Address</label>
                        <textarea name="npwp_address" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Married for Tax Purpose</label>
                        <select name="married_for_tax_purpose" class="form-control" required>
                            <option value="">- Select -</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Spouse Benefit</label>
                        <select name="spouse_benefit" class="form-control" required>
                            <option value="">- Select -</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Number of Dependents</label>
                        <input type="number" name="number_of_dependents" class="form-control" required>
                    </div>
                    <h4>Jamsostek</h4>
                    <div class="mb-3">
                        <label>Jamsostek ID</label>
                        <input type="text" name="jamsostek_id" class="form-control">
                    </div>
                    <h4>BPJS</h4>
                    <div class="mb-3">
                        <label>BPJS ID</label>
                        <input type="text" name="bpjs_id" class="form-control" required>
                    </div>
                    {{-- <div class="mb-3">
                        <label>Benefit Class</label>
                        <input type="number" name="benefit_class" min="0" max="3" class="form-control" required>
                    </div> --}}
                    <div class="mb-3">
                        <label>Dependents Count</label>
                        <input type="text" name="dependents_count" class="form-control" value="Use Family Info" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Help Modal: Tax Information -->
<div class="modal fade" id="helpModalTax" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">How to Fill Out Tax Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Please provide accurate and complete tax-related data. Here's a sample and field explanation:</p>

                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>NPWP ID</th>
                            <th>Registered Date</th>
                            <th>Married for Tax?</th>
                            <th>Spouse Benefit</th>
                            <th>Dependents</th>
                            <th>Jamsostek ID</th>
                            <th>BPJS ID</th>
                            <th>Benefit Class</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>09.888.999.3-123.000</td>
                            <td>2020-01-01</td>
                            <td>Yes</td>
                            <td>No</td>
                            <td>2</td>
                            <td>JST123456789</td>
                            <td>BPJS99887766</td>
                            <td>2</td>
                        </tr>
                    </tbody>
                </table>

                <ul class="mt-3">
                    <li><strong>NPWP ID:</strong> Your registered Indonesian tax ID number (16 digits).</li>
                    <li><strong>Registered Date:</strong> The date when your NPWP was issued.</li>
                    <li><strong>NPWP Address:</strong> The address registered with the NPWP.</li>
                    <li><strong>Married for Tax Purpose:</strong> Select Yes if your marital status affects your tax calculation.</li>
                    <li><strong>Spouse Benefit:</strong> If applicable, select Yes to include your spouse in tax benefits.</li>
                    <li><strong>Number of Dependents:</strong> Total number of children or family members you claim for tax deduction.</li>
                    <li><strong>Jamsostek ID:</strong> Employment social security number, if applicable.</li>
                    <li><strong>BPJS ID & Benefit Class:</strong> Your national health insurance ID and classification.</li>
                    <li><strong>Dependents Count:</strong> This is auto-calculated and not editable.</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

