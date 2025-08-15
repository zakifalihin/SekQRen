@extends('layouts.admin')
@section('title','Kelola Kelas')

@section('content')
<h3 class="mb-3">Kelola Kelas</h3>

<div class="card card-elev mb-3">
  <div class="card-body">
    <form id="formAdd" class="row g-2">
      <div class="col-md-4">
        <input class="form-control" name="nama_kelas" placeholder="Nama Kelas (mis. MIPA 1)" required>
      </div>
      <div class="col-md-4">
        <input class="form-control" name="wali_kelas_id" placeholder="ID Wali Kelas (User ID)">
      </div>
      <div class="col-md-2">
        <button class="btn btn-primary w-100">Tambah</button>
      </div>
    </form>
  </div>
</div>

<div class="card card-elev">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped">
        <thead class="table-dark"><tr><th>Nama Kelas</th><th>Wali Kelas</th><th class="text-end">Aksi</th></tr></thead>
        <tbody id="rows"></tbody>
      </table>
    </div>
  </div>
</div>

<script>
const token = localStorage.getItem('admin_token');
const headers = {'Authorization':'Bearer '+token,'Accept':'application/json','Content-Type':'application/json'};

async function loadKelas(){
  const res = await fetch('/api/admin/kelas',{headers});
  const js = await res.json();
  const list = js.data ?? js;
  const rows = document.getElementById('rows');
  rows.innerHTML = '';
  (list||[]).forEach(k=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${k.nama_kelas}</td>
      <td>${k.wali_kelas?.nama ?? '-'}</td>
      <td class="text-end">
        <button class="btn btn-sm btn-danger" onclick="hapus(${k.id})"><i class="bi bi-trash"></i></button>
      </td>`;
    rows.appendChild(tr);
  });
}

async function hapus(id){
  if(!confirm('Hapus kelas ini?')) return;
  const res = await fetch('/api/admin/kelas/'+id,{method:'DELETE',headers});
  if(res.ok) loadKelas(); else alert('Gagal hapus');
}

document.getElementById('formAdd').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  const payload = Object.fromEntries(fd.entries());
  const res = await fetch('/api/admin/kelas',{method:'POST',headers,body:JSON.stringify(payload)});
  if(res.ok){ e.target.reset(); loadKelas(); } else { alert('Gagal tambah kelas'); }
});

loadKelas();
</script>
@endsection
