<?php

namespace App\Controller\Admin;

use App\Entity\Rne;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use Symfony\Component\HttpFoundation\RequestStack;

class RneCrudController extends AbstractCrudController
{
    private RequestStack $requestStack;
    private AdminContextProvider $adminContextProvider;
    private ManagerRegistry $doctrine;

    public function __construct(RequestStack $requestStack, AdminContextProvider $adminContextProvider, ManagerRegistry $doctrine)
    {
        $this->requestStack = $requestStack;;
        $this->adminContextProvider = $adminContextProvider;
        $this->doctrine=$doctrine;
    }

    public static function getEntityFqcn(): string
    {
        return Rne::class;
    }


    public function configureFields(string $pageName): iterable
    {

        $nom = TextField::new('appellationOfficielle', 'Nom');
        $adresse = TextField::new('adresse', 'Adresse');
        $CP = TextField::new('codePostal', 'CP');
        $ville = TextField::new('commune', 'Ville');
        $academie = TextField::new('academie', 'Académie');
        $codeUAI = IntegerField::new('rne', 'Code UAI');
        if (Crud::PAGE_INDEX === $pageName) {
            return [$nom, $adresse, $CP, $ville, $academie, $codeUAI,];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$nom, $adresse, $CP, $ville, $academie, $codeUAI,];
        }
    }

    public function configureActions(Actions $actions): Actions
    {

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE);


    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $session = $this->requestStack->getSession();
        $context = $this->adminContextProvider->getContext();
        $repositoryEdition = $this->doctrine->getManager()->getRepository(Edition::class);

        if ($context->getRequest()->query->get('filters') == null) {
            $edition = $session->get('edition');

        } else {
            if (isset($context->getRequest()->query->get('filters')['edition'])) {

                $idEdition = $context->getRequest()->query->get('filters')['edition'];
                $edition = $repositoryEdition->findOneBy(['id' => $idEdition]);
                $session->set('titreedition', $edition);
            }


        }
        $qb = $this->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->leftJoin('entity.equipes', 'eq')
            ->andWhere('eq.edition =:edition')
            ->setParameter('edition', $edition)
            ->leftJoin('entity.user', 'u')
            ->orderBy('u.nom', 'ASC');;
        $this->set_equipeString($edition, $qb);
        return $qb;
    }

}
