<?php

namespace App\Repository;

use App\Data\ListData;
use App\Entity\Liste;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * @extends ServiceEntityRepository<Liste>
 *
 * @method Liste|null find($id, $lockMode = null, $lockVersion = null)
 * @method Liste|null findOneBy(array $criteria, array $orderBy = null)
 * @method Liste[]    findAll()
 * @method Liste[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Liste::class);
        $this->paginator = $paginator;
    }

    public function findList(ListData $search, $id): PaginationInterface
    {
        $query = $this
            ->createQueryBuilder('l')
            ->andWhere('l.user_id = :id')
            ->setParameter('id', $id);

        if (!empty($search->q)) {
            $query = $query
                ->andWhere('LOWER(l.nom) LIKE LOWER(:q)')
                ->setParameter('q', "%{$search->q}%");
        }
        
        $query = $query->getQuery();
        return $this->paginator->paginate(
            $query,
            $search->page,
            40
        );
    }
    //    /**
    //     * @return Liste[] Returns an array of Liste objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Liste
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
