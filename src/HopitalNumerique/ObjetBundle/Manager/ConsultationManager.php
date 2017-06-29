<?php

namespace HopitalNumerique\ObjetBundle\Manager;

use HopitalNumerique\ObjetBundle\Entity\Consultation;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Manager de l'entité Consultation.
 */
class ConsultationManager extends BaseManager
{
    protected $class = 'HopitalNumerique\ObjetBundle\Entity\Consultation';
    protected $securityContext;

    /**
     * Construct.
     *
     * @param EntityManager   $em
     * @param SecurityContext $securityContext
     */
    public function __construct(EntityManager $em, $securityContext)
    {
        parent::__construct($em);
        $this->securityContext = $securityContext;
    }

    /**
     * Retourne les dernières consultations de l'user $user.
     *
     * @param User $user L'user connecté
     *
     * @return array
     */
    public function getLastsConsultations($user, $domaineId)
    {
        return $this->getRepository()->getLastsConsultations($user, $domaineId)->getQuery()->getResult();
    }

    /**
     * On met l'objet en consulté (création si première visite, ou update de la date).
     *
     * @param Objet|Contenu $objet     La publication visitée
     * @param bool          $isContenu Is contenu ?
     */
    public function consulted($domaine, $objet, $isContenu = false)
    {
        $user = $this->securityContext->getToken()->getUser();

        if ($user instanceof User) {
            $consultation = $isContenu ? $this->findOneBy(
                ['objet' => $objet->getObjet(), 'contenu' => $objet, 'user' => $user, 'domaine' => $domaine]
            ) : $this->findOneBy(['objet' => $objet, 'user' => $user, 'contenu' => null, 'domaine' => $domaine]);

            //new
            /** @var Consultation $consultation */
            if (is_null($consultation)) {
                $consultation = $this->createEmpty();

                $consultation->setDomaine($domaine);

                if ($isContenu) {
                    $consultation->setContenu($objet);
                    $consultation->setObjet($objet->getObjet());
                } else {
                    $consultation->setObjet($objet);
                }

                $consultation->setUser($user);
                //update
            } else {
                $consultation->setDateLastConsulted(new \DateTime());
                $consultation->setViewsCount($consultation->getViewsCount()+1);
            }
        } else {
            /** @var Consultation $consultation */
            $consultation = $isContenu
                ? $this->findOneBy(
                    [
                        'objet' => $objet->getObjet(),
                        'contenu' => $objet,
                        'user' => null,
                        'domaine' => $domaine,
                        'sessionId' => session_id(),
                    ]
                )
                : $this->findOneBy(
                    [
                        'objet' => $objet,
                        'user' => null,
                        'contenu' => null,
                        'domaine' => $domaine,
                        'sessionId' => session_id(),
                    ]
                )
            ;

            if (is_null($consultation)) {
                $consultation = $this->createEmpty();

                $consultation->setDomaine($domaine);

                if ($isContenu) {
                    $consultation->setContenu($objet);
                    $consultation->setObjet($objet->getObjet());
                } else {
                    $consultation->setObjet($objet);
                }

                $consultation->setSessionId(session_id());
            } else {
                $consultation->setDateLastConsulted(new \DateTime());
                $consultation->setViewsCount($consultation->getViewsCount()+1);
            }
        }

        $this->save($consultation);
    }

    /**
     * Récupère les consultations concernées par l'objet passé en param.
     *
     * @param Objet $objet
     *
     * @return mixed
     */
    public function getConultationsByObjet(Objet $objet)
    {
        return $this->getRepository()
                    ->getUsersConcerneByObjet($objet->getId(), $objet->getDomainesId())->getQuery()
                    ->getResult()
        ;
    }

    /**
     * Met à jour le tableau de productions avec les prod consultées par l'user connecté.
     *
     * @param $domaineId
     * @param $productions
     * @param $user
     *
     * @return mixed
     */
    public function updateProductionsWithConnectedUser($domaineId, $productions, $user)
    {
        if ($user instanceof User) {
            // get date Inscription user
            $dateInscription = $user->getRegistrationDate();

            // get consulted objets and formate them
            $results = $this->getLastsConsultations($user, $domaineId);
            $consulted = ['objets' => [], 'contenus' => []];
            foreach ($results as $one) {
                // Cas objet
                if (is_null($one->getContenu())) {
                    // Si la date de dernière mise à jour de l'objet est
                    // postérieure à la dernière consultation de l'objet : Notif updated
                    $consulted['objets'][$one->getObjet()->getId()] = $one->getObjet()->getDateModification()
                                                                      > $one->getDateLastConsulted();
                    // Cas contenu
                } else {
                    // Si la date de dernière mise à jour du contenu est
                    // postérieure à la dernière consultation du contenu : Notif updated
                    $consulted['contenus'][$one->getContenu()->getId()] = $one->getContenu()->getDateModification()
                                                                          > $one->getDateLastConsulted();
                }
            }

            // Parcours des objets retournés par la recherche
            foreach ($productions as &$production) {
                $id = $production->id;
                $isConsulted = false;
                $type = $production->objet ? 'objets' : 'contenus';

                // la publication fait partie des publications déjà consultées par l'utilisateur
                if (isset($consulted[$type][$id])) {
                    $isConsulted = true;
                    $production->updated = $consulted[$type][$id];
                }

                // Si la publication n'a jamais été consulté
                // ET
                // Si la date de création de l'objet est
                // postérieure à la date d'inscription de l'utilisateur : Notif new
                if ($isConsulted === false && ($production->created > $dateInscription)) {
                    $production->new = true;
                }
            }
        }

        return $productions;
    }

    /**
     * Get nombre consultations.
     *
     * @return int
     */
    public function getNbConsultations()
    {
        return $this->getRepository()->getNbConsultations()->getQuery()->getSingleScalarResult();
    }
}
