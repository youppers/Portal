<?php

namespace Youppers\ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Doctrine\ORM\EntityRepository;

class DashboardController extends Controller {


    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getProductRepository()
    {
        return $this->getDoctrine()->getRepository('YouppersCompanyBundle:Product');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getProductCollectionRepository()
    {
        return $this->getDoctrine()->getRepository('YouppersProductBundle:ProductCollection');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getProductVariantRepository()
    {
        return $this->getDoctrine()->getRepository('YouppersProductBundle:ProductVariant');
    }

    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        $admin_pool = $this->get('sonata.admin.pool');

        $stats = array();

        $qb = $this->getProductRepository()
            ->createQueryBuilder('p')
            ->leftJoin('p.brand','b')
            ->leftJoin('b.company','c')
            ->select('c.name as company','b.name as brand','count(p.id) as products')
            ->groupBy('b.id')
            ->orderBy('c.name','ASC')
            ->addOrderBy('b.name','ASC');

        foreach ($qb->getQuery()->getArrayResult() as $row) {
            $stats[$row['company']][$row['brand']]['products'] = $row['products'];
        }

        $qb = $this->getProductCollectionRepository()
            ->createQueryBuilder('pc')
            ->leftJoin('pc.brand','b')
            ->leftJoin('b.company','c')
            ->select('c.name as company','b.name as brand','count(pc.id) as collections','sum(pc.enabled) as collections_enabled')
            ->groupBy('b.id')
            ->orderBy('c.name','ASC')
            ->addOrderBy('b.name','ASC');

        foreach ($qb->getQuery()->getArrayResult() as $row) {
            $stats[$row['company']][$row['brand']]['collections'] = $row['collections'];
            $stats[$row['company']][$row['brand']]['collections_enabled'] = $row['collections_enabled'];
        }

        $qb1 = clone $qb;
        $qb1->where('pc.image is not null');

        foreach ($qb1->getQuery()->getArrayResult() as $row) {
            $stats[$row['company']][$row['brand']]['collections_with_image'] = $row['collections'];
            $stats[$row['company']][$row['brand']]['collections_with_image_enabled'] = $row['collections_enabled'];
        }

        $qb1 = clone $qb;
        $qb1->where('pc.pdfGallery is not null');

        foreach ($qb1->getQuery()->getArrayResult() as $row) {
            $stats[$row['company']][$row['brand']]['collections_with_attach'] = $row['collections'];
            $stats[$row['company']][$row['brand']]['collections_with_attach_enabled'] = $row['collections_enabled'];
        }

        $qb = $this->getProductVariantRepository()
            ->createQueryBuilder('v')
            ->leftJoin('v.product','p')
            ->leftJoin('p.brand','b')
            ->leftJoin('b.company','c')
            ->select('c.name as company','b.name as brand','count(v.id) as variants','sum(v.enabled) as variants_enabled')
            ->groupBy('b.id')
            ->orderBy('c.name','ASC')
            ->addOrderBy('b.name','ASC');

        foreach ($qb->getQuery()->getArrayResult() as $row) {
            $stats[$row['company']][$row['brand']]['variants'] = $row['variants'];
            $stats[$row['company']][$row['brand']]['variants_enabled'] = $row['variants_enabled'];
        }

        $qb1 = clone $qb;
        $qb1->where('v.image is not null');

        foreach ($qb1->getQuery()->getArrayResult() as $row) {
            $stats[$row['company']][$row['brand']]['variants_with_image'] = $row['variants'];
            $stats[$row['company']][$row['brand']]['variants_with_image_enabled'] = $row['variants_enabled'];
        }

        $qb1 = clone $qb;
        $qb1->where('v.pdfGallery is not null');

        foreach ($qb1->getQuery()->getArrayResult() as $row) {
            $stats[$row['company']][$row['brand']]['variants_with_attach'] = $row['variants'];
            $stats[$row['company']][$row['brand']]['variants_with_attach_enabled'] = $row['variants_enabled'];
        }

        //dump($stats); //die;

        return array(
            'admin_pool' => $admin_pool,
            'stats' => $stats,
            'companies1' => array(
                array(
                    'name' => 'Marazzi',
                    'brands' => array(
                        array(
                            'name' => 'Marazzi',
                            'products' => 300,
                        ),
                        array(
                            'name' => 'Ragno',
                            'products' => 300,
                        ),
                    )
                )
            )
        );
    }

}