<?php

namespace gprs\AdminBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UnitProtesterRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DataRepository extends EntityRepository
{
    public function getSQL($query)
    {
        $connection = $this->getEntityManager()->getConnection();
        $sth = $connection->prepare($query);
        $sth->execute();
        $results = array();
        while($row = $sth->fetch()) {
                $results[] = $row; 
        }
        return $results;
    }

    public function getLastItemByIceboId($iceboxId) {
        $sql = "SELECT * FROM data d WHERE icebox_id = {$iceboxId} ORDER BY id DESC LIMIT 1";
        $result = $this->getSQL($sql);

        return isset($result[0]) ? $result[0] : NULL;
    }
    
    public function findLast($id)
    {
        $res = $this->getEntityManager()
            ->createQuery('SELECT d
                     FROM gprsAdminBundle:Data d
                     WHERE d.icebox_id = '.$id.'
                     ORDER BY d.id DESC
                     ')
            ->setMaxResults(1)
            ->getArrayResult();

        return isset($res[0]) ? $res[0] : false;
    }

    public function findLastData($icebox_id,$limit,$period=array(),$last_id=false)
    {
//AND d.weight >= 0
//                         AND d.weight <= 200
        $res = $this->getEntityManager()->createQuery('SELECT d
                     FROM gprsAdminBundle:Data d
                     WHERE d.icebox_id = '.$icebox_id.'

                         '.(($period)?' AND d.created_at >= :from AND d.created_at <= :to':'').' 
                         '.(($last_id)?' AND d.id < '.$last_id:'').' 
                     ORDER BY d.id DESC
                     ')
            ->setParameters($period)
            ->setMaxResults($limit)
            ->getResult();

        return $res;
    }
    
    public function getCount($icebox_id,$period=array())
    {
        $query = $this->getEntityManager()->createQuery('SELECT COUNT(d.id) 
            FROM gprsAdminBundle:Data d 
            WHERE d.icebox_id = '.$icebox_id.'
                AND d.weight >= 0
                AND d.weight <= 200
                '.(($period)?' AND d.created_at >= :from AND d.created_at <= :to':'').' 
            ')
                ->setParameters($period);
        
        $count = $query->getSingleScalarResult();

        return $count;
    }

    public function getFilterData($filter,$from=0,$coun=60)
    {
        $format = '%Y.%m.%d %H:%i:%s';
        $group_by = '';
        if(isset($filter['period'])) {
            switch($filter['period']) {
                case 'minute': $format = '%Y-%m-%d %H:%i'; break;
                case 'hour': $format = '%Y-%m-%d %Hh'; break;
                case 'day': $format = '%Y-%m-%d'; break;
                default : $format = '%Y-%m-%d %H:%i:%s';
            }
            $group_by = ' GROUP BY num_hour';
        }
        if($filter['graph']=='dooropen'){
            $format = '%Y-%m-%d';
            $coun = 30;
            $page = isset($post['page'])?$post['page']:1;
            $from=($page-1)*$coun;
        }
        if($filter['daterange']) {
            $range = $filter['daterange'];
        }
        // , COUNT( DATE_FORMAT( `created_at` , '%H' ) ) AS counts, AVG( t_inside ) AS t_ins
        $sql = "SELECT *, SUM(`dooropen`) AS summ_dooropen, DATE_FORMAT( `created_at` , '".$format."' ) AS num_hour
                FROM data d
                WHERE d.icebox_id=".$filter['id'];

        if($filter['graph']=='temperature') $sql .= " AND d.t_inside > -20 AND d.t_out > -20";
        if(isset($range)) $sql .= " AND d.created_at >= '".$range['start']." 00:00:00' AND d.created_at <= '".$range['end']." 23:59:59'";
        $sql .= $group_by;
        $count_rows = $this->getSQL('SELECT COUNT(*) coun FROM ('.$sql.') cn');
        $sql .= " ORDER BY id";
        if(!isset($range)) $sql .= " DESC";
        if($count_rows[0]['coun']>$from) $sql .= " LIMIT ".$from.",".$coun;
        else $sql .= " LIMIT 0,".$coun;
        if(!isset($range)) $sql = "SELECT * FROM (".$sql.") sub ORDER BY id ASC";
        $res = $this->getSQL($sql);
                 
        return array('data'=>$res,'count'=>$count_rows[0]['coun']);
    }
}
