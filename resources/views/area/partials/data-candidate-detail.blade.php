@php
    $coreInfo = [
        'NIK' => $personal->nik,
        'Known As' => $personal->known_as,
        'Gender' => $personal->gender,
        'Place / Date of Birth' => trim(($personal->place_of_birth ?? '-') . ' / ' . (optional($personal->date_of_birth)->format('d M Y') ?? '-')),
        'Country of Birth' => $personal->country_of_birth,
        'Marital Status' => $personal->marital_status,
        'Marital Status Since' => $personal->marital_status_since,
        'Nationality' => $personal->nationality,
        'Primary Language' => $personal->language,
        'Religion' => $personal->religion,
        'Ethnic' => $personal->ethnic,
        'Blood Type' => $personal->blood_type,
        'Province Domicily' => $personal->province_domicily,
        'Last Education' => $personal->last_education,
        'Disease History' => $personal->disease_history,
        'Allergy History' => $personal->allergy_history,
        'Willing Duty Bound' => $personal->willing_duty_bound ? 'Yes' : 'No',
        'Join Availability' => $personal->join_availability,
        'Declaration Acknowledged' => $personal->isDeclaration ? 'Yes' : 'No',
    ];
    $communications = $personal->communications ?? collect();
    $socials = $personal->socialMedia ?? collect();
    $addresses = $personal->addresses ?? collect();
    $families = $personal->families ?? collect();
    $educations = $personal->educations ?? collect();
    $documents = $personal->documents ?? collect();
    $experiences = $personal->experiences ?? collect();
    $languages = $personal->languages ?? collect();
    $skills = $personal->skills ?? collect();
    $sizes = $personal->sizes ?? collect();
    $banks = $personal->banks ?? collect();
    $taxes = $personal->taxes ?? collect();
    $references = $personal->references ?? collect();
@endphp

<div class="row g-3">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header">Data Pribadi</div>
            <div class="card-body">
                <dl class="row mb-0">
                    @foreach($coreInfo as $label => $value)
                        <dt class="col-sm-5">{{ $label }}</dt>
                        <dd class="col-sm-7">{{ $value !== null && $value !== '' ? $value : '—' }}</dd>
                    @endforeach
                </dl>
                @if(!empty($personal->profile_photo))
                    <div class="mt-3">
                        <span class="fw-semibold d-block">Profile Photo</span>
                        <img src="{{ asset('https://career.itcihutanimanunggal.co.id/public/upload/'.$personal->profile_photo) }}" alt="Profile" class="img-thumbnail" style="max-width: 120px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#photoModal" data-photo="{{ asset('https://career.itcihutanimanunggal.co.id/public/upload/'.$personal->profile_photo) }}">
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
                    <span>{{ optional($personal->user)->email ?? '—' }}</span>
                </div>
                <div class="mb-3">
                    <span class="fw-semibold d-block">WhatsApp</span>
                    <span>{{ $personal->whatsapp_number ?? '—' }}</span>
                </div>
                <div class="mb-3">
                    <span class="fw-semibold d-block">Komunikasi</span>
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
        @include('area.partials.personal-section-table', [
            'title' => 'Keahlian',
            'headers' => ['Skill'],
            'rows' => $skills->map(fn($item) => [
                $item->skill,
            ]),
        ])
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
            'headers' => ['Bank', 'Nomor Rekening', 'Pemilik', 'Detail'],
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