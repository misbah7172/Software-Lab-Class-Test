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













