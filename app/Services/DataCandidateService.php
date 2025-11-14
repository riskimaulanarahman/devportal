<?php

namespace App\Services;

use App\Models\PersonalData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class DataCandidateService
{
    public function getPreview(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $this->buildPreviewQuery($filters)
            ->paginate($perPage)
            ->appends($this->sanitizeFilters($filters));
    }

    public function getDetail(int $id): ?PersonalData
    {
        return PersonalData::query()
            ->with($this->detailRelations())
            // ->whereHas('jobApplyments')
            ->find($id);
    }

    public function headings(): array
    {
        return [
            'Title',
            'First Name',
            'Last Name',
            'Known As',
            'Email',
            'NIK',
            'Gender',
            'Place of Birth',
            'Date of Birth',
            'Country of Birth',
            'Marital Status',
            'Marital Status Since',
            'Nationality',
            'Primary Language',
            'Religion',
            'Ethnic',
            'Blood Type',
            'Province Domicily',
            'Last Education',
            'Disease History',
            'Allergy History',
            'Willing Duty Bound',
            'Join Availability',
            'WhatsApp Number',
            'Declaration Acknowledged',
            'Communications',
            'Addresses',
            'Social Media',
            'Family Members',
            'Educations',
            'Experiences',
            'Languages',
            'Skills',
            'Body Sizes',
            'Bank Accounts',
            'Tax Records',
            // 'Documents',
            'References',
        ];
    }

    public function hasResults(array $filters): bool
    {
        return $this->applyFilters($this->baseQuery(), $filters)->exists();
    }

    public function chunkedExport(array $filters, callable $writer, int $chunkSize = 500): void
    {
        $this->applyFilters($this->baseQuery(), $filters)
            ->orderBy('id')
            ->chunkById($chunkSize, function ($chunk) use ($writer) {
                /** @var \Illuminate\Support\Collection<int, PersonalData> $chunk */
                foreach ($chunk as $personal) {
                    $writer($this->mapRow($personal));
                }
            }, 'id');
    }

    public function sanitizeFilters(array $filters): array
    {
        return array_filter($filters, static fn ($value) => $value !== null && $value !== '');
    }

    protected function buildPreviewQuery(array $filters): Builder
    {
        return $this->applyFilters($this->previewQuery(), $filters)
            ->orderByDesc('created_at');
    }

    protected function previewQuery(): Builder
    {
        return PersonalData::query()
            ->select([
                'id',
                'user_id',
                'title',
                'first_name',
                'last_name',
                'known_as',
                'nik',
                'gender',
                'province_domicily',
                'last_education',
                'whatsapp_number',
                'created_at',
            ])
            ->with([
                'user:id,email',
            ]);
            // ->whereHas('jobApplyments');
    }

    protected function baseQuery(): Builder
    {
        return PersonalData::query()
            ->select([
                'id',
                'user_id',
                'title',
                'first_name',
                'last_name',
                'known_as',
                'nik',
                'gender',
                'place_of_birth',
                'date_of_birth',
                'country_of_birth',
                'marital_status',
                'marital_status_since',
                'nationality',
                'language',
                'religion',
                'ethnic',
                'blood_type',
                'province_domicily',
                'last_education',
                'disease_history',
                'allergy_history',
                'willing_duty_bound',
                'join_availability',
                'whatsapp_number',
                'isDeclaration',
                'created_at',
            ])
            ->with($this->detailRelations());
            // ->whereHas('jobApplyments');
    }

    protected function detailRelations(): array
    {
        return [
            'user:id,email',
            'communications' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'type',
                'number',
            ]),
            'addresses' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'address_type',
                'street_and_house_number',
                'city',
                'postal_code',
                'country',
                'tel_number',
                'name_contact_person',
            ]),
            'socialMedia' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'platform',
                'username',
            ]),
            'families' => function ($query) {
                $query->select($this->familySelectColumns());
            },
            'educations' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'education_establishment',
                'institute_location',
                'country',
                'start_date',
                'end_date',
                'certificate',
                'branch_of_study_major',
                'branch_of_study_minor',
            ]),
            'experiences' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'last_position_held',
                'company',
                'industry_type',
                'start_date',
                'end_date',
                'name_of_superior',
                'designation_of_superior',
                'last_drawn_salary',
                'reason_for_leaving',
            ]),
            'languages' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'language',
                'read',
                'write',
                'speak',
            ]),
            'skills' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'skill',
            ]),
            'sizes' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'height',
                'weight',
                'clothing_size',
                'pants_size',
                'shoe_size',
            ]),
            'banks' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'bank_name',
                'account_number',
                'payee',
                'bank_country',
                'branch_address',
            ]),
            'taxes' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'npwp',
                'registered_date',
                'npwp_address',
                'married_for_tax_purpose',
                'spouse_benefit',
                'number_of_dependents',
                'benefit_class',
                'jamsostek_id',
                'bpjs_id',
            ]),
            // 'documents' => fn ($query) => $query->select([
            //     'id',
            //     'personal_data_id',
            //     'type_document_id',
            //     'path',
            // ])->with([
            //     'typeDocument:id,name',
            // ]),
            'references' => fn ($query) => $query->select([
                'id',
                'personal_data_id',
                'relation',
                'name',
                'number',
            ]),
        ];
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);
            $query->where(function (Builder $inner) use ($keyword) {
                $likePattern = "%{$keyword}%";
                $inner->where('first_name', 'LIKE', $likePattern)
                    ->orWhere('last_name', 'LIKE', $likePattern)
                    ->orWhere('known_as', 'LIKE', $likePattern)
                    ->orWhere('nik', 'LIKE', $likePattern)
                    ->orWhereHas('user', function (Builder $userQuery) use ($likePattern) {
                        $userQuery->where('email', 'LIKE', $likePattern);
                    });
            });
        }

        return $query;
    }

    protected function mapRow(PersonalData $personal): array
    {
        $user = $personal->user;

        return [
            $personal->title,
            $personal->first_name,
            $personal->last_name,
            $personal->known_as,
            optional($user)->email,
            $personal->nik,
            $personal->gender,
            $personal->place_of_birth,
            $this->formatDate($personal->date_of_birth),
            $personal->country_of_birth,
            $personal->marital_status,
            $personal->marital_status_since,
            $personal->nationality,
            $personal->language,
            $personal->religion,
            $personal->ethnic,
            $personal->blood_type,
            $personal->province_domicily,
            $personal->last_education,
            $personal->disease_history,
            $personal->allergy_history,
            $this->formatBoolean($personal->willing_duty_bound),
            $personal->join_availability,
            $personal->whatsapp_number,
            $this->formatBoolean($personal->isDeclaration),
            $this->formatCollection($personal->communications, function ($item) {
                return sprintf('%s: %s', $item->type, $item->number);
            }),
            $this->formatCollection($personal->addresses, function ($item) {
                $parts = array_filter([
                    $item->street_and_house_number,
                    $item->city,
                    $item->postal_code,
                    $item->country,
                ]);
                $contact = $item->tel_number ? 'Tel: '.$item->tel_number : null;
                $contactPerson = $item->name_contact_person ? 'CP: '.$item->name_contact_person : null;
                $details = array_filter(array_merge($parts, [$contact, $contactPerson]));
                return $item->address_type.' - '.implode(', ', $details);
            }),
            $this->formatCollection($personal->socialMedia, function ($item) {
                return sprintf('%s: %s', $item->platform, $item->username);
            }),
            $this->formatCollection($personal->families, function ($item) {
                $meta = array_filter([
                    $item->gender,
                    $item->birth_place,
                    $this->formatDate($item->date_of_birth),
                    $item->country_of_birth,
                    $item->nationality,
                ]);
                $jobs = array_filter([$item->job_title, $item->employer, $item->employer_type]);
                return sprintf('%s - %s (%s)', $item->members, $item->name, implode(' | ', array_merge($meta, $jobs)));
            }),
            $this->formatCollection($personal->educations, function ($item) {
                $period = $this->formatPeriod($item->start_date, $item->end_date);
                $major = array_filter([$item->branch_of_study_major, $item->branch_of_study_minor]);
                return sprintf('%s @ %s, %s [%s] %s', $item->education_establishment, $item->institute_location, $item->country, $period, implode(' / ', array_filter([$item->certificate, implode(' & ', $major)])));
            }),
            $this->formatCollection($personal->experiences, function ($item) {
                $period = $this->formatPeriod($item->start_date, $item->end_date);
                $superior = array_filter([$item->name_of_superior, $item->designation_of_superior]);
                return sprintf('%s @ %s (%s) [%s] | Superior: %s | Salary: %s | Reason: %s', $item->last_position_held, $item->company, $item->industry_type, $period, implode(' - ', $superior), $item->last_drawn_salary, $item->reason_for_leaving);
            }),
            $this->formatCollection($personal->languages, function ($item) {
                return sprintf('%s (R:%s W:%s S:%s)', $item->language, $item->read, $item->write, $item->speak);
            }),
            $this->formatCollection($personal->skills, fn ($item) => $item->skill),
            $this->formatCollection($personal->sizes, function ($item) {
                return sprintf('H:%s W:%s Clothes:%s Pants:%s Shoes:%s', $item->height, $item->weight, $item->clothing_size, $item->pants_size, $item->shoe_size);
            }),
            $this->formatCollection($personal->banks, function ($item) {
                $parts = array_filter([$item->bank_country, $item->branch_address]);
                return sprintf('%s - %s (%s) [%s]', $item->bank_name, $item->account_number, $item->payee, implode(', ', $parts));
            }),
            $this->formatCollection($personal->taxes, function ($item) {
                $flags = array_filter([
                    'Married: '.$item->married_for_tax_purpose,
                    'Spouse Benefit: '.$item->spouse_benefit,
                    'Dependents: '.$item->number_of_dependents,
                    'Benefit Class: '.$item->benefit_class,
                ]);
                $ids = array_filter([
                    'Jamsostek: '.$item->jamsostek_id,
                    'BPJS: '.$item->bpjs_id,
                ]);
                return sprintf('%s (Reg %s) - %s | %s | %s', $item->npwp, $this->formatDate($item->registered_date), $item->npwp_address, implode(' | ', $flags), implode(' | ', $ids));
            }),
            // $this->formatCollection($personal->documents, function ($item) {
            //     return sprintf('%s: %s', optional($item->typeDocument)->name ?? 'Unknown', $item->path);
            // }),
            $this->formatCollection($personal->references, function ($item) {
                return sprintf('%s - %s (%s)', $item->relation, $item->name, $item->number);
            }),
        ];
    }

    protected function formatDate($value, string $format = 'Y-m-d')
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format($format);
        }

        try {
            return Carbon::parse($value)->format($format);
        } catch (\Exception $e) {
            return (string) $value;
        }
    }

    protected function formatPeriod($start, $end): string
    {
        $startFormatted = $this->formatDate($start);
        $endFormatted = $this->formatDate($end);

        if ($startFormatted && $endFormatted) {
            return $startFormatted.' - '.$endFormatted;
        }

        return $startFormatted ?? ($endFormatted ? 'Until '.$endFormatted : '-');
    }

    protected function formatBoolean($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return (bool) $value ? 'Yes' : 'No';
    }

    protected function formatCollection($collection, callable $formatter): ?string
    {
        if (!$collection || $collection->isEmpty()) {
            return null;
        }

        return $collection
            ->map($formatter)
            ->filter()
            ->implode(' | ');
    }

    protected function familySelectColumns(): array
    {
        $columns = [
            'id',
            'personal_data_id',
            'members',
            'name',
            'gender',
            'birth_place',
            'date_of_birth',
            'country_of_birth',
            'nationality',
        ];

        foreach (['job_title', 'employer', 'employer_type'] as $column) {
            if (Schema::hasColumn('personal_family', $column)) {
                $columns[] = $column;
            }
        }

        return $columns;
    }
}