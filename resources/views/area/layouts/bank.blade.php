<div class="col-lg-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <h4 class="card-title mb-0">Bank Details</h4>
                <button type="button" class="btn btn-sm btn-secondary p-1" data-bs-toggle="modal" data-bs-target="#helpModalBank" title="Help">
                    <i class="fa fa-question-circle"></i>
                </button>
            </div>

            @if($isEditable)<button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createModalBank">Create</button>@endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            @if($isEditable)<th>Actions</th>@endif
                            <th>Full Name</th>
                            <th>Bank Country</th>
                            <th>Bank Name</th>
                            <th>Bank Branch/Address</th>
                            <th>Bank Account Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bank as $item)
                        <tr>
                            @if($isEditable)<td>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModalBank{{ $item->id }}">Edit</button>
                                <button class="btn btn-danger btn-sm" id="deleteFormBank" data-id="{{ $item->id }}">Delete</button>
                            </td>@endif
                            <td>{{ $item->payee }}</td>
                            <td>{{ $item->bank_country }}</td>
                            <td>{{ $item->bank_name }}</td>
                            <td>{{ $item->branch_address }}</td>
                            <td>{{ $item->account_number }}</td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModalBank{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form id="editFormBank{{ $item->id }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Bank Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>Full Name</label>
                                                <input type="text" name="payee" class="form-control" value="{{ $item->payee }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Bank Country</label>
                                                <input type="text" name="bank_country" class="form-control" value="{{ $item->bank_country }}" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label>Bank Name</label>
                                                <select class="form-control" name="bank_name" required>
                                                    <option value="">- Select -</option>
                                                    <option value="Bank Mandiri" {{ $item->bank_name == 'Bank Mandiri' ? 'selected' : '' }}>Bank Mandiri</option>
                                                    <option value="Bank Indonesia" {{ $item->bank_name == 'Bank Indonesia' ? 'selected' : '' }}>Bank Indonesia</option>
                                                    <option value="Bank Rakyat Indonesia" {{ $item->bank_name == 'Bank Rakyat Indonesia' ? 'selected' : '' }}>Bank Rakyat Indonesia</option>
                                                    <option value="Bank BCA" {{ $item->bank_name == 'Bank BCA' ? 'selected' : '' }}>Bank BCA</option>
                                                    <option value="Bank BNI" {{ $item->bank_name == 'Bank BNI' ? 'selected' : '' }}>Bank BNI</option>
                                                    <option value="Bank CIMB Niaga" {{ $item->bank_name == 'Bank CIMB Niaga' ? 'selected' : '' }}>Bank CIMB Niaga</option>
                                                    <option value="Bank BTN" {{ $item->bank_name == 'Bank BTN' ? 'selected' : '' }}>Bank BTN</option>
                                                    <option value="Bank Danamon" {{ $item->bank_name == 'Bank Danamon' ? 'selected' : '' }}>Bank Danamon</option>
                                                    <option value="Bank Mega" {{ $item->bank_name == 'Bank Mega' ? 'selected' : '' }}>Bank Mega</option>
                                                    <option value="Bank Permata" {{ $item->bank_name == 'Bank Permata' ? 'selected' : '' }}>Bank Permata</option>
                                                    <option value="Bank BTPN" {{ $item->bank_name == 'Bank BTPN' ? 'selected' : '' }}>Bank BTPN</option>
                                                    <option value="Bank Muamalat" {{ $item->bank_name == 'Bank Muamalat' ? 'selected' : '' }}>Bank Muamalat</option>
                                                    <option value="Bank Jago" {{ $item->bank_name == 'Bank Jago' ? 'selected' : '' }}>Bank Jago</option>
                                                    <option value="Bank Maybank" {{ $item->bank_name == 'Bank Maybank' ? 'selected' : '' }}>Bank Maybank</option>
                                                    <option value="Bank HSBC" {{ $item->bank_name == 'Bank HSBC' ? 'selected' : '' }}>Bank HSBC</option>
                                                    <option value="Bank UOB" {{ $item->bank_name == 'Bank UOB' ? 'selected' : '' }}>Bank UOB</option>
                                                    <option value="Bank OCBC NISP" {{ $item->bank_name == 'Bank OCBC NISP' ? 'selected' : '' }}>Bank OCBC NISP</option>
                                                    <option value="Bank Bukopin" {{ $item->bank_name == 'Bank Bukopin' ? 'selected' : '' }}>Bank Bukopin</option>
                                                    <option value="Bank Panin" {{ $item->bank_name == 'Bank Panin' ? 'selected' : '' }}>Bank Panin</option>
                                                    <option value="Bank Syariah Indonesia" {{ $item->bank_name == 'Bank Syariah Indonesia' ? 'selected' : '' }}>Bank Syariah Indonesia</option>
                                                    <!-- Tambahkan bank lainnya sesuai kebutuhan -->
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Bank Branch/Address</label>
                                                <input type="text" name="branch_address" class="form-control" value="{{ $item->branch_address }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Bank Account Number</label>
                                                <input type="text" name="account_number" class="form-control" value="{{ $item->account_number }}" required>
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

<!-- Create Modal Bank -->
<div class="modal fade" id="createModalBank" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="createFormBank">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Bank Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Full Name</label>
                        <input type="text" name="payee" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Bank Country</label>
                        <input type="text" name="bank_country" class="form-control" value="Indonesia" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Bank Name</label>
                        <select class="form-control" name="bank_name" required>
                            <option value="">- Select -</option>
                            <option value="Bank Mandiri">Bank Mandiri</option>
                            <option value="Bank Indonesia">Bank Indonesia</option>
                            <option value="Bank Rakyat Indonesia">Bank Rakyat Indonesia</option>
                            <option value="Bank BCA">Bank BCA</option>
                            <option value="Bank BNI">Bank BNI</option>
                            <option value="Bank CIMB Niaga">Bank CIMB Niaga</option>
                            <option value="Bank BTN">Bank BTN</option>
                            <option value="Bank Danamon">Bank Danamon</option>
                            <option value="Bank Mega">Bank Mega</option>
                            <option value="Bank Permata">Bank Permata</option>
                            <option value="Bank BTPN">Bank BTPN</option>
                            <option value="Bank Muamalat">Bank Muamalat</option>
                            <option value="Bank Jago">Bank Jago</option>
                            <option value="Bank Maybank">Bank Maybank</option>
                            <option value="Bank HSBC">Bank HSBC</option>
                            <option value="Bank UOB">Bank UOB</option>
                            <option value="Bank OCBC NISP">Bank OCBC NISP</option>
                            <option value="Bank Bukopin">Bank Bukopin</option>
                            <option value="Bank Panin">Bank Panin</option>
                            <option value="Bank Syariah Indonesia">Bank Syariah Indonesia</option>
                            <!-- Tambahkan bank lainnya sesuai kebutuhan -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Bank Branch/Address</label>
                        <input type="text" name="branch_address" class="form-control" placeholder="Nusantara" required>
                    </div>
                    <div class="mb-3">
                        <label>Bank Account Number</label>
                        <input type="text" name="account_number" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Help Modal: Bank Details -->
<div class="modal fade" id="helpModalBank" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">How to Fill in Bank Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Please enter your bank information as shown in this example:</p>

                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Bank Country</th>
                            <th>Bank Name</th>
                            <th>Bank Branch/Address</th>
                            <th>Account Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Jane Doe</td>
                            <td>Indonesia</td>
                            <td>Bank Mandiri</td>
                            <td>Jl. Sudirman No. 45, Jakarta</td>
                            <td>1234567890</td>
                        </tr>
                    </tbody>
                </table>

                <p><strong>Instructions:</strong></p>
                <ul>
                    <li><strong>Full Name:</strong> Enter the account holder’s full legal name.</li>
                    <li><strong>Bank Country:</strong> This field is set based on your location and cannot be changed.</li>
                    <li><strong>Bank Name:</strong> Choose from the provided list of supported banks.</li>
                    <li><strong>Bank Branch/Address:</strong> Provide the address of the branch where your account is held.</li>
                    <li><strong>Bank Account Number:</strong> Enter your active bank account number without spaces.</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>