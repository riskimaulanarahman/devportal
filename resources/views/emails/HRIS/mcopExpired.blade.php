<div style="margin-bottom: 20px">Berikut adalah daftar kontrak MCOP yang akan segera berakhir atau sudah lewat masa berlakunya:</div>

<table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse; font-size:12px; line-height:1.3; border:1px solid #000000; margin-bottom:20px;">
    <thead>
        <tr>
            <th style="background:#dbeb8b; border:1px solid #000000; padding:6px;">SAP ID</th>
            <th style="background:#dbeb8b; border:1px solid #000000; padding:6px;">Nama</th>
            <th style="background:#dbeb8b; border:1px solid #000000; padding:6px;">Estate</th>
            <th style="background:#dbeb8b; border:1px solid #000000; padding:6px;">Dept</th>
            <th style="background:#dbeb8b; border:1px solid #000000; padding:6px;">Posisi</th>
            <th style="background:#dbeb8b; border:1px solid #000000; padding:6px;">No. Kontrak</th>
            <th style="background:#dbeb8b; border:1px solid #000000; padding:6px;">Sisa Hari</th>
            <th style="background:#dbeb8b; border:1px solid #000000; padding:6px;">Mulai Kontrak</th>
            <th style="background:#dbeb8b; border:1px solid #000000; padding:6px;">Akhir Kontrak</th>
            <th style="background:#dbeb8b; border:1px solid #000000; padding:6px;">Tipe Kendaraan</th>
            <th style="background:#dbeb8b; border:1px solid #000000; padding:6px;">No Polisi</th>
            <th style="background:#dbeb8b; border:1px solid #000000; padding:6px;">Tgl Diterima</th>
            <th style="background:#dbeb8b; border:1px solid #000000; padding:6px;">No Mesin</th>
            <th style="background:#dbeb8b; border:1px solid #000000; padding:6px;">Tahun Pembuatan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($mailData['mcop'] as $contract)
            <tr>
                <td style="border:1px solid #000000; padding:6px;">{{ $contract->SAP_ID }}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ $contract->Name }}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ $contract->Estate }}</td>
                <td style="border:1px solid #000000; padding:6px;">{!! $contract->Dept !!}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ $contract->Position }}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ $contract->Contract_No }}</td>
                <td style="border:1px solid #000000; padding:6px;
                    @if($contract->Days_To_End_Contract < 0)
                        color:red;
                    @endif
                ">{{ $contract->Days_To_End_Contract }}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ \Carbon\Carbon::parse($contract->Start_Contract)->format('d-m-Y') }}</td>
                <td style="border:1px solid #000000; padding:6px; font-weight:bold;">{{ \Carbon\Carbon::parse($contract->End_Contract)->format('d-m-Y') }}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ $contract->Vehicle_Type }}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ $contract->Number_Plate_New }}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ \Carbon\Carbon::parse($contract->Date_of_Receipt)->format('d-m-Y') }}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ $contract->Machine_No }}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ $contract->Unit_Build_Year }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="17" style="border:1px solid #000000; padding:6px; text-align:center;">Tidak ada kontrak yang akan berakhir atau sudah expired.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div style="margin-bottom: 20px; margin-top: 20px;">Berikut adalah daftar STNK MCOP yang akan segera berakhir atau sudah lewat masa berlakunya:</div>

<table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse; font-size:12px; line-height:1.3; border:1px solid #000000; margin-bottom:20px;">
    <thead>
        <tr>
            <th style="background:#a3bd94; border:1px solid #000000; padding:6px;">SAP ID</th>
            <th style="background:#a3bd94; border:1px solid #000000; padding:6px;">Nama</th>
            <th style="background:#a3bd94; border:1px solid #000000; padding:6px;">Estate</th>
            <th style="background:#a3bd94; border:1px solid #000000; padding:6px;">Dept</th>
            <th style="background:#a3bd94; border:1px solid #000000; padding:6px;">Posisi</th>
            <th style="background:#a3bd94; border:1px solid #000000; padding:6px;">STNK No</th>
            <th style="background:#a3bd94; border:1px solid #000000; padding:6px;">Sisa Hari</th>
            <th style="background:#a3bd94; border:1px solid #000000; padding:6px;">Tanggal Expired</th>
            <th style="background:#a3bd94; border:1px solid #000000; padding:6px;">Tipe Kendaraan</th>
            <th style="background:#a3bd94; border:1px solid #000000; padding:6px;">No Polisi</th>
            <th style="background:#a3bd94; border:1px solid #000000; padding:6px;">Tgl Diterima</th>
            <th style="background:#a3bd94; border:1px solid #000000; padding:6px;">No Mesin</th>
            <th style="background:#a3bd94; border:1px solid #000000; padding:6px;">Tahun Pembuatan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($mailData['stnk'] as $stnk)
            <tr>
                <td style="border:1px solid #000000; padding:6px;">{{ $stnk->SAP_ID }}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ $stnk->Name }}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ $stnk->Estate }}</td>
                <td style="border:1px solid #000000; padding:6px;">{!! $stnk->Dept !!}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ $stnk->Position }}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ $stnk->STNK_No }}</td>
                <td style="border:1px solid #000000; padding:6px;
                    @if($stnk->Days_To_End_Contract < 0)
                        color:red;
                    @endif
                ">{{ $stnk->Days_To_End_Contract }}</td>
                <td style="border:1px solid #000000; padding:6px; font-weight:bold;">{{ \Carbon\Carbon::parse($stnk->STNK_Expiry_Date)->format('d-m-Y') }}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ $stnk->Vehicle_Type }}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ $stnk->Number_Plate_New }}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ \Carbon\Carbon::parse($stnk->Date_of_Receipt)->format('d-m-Y') }}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ $stnk->Machine_No }}</td>
                <td style="border:1px solid #000000; padding:6px;">{{ $stnk->Unit_Build_Year }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="17" style="border:1px solid #000000; padding:6px; text-align:center;">Tidak ada kontrak yang akan berakhir atau sudah expired.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:20px;"><b>Mohon segera dilakukan tindak lanjut.</b></div>