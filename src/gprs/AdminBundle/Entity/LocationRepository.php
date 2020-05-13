<?php

namespace gprs\AdminBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UnitProtesterRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LocationRepository extends EntityRepository
{
    // Количество пользователей
    public function getLastLocation($icebox_id)
    {
        $res = $this->getEntityManager()
            ->createQuery('SELECT l.lat, l.lng, l.radius
                     FROM gprsAdminBundle:Location l
                     WHERE l.icebox_id = '.$icebox_id.'
                     ORDER BY l.id DESC
                     ')
            ->setMaxResults(1)
            ->getResult();
      
        $res = $res ? $res[0] : false;

        return $res;
    }
}
