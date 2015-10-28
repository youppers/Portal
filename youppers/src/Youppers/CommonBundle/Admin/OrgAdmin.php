<?php

namespace Youppers\CommonBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Youppers\CommonBundle\Admin\YouppersAdmin;
use Symfony\Component\Validator\Constraints as Assert;

class OrgAdmin extends YouppersAdmin
{

    private $companies;
    private $dealers;
    private $users;

    public function setSubject($subject) {
        $this->companies = clone $subject->getCompanies();
        $this->dealers = clone $subject->getDealers();
        $this->users = clone $subject->getUsers();
        return parent::setSubject($subject);
    }

    public function prePersist($org) {
        return $this->preUpdate($org);
    }

    public function preUpdate($org)
    {
//        dump(array(
//            'form'=> $org,
//            'companies' => $this->companies,
//            'dealers' => $this->dealers,
//            'users' => $this->users,
//        ));
        foreach ($this->companies as $company) {
            $company->setOrg(null);
        }
        foreach ($this->dealers as $dealer) {
            $dealer->setOrg(null);
        }
        foreach ($this->users as $user) {
            $user->setOrg(null);
        }
        foreach ($org->getCompanies() as $company) {
            if ($company->getOrg() != null && $company->getOrg() != $org) {
                throw new ModelManagerException("Company is of other org");
            }
            $company->setOrg($org);
        }
        foreach ($org->getDealers() as $dealer) {
            if ($dealer->getOrg() != null && $dealer->getOrg() != $org) {
                throw new ModelManagerException("Dealer is of other org");
            }
            $dealer->setOrg($org);
        }
        foreach ($org->getUsers() as $user) {
            if ($user->getOrg() != null && $user->getOrg() != $org) {
                throw new ModelManagerException("User is of other org");
            }
            $user->setOrg($org);
        }
//        dump(array(
//            'form'=> $org,
//            'companies' => $this->companies,
//            'dealers' => $this->dealers,
//            'users' => $this->users,
//        ));
    }

	/**
	 * {@inheritdoc}
	 */
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
		    ->addIdentifier('name')
            ->add('enabled')
            ->add('companies')
            ->add('dealers')
            ->add('users')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$datagridMapper
    		->add('name')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
            ->add('name')
            ->add('enabled')
            ->add('companies')
            ->add('dealers')
            ->add('users')
            ->add('createdAt')
            ->add('updatedAt')
		;

	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		
		$formMapper
			->add('name')
            ->add('enabled', null, array('required' => false))
            ->add('companies', null, array(
                'help' => 'Companies owned by this org - each company can belong to only one organization'
            ))
            ->add('dealers', null, array(
                'help' => 'Dealers owned by this org - each dealer can belong to only one organization'
            ))
            ->add('users', null, array(
                'help' => 'Users owned by this org - each user can belong to only one organization'
            ))
		;

	}



}
