<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    // CRUD Guru
    public function indexGuru() {}
    public function storeGuru(Request $request) {}
    public function updateGuru(Request $request, $id) {}
    public function deleteGuru($id) {}

    // CRUD Siswa
    public function indexSiswa() {}
    public function storeSiswa(Request $request) {}
    public function updateSiswa(Request $request, $id) {}
    public function deleteSiswa($id) {}

    // CRUD Kelas
    public function indexKelas() {}
    public function storeKelas(Request $request) {}
    public function updateKelas(Request $request, $id) {}
    public function deleteKelas($id) {}

    // CRUD Mata Pelajaran
    public function indexMapel() {}
    public function storeMapel(Request $request) {}
    public function updateMapel(Request $request, $id) {}
    public function deleteMapel($id) {}

    // CRUD Jadwal
    public function indexJadwal() {}
    public function storeJadwal(Request $request) {}
    public function updateJadwal(Request $request, $id) {}
    public function deleteJadwal($id) {}
}
