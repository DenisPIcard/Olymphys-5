<?php


namespace App\Form;

use App\Entity\Equipesadmin;
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

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $session = $this->requestStack->getSession();

        if ($options['data']['role'] != 'ROLE_PROF') {
            if ($options['data']['concours'] == 'inter') {

                $builder->add('equipe', EntityType::class, [
                    'class' => Equipesadmin::class,
                    'query_builder' => function (EntityRepository $ea) {
                        return $ea->createQueryBuilder('e')
                            ->andWhere('e.edition =:edition')
                            ->setParameter('edition', $this->requestStack->getSession()->get('edition'))
                            ->addOrderBy('e.centre', 'ASC')
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