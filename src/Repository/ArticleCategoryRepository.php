<?php

namespace App\Repository;

use App\Entity\ArticleCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method ArticleCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArticleCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArticleCategory[]    findAll()
 * @method ArticleCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleCategory::class);
    }

    /**
     * @return [] Returns an array of ArticleCategory objects
     */
    public function searchBack(Request $request, Session $session, array $data, string &$page)
    {
        if ((int) $page < 1) {
            throw new \InvalidArgumentException(sprintf('The page argument can not be less than 1 (value : %s)', $page));
        }
        $firstResult = ($page - 1) * $data['number_by_page'];
        $query = $this->getBackQuery($data);
        $query->setFirstResult($firstResult)->setMaxResults($data['number_by_page'])->addOrderBy('a.id', 'DESC');
        $paginator = new Paginator($query);
        if ($paginator->count() <= $firstResult && 1 != $page) {
            if (!$request->get('page')) {
                $session->set('back_article_category_page', --$page);

                return $this->search($request, $session, $data, $page);
            } else {
                throw new NotFoundHttpException();
            }
        }

        return $paginator;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getBackQuery(array $data)
    {
        $query = $this->createQueryBuilder('a');
        if (null !== ($data['search'] ?? null)) {
            $exprOrX = $query->expr()->orX();
            $exprOrX->add($query->expr()->like('a.slug', ':search'));
            $query->where($exprOrX)->setParameter('search', '%'.$data['search'].'%');
        }

        return $query;
    }
}