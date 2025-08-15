@extends('layouts.admin')
@section('title','Generate QR')

@section('content')
<h3 class="mb-3">Generate QR Absensi Guru</h3>

<div class="card card-elev mb-3">
  <div class="card-body">
    <form id="formQR" class="row g-2">
      <div class="col-md-3">
        <select class="form-select" name="status" required>
          <option value="datang">Datang</option>
          <option value="pulang">Pulang</option>
        </select>
      </div>
      <div class="col-md-2">
        <button class="btn btn-primary w-100">Generate</button>
      </div>
    </form>
  </div>
</div>

<div id="qrWrap" class="card card-elev d-none">
  <div class="card-body">
    <h6 class="text-muted mb-2">QR Code:</h6>
    <div id="qrImage" class="mb-2"></div>
    <div>Expired at: <span id="exp"></span></div>
  </div>
</div>

<script>
const token = localStorage.getItem('admin_token');
const headers = {'Authorization':'Bearer '+token,'Accept':'application/json','Content-Type':'application/json'};

document.getElementById('formQR').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  const payload = Object.fromEntries(fd.entries());

  const res = await fetch('/api/admin/absensi/generate',{method:'POST',headers,body:JSON.stringify(payload)});
  const js = await res.json();

  if(!res.ok){ alert(js.message||'Gagal generate'); return; }

  // API-mu mengembalikan qr_html (data:image/png;base64,...) + expired_at
  // Tampilkan gambar QR
  const wrap = document.getElementById('qrWrap');
  const imgWrap = document.getElementById('qrImage');
  const exp = document.getElementById('exp');
  imgWrap.innerHTML = '';

  if (js.qr_html && js.qr_html.startsWith('data:image')) {
    const img = new Image();
    img.src = js.qr_html;
    img.width = 220;
    img.height = 220;
    imgWrap.appendChild(img);
  } else if (js.qr_html) {
    // kalau qr_html berisi <svg> / html, render apa adanya
    imgWrap.innerHTML = js.qr_html;
  }

  exp.textContent = js.expired_at || '-';
  wrap.classList.remove('d-none');
});
</script>
@endsection
