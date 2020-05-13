<?php

namespace gprs\AdminBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UnitProtesterRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TowerRepository extends EntityRepository
{
    public function findByCids($cids=array())
    {
        $res = array();
        if(count($cids)){
            $res = $this->getEntityManager()
                ->createQuery('SELECT t
                         FROM gprsAdminBundle:Tower t
                         WHERE t.cid in ('.implode(',',$cids).')
                         ')
                ->getArrayResult();
        }
        return $res;
    }
}
