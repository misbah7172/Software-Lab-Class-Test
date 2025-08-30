 Project Description 
You will build a Library Book Tracker application where users can: 
● Add new books with genres 
● View all books or filter them by genre 
● Update book details (title, author, availability, genre) 
● Remove books from the collection 
CRUD + Genres Functionality 
1. Each book record should have the following fields: 
○ id (auto-generated) 
○ title (string) 
○ author (string) 
○ availability (boolean: available/not available) 
○ genres (array/list, e.g., ["fiction", "mystery"]) 
○ createdAt (timestamp) 
2. Application must support the following APIs: 
● POST /books → Add a new book with optional genres 
● GET /books → List all books 
● GET /books/{id} → Get a specific book by ID 
● GET /books?genre=fiction → Filter books by a specific genre 
● PUT /books/{id} → Update book info (title, author, availability, genres) 
● DELETE /books/{id} → Remove book



















## Implementation process

I built this Library Book Tracker in these steps. This documents how the code works, the schema choices, and the commands I used to verify the implementation.

1. Project files created/updated
	- `db.php` — PDO helper to connect to MySQL (used by `api.php`). Default credentials assume XAMPP: host `127.0.0.1`, db `library`, user `root`, empty password. Update as needed.
	- `schema.sql` — SQL to create the database and tables. I implemented a two-table design: `books` (includes a JSON `genres` column) and `genres` (registry of known genres). Example rows are inserted.
	- `api.php` — REST-like endpoints that implement the required APIs (POST, GET, GET by id, GET?genre, PUT, DELETE). Key behavior:
	  - POST `/books`: inserts a new book, stores genres as JSON in `books.genres`, and inserts genre names into `genres` table.
	  - GET `/books`: returns all books; each book's `genres` field is returned as an array (decoded from JSON).
	  - GET `/books?genre=NAME`: attempts to use `JSON_CONTAINS` to filter books whose `genres` JSON contains the given name; falls back to a `LIKE` search against the JSON string if `JSON_CONTAINS` isn't available.
	  - GET `/books/{id}`: returns a single book by id.
	  - PUT `/books/{id}`: updates specified fields and writes the `genres` array as JSON into `books.genres`; it also ensures the `genres` registry contains any provided genre names.
	  - DELETE `/books/{id}`: deletes the book record.

2. Schema design choices
	- You requested only two tables. I implemented `books` with a JSON `genres` column (array of genre names) and a lightweight `genres` registry table for discovery. This keeps the model simple while allowing a registry of known genres.
	- Alternative: a fully normalized `book_genres` join table is also possible; I implemented that earlier but switched to two tables per your request.

3. Commands I used to validate code (run locally)
	- Lint PHP files:
	  php -l "C:\\xampp\\htdocs\\Software-Lab-Class-Test\\ClassTest02\\api.php"
	  php -l "C:\\xampp\\htdocs\\Software-Lab-Class-Test\\ClassTest02\\db.php"
	- Import schema into MySQL (XAMPP default):
	  mysql -u root < "C:\\xampp\\htdocs\\Software-Lab-Class-Test\\ClassTest02\\schema.sql"
	- Test API via PowerShell (examples):
	  $body = @{ title='Dune'; author='Frank Herbert'; availability=1; genres=@('fiction','sci-fi') } | ConvertTo-Json
	  Invoke-RestMethod -Uri 'http://localhost/Software-Lab-Class-Test/ClassTest02/api.php/books' -Method Post -Body $body -ContentType 'application/json'

4. Verification
	- After importing the schema I linted the PHP files with `php -l` (no syntax errors).
	- The API was tested by calling POST/GET/PUT/DELETE via PowerShell examples and using the `index.php` UI in a browser.

5. Notes and next steps you might want
	- If you prefer stricter normalization I can switch to a `book_genres` join table (three-table design).
	- I can add cleanup of unused genres or an endpoint that lists all genres from the registry.
	- I can add validation and better error messages.

If you'd like, I can update this README further to include full example request/response payloads and a short Postman collection.
 