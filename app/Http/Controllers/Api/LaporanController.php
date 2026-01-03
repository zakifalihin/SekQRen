<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiGuru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\AbsensiGuruExport; 
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function getLaporanAbsensiGuru(Request $request)
    {
        try {
            // 1. Validasi Input Filter
            $request->validate([
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
                'guru_id' => 'nullable|exists:users,id', // Lacak Guru ID
            ]);

            // 2. Query Dasar & Relasi (AbsensiGuru JOIN Guru/User)
            $laporan = AbsensiGuru::query()
                ->whereBetween('tanggal', [$request->start_date, $request->end_date])
                ->with(['guru']); // Memuat relasi ke tabel users (guru)

            // 3. Aplikasi Filter Guru Opsional (Fungsi Lacak Guru)
            if ($request->filled('guru_id')) {
                // Filter data hanya untuk guru yang dipilih
                $laporan->where('guru_id', $request->guru_id);
            }

            // 4. Eksekusi Query
            $data = $laporan->orderBy('tanggal', 'desc')->get();

            if ($data->isEmpty()) {
                 return response()->json(['message' => 'Tidak ada data absensi guru untuk filter ini.', 'data' => []], 200);
            }

            // 5. Format Output untuk Frontend (Mapping)
            $output = $data->map(function ($item) {
                return [
                    'tanggal' => $item->tanggal,
                    'guru_nama' => $item->guru->nama ?? 'Guru Tidak Ditemukan',
                    'status' => $item->status,
                    'jam_datang' => $item->jam_datang,
                    'jam_pulang' => $item->jam_pulang,
                    'total_jam_ajar' => $item->total_jam_ajar, // Mengambil dari kolom database
                    'keterangan' => $item->keterangan ?? '-',
                ];
            });

            return response()->json([
                'message' => 'Laporan absensi guru berhasil diambil.',
                'data' => $output,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error fetching guru report: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan pada server saat mengambil laporan guru.', 'error_detail' => $e->getMessage()], 500);
        }
    }

    public function exportAbsensiGuruExcel(Request $request)
    {
        
        $response = $this->getLaporanAbsensiGuru($request);
        
        if ($response->getStatusCode() !== 200) {
            return response()->json(['message' => 'Gagal membuat file, periksa filter data.', 'errors' => json_decode($response->getContent())], 400); 
        }

        $responseData = json_decode($response->getContent(), true);
        $laporanData = collect($responseData['data']);
        
        if ($laporanData->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data absensi guru untuk diekspor dengan filter ini.'], 404);
        }

        
        $exportData = $laporanData->map(function ($item) {
            return [
                $item['tanggal'],
                $item['guru_nama'],
                $item['status'],
                $item['jam_datang'],
                $item['jam_pulang'],
                $item['total_jam_ajar'],
                $item['keterangan']
            ];
        });

        // 4. Generate Nama File
        $startDate = Carbon::parse($request->start_date)->format('Ymd');
        $endDate = Carbon::parse($request->end_date)->format('Ymd');
        $fileName = "Laporan_Absensi_Guru_{$startDate}_to_{$endDate}.xlsx";

        // 5. EKSEKUSI EXPORT FILE
        return Excel::download(new AbsensiGuruExport($exportData), $fileName);
    }
}