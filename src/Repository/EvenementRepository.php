<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evenement>
 *
 * @method Evenement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Evenement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Evenement[]    findAll()
 * @method Evenement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    public function save(Evenement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Evenement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findEvents()
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->where('e.total > 0')
            ->andWhere('e.date < :currentDate')
            ->setParameter('currentDate', new \DateTime())
            ->orderBy('e.date', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

    public function selectEvents()
    {
        $dateActuelle = new \DateTimeImmutable();

        $qb = $this->createQueryBuilder('e')
            ->where('e.date > :dateActuelle')
            ->orderBy('e.date', 'ASC')
            ->setMaxResults(3)
            ->setParameter('dateActuelle', $dateActuelle);

        $query = $qb->getQuery();
        $resultats = $query->getResult();

        return $resultats;
    }


    public function selectEvents1()
    {


        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.date', 'DESC');


        $query = $qb->getQuery();
        $resultats = $query->getResult();

        return $resultats;
    }

    public function chart_repository(){
        return  $this->createQueryBuilder('r')
            -> select('r.gouv, COUNT(r.id) as count')
            ->groupBy('r.gouv')
            ->getQuery()
            ->getResult()
            ;
    }


    public function chartRepository()
    {
        $qb = $this->createQueryBuilder('r');

        $qb->select('r.gouv, COUNT(r.id) as count')
            ->groupBy('r.gouv');

        return $qb->getQuery()->getResult();
    }






//    /**
//     * @return Evenement[] Returns an array of Evenement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Evenement
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
