<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Data\AnimeListData;
use App\Entity\Anime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
/**
 * @extends ServiceEntityRepository<Anime>
 *
 * @method Anime|null find($id, $lockMode = null, $lockVersion = null)
 * @method Anime|null findOneBy(array $criteria, array $orderBy = null)
 * @method Anime[]    findAll()
 * @method Anime[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnimeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Anime::class);
        $this->paginator = $paginator;
    }

    public function findSearch(SearchData $search): PaginationInterface
    {
        $query = $this
            ->createQueryBuilder('a')
            ->join('a.categorie', 'c')
            ->join('a.format', 'f')
            ->join('a.season', 's')
            ->join('a.status', 'st');
                    

        if (!empty($search->q)) {
            $query = $query
                ->andWhere('LOWER(a.nom) LIKE LOWER(:q)')
                ->setParameter('q', "%{$search->q}%");
        }

        if (!empty($search->categories)) {
            $query = $query
                ->andWhere('c.id IN (:categories)')
                ->setParameter('categories', $search->categories);
        }

        if (!empty($search->status)) {
            $query = $query
                ->andWhere('st.id IN (:status)')
                ->setParameter('status', $search->status);
        }

        if (!empty($search->seasons)) {
            $query = $query
                ->andWhere('s.id IN (:seasons)')
                ->setParameter('seasons', $search->seasons);
        }

        if (!empty($search->formats)) {
            $query = $query
                ->andWhere('f.id IN (:formats)')
                ->setParameter('formats', $search->formats);
        }

        if (!empty($search->min)) {
            $minDate = new \DateTime($search->min . '/01/01');
            $query = $query
                ->andWhere('a.startDate >= :minDate')
                ->setParameter('minDate', $minDate);
        }

        if (!empty($search->max)) {
            $maxDate = new \DateTime($search->max . '/12/31');
            $query = $query
                ->andWhere('a.endDate <= :maxDate')
                ->setParameter('maxDate', $maxDate);
        }
        

        $query = $query->getQuery();
        return $this->paginator->paginate(
            $query,
            $search->page,
            40
        );
    }

    public function findAnimesInList(AnimeListData $search, $id): PaginationInterface
    {
        $query = $this->createQueryBuilder('a')
            ->join('a.categorie', 'c')
            ->join('a.format', 'f')
            ->join('a.season', 's')
            ->join('a.status', 'st')
            ->leftJoin('a.listes', 'l')
            ->andWhere('l.id = :listId')
            ->setParameter('listId', $id);

            if (!empty($search->q)) {
                $query = $query
                    ->andWhere('LOWER(a.nom) LIKE LOWER(:q)')
                    ->setParameter('q', "%{$search->q}%");
            }
    
            if (!empty($search->categories)) {
                $query = $query
                    ->andWhere('c.id IN (:categories)')
                    ->setParameter('categories', $search->categories);
            }
    
            if (!empty($search->status)) {
                $query = $query
                    ->andWhere('st.id IN (:status)')
                    ->setParameter('status', $search->status);
            }
    
            if (!empty($search->seasons)) {
                $query = $query
                    ->andWhere('s.id IN (:seasons)')
                    ->setParameter('seasons', $search->seasons);
            }
    
            if (!empty($search->formats)) {
                $query = $query
                    ->andWhere('f.id IN (:formats)')
                    ->setParameter('formats', $search->formats);
            }
    
            if (!empty($search->min)) {
                $minDate = new \DateTime($search->min . '/01/01');
                $query = $query
                    ->andWhere('a.startDate >= :minDate')
                    ->setParameter('minDate', $minDate);
            }
    
            if (!empty($search->max)) {
                $maxDate = new \DateTime($search->max . '/12/31');
                $query = $query
                    ->andWhere('a.endDate <= :maxDate')
                    ->setParameter('maxDate', $maxDate);
            }
        
        $query = $query->getQuery();
        return $this->paginator->paginate(
            $query,
            $search->page,
            40
        );
    }
    //    /**
    //     * @return Anime[] Returns an array of Anime objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Anime
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
