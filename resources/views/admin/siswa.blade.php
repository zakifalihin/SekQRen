@extends('layouts.admin')
@section('title','Kelola Siswa')

@section('content')
<h3 class="mb-3">Kelola Siswa</h3>
<div class="card card-elev">
  <div class="card-body">
    <div class="input-group mb-3">
      <input type="text" id="q" class="form-control" placeholder="Cari nama/NISN...">
      <button class="btn btn-outline-secondary" id="go"><i class="bi bi-search"></i></button>
    </div>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead class="table-dark"><tr><th>Nama</th><th>NISN</th><th>Kelas</th></tr></thead>
        <tbody id="rows"></tbody>
      </table>
    </div>
  </div>
</div>

<script>
const token = localStorage.getItem('admin_token');
const headers = {'Authorization':'Bearer '+token,'Accept':'application/json'};

async function loadSiswa(q=''){
  const url = q? `/api/admin/siswa?search=${encodeURIComponent(q)}` : '/api/admin/siswa';
  const res = await fetch(url,{headers});
  const js = await res.json();
  const list = js.data ?? js;
  const rows = document.getElementById('rows');
  rows.innerHTML = '';
  (list||[]).forEach(s=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${s.nama}</td><td>${s.nisn??'-'}</td><td>${s.kelas?.nama_kelas ?? '-'}</td>`;
    rows.appendChild(tr);
  });
}
document.getElementById('go').addEventListener('click', ()=>loadSiswa(document.getElementById('q').value));
loadSiswa();
</script>
@endsection
