<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Library Preview</title>
</head>
<body>
  <h1>Library</h1>

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
    const api = 'api.php/books';
    const q = id => document.getElementById(id);
    const json = r => r.json();

    const get = async (genre) => fetch(api + (genre? '?genre='+encodeURIComponent(genre): '')).then(json);
    const post = async (payload) => fetch(api, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)});
    const put = async (id,payload) => fetch(api+'/'+id, {method:'PUT', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)});
    const del = async id => fetch(api+'/'+id, {method:'DELETE'});

    function esc(s){ return String(s||'').replace(/[&<>'"]/g,ch=>({ '&':'&amp;','<':'&lt;','>':'&gt;','\'':'&#39;','"':'&quot;' })[ch]||ch); }

    async function renderTable(){
      const f = q('filter').value.trim();
      try{
        const rows = await get(f);
        const tbody = q('tbody'); tbody.innerHTML='';
        if (!rows.length) { tbody.innerHTML='<tr><td colspan="2">No books</td></tr>'; return; }
        rows.forEach(b=>{
          const tr = document.createElement('tr');
          tr.innerHTML = `<td style="padding:12px"><strong>${esc(b.title)}</strong><div>${esc(b.author)}</div><div>${(b.genres||[]).map(esc).join(', ')}</div></td>
                          <td style="padding:12px"><button data-id="${b.id}" class="edit">Edit</button> <button data-id="${b.id}" class="del">Delete</button></td>`;
          tbody.appendChild(tr);
        });
        q('error').textContent='';
      }catch(e){ q('tbody').innerHTML='<tr><td colspan="2">Failed to load</td></tr>'; q('error').textContent = e.message; }
    }

    // events
    document.addEventListener('click', async e=>{
      const id = e.target.dataset && e.target.dataset.id;
      if (e.target.classList.contains('edit')){
        const b = await fetch(api+'/'+id).then(json);
        const title = prompt('Title', b.title); if (title===null) return;
        const author = prompt('Author', b.author); if (author===null) return;
        const genres = prompt('Genres (comma separated)', (b.genres||[]).join(',')); if (genres===null) return;
        const avail = confirm('OK = available')?1:0;
        await put(id, {title,author,availability:avail,genres:genres.split(',').map(s=>s.trim()).filter(Boolean)});
        renderTable();
      }
      if (e.target.classList.contains('del')){
        if (!confirm('Delete?')) return; await del(id); renderTable();
      }
    });

    q('go').addEventListener('click', renderTable);
    q('add').addEventListener('click', async ()=>{
      const title = prompt('Title'); if (!title) return;
      const author = prompt('Author'); if (!author) return;
      const genres = prompt('Genres (comma separated)') || '';
      const avail = confirm('OK = available')?1:0;
      await post({title,author,availability:avail,genres:genres.split(',').map(s=>s.trim()).filter(Boolean)});
      renderTable();
    });

    renderTable();
  </script>
</body>
</html>
