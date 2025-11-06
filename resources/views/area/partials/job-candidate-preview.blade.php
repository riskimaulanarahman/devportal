<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Preview Data</span>
        <form method="GET" action="{{ $downloadRoute }}">
            @foreach($filters as $key => $value)
                @if($value !== null && $value !== '')
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <button type="submit" class="btn btn-success" {{ $results->total() === 0 ? 'disabled' : '' }}>Download CSV</button>
        </form>
    </div>
    <div class="card-body">
        @if($results->count())
            <div class="accordion" id="applymentAccordion">
                @foreach($results as $row)
                    @php
                        $personal = $row->personalData;
                        $job = $row->jobs;
                        $phase = $row->phase;
                        $headingId = 'heading-'.$row->id;
                        $collapseId = 'collapse-'.$row->id;
                        $title = optional($personal)->title;
                        $firstName = optional($personal)->first_name;
                        $lastName = optional($personal)->last_name;
                        $fullNameParts = array_filter([
                            $title ? $title.'.' : null,
                            trim(trim(($firstName ?? '').' '.($lastName ?? ''))),
                        ], function ($value) {
                            return $value !== null && $value !== '';
                        });
                        $fullName = trim(implode(' ', $fullNameParts));
                        if ($fullName === '') {
                            $fullName = 'Tanpa Nama';
                        }
                        $jobTitle = optional($job)->job_title ?? 'Unknown Job';
                        $jobCode = optional($job)->code_job ?? '-';
                        $phaseName = optional($phase)->name ?? '-';
                        $statusLabel = ($row->status === \App\Models\Module\JobApplyment::STATUS_APPROVED && (int)$row->phase_id === 5)
                            ? 'Hired'
                            : (\App\Models\Module\JobApplyment::STATUS_LABELS[$row->status] ?? 'Unknown');
                    @endphp
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header" id="{{ $headingId }}">
                            <button class="accordion-button {{ !$loop->first ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
                                <div class="d-flex flex-column flex-lg-row w-100 gap-2">
                                    <span class="fw-semibold">{{ $fullName }}</span>
                                    <span class="text-muted">| {{ $jobTitle }} ({{ $jobCode }})</span>
                                    <span class="badge bg-primary align-self-start">Phase: {{ $phaseName }}</span>
                                    <span class="badge bg-info align-self-start">Status: {{ $statusLabel }}</span>
                                </div>
                            </button>
                        </h2>
                        <div id="{{ $collapseId }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="{{ $headingId }}" data-bs-parent="#applymentAccordion">
                            <div class="accordion-body">
                                <div class="mb-3">
                                    <span class="badge bg-light text-dark me-2">Applied: {{ optional($row->created_at)->format('d M Y H:i') ?? '-' }}</span>
                                    <span class="badge bg-light text-dark me-2">Current Salary: {{ $row->current_salary ? number_format($row->current_salary) : '-' }}</span>
                                    <span class="badge bg-light text-dark">Expected Salary: {{ $row->expected_salary ? number_format($row->expected_salary) : '-' }}</span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-xl-6">
                                        <div class="card h-100">
                                            <div class="card-header">Data Pribadi Inti</div>
                                            <div class="card-body">
                                                @php
                                                    $dateOfBirth = optional(optional($personal)->date_of_birth)->format('d M Y');
                                                    $coreInfo = [
                                                        'NIK' => optional($personal)->nik,
                                                        'Title' => $title,
                                                        'Known As' => optional($personal)->known_as,
                                                        'Gender' => optional($personal)->gender,
                                                        'Place / Date of Birth' => trim(($personal && $personal->place_of_birth ? $personal->place_of_birth : '-') . ' / ' . ($dateOfBirth ?? '-')),
                                                        'Country of Birth' => optional($personal)->country_of_birth,
                                                        'Marital Status' => optional($personal)->marital_status,
                                                        'Marital Status Since' => optional($personal)->marital_status_since,
                                                        'Nationality' => optional($personal)->nationality,
                                                        'Religion' => optional($personal)->religion,
                                                        'Ethnic' => optional($personal)->ethnic,
                                                        'Blood Type' => optional($personal)->blood_type,
                                                        'Language' => optional($personal)->language,
                                                        'Last Education' => optional($personal)->last_education,
                                                        'Join Availability' => optional($personal)->join_availability,
                                                        'Province Domicily' => optional($personal)->province_domicily,
                                                        'Disease History' => optional($personal)->disease_history,
                                                        'Allergy History' => optional($personal)->allergy_history,
                                                        'Willing Duty Bound' => optional($personal)->willing_duty_bound ? 'Yes' : 'No',
                                                        'Declaration Acknowledged' => optional($personal)->isDeclaration ? 'Yes' : 'No',
                                                        'WhatsApp Number' => optional($personal)->whatsapp_number,
                                                    ];
                                                @endphp
                                                <dl class="row mb-0">
                                                    @foreach($coreInfo as $label => $value)
                                                        <dt class="col-sm-5">{{ $label }}</dt>
                                                        <dd class="col-sm-7">{{ $value !== null && $value !== '' ? $value : '—' }}</dd>
                                                    @endforeach
                                                </dl>
                                                @if(!empty(optional($personal)->profile_photo))
                                                    <div class="mt-3">
                                                        <span class="fw-semibold d-block">Profile Photo</span>
                                                        <img src="{{ asset('upload/'.$personal->profile_photo) }}" alt="Profile" class="img-thumbnail" style="max-width: 120px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#photoModal" data-photo="{{ asset('upload/'.$personal->profile_photo) }}">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-6">
                                        <div class="card h-100">
                                            <div class="card-header">Kontak & Komunikasi</div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <span class="fw-semibold d-block">Email</span>
                                                    <span>{{ optional(optional($personal)->user)->email ?? '—' }}</span>
                                                </div>
                                                <div class="mb-3">
                                                    <span class="fw-semibold d-block">Komunikasi</span>
                                                    @php($communications = optional($personal)->communications ?? collect())
                                                    @if($communications->count())
                                                        <ul class="list-unstyled mb-0">
                                                            @foreach($communications as $item)
                                                                <li><span class="fw-semibold">{{ $item->type }}:</span> {{ $item->number }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <span class="text-muted">Tidak ada data</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <span class="fw-semibold d-block">Sosial Media</span>
                                                    @php($socials = optional($personal)->socialMedia ?? collect())
                                                    @if($socials->count())
                                                        <ul class="list-unstyled mb-0">
                                                            @foreach($socials as $item)
                                                                <li>{{ $item->platform }}: {{ $item->username }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <span class="text-muted">Tidak ada data</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>

                                <div class="row g-3 mt-1">
                                    <div class="col-lg-6">
                                        @php($addresses = optional($personal)->addresses ?? collect())
                                        @php($families = optional($personal)->families ?? collect())
                                        @php($educations = optional($personal)->educations ?? collect())
                                        @php($documents = optional($personal)->documents ?? collect())
                                        @php($experiences = optional($personal)->experiences ?? collect())
                                        @php($languages = optional($personal)->languages ?? collect())
                                        @php($skills = optional($personal)->skills ?? collect())
                                        @php($sizes = optional($personal)->sizes ?? collect())
                                        @php($banks = optional($personal)->banks ?? collect())
                                        @php($taxes = optional($personal)->taxes ?? collect())
                                        @php($references = optional($personal)->references ?? collect())
                                        @include('area.partials.personal-section-table', [
                                            'title' => 'Alamat & Kontak Darurat',
                                            'headers' => ['Tipe', 'Detail', 'Telepon', 'Kontak'],
                                            'rows' => $addresses->map(fn($item) => [
                                                $item->address_type,
                                                implode(', ', array_filter([$item->street_and_house_number, $item->city, $item->postal_code, $item->country])),
                                                $item->tel_number,
                                                $item->name_contact_person,
                                            ]),
                                        ])
                                        @include('area.partials.personal-section-table', [
                                            'title' => 'Keluarga',
                                            'headers' => ['Relasi', 'Nama', 'Detail'],
                                            'rows' => $families->map(fn($item) => [
                                                $item->members,
                                                $item->name,
                                                implode(' | ', array_filter([
                                                    $item->gender,
                                                    $item->birth_place,
                                                    $item->date_of_birth ? \Illuminate\Support\Carbon::parse($item->date_of_birth)->format('d M Y') : null,
                                                    $item->country_of_birth,
                                                    $item->nationality,
                                                    implode(', ', array_filter([$item->job_title, $item->employer, $item->employer_type])),
                                                ])),
                                            ]),
                                        ])
                                        @include('area.partials.personal-section-table', [
                                            'title' => 'Pendidikan',
                                            'headers' => ['Institusi', 'Periode', 'Detail'],
                                            'rows' => $educations->map(fn($item) => [
                                                $item->education_establishment,
                                                ($item->start_date ? \Illuminate\Support\Carbon::parse($item->start_date)->format('M Y') : '-') . ' - ' . ($item->end_date ? \Illuminate\Support\Carbon::parse($item->end_date)->format('M Y') : 'Kini'),
                                                implode(' | ', array_filter([
                                                    $item->institute_location,
                                                    $item->country,
                                                    $item->certificate,
                                                    $item->duration,
                                                    implode(', ', array_filter([$item->branch_of_study_major, $item->branch_of_study_minor])),
                                                ])),
                                            ]),
                                        ])
                                        <!-- @include('area.partials.personal-section-table', [
                                            'title' => 'Dokumen Pendukung',
                                            'headers' => ['Jenis', 'Lokasi Berkas'],
                                            'rows' => $documents->map(fn($item) => [
                                                optional($item->typeDocument)->name ?? 'Unknown',
                                                $item->path ? '<a href="'.route('document.download', $item->id).'" target="_blank" rel="noopener">Download</a>' : '—',
                                            ]),
                                        ]) -->
                                    </div>
                                    <div class="col-lg-6">
                                        @include('area.partials.personal-section-table', [
                                            'title' => 'Pengalaman Kerja',
                                            'headers' => ['Perusahaan', 'Periode', 'Detail'],
                                            'rows' => $experiences->map(fn($item) => [
                                                $item->company,
                                                ($item->start_date ? \Illuminate\Support\Carbon::parse($item->start_date)->format('M Y') : '-') . ' - ' . ($item->end_date ? \Illuminate\Support\Carbon::parse($item->end_date)->format('M Y') : 'Kini'),
                                                implode(' | ', array_filter([
                                                    $item->city_or_country,
                                                    $item->industry_type,
                                                    'Posisi: '.$item->last_position_held,
                                                    'Atasan: '.implode(' - ', array_filter([$item->name_of_superior, $item->designation_of_superior])),
                                                    'Salary: '.($item->last_drawn_salary ?? '-'),
                                                    'Reason: '.$item->reason_for_leaving,
                                                ])),
                                            ]),
                                        ])
                                        @include('area.partials.personal-section-table', [
                                            'title' => 'Kemampuan Bahasa',
                                            'headers' => ['Bahasa', 'Baca', 'Tulis', 'Bicara'],
                                            'rows' => $languages->map(fn($item) => [
                                                $item->language,
                                                $item->read,
                                                $item->write,
                                                $item->speak,
                                            ]),
                                        ])
                                        @include('area.partials.personal-section-table', [
                                            'title' => 'Keahlian Teknis / Lainnya',
                                            'headers' => ['Skill'],
                                            'rows' => $skills->map(fn($item) => [$item->skill]),
                                        ])
                                        @include('area.partials.personal-section-table', [
                                            'title' => 'Ukuran Tubuh',
                                            'headers' => ['Tinggi', 'Berat', 'Baju', 'Celana', 'Sepatu'],
                                            'rows' => $sizes->map(fn($item) => [
                                                $item->height,
                                                $item->weight,
                                                $item->clothing_size,
                                                $item->pants_size,
                                                $item->shoe_size,
                                            ]),
                                        ])
                                        @include('area.partials.personal-section-table', [
                                            'title' => 'Rekening Bank',
                                            'headers' => ['Bank', 'No. Rekening', 'Pemilik', 'Detail'],
                                            'rows' => $banks->map(fn($item) => [
                                                $item->bank_name,
                                                $item->account_number,
                                                $item->payee,
                                                implode(', ', array_filter([$item->bank_country, $item->branch_address])),
                                            ]),
                                        ])
                                        @include('area.partials.personal-section-table', [
                                            'title' => 'Pajak & Jaminan Sosial',
                                            'headers' => ['NPWP', 'Tanggal Daftar', 'Alamat', 'Detail'],
                                            'rows' => $taxes->map(fn($item) => [
                                                $item->npwp,
                                                $item->registered_date ? \Illuminate\Support\Carbon::parse($item->registered_date)->format('d M Y') : '-',
                                                $item->npwp_address,
                                                implode(' | ', array_filter([
                                                    'Married: '.$item->married_for_tax_purpose,
                                                    'Spouse Benefit: '.$item->spouse_benefit,
                                                    'Dependents: '.$item->number_of_dependents,
                                                    'Benefit Class: '.$item->benefit_class,
                                                    'Jamsostek: '.$item->jamsostek_id,
                                                    'BPJS: '.$item->bpjs_id,
                                                ])),
                                            ]),
                                        ])
                                        @include('area.partials.personal-section-table', [
                                            'title' => 'Referensi',
                                            'headers' => ['Relasi', 'Nama', 'Kontak'],
                                            'rows' => $references->map(fn($item) => [
                                                $item->relation,
                                                $item->name,
                                                $item->number,
                                            ]),
                                        ])
                                    </div>
                                </div>
                                @php($latestPsych = $row->psychotests->first())
                                @php($latestInterview = $row->interviews->first())
                                @php($offeringSchedule = $row->schedules->where('type', 'offering')->sortByDesc(function($schedule){ $date = $schedule->updated_at ?? $schedule->created_at; return $date ? $date->timestamp : 0; })->first())
                                @php($mcuSchedule = $row->schedules->where('type', 'mcu')->sortByDesc(function($schedule){ $date = $schedule->updated_at ?? $schedule->created_at; return $date ? $date->timestamp : 0; })->first())
                                <div class="row g-3 mt-3">
                                    <div class="col-lg-6">
                                        <div class="card">
                                            <div class="card-header">Psychotest</div>
                                            <div class="card-body">
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-5">TIU Score</dt>
                                                    <dd class="col-sm-7">{{ optional($latestPsych)->tiu_score ?? '—' }}</dd>
                                                    <dt class="col-sm-5">TIU Category</dt>
                                                    <dd class="col-sm-7">{{ optional($latestPsych)->tiu_category ?? '—' }}</dd>
                                                    <dt class="col-sm-5">Math Score</dt>
                                                    <dd class="col-sm-7">{{ optional($latestPsych)->math_score ?? '—' }}</dd>
                                                    <dt class="col-sm-5">DISC Letters</dt>
                                                    <dd class="col-sm-7">{{ $latestPsych && $latestPsych->disc_letters ? implode(', ', array_filter(array_map('trim', explode(',', $latestPsych->disc_letters)))) : '—' }}</dd>
                                                </dl>
                                            </div>
                                        </div>

                                        <div class="card mt-3">
                                            <div class="card-header">Interview</div>
                                            <div class="card-body">
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-5">Assessment Score</dt>
                                                    <dd class="col-sm-7">{{ $latestInterview ? ($latestInterview->job_assessment_score !== null && $latestInterview->job_assessment_max !== null ? $latestInterview->job_assessment_score.' / '.$latestInterview->job_assessment_max : ($latestInterview->job_assessment_score ?? $latestInterview->job_assessment_max ?? '—')) : '—' }}</dd>
                                                    <dt class="col-sm-5">TOPICC Score</dt>
                                                    <dd class="col-sm-7">{{ $latestInterview ? ($latestInterview->topicc_score !== null && $latestInterview->topicc_max !== null ? $latestInterview->topicc_score.' / '.$latestInterview->topicc_max : ($latestInterview->topicc_score ?? $latestInterview->topicc_max ?? '—')) : '—' }}</dd>
                                                    <dt class="col-sm-5">MBTI</dt>
                                                    <dd class="col-sm-7">
                                                        @php($mbtParts = $latestInterview ? array_filter([
                                                            $latestInterview->mbt_m !== null ? 'M: '.$latestInterview->mbt_m : null,
                                                            $latestInterview->mbt_b !== null ? 'B: '.$latestInterview->mbt_b : null,
                                                            $latestInterview->mbt_c !== null ? 'C: '.$latestInterview->mbt_c : null,
                                                        ]) : [])
                                                        {{ $mbtParts ? implode(' | ', $mbtParts) : '—' }}
                                                    </dd>
                                                </dl>

                                                @if($latestInterview && $latestInterview->details && $latestInterview->details->count())
                                                    <div class="table-responsive mt-3">
                                                        <table class="table table-sm align-middle mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>Nama</th>
                                                                    <th>Putaran</th>
                                                                    <th>Score 1</th>
                                                                    <th>Score 2</th>
                                                                    <th>Tempat</th>
                                                                    <th>Catatan</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($latestInterview->details as $detail)
                                                                    <tr>
                                                                        <td>{{ $detail->name }}</td>
                                                                        <td>{{ $detail->phase_round ?? '—' }}</td>
                                                                        <td>{{ $detail->score1 ?? '—' }}</td>
                                                                        <td>{{ $detail->score2 ?? '—' }}</td>
                                                                        <td>{{ $detail->place ?? '—' }}</td>
                                                                        <td>{{ $detail->remarks ?? '—' }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="card">
                                            <div class="card-header">Offering Letter</div>
                                            <div class="card-body">
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-5">Latest Result</dt>
                                                    <dd class="col-sm-7">{{ optional($offeringSchedule)->result ? ucfirst($offeringSchedule->result) : '—' }}</dd>
                                                    <dt class="col-sm-5">Accepted</dt>
                                                    <dd class="col-sm-7">{{ optional($offeringSchedule)->is_accept !== null ? (optional($offeringSchedule)->is_accept ? 'Yes' : 'No') : '—' }}</dd>
                                                    <dt class="col-sm-5">Reschedulable</dt>
                                                    <dd class="col-sm-7">{{ optional($offeringSchedule)->reschedulable !== null ? (optional($offeringSchedule)->reschedulable ? 'Yes' : 'No') : '—' }}</dd>
                                                    <dt class="col-sm-5">Notes</dt>
                                                    <dd class="col-sm-7">{{ optional($offeringSchedule)->result_notes ?? '—' }}</dd>
                                                    <dt class="col-sm-5">Attachment</dt>
                                                    <dd class="col-sm-7">{{ optional($offeringSchedule)->attachment_name ?? optional($offeringSchedule)->attachment_path ?? '—' }}</dd>
                                                </dl>
                                            </div>
                                        </div>

                                        <div class="card mt-3">
                                            <div class="card-header">Medical Check Up</div>
                                            <div class="card-body">
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-5">Latest Result</dt>
                                                    <dd class="col-sm-7">{{ optional($mcuSchedule)->result ? ucfirst($mcuSchedule->result) : '—' }}</dd>
                                                    <dt class="col-sm-5">Accepted</dt>
                                                    <dd class="col-sm-7">{{ optional($mcuSchedule)->is_accept !== null ? (optional($mcuSchedule)->is_accept ? 'Yes' : 'No') : '—' }}</dd>
                                                    <dt class="col-sm-5">Reschedulable</dt>
                                                    <dd class="col-sm-7">{{ optional($mcuSchedule)->reschedulable !== null ? (optional($mcuSchedule)->reschedulable ? 'Yes' : 'No') : '—' }}</dd>
                                                    <dt class="col-sm-5">Notes</dt>
                                                    <dd class="col-sm-7">{{ optional($mcuSchedule)->result_notes ?? '—' }}</dd>
                                                    <dt class="col-sm-5">Attachment</dt>
                                                    <dd class="col-sm-7">{{ optional($mcuSchedule)->attachment_name ?? optional($mcuSchedule)->attachment_path ?? '—' }}</dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $results->links() }}
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <p class="mb-0">Tidak ada data kandidat untuk filter ini.</p>
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalLabel">Profile Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" alt="Profile Photo" id="photoModalImage" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>
