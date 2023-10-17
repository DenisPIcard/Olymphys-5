<?php


namespace App\Form;

use App\Entity\Centrescia;
use App\Entity\Edition;
use App\Entity\Equipesadmin;
use App\Entity\Odpf\OdpfEquipesPassees;
use App\Repository\EquipesadminRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PhotosType extends AbstractType
{
    private RequestStack $requestStack;
    private EntityManagerInterface $doctrine;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $session = $this->requestStack->getSession();

        $edition = $this->requestStack->getSession()->get('edition');
        $editionN1 = $this->doctrine->getRepository(Edition::class)->findOneBy(['ed'=>$edition->getEd()-1]);
        if (new DateTime('now')<$session->get('edition')->getDateouverturesite()){
            $edition = $editionN1;
        }
        $centre = null;
        $options['data']['concours']=='cn'?$valSel= true: $valSel=false;
        $qb = $this->doctrine->getRepository(Equipesadmin::class)->createQueryBuilder('e')
                ->andWhere('e.edition =:edition')
                ->setParameter('edition', $edition)
                ->andWhere('e.inscrite = 1')
                ->leftJoin('e.edition','ed')
                ->addOrderBy('ed.ed','DESC');
        if ($valSel==false){
            $qb->andWhere('e.numero <100');
            $infoEquipe='infoequipe';
        }
        if ($valSel==true){
            $qb ->addOrderBy('e.lettre', 'ASC')
                ->andWhere('e.selectionnee =:valeur')
                ->setParameter('valeur',$valSel);

            $infoEquipe='infoequipenat';
            }
        else{
                $qb->addOrderBy('e.numero', 'ASC')
                   ->addOrderBy('e.centre', 'ASC');
        }
        if ($options['data']['centre'] != '') {
            $centre = $this->doctrine->getRepository(Centrescia::class)->findOneBy(['centre' => $options['data']['centre']]);
            $qb = $qb->andWhere('e.centre =:centre')
                ->setParameter('centre', $centre);
        }

        if ($options['data']['role'] != 'ROLE_PROF') {

                $builder->add('equipe', EntityType::class, [
                    'class' => Equipesadmin::class,
                    'query_builder' => $qb,
                    'choice_label' => $infoEquipe,
                    'label' => 'Choisir une équipe',
                    'mapped' => false
                ]);


        }

        if ($options['data']['role'] == 'ROLE_PROF') {
            $prof = $options['data']['prof'];

            $qb->andWhere('e.idProf1 =:id or e.idProf2 =:id')
                ->setParameter('id', $prof)
                ->andWhere('e.inscrite = 1');


                $builder->add('equipe', EntityType::class, [
                    'class' => Equipesadmin::class,
                    'query_builder' => $qb,
                    'choice_label' => 'getInfoequipe',
                    'label' => 'Choisir une équipe',
                    'mapped' => false
                ]);



        }
        $builder->add('photoFiles', FileType::class, [
            'label' => 'Choisir les photos(format .jpeg obligatoire)',
            'mapped' => false,
            'required' => false,
            'multiple' => true,
        ])
            ->add('Valider', SubmitType::class);


    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => null, 'concours' => '',
            'role' => '', 'prof' => null]);

    }
}