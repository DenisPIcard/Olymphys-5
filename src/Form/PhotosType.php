<?php


namespace App\Form;

use App\Entity\Centrescia;
use App\Entity\Equipesadmin;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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

        $edition = $session->get('edition');
        $centre = null;

        $qb = $this->doctrine->getRepository(Equipesadmin::class)->createQueryBuilder('e')
            ->andWhere('e.edition =:edition')
            ->setParameter('edition', $this->requestStack->getSession()->get('edition'))
            ->andWhere('e.inscrite = 1')
            ->addOrderBy('e.numero', 'ASC')
            ->addOrderBy('e.centre', 'ASC');
        if ($options['data']['centre'] != '') {
            $centre = $this->doctrine->getRepository(Centrescia::class)->findOneBy(['centre' => $options['data']['centre']]);
            $qb = $qb->andWhere('e.centre =:centre')
                ->setParameter('centre', $centre);
        }

        if ($options['data']['role'] != 'ROLE_PROF') {
            if ($options['data']['concours'] == 'inter') {

                $builder->add('equipe', EntityType::class, [
                    'class' => Equipesadmin::class,
                    'query_builder' => $qb,
                    'choice_value' => 'getInfoequipe',
                    'label' => 'Choisir une équipe',
                    'mapped' => false
                ]);
            }
            if ($options['data']['concours'] == 'cn') {
                $builder->add('equipe', EntityType::class, [
                    'class' => Equipesadmin::class,
                    'query_builder' => function (EntityRepository $ea) {
                        return $ea->createQueryBuilder('e')
                            ->andWhere('e.edition =:edition')
                            ->setParameter('edition', $this->requestStack->getSession()->get('edition'))
                            ->andWhere('e.selectionnee = 1')
                            // ->setParameter('selectionnee', 'TRUE')
                            ->addOrderBy('e.lettre', 'ASC');
                    },
                    'choice_label' => 'getInfoequipenat',
                    'label' => 'Choisir une équipe .',
                    'mapped' => false
                ]);
            }
        }
        if ($options['data']['role'] == 'ROLE_PROF') {
            $prof = $options['data']['prof'];
            $session->set('prof', $prof);

            if ($options['data']['concours'] == 'inter') {
                $builder->add('equipe', EntityType::class, [
                    'class' => Equipesadmin::class,
                    'query_builder' => function (EntityRepository $ea) {
                        return $ea->createQueryBuilder('e')
                            ->andWhere('e.idProf1 =:id')
                            ->orWhere('e.idProf2 =:id')
                            ->setParameter('id', $this->requestStack->getSession()->get('prof'))
                            ->andWhere('e.edition =:edition')
                            ->andWhere('e.inscrite = 1')
                            ->setParameter('edition', $this->requestStack->getSession()->get('edition'))
                            ->addOrderBy('e.numero', 'ASC');
                    },
                    'choice_label' => 'getInfoequipe',
                    'label' => 'Choisir une équipe',
                    'mapped' => false
                ]);
            }

            if ($options['data']['concours'] == 'cn') {

                $builder->add('equipe', EntityType::class, [
                    'class' => Equipesadmin::class,
                    'query_builder' => function (EntityRepository $ea) {
                        return $ea->createQueryBuilder('e')
                            ->andWhere('e.idProf1 =:id')
                            ->orWhere('e.idProf2 =:id')
                            ->setParameter('id', $this->requestStack->getSession()->get('prof'))
                            ->andWhere('e.edition =:edition')
                            ->setParameter('edition', $this->requestStack->getSession()->get('edition'))
                            ->andWhere('e.selectionnee = TRUE')
                            ->addOrderBy('e.lettre', 'ASC');
                    },
                    'choice_label' => 'getInfoequipenat',
                    'label' => 'Choisir une équipe',
                    'mapped' => false
                ]);
            }
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