<?php

/**
 * Book Model
 */
class BookModel extends BaseModel
{
    protected $table = 'books';
    protected $fillable = [
        'title',
        'isbn',
        'author_id',
        'category_id',
        'publication_year',
        'description',
        'total_copies',
        'available_copies',
        'cover_image',
        'status'
    ];

    /**
     * Get books with pagination and filters
     */
    public function getBooksPaginated($page = 1, $limit = 10, $search = '', $categoryId = '')
    {
        $limit = $limit ?? Config::ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $whereClause = 'WHERE 1=1';
        $params = [];

        if (!empty($search)) {
            $whereClause .= ' AND (b.title LIKE :search OR b.isbn LIKE :search OR a.name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if (!empty($categoryId)) {
            $whereClause .= ' AND b.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM books b
                     LEFT JOIN authors a ON b.author_id = a.id
                     LEFT JOIN categories c ON b.category_id = c.id
                     $whereClause";

        $countResult = $this->db->fetch($countSql, $params);
        $total = $countResult['total'] ?? 0;

        // Get records
        $sql = "SELECT b.*, a.name as author_name, c.name as category_name
                FROM books b
                LEFT JOIN authors a ON b.author_id = a.id
                LEFT JOIN categories c ON b.category_id = c.id
                $whereClause
                ORDER BY b.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->query($sql, array_merge($params, [
            'limit' => $limit,
            'offset' => $offset
        ]));

        $records = $stmt ? $stmt->fetchAll() : [];

        return [
            'data' => $records,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }

    /**
     * Get book with related details
     */
    public function getBookDetails($id)
    {
        $sql = "SELECT b.*, a.name as author_name, a.bio as author_bio,
                       c.name as category_name, c.description as category_description
                FROM books b
                LEFT JOIN authors a ON b.author_id = a.id
                LEFT JOIN categories c ON b.category_id = c.id
                WHERE b.id = :id";

        return $this->db->fetch($sql, ['id' => $id]);
    }

    /**
     * Get all categories
     */
    public function getAllCategories()
    {
        $sql = "SELECT * FROM categories ORDER BY name";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get all authors
     */
    public function getAllAuthors()
    {
        $sql = "SELECT * FROM authors ORDER BY name";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get books by author
     */
    public function getBooksByAuthor($authorId)
    {
        $sql = "SELECT b.*, c.name as category_name
                FROM books b
                LEFT JOIN categories c ON b.category_id = c.id
                WHERE b.author_id = :author_id
                ORDER BY b.title";

        return $this->db->fetchAll($sql, ['author_id' => $authorId]);
    }

    /**
     * Get books by category
     */
    public function getBooksByCategory($categoryId)
    {
        $sql = "SELECT b.*, a.name as author_name
                FROM books b
                LEFT JOIN authors a ON b.author_id = a.id
                WHERE b.category_id = :category_id
                ORDER BY b.title";

        return $this->db->fetchAll($sql, ['category_id' => $categoryId]);
    }

    /**
     * Get available books for borrowing
     */
    public function getAvailableBooks()
    {
        $sql = "SELECT b.*, a.name as author_name, c.name as category_name
                FROM books b
                LEFT JOIN authors a ON b.author_id = a.id
                LEFT JOIN categories c ON b.category_id = c.id
                WHERE b.status = 'available' AND b.available_copies > 0
                ORDER BY b.title";

        return $this->db->fetchAll($sql);
    }

    /**
     * Update available copies
     */
    public function updateAvailableCopies($bookId, $change)
    {
        $sql = "UPDATE books SET available_copies = available_copies + :change,
                total_copies = GREATEST(total_copies, available_copies)
                WHERE id = :id";

        return $this->db->query($sql, [
            'change' => $change,
            'id' => $bookId
        ]);
    }

    /**
     * Search books
     */
    public function searchBooks($query, $limit = 20)
    {
        $sql = "SELECT b.*, a.name as author_name, c.name as category_name
                FROM books b
                LEFT JOIN authors a ON b.author_id = a.id
                LEFT JOIN categories c ON b.category_id = c.id
                WHERE b.title LIKE :query OR b.isbn LIKE :query OR
                      a.name LIKE :query OR b.description LIKE :query
                ORDER BY b.title
                LIMIT :limit";

        return $this->db->fetchAll($sql, [
            'query' => '%' . $query . '%',
            'limit' => $limit
        ]);
    }

    /**
     * Get popular books (most borrowed)
     */
    public function getPopularBooks($limit = 10)
    {
        $sql = "SELECT b.*, a.name as author_name, COUNT(l.id) as borrow_count
                FROM books b
                LEFT JOIN authors a ON b.author_id = a.id
                LEFT JOIN loans l ON b.id = l.book_id
                GROUP BY b.id
                ORDER BY borrow_count DESC, b.title
                LIMIT :limit";

        $stmt = $this->db->query($sql, ['limit' => $limit]);
        return $stmt ? $stmt->fetchAll() : [];
    }

    /**
     * Get recent books
     */
    public function getRecentBooks($limit = 10)
    {
        $sql = "SELECT b.*, a.name as author_name, c.name as category_name
                FROM books b
                LEFT JOIN authors a ON b.author_id = a.id
                LEFT JOIN categories c ON b.category_id = c.id
                ORDER BY b.created_at DESC
                LIMIT :limit";

        $stmt = $this->db->query($sql, ['limit' => $limit]);
        return $stmt ? $stmt->fetchAll() : [];
    }
}