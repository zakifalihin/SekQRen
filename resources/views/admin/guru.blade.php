@extends('layouts.admin')
@section('title','Kelola Guru')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Kelola Guru</h3>
  <button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalAdd"><i class="bi bi-plus me-1"></i> Tambah Guru</button>
</div>

<div class="card card-elev">
  <div class="card-body">
    <div class="input-group mb-3">
      <input type="text" id="inputSearch" class="form-control" placeholder="Cari nama atau NIP..." />
      <button class="btn btn-primary" id="btnSearch"><i class="bi bi-search"></i> Cari</button>
    </div>
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead class="table-dark">
          <tr>
            <th>Nama</th>
            <th>NIP</th>
            <th>Email</th>
            <th>Role</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody id="tbGuru">
          </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAdd" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" id="formAdd">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Guru Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nama</label>
          <input name="nama" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">NIP</label>
          <input name="nip" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <input type="hidden" name="role" value="guru">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" id="formEdit">
      <div class="modal-header">
        <h5 class="modal-title">Edit Guru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="edit-id">
        <div class="mb-3">
          <label class="form-label">Nama</label>
          <input name="nama" id="edit-nama" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">NIP</label>
          <input name="nip" id="edit-nip" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" id="edit-email" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-success">Perbarui</button>
      </div>
    </form>
  </div>
</div>


<script>
  // Konfigurasi umum
  const token = localStorage.getItem('admin_token');
  const headers = {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  };

  // --- Fungsi-fungsi Utama ---

  // Memuat data guru dari API
  async function loadGuru(query = '') {
    const url = query ? `/api/admin/guru?search=${encodeURIComponent(query)}` : '/api/admin/guru';
    try {
      const res = await fetch(url, { headers });
      if (!res.ok) throw new Error('Gagal memuat data');
      const js = await res.json();
      const list = js.data || js;
      const tb = document.getElementById('tbGuru');
      tb.innerHTML = '';
      if (!list || list.length === 0) {
        tb.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Tidak ada data guru.</td></tr>';
        return;
      }
      list.forEach(g => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${g.nama}</td>
          <td>${g.nip}</td>
          <td>${g.email}</td>
          <td><span class="badge bg-secondary">${g.role}</span></td>
          <td class="text-end">
            <button class="btn btn-sm btn-outline-success" onclick="openEditModal(${g.id}, '${g.nama}', '${g.nip}', '${g.email}')"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-danger" onclick="hapusGuru(${g.id})"><i class="bi bi-trash"></i></button>
          </td>`;
        tb.appendChild(tr);
      });
    } catch (error) {
      alert('Terjadi kesalahan: ' + error.message);
    }
  }

  // Menangani proses penghapusan guru
  async function hapusGuru(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus guru ini?')) return;
    try {
      const res = await fetch(`/api/admin/guru/${id}`, {
        method: 'DELETE',
        headers
      });
      if (!res.ok) throw new Error('Gagal menghapus data guru');
      alert('Guru berhasil dihapus!');
      loadGuru(document.getElementById('inputSearch').value || '');
    } catch (error) {
      alert('Terjadi kesalahan: ' + error.message);
    }
  }

  // Membuka modal edit dan mengisi form
  function openEditModal(id, nama, nip, email) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-nama').value = nama;
    document.getElementById('edit-nip').value = nip;
    document.getElementById('edit-email').value = email;
    const modalEdit = new bootstrap.Modal(document.getElementById('modalEdit'));
    modalEdit.show();
  }

  // --- Event Listeners ---

  // Tombol Cari
  document.getElementById('btnSearch').addEventListener('click', () => {
    loadGuru(document.getElementById('inputSearch').value);
  });
  // Event "Enter" pada input cari
  document.getElementById('inputSearch').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
      loadGuru(document.getElementById('inputSearch').value);
    }
  });

  // Form Tambah Guru
  document.getElementById('formAdd').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const payload = Object.fromEntries(fd.entries());
    try {
      const res = await fetch('/api/admin/guru', {
        method: 'POST',
        headers,
        body: JSON.stringify(payload)
      });
      if (!res.ok) {
        const j = await res.json();
        throw new Error(j.message || 'Gagal menyimpan');
      }
      alert('Guru berhasil ditambahkan!');
      e.target.reset();
      bootstrap.Modal.getInstance(document.getElementById('modalAdd')).hide();
      loadGuru();
    } catch (error) {
      alert('Terjadi kesalahan: ' + error.message);
    }
  });

  // Form Edit Guru
  document.getElementById('formEdit').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const payload = Object.fromEntries(fd.entries());
    const id = payload.id;
    try {
      const res = await fetch(`/api/admin/guru/${id}`, {
        method: 'PUT', // Menggunakan PUT untuk update
        headers,
        body: JSON.stringify(payload)
      });
      if (!res.ok) {
        const j = await res.json();
        throw new Error(j.message || 'Gagal memperbarui');
      }
      alert('Data guru berhasil diperbarui!');
      bootstrap.Modal.getInstance(document.getElementById('modalEdit')).hide();
      loadGuru(document.getElementById('inputSearch').value || '');
    } catch (error) {
      alert('Terjadi kesalahan: ' + error.message);
    }
  });

  // Panggil fungsi untuk memuat data saat halaman pertama kali dibuka
  loadGuru();
</script>
@endsection