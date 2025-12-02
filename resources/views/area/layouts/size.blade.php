<div class="col-lg-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <h4 class="card-title mb-0">Physical Measurements</h4>
                <button type="button" class="btn btn-sm btn-secondary p-1" data-bs-toggle="modal" data-bs-target="#helpModalSize" title="Help">
                    <i class="fa fa-question-circle"></i>
                </button>
            </div>

            @if($isEditable)<button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createModalSize">Create</button>@endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>Height</th>
                            <th>Weight</th>
                            <th>Clothing Size</th>
                            <th>Pants Size</th>
                            <th>Shoe Size</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($size as $item)
                        <tr>
                            @if($isEditable)<td>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModalSize{{ $item->id }}">Edit</button>
                                <button class="btn btn-danger btn-sm" id="deleteFormSize" data-id="{{ $item->id }}">Delete</button>
                            </td>@endif
                            <td>{{ $item->height }}</td>
                            <td>{{ $item->weight }}</td>
                            <td>{{ $item->clothing_size }}</td>
                            <td>{{ $item->pants_size }}</td>
                            <td>{{ $item->shoe_size }}</td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModalSize{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form id="editFormSize{{ $item->id }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Physical Measurements</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>Height</label>
                                                <input type="text" name="height" class="form-control" value="{{ $item->height }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Weight</label>
                                                <input type="text" name="weight" class="form-control" value="{{ $item->weight }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Clothing Size</label>
                                                <select class="form-control" id="clothing_size" name="clothing_size" required>
                                                    <option value="">- Select -</option>
                                                    <option value="XS" {{ $item->clothing_size == 'XS' ? 'selected' : '' }}>XS</option>
                                                    <option value="S" {{ $item->clothing_size == 'S' ? 'selected' : '' }}>S</option>
                                                    <option value="M" {{ $item->clothing_size == 'M' ? 'selected' : '' }}>M</option>
                                                    <option value="L" {{ $item->clothing_size == 'L' ? 'selected' : '' }}>L</option>
                                                    <option value="XL" {{ $item->clothing_size == 'XL' ? 'selected' : '' }}>XL</option>
                                                    <option value="XXL" {{ $item->clothing_size == 'XXL' ? 'selected' : '' }}>XXL</option>
                                                    <option value="XXXL" {{ $item->clothing_size == 'XXXL' ? 'selected' : '' }}>XXXL</option>
                                                    <option value="XXXXL" {{ $item->clothing_size == 'XXXXL' ? 'selected' : '' }}>XXXXL</option>
                                                    <option value="XXXXXL" {{ $item->clothing_size == 'XXXXXL' ? 'selected' : '' }}>XXXXXL</option>
                                                    <!-- Add more countries as needed -->
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Pants Size</label>
                                                <input type="text" name="pants_size" class="form-control" value="{{ $item->pants_size }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Shoe Size</label>
                                                <input type="text" name="shoe_size" class="form-control" value="{{ $item->shoe_size }}" required>
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
<!-- Create Modal Size-->
<div class="modal fade" id="createModalSize" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="createFormSize">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Physical Measurements</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Height</label>
                        <input type="number" name="height" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Weight</label>
                        <input type="number" name="weight" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Clothing Size</label>
                        <select class="form-control" id="clothing_size" name="clothing_size" required>
                            <option value="">- Select -</option>
                            <option value="XS">XS</option>
                            <option value="S">S</option>
                            <option value="M">M</option>
                            <option value="L">L</option>
                            <option value="XL">XL</option>
                            <option value="XXL">XXL</option>
                            <option value="XXXL">XXXL</option>
                            <option value="XXXXL">XXXXL</option>
                            <option value="XXXXXL">XXXXXL</option>
                            <!-- Add more countries as needed -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Pants Size</label>
                        <input type="number" name="pants_size" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Shoe Size</label>
                        <input type="number" name="shoe_size" class="form-control" required>
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
<div class="modal fade" id="helpModalSize" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Guide to Filling in Physical Measurements</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Please refer to the examples below when filling in your physical data:</p>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Height (cm)</th>
                            <th>Weight (kg)</th>
                            <th>Clothing Size</th>
                            <th>Pants Size</th>
                            <th>Shoe Size (EU)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>175</td>
                            <td>70</td>
                            <td>M</td>
                            <td>32</td>
                            <td>42</td>
                        </tr>
                        <tr>
                            <td>160</td>
                            <td>55</td>
                            <td>S</td>
                            <td>28</td>
                            <td>38</td>
                        </tr>
                        <tr>
                            <td>185</td>
                            <td>85</td>
                            <td>L</td>
                            <td>34</td>
                            <td>44</td>
                        </tr>
                    </tbody>
                </table>

                <p><strong>Notes:</strong></p>
                <ul>
                    <li><strong>Height:</strong> Use centimeters (e.g., 170, 180).</li>
                    <li><strong>Weight:</strong> Use kilograms (e.g., 65, 80).</li>
                    <li><strong>Clothing Size:</strong> Choose from XS, S, M, L, XL, XXL, etc.</li>
                    <li><strong>Pants Size:</strong> Use waist size in inches (e.g., 30, 32, 34).</li>
                    <li><strong>Shoe Size:</strong> Use European sizing (e.g., 39, 40, 42).</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
