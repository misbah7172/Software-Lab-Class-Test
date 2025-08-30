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
</body>
</html>
