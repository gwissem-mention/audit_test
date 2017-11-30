<?php

namespace HopitalNumerique\ObjetBundle\Manager;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ObjetBundle\Entity\Consultation;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Doctrine\ORM\EntityManager;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use HopitalNumerique\ObjetBundle\Repository\ConsultationRepository;

/**
 * Manager de l'entité Consultation.
 * @method ConsultationRepository getRepository
 */
class ConsultationManager extends BaseManager
{
    protected $class = 'HopitalNumerique\ObjetBundle\Entity\Consultation';

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * Construct.
     *
     * @param EntityManager   $em
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(EntityManager $em, TokenStorageInterface $tokenStorage)
    {
        parent::__construct($em);

        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Retourne les dernières consultations de l'user $user.
     *
     * @param User $user L'user connecté
     * @param integer $domaineId
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
     * @param Domaine $domaine
     * @param Objet|Contenu $objet     La publication visitée
     * @param bool          $isContenu Is contenu ?
     */
    public function consulted($domaine, $objet, $isContenu = false)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ($isContenu) {
            $queryParameters = [
                'objet' => $objet->getObjet(),
                'contenu' => $objet,
                'domaine' => $domaine,
            ];
        } else {
            $queryParameters = [
                'objet' => $objet,
                'contenu' => null,
                'domaine' => $domaine,
            ];
        }

        if ($user instanceof User) {
            $queryParameters['user'] = $user;
            $consultation = $this->getRepository()->findCurrentConsultation($queryParameters);
        } else {
            $queryParameters['user'] = null;
            $queryParameters['sessionId'] = session_id();
            $consultation = $this->getRepository()->findCurrentConsultation($queryParameters);
        }

        if (null === $consultation) {
            $consultation = $this->createEmpty();
            $consultation->setDomaine($domaine);

            if ($isContenu) {
                $consultation->setContenu($objet);
                $consultation->setObjet($objet->getObjet());
            } else {
                $consultation->setObjet($objet);
            }

            if ($user instanceof User) {
                $consultation->setUser($user);
            } else {
                $consultation->setSessionId(session_id());
            }

            $this->save($consultation);
        }
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
                                                                      > $one->getConsultationDate();
                    // Cas contenu
                } else {
                    // Si la date de dernière mise à jour du contenu est
                    // postérieure à la dernière consultation du contenu : Notif updated
                    $consulted['contenus'][$one->getContenu()->getId()] = $one->getContenu()->getDateModification()
                                                                          > $one->getConsultationDate();
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
