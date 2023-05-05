<?php
namespace App\Model;

use Nette;

class PostFacade
{
	public function __construct(
		private Nette\Database\Connection $database,
	) {
	}
    public function getPublicArticles(int $limit, int $offset): Nette\Database\ResultSet
    {
        return $this->database->query('
            SELECT * FROM posts
            WHERE created_at < ?
            ORDER BY created_at DESC
            LIMIT ?
            OFFSET ?',
            new \DateTime,
            $limit,
            $offset
        );
    }
    
    public function getPublishedArticlesCount(): int
	{
		return $this->database->fetchField('SELECT COUNT(3) FROM posts WHERE created_at < ?', new \DateTime);
	}
}

