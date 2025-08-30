<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Library Preview</title>
</head>
<body>
  <h1>Library — Quick Preview</h1>

  <div class="card">
    <div style="display:flex;gap:8px;margin-bottom:8px">
  <input id="filter" placeholder="filter by genre (optional)" style="flex:1"/>
  <button id="go">Refresh</button>
  <button id="add">Add Book</button>
    </div>

    <table id="table" style="width:100%;border-collapse:collapse">
      <thead style="text-align:left"><tr><th style="border-bottom:1px solid #ccc;padding:8px">Details</th><th style="border-bottom:1px solid #ccc;padding:8px">Actions</th></tr></thead>
      <tbody id="tbody"><tr><td colspan="2" style="padding:12px">Loading...</td></tr></tbody>
    </table>
    <div id="error" class="small" style="color:#a00;margin-top:8px"></div>
  </div>

  <script>
    const apiBase = 'api.php/books';
    let editId = null;

    // helpers
    const $ = id => document.getElementById(id);
    const esc = s => String(s||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[c]);

    function renderPreview(){
      const t = $('title').value.trim();
      const a = $('author').value.trim();
      const g = $('genres').value.split(',').map(s=>s.trim()).filter(Boolean);
      const av = $('availability').value === '1';
      $('livePreview').innerHTML = `
        <strong>${esc(t)||'—'}</strong> <div class="small">by ${esc(a)||'—'}</div>
        <div class="small">${av? 'Available':'Not available'}</div>
        <div class="small">Genres: ${g.map(esc).join(', ') || '—'}</div>
      `;
    }

    async function loadBooks(){
      const f = $('filter').value.trim();
      let url = apiBase + (f ? '?genre=' + encodeURIComponent(f) : '');
      try {
        const res = await fetch(url);
        if (!res.ok) throw new Error('API responded '+res.status);
        const data = await res.json();
        const tbody = $('tbody');
        tbody.innerHTML = '';
        if (!Array.isArray(data) || data.length === 0) { tbody.innerHTML = '<tr><td colspan="2" style="padding:12px">No books</td></tr>'; return; }
        data.forEach(b => {
          const tr = document.createElement('tr');
          const details = document.createElement('td');
          details.style.padding = '12px';
          details.innerHTML = `<strong>${esc(b.title)}</strong><div class="small">${esc(b.author)}</div><div class="small">Genres: ${(b.genres||[]).map(esc).join(', ')}</div>`;
          const actions = document.createElement('td');
          actions.style.padding = '12px';
          actions.innerHTML = `<button data-id="${b.id}" class="edit">Edit</button> <button data-id="${b.id}" class="delete">Delete</button>`;
          tr.appendChild(details); tr.appendChild(actions);
          tbody.appendChild(tr);
        });
        $('error').textContent = '';
      } catch (err) {
        $('tbody').innerHTML = '<tr><td colspan="2" style="padding:12px">Failed to load</td></tr>';
        $('error').textContent = err.message;
      }
    }

    function loadToForm(b){
      // load details to temporary edit form via prompt for simplicity
      const title = prompt('Title', b.title || '');
      if (title === null) return;
      const author = prompt('Author', b.author || '');
      if (author === null) return;
      const genres = prompt('Genres (comma separated)', (b.genres||[]).join(','));
      if (genres === null) return;
      const availability = confirm('Click OK if available, Cancel if not') ? 1 : 0;
      const payload = { title, author, availability, genres: genres.split(',').map(s=>s.trim()).filter(Boolean) };
      fetch(apiBase + '/' + b.id, { method: 'PUT', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) }).then(()=> loadBooks()).catch(e=> $('error').textContent = e.message);
    }

    async function save(){
      const payload = { title: $('title').value.trim(), author: $('author').value.trim(), availability: Number($('availability').value), genres: $('genres').value.split(',').map(s=>s.trim()).filter(Boolean) };
      if (!payload.title || !payload.author) { alert('Title and author required'); return; }
      if (editId) {
        await fetch(apiBase + '/' + editId, { method: 'PUT', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
      } else {
        await fetch(apiBase, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
      }
      clearForm();
      loadBooks();
    }

    // simple actions for table
    document.addEventListener('click', e=>{
      if (e.target.classList.contains('edit')) {
        const id = e.target.dataset.id; // fetch book and open prompt
        fetch(apiBase + '/' + id).then(r=>r.json()).then(b=> loadToForm(b)).catch(err=> $('error').textContent = err.message);
      }
      if (e.target.classList.contains('delete')) {
        const id = e.target.dataset.id;
        if (!confirm('Delete this book?')) return;
        fetch(apiBase + '/' + id, { method: 'DELETE' }).then(()=> loadBooks()).catch(err=> $('error').textContent = err.message);
      }
    });

      $('go').addEventListener('click', loadBooks);
      // add new book via prompt
      $('add').addEventListener('click', async ()=>{
        const title = prompt('Title'); if (!title) return;
        const author = prompt('Author'); if (!author) return;
        const genres = prompt('Genres (comma separated)') || '';
        const availability = confirm('Click OK if available, Cancel if not') ? 1 : 0;
        const payload = { title, author, availability, genres: genres.split(',').map(s=>s.trim()).filter(Boolean) };
        try { await fetch(apiBase, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) }); loadBooks(); } catch (e) { $('error').textContent = e.message; }
      });
    // initial
    loadBooks();
  </script>
</body>
</html>
