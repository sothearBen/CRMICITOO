<?php

namespace App\Repository;

use App\Entity\Editor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use \Doctrine\ORM\Tools\Pagination\Paginator;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Session\Session;
use \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Editor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Editor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Editor[]    findAll()
 * @method Editor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EditorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Editor::class);
    }

    /**
     * @return [] Returns an array of Editor objects
     */
    public function search(Request $request, Session $session, array $data, string &$page)
    {
        if ((int) $page < 1) {
            throw new \InvalidArgumentException(sprintf("The page argument can not be less than 1 (value : %s)", $page));
        }
        $firstResult = ($page - 1) * $data['number_by_page'];
        $query = $this->getSearchQuery($data);
        $query->setFirstResult($firstResult)->setMaxResults($data['number_by_page'])->addOrderBy('e.position', 'ASC');
        $paginator = new Paginator($query);
        if ($paginator->count() <= $firstResult && $page != 1) {
            if (!$request->get('page')) {
                $session->set('back_editor_page', --$page);
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
    public function getSearchQuery(array $data)
    {
        $query = $this->createQueryBuilder('e');
        if (null !== ($data['search'] ?? null)) {
            $exprOrX = $query->expr()->orX();
            $exprOrX
                ->add($query->expr()->like('e.key', ':search'))
                ->add($query->expr()->like('e.body', ':search'));
                
            $query->where($exprOrX)->setParameter('search', '%' . $data['search'] . '%');
        }
        
        return $query;
    }
}
